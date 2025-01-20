<?php
// Database connection variables
$host = "localhost"; // Replace with your database host
$username = "root";  // Replace with your database username
$password = "";      // Replace with your database password
$database = "store"; // Replace with your database name

// Create a connection to the database
$connect_android = mysqli_connect($host, $username, $password, $database);

// Check if the connection was successful
if (!$connect_android) {
    die("Connection failed: " . mysqli_connect_error());
}

// Optionally, set the character set for the connection
mysqli_set_charset($connect_android, "utf8");

// Now, the $connect_android variable can be included in other files
