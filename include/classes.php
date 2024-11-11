<?php
//including class file
 //include_once '../class/hititdump.php';
 if (file_exists('../class/revenue.php')) {
    include_once '../class/revenue.php';
} elseif (file_exists('class/revenue.php')) {
    include_once 'class/revenue.php';
} 
 //include_once '../class/revenue.php';
//initializing new instance
//$hititdump = new hititdump($con);
$revenue = new revenue($con);
?>