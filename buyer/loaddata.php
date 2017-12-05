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
// $grid->addColumn('id', 'ID', 'integer', NULL, false);
// $grid->addColumn('publish', 'Publish Time', 'datetime', NULL, false); 
// $grid->addColumn('pfc', 'PF Category', 'string', array('PassThrough', 'ISS-Server', 'EVA', 'Kitting', 'MM-Kitting'), false);  
// $grid->addColumn('orderday', 'Order Date', 'string', NULL, false); 
// $grid->addColumn('rtp', 'RTP Time', 'datetime', NULL, false); 
// $grid->addColumn('so', 'Sales Order', 'string', NULL, false); 
// $grid->addColumn('so_item', 'Sales Order Item', 'int', NULL, false);
// $grid->addColumn('product', 'Product', 'string', NULL, false);
// $grid->addColumn('product_pl', 'Product PL', 'string', NULL, false);
// $grid->addColumn('bpo', 'BPO', 'string', NULL, false);
// $grid->addColumn('plo', 'PLO', 'string', NULL, false);
$grid->addColumn('is_copy', 'Copy#', 'integer', NULL, false);
$grid->addColumn('pn', 'Part No.            ', 'string', NULL, false); 
$grid->addColumn('ctrl_id', 'Ctrl ID', 'string', NULL, false);  
$grid->addColumn('buyer_name', 'Buyer        ', 'string', NULL, false); 
$grid->addColumn('shortage_qty', 'TTL-S', 'double(, 0, dot, comma, 1)', NULL, false);
$grid->addColumn('pline_shortage_qty', 'S-RAW', 'double(, 0, dot, comma, 1)', NULL, false);
$grid->addColumn('passthru_shortage_qty', 'S-OPT', 'double(, 0, dot, comma, 1)', NULL, false);
$grid->addColumn('earliest_bkpl', 'Earliest BKPL Time              ', 'datetime', NULL, false);
$grid->addColumn('arrival_qty', 'Supp.Q', 'double(, 0, dot, comma, 1)');
$grid->addColumn('eta', 'ETA', 'date');
$grid->addColumn('slot', 'Slot', 'string', array('morning', 'afternoon', 'night'));
$grid->addColumn('remark', 'Remark      ', 'string'); 
$grid->addColumn('id_carrier', 'Carrier            ', 'string', fetch_pairs($mysqli,'SELECT id, name FROM carrier'), true); 
$grid->addColumn('judge_supply', 'Judge Supply?', 'string', NULL, false); 
$grid->addColumn('id_shortage_reason', 'Shortage Reason (Category)', 'string', fetch_pairs($mysqli,'SELECT id, name FROM shortage_reason'), true); 
$grid->addColumn('shortage_reason_detail', 'Shortage Reason (Comments)', 'string'); 
$grid->addColumn('bill_number', '运单号     ', 'string'); 
$grid->addColumn('lastupdated', 'Updated                             ', 'datetime', NULL, false); 
$grid->addColumn('action', 'Action', 'html', NULL, false, 'id');
// $grid->addColumn('height', 'Height', 'float');  
/* The column id_country and id_continent will show a list of all available countries and continents. So, we select all rows from the tables */
// $grid->addColumn('id_continent', 'Continent', 'string' , fetch_pairs($mysqli,'SELECT id, name FROM continent'),true);  
// $grid->addColumn('id_country', 'Country', 'string', fetch_pairs($mysqli,'SELECT id, name FROM country'),true );  
// $grid->addColumn('email', 'Email', 'email');                                                

$mydb_tablename = (isset($_GET['db_tablename'])) ? stripslashes($_GET['db_tablename']) : 'pn';
                                                                       
$result = $mysqli->query('SELECT *, date_format(lastupdated, "%b %d %Y %h:%i %p") as lastupdated FROM '.$mydb_tablename.' WHERE (status=1 OR is_copy = -1) AND received IS NULL ORDER BY pn' );
$mysqli->close();

// send data to the browser
$grid->renderJSON($result);

