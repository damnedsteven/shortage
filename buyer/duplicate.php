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
if ($stmt = $mysqli->prepare("
		INSERT INTO ".$tablename." (pn, is_copy, ctrl_id, buyer_name, shortage_qty, earliest_bkpl, judge_supply, status) 
			SELECT pn, 1+(
				SELECT max(is_copy)
				FROM ".$tablename."
				WHERE pn = (SELECT pn FROM ".$tablename." WHERE id = ?)
			), ctrl_id, buyer_name, shortage_qty, earliest_bkpl, judge_supply, 1 FROM ".$tablename." WHERE id = ?
		")) {
				$stmt->bind_param("si", $id, $id);
				$return = $stmt->execute();
				$stmt->close();
			}             
$mysqli->close();        

echo $return ? "ok" : "error";

      

