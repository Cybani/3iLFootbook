<?php
require "bdd.php";

// Protection : si pas connecté, on renvoie vers la page de connexion
if (!isset($_SESSION["id_utilisateur"])) {
    header("Location: connexion.php");
    exit;
}

$id_utilisateur = $_SESSION["id_utilisateur"];
$erreur = "";
$succes = "";

// Traitement du formulaire de modification
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = trim($_POST["nom"] ?? "");
    $prenom = trim($_POST["prenom"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $nouveau_mot_de_passe = $_POST["nouveau_mot_de_passe"] ?? "";

    if ($nom === "" || $prenom === "" || $email === "") {
        $erreur = "Le nom, le prénom et l'email sont obligatoires.";
    } else {
        // Vérifie que l'email n'est pas déjà utilisé par un AUTRE utilisateur
        $stmt = $pdo->prepare(
            "SELECT id_utilisateur FROM utilisateur WHERE email = :email AND id_utilisateur != :id"
        );
        $stmt->execute(["email" => $email, "id" => $id_utilisateur]);

        if ($stmt->fetch()) {
            $erreur = "Cet email est déjà utilisé par un autre compte.";
        } else {
            if ($nouveau_mot_de_passe !== "") {
                // Mise à jour avec changement de mot de passe
                $hash = password_hash($nouveau_mot_de_passe, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare(
                    "UPDATE utilisateur
                     SET nom = :nom, prenom = :prenom, email = :email, mot_de_passe = :mdp
                     WHERE id_utilisateur = :id"
                );
                $stmt->execute([
                    "nom" => $nom,
                    "prenom" => $prenom,
                    "email" => $email,
                    "mdp" => $hash,
                    "id" => $id_utilisateur
                ]);
            } else {
                // Mise à jour sans toucher au mot de passe
                $stmt = $pdo->prepare(
                    "UPDATE utilisateur
                     SET nom = :nom, prenom = :prenom, email = :email
                     WHERE id_utilisateur = :id"
                );
                $stmt->execute([
                    "nom" => $nom,
                    "prenom" => $prenom,
                    "email" => $email,
                    "id" => $id_utilisateur
                ]);
            }

            // On met à jour les infos en session (nom/prenom affichés dans la navbar)
            $_SESSION["nom"] = $nom;
            $_SESSION["prenom"] = $prenom;

            $succes = "Profil mis à jour avec succès.";
        }
    }
}

// On récupère les infos actuelles de l'utilisateur pour pré-remplir le formulaire
$stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE id_utilisateur = :id");
$stmt->execute(["id" => $id_utilisateur]);
$utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mon profil — 3iL FootBook</title>
  <meta name="description" content="Modifiez vos informations personnelles sur 3iL FootBook.">

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
            <a href="accueil.php">Accueil</a>
            <a href="apropos.php">À propos</a>
            <a href="accueil.php#planning">Planning</a>
            <a href="terrains.php">Nos terrains</a>
            <a href="reservation.php" class="nav-cta">Réserver</a>
            <a href="profil.php" class="active">Profil</a>
            <?php if ((int)($_SESSION["role"] ?? 1) === 0): ?>
                <a href="admin.php">⚙️ Admin</a>
            <?php endif; ?>
            <a href="deconnexion.php">Déconnexion</a>
        </nav>
    </header>

    <!-- ===================== FORMULAIRE PROFIL ===================== -->
    <section class="page-auth">
        <div class="carte-auth">
            <span class="sur-titre">Mon compte</span>
            <h1>Mon profil</h1>
            <p class="soustitre-auth">Modifie tes informations personnelles ci-dessous.</p>

            <?php if ($erreur): ?>
                <div class="message-erreur"><?= htmlspecialchars($erreur) ?></div>
            <?php endif; ?>

            <?php if ($succes): ?>
                <div class="message-succes"><?= htmlspecialchars($succes) ?></div>
            <?php endif; ?>

            <form method="post" class="formulaire-auth">
                <label for="nom">Nom</label>
                <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($utilisateur["nom"]) ?>" required>

                <label for="prenom">Prénom</label>
                <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($utilisateur["prenom"]) ?>" required>

                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($utilisateur["email"]) ?>" required>

                <label for="nouveau_mot_de_passe">
                    Nouveau mot de passe
                    <span style="font-weight:400; color:var(--gris-texte); font-size:0.82rem;">(laisser vide pour ne pas changer)</span>
                </label>
                <input type="password" id="nouveau_mot_de_passe" name="nouveau_mot_de_passe" placeholder="••••••••">

                <button type="submit" class="btn btn-primaire btn-auth">Enregistrer les modifications</button>
            </form>

            <p class="lien-secondaire"><a href="deconnexion.php">Se déconnecter</a></p>
        </div>
    </section>

    <!-- ===================== FOOTER ===================== -->
    <footer class="footer">
        <p><strong>3iL FootBook</strong> — Projet étudiant, 3iL Limoges</p>
        <p>Développé par Rayan, Mike, Matthias, Inès et Baptiste</p>
    </footer>

</body>

</html>