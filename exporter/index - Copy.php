<?php
//---------------------------------
	// Insert the page header
	$page_title = 'Main';
	require_once('header.php');
	
	$page = $_SERVER['PHP_SELF'];
	$sec = "99999";
	header("Refresh: $sec; url=$page");
	
	require_once('navmenu.php');
	
	require_once('connectvars.php');
	
	date_default_timezone_set("Asia/Shanghai");
	
	require_once('overlap.php');//调用overlap算法
	
	$Max_Gap_A = 24;
	
	$Max_Gap_C = 6;
	
	$Target_TAT_TD = 4;
	
//---------------------------------------------------------------------------------------------------------------------------------------------------

	echo '<div class="input">';
	
		echo '<form method="post" action="'.$_SERVER['PHP_SELF'].'">';
			//search by Model, Client, PLO, SO
			echo '<select name="identity" id="identity">';
				echo '<option value="Client">Client Name</option>';
				echo '<option value="PLO" selected>PLO</option>';
				echo '<option value="SO">SO</option>';
				echo '<option value="DN">DN</option>';
				echo '<option value="Picklist_PL">生产车间</option>';
			echo '</select> &nbsp';
			
			if (isset($_POST['identity'])) {
				echo '<script type="text/javascript">';
					echo 'document.getElementById(\'identity\').value = "'.$_POST['identity'].'"';
				echo '</script>';
			}
			
			echo '<input id="entry" name="entry" value="'.htmlspecialchars($_POST['entry']).'"/> &nbsp';
			
			echo '&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp';

			echo '<input type="checkbox" name="pgiflag" id="pgiflag" value="1"'; if(isset($_POST['pgiflag'])) echo "checked='checked'"; echo '> Show PGIed Only &nbsp';
			
			echo '&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp';
			
			//select time base
			echo '<select name="base" id="base">';
				echo '<option value="BirthDate">BKPL</option>';
				echo '<option value="WHUpdateTime">Material Ready</option>';
				echo '<option value="HandoverTime">Handover</option>';
				echo '<option value="PGITime" selected>PGI</option>';
			echo '</select>';
			
			if (isset($_POST['base'])) {
				echo '<script type="text/javascript">';
					echo 'document.getElementById(\'base\').value = "'.$_POST['base'].'"';
				echo '</script>';
			}
			
			echo '&nbsp&nbsp';
			
			//time input box
			echo '<label for="startdate"> From : </label><input id="startdate" name="startdate" type="date" value="'.date("Y-m-d").'"/> &nbsp';
			if (isset($_POST['startdate'])) {
				echo '<script type="text/javascript">';
					echo 'document.getElementById(\'startdate\').value = "'.$_POST['startdate'].'"';
				echo '</script>';
			}
			
			echo '<label for="enddate"> To : </label><input id="enddate" name="enddate" type="date" value="'.date("Y-m-d",strtotime(date("Y-m-d")."+1 days")).'"/> &nbsp';
			if (isset($_POST['enddate'])) {
				echo '<script type="text/javascript">';
					echo 'document.getElementById(\'enddate\').value = "'.$_POST['enddate'].'"';
				echo '</script>';
			}
			
			echo '<input type="submit" name="submit" value="Search" />';

			echo '&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp (Unit: hour) &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp';

			echo '<iframe id="txtArea1" style="display:none"></iframe>';
			echo '<button id="btnExport" onclick="fnExcelReport();"> EXPORT </button>';
		echo '</form>';
	echo '</div>';
	
	echo '</br>';
	
	// Get post data
	$submit = $_POST['submit'];
	$startdate = $_POST['startdate'];
	$enddate = $_POST['enddate'];
	$base = $_POST['base'];
	$pgiflag = $_POST['pgiflag'];
	$identity = $_POST['identity'];
	// enable multiple PLO/SO search
	if (in_array($identity, array('PLO', 'SO', 'DN')) && !empty($_POST['entry'])) {
		$entries = explode(",",$_POST['entry']);
		$entryArr = array();
		foreach ($entries as $v) {
			array_push($entryArr,trim($v));
		} 
		$entry = "'".implode("','", $entryArr)."'";
	} else {
		$entry = trim($_POST['entry']);
	}
