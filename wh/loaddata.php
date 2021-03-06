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
$mysqli->set_charset('utf8'); 
                    
// create a new EditableGrid object
$grid = new EditableGrid();

/* 
*  Add columns. The first argument of addColumn is the name of the field in the databse. 
*  The second argument is the label that will be displayed in the header
*/
$grid->addColumn('pn', 'Part No.             ', 'string', NULL, false); 
$grid->addColumn('shortage_qty', 'S-QTY', 'double(, 0, dot, comma, 1)', NULL, false);
$grid->addColumn('arrival_qty', 'A-QTY', 'double(, 0, dot, comma, 1)', NULL, false);
$grid->addColumn('eta', 'ETA', 'date', NULL, false);
$grid->addColumn('slot', 'Slot', 'string', array('morning', 'afternoon', 'night'), NULL, false);
// $grid->addColumn('remark', 'Remark', 'string', NULL, false); 
$grid->addColumn('id_carrier', 'Carrier            ', 'string', fetch_pairs($mysqli,'SELECT id, name FROM carrier'), false); 
$grid->addColumn('bill_number', '运单号     ', 'string', NULL, false); 

// $grid->addColumn('delivery', '实际送货日期', 'date', NULL, false); 
// $grid->addColumn('vehicle_info', '到达车辆信息', 'string', NULL, false);
$grid->addColumn('received', '抵达时间', 'date');     
// $grid->addColumn('lastupdated', 'Updated', 'datetime', NULL, false);     
$grid->addColumn('action', 'Action', 'html', NULL, false, 'id');                                       

$mydb_tablename = (isset($_GET['db_tablename'])) ? stripslashes($_GET['db_tablename']) : 'pn';
                                                                       
$result = $mysqli->query('SELECT *, date_format(lastupdated, "%b %d %Y %h:%i %p") as lastupdated FROM '.$mydb_tablename.' WHERE eta IS NOT NULL AND received IS NULL ORDER BY eta' );
$mysqli->close();

// send data to the browser
$grid->renderJSON($result);

