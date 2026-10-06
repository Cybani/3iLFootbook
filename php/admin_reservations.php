<?php
require "admin_garde.php";

$flash = $_SESSION["flash_resa"] ?? null;
unset($_SESSION["flash_resa"]);

// ---------- Annulation ----------
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!csrf_valide($_POST["csrf_token"] ?? null)) {
        $_SESSION["flash_resa"] = ["type" => "err", "texte" => "Formulaire expiré, réessaie."];
        header("Location: admin_reservations.php");
        exit;
    }

    if (($_POST["action"] ?? "") === "annuler") {
        $id = (int)($_POST["id_reservation"] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM reservation WHERE id_reservation = :id");
        $stmt->execute(["id" => $id]);
        $_SESSION["flash_resa"] = [
            "type" => $stmt->rowCount() > 0 ? "ok" : "err",
            "texte" => $stmt->rowCount() > 0 ? "Réservation annulée." : "Réservation introuvable.",
        ];
    }
    header("Location: admin_reservations.php" . (isset($_POST["retour_filtre"]) && $_POST["retour_filtre"] !== "" ? "?terrain=" . (int)$_POST["retour_filtre"] : ""));
    exit;
}

// ---------- Filtres ----------
$filtreTerrain = isset($_GET["terrain"]) ? (int)$_GET["terrain"] : 0;
$filtrePeriode = $_GET["periode"] ?? "avenir"; // avenir | toutes

$terrainsListe = $pdo->query("SELECT id_terrain, nom FROM terrain ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);

$conditions = [];
$params = [];
if ($filtreTerrain > 0) {
    $conditions[] = "r.id_terrain = :terrain";
    $params["terrain"] = $filtreTerrain;
}
if ($filtrePeriode === "avenir") {
    $conditions[] = "r.date_reservation >= CURRENT_DATE";
}
$where = $conditions ? ("WHERE " . implode(" AND ", $conditions)) : "";

$sql = "SELECT r.id_reservation, r.date_reservation, r.heure_debut, r.heure_fin, r.statut_reservation,
               t.nom AS terrain_nom, t.quartier,
               u.nom AS u_nom, u.prenom AS u_prenom, u.email
        FROM reservation r
        JOIN terrain t ON t.id_terrain = r.id_terrain
        JOIN utilisateur u ON u.id_utilisateur = r.id_utilisateur
        $where
        ORDER BY r.date_reservation ASC, r.heure_debut ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

function h($heure) { return substr((string)$heure, 0, 5); }

$pageActiveAdmin = "reservations";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Toutes les réservations — Admin 3iL FootBook</title>
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
        <h1 class="admin-title">Toutes les réservations</h1>
        <p class="admin-intro">Vue d'ensemble des créneaux réservés par les étudiants.</p>
      </div>
      <a class="back-link" href="admin.php">← Tableau de bord</a>
    </div>

    <?php if ($flash): ?>
      <div class="flash <?= $flash["type"] === "err" ? "flash-erreur" : "" ?>"><?= e($flash["texte"]) ?></div>
    <?php endif; ?>

    <section class="admin-card">
      <!-- Filtres -->
      <form method="get" class="admin-filtres">
        <div class="champ">
          <label for="terrain">Terrain</label>
          <select id="terrain" name="terrain" onchange="this.form.submit()">
            <option value="0">Tous les terrains</option>
            <?php foreach ($terrainsListe as $t): ?>
              <option value="<?= (int)$t["id_terrain"] ?>" <?= $filtreTerrain === (int)$t["id_terrain"] ? "selected" : "" ?>>
                <?= e($t["nom"]) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="champ">
          <label for="periode">Période</label>
          <select id="periode" name="periode" onchange="this.form.submit()">
            <option value="avenir" <?= $filtrePeriode === "avenir" ? "selected" : "" ?>>À venir</option>
            <option value="toutes" <?= $filtrePeriode === "toutes" ? "selected" : "" ?>>Toutes (historique inclus)</option>
          </select>
        </div>
        <noscript><button type="submit" class="btn-admin">Filtrer</button></noscript>
      </form>

      <h2><?= count($reservations) ?> réservation<?= count($reservations) > 1 ? "s" : "" ?></h2>

      <?php if (empty($reservations)): ?>
        <div class="aucune-reservation">Aucune réservation ne correspond à ce filtre.</div>
      <?php else: ?>
        <div class="table-wrap">
          <table class="admin-table">
            <thead>
              <tr>
                <th>Date</th>
                <th>Créneau</th>
                <th>Terrain</th>
                <th>Étudiant</th>
                <th>Statut</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($reservations as $r): ?>
                <?php $passee = $r["date_reservation"] < date("Y-m-d"); ?>
                <tr>
                  <td><?= date("d/m/Y", strtotime($r["date_reservation"])) ?></td>
                  <td><?= h($r["heure_debut"]) ?> → <?= h($r["heure_fin"]) ?></td>
                  <td><strong><?= e($r["terrain_nom"]) ?></strong><br><span style="font-size:.8rem;color:#718078;">📍 <?= e($r["quartier"] ?? "Limoges") ?></span></td>
                  <td><?= e(trim($r["u_prenom"] . " " . $r["u_nom"])) ?><br><span style="font-size:.8rem;color:#718078;"><?= e($r["email"]) ?></span></td>
                  <td>
                    <span class="pill <?= $passee ? "pill-user" : "pill-ok" ?>">
                      <?= $passee ? "Passée" : e($r["statut_reservation"]) ?>
                    </span>
                  </td>
                  <td>
                    <form method="post" onsubmit="return confirm('Annuler la réservation de <?= e(trim($r["u_prenom"] . " " . $r["u_nom"])) ?> ?');">
                      <input type="hidden" name="csrf_token" value="<?= e(jeton_csrf()) ?>">
                      <input type="hidden" name="action" value="annuler">
                      <input type="hidden" name="id_reservation" value="<?= (int)$r["id_reservation"] ?>">
                      <input type="hidden" name="retour_filtre" value="<?= $filtreTerrain > 0 ? (int)$filtreTerrain : "" ?>">
                      <button type="submit" class="btn-danger">Annuler</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </section>
  </main>

</body>
</html>
