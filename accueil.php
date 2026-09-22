<?php
require "php/bdd.php";

// Protection : si pas connecté, on renvoie vers la page de connexion
if (!isset($_SESSION["id_utilisateur"])) {
    header("Location: php/connexion.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>3iL FootBook — Réservez votre terrain</title>
  <meta name="description" content="3iL FootBook, la plateforme de réservation de terrains de sport réservée aux étudiants de 3iL Limoges.">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
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
      <a href="accueil.php" class="active">Accueil</a>
      <a href="apropos.html">À propos</a>
      <a href="#terrains">Nos terrains</a>
      <a href="php/connexion.php" class="nav-cta">Connexion</a>
    </nav>
  </header>

  <!-- ===================== HERO / DIAPORAMA ===================== -->
  <section class="hero">
    <div class="hero-slide actif" style="background-image: url('images/accueil-1.jpg');"></div>
    <div class="hero-slide" style="background-image: url('images/accueil-2.jpg');"></div>
    <div class="hero-overlay"></div>

    <div class="hero-contenu">
      <span class="sur-titre">Réservé aux étudiants 3iL Limoges</span>
      <p>[DEBUG] Tu es connecté en tant que <strong><?= htmlspecialchars($_SESSION["role"]) ?></strong>.[DEBUG]</p>

      <h1>Réservez votre <span>terrain de foot</span> en quelques clics</h1>
      <p>3iL FootBook, c'est la plateforme qui simplifie la réservation des terrains de sport entre étudiants. Choisissez un créneau, un terrain, et jouez.</p>
      <div class="hero-boutons">
        <a href="#terrains" class="btn btn-primaire">Voir les terrains</a>
        <a href="apropos.html" class="btn btn-secondaire">En savoir plus</a>
      </div>
    </div>

    <div class="hero-points">
      <button class="actif" aria-label="Photo 1"></button>
      <button aria-label="Photo 2"></button>
    </div>
  </section>

  <!-- ===================== ATOUTS ===================== -->
  <section class="section" id="terrains">
    <div class="section-tete">
      <span class="sur-titre">Pourquoi 3iL FootBook ?</span>
      <h2>Une réservation simple, pensée pour les étudiants</h2>
    </div>

    <div class="cartes-atouts">
      <div class="carte-atout">
        <div class="icone">⚽</div>
        <h3>Plusieurs terrains</h3>
        <p>Synthétique, gazon ou futsal : choisissez le terrain qui vous convient.</p>
      </div>
      <div class="carte-atout">
        <div class="icone">🕒</div>
        <h3>Créneaux en temps réel</h3>
        <p>Consultez les disponibilités et réservez le créneau qui vous arrange.</p>
      </div>
      <div class="carte-atout">
        <div class="icone">🎓</div>
        <h3>100% étudiant 3iL</h3>
        <p>Une plateforme conçue par et pour les étudiants du campus de Limoges.</p>
      </div>
      <div class="carte-atout">
        <div class="icone">✅</div>
        <h3>Confirmation immédiate</h3>
        <p>Votre réservation est validée instantanément, sans échange d'e-mails.</p>
      </div>
    </div>
  </section>

  <!-- ===================== FOOTER ===================== -->
  <footer class="footer">
    <p><strong>3iL FootBook</strong> — Projet étudiant, 3iL Limoges</p>
    <p>Développé par Rayan, Mike, Matthias, Inès et Baptiste</p>
  </footer>

  <script src="js/accueil.js"></script>
</body>
</html>
