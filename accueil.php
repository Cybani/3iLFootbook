<?php
date_default_timezone_set('Europe/Paris');
require_once "php/bdd.php";
require "php/planning.php";

// Seuls les utilisateurs connectés peuvent consulter la page.
if (!isset($_SESSION["id_utilisateur"])) {
    header("Location: php/connexion.php");
    exit;
}

// Charger les terrains enregistrés dans la base.
$terrains = $pdo->query(
    "SELECT id_terrain, nom, type_surface, capacite
     FROM terrain
     ORDER BY nom"
)->fetchAll(PDO::FETCH_ASSOC);

// Une seule requête charge toutes les occupations de la semaine affichée.
$aujourdhui = new DateTimeImmutable('today');
$dateDemandee = isset($_GET['semaine']) && is_string($_GET['semaine'])
    ? datePlanningValide($_GET['semaine']) : null;
$dateInvalide = isset($_GET['semaine']) && $dateDemandee === null;
$debutSemaine = debutSemainePlanning($dateDemandee ?? $aujourdhui);
$finSemaine = $debutSemaine->modify('+6 days');
$semaineSuivante = $debutSemaine->modify('+7 days');

$terrainFiltre = filter_var($_GET['terrain'] ?? 0, FILTER_VALIDATE_INT) ?: 0;
if (!in_array($terrainFiltre, array_map('intval', array_column($terrains, 'id_terrain')), true)) {
    $terrainFiltre = 0;
}
$terrainsPlanning = array_filter($terrains, fn(array $terrain): bool =>
    $terrainFiltre === 0 || (int)$terrain['id_terrain'] === $terrainFiltre
);

$requetePlanning = $pdo->prepare(
    "SELECT id_terrain, date_reservation, heure_debut, heure_fin
     FROM reservation
     WHERE date_reservation >= CAST(:debut AS date)
       AND date_reservation < CAST(:fin AS date)
     ORDER BY id_terrain, date_reservation, heure_debut"
);
$requetePlanning->execute([
    'debut' => $debutSemaine->format('Y-m-d'),
    'fin' => $semaineSuivante->format('Y-m-d'),
]);
$reservationsPlanning = [];
foreach ($requetePlanning->fetchAll(PDO::FETCH_ASSOC) as $reservation) {
    $reservationsPlanning[(int)$reservation['id_terrain']][$reservation['date_reservation']][] = $reservation;
}

