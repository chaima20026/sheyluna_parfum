<?php
// Point d'entree de l'administration (URL : /admin).
// Redirige vers le tableau de bord si connecte, sinon vers la page de connexion.
session_start();

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin.php");
} else {
    header("Location: admin_login.php");
}
exit();
?>
