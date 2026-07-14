<?php
$conn = mysqli_connect("localhost", "root", "", "sheyluna_db");

if (!$conn) {
    die("Erreur de connexion : " . mysqli_connect_error());
}
?>