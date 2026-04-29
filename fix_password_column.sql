-- ============================================================
-- CORRECTION : Colonne password trop courte
-- password_hash() génère ~60-72 chars → la colonne doit être VARCHAR(255)
-- Exécuter dans phpMyAdmin sur la base : Ecoride1
-- ============================================================

ALTER TABLE users MODIFY COLUMN password VARCHAR(255) NOT NULL;

-- Vérification après modification :
-- SHOW COLUMNS FROM users LIKE 'password';
