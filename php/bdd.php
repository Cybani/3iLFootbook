<?php
// db.php - Connexion à la base de données PostgreSQL
 
// -- À adapter selon ta configuration --
$host = "localhost";
$port = "5432";
$dbname = "3iLFootbook"; // remplace par le nom de ta base
$user = "postgres";      // remplace par ton utilisateur
$password = "postgres";  // remplace par ton mot de passe
 
try {
    $pdo = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname",
        $user,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
 
// On démarre la session ici pour pouvoir l'utiliser partout
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
