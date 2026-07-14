<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

include "connexion.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: admin_produits.php");
    exit();
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$edition = $id > 0;

// Renvoie vers le formulaire avec un message d'erreur
function erreur($msg, $id) {
    $suffix = $id > 0 ? "?id=$id&err=" : "?err=";
    header("Location: produit_form.php" . $suffix . urlencode($msg));
    exit();
}

// --- Champs texte ---
$nom          = trim($_POST['nom'] ?? '');
$genre        = ($_POST['genre'] ?? 'Femme') === 'Homme' ? 'Homme' : 'Femme';
$categorie    = trim($_POST['categorie'] ?? '');
$prix         = trim($_POST['prix'] ?? '');
$prix_orig    = trim($_POST['prix_original'] ?? '');
$badge        = trim($_POST['badge'] ?? '');
$badge_type   = in_array($_POST['badge_type'] ?? '', ['new', 'hot'], true) ? $_POST['badge_type'] : null;
$note_courte  = trim($_POST['note_courte'] ?? '');
$description  = trim($_POST['description'] ?? '');
$notes_liste  = trim($_POST['notes_liste'] ?? '');
$note_etoiles = (float) ($_POST['note_etoiles'] ?? 5);
if ($note_etoiles < 0) $note_etoiles = 0;
if ($note_etoiles > 5) $note_etoiles = 5;
$avis         = trim($_POST['avis'] ?? '');
$position     = (int) ($_POST['position'] ?? 0);
$actif        = isset($_POST['actif']) ? 1 : 0;
$est_bestseller  = isset($_POST['est_bestseller']) ? 1 : 0;
$bestseller_rang = (int) ($_POST['bestseller_rang'] ?? 0);
$prix_orig    = $prix_orig === '' ? null : $prix_orig;
$badge        = $badge === '' ? null : $badge;
$note_courte  = $note_courte === '' ? null : $note_courte;
$notes_liste  = $notes_liste === '' ? null : $notes_liste;
$avis         = $avis === '' ? null : $avis;

if ($nom === '' || $categorie === '' || $prix === '') {
    erreur("Le nom, la catégorie et le prix sont obligatoires.", $id);
}

// --- Gestion de l'image ---
$image = $_POST['image_actuelle'] ?? '';  // conservee par defaut en edition

if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $tmp  = $_FILES['image']['tmp_name'];
    $size = $_FILES['image']['size'];
    if ($size > 3 * 1024 * 1024) {
        erreur("L'image dépasse 3 Mo.", $id);
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $tmp);
    finfo_close($finfo);
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    if (!isset($extensions[$mime])) {
        erreur("Format d'image non supporté (JPG, PNG, WEBP ou GIF uniquement).", $id);
    }
    $dossier = __DIR__ . "/images/produits";
    if (!is_dir($dossier)) {
        mkdir($dossier, 0755, true);
    }
    // Nom de fichier sur : slug du produit + identifiant unique
    $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $nom)));
    $slug = trim($slug, '-') ?: 'produit';
    $fichier = $slug . '-' . uniqid() . '.' . $extensions[$mime];
    if (!move_uploaded_file($tmp, $dossier . '/' . $fichier)) {
        erreur("Échec de l'enregistrement de l'image.", $id);
    }
    $image = "images/produits/" . $fichier;
} elseif (!$edition) {
    // Ajout sans image
    erreur("Veuillez choisir une image pour le produit.", $id);
}

// --- Enregistrement ---
if ($edition) {
    $stmt = mysqli_prepare($conn,
        "UPDATE produits SET nom=?, genre=?, categorie=?, prix=?, prix_original=?, image=?, badge=?, badge_type=?,
         note_courte=?, description=?, notes_liste=?, note_etoiles=?, avis=?, position=?, actif=?, est_bestseller=?, bestseller_rang=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "sssssssssssdsiiiii",
        $nom, $genre, $categorie, $prix, $prix_orig, $image, $badge, $badge_type,
        $note_courte, $description, $notes_liste, $note_etoiles, $avis, $position, $actif, $est_bestseller, $bestseller_rang, $id);
    mysqli_stmt_execute($stmt);
    header("Location: admin_produits.php?msg=modif");
} else {
    $stmt = mysqli_prepare($conn,
        "INSERT INTO produits
         (nom, genre, categorie, prix, prix_original, image, badge, badge_type, note_courte, description, notes_liste, note_etoiles, avis, position, actif, est_bestseller, bestseller_rang)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    mysqli_stmt_bind_param($stmt, "sssssssssssdsiiii",
        $nom, $genre, $categorie, $prix, $prix_orig, $image, $badge, $badge_type,
        $note_courte, $description, $notes_liste, $note_etoiles, $avis, $position, $actif, $est_bestseller, $bestseller_rang);
    mysqli_stmt_execute($stmt);
    header("Location: admin_produits.php?msg=ajout");
}
exit();
?>
