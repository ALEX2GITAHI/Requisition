
<?php
$servername = "localhost";
$username = "root";  // Adjust based on your database
$password = "";      // Adjust based on your database
$dbname = "requisition_system"; // Adjust with your DB name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
