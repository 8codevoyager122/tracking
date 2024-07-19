<?php
session_start();

// Unset all of the session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Redirect to the login page
header("Location: /login.php"); // Adjust the path as per your login page location
exit;
?>
