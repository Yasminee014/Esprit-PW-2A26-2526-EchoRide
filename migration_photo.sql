-- ============================================================
-- Migration : ajout de la colonne photo
-- À exécuter UNE SEULE FOIS dans phpMyAdmin > onglet SQL
-- ============================================================

-- 1. Colonne photo pour les utilisateurs
ALTER TABLE users
ADD COLUMN photo VARCHAR(255) NULL DEFAULT NULL
AFTER telephone;

-- 2. Colonne photo pour les admins
ALTER TABLE admins
ADD COLUMN photo VARCHAR(255) NULL DEFAULT NULL
AFTER email;
