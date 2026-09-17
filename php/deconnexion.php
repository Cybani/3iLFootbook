<?php
require "bdd.php";

// On vide et détruit la session
$_SESSION = [];
session_destroy();

header("Location: connexion.php");
exit;