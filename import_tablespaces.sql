-- ============================================================
-- EcoRide Import Tablespaces Script
-- Run AFTER discard_tablespaces.sql + copying .ibd files back
-- ============================================================

SET foreign_key_checks = 0;
USE ecoride;

ALTER TABLE app_settings  IMPORT TABLESPACE;
ALTER TABLE admins        IMPORT TABLESPACE;
ALTER TABLE users         IMPORT TABLESPACE;
ALTER TABLE trajet        IMPORT TABLESPACE;
ALTER TABLE evenements    IMPORT TABLESPACE;
ALTER TABLE sponsors      IMPORT TABLESPACE;
ALTER TABLE destination   IMPORT TABLESPACE;
ALTER TABLE vehicules     IMPORT TABLESPACE;
ALTER TABLE reservations  IMPORT TABLESPACE;
ALTER TABLE paiements     IMPORT TABLESPACE;
ALTER TABLE reclamations  IMPORT TABLESPACE;
ALTER TABLE reponse       IMPORT TABLESPACE;
ALTER TABLE declarations  IMPORT TABLESPACE;
ALTER TABLE commentaires  IMPORT TABLESPACE;

SET foreign_key_checks = 1;

-- Verify all tables are accessible
SELECT 'app_settings'  AS tbl, COUNT(*) AS rows FROM app_settings  UNION ALL
SELECT 'admins',                COUNT(*) FROM admins                UNION ALL
SELECT 'users',                 COUNT(*) FROM users                 UNION ALL
SELECT 'trajet',                COUNT(*) FROM trajet                UNION ALL
SELECT 'evenements',            COUNT(*) FROM evenements            UNION ALL
SELECT 'sponsors',              COUNT(*) FROM sponsors              UNION ALL
SELECT 'destination',           COUNT(*) FROM destination           UNION ALL
SELECT 'vehicules',             COUNT(*) FROM vehicules             UNION ALL
SELECT 'reservations',          COUNT(*) FROM reservations          UNION ALL
SELECT 'paiements',             COUNT(*) FROM paiements             UNION ALL
SELECT 'reclamations',          COUNT(*) FROM reclamations          UNION ALL
SELECT 'reponse',               COUNT(*) FROM reponse               UNION ALL
SELECT 'declarations',          COUNT(*) FROM declarations          UNION ALL
SELECT 'commentaires',          COUNT(*) FROM commentaires;
