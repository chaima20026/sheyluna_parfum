<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

include "../connexion.php";

$result = mysqli_query($conn, "SELECT * FROM categories ORDER BY position, id");

$flash = "";
if (isset($_GET['msg'])) {
    $m = ['ajout' => "✅ Catégorie ajoutée.", 'modif' => "✅ Catégorie modifiée.", 'suppr' => "🗑️ Catégorie supprimée."];
    if (isset($m[$_GET['msg']])) $flash = "<div class='alert alert-success'>" . $m[$_GET['msg']] . "</div>";
}

$page_title = "Catégories";
$active = "categories";
include "admin_head.php";
?>
    <div class="header">
        <h1>Catégories (Explorez par Univers)</h1>
        <a href="categorie_form.php" class="btn btn-gold">+ Ajouter une catégorie</a>
    </div>

    <?php echo $flash; ?>

    <div class="table-container">
        <table>
            <thead>
                <tr><th>Image</th><th>Titre</th><th>Nombre</th><th>Visible</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($c = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><img src="../<?php echo htmlspecialchars($c['image']); ?>" alt="" class="thumb" onerror="this.style.visibility='hidden'"></td>
                            <td><strong><?php echo htmlspecialchars($c['titre']); ?></strong></td>
                            <td><?php echo htmlspecialchars((string)$c['nombre_texte']); ?></td>
                            <td>
                                <?php if ($c['actif']): ?><span class="badge badge-traitee">Oui</span><?php else: ?><span class="badge badge-off">Non</span><?php endif; ?>
                            </td>
                            <td>
                                <a href="categorie_form.php?id=<?php echo $c['id']; ?>" class="btn btn-edit">Modifier</a>
                                <a href="delete_categorie.php?id=<?php echo $c['id']; ?>" class="btn btn-delete" onclick="return confirm('Supprimer cette catégorie ?');">Supprimer</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="empty-state">Aucune catégorie.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php include "admin_foot.php"; ?>
