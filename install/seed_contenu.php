<?php
// Remplissage initial du contenu editable (a lancer une fois).
include __DIR__ . "/../connexion.php";

// ---------- PARAMETRES ----------
$parametres = [
    'promo_texte'        => "Livraison gratuite à partir de 500 MAD — Code LUNA20 pour -20%",
    'social_whatsapp'    => "https://wa.me/message/NZ5DOI5ERKWRB1",
    'social_facebook'    => "https://www.facebook.com/share/1Gx4Ay6po9/?mibextid=wwXIfr",
    'social_instagram'   => "https://www.instagram.com/sheyluna_perfume?igsh=MW0wazF2c3ZqNmVoMw%3D%3D&utm_source=qr",
    'contact_adresse'    => "Marrakech, Maroc",
    'contact_tel'        => "+212 7 79 21 54 43",
    'contact_tel_lien'   => "+212779215443",
    'contact_email'      => "contact@sheyluna.com",
    'contact_horaires'   => "Lun-Sam: 9h-19h",
    'footer_description' => "SheyLuna Parfums — Maison de parfum marocaine dédiée à la création de fragrances d'exception. Chaque parfum est une œuvre d'art olfactive, composée avec les matières premières les plus nobles du monde.",
    'copyright'          => "© 2026 SheyLuna Parfums - Maroc. Tous droits réservés.",
    'banner_tag'         => "✨ Édition Limitée",
    'banner_titre'       => "Coffret Signature SheyLuna 2026",
    'banner_desc'        => "Trois parfums iconiques dans un coffret d'exception. Offrez-vous l'élégance absolue avec notre coffret découverte exclusif.",
    'banner_prix'        => "129 MAD",
    'banner_prix_original' => "169 MAD",
    'banner_bouton'      => "Découvrir le Coffret",
    'banner_image'       => "images/site/WhatsApp Image 2026-06-09 at 14.42.27.jpeg",
];
$stmt = mysqli_prepare($conn, "INSERT INTO parametres (cle, valeur) VALUES (?, ?) ON DUPLICATE KEY UPDATE valeur = VALUES(valeur)");
foreach ($parametres as $cle => $val) {
    mysqli_stmt_bind_param($stmt, "ss", $cle, $val);
    mysqli_stmt_execute($stmt);
}
echo count($parametres) . " parametres enregistres.\n";

// ---------- CATEGORIES ----------
if (mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) n FROM categories"))['n'] == 0) {
    $categories = [
        ["Floral",   "12 parfums", "images/site/WhatsApp Image 2026-06-08 at 20.18.46.jpeg", 1],
        ["Oriental", "8 parfums",  "images/site/WhatsApp Image 2026-06-08 at 20.18.40.jpeg", 2],
        ["Fraîche",  "10 parfums", "images/site/WhatsApp Image 2026-07-01 at 14.17.05.jpeg", 3],
        ["Boisé",    "7 parfums",  "images/site/WhatsApp Image 2026-06-08 at 20.18.46.jpeg", 4],
    ];
    $stmt = mysqli_prepare($conn, "INSERT INTO categories (titre, nombre_texte, image, position) VALUES (?,?,?,?)");
    foreach ($categories as $c) {
        mysqli_stmt_bind_param($stmt, "sssi", $c[0], $c[1], $c[2], $c[3]);
        mysqli_stmt_execute($stmt);
    }
    echo count($categories) . " categories inserees.\n";
} else {
    echo "Categories deja presentes : ignore.\n";
}

// ---------- BEST-SELLERS (drapeaux sur produits existants) ----------
$bestsellers = [
    "Baccara Rouge 540" => 1,
    "Valentino Umo"     => 2,
    "Versace Eros"      => 3,
    "Kayali Vanille 28" => 4,
    "Faraway Avon"      => 5,
];
$stmt = mysqli_prepare($conn, "UPDATE produits SET est_bestseller = 1, bestseller_rang = ? WHERE nom = ?");
foreach ($bestsellers as $nom => $rang) {
    mysqli_stmt_bind_param($stmt, "is", $rang, $nom);
    mysqli_stmt_execute($stmt);
}
echo count($bestsellers) . " best-sellers marques.\n";
