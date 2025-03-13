<?php
// Database configuration
$db_server = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "eventmanagementsystemdb";   

// Establish database connection
$conn = mysqli_connect($db_server, $db_user, $db_pass, $db_name);

// Check the connection
if (!$conn) {
    die("<script>alert('Database Connection Failed: " . mysqli_connect_error() . "');</script>");
}
?>
