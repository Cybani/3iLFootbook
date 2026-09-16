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
    <title>Inscription - 3ilFootbook</title>
</head>
<body>
    <h1>Créer un compte</h1>

    <?php if ($erreur): ?>
        <p style="color:red;"><?= htmlspecialchars($erreur) ?></p>
    <?php endif; ?>

    <?php if ($succes): ?>
        <p style="color:green;"><?= htmlspecialchars($succes) ?></p>
    <?php endif; ?>

    <form method="post">
        <label>Nom :</label><br>
        <input type="text" name="nom" required><br><br>

        <label>Prénom :</label><br>
        <input type="text" name="prenom" required><br><br>

        <label>Email :</label><br>
        <input type="email" name="email" required><br><br>

        <label>Mot de passe :</label><br>
        <input type="password" name="mot_de_passe" required><br><br>

        <button type="submit">S'inscrire</button>
    </form>

    <p>Déjà un compte ? <a href="connexion.php">Se connecter</a></p>
</body>
</html>