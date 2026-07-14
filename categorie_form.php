<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

include "connexion.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$edition = $id > 0;

$c = ['titre' => '', 'nombre_texte' => '', 'image' => '', 'position' => '0', 'actif' => 1];

if ($edition) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM categories WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    if (!$row) { header("Location: admin_categories.php"); exit(); }
    $c = $row;
}

$page_title = $edition ? "Modifier une catégorie" : "Ajouter une catégorie";
$active = "categories";
include "admin_head.php";
?>
    <div class="header">
        <h1><?php echo $edition ? "Modifier la catégorie" : "Ajouter une catégorie"; ?></h1>
        <a href="admin_categories.php" class="cancel-link">← Retour</a>
    </div>

    <?php if (isset($_GET['err'])): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($_GET['err']); ?></div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST" action="categorie_save.php" enctype="multipart/form-data">
            <?php if ($edition): ?>
                <input type="hidden" name="id" value="<?php echo (int)$c['id']; ?>">
                <input type="hidden" name="image_actuelle" value="<?php echo htmlspecialchars($c['image']); ?>">
            <?php endif; ?>

            <div class="form-row">
                <div class="form-group">
                    <label>Titre *</label>
                    <input type="text" name="titre" class="form-control" required placeholder="Ex: Floral" value="<?php echo htmlspecialchars($c['titre']); ?>">
                </div>
                <div class="form-group">
                    <label>Nombre (texte)</label>
                    <input type="text" name="nombre_texte" class="form-control" placeholder="Ex: 12 parfums" value="<?php echo htmlspecialchars((string)$c['nombre_texte']); ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Image <?php echo $edition ? '(laisser vide pour garder l\'actuelle)' : '*'; ?></label>
                <input type="file" name="image" class="form-control" accept="image/*" <?php echo $edition ? '' : 'required'; ?>>
                <?php if ($edition && !empty($c['image'])): ?>
                    <div class="current-thumb"><img src="<?php echo htmlspecialchars($c['image']); ?>" class="thumb" onerror="this.style.display='none'"></div>
                <?php endif; ?>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Position</label>
                    <input type="number" name="position" class="form-control" value="<?php echo htmlspecialchars($c['position']); ?>">
                </div>
                <div class="form-group">
                    <label><input type="checkbox" name="actif" value="1" <?php echo $c['actif'] ? 'checked' : ''; ?>> Visible sur le site</label>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-gold"><?php echo $edition ? "Enregistrer" : "Ajouter"; ?></button>
                <a href="admin_categories.php" class="cancel-link">Annuler</a>
            </div>
        </form>
    </div>
<?php include "admin_foot.php"; ?>
