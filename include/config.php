<?php
set_time_limit(0);
ini_set('memory_limit', '-1');
ini_set('max_execution_time', 100);
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hititdatadump";

try {
	$con = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
	$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
	die($e->getMessage());
}

include_once 'classes.php';
