<?php
$host = 'localhost';         // Database host, typically 'localhost' for XAMPP
$db = 'esp32_project';       // Name of your database
$user = 'root';              // Default XAMPP username
$pass = '';                  // Default XAMPP password is empty

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
