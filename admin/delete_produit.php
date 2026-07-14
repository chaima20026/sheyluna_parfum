<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

include "../connexion.php";

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Recupere l'image pour la supprimer du disque si elle a ete uploadee
    $stmt = mysqli_prepare($conn, "SELECT image FROM produits WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);

    // Suppression en base
    $stmt = mysqli_prepare($conn, "DELETE FROM produits WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    // Supprime le fichier image seulement s'il est dans images/produits/
    if ($row && strpos($row['image'], 'images/produits/') === 0) {
        $chemin = __DIR__ . '/../' . $row['image'];
        if (is_file($chemin)) {
            @unlink($chemin);
        }
    }
}

header("Location: admin_produits.php?msg=suppr");
exit();
?>
