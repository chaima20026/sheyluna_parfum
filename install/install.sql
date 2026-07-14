-- ============================================================
--  Sheyluna Parfums - Script d'installation de la base
--  A executer une seule fois (via phpMyAdmin ou en ligne de commande)
-- ============================================================

-- Garantit que les accents (é, è, à...) sont interpretes en UTF-8 a l'import
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS sheyluna_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE sheyluna_db;

CREATE TABLE IF NOT EXISTS commandes (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    nom_client    VARCHAR(150) NOT NULL,
    telephone     VARCHAR(30)  NOT NULL,
    parfum        TEXT         NOT NULL,
    statut        ENUM('En attente', 'Traitée', 'Annulée') NOT NULL DEFAULT 'En attente',
    date_commande DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
