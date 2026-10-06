<?php
require "admin_garde.php";

$TYPES_SURFACE = ["Synthétique", "Gazon", "Parquet", "Stabilisé", "Futsal"];
$STATUTS = ["actif", "maintenance"];

// Message flash (motif Post/Redirect/Get)
$flash = $_SESSION["flash_terrains"] ?? null; // ["type"=>"ok|err", "texte"=>"..."]
unset($_SESSION["flash_terrains"]);

function redirige_terrains(string $type, string $texte): void
{
    $_SESSION["flash_terrains"] = ["type" => $type, "texte" => $texte];
    header("Location: admin_terrains.php");
    exit;
}

// ---------- Traitement des actions ----------
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!csrf_valide($_POST["csrf_token"] ?? null)) {
        redirige_terrains("err", "Formulaire expiré, merci de réessayer.");
    }

    $action = $_POST["action"] ?? "";

    if ($action === "supprimer") {
        $id = (int)($_POST["id_terrain"] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM terrain WHERE id_terrain = :id");
        $stmt->execute(["id" => $id]);
        redirige_terrains(
            $stmt->rowCount() > 0 ? "ok" : "err",
            $stmt->rowCount() > 0
                ? "Terrain supprimé (ainsi que ses réservations)."
                : "Terrain introuvable."
        );
    }

    if ($action === "basculer_statut") {
        $id = (int)($_POST["id_terrain"] ?? 0);
        $stmt = $pdo->prepare(
            "UPDATE terrain
             SET statut_terrain = CASE WHEN statut_terrain = 'actif' THEN 'maintenance' ELSE 'actif' END
             WHERE id_terrain = :id"
        );
        $stmt->execute(["id" => $id]);
        redirige_terrains(
            $stmt->rowCount() > 0 ? "ok" : "err",
            $stmt->rowCount() > 0 ? "Statut du terrain mis à jour." : "Terrain introuvable."
        );
    }

    if ($action === "ajouter" || $action === "modifier") {
        $nom        = trim($_POST["nom"] ?? "");
        $surface    = trim($_POST["type_surface"] ?? "");
        $capacite   = (int)($_POST["capacite"] ?? 0);
        $quartier   = trim($_POST["quartier"] ?? "");
        $adresse    = trim($_POST["adresse"] ?? "");
        $description = trim($_POST["description"] ?? "");
        $statut     = in_array($_POST["statut_terrain"] ?? "", $STATUTS, true)
            ? $_POST["statut_terrain"] : "actif";

        if ($nom === "" || $surface === "" || $capacite < 2) {
            redirige_terrains("err", "Nom, surface et une capacité d'au moins 2 joueurs sont obligatoires.");
        }

        if ($action === "ajouter") {
            $stmt = $pdo->prepare(
                "INSERT INTO terrain (nom, type_surface, capacite, quartier, adresse, description, statut_terrain)
                 VALUES (:nom, :surface, :capacite, :quartier, :adresse, :description, :statut)"
            );
            $stmt->execute([
                "nom" => $nom, "surface" => $surface, "capacite" => $capacite,
                "quartier" => $quartier ?: null, "adresse" => $adresse ?: null,
                "description" => $description ?: null, "statut" => $statut,
            ]);
            redirige_terrains("ok", "Terrain « " . $nom . " » ajouté.");
        } else {
            $id = (int)($_POST["id_terrain"] ?? 0);
            $stmt = $pdo->prepare(
                "UPDATE terrain
                 SET nom = :nom, type_surface = :surface, capacite = :capacite,
                     quartier = :quartier, adresse = :adresse, description = :description,
                     statut_terrain = :statut
                 WHERE id_terrain = :id"
            );
            $stmt->execute([
                "nom" => $nom, "surface" => $surface, "capacite" => $capacite,
                "quartier" => $quartier ?: null, "adresse" => $adresse ?: null,
                "description" => $description ?: null, "statut" => $statut, "id" => $id,
            ]);
            redirige_terrains("ok", "Terrain « " . $nom . " » modifié.");
        }
    }

    redirige_terrains("err", "Action inconnue.");
}

