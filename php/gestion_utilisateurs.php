<?php
require __DIR__ . "/bdd.php";

if (!isset($_SESSION["id_utilisateur"])) {
    http_response_code(403);
    exit("Accès réservé aux administrateurs.");
}

// Relire le rôle en base afin qu'une rétrogradation prenne effet immédiatement.
$verificationAdmin = $pdo->prepare(
    "SELECT role FROM utilisateur WHERE id_utilisateur = :id"
);
$verificationAdmin->execute(["id" => (int)$_SESSION["id_utilisateur"]]);
$roleActuel = $verificationAdmin->fetchColumn();

if ($roleActuel === false || (int)$roleActuel !== 0) {
    unset($_SESSION["role"]);
    http_response_code(403);
    exit("Accès réservé aux administrateurs.");
}

if (empty($_SESSION["csrf_admin_users"])) {
    $_SESSION["csrf_admin_users"] = bin2hex(random_bytes(32));
}

$message = $_SESSION["message_admin_users"] ?? "";
unset($_SESSION["message_admin_users"]);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST["csrf_token"] ?? "";

    if (!is_string($token) || !hash_equals($_SESSION["csrf_admin_users"], $token)) {
        http_response_code(400);
        exit("Formulaire expiré. Recharge la page et réessaie.");
    }

    $idUtilisateur = filter_var($_POST["id_utilisateur"] ?? null, FILTER_VALIDATE_INT);
    $nouveauRole = filter_var($_POST["nouveau_role"] ?? null, FILTER_VALIDATE_INT);

    if (
        $idUtilisateur === false ||
        $idUtilisateur === null ||
        $idUtilisateur < 1 ||
        !in_array($nouveauRole, [0, 1], true)
    ) {
        $message = "Demande invalide.";
    } elseif (
        $idUtilisateur === (int)$_SESSION["id_utilisateur"] &&
        $nouveauRole === 1
    ) {
        $message = "Tu ne peux pas retirer ton propre rôle administrateur.";
    } else {
        try {
            $pdo->beginTransaction();
            $pdo->exec("LOCK TABLE utilisateur IN EXCLUSIVE MODE");

            $stmt = $pdo->prepare(
                "SELECT id_utilisateur, role
                 FROM utilisateur
                 WHERE id_utilisateur = :id"
            );
            $stmt->execute(["id" => $idUtilisateur]);
            $cible = $stmt->fetch(PDO::FETCH_ASSOC);
            $peutModifier = false;

            if (!$cible) {
                $message = "Utilisateur introuvable.";
            } elseif ((int)$cible["role"] === $nouveauRole) {
                $message = "Le rôle de cet utilisateur n'a pas changé.";
            } elseif ((int)$cible["role"] === 0 && $nouveauRole === 1) {
                $nombreAdmins = (int)$pdo
                    ->query("SELECT COUNT(*) FROM utilisateur WHERE role = 0")
                    ->fetchColumn();

                if ($nombreAdmins <= 1) {
                    $message = "Le dernier administrateur ne peut pas être rétrogradé.";
                } else {
                    $peutModifier = true;
                }
            } else {
                $peutModifier = true;
            }

            if ($peutModifier) {
                $update = $pdo->prepare(
                    "UPDATE utilisateur
                     SET role = :role
                     WHERE id_utilisateur = :id"
                );
                $update->execute([
                    "role" => $nouveauRole,
                    "id" => $idUtilisateur,
                ]);
                $message = $nouveauRole === 0
                    ? "Utilisateur promu administrateur."
                    : "Administrateur rétrogradé en utilisateur.";
            }

            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $message = "La modification a échoué. Vérifie la connexion à la base de données.";
        }
    }

    $_SESSION["message_admin_users"] = $message;
    header("Location: gestion_utilisateurs.php");
    exit;
}

$utilisateurs = $pdo->query(
    "SELECT id_utilisateur, nom, prenom, email, role
     FROM utilisateur
     ORDER BY role ASC, nom ASC, prenom ASC"
)->fetchAll(PDO::FETCH_ASSOC);

