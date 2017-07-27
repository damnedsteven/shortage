<!DOCTYPE html>
<html>
	<head>
		<title>运单号 - 录入</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	</head>
	
	<body>
	
			
	<form id="form" action="" method="post" onkeydown="if(event.keyCode==13){return false;}">
	<!--	
	<form id="form" action="" method="post">
	
		<select name="pl" id="pl">
			<option value="" selected>选择生产车间</option>
			<option value="SY">SY</option>
			<option value="LM">LM</option>
			<option value="SI">SI</option>
			<option value="MM">MM</option>
			<option value="OP">OP</option>
		</select>
	-->	
		<input onkeydown="enterSumbit()" id="selectsrcid" type="text" class="text"/>
		
		<button class="default" id="enter" type="button" onclick="srcToDest('selectsrcid','selectdestid'); ClearFields();"> 输入运单号 </button>
		
		<br>
		
		<select name="bill_arr[]" multiple="multiple" size="30" style="width=200px" id="selectdestid">   
    
		</select>

		<button id="delete" type="button" onclick="javascript:destToSrc('selectdestid')"> >> </button>
	
		<br/><br/> 请确认欲提交的运单号处于高亮选中状态 （默认所有都选中）！ <br/><br/>	
		
	<button id="submit" type="submit" name="submit"> Submit </button>
<!-- 
	<input type="image" name="submit" src="images/submit.png" height="42" width="84"/>
-->	
	</form>

<script>
    window.onunload = refreshParent;
    function refreshParent() {
        window.opener.location.reload();
    }
</script>

<script>
$(function(){
    $('form').each(function () {
        var thisform = $(this);
        thisform.prepend(thisform.find('button.default').clone().css({
            position: 'absolute',
            left: '-999px',
            top: '-999px',
            height: 0,
            width: 0
        }));
    });
});

function enterSumbit(){  
     var event=arguments.callee.caller.arguments[0]||window.event;//消除浏览器差异  
    if (event.keyCode == 13){  
        srcToDest('selectsrcid','selectdestid');
		ClearFields();
     }  
}   

function ClearFields() {
     document.getElementById("selectsrcid").value = "";
}
	
function srcToDest(srcid,destid) {   
	var optionsObjects=document.getElementById(srcid);   
	var optionsSubObjects=document.getElementById(destid);    
	var optionsvalue=optionsObjects.value;  
	count = optionsSubObjects.length+1;
	var optionstext='#'+count+' - '+optionsObjects.value;   //count
	addoptions(destid,optionstext,optionsvalue)   
}           
         
      //向目标   
function addoptions(objectid,optionstext,optionsvalue) {   
	var optionsSubObjects=document.getElementById(objectid);   
	var hasexist=0;   
	for(var o=0;o<optionsSubObjects.length;o++) {   
		var optionsvalue_sub=optionsSubObjects.options[o].text;   
		optionsSubObjects.options[o].selected = true; // selected by default
		if(optionsvalue_sub==optionstext)   
			hasexist+=1;   
	}   
	if(hasexist==0) {   
		optionsSubObjects.add(new Option(optionstext, optionsvalue));   
		optionsSubObjects.options[o].selected = true; // selected by default
	}   
}   
  
  
//将对象中所选的项删除   
  
function destToSrc(objectid)   
{   
var optionsObjects=document.getElementById(objectid);   
  
for(var o=0;o<optionsObjects.length;o++)   
{   
if(optionsObjects.options[o].selected==true)   
 {   
 var optionsvalue=optionsObjects.options[o].value;   
 var optionstext=optionsObjects.options[o].text;   
      removeoption(objectid,optionstext,optionsvalue)   
 }   
}   
}   
  
//删除单个选项   
function removeoption(objectid,textvalue,optionsvalue)   
{   
var optionsSubObjects=document.getElementById(objectid);   
for(var o=0;o<optionsSubObjects.length;o++)   
{   
 var optionsvalue_sub=optionsSubObjects.options[o].text;   
 if(optionsvalue_sub==textvalue)   
  optionsSubObjects.options.remove(o);    
}   
}   
</script>

