<?php
session_start();

$host = "localhost";
$username = "root";
$password = "";
$dbname = "gym_management";

// User types
define('USER_MEMBER', 'member');
define('USER_TRAINER', 'trainer');
define('USER_ADMIN', 'admin');

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function redirect($url) {
    header("Location: $url");
    exit();
}
?>