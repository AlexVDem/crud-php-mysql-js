<?php
// Database configuration
$dbHost     = "localhost";
$dbUsername = "username";
$dbPassword = "userpassword";
$dbName     = "databasename";
$dbPort     = "3306";

// Create database connection
$db = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);
$db -> set_charset("utf8");

// Check connection
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

?>