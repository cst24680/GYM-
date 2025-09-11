<?php
session_start(); // Start the session

// Destroy all session variables
session_unset();
session_destroy();

// Redirect to login page (or homepage)
header("Location: index.php");
exit();
?>
