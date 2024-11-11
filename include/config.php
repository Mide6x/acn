<?php
session_start();
set_time_limit(0);
ini_set('memory_limit', '-1'); //set the memory limit not to time out
ini_set('max_execution_time', 100); //set the maximum execution time

	$servername = "localhost";
	$username = "root";
	$password = "";
	$dbname = "hititdatadump";
		//  $servername = "localhost";
		//  $username = "maritimefrmlimit_nscfrmlimited";
		//  $password = "^}9{{ey+WnH0";
		//  $dbname = "maritimefrmlimit_shipping"; 

	try{
		//$con = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4",$username,"");
		$con = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4",$username,$password);
		$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
	}
	catch(PDOException $e){
		//echo $this->ExceptionLog($e->getMessage());
		die($e);
	}
	
//import all classes
include_once 'classes.php';

	
?>