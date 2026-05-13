-- ============================================================
-- EcoRide Tablespace Import Script
-- Run AFTER recover_db.sql and AFTER copying .ibd files back
-- ============================================================
-- Before running this script:
--   1. recover_db.sql was already executed (tables recreated)
--   2. For each table, run:
--      ALTER TABLE <name> DISCARD TABLESPACE;
--   3. Copy the backup .ibd files into C:\xampp\mysql\data\ecoride\
--   4. Then run this script to import them
-- ============================================================

SET foreign_key_checks = 0;
USE ecoride;

ALTER TABLE app_settings  DISCARD TABLESPACE;
ALTER TABLE admins        DISCARD TABLESPACE;
ALTER TABLE users         DISCARD TABLESPACE;
ALTER TABLE trajet        DISCARD TABLESPACE;
ALTER TABLE evenements    DISCARD TABLESPACE;
ALTER TABLE sponsors      DISCARD TABLESPACE;
ALTER TABLE destination   DISCARD TABLESPACE;
ALTER TABLE vehicules     DISCARD TABLESPACE;
ALTER TABLE reservations  DISCARD TABLESPACE;
ALTER TABLE paiements     DISCARD TABLESPACE;
ALTER TABLE reclamations  DISCARD TABLESPACE;
ALTER TABLE reponse       DISCARD TABLESPACE;
ALTER TABLE declarations  DISCARD TABLESPACE;
ALTER TABLE commentaires  DISCARD TABLESPACE;

SELECT 'Tablespaces discarded. Now copy .ibd files from backup into data/ecoride/ and run import_tablespaces.sql' AS next_step;
