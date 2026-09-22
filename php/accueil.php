<?php
require "bdd.php";

// Protection : si pas connecté, on renvoie vers la page de connexion
if (!isset($_SESSION["id_utilisateur"])) {
    header("Location: connexion.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil - 3ilFootbook</title>
</head>
<body>
    <?php include "navbar.php"; ?>

    <div style="padding:30px;">
        <h1>Bienvenue, <?= htmlspecialchars($_SESSION["prenom"]) ?> !</h1>
        <p>Tu es connecté en tant que <strong><?= htmlspecialchars($_SESSION["role"]) ?></strong>.</p>

        <p>C'est ici que viendra la liste des terrains et des réservations.</p>
    </div>
</body>
</html>