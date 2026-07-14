-- ============================================================
--  Sheyluna Parfums - Table des produits (catalogue)
--  A executer une fois (le seed des donnees se fait via seed_produits.php)
-- ============================================================

SET NAMES utf8mb4;
USE sheyluna_db;

CREATE TABLE IF NOT EXISTS produits (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    nom           VARCHAR(150) NOT NULL,
    genre         ENUM('Femme', 'Homme') NOT NULL DEFAULT 'Femme',
    categorie     VARCHAR(120) NOT NULL,
    prix          VARCHAR(30)  NOT NULL,            -- ex: "40 MAD"
    prix_original VARCHAR(30)  DEFAULT NULL,        -- ex: "70 MAD" (barre), ou NULL
    image         VARCHAR(255) NOT NULL,            -- nom du fichier image
    badge         VARCHAR(40)  DEFAULT NULL,        -- ex: "Nouveau", "Hot", "Best-seller"
    badge_type    ENUM('new', 'hot') DEFAULT NULL,  -- couleur du badge
    note_courte   VARCHAR(255) DEFAULT NULL,        -- petit texte sous le nom (carte)
    description   TEXT,                             -- description longue (modale)
    notes_liste   VARCHAR(255) DEFAULT NULL,        -- ex: "Rose, Jasmin, Musc"
    note_etoiles  DECIMAL(2,1) NOT NULL DEFAULT 5.0,-- ex: 4.5
    avis          VARCHAR(40)  DEFAULT NULL,        -- ex: "127 avis"
    position      INT NOT NULL DEFAULT 0,           -- ordre d'affichage
    actif         TINYINT(1) NOT NULL DEFAULT 1,    -- 1 = visible sur le site
    date_ajout    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