function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des utilisateurs — 3iL FootBook</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { background: #f4f6f4; }
        .admin-page { width: min(1000px, calc(100% - 32px)); margin: 110px auto 48px; }
        .admin-card { background: #fff; border-radius: 16px; padding: 28px; box-shadow: 0 12px 35px #10251a12; }
        .admin-title { margin: 0 0 8px; color: #15241d; }
        .admin-intro { margin: 0 0 24px; color: #64736a; }
        .admin-top { display: flex; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: 22px; }
        .back-link { color: #185638; font-weight: 600; text-decoration: none; }
        .flash { padding: 12px 16px; margin-bottom: 18px; border-radius: 10px; background: #eef8ef; color: #245d31; }
        .table-wrap { overflow-x: auto; }
        .users-table { width: 100%; border-collapse: collapse; min-width: 620px; }
        .users-table th, .users-table td { padding: 14px 12px; text-align: left; border-bottom: 1px solid #e8ede9; }
        .users-table th { color: #56665d; font-size: 13px; text-transform: uppercase; letter-spacing: .04em; }
        .role-pill { display: inline-block; padding: 5px 10px; border-radius: 999px; font-size: 13px; font-weight: 600; }
        .role-admin { background: #fff3c4; color: #765700; }
        .role-user { background: #e8f0eb; color: #385346; }
        .role-form { margin: 0; }
        .role-button { border: 0; border-radius: 8px; padding: 9px 12px; font: inherit; font-size: 13px; font-weight: 600; cursor: pointer; }
        .promote { background: var(--or, #f2b705); color: var(--nuit, #0f1f17); }
        .demote { background: #e9eeeb; color: #34473c; }
        .self-label { color: #718078; font-size: 13px; }
        @media (max-width: 620px) {
            .admin-card { padding: 20px 14px; }
            .admin-top { align-items: flex-start; flex-direction: column; }
        }
    </style>
</head>
<body>
    <input type="checkbox" id="nav-toggle" class="nav-toggle">
    <header class="navbar">
        <a href="../accueil.php" class="logo">🏟️ 3iL <span>FootBook</span></a>
        <label for="nav-toggle" class="nav-burger">
            <span></span><span></span><span></span>
        </label>
        <nav class="nav-links">
            <a href="../accueil.php">Accueil</a>
            <a href="../apropos.html">À propos</a>
            <a href="../accueil.php#terrains">Nos terrains</a>
            <a href="gestion_utilisateurs.php" class="active">Gestion utilisateurs</a>
            <a href="deconnexion.php" class="nav-cta">Déconnexion</a>
        </nav>
    </header>

    <main class="admin-page">
        <div class="admin-top">
            <div>
                <h1 class="admin-title">Gestion des utilisateurs</h1>
                <p class="admin-intro">Consulte les comptes et gère leur rôle.</p>
            </div>
            <a class="back-link" href="../accueil.php">← Retour à l’accueil</a>
        </div>

        <section class="admin-card">
            <?php if ($message !== ""): ?>
                <div class="flash" role="status"><?= e($message) ?></div>
            <?php endif; ?>

            <div class="table-wrap">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($utilisateurs as $utilisateur): ?>
                        <?php
                        $role = (int)$utilisateur["role"];
                        $estMoi = (int)$utilisateur["id_utilisateur"] === (int)$_SESSION["id_utilisateur"];
                        ?>
                        <tr>
                            <td><?= e(trim($utilisateur["prenom"] . " " . $utilisateur["nom"])) ?></td>
                            <td><?= e($utilisateur["email"]) ?></td>
                            <td>
                                <span class="role-pill <?= $role === 0 ? "role-admin" : "role-user" ?>">
                                    <?= $role === 0 ? "Administrateur" : "Utilisateur" ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($estMoi): ?>
                                    <span class="self-label">Compte actuel</span>
                                <?php else: ?>
                                    <form class="role-form" method="post">
                                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION["csrf_admin_users"]) ?>">
                                        <input type="hidden" name="id_utilisateur" value="<?= (int)$utilisateur["id_utilisateur"] ?>">
                                        <input type="hidden" name="nouveau_role" value="<?= $role === 0 ? 1 : 0 ?>">
                                        <button class="role-button <?= $role === 0 ? "demote" : "promote" ?>" type="submit">
                                            <?= $role === 0 ? "Rendre utilisateur" : "Rendre administrateur" ?>
                                        </button>
                                    </form>
                                <?php endif; ?>
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