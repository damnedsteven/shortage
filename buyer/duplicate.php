<?php
 
/*
 * 
 * http://editablegrid.net
 *
 * Copyright (c) 2011 Webismymind SPRL
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://editablegrid.net/license
 */
      
require_once('config.php');         

// Database connection                                   
$mysqli = mysqli_init();
$mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);
$mysqli->real_connect($config['db_host'],$config['db_user'],$config['db_password'],$config['db_name']); 

// Get all parameter provided by the javascript
$id = $mysqli->real_escape_string(strip_tags($_POST['id']));
$tablename = $mysqli->real_escape_string(strip_tags($_POST['tablename']));

// This very generic. So this script can be used to update several tables.
$return=false;
// if ($stmt = $mysqli->prepare("INSERT INTO ".$tablename." (pn, ctrl_id, buyer_name, shortage_qty, eta, status) SELECT CONCAT(pn, '_c'), ctrl_id, buyer_name, shortage_qty, eta, 1 FROM ".$tablename." WHERE id = ?")) {
if ($stmt = $mysqli->prepare("INSERT INTO ".$tablename." (pn, is_copy, ctrl_id, buyer_name, shortage_qty, status) SELECT pn, is_copy+1, ctrl_id, buyer_name, shortage_qty, 1 FROM ".$tablename." WHERE id = ?")) {
	$stmt->bind_param("i", $id);
	$return = $stmt->execute();
	$stmt->close();
}             
$mysqli->close();        

echo $return ? "ok" : "error";

      

