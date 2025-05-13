<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "student_club_system";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if ($conn) {
    echo "Connected successfully";
} else {
    echo "Database connection failed: " . mysqli_connect_error();
}

?>