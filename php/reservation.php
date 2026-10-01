<?php
require "bdd.php";

// Protection : réservé aux étudiants connectés
if (!isset($_SESSION["id_utilisateur"])) {
    header("Location: connexion.php");
    exit;
}

$id_utilisateur = $_SESSION["id_utilisateur"];
$erreur = "";
$succes = "";

// Horaires d'ouverture des terrains
const HEURE_OUVERTURE = "08:00";
const HEURE_FERMETURE = "22:00";

// ---------- Traitement des formulaires (POST) ----------
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "reserver";

    // --- Annulation d'une réservation ---
    if ($action === "annuler") {
        $id_reservation = (int)($_POST["id_reservation"] ?? 0);
        // On ne supprime que si la réservation appartient bien à l'utilisateur
        $stmt = $pdo->prepare(
            "DELETE FROM reservation
             WHERE id_reservation = :id AND id_utilisateur = :uid"
        );
        $stmt->execute(["id" => $id_reservation, "uid" => $id_utilisateur]);
        $succes = $stmt->rowCount() > 0
            ? "Réservation annulée."
            : "Réservation introuvable.";
    }

    // --- Création d'une réservation ---
    if ($action === "reserver") {
        $id_terrain = (int)($_POST["id_terrain"] ?? 0);
        $date       = trim($_POST["date_reservation"] ?? "");
        $debut      = trim($_POST["heure_debut"] ?? "");
        $fin        = trim($_POST["heure_fin"] ?? "");

        if ($id_terrain === 0 || $date === "" || $debut === "" || $fin === "") {
            $erreur = "Merci de remplir tous les champs.";
        } elseif ($date < date("Y-m-d")) {
            $erreur = "Impossible de réserver dans le passé.";
        } elseif ($fin <= $debut) {
            $erreur = "L'heure de fin doit être après l'heure de début.";
        } elseif ($debut < HEURE_OUVERTURE || $fin > HEURE_FERMETURE) {
            $erreur = "Les terrains sont ouverts de " . HEURE_OUVERTURE . " à " . HEURE_FERMETURE . ".";
        } else {
            // Le terrain existe-t-il et est-il disponible ?
            $stmt = $pdo->prepare("SELECT * FROM terrain WHERE id_terrain = :id");
            $stmt->execute(["id" => $id_terrain]);
            $terrain = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$terrain) {
                $erreur = "Ce terrain n'existe pas.";
            } elseif ($terrain["statut_terrain"] !== "actif") {
                $erreur = "Ce terrain est en maintenance, il n'est pas réservable.";
            } else {
                // Vérifie qu'aucune autre réservation ne chevauche ce créneau
                $stmt = $pdo->prepare(
                    "SELECT COUNT(*) FROM reservation
                     WHERE id_terrain = :id
                       AND date_reservation = CAST(:date AS date)
                       AND heure_debut < CAST(:fin AS time)
                       AND heure_fin   > CAST(:debut AS time)"
                );
                $stmt->execute([
                    "id"    => $id_terrain,
                    "date"  => $date,
                    "fin"   => $fin,
                    "debut" => $debut,
                ]);

                if ($stmt->fetchColumn() > 0) {
                    $erreur = "Ce créneau est déjà réservé sur ce terrain. Choisissez un autre horaire.";
                } else {
                    $stmt = $pdo->prepare(
                        "INSERT INTO reservation
                            (id_utilisateur, id_terrain, date_reservation, heure_debut, heure_fin, statut_reservation)
                         VALUES (:uid, :id, :date, :debut, :fin, 'validée')"
                    );
                    $stmt->execute([
                        "uid"   => $id_utilisateur,
                        "id"    => $id_terrain,
                        "date"  => $date,
                        "debut" => $debut,
                        "fin"   => $fin,
                    ]);
                    $succes = "Réservation confirmée pour " . htmlspecialchars($terrain["nom"]) . " !";
                }
            }
        }
    }
}

// ---------- Données pour l'affichage ----------

// Terrains actifs pour le menu déroulant
$terrainsActifs = $pdo->query(
    "SELECT id_terrain, nom, quartier, type_surface, capacite
     FROM terrain WHERE statut_terrain = 'actif' ORDER BY nom"
)->fetchAll(PDO::FETCH_ASSOC);

// Terrain pré-sélectionné (depuis la page Nos terrains : reservation.php?terrain=ID)
$terrainSelectionne = (int)($_GET["terrain"] ?? ($_POST["id_terrain"] ?? 0));

