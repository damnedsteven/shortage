<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<?php
	//Refresh every 5 mins
	// $page = $_SERVER['PHP_SELF'];
	// $sec = "300";
	// header("Refresh: $sec; url=$page");
	//Set correct local time
	date_default_timezone_set('Asia/Shanghai'); 
	error_reporting(E_ALL ^ E_NOTICE);
	
	echo '<title>ESSN Material Shortage - ' . $page_title . '</title>';
?>

  <link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>

<a href="index.php"><img src="images/HPE_log_left_wht.png" alt="HPE Logo" align="right" border=0 /></a>

<?php
  echo '<h3>ESSN Material Shortage - ' . $page_title . '</h3>';
?>