//---------------------------------------------------------------------------------------------------------------------------------------------------
	
	// Connect to 112 DB	
	$dbc = mssql_connect(DB_HOST_112, DB_USER_112, DB_PASSWORD_112) or die("connect db error"); 
	mssql_select_db(DB_NAME_112,$dbc) or die('can not open db table');
	
	// SQL linked server query error on PHP
	mssql_query("SET ANSI_NULLS ON", $dbc); 
	mssql_query("SET ANSI_WARNINGS ON", $dbc);
	
		$query = "SELECT 
					DISTINCT
					PCTMaster.*,
					Comment7,
					Comment7_2,
					Comment8,
					Comment9,
					Comment10,
					Comment11,
					Comment12,
					logtime,
					closetime,
					[Complexity Groups] AS Complexity,
					PCTTarget,
					CusName,
					EndCusName,
					ShippingName,
					Owner,
					CASE WHEN FEFlag=1 THEN MaxPCT+1 ELSE MaxPCT END MaxPCT
				  FROM 
					PCTMaster
					LEFT JOIN
					Picklist
					ON PCTMaster.PLO=Picklist.PLO
					LEFT JOIN
					BPOCustomer
					ON PCTMaster.BPO=BPOCustomer.BPO
					LEFT JOIN
					ProductFamily
					ON PCTMaster.Family=ProductFamily.ProductFamily AND (PCTMaster.ConfigType=ProductFamily.ConfigType OR PCTMaster.ConfigType is NULL)
					LEFT JOIN
					TechDirect.dbo.TechDirect
					ON PCTMaster.TDNo=TechDirect.id
					LEFT JOIN
					--(SELECT DISTINCT [Complexity Groups], [Sales Order ID], [Sales Order Line Item ID] FROM [104].VOM.dbo.DailyPROExtraction WHERE [Plant Code] = 'BF02' AND [Higher Level Item Flag] = 'Y' AND [Base Quantity] <> '0') T
					[104].VOM.dbo.DailyPROExtraction T
					ON PCTMaster.SO collate Chinese_PRC_CI_AS=T.[Sales Order ID] AND PCTMaster.Line collate Chinese_PRC_CI_AS=T.[Sales Order Line Item ID]
					LEFT JOIN
					ComplexityGroup
					ON Complexity = ComplexityGroup.Complexity
				  WHERE 
					";
	if (isset($submit)) {
		if (isset($pgiflag)) {
			$query   .="  PGITime IS NOT NULL
						  AND 
			";
		}
		if (isset($entry) && !empty($entry)) {
			if ($identity <> 'Client') {
				if (in_array($identity, array('PLO', 'SO', 'DN'))) {
					$query .=" PCTMaster.{$identity} IN ({$entry})
					";
				} elseif ($identity == 'Picklist_PL') {
					$query .=" {$identity} LIKE '%{$entry}%'
				";
				} else {
					$query .=" PCTMaster.{$identity} LIKE '%{$entry}%'
					";
				}
			} else {
				$query .= "
						(CusName LIKE '%{$entry}%'
						OR
						EndCusName LIKE '%{$entry}%'
						OR
						ShippingName LIKE '%{$entry}%')
				";
			}
		} else {
			if (isset($base) && isset($startdate) && isset($enddate)) {
				$query .=" {$base} BETWEEN '{$startdate}' and '{$enddate}'
						   --ORDER BY
						   --{$base} DESC 
				"; 
			}
		}
		
	} elseif (isset($_GET['PLO'])) {
		$query .=" PCTMaster.PLO IN ({$_GET['PLO']})
					";
	} else {
		$query .=" PGITime >= dateadd(day,datediff(day,1,GETDATE()),0)
				   AND 
				   PGITime < dateadd(day,datediff(day,0,GETDATE()),0)
				   --ORDER BY
				   --PGITime DESC
		";			
	}	
	// print_r($query);
	// for PCT Target to BPO
	$query2 = "SELECT
			    DN,
				MAX(MaxPCT) MaxPCT
			   FROM (
				   SELECT
					PCTMaster.*,
					CASE WHEN FEFlag=1 THEN MaxPCT+1 ELSE MaxPCT END MaxPCT
				   FROM
					PCTMaster
					LEFT JOIN
					ProductFamily
					ON PCTMaster.Family=ProductFamily.ProductFamily AND (PCTMaster.ConfigType=ProductFamily.ConfigType OR PCTMaster.ConfigType is NULL)
				   WHERE
					DN IN (
						SELECT
							DN
						FROM (
							{$query}
						) T2
					) 
			   ) T1
			   GROUP BY 
				DN";

	$data2 = mssql_query($query2,$dbc) or die('search db error ');
			
	while ($row2 = mssql_fetch_assoc($data2)){
		if (isset($row2['DN'])) {
			$Max_PCT[trim($row2['DN'])] = $row2['MaxPCT']*24;	
		} 
	}

	// Show PGIed PLOs of yesterday by default
	if (true) {		
		echo '<table width=100%>';
		echo '<tr>';
			echo '<td valign="top">';
				//------print data table
				echo '<table id="major" border="1" cellpadding=2 style="color:navy;font-weight:bold;font-family:Calibri;font-size:9pt;">';
				echo '<thead>';
				echo '<tr bgcolor=navy style=color:white>';
				  echo '<th>Client</th>';//-----------------------------------------For Getting CusName
				  echo '<th>SO</th>';
				  echo '<th>BPO</th>';
				  echo '<th>PLO</th>';
				  echo '<th>FE?</th>';
				  echo '<th>Complexity</th>';
				  echo '<th>Family</th>';
				  echo '<th>Model</th>';
				  echo '<th>ShipRef</th>';
				  echo '<th>BKPL</th>';
				  echo '<th data-tsorter="numeric">Dev A</th>';
				  echo '<th>Material Ready</th>';
				  echo '<th data-tsorter="numeric">Dev B</th>';
				  echo '<th>Handover</th>';
				  echo '<th data-tsorter="numeric">Dev C</th>';
				  echo '<th>PGI</th>';
				  echo '<th data-tsorter="numeric">PCT</th>';
				  echo '<th data-tsorter="numeric">Dev PCT</th>';
				  echo '<th>Comments</th>';
				  echo '<th>Details</th>';
				  echo '<th>Owner</th>';
				  // echo '<th>Egoras Fail</th>';
				  // echo '<th>Details</th>';
				  echo '<th>TD#</th>';
				  echo '<th>TD solution time</th>';
				echo '</tr>';
				echo '</thead>';
				
				$Priority = array('Failed' => 0, 'Passed' => 0);
				
				$data = mssql_query($query,$dbc) or die('search db error ');
				
				while ($row = mssql_fetch_assoc($data)){
					// calc dev
					if (isset($row['BirthDate'])) {
						$row['BirthDate'] = date_format(date_create_from_format('M d Y  h:i:s:ua', $row['BirthDate']), 'Y-m-d H:i:s');
					}
					if (isset($row['WHUpdateTime'])) {
						$row['WHUpdateTime'] = date_format(date_create_from_format('M d Y  h:i:s:ua', $row['WHUpdateTime']), 'Y-m-d H:i:s');
					}
					if (isset($row['HandoverTime'])) {
						$row['HandoverTime'] = date_format(date_create_from_format('M d Y  h:i:s:ua', $row['HandoverTime']), 'Y-m-d H:i:s');
					}
					if (isset($row['PGITime'])) {
						$row['PGITime'] = date_format(date_create_from_format('M d Y  h:i:s:ua', $row['PGITime']), 'Y-m-d H:i:s');
					}
					if (isset($row['logtime'])) {
						$row['logtime'] = date_format(date_create_from_format('M d Y  h:i:s:ua', $row['logtime']), 'Y-m-d H:i:s');
					}
					if (isset($row['closetime'])) {
						$row['closetime'] = date_format(date_create_from_format('M d Y  h:i:s:ua', $row['closetime']), 'Y-m-d H:i:s');
					}

					if (isset($row['BirthDate']) && isset($row['WHUpdateTime'])) {
						$Gap_A[trim($row['PLO'])] = ol_wh($row['BirthDate'], $row['WHUpdateTime']);
						$dev_a[trim($row['PLO'])] = $Gap_A[trim($row['PLO'])] - $Max_Gap_A; // calc dev a
					} else {
						$dev_a[trim($row['PLO'])] = null;
					}
					// PCT Goal by BPO or PLO
					if (isset($Max_PCT[trim($row['DN'])])) {
						$Max_PCT[trim($row['PLO'])] = $Max_PCT[trim($row['DN'])];		
					} else {
						// $Max_PCT[trim($row['PLO'])] = $row['MaxPCT']*24;
						$Max_PCT[trim($row['PLO'])] = $row['PCTTarget']*24; // changed 20171205
					}

					if (isset($row['WHUpdateTime']) && isset($row['HandoverTime'])) {
						$Gap_B[trim($row['PLO'])] = ol_line($row['WHUpdateTime'], $row['HandoverTime']);
						$Max_Gap_B[trim($row['PLO'])] = $Max_PCT[trim($row['PLO'])] - $Max_Gap_A - $Max_Gap_C;
						$dev_b[trim($row['PLO'])] = $Gap_B[trim($row['PLO'])] - $Max_Gap_B[trim($row['PLO'])]; // calc dev b
					} else {
						$dev_b[trim($row['PLO'])] = null;
					}
					
					if (isset($row['HandoverTime']) && isset($row['PGITime'])) {
						$Gap_C[trim($row['PLO'])] = ol_wh($row['HandoverTime'], $row['PGITime']);
						$dev_c[trim($row['PLO'])] = $Gap_C[trim($row['PLO'])] - $Max_Gap_C; // calc gap dev c
					} else {
						$dev_c[trim($row['PLO'])] = null;
					}
					
					if (isset($row['BirthDate']) && isset($row['PGITime'])) {
						$PCT[trim($row['PLO'])] = ol_wh($row['BirthDate'], $row['PGITime']);
						$dev[trim($row['PLO'])] = $PCT[trim($row['PLO'])] - $Max_PCT[trim($row['PLO'])]; // calc PCT
					} else {
						$dev[trim($row['PLO'])] = null;
					}
					if (isset($dev[trim($row['PLO'])])) {
						$Max_dev[trim($row['PLO'])] = max($dev_a[trim($row['PLO'])], $dev_b[trim($row['PLO'])], $dev_c[trim($row['PLO'])]);
					}
					
					// Begin to print
					if ($dev[trim($row['PLO'])] > 0 && !stristr($row['SO'], '4251')) { // Excl. Japan Orders
						echo '<tr bgcolor="#FFC7CE">';
						$Priority['Failed']++;
					} else {
						echo '<tr>';
						$Priority['Passed']++;
					}
							if (isset($row['EndCusName'])) {
								if (trim($row['EndCusName']) <> 'n/a') {
									echo '<td>'.trim($row['EndCusName']).'</td>';
								} else {
									if (isset($row['ShippingName'])) {
										echo '<td>'.trim($row['ShippingName']).'</td>';
									}
									else {
										echo '<td>N/A</td>';
									}
								}
							} else {
								echo '<td>N/A</td>';//-----------------------------------------For Getting CusName
							}
							
							if (isset($row['SO'])) {
								echo '<td>'.$row['SO'].'</td>';
							} else {
								echo '<td>TBD</td>';
							}				
							
							if (isset($row['DN'])) {
								echo '<td>'.$row['DN'].'</td>';
							} else {
								if (isset($row['BPO'])) {
									echo '<td>'.$row['BPO'].'</td>';
								} else {
									echo '<td>TBD</td>';
								}
							}
							
							if (isset($row['PLO'])) {
								echo '<td>'.$row['PLO'].'</td>';
							} else {
								echo '<td>TBD</td>';
							}
							// Is Fe Order?
							if (isset($row['FEFlag'])) {
								if ($row['FEFlag'] == 1) {
									echo '<td>Y</td>';		
								} else {
									echo '<td>N</td>';
								}
							} else {
								echo '<td>TBD</td>';
							}
							
							if (isset($row['Complexity'])) {
								echo '<td>'.$row['Complexity'].'</td>';
							} else {
								echo '<td>TBD</td>';
							}
							
							if (isset($row['Family'])) {
								echo '<td>'.$row['Family'].'</td>';
							} else {
								echo '<td>TBD</td>';
							}
							
							if (isset($row['Model'])) {
								echo '<td>'.$row['Model'].'</td>';
							} else {
								echo '<td>TBD</td>';
							}
							
							if (isset($row['ShipRef'])) {
								echo '<td>'.$row['ShipRef'].'</td>';
							} else {
								echo '<td>TBD</td>';
							}
							
							if (isset($row['BirthDate'])) {
								echo '<td>'.$row['BirthDate'].'</td>';
							} else {
								echo '<td>TBD</td>';
							}
							// determine who's responsible
							if ($dev_a[trim($row['PLO'])] > 0 && $dev_a[trim($row['PLO'])] == $Max_dev[trim($row['PLO'])]) {
								echo '<td bgcolor="#FFFF99">'.round($dev_a[trim($row['PLO'])],1).'</td>';
							} else {
								echo '<td>'.$dev_a[trim($row['PLO'])].'</td>';
							}
							
							if (isset($row['WHUpdateTime'])) {
								echo '<td>'.$row['WHUpdateTime'].'</td>';
							} else {
								echo '<td>TBD</td>';
							}
							
							if ($dev_b[trim($row['PLO'])] > 0 && $dev_b[trim($row['PLO'])] == $Max_dev[trim($row['PLO'])]) {
								echo '<td bgcolor="#FFFF99">'.round($dev_b[trim($row['PLO'])],1).'</td>';
							} else {
								echo '<td>'.$dev_b[trim($row['PLO'])].'</td>';
							}
							
							if (isset($row['HandoverTime'])) {
								echo '<td>'.$row['HandoverTime'].'</td>';
							} else {
								echo '<td>TBD</td>';
							}
							
							if ($dev_c[trim($row['PLO'])] > 0 && $dev_c[trim($row['PLO'])] == $Max_dev[trim($row['PLO'])]) {
								echo '<td bgcolor="#FFFF99">'.$dev_c[trim($row['PLO'])].'</td>';
							} else {
								echo '<td>'.round($dev_c[trim($row['PLO'])],1).'</td>';
							}
							
							if (isset($row['PGITime'])) {
								echo '<td>'.$row['PGITime'].'</td>';
							} else {
								echo '<td>TBD</td>';
							}
							
							// if (isset($Max_PCT[trim($row['PLO'])])) {
								// echo '<td>'.$Max_PCT[trim($row['PLO'])].'</td>';
							// } else {
								// echo '<td>TBD</td>';
							// }
							if (isset($row['PCTTarget'])) {
								echo '<td>'.($row['PCTTarget']*24).'</td>';
							} else {
								echo '<td>TBD</td>';
							}							
							
							if (isset($dev[trim($row['PLO'])])) {
								echo '<td>'.round($dev[trim($row['PLO'])],1).'</td>';
							} else {
								echo '<td>TBD</td>';
							}
							
							if ($dev[trim($row['PLO'])] > 0 && !stristr($row['SO'], '4251')) {
								if ($dev_b[trim($row['PLO'])] == $Max_dev[trim($row['PLO'])]) {
									if (isset($row['Comment']) && trim($row['Comment']) <> '') {
										echo "<td bgcolor=\"#FFFF99\"><a target = '_blank' href=\"./comment.php?PLO=".$row['PLO']."&comment=".$row['Comment']."&comment2=".$row['Comment3']."&comment3=".$row['Comment2']."\">".$row['Comment']." - ".$row['Comment2']."</a></td>";
										echo "<td bgcolor=\"#FFFF99\">".$row['Comment3']."</td>";
									} else {
										echo "<td bgcolor=\"#FFFF99\"><a target = '_blank' href=\"./comment.php?PLO=".$row['PLO']."\">->Add<-</a></td>";
										echo "<td bgcolor=\"#FFFF99\"></td>";
									}
								} elseif ($dev_a[trim($row['PLO'])] == $Max_dev[trim($row['PLO'])]) {
									if (isset($row['Comment']) && trim($row['Comment']) <> '') {
										echo "<td bgcolor=\"#FFFF99\"><a target = '_blank' href=\"../PCTWHView/comment.php?PLO=".$row['PLO']."&comment=".$row['Comment']."\">".$row['Comment']."</a></td>";
										echo "<td bgcolor=\"#FFFF99\">".$row['Comment3']."</td>";
									} elseif (isset($row['Comment7']) && trim($row['Comment7']) <> '') {
										if ($row['Comment7'] <> 'short') {
											echo "<td bgcolor=\"#FFC7CE\"><a target = '_blank' href=\"../PCTWHView/comment.php?PLO=".$row['PLO']."&comment=".$row['Comment7']."&comment2=".$row['Comment7_2']."\">".$row['Comment7']."</a></td>";
											echo "<td bgcolor=\"#FFFF99\">".$row['Comment7_2']."</td>";
										} else {
											echo "<td bgcolor=\"#FFC7CE\"><a target = '_blank' href=\"../PCTWHView/comment.php?PLO=".$row['PLO']."&comment=".$row['Comment7']."&comment2=".$row['Comment8']."&eta=".$row['Comment9']."\">".$row['Comment7']."</a></td>";
											echo "<td bgcolor=\"#FFFF99\">".$row['Comment8']." | ".$row['Comment9']."</td>";
										}
									} else {
										echo "<td bgcolor=\"#FFFF99\"><a target = '_blank' href=\"../PCTWHView/comment.php?PLO=".$row['PLO']."\">->Add<-</a></td>";
										echo "<td bgcolor=\"#FFFF99\"></td>";
									}
								} elseif ($dev_c[trim($row['PLO'])] == $Max_dev[trim($row['PLO'])]) {
									if (isset($row['Comment']) && trim($row['Comment']) <> '') {
										echo "<td bgcolor=\"#FFFF99\"><a target = '_blank' href=\"../PCTWHView/comment3.php?PLO=".$row['PLO']."&comment=".$row['Comment']."&comment2=".$row['Comment3']."&comment3=".$row['Comment2']."\">".$row['Comment']." - ".$row['Comment2']."</a></td>";
										echo "<td bgcolor=\"#FFFF99\">".$row['Comment3']."</td>";
									} elseif (isset($row['Comment10']) && trim($row['Comment10']) <> '') {
										echo "<td bgcolor=\"#FFFF99\"><a target = '_blank' href=\"../PCTWHView/comment3.php?PLO=".$row['PLO']."&comment=".$row['Comment10']."&comment2=".$row['Comment12']."&comment3=".$row['Comment11']."\">".$row['Comment10']." - ".$row['Comment11']."</a></td>";
										if (trim($row['Family']) == 'TSG_PSTOPT_PF') {
											echo "<td bgcolor=\"#FFFF99\">".$row['Comment7']." - ".$row['Comment8']."</td>";
										} else {
											echo "<td bgcolor=\"#FFFF99\">".$row['Comment12']."</td>";
										}
									} else {
										echo "<td bgcolor=\"#FFFF99\"><a target = '_blank' href=\"../PCTWHView/comment3.php?PLO=".$row['PLO']."\">->Add<-</a></td>";
										echo "<td bgcolor=\"#FFFF99\"></td>";
									}
								} else {
									echo '<td>N/A</td>';
									echo '<td>N/A</td>';
								}
							} else {
									echo '<td>N/A</td>';
									echo '<td>N/A</td>';
							}
							
							if ($dev[trim($row['PLO'])] > 0 && !stristr($row['SO'], '4251')) {
								if ($dev_b[trim($row['PLO'])] > 0 && $dev_b[trim($row['PLO'])] == $Max_dev[trim($row['PLO'])]) {
									echo '<td>'.$row['Owner'].'</td>';
								} else {
									echo '<td>WH</td>';
								}
							} else {
								echo '<td>N/A</td>';
							}
							
							// if (isset($row['EgorasFail'])) {
								// if ($row['EgorasFail'] == 1) {
									// echo "<td bgcolor=\"#FFFF99\"><a target = '_blank' href=\"./egoras.php?PLO=".$row['PLO']."&comment=".$row['EgorasFail']."&comment2=".$row['EgorasFailDetail']."\">Yes</a></td>";
									// echo "<td bgcolor=\"#FFFF99\">".$row['EgorasFailDetail']."</td>";		
								// } else {
									// echo "<td><a target = '_blank' href=\"./egoras.php?PLO=".$row['PLO']."\">->Add<-</a></td>";
									// echo "<td></td>";
								// }
							// } else {
								// echo "<td><a target = '_blank' href=\"./egoras.php?PLO=".$row['PLO']."\">->Add<-</a></td>";
								// echo "<td></td>";
							// }
							
							if (isset($row['TDNo'])) {
								echo "<td bgcolor=\"#FFFF99\"><a target = '_blank' href=\"./td.php?PLO=".$row['PLO']."&comment=".$row['TDNo']."\">".$row['TDNo']."</a></td>";						
								// TD TAT
								if (isset($row['logtime'])) {
									if (isset($row['closetime'])) {var_dump($row['closetime']);
										$TAT_TD[trim($row['PLO'])] = ol_line($row['logtime'], $row['closetime']);
										if ($TAT_TD[trim($row['PLO'])] <= $Target_TAT_TD) {
											echo '<td bgcolor=\'#C6EFCE\'>'.$TAT_TD[trim($row['PLO'])].'</td>';
										} else {
											echo '<td bgcolor=\'#FFC7CE\'>'.$TAT_TD[trim($row['PLO'])].'</td>';
										}
									} else {
										$GAP_TD[trim($row['PLO'])] = ol_line($row['logtime'], date('Y-m-d H:i:s'));
										if ($GAP_TD[trim($row['PLO'])] <= $Target_TAT_TD) {
											echo '<td bgcolor=\'#C6EFCE\'>剩余: '.($Target_TAT_TD - $GAP_TD[trim($row['PLO'])]).'</td>';
										} else {
											echo '<td bgcolor=\'#FFC7CE\'>超时: '.($GAP_TD[trim($row['PLO'])] - $Target_TAT_TD).'</td>';
										}
									}
								} else {
									echo '<td>TBD</td>';
								}
							} else {
								echo "<td><a target = '_blank' href=\"./td.php?PLO=".$row['PLO']."\">->Add<-</a></td>";
								echo "<td>N/A</td>";
							}
						echo '</tr>';
				}
				echo '</table>';
				echo '</td>';	
				echo '<td valign="top">';
				//---------print num table
				echo '<table border="0" cellpadding=2 style="color:navy;font-weight:bold;font-family:Calibri;font-size:9pt;">';
				
				foreach($Priority as $k => $v) {
					if ($k == 'Failed') {
						echo '<tr bgcolor="#FFC7CE">';
					}
					else {
						echo '<tr>';
					}
							echo '<td>'.$k.'</td>';
							echo '<td>'.$v.'</td>';
					echo '</tr>';
				}
				echo '</table>';
				echo '</td>';
			echo '</tr>';
		echo '</table>';
				// echo '<p >';
				
				echo '<script src="tsorter.min.js"></script>';
				echo '<script src="fnExcelReport.js"></script>';
		}
		echo '</body>';
	echo '</html>';

	mssql_free_result($data2);
	mssql_free_result($data);
	mssql_close($dbc);


?>