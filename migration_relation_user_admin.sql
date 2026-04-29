-- ============================================================
-- Migration : Relation users ↔ admins
-- À exécuter UNE SEULE FOIS dans phpMyAdmin > onglet SQL
-- Base : Ecoride1
-- ============================================================

-- 1. Ajouter la colonne created_by_admin dans la table users
--    (stocke l'ID de l'admin qui a créé le compte)
ALTER TABLE users
ADD COLUMN created_by_admin INT NULL DEFAULT NULL
AFTER statut;

-- 2. Ajouter la contrainte de clé étrangère
--    Si l'admin est supprimé → la colonne passe à NULL (SET NULL)
ALTER TABLE users
ADD CONSTRAINT fk_users_created_by_admin
FOREIGN KEY (created_by_admin)
REFERENCES admins(id)
ON DELETE SET NULL
ON UPDATE CASCADE;

-- 3. (Optionnel) Index pour accélérer les requêtes de jointure
CREATE INDEX idx_users_created_by_admin ON users(created_by_admin);

-- ============================================================
-- Vérification après exécution :
-- SHOW COLUMNS FROM users LIKE 'created_by_admin';
-- SHOW CREATE TABLE users;
-- ============================================================
