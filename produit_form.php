<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

include "connexion.php";

// Mode edition si un id est fourni
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$edition = $id > 0;

// Valeurs par defaut (ajout)
$p = [
    'nom' => '', 'genre' => 'Femme', 'categorie' => '', 'prix' => '', 'prix_original' => '',
    'image' => '', 'badge' => '', 'badge_type' => '', 'note_courte' => '', 'description' => '',
    'notes_liste' => '', 'note_etoiles' => '5.0', 'avis' => '', 'position' => '0', 'actif' => 1,
    'est_bestseller' => 0, 'bestseller_rang' => '0',
];

if ($edition) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM produits WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    if (!$row) { header("Location: admin_produits.php"); exit(); }
    $p = $row;
}

// Categories deja utilisees (pour la liste automatique du formulaire)
$cats_existantes = [];
$rc = mysqli_query($conn, "SELECT DISTINCT categorie FROM produits WHERE categorie <> '' ORDER BY categorie");
if ($rc) { while ($row = mysqli_fetch_assoc($rc)) $cats_existantes[] = $row['categorie']; }

$page_title = $edition ? "Modifier un produit" : "Ajouter un produit";
$active = "produits";
include "admin_head.php";
?>
    <div class="header">
        <h1><?php echo $edition ? "Modifier le produit" : "Ajouter un produit"; ?></h1>
        <a href="admin_produits.php" class="cancel-link">← Retour à la liste</a>
    </div>

    <?php if (isset($_GET['err'])): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($_GET['err']); ?></div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST" action="produit_save.php" enctype="multipart/form-data">
            <?php if ($edition): ?>
                <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
                <input type="hidden" name="image_actuelle" value="<?php echo htmlspecialchars($p['image']); ?>">
            <?php endif; ?>

            <div class="form-row">
                <div class="form-group">
                    <label>Nom du produit *</label>
                    <input type="text" name="nom" class="form-control" required value="<?php echo htmlspecialchars($p['nom']); ?>">
                </div>
                <div class="form-group">
                    <label>Genre *</label>
                    <select name="genre" class="form-control" required>
                        <option value="Femme" <?php echo $p['genre']==='Femme'?'selected':''; ?>>Femme</option>
                        <option value="Homme" <?php echo $p['genre']==='Homme'?'selected':''; ?>>Homme</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Catégorie *</label>
                <input type="text" name="categorie" class="form-control" required placeholder="Ex: Eau de Parfum — Femme" value="<?php echo htmlspecialchars($p['categorie']); ?>" list="cat_liste" autocomplete="off">
                <datalist id="cat_liste">
                    <?php foreach ($cats_existantes as $cat) echo '<option value="' . htmlspecialchars($cat) . '"></option>'; ?>
                </datalist>
                <div class="form-hint">Choisissez dans la liste ou saisissez une nouvelle catégorie.</div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Prix *</label>
                    <input type="text" name="prix" class="form-control" required placeholder="Ex: 40 MAD" value="<?php echo htmlspecialchars($p['prix']); ?>">
                </div>
                <div class="form-group">
                    <label>Prix barré (optionnel)</label>
                    <input type="text" name="prix_original" class="form-control" placeholder="Ex: 70 MAD" value="<?php echo htmlspecialchars($p['prix_original'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Image du produit <?php echo $edition ? '(laisser vide pour garder l\'actuelle)' : '*'; ?></label>
                <input type="file" name="image" class="form-control" accept="image/*" <?php echo $edition ? '' : 'required'; ?>>
                <div class="form-hint">Formats acceptés : JPG, PNG, WEBP, GIF — 3 Mo max.</div>
                <?php if ($edition && !empty($p['image'])): ?>
                    <div class="current-thumb"><img src="<?php echo htmlspecialchars($p['image']); ?>" class="thumb" onerror="this.style.display='none'"></div>
                <?php endif; ?>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Badge (texte, optionnel)</label>
                    <input type="text" name="badge" class="form-control" placeholder="Ex: Nouveau, Hot, 🔥 Best-seller" value="<?php echo htmlspecialchars($p['badge'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Couleur du badge</label>
                    <select name="badge_type" class="form-control">
                        <option value="" <?php echo empty($p['badge_type'])?'selected':''; ?>>Aucune</option>
                        <option value="new" <?php echo $p['badge_type']==='new'?'selected':''; ?>>Vert (Nouveau)</option>
                        <option value="hot" <?php echo $p['badge_type']==='hot'?'selected':''; ?>>Rouge (Hot)</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Petit texte sous le nom (carte)</label>
                <input type="text" name="note_courte" class="form-control" placeholder="Ex: La douceur de la vanille" value="<?php echo htmlspecialchars($p['note_courte'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label>Description (fenêtre de détail)</label>
                <textarea name="description" class="form-control"><?php echo htmlspecialchars($p['description'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label>Notes olfactives (séparées par des virgules)</label>
                <input type="text" name="notes_liste" class="form-control" placeholder="Ex: Rose, Jasmin, Musc" value="<?php echo htmlspecialchars($p['notes_liste'] ?? ''); ?>">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Note (étoiles, 0 à 5)</label>
                    <input type="number" name="note_etoiles" class="form-control" min="0" max="5" step="0.5" value="<?php echo htmlspecialchars($p['note_etoiles']); ?>">
                </div>
                <div class="form-group">
                    <label>Nombre d'avis (texte)</label>
                    <input type="text" name="avis" class="form-control" placeholder="Ex: 127 avis" value="<?php echo htmlspecialchars($p['avis'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Position (ordre d'affichage)</label>
                    <input type="number" name="position" class="form-control" value="<?php echo htmlspecialchars($p['position']); ?>">
                </div>
            </div>

            <div class="form-group">
                <label><input type="checkbox" name="actif" value="1" <?php echo $p['actif'] ? 'checked' : ''; ?>> Visible sur le site</label>
            </div>

            <div class="form-row" style="align-items:center;background:#FAFAF5;border-radius:10px;padding:15px 18px;">
                <div class="form-group" style="margin-bottom:0;">
                    <label><input type="checkbox" name="est_bestseller" value="1" <?php echo !empty($p['est_bestseller']) ? 'checked' : ''; ?>> Mettre en avant dans les « Best-sellers »</label>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>Rang best-seller (ordre)</label>
                    <input type="number" name="bestseller_rang" class="form-control" value="<?php echo htmlspecialchars($p['bestseller_rang']); ?>">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-gold"><?php echo $edition ? "Enregistrer les modifications" : "Ajouter le produit"; ?></button>
                <a href="admin_produits.php" class="cancel-link">Annuler</a>
            </div>
        </form>
    </div>
<?php include "admin_foot.php"; ?>
