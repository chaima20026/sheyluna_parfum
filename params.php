<?php
// Charge les reglages du site (table parametres) dans $SITE + fonction param().
if (!isset($conn)) { include __DIR__ . "/connexion.php"; }

$SITE = [];
$rp = mysqli_query($conn, "SELECT cle, valeur FROM parametres");
if ($rp) {
    while ($row = mysqli_fetch_assoc($rp)) {
        $SITE[$row['cle']] = $row['valeur'];
    }
}

// Renvoie la valeur d'un reglage, ou une valeur par defaut si absente.
function param($cle, $defaut = '') {
    global $SITE;
    return (isset($SITE[$cle]) && $SITE[$cle] !== null && $SITE[$cle] !== '') ? $SITE[$cle] : $defaut;
}
