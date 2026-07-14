<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

include "connexion.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: admin_categories.php");
    exit();
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$edition = $id > 0;

function erreur_cat($msg, $id) {
    $suffix = $id > 0 ? "?id=$id&err=" : "?err=";
    header("Location: categorie_form.php" . $suffix . urlencode($msg));
    exit();
}

$titre        = trim($_POST['titre'] ?? '');
$nombre_texte = trim($_POST['nombre_texte'] ?? '');
$position     = (int) ($_POST['position'] ?? 0);
$actif        = isset($_POST['actif']) ? 1 : 0;
$nombre_texte = $nombre_texte === '' ? null : $nombre_texte;

if ($titre === '') {
    erreur_cat("Le titre est obligatoire.", $id);
}

// Image
$image = $_POST['image_actuelle'] ?? '';
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $tmp = $_FILES['image']['tmp_name'];
    if ($_FILES['image']['size'] > 3 * 1024 * 1024) {
        erreur_cat("L'image dépasse 3 Mo.", $id);
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $tmp);
    finfo_close($finfo);
    $exts = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    if (!isset($exts[$mime])) {
        erreur_cat("Format d'image non supporté.", $id);
    }
    $dossier = __DIR__ . "/images/produits";
    if (!is_dir($dossier)) mkdir($dossier, 0755, true);
    $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $titre)));
    $slug = trim($slug, '-') ?: 'categorie';
    $fichier = 'cat-' . $slug . '-' . uniqid() . '.' . $exts[$mime];
    if (!move_uploaded_file($tmp, $dossier . '/' . $fichier)) {
        erreur_cat("Échec de l'enregistrement de l'image.", $id);
    }
    $image = "images/produits/" . $fichier;
} elseif (!$edition) {
    erreur_cat("Veuillez choisir une image.", $id);
}

if ($edition) {
    $stmt = mysqli_prepare($conn, "UPDATE categories SET titre=?, nombre_texte=?, image=?, position=?, actif=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "sssiii", $titre, $nombre_texte, $image, $position, $actif, $id);
    mysqli_stmt_execute($stmt);
    header("Location: admin_categories.php?msg=modif");
} else {
    $stmt = mysqli_prepare($conn, "INSERT INTO categories (titre, nombre_texte, image, position, actif) VALUES (?,?,?,?,?)");
    mysqli_stmt_bind_param($stmt, "sssii", $titre, $nombre_texte, $image, $position, $actif);
    mysqli_stmt_execute($stmt);
    header("Location: admin_categories.php?msg=ajout");
}
exit();
?>
