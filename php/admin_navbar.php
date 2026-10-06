<?php
// Navbar commune de l'espace admin.
// Définir $pageActiveAdmin avant l'include : "dashboard", "users", "terrains" ou "reservations".
$pageActiveAdmin = $pageActiveAdmin ?? "";
?>
<input type="checkbox" id="nav-toggle" class="nav-toggle">
<header class="navbar">
  <a href="admin.php" class="logo">🏟️ 3iL <span>FootBook</span> · Admin</a>

  <label for="nav-toggle" class="nav-burger">
    <span></span><span></span><span></span>
  </label>

  <nav class="nav-links">
    <a href="admin.php" class="<?= $pageActiveAdmin === 'dashboard' ? 'active' : '' ?>">Tableau de bord</a>
    <a href="gestion_utilisateurs.php" class="<?= $pageActiveAdmin === 'users' ? 'active' : '' ?>">Utilisateurs</a>
    <a href="admin_terrains.php" class="<?= $pageActiveAdmin === 'terrains' ? 'active' : '' ?>">Terrains</a>
    <a href="admin_reservations.php" class="<?= $pageActiveAdmin === 'reservations' ? 'active' : '' ?>">Réservations</a>
    <a href="../accueil.php">Voir le site</a>
    <a href="deconnexion.php" class="nav-cta">Déconnexion</a>
  </nav>
</header>
