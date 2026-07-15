<?php
// En-tete commun aux pages d'administration.
// A inclure APRES la verification de session et l'ouverture de connexion.
// Variables attendues : $page_title (titre) et $active ('commandes' ou 'produits').
if (!isset($page_title)) $page_title = "Administration";
if (!isset($active))     $active = "";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - Sheyluna Parfums</title>
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
        * { box-sizing: border-box; }
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
        .main-content { margin-left: 250px; padding: 40px; }
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
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th, td { padding: 15px; border-bottom: 1px solid #eee; vertical-align: middle; }
        th {
            background-color: #f9f9f9;
            color: var(--dark-gray);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
        }
        tr:hover { background-color: #fdfdfd; }
        .empty-state { text-align: center; padding: 50px; color: #888; font-style: italic; }
        /* Boutons */
        .btn {
            display: inline-block;
            text-decoration: none;
            border: none;
            cursor: pointer;
            padding: 10px 18px;
            border-radius: 8px;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.9rem;
            font-weight: 600;
            transition: 0.3s;
        }
        .btn-gold { background: linear-gradient(135deg, var(--gold-primary), var(--gold-dark)); color: white; }
        .btn-gold:hover { opacity: 0.9; box-shadow: 0 6px 15px rgba(201,169,110,0.35); }
        .btn-edit { background-color: #eef2ff; color: #3b5bdb; padding: 8px 12px; font-size: 0.85rem; border-radius: 5px; }
        .btn-delete { background-color: var(--red-delete); color: white; padding: 8px 12px; font-size: 0.85rem; border-radius: 5px; }
        .btn-download { background: linear-gradient(135deg, var(--gold-primary), var(--gold-dark)); color: white; padding: 8px 12px; font-size: 0.85rem; border-radius: 5px; margin-right: 6px; }
        .btn-edit:hover, .btn-delete:hover, .btn-download:hover { opacity: 0.8; }
        /* Badges */
        .badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 0.78rem; font-weight: 600; }
        .badge-new { background: #e7f5ff; color: #1971c2; }
        .badge-hot { background: #fff0f0; color: #c92a2a; }
        .badge-off { background: #f1f3f5; color: #868e96; }
        .badge-attente { background: #fff3cd; color: #856404; }
        .badge-traitee { background: #d4edda; color: #155724; }
        .badge-annulee { background: #f8d7da; color: #721c24; }
        .thumb { width: 54px; height: 54px; object-fit: cover; border-radius: 8px; border: 1px solid #eee; }
        /* Alertes */
        .alert { padding: 14px 18px; border-radius: 10px; margin-bottom: 25px; font-weight: 500; font-size: 0.95rem; }
        .alert-success { background: #E8F5E9; color: #2E7D32; border: 1px solid #C8E6C9; }
        .alert-error { background: #FFEBEE; color: #C62828; border: 1px solid #FFCDD2; }
        /* Formulaire */
        .form-card { background: white; border-radius: 10px; padding: 35px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 820px; }
        .form-row { display: flex; gap: 20px; flex-wrap: wrap; }
        .form-group { margin-bottom: 22px; flex: 1; min-width: 220px; }
        .form-group label { display: block; font-size: 0.82rem; font-weight: 600; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 1px; }
        .form-control {
            width: 100%; padding: 12px 14px; border: 1px solid #E2E2E2; border-radius: 10px;
            font-family: 'Montserrat', sans-serif; font-size: 0.95rem; background: #FAFAFA;
        }
        .form-control:focus { outline: none; border-color: var(--gold-primary); background: white; box-shadow: 0 0 0 4px rgba(201,169,110,0.1); }
        textarea.form-control { min-height: 100px; resize: vertical; }
        .form-actions { display: flex; gap: 12px; align-items: center; margin-top: 10px; }
        .form-hint { font-size: 0.78rem; color: #999; margin-top: 6px; }
        .cancel-link { color: #666; text-decoration: none; font-size: 0.9rem; }
        .cancel-link:hover { color: var(--gold-primary); }
        .current-thumb { margin-top: 10px; }
        /* Commandes : compteurs, recherche, statut */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; border-radius: 10px; padding: 20px 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-left: 4px solid var(--gold-primary); }
        .stat-card .stat-number { font-family: 'Playfair Display', serif; font-size: 2rem; font-weight: 600; color: var(--dark-gray); }
        .stat-card .stat-label { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; color: #888; margin-top: 5px; }
        .stat-card.attente { border-left-color: #f0ad4e; }
        .stat-card.traitee { border-left-color: #2E7D32; }
        .stat-card.annulee { border-left-color: var(--red-delete); }
        .search-form { display: flex; gap: 10px; }
        .search-form input[type="text"] { padding: 10px 15px; border: 1px solid #ddd; border-radius: 8px; font-family: 'Montserrat', sans-serif; font-size: 0.9rem; min-width: 240px; }
        .search-form input[type="text"]:focus { outline: none; border-color: var(--gold-primary); }
        .search-form button { background: linear-gradient(135deg, var(--gold-primary), var(--gold-dark)); color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; }
        .search-form .reset-link { display: flex; align-items: center; color: #888; text-decoration: none; font-size: 0.85rem; }
        .statut-form { margin-top: 8px; }
        .statut-form select { padding: 5px 8px; border: 1px solid #ddd; border-radius: 5px; font-size: 0.8rem; font-family: 'Montserrat', sans-serif; cursor: pointer; }
        .date-cell { font-size: 0.85rem; color: #666; white-space: nowrap; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Sheyluna Admin</h2>
    <a href="admin.php" class="<?php echo $active === 'commandes' ? 'active' : ''; ?>">Commandes</a>
    <a href="admin_produits.php" class="<?php echo $active === 'produits' ? 'active' : ''; ?>">Produits</a>
    <a href="admin_categories.php" class="<?php echo $active === 'categories' ? 'active' : ''; ?>">Catégories</a>
    <a href="admin_parametres.php" class="<?php echo $active === 'parametres' ? 'active' : ''; ?>">Paramètres du site</a>
    <a href="logout.php">Déconnexion</a>
</div>

<div class="main-content">
