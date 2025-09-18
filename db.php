<?php
$host = "localhost";
$user = "root";   // default for XAMPP
$pass = "";       // default for XAMPP
$dbname = "gym_db";  // your database name

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
