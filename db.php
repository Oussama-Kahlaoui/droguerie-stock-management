<?php
$host = "localhost";   // Serveur MySQL (généralement localhost)
$dbname = "droguerie_db";  // Nom de ta base de données
$username = "root";  // Nom d'utilisateur MySQL (par défaut root pour XAMPP)
$password = "";  // Mot de passe (vide par défaut pour XAMPP)

try {
    // Connexion PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
