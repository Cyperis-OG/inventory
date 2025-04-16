<?php
// Database connection
$servername = "localhost";
$username = "freeman";
$password = "4T1vdYG34c";
$dbname = "095";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
