<?php
$parfum = "";
if (isset($_GET['parfum'])) {
    $parfum = $_GET['parfum'];
}
include "connexion.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom_client = $_POST["nom_client"];
    $telephone = $_POST["telephone"];
    $parfum = $_POST["parfum"];

    // Requete preparee : protege contre l'injection SQL et gere les apostrophes (ex: "Fleur d'Été")
    $stmt = mysqli_prepare($conn, "INSERT INTO commandes (nom_client, telephone, parfum) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sss", $nom_client, $telephone, $parfum);

    if (mysqli_stmt_execute($stmt)) {
        $message = "<div class='success-msg'>✅ Votre commande a été enregistrée avec succès ! Nous vous contacterons bientôt.</div>";
    } else {
        $message = "<div class='error-msg'>Erreur : " . mysqli_error($conn) . "</div>";
    }
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finaliser votre commande - Sheyluna Parfums</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Playfair+Display:wght@400;600&family=Cormorant+Garamond:ital,wght@0,400;1,400&display=swap" rel="stylesheet">
    <style>
        :root {
            --gold-primary: #C9A96E;
            --gold-light: #E8D5B0;
            --gold-dark: #A68B5B;
            --pink-soft: #F5E6F0;
            --cream-light: #FFFDF9;
            --dark-gray: #2D2D2D;
            --shadow-soft: 0 4px 20px rgba(0,0,0,0.08);
            --transition-smooth: all 0.4s ease;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, var(--cream-light) 0%, var(--pink-soft) 100%);
            color: var(--dark-gray);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .checkout-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 50px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px;
            padding: 50px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
            margin: 20px;
        }

        .checkout-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--gold-light), var(--gold-primary), var(--gold-dark));
        }

        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            color: var(--gold-primary);
            margin-bottom: 10px;
            letter-spacing: 2px;
        }

        .subtitle {
            font-family: 'Cormorant Garamond', serif;
            font-style: italic;
            color: #666;
            font-size: 1.2rem;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 25px;
            text-align: left;
        }

        .form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--dark-gray);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .form-control {
            width: 100%;
            padding: 15px;
            border: 1px solid #E8E8E8;
            border-radius: 10px;
            font-family: 'Montserrat', sans-serif;
            font-size: 1rem;
            transition: var(--transition-smooth);
            background-color: #FAFAFA;
            box-sizing: border-box;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--gold-primary);
            background-color: white;
            box-shadow: 0 0 0 4px rgba(201, 169, 110, 0.1);
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--gold-primary) 0%, var(--gold-dark) 100%);
            color: white;
            border: none;
            padding: 18px 30px;
            width: 100%;
            border-radius: 50px;
            font-family: 'Montserrat', sans-serif;
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            cursor: pointer;
            transition: var(--transition-smooth);
            margin-top: 10px;
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(201, 169, 110, 0.4);
        }

        .success-msg {
            background-color: #E8F5E9;
            color: #2E7D32;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-weight: 500;
            font-size: 0.95rem;
            border: 1px solid #C8E6C9;
        }

        .error-msg {
            background-color: #FFEBEE;
            color: #C62828;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-weight: 500;
            font-size: 0.95rem;
            border: 1px solid #FFCDD2;
        }

        .back-link {
            display: inline-block;
            margin-top: 25px;
            color: #666;
            text-decoration: none;
            font-size: 0.9rem;
            transition: var(--transition-smooth);
            border-bottom: 1px solid transparent;
        }

        .back-link:hover {
            color: var(--gold-primary);
            border-bottom-color: var(--gold-primary);
        }
    </style>
</head>
<body>

<div class="checkout-container">
    <div class="logo">Sheyluna Parfums</div>
    <div class="subtitle">Finalisez votre commande</div>

    <?php echo $message; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="nom_client">Nom Complet</label>
            <input type="text" id="nom_client" name="nom_client" class="form-control" placeholder="Entrez votre nom" required>
        </div>

        <div class="form-group">
            <label for="telephone">Numéro de Téléphone</label>
            <input type="tel" id="telephone" name="telephone" class="form-control" placeholder="Ex: 06 12 34 56 78" required>
        </div>

        <div class="form-group">
            <label for="parfum">Parfum Choisi</label>
            <input type="text" id="parfum" name="parfum" class="form-control" value="<?php echo htmlspecialchars($parfum); ?>" placeholder="Nom du parfum" required readonly style="background-color: #f0f0f0; cursor: not-allowed; color: #555;">
        </div>

        <button type="submit" class="btn-submit">Confirmer la Commande</button>
    </form>

    <a href="index.html" class="back-link">← Retour à la boutique</a>
</div>

</body>
</html>