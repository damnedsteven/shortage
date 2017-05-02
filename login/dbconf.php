<?php
//DATABASE CONNECTION VARIABLES
$host = "16.187.224.28"; // Host name
$username = "root"; // Mysql username
$password = "mysql"; // Mysql password
$db_name = "shortage"; // Database name

//DO NOT CHANGE BELOW THIS LINE UNLESS YOU CHANGE THE NAMES OF THE MEMBERS AND LOGINATTEMPTS TABLES

$tbl_prefix = ""; //***PLANNED FEATURE, LEAVE VALUE BLANK FOR NOW*** Prefix for all database tables
$tbl_members = $tbl_prefix."members";
$tbl_attempts = $tbl_prefix."loginAttempts";
