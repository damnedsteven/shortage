<?php

//and also consider to check if the file exists as with the other guy suggested.
$dir = 'uploads/';
$file = $_GET['file']; //get the filename

unlink($dir.$file); //delete it

// if (!unlink($dir.$file))
  // {
  // echo ("Error deleting $file");
  // }
// else
  // {
  // echo ("Deleted $file");
  // }

header('Location: ' . $_SERVER['HTTP_REFERER']); //redirect back to the other page

?>