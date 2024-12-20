<?php
session_start();
require_once 'user_config.php';

// Database configuration
$host = "localhost";
$username = "root";
$password = "";
$database = "hrdb";

try {
	$con = new PDO("mysql:host=$host;dbname=$database", $username, $password);
	$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
	error_log("Connection failed: " . $e->getMessage());
	die("Connection failed: " . $e->getMessage());
}

// Initialize user session with hardcoded values
initializeUserSession();

include_once 'classes.php';
