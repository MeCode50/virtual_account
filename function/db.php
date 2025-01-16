<?php
// Start output buffering and session
ob_start();

// Centralized function to handle database connections
function connect_database($host, $username, $password, $dbname)
{
    $connection = mysqli_connect($host, $username, $password, $dbname);

    if (!$connection) {
        die("Connection failed: " . mysqli_connect_error());
    }

    return $connection;
}

// Database credentials
$host = "129.121.17.211";
$databases = [
    "main" => ["user" => "jankaraa_dope", "password" => "Sapphirev1234!", "dbname" => "jankaraa_sentinel"],
    "red" => ["user" => "jankaraa_dope", "password" => "Sapphirev1234!", "dbname" => "jankaraa_sentinel"],
    "ios" => ["user" => "jankaraa_dope2", "password" => "Sapphirev1234!", "dbname" => "jankaraa_sentinel_ios"],
    "android" => ["user" => "jankaraa_diva", "password" => "Sapphirev1234!", "dbname" => "jankaraa_sentinel_android"],
    "slam" => ["user" => "jankaraa_slam", "password" => "Sapphirev1234!", "dbname" => "jankaraa_sentinel_slam"],
    "merchant" => ["user" => "jankaraa_merchan", "password" => "Sapphirev1234!", "dbname" => "jankaraa_sentinel_merchant"],
    "devfin" => ["user" => "jankaraa_finance", "password" => "Sapphirev1234!", "dbname" => "jankaraa_devfin"],
    "matrix" => ["user" => "jankaraa_mdata22", "password" => "Sapphirev1234!", "dbname" => "jankaraa_matrix2022"],
    "relay" => ["user" => "jankaraa_sales", "password" => "Sapphirev1234!", "dbname" => "jankaraa_relay"],
    "sentiflex" => ["user" => "jankaraa_sef", "password" => "Sapphirev1234", "dbname" => "jankaraa_sentiflex"],
    "sentiflex_online" => ["user" => "jankaraa_sentiflexonline", "password" => "y]BOxDpNsH01", "dbname" => "jankaraa_sentiflex_online"],
];

// Establish connections
$connections = [];

foreach ($databases as $key => $db) {
    $connections[$key] = connect_database($host, $db['user'], $db['password'], $db['dbname']);
}

// Example utility functions
function row_count($result)
{
    return mysqli_num_rows($result);
}

function escape($string)
{
    global $connections; // Use the main connection or specific connection as needed
    return mysqli_real_escape_string($connections['main'], $string);
}

function query($query, $connectionKey = 'main')
{
    global $connections;
    return mysqli_query($connections[$connectionKey], $query);
}

function confirm($result)
{
    if (!$result) {
        die("Query failed.");
    }
}

function last_id($connectionKey = 'main')
{
    global $connections;
    return mysqli_insert_id($connections[$connectionKey]);
}

function fetch_array($result)
{
    return mysqli_fetch_array($result);
}
