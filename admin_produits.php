<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

include "connexion.php";

$result = mysqli_query($conn, "SELECT * FROM produits ORDER BY genre, position, id");

// Message de retour (apres ajout / modif / suppression)
$flash = "";
if (isset($_GET['msg'])) {
    $messages = [
        'ajout'      => "✅ Produit ajouté avec succès.",
        'modif'      => "✅ Produit modifié avec succès.",
        'suppr'      => "🗑️ Produit supprimé.",
    ];
    if (isset($messages[$_GET['msg']])) {
        $flash = "<div class='alert alert-success'>" . $messages[$_GET['msg']] . "</div>";
    }
}

$page_title = "Produits";
$active = "produits";
include "admin_head.php";
?>
    <div class="header">
        <h1>Gestion des Produits</h1>
        <a href="produit_form.php" class="btn btn-gold">+ Ajouter un produit</a>
    </div>

    <?php echo $flash; ?>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Nom</th>
                    <th>Genre</th>
                    <th>Prix</th>
                    <th>Badge</th>
                    <th>Visible</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($p = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><img src="<?php echo htmlspecialchars($p['image']); ?>" alt="" class="thumb" onerror="this.style.visibility='hidden'"></td>
                            <td>
                                <strong><?php echo htmlspecialchars($p['nom']); ?></strong><br>
                                <span style="font-size:0.8rem;color:#999;"><?php echo htmlspecialchars($p['categorie']); ?></span>
                            </td>
                            <td><?php echo htmlspecialchars($p['genre']); ?></td>
                            <td>
                                <?php echo htmlspecialchars($p['prix']); ?>
                                <?php if (!empty($p['prix_original'])): ?>
                                    <br><span style="font-size:0.8rem;color:#bbb;text-decoration:line-through;"><?php echo htmlspecialchars($p['prix_original']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($p['badge'])): ?>
                                    <span class="badge badge-<?php echo $p['badge_type'] === 'hot' ? 'hot' : 'new'; ?>"><?php echo htmlspecialchars($p['badge']); ?></span>
                                <?php else: ?>
                                    <span style="color:#ccc;">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($p['actif']): ?>
                                    <span class="badge badge-traitee">Oui</span>
                                <?php else: ?>
                                    <span class="badge badge-off">Non</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="produit_form.php?id=<?php echo $p['id']; ?>" class="btn btn-edit">Modifier</a>
                                <a href="delete_produit.php?id=<?php echo $p['id']; ?>" class="btn btn-delete" onclick="return confirm('Supprimer définitivement ce produit ?');">Supprimer</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="empty-state">Aucun produit. Cliquez sur « Ajouter un produit ».</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php include "admin_foot.php"; ?>
