<?php
$host = 'localhost'; // MySQL host
$dbname = ''; // Database name
$username = ''; // Database username
$password = 'track!1A'; // Database password

// Create PDO instance
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database $dbname :" . $e->getMessage());
}
?>
