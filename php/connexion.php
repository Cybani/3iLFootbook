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
 
            // Redirige vers la page d'accueil (à adapter selon ton projet)
            header("Location: accueil.php");
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
    <title>Connexion - 3ilFootbook</title>
</head>
<body>
    <h1>Se connecter</h1>
 
    <?php if ($erreur): ?>
        <p style="color:red;"><?= htmlspecialchars($erreur) ?></p>
    <?php endif; ?>
 
    <form method="post">
        <label>Email :</label><br>
        <input type="email" name="email" required><br><br>
 
        <label>Mot de passe :</label><br>
        <input type="password" name="mot_de_passe" required><br><br>
 
        <button type="submit">Se connecter</button>
    </form>
 
    <p>Pas encore de compte ? <a href="inscription.php">S'inscrire</a></p>
</body>
</html>
 
