-- ============================================================
-- Migration : colonnes avatar et avatar_options
-- À exécuter UNE SEULE FOIS dans phpMyAdmin > onglet SQL
-- Base : Ecoride1
-- ============================================================

-- 1. Colonne avatar (clé de l'avatar prédéfini : av1..av15, ou 'custom')
ALTER TABLE users
ADD COLUMN IF NOT EXISTS avatar VARCHAR(50) NULL DEFAULT NULL
AFTER photo;

-- 2. Colonne avatar_options (JSON des options de l'avatar personnalisé)
ALTER TABLE users
ADD COLUMN IF NOT EXISTS avatar_options TEXT NULL DEFAULT NULL
AFTER avatar;

-- ============================================================
-- Vérification après exécution :
-- SHOW COLUMNS FROM users LIKE 'avatar%';
-- ============================================================
