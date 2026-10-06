<?php
// =====================================================================
// Garde de sécurité commune à tout l'espace administrateur.
// À inclure EN PREMIER dans chaque page admin : require "admin_garde.php";
// Fournit : $pdo, jeton_csrf(), csrf_valide(), e().
// =====================================================================
require __DIR__ . "/bdd.php";

// 1) Doit être connecté
if (!isset($_SESSION["id_utilisateur"])) {
    header("Location: connexion.php");
    exit;
}

// 2) Re-vérifier le rôle en base : une rétrogradation prend effet immédiatement
$stmt = $pdo->prepare("SELECT role FROM utilisateur WHERE id_utilisateur = :id");
$stmt->execute(["id" => (int)$_SESSION["id_utilisateur"]]);
$roleBdd = $stmt->fetchColumn();

if ($roleBdd === false || (int)$roleBdd !== 0) {
    // Connecté mais pas administrateur -> retour à l'espace utilisateur
    $_SESSION["role"] = $roleBdd === false ? ($_SESSION["role"] ?? 1) : (int)$roleBdd;
    header("Location: ../accueil.php");
    exit;
}

// 3) Jeton CSRF partagé pour les actions d'administration
if (empty($_SESSION["csrf_admin"])) {
    $_SESSION["csrf_admin"] = bin2hex(random_bytes(32));
}

function jeton_csrf(): string
{
    return $_SESSION["csrf_admin"];
}

function csrf_valide($token): bool
{
    return is_string($token) && hash_equals($_SESSION["csrf_admin"], $token);
}

// Échappement HTML (protégé contre une double déclaration)
if (!function_exists("e")) {
    function e($valeur): string
    {
        return htmlspecialchars((string)$valeur, ENT_QUOTES, "UTF-8");
    }
}
