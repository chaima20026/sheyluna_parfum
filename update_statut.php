<?php
session_start();

// Verification de connexion admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

include "connexion.php";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id'], $_POST['statut'])) {
    $id = intval($_POST['id']);

    // On n'accepte que des valeurs de statut connues
    $statuts_valides = ['En attente', 'Traitée', 'Annulée'];
    $statut = in_array($_POST['statut'], $statuts_valides, true) ? $_POST['statut'] : null;

    if ($id > 0 && $statut !== null) {
        $stmt = mysqli_prepare($conn, "UPDATE commandes SET statut = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "si", $statut, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

header("Location: admin.php");
exit();
?>
