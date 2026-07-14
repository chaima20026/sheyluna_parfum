<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

include "connexion.php";

// Fetch all orders
$sql = "SELECT * FROM commandes ORDER BY id DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Admin - Sheyluna Parfums</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Playfair+Display:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --gold-primary: #C9A96E;
            --gold-light: #E8D5B0;
            --gold-dark: #A68B5B;
            --cream-light: #FFFDF9;
            --dark-gray: #2D2D2D;
            --pink-soft: #F5E6F0;
            --red-delete: #dc3545;
        }
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f4f7f6;
            color: var(--dark-gray);
            margin: 0;
            padding: 0;
        }
        .sidebar {
            width: 250px;
            background: linear-gradient(180deg, var(--dark-gray) 0%, #1a1a1a 100%);
            color: white;
            position: fixed;
            height: 100vh;
            padding-top: 30px;
        }
        .sidebar h2 {
            font-family: 'Playfair Display', serif;
            text-align: center;
            color: var(--gold-primary);
            margin-bottom: 40px;
            font-size: 1.5rem;
        }
        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 15px 25px;
            font-size: 1rem;
            transition: 0.3s;
            border-left: 4px solid transparent;
        }
        .sidebar a:hover, .sidebar a.active {
            background: rgba(255,255,255,0.1);
            border-left-color: var(--gold-primary);
            color: var(--gold-primary);
        }
        .main-content {
            margin-left: 250px;
            padding: 40px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            background: white;
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .header h1 {
            font-family: 'Playfair Display', serif;
            margin: 0;
            color: var(--dark-gray);
            font-size: 1.8rem;
        }
        .table-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }
        th, td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        th {
            background-color: #f9f9f9;
            color: var(--dark-gray);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
        }
        tr:hover {
            background-color: #fdfdfd;
        }
        .btn-delete {
            background-color: var(--red-delete);
            color: white;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 5px;
            font-size: 0.85rem;
            font-weight: 500;
            transition: opacity 0.3s;
        }
        .btn-delete:hover {
            opacity: 0.8;
        }
        .empty-state {
            text-align: center;
            padding: 50px;
            color: #888;
            font-style: italic;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Sheyluna Admin</h2>
    <a href="admin.php" class="active">Commandes</a>
    <a href="logout.php">Déconnexion</a>
</div>

<div class="main-content">
    <div class="header">
        <h1>Gestion des Commandes</h1>
        <div>Connecté en tant qu'Administrateur</div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom du Client</th>
                    <th>Téléphone</th>
                    <th>Parfum Choisi</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td>#<?php echo $row['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($row['nom_client']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['telephone']); ?></td>
                            <td><?php echo htmlspecialchars($row['parfum']); ?></td>
                            <td>
                                <a href="delete_commande.php?id=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette commande ?');">Supprimer</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="empty-state">Aucune commande pour le moment.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
