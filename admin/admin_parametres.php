<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

include "../connexion.php";
include "../params.php";

$flash = "";
if (isset($_GET['msg']) && $_GET['msg'] === 'ok') {
    $flash = "<div class='alert alert-success'>✅ Paramètres enregistrés.</div>";
}

// Champ texte
function champ($cle, $label, $placeholder = '') {
    $val = htmlspecialchars(param($cle));
    echo "<div class='form-group'><label>" . htmlspecialchars($label) . "</label>";
    echo "<input type='text' name='$cle' class='form-control' value='$val' placeholder='" . htmlspecialchars($placeholder) . "'></div>";
}
// Champ zone de texte
function champ_zone($cle, $label) {
    $val = htmlspecialchars(param($cle));
    echo "<div class='form-group'><label>" . htmlspecialchars($label) . "</label>";
    echo "<textarea name='$cle' class='form-control'>$val</textarea></div>";
}

$page_title = "Paramètres du site";
$active = "parametres";
include "admin_head.php";
?>
    <div class="header">
        <h1>Paramètres du site</h1>
    </div>

    <?php echo $flash; ?>

    <form method="POST" action="parametres_save.php" enctype="multipart/form-data">
        <div class="form-card" style="margin-bottom:25px;">
            <h3 style="margin-top:0;font-family:'Playfair Display',serif;">Bandeau promo (haut de page)</h3>
            <?php champ('promo_texte', "Texte de la barre promo"); ?>
        </div>

        <div class="form-card" style="margin-bottom:25px;">
            <h3 style="margin-top:0;font-family:'Playfair Display',serif;">Réseaux sociaux</h3>
            <?php champ('social_whatsapp', "Lien WhatsApp"); ?>
            <?php champ('social_facebook', "Lien Facebook"); ?>
            <?php champ('social_instagram', "Lien Instagram"); ?>
        </div>

        <div class="form-card" style="margin-bottom:25px;">
            <h3 style="margin-top:0;font-family:'Playfair Display',serif;">Contact</h3>
            <div class="form-row">
                <?php champ('contact_adresse', "Adresse", "Marrakech, Maroc"); ?>
                <?php champ('contact_horaires', "Horaires", "Lun-Sam: 9h-19h"); ?>
            </div>
            <div class="form-row">
                <?php champ('contact_tel', "Téléphone (affiché)", "+212 7 79 21 54 43"); ?>
                <?php champ('contact_tel_lien', "Téléphone (lien, sans espaces)", "+212779215443"); ?>
            </div>
            <?php champ('contact_email', "Email", "contact@sheyluna.com"); ?>
        </div>

        <div class="form-card" style="margin-bottom:25px;">
            <h3 style="margin-top:0;font-family:'Playfair Display',serif;">Pied de page</h3>
            <?php champ_zone('footer_description', "Description de la marque"); ?>
            <?php champ('copyright', "Copyright"); ?>
        </div>

        <div class="form-card" style="margin-bottom:25px;">
            <h3 style="margin-top:0;font-family:'Playfair Display',serif;">Bandeau « Coffret Signature »</h3>
            <div class="form-row">
                <?php champ('banner_tag', "Étiquette", "✨ Édition Limitée"); ?>
                <?php champ('banner_bouton', "Texte du bouton"); ?>
            </div>
            <?php champ('banner_titre', "Titre"); ?>
            <?php champ_zone('banner_desc', "Description"); ?>
            <div class="form-row">
                <?php champ('banner_prix', "Prix", "129 MAD"); ?>
                <?php champ('banner_prix_original', "Prix barré (optionnel)", "169 MAD"); ?>
            </div>
            <div class="form-group">
                <label>Image du bandeau (laisser vide pour garder l'actuelle)</label>
                <input type="file" name="banner_image_file" class="form-control" accept="image/*">
                <?php if (param('banner_image')): ?>
                    <div class="current-thumb"><img src="../<?php echo htmlspecialchars(param('banner_image')); ?>" class="thumb" onerror="this.style.display='none'"></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-gold">Enregistrer les paramètres</button>
        </div>
    </form>
<?php include "admin_foot.php"; ?>