<?php
	
	echo "<meta charset=\"UTF-8\">";
	
	require_once('config.php');         

	// Database connection                                   
	$mysqli = mysqli_init();
	$mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);
	$mysqli->real_connect($config['db_host'],$config['db_user'],$config['db_password'],$config['db_name']); 
	
//---------------------------------------------------------------------------------------------------------------------------------------------------

	if (isset($_POST['bill_arr'])) {
		$query = "
			IF OBJECT_ID('dbo.Picklist', 'U') IS NULL
					BEGIN
						CREATE TABLE Picklist
						(
						PLO NVARCHAR(20) NOT NULL,
						Picklist_PL NVARCHAR(20) NULL,
						Picklist_Time SMALLDATETIME NULL,
						Comment7 NVARCHAR(200) NULL,
						Comment8 NVARCHAR(200) NULL,
						Comment9 SMALLDATETIME NULL,	
						Comment10 NVARCHAR(200) NULL,
						Comment11 NVARCHAR(200) NULL,
						Comment12 NVARCHAR(200) NULL						
						);
						CREATE INDEX PicklistIndex
						ON Picklist (PLO);
					END
		";
		
		$n = 0;
		
		$strArr = array();
		
		$PLOArr = array();
		
		foreach ($_POST['bill_arr'] as $v) {
			if (!empty($v) && !empty($_POST['pl'])) {
				$PLO = trim($v);
				
				array_push($PLOArr,$PLO);
				
				array_push($strArr, " IF NOT EXISTS (SELECT PLO FROM Picklist WHERE PLO = '{$PLO}') INSERT INTO Picklist (PLO, Picklist_PL, Picklist_Time) VALUES ('{$PLO}', '{$_POST['pl']}', GETDATE())"); 
				$n++;
			}
		}
		
		$PLOs = "'".implode("','", $PLOArr)."'";
		
		$query .= implode(' ', $strArr);
		
		$query .= " SELECT * FROM Urgent WHERE PLO IN ({$PLOs})";
		
		// Connect to 112 DB
		$dbc = mssql_connect(DB_HOST_112, DB_USER_112, DB_PASSWORD_112) or die("connect db error");	
		mssql_select_db(DB_NAME_112,$dbc) or die('can not open db table');

		$data = mssql_query($query,$dbc) or die('search db error ');
		
		echo $n, ' PLOs submitted.<br />';
		
		if (!mssql_num_rows($data)) {
			echo '<span style="color:#80BFFF">';
			echo "当前录入 PLO 中无特殊订单。 <br>";
		} else {
			echo '<span style="color:#FF0000">';
			echo "当前录入 PLO 中特殊订单及类型如下： <br>";
			
			while ($row = mssql_fetch_assoc($data)){
				echo $row['PLO'], '-', $row['Remark'], '<br />';
			}
		}
		
		mssql_free_result($data);
		mssql_close($dbc);
	}
	
?>


/*
 * 
 * http://editablegrid.net
 *
 * Copyright (c) 2011 Webismymind SPRL
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://editablegrid.net/license
 */
      


// Get all parameter provided by the javascript
$id = $mysqli->real_escape_string(strip_tags($_POST['id']));
$tablename = $mysqli->real_escape_string(strip_tags($_POST['tablename']));

// This very generic. So this script can be used to update several tables.
$return=false;
if ($stmt = $mysqli->prepare("INSERT INTO ".$tablename." (pn, ctrl_id, buyer_name, shortage_qty) SELECT CONCAT(pn, '_copy'), ctrl_id, buyer_name, shortage_qty FROM ".$tablename." WHERE id = ?")) {
	$stmt->bind_param("i", $id);
	$return = $stmt->execute();
	$stmt->close();
}             
$mysqli->close();        

echo $return ? "ok" : "error";

</body>
</html>