<?php
$conn = mysqli_connect("localhost", "root", "", "sheyluna_db");

if (!$conn) {
    die("Erreur de connexion : " . mysqli_connect_error());
}

// Force l'encodage UTF-8 (indispensable pour les accents : é, è, à...)
mysqli_set_charset($conn, "utf8mb4");
?>