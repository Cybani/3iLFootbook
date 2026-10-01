<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$connecte = isset($_SESSION['connecte']) && $_SESSION['connecte'] === true;
$admin = $connecte && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
?>

<nav class="navbar">

    <!-- Logo -->
    <a href="accueil.php" class="logo">
        3iL <span>Footbook</span>
    </a>

    <!-- Bouton burger mobile -->
    <input type="checkbox" id="nav-toggle" class="nav-toggle">

    <label for="nav-toggle" class="nav-burger">
        <span></span>
        <span></span>
        <span></span>
    </label>

    <!-- Liens -->
    <div class="nav-links">

        <a href="apropos.html">
            À propos
        </a>

        <a href="php/terrains.php">
            Nos terrains
        </a>

        <a href="php/reservation.php" class="nav-cta">
            Réserver
        </a>

        <?php if ($connecte): ?>

          <a href="accueil.php" class="active">
            Accueil
          </a>

            <a href="php/profil.php">
                Profil
            </a>

            <?php if ($admin): ?>
                <a href="php/gestion_utilisateurs.php">
                    Gestion
                </a>
            <?php endif; ?>

            <a href="php/deconnexion.php">
                Déconnexion
            </a>

        <?php else: ?>

            <a href="php/connexion.php">
                Connexion
            </a>

            <a href="php/inscription.php">
                Inscription
            </a>

        <?php endif; ?>

    </div>

</nav>