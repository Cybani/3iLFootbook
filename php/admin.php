<?php
require "admin_garde.php";

// Statistiques pour le tableau de bord
$nbUtilisateurs = (int)$pdo->query("SELECT COUNT(*) FROM utilisateur")->fetchColumn();
$nbAdmins       = (int)$pdo->query("SELECT COUNT(*) FROM utilisateur WHERE role = 0")->fetchColumn();
$nbTerrains     = (int)$pdo->query("SELECT COUNT(*) FROM terrain")->fetchColumn();
$nbMaintenance  = (int)$pdo->query("SELECT COUNT(*) FROM terrain WHERE statut_terrain <> 'actif'")->fetchColumn();
$nbResaVenir    = (int)$pdo->query("SELECT COUNT(*) FROM reservation WHERE date_reservation >= CURRENT_DATE")->fetchColumn();

$prenom = $_SESSION["prenom"] ?? "";
$pageActiveAdmin = "dashboard";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tableau de bord — Admin 3iL FootBook</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">
  <style> body { background: var(--gris-clair); } </style>
</head>
<body>

  <?php include __DIR__ . "/admin_navbar.php"; ?>

  <main class="admin-page">
    <div class="admin-top">
      <div>
        <h1 class="admin-title">Tableau de bord</h1>
        <p class="admin-intro">Bienvenue <?= e($prenom) ?> 👋 Voici l'administration de 3iL FootBook.</p>
      </div>
      <a class="back-link" href="../accueil.php">← Voir le site</a>
    </div>

    <!-- Statistiques -->
    <div class="admin-stats">
      <div class="stat-carte">
        <div class="stat-chiffre"><?= $nbUtilisateurs ?></div>
        <div class="stat-label">Utilisateurs<br>(dont <?= $nbAdmins ?> admin<?= $nbAdmins > 1 ? "s" : "" ?>)</div>
      </div>
      <div class="stat-carte">
        <div class="stat-chiffre"><?= $nbTerrains ?></div>
        <div class="stat-label">Terrains</div>
      </div>
      <div class="stat-carte">
        <div class="stat-chiffre"><?= $nbMaintenance ?></div>
        <div class="stat-label">En maintenance</div>
      </div>
      <div class="stat-carte">
        <div class="stat-chiffre"><?= $nbResaVenir ?></div>
        <div class="stat-label">Réservations à venir</div>
      </div>
    </div>

    <!-- Accès aux gestions -->
    <div class="admin-grille">
      <a class="admin-lien-carte" href="gestion_utilisateurs.php">
        <div class="icone">👥</div>
        <h3>Gestion des utilisateurs</h3>
        <p>Consulter les comptes et gérer les rôles (admin / utilisateur).</p>
      </a>
      <a class="admin-lien-carte" href="admin_terrains.php">
        <div class="icone">🏟️</div>
        <h3>Gestion des terrains</h3>
        <p>Ajouter, modifier, supprimer un terrain ou le mettre en maintenance.</p>
      </a>
      <a class="admin-lien-carte" href="admin_reservations.php">
        <div class="icone">📅</div>
        <h3>Toutes les réservations</h3>
        <p>Voir et annuler les réservations de tous les étudiants.</p>
      </a>
    </div>
  </main>

</body>
</html>
