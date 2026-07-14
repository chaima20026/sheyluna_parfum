<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

include "connexion.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: admin_parametres.php");
    exit();
}

// Cles texte autorisees
$cles = [
    'promo_texte', 'social_whatsapp', 'social_facebook', 'social_instagram',
    'contact_adresse', 'contact_horaires', 'contact_tel', 'contact_tel_lien', 'contact_email',
    'footer_description', 'copyright',
    'banner_tag', 'banner_bouton', 'banner_titre', 'banner_desc', 'banner_prix', 'banner_prix_original',
];

$stmt = mysqli_prepare($conn, "INSERT INTO parametres (cle, valeur) VALUES (?, ?) ON DUPLICATE KEY UPDATE valeur = VALUES(valeur)");
foreach ($cles as $cle) {
    if (isset($_POST[$cle])) {
        $val = trim($_POST[$cle]);
        mysqli_stmt_bind_param($stmt, "ss", $cle, $val);
        mysqli_stmt_execute($stmt);
    }
}

// Image du bandeau (optionnelle)
if (isset($_FILES['banner_image_file']) && $_FILES['banner_image_file']['error'] === UPLOAD_ERR_OK) {
    $tmp  = $_FILES['banner_image_file']['tmp_name'];
    if ($_FILES['banner_image_file']['size'] <= 3 * 1024 * 1024) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $tmp);
        finfo_close($finfo);
        $exts = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
        if (isset($exts[$mime])) {
            $dossier = __DIR__ . "/images/produits";
            if (!is_dir($dossier)) mkdir($dossier, 0755, true);
            $fichier = 'banner-' . uniqid() . '.' . $exts[$mime];
            if (move_uploaded_file($tmp, $dossier . '/' . $fichier)) {
                $chemin = "images/produits/" . $fichier;
                $stmt = mysqli_prepare($conn, "INSERT INTO parametres (cle, valeur) VALUES ('banner_image', ?) ON DUPLICATE KEY UPDATE valeur = VALUES(valeur)");
                mysqli_stmt_bind_param($stmt, "s", $chemin);
                mysqli_stmt_execute($stmt);
            }
        }
    }
}

header("Location: admin_parametres.php?msg=ok");
exit();
?>
