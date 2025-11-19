<?php
// db.php
// Basic MySQL connection for the lab

$DB_HOST = '127.0.0.1';   // or 'localhost'
$DB_PORT = 3306;         // you used localhost:33060 in CLI
$DB_USER = 'root';        // change if needed
$DB_PASS = 'root';            // change if needed
$DB_NAME = 'sqli_lab';

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);

if ($mysqli->connect_error) {
    die('Database connection failed: ' . $mysqli->connect_error);
}

// Optional: set charset
$mysqli->set_charset('utf8mb4');
