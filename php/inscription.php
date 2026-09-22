<?php
require "bdd.php";

$erreur = "";
$succes = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = trim($_POST["nom"] ?? "");
    $prenom = trim($_POST["prenom"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $mot_de_passe = $_POST["mot_de_passe"] ?? "";

    if ($nom === "" || $prenom === "" || $email === "" || $mot_de_passe === "") {
        $erreur = "Merci de remplir tous les champs.";
    } else {
        // Vérifie si l'email existe déjà
        $stmt = $pdo->prepare("SELECT id_utilisateur FROM utilisateur WHERE email = :email");
        $stmt->execute(["email" => $email]);

        if ($stmt->fetch()) {
            $erreur = "Un compte existe déjà avec cet email.";
        } else {
            // On hache le mot de passe avant de l'enregistrer
            $hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare(
                "INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, role)
                 VALUES (:nom, :prenom, :email, :mot_de_passe, 'user')"
            );
            $stmt->execute([
                "nom" => $nom,
                "prenom" => $prenom,
                "email" => $email,
                "mot_de_passe" => $hash
            ]);

            $succes = "Compte créé avec succès ! Tu peux maintenant te connecter.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription — 3iL FootBook</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

    <!-- ===================== NAVIGATION ===================== -->
    <input type="checkbox" id="nav-toggle" class="nav-toggle">
    <header class="navbar">
        <a href="../accueil.html" class="logo">🏟️ 3iL <span>FootBook</span></a>

        <label for="nav-toggle" class="nav-burger">
            <span></span><span></span><span></span>
        </label>

        <nav class="nav-links">
            <a href="../accueil.html">Accueil</a>
            <a href="../apropos.html">À propos</a>
            <a href="../accueil.html#terrains">Nos terrains</a>
            <a href="inscription.php" class="nav-cta">Inscription</a>
            <a href="connexion.php">Connexion</a>
        </nav>
    </header>

    <!-- ===================== FORMULAIRE INSCRIPTION ===================== -->
    <section class="page-auth">
        <div class="carte-auth">
            <span class="sur-titre">Bienvenue</span>
            <h1>Créer un compte</h1>
            <p class="soustitre-auth">Rejoins 3iL FootBook pour réserver tes créneaux en quelques clics.</p>

            <?php if ($erreur): ?>
                <div class="message-erreur"><?= htmlspecialchars($erreur) ?></div>
            <?php endif; ?>

            <?php if ($succes): ?>
                <div class="message-succes"><?= htmlspecialchars($succes) ?></div>
            <?php endif; ?>

            <form method="post" class="formulaire-auth">
                <label for="nom">Nom</label>
                <input type="text" id="nom" name="nom" placeholder="Dupont" required>

                <label for="prenom">Prénom</label>
                <input type="text" id="prenom" name="prenom" placeholder="Camille" required>

                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="prenom.nom@3il.fr" required>

                <label for="mot_de_passe">Mot de passe</label>
                <input type="password" id="mot_de_passe" name="mot_de_passe" placeholder="••••••••" required>

                <button type="submit" class="btn btn-primaire btn-auth">S'inscrire</button>
            </form>

            <p class="lien-secondaire">Déjà un compte ? <a href="connexion.php">Se connecter</a></p>
        </div>
    </section>

    <!-- ===================== FOOTER ===================== -->
    <footer class="footer">
        <p><strong>3iL FootBook</strong> — Projet étudiant, 3iL Limoges</p>
        <p>Développé par Rayan, Mike, Mathias, Inès et Baptiste</p>
    </footer>

</body>
</html>
