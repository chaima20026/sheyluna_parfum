<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

include "connexion.php";

// Recherche (nom, telephone ou parfum)
$recherche = isset($_GET['q']) ? trim($_GET['q']) : "";

if ($recherche !== "") {
    $like = "%" . $recherche . "%";
    $stmt = mysqli_prepare(
        $conn,
        "SELECT * FROM commandes
         WHERE nom_client LIKE ? OR telephone LIKE ? OR parfum LIKE ?
         ORDER BY date_commande DESC, id DESC"
    );
    mysqli_stmt_bind_param($stmt, "sss", $like, $like, $like);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, "SELECT * FROM commandes ORDER BY date_commande DESC, id DESC");
}

// Compteurs par statut
$stats = ['total' => 0, 'En attente' => 0, 'Traitée' => 0, 'Annulée' => 0];
$res_stats = mysqli_query($conn, "SELECT statut, COUNT(*) AS n FROM commandes GROUP BY statut");
if ($res_stats) {
    while ($s = mysqli_fetch_assoc($res_stats)) {
        $stats[$s['statut']] = (int) $s['n'];
        $stats['total'] += (int) $s['n'];
    }
}

$page_title = "Tableau de Bord";
$active = "commandes";
include "admin_head.php";
?>
    <div class="header">
        <h1>Gestion des Commandes</h1>
        <div>Connecté en tant qu'Administrateur</div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['total']; ?></div>
            <div class="stat-label">Total commandes</div>
        </div>
        <div class="stat-card attente">
            <div class="stat-number"><?php echo $stats['En attente']; ?></div>
            <div class="stat-label">En attente</div>
        </div>
        <div class="stat-card traitee">
            <div class="stat-number"><?php echo $stats['Traitée']; ?></div>
            <div class="stat-label">Traitées</div>
        </div>
        <div class="stat-card annulee">
            <div class="stat-number"><?php echo $stats['Annulée']; ?></div>
            <div class="stat-label">Annulées</div>
        </div>
    </div>

    <form method="GET" action="admin.php" class="search-form" style="margin-bottom: 25px;">
        <input type="text" name="q" placeholder="Rechercher un nom, téléphone ou parfum..." value="<?php echo htmlspecialchars($recherche); ?>">
        <button type="submit">Rechercher</button>
        <?php if ($recherche !== ""): ?>
            <a href="admin.php" class="reset-link">✕ Réinitialiser</a>
        <?php endif; ?>
    </form>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Nom du Client</th>
                    <th>Téléphone</th>
                    <th>Parfum Choisi</th>
                    <th>Statut</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <?php
                            // Correspondance statut -> classe CSS du badge
                            $statut = $row['statut'] ?? 'En attente';
                            $badge_class = 'badge-attente';
                            if ($statut === 'Traitée') $badge_class = 'badge-traitee';
                            elseif ($statut === 'Annulée') $badge_class = 'badge-annulee';

                            $date_aff = !empty($row['date_commande'])
                                ? date('d/m/Y H:i', strtotime($row['date_commande']))
                                : '—';
                        ?>
                        <tr>
                            <td>#<?php echo $row['id']; ?></td>
                            <td class="date-cell"><?php echo $date_aff; ?></td>
                            <td><strong><?php echo htmlspecialchars($row['nom_client']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['telephone']); ?></td>
                            <td><?php echo htmlspecialchars($row['parfum']); ?></td>
                            <td>
                                <span class="badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars($statut); ?></span>
                                <form method="POST" action="update_statut.php" class="statut-form">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <select name="statut" onchange="this.form.submit()">
                                        <?php foreach (['En attente', 'Traitée', 'Annulée'] as $opt): ?>
                                            <option value="<?php echo $opt; ?>" <?php echo ($statut === $opt) ? 'selected' : ''; ?>><?php echo $opt; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            </td>
                            <td>
                                <a href="delete_commande.php?id=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette commande ?');">Supprimer</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="empty-state">Aucune commande pour le moment.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php include "admin_foot.php"; ?>