// ---------- Données pour l'affichage ----------
// Terrain en cours d'édition (admin_terrains.php?modifier=ID)
$terrainEdite = null;
if (isset($_GET["modifier"])) {
    $stmt = $pdo->prepare("SELECT * FROM terrain WHERE id_terrain = :id");
    $stmt->execute(["id" => (int)$_GET["modifier"]]);
    $terrainEdite = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

// Nombre de réservations par terrain (pour info avant suppression)
$terrains = $pdo->query(
    "SELECT t.*, (SELECT COUNT(*) FROM reservation r WHERE r.id_terrain = t.id_terrain) AS nb_resa
     FROM terrain t
     ORDER BY t.nom"
)->fetchAll(PDO::FETCH_ASSOC);

$pageActiveAdmin = "terrains";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestion des terrains — Admin 3iL FootBook</title>
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
        <h1 class="admin-title">Gestion des terrains</h1>
        <p class="admin-intro">Ajoutez, modifiez ou mettez un terrain en maintenance.</p>
      </div>
      <a class="back-link" href="admin.php">← Tableau de bord</a>
    </div>

    <?php if ($flash): ?>
      <div class="flash <?= $flash["type"] === "err" ? "flash-erreur" : "" ?>"><?= e($flash["texte"]) ?></div>
    <?php endif; ?>

    <!-- Formulaire ajout / édition -->
    <section class="admin-card">
      <h2><?= $terrainEdite ? "Modifier le terrain" : "Ajouter un terrain" ?></h2>

      <form method="post" class="admin-form">
        <input type="hidden" name="csrf_token" value="<?= e(jeton_csrf()) ?>">
        <input type="hidden" name="action" value="<?= $terrainEdite ? "modifier" : "ajouter" ?>">
        <?php if ($terrainEdite): ?>
          <input type="hidden" name="id_terrain" value="<?= (int)$terrainEdite["id_terrain"] ?>">
        <?php endif; ?>

        <div class="champ">
          <label for="nom">Nom du terrain</label>
          <input type="text" id="nom" name="nom" required
                 value="<?= e($terrainEdite["nom"] ?? "") ?>" placeholder="Stade de Beaublanc">
        </div>

        <div class="champ">
          <label for="type_surface">Surface</label>
          <select id="type_surface" name="type_surface" required>
            <?php foreach ($TYPES_SURFACE as $t): ?>
              <option value="<?= e($t) ?>" <?= ($terrainEdite["type_surface"] ?? "") === $t ? "selected" : "" ?>><?= e($t) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="champ">
          <label for="capacite">Capacité (joueurs)</label>
          <input type="number" id="capacite" name="capacite" min="2" max="30" required
                 value="<?= e($terrainEdite["capacite"] ?? 10) ?>">
        </div>

        <div class="champ">
          <label for="quartier">Quartier</label>
          <input type="text" id="quartier" name="quartier"
                 value="<?= e($terrainEdite["quartier"] ?? "") ?>" placeholder="Beaublanc">
        </div>

        <div class="champ pleine-largeur">
          <label for="adresse">Adresse</label>
          <input type="text" id="adresse" name="adresse"
                 value="<?= e($terrainEdite["adresse"] ?? "") ?>" placeholder="Rue du Général Bessol, 87100 Limoges">
        </div>

        <div class="champ pleine-largeur">
          <label for="description">Description</label>
          <textarea id="description" name="description" placeholder="Petit texte de présentation"><?= e($terrainEdite["description"] ?? "") ?></textarea>
        </div>

        <div class="champ">
          <label for="statut_terrain">Statut</label>
          <select id="statut_terrain" name="statut_terrain">
            <option value="actif" <?= ($terrainEdite["statut_terrain"] ?? "actif") === "actif" ? "selected" : "" ?>>Actif (réservable)</option>
            <option value="maintenance" <?= ($terrainEdite["statut_terrain"] ?? "") === "maintenance" ? "selected" : "" ?>>En maintenance</option>
          </select>
        </div>

        <div class="barre-actions">
          <button type="submit" class="btn-admin"><?= $terrainEdite ? "Enregistrer les modifications" : "Ajouter le terrain" ?></button>
          <?php if ($terrainEdite): ?>
            <a class="btn-ghost" href="admin_terrains.php">Annuler</a>
          <?php endif; ?>
        </div>
      </form>
    </section>

    <!-- Liste des terrains -->
    <section class="admin-card">
      <h2>Terrains existants (<?= count($terrains) ?>)</h2>
      <div class="table-wrap">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Nom</th>
              <th>Quartier</th>
              <th>Surface</th>
              <th>Capacité</th>
              <th>Réserv.</th>
              <th>Statut</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($terrains as $t): ?>
              <?php $actif = $t["statut_terrain"] === "actif"; ?>
              <tr>
                <td><strong><?= e($t["nom"]) ?></strong></td>
                <td><?= e($t["quartier"] ?? "—") ?></td>
                <td><?= e($t["type_surface"]) ?></td>
                <td><?= (int)$t["capacite"] ?></td>
                <td><?= (int)$t["nb_resa"] ?></td>
                <td>
                  <span class="pill <?= $actif ? "pill-ok" : "pill-warn" ?>">
                    <?= $actif ? "Actif" : "Maintenance" ?>
                  </span>
                </td>
                <td>
                  <div class="actions-ligne">
                    <a class="btn-ghost" href="admin_terrains.php?modifier=<?= (int)$t["id_terrain"] ?>">Modifier</a>

                    <form method="post" onsubmit="return true;">
                      <input type="hidden" name="csrf_token" value="<?= e(jeton_csrf()) ?>">
                      <input type="hidden" name="action" value="basculer_statut">
                      <input type="hidden" name="id_terrain" value="<?= (int)$t["id_terrain"] ?>">
                      <button type="submit" class="btn-ghost"><?= $actif ? "Mettre en maintenance" : "Réactiver" ?></button>
                    </form>

                    <form method="post" onsubmit="return confirm('Supprimer ce terrain ? Ses <?= (int)$t["nb_resa"] ?> réservation(s) seront aussi supprimées.');">
                      <input type="hidden" name="csrf_token" value="<?= e(jeton_csrf()) ?>">
                      <input type="hidden" name="action" value="supprimer">
                      <input type="hidden" name="id_terrain" value="<?= (int)$t["id_terrain"] ?>">
                      <button type="submit" class="btn-danger">Supprimer</button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>

</body>
</html>
