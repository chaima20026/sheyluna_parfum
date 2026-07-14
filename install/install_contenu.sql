-- ============================================================
--  Sheyluna Parfums - Contenu editable du site
--  Tables : parametres (reglages), categories, avis (temoignages)
--  + colonnes best-seller sur produits
-- ============================================================

SET NAMES utf8mb4;
USE sheyluna_db;

-- Reglages generaux (cle -> valeur)
CREATE TABLE IF NOT EXISTS parametres (
    cle    VARCHAR(60) PRIMARY KEY,
    valeur TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cartes "Explorez par Univers"
CREATE TABLE IF NOT EXISTS categories (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    titre         VARCHAR(80)  NOT NULL,
    nombre_texte  VARCHAR(40)  DEFAULT NULL,   -- ex: "12 parfums"
    image         VARCHAR(255) NOT NULL,
    position      INT NOT NULL DEFAULT 0,
    actif         TINYINT(1) NOT NULL DEFAULT 1,
    date_ajout    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Colonnes best-seller sur les produits
ALTER TABLE produits ADD COLUMN est_bestseller TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE produits ADD COLUMN bestseller_rang INT NOT NULL DEFAULT 0;
