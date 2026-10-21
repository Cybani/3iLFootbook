<?php
require "bdd.php";

// Protection : réservé aux étudiants connectés
if (!isset($_SESSION["id_utilisateur"])) {
    header("Location: connexion.php");
    exit;
}

// La disponibilité se consulte par créneau dans le planning.
$stmt = $pdo->query(
    "SELECT * FROM terrain
     ORDER BY nom ASC"
);
$terrains = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Petite aide : une photo selon le type de surface
// Petite aide : une photo selon le type de surface
function photo_terrain($type, $id) {
    switch (strtolower($type)) {
        case "parquet":
        case "futsal":
            return "../images/apropos-2.jpg"; // salle couverte
        case "gazon":
        case "stabilisé":
            return "../images/accueil-1.jpg"; // grand terrain à 11
        default:
            // Synthétique : on alterne entre deux photos pour varier les cartes
            return $id % 2 === 0 ? "../images/apropos-1.jpg" : "../images/accueil-2.jpg";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nos terrains — 3iL FootBook</title>
  <meta name="description" content="Découvrez les terrains de foot disponibles à Limoges et réservez votre créneau.">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">
  <style>
    /* Photo du terrain en haut de chaque carte */
    .terrain-visuel.photo {
      height: 180px;
      background-color: #1c6b3e;
      background-size: cover;
      background-position: center;
    }
  </style>
</head>
<body>

  <!-- ===================== NAVIGATION ===================== -->
  <input type="checkbox" id="nav-toggle" class="nav-toggle">
  <header class="navbar">
    <a href="accueil.php" class="logo">🏟️ 3iL <span>FootBook</span></a>

    <label for="nav-toggle" class="nav-burger">
      <span></span><span></span><span></span>
    </label>

    <nav class="nav-links">
      <a href="accueil.php">Accueil</a>
      <a href="apropos.php">À propos</a>
      <a href="accueil.php#planning">Planning</a>
      <a href="terrains.php" class="active">Nos terrains</a>
      <a href="reservation.php" class="nav-cta">Réserver</a>
      <a href="profil.php">Profil</a>
      <?php if ((int)($_SESSION["role"] ?? 1) === 0): ?>
        <a href="admin.php">⚙️ Admin</a>
      <?php endif; ?>
      <a href="deconnexion.php">Déconnexion</a>
    </nav>
  </header>

  <!-- ===================== EN-TÊTE ===================== -->
  <section class="entete-page">
    <span class="sur-titre">Terrains à Limoges</span>
    <h1>Nos terrains</h1>
    <p>Choisissez un terrain de foot sur le campus et dans la ville de Limoges, puis réservez votre créneau en quelques clics.</p>
  </section>

  <!-- ===================== GRILLE DES TERRAINS ===================== -->
  <section class="section">
    <div class="grille-terrains">
      <?php foreach ($terrains as $t): ?>
        <article class="carte-terrain">
          <div class="terrain-visuel photo"
               style="background-image: url('<?= htmlspecialchars(photo_terrain($t["type_surface"], (int)$t["id_terrain"])) ?>');">
            <?php if (($t["statut_terrain"] ?? "actif") === "maintenance"): ?>
              <span class="badge badge-maintenance">En maintenance</span>
            <?php else: ?>
              <span class="badge badge-actif">Actif</span>
            <?php endif; ?>
          </div>

          <div class="terrain-corps">
            <h3><?= htmlspecialchars($t["nom"]) ?></h3>
            <p class="terrain-quartier">📍 <?= htmlspecialchars($t["quartier"] ?? "Limoges") ?></p>
            <p class="terrain-desc"><?= htmlspecialchars($t["description"] ?? "") ?></p>

            <div class="terrain-meta">
              <span class="puce"><?= htmlspecialchars($t["type_surface"]) ?></span>
              <span class="puce"><?= (int)$t["capacite"] ?> joueurs</span>
            </div>

            <a href="accueil.php?terrain=<?= (int)$t["id_terrain"] ?>#planning" class="btn-terrain">Voir le planning</a>
            <?php if (($t["statut_terrain"] ?? "actif") === "maintenance"): ?>
              <span class="btn-terrain desactive" title="Terrain actuellement en maintenance">En maintenance</span>
            <?php else: ?>
              <a href="reservation.php?terrain=<?= (int)$t["id_terrain"] ?>" class="btn-terrain">Réserver ce terrain</a>
            <?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- ===================== FOOTER ===================== -->
  <footer class="footer">
    <p><strong>3iL FootBook</strong> — Projet étudiant, 3iL Limoges</p>
    <p>Développé par Rayan, Mike, Mathias, Inès et Baptiste</p>
  </footer>

</body>
</html>