// Réservations à venir de l'utilisateur
$stmt = $pdo->prepare(
    "SELECT r.id_reservation, r.date_reservation, r.heure_debut, r.heure_fin,
            t.nom AS terrain_nom, t.quartier
     FROM reservation r
     JOIN terrain t ON t.id_terrain = r.id_terrain
     WHERE r.id_utilisateur = :uid AND r.date_reservation >= CURRENT_DATE
     ORDER BY r.date_reservation ASC, r.heure_debut ASC"
);
$stmt->execute(["uid" => $id_utilisateur]);
$mesReservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Petit formateur d'heure : "18:00:00" -> "18:00"
function h($heure) { return substr($heure, 0, 5); }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Réserver un terrain — 3iL FootBook</title>
  <meta name="description" content="Réservez un créneau sur un terrain de foot à Limoges.">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

  <!-- ===================== NAVIGATION ===================== -->
  <input type="checkbox" id="nav-toggle" class="nav-toggle">
  <header class="navbar">
    <a href="../accueil.php" class="logo">🏟️ 3iL <span>FootBook</span></a>

    <label for="nav-toggle" class="nav-burger">
      <span></span><span></span><span></span>
    </label>

    <nav class="nav-links">
      <a href="../accueil.php">Accueil</a>
      <a href="../apropos.html">À propos</a>
      <a href="terrains.php">Nos terrains</a>
      <a href="reservation.php" class="active">Réserver</a>
      <a href="profil.php">Profil</a>
      <a href="deconnexion.php">Déconnexion</a>
    </nav>
  </header>

  <!-- ===================== EN-TÊTE ===================== -->
  <section class="entete-page">
    <span class="sur-titre">Réservation</span>
    <h1>Réservez votre créneau</h1>
    <p>Choisissez un terrain, une date et un horaire. La disponibilité est vérifiée en temps réel.</p>
  </section>

  <!-- ===================== CONTENU ===================== -->
  <section class="section">
    <div class="grille-reservation">

      <!-- Colonne 1 : formulaire -->
      <div class="bloc-reservation">
        <h2>Nouvelle réservation</h2>
        <p class="intro">Terrains ouverts de <?= HEURE_OUVERTURE ?> à <?= HEURE_FERMETURE ?>.</p>

        <?php if ($erreur): ?>
          <div class="message-erreur"><?= htmlspecialchars($erreur) ?></div>
        <?php endif; ?>
        <?php if ($succes): ?>
          <div class="message-succes"><?= $succes ?></div>
        <?php endif; ?>

        <form method="post" class="formulaire-auth">
          <input type="hidden" name="action" value="reserver">

          <label for="id_terrain">Terrain</label>
          <select id="id_terrain" name="id_terrain" required>
            <option value="">— Choisir un terrain —</option>
            <?php foreach ($terrainsActifs as $t): ?>
              <option value="<?= (int)$t["id_terrain"] ?>"
                <?= $t["id_terrain"] == $terrainSelectionne ? "selected" : "" ?>>
                <?= htmlspecialchars($t["nom"]) ?> — <?= htmlspecialchars($t["quartier"]) ?> (<?= htmlspecialchars($t["type_surface"]) ?>)
              </option>
            <?php endforeach; ?>
          </select>

          <label for="date_reservation">Date</label>
          <input type="date" id="date_reservation" name="date_reservation"
                 min="<?= date("Y-m-d") ?>"
                 value="<?= htmlspecialchars($_POST["date_reservation"] ?? date("Y-m-d")) ?>" required>

          <div class="ligne-creneau">
            <div>
              <label for="heure_debut">Heure de début</label>
              <input type="time" id="heure_debut" name="heure_debut"
                     min="<?= HEURE_OUVERTURE ?>" max="<?= HEURE_FERMETURE ?>"
                     value="<?= htmlspecialchars($_POST["heure_debut"] ?? "18:00") ?>" required>
            </div>
            <div>
              <label for="heure_fin">Heure de fin</label>
              <input type="time" id="heure_fin" name="heure_fin"
                     min="<?= HEURE_OUVERTURE ?>" max="<?= HEURE_FERMETURE ?>"
                     value="<?= htmlspecialchars($_POST["heure_fin"] ?? "20:00") ?>" required>
            </div>
          </div>

          <button type="submit" class="btn btn-primaire btn-auth">Réserver</button>
        </form>
      </div>

      <!-- Colonne 2 : mes réservations -->
      <div class="bloc-reservation">
        <h2>Mes réservations à venir</h2>
        <p class="intro">Retrouvez ici vos créneaux réservés. Vous pouvez les annuler à tout moment.</p>

        <?php if (empty($mesReservations)): ?>
          <div class="aucune-reservation">Vous n'avez aucune réservation à venir.</div>
        <?php else: ?>
          <ul class="liste-reservations">
            <?php foreach ($mesReservations as $r): ?>
              <li class="item-reservation">
                <div class="infos">
                  <strong><?= htmlspecialchars($r["terrain_nom"]) ?></strong>
                  <span>📍 <?= htmlspecialchars($r["quartier"]) ?></span><br>
                  <span>📅 <?= date("d/m/Y", strtotime($r["date_reservation"])) ?>
                        · 🕒 <?= h($r["heure_debut"]) ?> → <?= h($r["heure_fin"]) ?></span>
                </div>
                <form method="post" onsubmit="return confirm('Annuler cette réservation ?');">
                  <input type="hidden" name="action" value="annuler">
                  <input type="hidden" name="id_reservation" value="<?= (int)$r["id_reservation"] ?>">
                  <button type="submit" class="btn-annuler">Annuler</button>
                </form>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>

    </div>
  </section>

  <!-- ===================== FOOTER ===================== -->
  <footer class="footer">
    <p><strong>3iL FootBook</strong> — Projet étudiant, 3iL Limoges</p>
    <p>Développé par Rayan, Mike, Mathias, Inès et Baptiste</p>
  </footer>

</body>
</html>