$joursSemaine = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
function lienSemainePlanning(DateTimeImmutable $date, int $terrain): string
{
    return 'accueil.php?' . http_build_query([
        'semaine' => $date->format('Y-m-d'), 'terrain' => $terrain,
    ]) . '#planning';
}

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
  <link rel="stylesheet" href="css/planning.css">

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
      <a href="accueil.php" class="active">Accueil</a>
      <a href="apropos.html">À propos</a>
      <a href="#planning">Planning</a>
      <a href="php/terrains.php">Nos terrains</a>
      <a href="php/reservation.php" class="nav-cta">Réserver</a>
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
      <p>Consultez le planning de la semaine et trouvez un créneau pour jouer.</p>
      <div class="hero-boutons">
        <a href="#planning" class="btn btn-primaire">Voir le planning</a>
        <a href="#terrains" class="btn btn-secondaire">Voir les terrains</a>
      </div>
    </div>

    <div class="hero-points">
      <button class="actif" aria-label="Photo 1"></button>
      <button aria-label="Photo 2"></button>
    </div>
  </section>

  <section class="section planning-section" id="planning" aria-labelledby="titre-planning">
    <div class="section-tete">
      <span class="sur-titre">Organisez votre prochain match</span>
      <h2 id="titre-planning">Planning des terrains</h2>
      <p>Du lundi au dimanche, de 8 h à 22 h. Cliquez sur une plage disponible pour préparer votre réservation.</p>
    </div>

    <div class="planning-commandes">
      <div class="planning-semaine">
        <p class="planning-periode">Du <?= h($debutSemaine->format('d/m/Y')) ?> au <?= h($finSemaine->format('d/m/Y')) ?></p>
        <nav class="planning-navigation" aria-label="Choisir la semaine du planning">
          <a href="<?= h(lienSemainePlanning($debutSemaine->modify('-7 days'), $terrainFiltre)) ?>">← Précédente</a>
          <a href="<?= h(lienSemainePlanning(debutSemainePlanning($aujourdhui), $terrainFiltre)) ?>">Cette semaine</a>
          <a href="<?= h(lienSemainePlanning($semaineSuivante, $terrainFiltre)) ?>">Suivante →</a>
        </nav>
      </div>
      <form method="get" action="accueil.php#planning" class="planning-filtre">
        <input type="hidden" name="semaine" value="<?= h($debutSemaine->format('Y-m-d')) ?>">
        <label for="planning-terrain">Terrain</label>
        <div class="planning-filtre-champs">
          <select name="terrain" id="planning-terrain">
            <option value="0">Tous les terrains</option>
            <?php foreach ($terrains as $terrain): ?>
              <option value="<?= (int)$terrain['id_terrain'] ?>" <?= (int)$terrain['id_terrain'] === $terrainFiltre ? 'selected' : '' ?>><?= h($terrain['nom']) ?></option>
            <?php endforeach; ?>
          </select>
          <button type="submit">Afficher</button>
        </div>
      </form>
    </div>

    <?php if ($dateInvalide): ?>
      <p class="message-erreur" role="status">La date demandée est invalide. La semaine en cours est affichée.</p>
    <?php endif; ?>

    <?php if (empty($terrainsPlanning)): ?>
      <p class="aucune-reservation">Aucun terrain n’est enregistré pour le moment.</p>
    <?php else: ?>
      <div class="planning-grille">
        <?php foreach ($terrainsPlanning as $terrain): ?>
          <article class="planning-carte">
            <header class="planning-carte-entete">
              <div>
                <h3><?= h($terrain['nom']) ?></h3>
                <p><?= h($terrain['type_surface']) ?> · <?= (int)$terrain['capacite'] ?> joueurs</p>
              </div>
            </header>
            <table class="planning-table">
              <caption class="planning-accessible">Planning de <?= h($terrain['nom']) ?> du <?= h($debutSemaine->format('d/m/Y')) ?> au <?= h($finSemaine->format('d/m/Y')) ?></caption>
              <thead><tr><th scope="col">Jour et date</th><th scope="col">Horaire</th><th scope="col">Statut</th></tr></thead>
              <?php for ($jourIndex = 0; $jourIndex < 7; $jourIndex++): ?>
                <?php
                  $jour = $debutSemaine->modify('+' . $jourIndex . ' days');
                  $dateJour = $jour->format('Y-m-d');
                  $estPasse = $jour < $aujourdhui;
                  $estAujourdhui = $dateJour === $aujourdhui->format('Y-m-d');
                  $creneaux = creneauxPlanning($reservationsPlanning[(int)$terrain['id_terrain']][$dateJour] ?? []);
                ?>
                <tbody class="planning-jour <?= $estAujourdhui ? 'planning-aujourdhui' : '' ?>">
                  <?php foreach ($creneaux as $creneauIndex => $creneau): ?>
                    <tr>
                      <?php if ($creneauIndex === 0): ?>
                        <th scope="rowgroup" rowspan="<?= count($creneaux) ?>">
                          <span class="planning-jour-nom"><?= $joursSemaine[$jourIndex] ?></span>
                          <time datetime="<?= h($dateJour) ?>"><?= h($jour->format('d/m/Y')) ?></time>
                          <?php if ($estAujourdhui): ?><span class="planning-repere">Aujourd’hui</span><?php endif; ?>
                          <?php if ($estPasse): ?><span class="planning-passe">Jour passé</span><?php endif; ?>
                        </th>
                      <?php endif; ?>
                      <td class="planning-horaire"><?= h($creneau['debut']) ?> – <?= h($creneau['fin']) ?></td>
                      <td>
                        <?php if ($creneau['occupe']): ?>
                          <span class="planning-statut planning-occupe">Occupé</span>
                        <?php elseif ($estPasse): ?>
                          <span class="planning-statut planning-libre">Disponible</span>
                        <?php else: ?>
                          <?php $lienReservation = 'php/reservation.php?' . http_build_query([
                              'terrain' => (int)$terrain['id_terrain'], 'date' => $dateJour,
                              'debut' => $creneau['debut'], 'fin' => $creneau['fin'],
                          ]); ?>
                          <a class="planning-statut planning-libre planning-reserver" href="<?= h($lienReservation) ?>" aria-label="<?= h('Disponible : réserver ' . $terrain['nom'] . ' le ' . $jour->format('d/m/Y') . ' de ' . $creneau['debut'] . ' à ' . $creneau['fin']) ?>">Disponible ↗</a>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              <?php endfor; ?>
            </table>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <p class="planning-note">Les disponibilités sont actualisées à chaque chargement. Le créneau est à nouveau vérifié lors de la réservation.</p>
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
              <a class="planning-statut planning-libre planning-reserver" href="<?= h(lienSemainePlanning($debutSemaine, (int)$terrain['id_terrain'])) ?>">Voir le planning</a>
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
