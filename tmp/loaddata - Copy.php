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
// $grid->addColumn('id', 'ID', 'integer', NULL, false);
$grid->addColumn('publish', 'Publish Time', 'datetime', NULL, false); 
$grid->addColumn('pn_type', 'Passthrough', 'boolean', NULL, false);  
$grid->addColumn('orderdate', 'Order Date', 'date', NULL, false);
$grid->addColumn('bkpl', 'BKPL Time', 'datetime', NULL, false); 
$grid->addColumn('rtp', 'RTP Time', 'datetime', NULL, false); 
$grid->addColumn('so', 'Sales Order', 'string', NULL, false); 
$grid->addColumn('so_item', 'Sales Order Item', 'int', NULL, false);
$grid->addColumn('product', 'Product', 'string', NULL, false);
$grid->addColumn('product_pl', 'Product PL', 'string', NULL, false);
$grid->addColumn('bpo', 'BPO', 'string', NULL, false);
$grid->addColumn('plo', 'PLO', 'string', NULL, false);
$grid->addColumn('pn', 'Material Part No.', 'string', NULL, false); 
$grid->addColumn('ctrl_id', 'Ctrl ID', 'string', NULL, false);  
$grid->addColumn('sales_area', 'Sales Area', 'string', NULL, false); 
$grid->addColumn('shortage_qty', 'Shortage QTY', 'integer', NULL, false);
$grid->addColumn('required_qty', 'Required QTY', 'integer', NULL, false);
$grid->addColumn('remark_wh', 'Remarks By WH', 'string'); 
$grid->addColumn('status', 'Status', 'string'); 
$grid->addColumn('status_update', 'Status Update', 'string'); 
$grid->addColumn('destination', 'Destination', 'string', array('EMCN', 'Hub')); 
$grid->addColumn('shortage_reason', 'Shortage Reason (Category)', 'string'); 
$grid->addColumn('shortage_reason_detail', 'Shortage Reason (Comments)', 'string'); 
$grid->addColumn('lastupdated', 'Updated', 'datetime', NULL, false); 
$grid->addColumn('action', 'Action', 'html', NULL, false, 'id');
// $grid->addColumn('height', 'Height', 'float');  
/* The column id_country and id_continent will show a list of all available countries and continents. So, we select all rows from the tables */
// $grid->addColumn('id_continent', 'Continent', 'string' , fetch_pairs($mysqli,'SELECT id, name FROM continent'),true);  
// $grid->addColumn('id_country', 'Country', 'string', fetch_pairs($mysqli,'SELECT id, name FROM country'),true );  
// $grid->addColumn('email', 'Email', 'email');                                                

$mydb_tablename = (isset($_GET['db_tablename'])) ? stripslashes($_GET['db_tablename']) : 'master';
                                                                       
$result = $mysqli->query('SELECT *, date_format(orderdate, "%d/%m/%Y") as orderdate, date_format(lastupdated, "%b %d %Y %h:%i %p") as lastupdated FROM '.$mydb_tablename );
$mysqli->close();

// send data to the browser
$grid->renderJSON($result);

