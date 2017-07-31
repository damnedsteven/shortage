<?php     


/*
 * examples/mysql/loaddata.php
 * 
 * This file is part of EditableGrid.
 * http://editablegrid.net
 *
 * Copyright (c) 2011 Webismymind SPRL
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://editablegrid.net/license
 */
                              


/**
 * This script loads data from the database and returns it to the js
 *
 */
       
require_once('config.php');      
require_once('EditableGrid.php');            

/**
 * fetch_pairs is a simple method that transforms a mysqli_result object in an array.
 * It will be used to generate possible values for some columns.
*/
function fetch_pairs($mysqli,$query){
	if (!($res = $mysqli->query($query)))return FALSE;
	$rows = array();
	while ($row = $res->fetch_assoc()) {
		$first = true;
		$key = $value = null;
		foreach ($row as $val) {
			if ($first) { $key = $val; $first = false; }
			else { $value = $val; break; } 
		}
		$rows[$key] = $value;
	}
	return $rows;
}


// Database connection
$mysqli = mysqli_init();
$mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);
$mysqli->real_connect($config['db_host'],$config['db_user'],$config['db_password'],$config['db_name']); 
                    
// create a new EditableGrid object
$grid = new EditableGrid();

/* 
*  Add columns. The first argument of addColumn is the name of the field in the databse. 
*  The second argument is the label that will be displayed in the header
*/
$grid->addColumn('pn', 'Material Part No.', 'string', NULL, false); 
$grid->addColumn('arrival_qty', 'Arrival QTY', 'double(, 0, dot, comma, 1)', NULL, false);
$grid->addColumn('eta', 'ETA', 'date', NULL, false);
$grid->addColumn('carrier', 'Carrier', 'string', array('KWE-HPE', 'KWE-EXTNL', 'HUB', '新杰', '明德', '迈创', 'Planner-action', '仓库-action', '产线-action', 'Other'), false); 
$grid->addColumn('bill_number', '运单号', 'string', NULL, false); 
$grid->addColumn('delivery', '实际送货日期', 'date');
$grid->addColumn('delay_reason', '晚送原因', 'string');
$grid->addColumn('vehicle_info', '到达车辆信息', 'string');
$grid->addColumn('lastupdated', 'Updated', 'datetime', NULL, false); 
// $grid->addColumn('action', 'Action', 'html', NULL, false, 'id');
// $grid->addColumn('height', 'Height', 'float');  
/* The column id_country and id_continent will show a list of all available countries and continents. So, we select all rows from the tables */
// $grid->addColumn('id_continent', 'Continent', 'string' , fetch_pairs($mysqli,'SELECT id, name FROM continent'),true);  
// $grid->addColumn('id_country', 'Country', 'string', fetch_pairs($mysqli,'SELECT id, name FROM country'),true );  
// $grid->addColumn('email', 'Email', 'email');                                                

$mydb_tablename = (isset($_GET['db_tablename'])) ? stripslashes($_GET['db_tablename']) : 'pn';
                                                                       
$result = $mysqli->query('SELECT *, date_format(lastupdated, "%b %d %Y %h:%i %p") as lastupdated FROM '.$mydb_tablename.' WHERE carrier<="1" AND received IS NULL AND eta<=ADDDATE(CURDATE(), 1)' );
$mysqli->close();

// send data to the browser
$grid->renderJSON($result);

