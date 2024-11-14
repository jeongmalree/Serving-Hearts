<?php

// Allows encryption of important database passwords
require 'constants.php';

// Get the DATABASE_URL from Heroku environment variables
$databaseUrl = getenv('DATABASE_URL');

// Parse the DATABASE_URL to extract the components
$parsedUrl = parse_url($databaseUrl);

$host = $parsedUrl['host'];
$port = $parsedUrl['port'];
$username = $parsedUrl['user'];
$password = $parsedUrl['pass'];
$dbname = ltrim($parsedUrl['path'], '/');

// Create a connection to the MySQL database
$conn = new mysqli($host, $username, $password, $dbname, $port);

// Check the connection
if ($conn->connect_error) {
    die('Database error: ' . $conn->connect_error);
}

echo "Database connected successfully!";
?>
