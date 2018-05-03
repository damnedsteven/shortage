<?php
//---------------------------------
	// Insert the page header
	$page_title = 'Exporter';
	
	require_once('header.php');
	
	require_once('navmenu.php');
	
	require_once('connectvars.php');
	
//---------------------------------------------------------------------------------------------------------------------------------------------------

	echo '<div class="input">';
	
		echo '<form method="post" action="'.$_SERVER['PHP_SELF'].'">';
			//search by 
			echo '<select name="identity" id="identity">';
				echo '<option value="plo" selected>PLO</option>';
				echo '<option value="bpo">BPO</option>';
				echo '<option value="pn">Meterial Part No.</option>';
				echo '<option value="c.name">Carrier</option>';
				echo '<option value="s.name">Shortage Reason</option>';
			echo '</select> &nbsp';
			
			if (isset($_POST['identity'])) {
				echo '<script type="text/javascript">';
					echo 'document.getElementById(\'identity\').value = "'.$_POST['identity'].'"';
				echo '</script>';
			}
			
			echo '<input id="entry" name="entry" value="'.htmlspecialchars($_POST['entry']).'"/> &nbsp';
			
			echo '&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp';

			// echo '<input type="checkbox" name="pgiflag" id="pgiflag" value="1"'; if(isset($_POST['pgiflag'])) echo "checked='checked'"; echo '> Show PGIed Only &nbsp';
			
			// echo '&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp';
			
			//select time base
			echo '<select name="base" id="base">';
				echo '<option value="publish">Publish Time</option>';
				echo '<option value="bkpl">BKPL Time</option>';
				echo '<option value="received" selected>抵达时间</option>';
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

			echo '&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp';

			echo '<iframe id="txtArea1" style="display:none"></iframe>';
			echo '<button type="button" id="btnExport" onclick="fnExcelReport();"> EXPORT </button>';
		echo '</form>';
	echo '</div>';
	
	echo '</br>';
	
	// Get post data
	$submit = $_POST['submit'];
	$startdate = $_POST['startdate'];
	$enddate = $_POST['enddate'];
	$base = $_POST['base'];
	// $pgiflag = $_POST['pgiflag'];
	$identity = $_POST['identity'];
	// enable multiple items search
	if (in_array($identity, array('plo', 'bpo')) && !empty($_POST['entry'])) {
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

	// Connect to Shortage DB	
	$dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME) or die("connect db error"); 
	mysqli_set_charset($dbc,"utf8");
	
		$query = '  
					SELECT m.*, date_format(orderdate, "%d/%m/%Y") as orderdate, date_format(m.lastupdated, "%b %d %Y %h:%i %p") as lastupdated, c.name carrier_name, s.name shortage_reason_name
					FROM master m 
					LEFT JOIN 
					carrier c
					ON m.id_carrier=c.id
					LEFT JOIN
					shortage_reason s
					ON m.id_shortage_reason=s.id
					WHERE 
					received IS NOT NULL 
					AND
					';
	if (isset($submit)) {
		if (isset($entry) && !empty($entry)) {
			if (in_array($identity, array('plo','bpo'))) {
				$query .=" {$identity} IN ({$entry})
				";
			}  else {
				$query .=" {$identity} LIKE '%{$entry}%'
				";
			}
		} else {
			if (isset($base) && isset($startdate) && isset($enddate)) {
				$query .=" {$base} BETWEEN '{$startdate}' and '{$enddate}'
						   ORDER BY
						   {$base} DESC 
				"; 
			}
		}		
	} else {
		$query .=" received >= NOW() - INTERVAL 1 DAY
				   ORDER BY
				   received DESC
		";			
	}	

