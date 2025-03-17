<?php
$host = "localhost";   // Change if necessary
$dbname = "droguerie_db";  // Your database name
$username = "root";  // Default XAMPP username
$password = "";  // Default XAMPP password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
