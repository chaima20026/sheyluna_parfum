<?php
// Script de remplissage initial du catalogue (a lancer une seule fois).
// Reprend les 8 produits qui etaient codes en dur dans index.html.
include __DIR__ . "/../connexion.php";

$res = mysqli_query($conn, "SELECT COUNT(*) AS n FROM produits");
if (mysqli_fetch_assoc($res)['n'] > 0) {
    echo "La table produits contient deja des donnees : seed ignore.\n";
    exit;
}

$produits = [
    // Femme
    ["Amber Élixir", "Femme", "Eau de Parfum — Femme", "40 MAD", "70 MAD",
     "images/site/WhatsApp Image 2026-06-09 at 14.47.02.jpeg", "Nouveau", "new",
     "✨ Amber Elixir, l'élégance de l'ambre dans un parfum raffiné et captivant",
     "Un bouquet floral sublime où la Rose de Damas s'allie au Jasmin délicat et au Musc blanc enveloppant. Une fragrance féminine et romantique, idéale pour toutes les occasions.",
     "Rose de Damas, Jasmin, Musc Blanc, Pivoine", 4.5, "127 avis", 1],
    ["Kayali Vanille 28", "Femme", "Eau de Parfum — Femme", "40 MAD", "70 MAD",
     "images/site/WhatsApp Image 2026-06-09 at 15.02.24.jpeg", "Nouveau", "new",
     "Kayali Vanille 28, La douceur de la vanille à l'état pur.",
     "Une fragrance fraîche et lumineuse comme un matin de printemps. La Rose blanche et la Pivoine s'entremêlent dans un nuage de Musc de coton pour un sillage aérien et élégant.",
     "Rose Blanche, Pivoine, Musc de Coton, Bergamote", 4.0, "99 avis", 2],
    ["Baccara Rouge 540", "Femme", "Eau de Parfum — Femme", "40 MAD", "70 MAD",
     "images/site/WhatsApp Image 2026-06-09 at 14.47.06.jpeg", null, null,
     "Baccara Rouge 540, Un parfum audacieux et sensuel.",
     "Une composition envoûtante où la Fleur d'oranger se marie à l'Ylang-Ylang et à la Vanille. Un parfum féminin d'une douceur incomparable, parfait pour les moments précieux.",
     "Fleur d'Oranger, Ylang-Ylang, Vanille, Bergamote", 5.0, "164 avis", 3],
    ["Faraway Avon", "Femme", "Eau de Parfum Intense — Femme", "40 MAD", "70 MAD",
     "images/site/WhatsApp Image 2026-06-09 at 15.22.53.jpeg", "Hot", "hot",
     "Far Away Avon, l'élégance qui voyage avec vous.",
     "Un parfum d'exception où le Lys majestueux rencontre l'Iris poudré et l'Ambre sensuel. Une fragrance intense et raffinée pour la femme qui ose briller.",
     "Lys, Iris, Ambre, Bois de Santal", 4.5, "98 avis", 4],
    // Homme
    ["Versace Eros", "Homme", "Eau de Parfum — Homme", "40 MAD", null,
     "images/site/WhatsApp Image 2026-07-03 at 13.08.27.jpeg", "Nouveau", "new",
     "Iris, Santal, Bois de cèdre, Encens",
     "Un voyage olfactif dans les nuits étoilées de l'Orient. L'Iris poudré rencontre le Santal crémeux, le Bois de Cèdre noble et l'Encens mystérieux pour un parfum sophistiqué et masculin.",
     "Iris, Santal, Bois de Cèdre, Encens, Vétiver", 4.5, "156 avis", 1],
    ["Bleu De Chanel", "Homme", "Eau de Parfum Intense — Homme", "40 MAD", null,
     "images/site/WhatsApp Image 2026-07-03 at 13.08.27 (1).jpeg", "🔥 Best-seller", "hot",
     "Oud, Safran, Ambre gris, Cuir",
     "L'essence même du luxe oriental masculin. L'Oud précieux se marie au Safran épicé, à l'Ambre gris et au Cuir noble pour créer un parfum majestueux et envoûtant.",
     "Oud, Safran, Ambre Gris, Cuir, Bois de Santal", 5.0, "203 avis", 2],
    ["1 Million", "Homme", "Eau de Parfum — Homme", "40 MAD", null,
     "images/site/WhatsApp Image 2026-07-03 at 13.08.28 (1).jpeg", null, null,
     "Santal, Vétiver, Cardamome, Musc",
     "Une fragrance boisée élégante où le Santal crémeux s'entoure de Vétiver profond, de Cardamome épicée et de Musc. Un parfum masculin raffiné pour l'homme moderne.",
     "Santal, Vétiver, Cardamome, Musc, Bois de Cèdre", 4.5, "142 avis", 3],
    ["Valentino Umo", "Homme", "Eau de Parfum — Homme", "40 MAD", null,
     "images/site/WhatsApp Image 2026-07-03 at 13.08.28.jpeg", "Nouveau", "new",
     "Poivre noir, Cuir, Tabac, Patchouli",
     "Un parfum audacieux et puissant. Le Poivre noir et le Cuir s'allient au Tabac et au Patchouli pour créer une fragrance masculine intense et charismatique.",
     "Poivre Noir, Cuir, Tabac, Patchouli, Oud", 5.0, "178 avis", 4],
];

$stmt = mysqli_prepare($conn,
    "INSERT INTO produits
     (nom, genre, categorie, prix, prix_original, image, badge, badge_type, note_courte, description, notes_liste, note_etoiles, avis, position)
     VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

$n = 0;
foreach ($produits as $p) {
    mysqli_stmt_bind_param($stmt, "sssssssssssdsi",
        $p[0], $p[1], $p[2], $p[3], $p[4], $p[5], $p[6], $p[7], $p[8], $p[9], $p[10], $p[11], $p[12], $p[13]);
    mysqli_stmt_execute($stmt);
    $n++;
}
echo "$n produits inseres.\n";
