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
    <title>Mon profil - 3ilFootbook</title>
</head>
<body>
    <?php include "navbar.php"; ?>

    <div style="padding:30px; max-width:400px;">
        <h1>Mon profil</h1>

        <?php if ($erreur): ?>
            <p style="color:red;"><?= htmlspecialchars($erreur) ?></p>
        <?php endif; ?>

        <?php if ($succes): ?>
            <p style="color:green;"><?= htmlspecialchars($succes) ?></p>
        <?php endif; ?>

        <form method="post">
            <label>Nom :</label><br>
            <input type="text" name="nom" value="<?= htmlspecialchars($utilisateur["nom"]) ?>" required><br><br>

            <label>Prénom :</label><br>
            <input type="text" name="prenom" value="<?= htmlspecialchars($utilisateur["prenom"]) ?>" required><br><br>

            <label>Email :</label><br>
            <input type="email" name="email" value="<?= htmlspecialchars($utilisateur["email"]) ?>" required><br><br>

            <label>Nouveau mot de passe (laisser vide pour ne pas le changer) :</label><br>
            <input type="password" name="nouveau_mot_de_passe"><br><br>

            <button type="submit">Enregistrer les modifications</button>
        </form>
    </div>
</body>
</html>