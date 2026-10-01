<?php
require "php/bdd.php";

// Seuls les utilisateurs connectés peuvent consulter la page.
if (!isset($_SESSION["id_utilisateur"])) {
    header("Location: php/connexion.php");
    exit;
}

// Charger les terrains enregistrés dans la base.
$terrains = $pdo->query(
    "SELECT nom, type_surface, capacite, statut_terrain
     FROM terrain
     ORDER BY nom"
)->fetchAll(PDO::FETCH_ASSOC);

// Choisir une image selon la surface du terrain.
function imagePourSurface(string $surface): string
{
    if (
        stripos($surface, "futsal") !== false ||
        stripos($surface, "parquet") !== false
    ) {
        return "images/apropos-2.jpg";
    }

    if (
        stripos($surface, "synth") !== false ||
        stripos($surface, "gazon") !== false
    ) {
        return "images/apropos-1.jpg";
    }

    return "images/accueil-1.jpg";
}

function h($valeur): string
{
    return htmlspecialchars((string)$valeur, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>3iL FootBook — Nos terrains</title>
  <meta name="description" content="Consultez les terrains de sport disponibles sur 3iL FootBook.">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">

  <style>
    #terrains { scroll-margin-top: 90px; }

    .terrains-grille {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 24px;
    }

    .terrain-carte {
      overflow: hidden;
      background: #fff;
      border: 1px solid #e7ece9;
      border-radius: 14px;
      box-shadow: 0 8px 24px #10251a12;
    }

    .terrain-photo {
      display: block;
      width: 100%;
      height: 190px;
      object-fit: cover;
    }

    .terrain-infos { padding: 18px 20px 22px; }
    .terrain-infos h3 { margin: 0 0 10px; }
    .terrain-infos p { margin: 7px 0; }

    .terrain-statut {
      display: inline-block;
      margin-top: 8px;
      padding: 5px 10px;
      border-radius: 999px;
      font-size: 13px;
      font-weight: 600;
    }

    .terrain-actif { background: #e5f5ea; color: #185638; }
    .terrain-maintenance { background: #fff1e6; color: #8b4513; }

    .terrains-vide {
      padding: 20px;
      text-align: center;
    }
  </style>
</head>

<body>
  <input type="checkbox" id="nav-toggle" class="nav-toggle">

  <header class="navbar">
    <a href="accueil.php" class="logo">🏟️ 3iL <span>FootBook</span></a>

    <label for="nav-toggle" class="nav-burger">
      <span></span><span></span><span></span>
    </label>

    <nav class="nav-links">
      <a href="accueil.php" class="nav-cta">Accueil</a>
      <a href="apropos.html">À propos</a>
      <a href="#terrains">Nos terrains</a>
      <a href="php/profil.php">Profil</a>
      <a href="php/deconnexion.php">Déconnexion</a>
    </nav>
  </header>

  <section class="hero">
    <div class="hero-slide actif" style="background-image: url('images/accueil-1.jpg');"></div>
    <div class="hero-slide" style="background-image: url('images/accueil-2.jpg');"></div>
    <div class="hero-overlay"></div>

    <div class="hero-contenu">
      <span class="sur-titre">Réservé aux étudiants 3iL Limoges</span>
      <h1>Découvrez nos <span>terrains de sport</span></h1>
      <p>Consultez les terrains et leurs caractéristiques.</p>
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

  <section class="section" id="terrains">
    <div class="section-tete">
      <span class="sur-titre">3iL FootBook</span>
      <h2>Nos terrains</h2>
    </div>

    <?php if (empty($terrains)): ?>
      <p class="terrains-vide">Aucun terrain n’est enregistré dans la base pour le moment.</p>
    <?php else: ?>
      <div class="terrains-grille">
        <?php foreach ($terrains as $terrain): ?>
          <?php
            $surface = (string)$terrain["type_surface"];
            $statut = strtolower((string)$terrain["statut_terrain"]);
            $estActif = $statut === "actif";
            $image = imagePourSurface($surface);
          ?>

          <article class="terrain-carte">
            <img
              class="terrain-photo"
              src="<?= h($image) ?>"
              alt="Terrain <?= h($terrain["nom"]) ?>"
            >

            <div class="terrain-infos">
              <h3><?= h($terrain["nom"]) ?></h3>
              <p>Surface : <?= h($surface) ?></p>
              <p>Capacité : <?= (int)$terrain["capacite"] ?> joueurs</p>
              <span class="terrain-statut <?= $estActif ? "terrain-actif" : "terrain-maintenance" ?>">
                <?= $estActif ? "Disponible" : h($terrain["statut_terrain"]) ?>
              </span>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <footer class="footer">
    <p><strong>3iL FootBook</strong> — Projet étudiant, 3iL Limoges</p>
    <p>Développé par Rayan, Mike, Mathias, Inès et Baptiste</p>
  </footer>

  <script src="js/accueil.js"></script>
</body>
</html>