// var_dump($query);	
	// Show Table
	if (true) {
	// if (!empty($entry)) {		
		// ------print data table
		echo '<table id="major" border="1" cellpadding=2 style="color:navy;font-weight:bold;font-family:Calibri;font-size:9pt;">';
		echo '<thead>';
		echo '<tr bgcolor=navy style=color:white>';
		
		  echo '<th>Item No.</th>';
		  echo '<th>Publish Time</th>';
		  echo '<th>PF Category</th>';
		  echo '<th>Order Date</th>';
		  echo '<th>BKPL Time</th>';
		  echo '<th>Sales Order</th>';
		  echo '<th data-tsorter="numeric">Sales Order Item</th>';	
		  echo '<th>Product</th>';
		  echo '<th>Product PL</th>';
		  echo '<th>BPO</th>';
		  echo '<th>PLO</th>';
		  echo '<th>Material Part No.</th>';
		  echo '<th>Ctrl ID</th>';
		  echo '<th>Sales Area</th>';
		  echo '<th data-tsorter="numeric">Shortage QTY</th>';
		  echo '<th data-tsorter="numeric">Required QTY</th>';
		  echo '<th data-tsorter="numeric">Supp.Q</th>';
		  echo '<th>ETA</th>';
		  echo '<th>Remarks</th>';
		  echo '<th>Carrier</th>';
		  echo '<th>Judge Supply?</th>';
		  echo '<th>Shortage Reason (Category)</th>';
		  echo '<th>Shortage Reason (Comments)</th>';
		  echo '<th>运单号</th>';
		  echo '<th>实际送货日期</th>';
		  echo '<th>晚送原因</th>';
		  echo '<th>到达车辆信息</th>';
		  echo '<th>抵达时间</th>';
		  echo '<th>自动抵达时间</th>';
		  echo '<th>Updated</th>';
		echo '</tr>';
		echo '</thead>';
		
		$data = mysqli_query($dbc, $query) or die('search db error ');

		$count = 0;
		
		while ($row = mysqli_fetch_assoc($data)){	
			$count++;
			
			echo '<tr>';		
				echo '<td>'.$count.'</td>';
				if (isset($row['publish'])) {
					echo '<td>'.$row['publish'].'</td>';
				} else {
					echo '<td>TBD</td>';
				}
				if (isset($row['pfc'])) {
					echo '<td>'.$row['pfc'].'</td>';
				} else {
					echo '<td>TBD</td>';
				}
				if (isset($row['orderday'])) {
					echo '<td>'.$row['orderday'].'</td>';
				} else {
					echo '<td>TBD</td>';
				}
				if (isset($row['bkpl'])) {
					echo '<td>'.$row['bkpl'].'</td>';
				} else {
					echo '<td>TBD</td>';
				}
				if (isset($row['so'])) {
					echo '<td>'.$row['so'].'</td>';
				} else {
					echo '<td>TBD</td>';
				}
				if (isset($row['so_item'])) {
					echo '<td>'.$row['so_item'].'</td>';
				} else {
					echo '<td>TBD</td>';
				}
				if (isset($row['product'])) {
					echo '<td>'.$row['product'].'</td>';
				} else {
					echo '<td>TBD</td>';
				}
				if (isset($row['product_pl'])) {
					echo '<td>'.$row['product_pl'].'</td>';
				} else {
					echo '<td>TBD</td>';
				}
				if (isset($row['bpo'])) {
					echo '<td>'.$row['bpo'].'</td>';
				} else {
					echo '<td>TBD</td>';
				}
				if (isset($row['plo'])) {
					echo '<td>'.$row['plo'].'</td>';
				} else {
					echo '<td>TBD</td>';
				}
				if (isset($row['pn'])) {
					echo '<td>'.$row['pn'].'</td>';
				} else {
					echo '<td>TBD</td>';
				}
				if (isset($row['ctrl_id'])) {
					echo '<td>'.$row['ctrl_id'].'</td>';
				} else {
					echo '<td>TBD</td>';
				}
				if (isset($row['sales_area'])) {
					echo '<td>'.$row['sales_area'].'</td>';
				} else {
					echo '<td>TBD</td>';
				}
				if (isset($row['shortage_qty'])) {
					echo '<td>'.$row['shortage_qty'].'</td>';
				} else {
					echo '<td>TBD</td>';
				}
				if (isset($row['required_qty'])) {
					echo '<td>'.$row['required_qty'].'</td>';
				} else {
					echo '<td>TBD</td>';
				}
				if (isset($row['arrival_qty'])) {
					echo '<td>'.$row['arrival_qty'].'</td>';
				} else {
					echo '<td>TBD</td>';
				}
				if (isset($row['eta'])) {
					echo '<td>'.$row['eta'].'</td>';
				} else {
					echo '<td>TBD</td>';
				}
				if (isset($row['remark'])) {
					echo '<td>'.$row['remark'].'</td>';
				} else {
					echo '<td>TBD</td>';
				}
				if (isset($row['carrier_name'])) {
					echo '<td>'.$row['carrier_name'].'</td>';
				} else {
					echo '<td>TBD</td>';
				}
				if (isset($row['judge_supply'])) {
					echo '<td>'.$row['judge_supply'].'</td>';
				} else {
					echo '<td>TBD</td>';
				}
				if (isset($row['shortage_reason_name'])) {
					echo '<td>'.$row['shortage_reason_name'].'</td>';
				} else {
					echo '<td>TBD</td>';
				}
				if (isset($row['shortage_reason_detail'])) {
					echo '<td>'.$row['shortage_reason_detail'].'</td>';
				} else {
					echo '<td>TBD</td>';
				}
				if (isset($row['bill_number'])) {
					echo '<td>'.$row['bill_number'].'</td>';
				} else {
					echo '<td>TBD</td>';
				}
				if (isset($row['delivery'])) {
					echo '<td>'.$row['delivery'].'</td>';
				} else {
					echo '<td>TBD</td>';
				}
				if (isset($row['delay_reason'])) {
					echo '<td>'.$row['delay_reason'].'</td>';
				} else {
					echo '<td>TBD</td>';
				}
				if (isset($row['vehicle_info'])) {
					echo '<td>'.$row['vehicle_info'].'</td>';
				} else {
					echo '<td>TBD</td>';
				}
				if (isset($row['received'])) {
					echo '<td>'.$row['received'].'</td>';
				} else {
					echo '<td>TBD</td>';
				}
				if (isset($row['auto_received'])) {
					echo '<td>'.$row['auto_received'].'</td>';
				} else {
					echo '<td>TBD</td>';
				}
				if (isset($row['lastupdated'])) {
					echo '<td>'.$row['lastupdated'].'</td>';
				} else {
					echo '<td>TBD</td>';
				}

			echo '</tr>';
		}
		echo '</table>';			
				
		echo '<script src="tsorter.min.js"></script>';
		echo '<script src="fnExcelReport.js"></script>';
	
		echo '</body>';
		echo '</html>';
				
		mysqli_free_result($data);
		mysqli_close($dbc);
	}


?>