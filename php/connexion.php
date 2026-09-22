<?php
require "bdd.php";

$erreur = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $mot_de_passe = $_POST["mot_de_passe"] ?? "";

    if ($email === "" || $mot_de_passe === "") {
        $erreur = "Merci de remplir tous les champs.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE email = :email");
        $stmt->execute(["email" => $email]);
        $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($utilisateur && password_verify($mot_de_passe, $utilisateur["mot_de_passe"])) {
            // Connexion réussie : on stocke les infos utiles en session
            $_SESSION["id_utilisateur"] = $utilisateur["id_utilisateur"];
            $_SESSION["nom"] = $utilisateur["nom"];
            $_SESSION["prenom"] = $utilisateur["prenom"];
            $_SESSION["role"] = $utilisateur["role"];

           // Redirige vers la page d'accueil
header("Location: ../accueil.php");
exit;
        } else {
            $erreur = "Email ou mot de passe incorrect.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — 3iL FootBook</title>

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
            <a href="../accueil.html#terrains">Nos terrains</a>
            <a href="connexion.php" class="nav-cta">Connexion</a>
        </nav>
    </header>

    <!-- ===================== FORMULAIRE CONNEXION ===================== -->
    <section class="page-auth">
        <div class="carte-auth">
            <span class="sur-titre">Bon retour</span>
            <h1>Se connecter</h1>
            <p class="soustitre-auth">Accède à ton compte 3iL FootBook pour réserver un terrain.</p>

            <?php if ($erreur): ?>
                <div class="message-erreur"><?= htmlspecialchars($erreur) ?></div>
            <?php endif; ?>

            <form method="post" class="formulaire-auth">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="prenom.nom@3il.fr" required>

                <label for="mot_de_passe">Mot de passe</label>
                <input type="password" id="mot_de_passe" name="mot_de_passe" placeholder="••••••••" required>

                <button type="submit" class="btn btn-primaire btn-auth">Se connecter</button>
            </form>

            <p class="lien-secondaire">Pas encore de compte ? <a href="inscription.php">S'inscrire</a></p>
        </div>
    </section>

    <!-- ===================== FOOTER ===================== -->
    <footer class="footer">
        <p><strong>3iL FootBook</strong> — Projet étudiant, 3iL Limoges</p>
        <p>Développé par Rayan, Mike, Mathias, Inès et Baptiste</p>
    </footer>

</body>
</html>
