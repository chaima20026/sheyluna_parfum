-- ============================================================
--  Sheyluna Parfums - Migration : ajout de la gestion du stock
--  A executer UNE FOIS sur une base existante (via phpMyAdmin
--  ou en ligne de commande) pour ajouter la colonne "stock".
--  (Pour une nouvelle installation, install_produits.sql suffit.)
-- ============================================================

USE sheyluna_db;

-- Ajoute la colonne stock (0 = rupture de stock)
ALTER TABLE produits
    ADD COLUMN stock INT NOT NULL DEFAULT 0 AFTER actif;

-- Initialise les produits existants a un stock positif
-- (a ajuster ensuite produit par produit depuis l'administration)
UPDATE produits SET stock = 50;
