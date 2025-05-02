<?php
// Load environment variables
$env = parse_ini_file('.env');

// Database connection
$host = $env['DB_HOST'];
$username = $env['DB_USERNAME'];
$password = $env['DB_PASSWORD'];
$database = $env['DB_DATABASE'];
$port = $env['DB_PORT'];
// $s3_bucket = $env['S3_BUCKET'];

$conn = new mysqli($host, $username, $password, $database, $port);
// echo "Connected successfully";
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}