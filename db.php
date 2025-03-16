<?php
// Database connection settings
$servername = "localhost"; // or "127.0.0.1"
$username = "root";        // default XAMPP MySQL username
$password = "";            // default XAMPP MySQL password
$dbname = "droguerie_db";  // Your database name

// Create the connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check if the connection is successful
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
