<?php
require "bdd.php"; // démarre la session

$connecte = isset($_SESSION["id_utilisateur"]);
$estAdmin = $connecte && (int)($_SESSION["role"] ?? 1) === 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>À propos — 3iL FootBook</title>
  <meta name="description" content="Découvrez le projet 3iL FootBook et l'équipe étudiante derrière la plateforme.">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">
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
      <?php if ($connecte): ?>
        <a href="accueil.php">Accueil</a>
        <a href="apropos.php" class="active">À propos</a>
        <a href="accueil.php#planning">Planning</a>
        <a href="terrains.php">Nos terrains</a>
        <a href="reservation.php" class="nav-cta">Réserver</a>
        <a href="profil.php">Profil</a>
        <?php if ($estAdmin): ?>
          <a href="admin.php">⚙️ Admin</a>
        <?php endif; ?>
        <a href="deconnexion.php">Déconnexion</a>
      <?php else: ?>
        <a href="accueil.php">Accueil</a>
        <a href="apropos.php" class="active">À propos</a>
        <a href="accueil.php#planning">Planning</a>
        <a href="connexion.php">Connexion</a>
        <a href="inscription.php" class="nav-cta">Inscription</a>
      <?php endif; ?>
    </nav>
  </header>

  <!-- ===================== BANDEAU DE PAGE ===================== -->
  <section class="bandeau-page">
    <div class="bandeau-slide actif" style="background-image: url('../images/apropos-1.jpg');"></div>
    <div class="bandeau-slide" style="background-image: url('../images/apropos-2.jpg');"></div>
    <div class="bandeau-overlay"></div>

    <div class="bandeau-contenu">
      <span class="sur-titre">Le projet</span>
      <h1>À propos de 3iL FootBook</h1>
      <p>Une plateforme pensée par des étudiants, pour des étudiants, afin de rendre la réservation des terrains de sport plus simple sur le campus de 3iL Limoges.</p>
    </div>
  </section>

  <!-- ===================== MISSION ===================== -->
  <section class="section">
    <div class="mission">
      <div class="texte">
        <h2>Notre mission</h2>
        <p><strong>3iL FootBook</strong> est un site de réservation de terrains de sport développé dans le cadre d'un projet étudiant à 3iL Limoges.</p>
        <p>L'objectif est simple : permettre à n'importe quel étudiant de l'école de consulter les terrains disponibles (synthétique, gazon, futsal...) et de réserver un créneau en quelques clics, sans paperasse ni échange interminable de messages.</p>
        <p>Le site est entièrement réservé à la communauté étudiante de 3iL Limoges.</p>
      </div>

      <div class="chiffre-bloc">
        <ul>
          <li><span>Destiné à</span> <strong>Étudiants 3iL</strong></li>
          <li><span>Campus</span> <strong>Limoges</strong></li>
          <li><span>Type de terrains</span> <strong>Synthétique · Gazon · Futsal</strong></li>
          <li><span>Réservation</span> <strong>En ligne, en direct</strong></li>
        </ul>
      </div>
    </div>
  </section>

  <!-- ===================== ÉQUIPE ===================== -->
  <section class="section equipe">
    <div class="section-tete">
      <span class="sur-titre">L'équipe</span>
      <h2>Le projet a été développé par</h2>
    </div>

    <div class="grille-equipe">
      <div class="carte-membre">
        <div class="avatar-initiale">R</div>
        <h3>Rayan</h3>
        <p class="role">Membre du projet</p>
      </div>
      <div class="carte-membre">
        <div class="avatar-initiale">M</div>
        <h3>Mike</h3>
        <p class="role">Membre du projet</p>
      </div>
      <div class="carte-membre">
        <div class="avatar-initiale">M</div>
        <h3>Matthias</h3>
        <p class="role">Membre du projet</p>
      </div>
      <div class="carte-membre">
        <div class="avatar-initiale">I</div>
        <h3>Inès</h3>
        <p class="role">Membre du projet</p>
      </div>
      <div class="carte-membre">
        <div class="avatar-initiale">B</div>
        <h3>Baptiste</h3>
        <p class="role">Membre du projet</p>
      </div>
    </div>
  </section>

  <!-- ===================== ÉCOLE ===================== -->
  <section class="section">
    <div class="ecole">
      <h2>🎓 3iL Limoges</h2>
      <p>Projet réalisé dans le cadre de notre formation à 3iL Limoges, école d'ingénieurs en informatique. 3iL FootBook illustre nos compétences en développement web appliquées à un besoin concret du quotidien étudiant.</p>
    </div>
  </section>

  <!-- ===================== FOOTER ===================== -->
  <footer class="footer">
    <p><strong>3iL FootBook</strong> — Projet étudiant, 3iL Limoges</p>
    <p>Développé par Rayan, Mike, Matthias, Inès et Baptiste</p>
  </footer>

  <script src="../js/apropos.js"></script>
</body>
</html>
