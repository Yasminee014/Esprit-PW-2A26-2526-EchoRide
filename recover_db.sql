-- ============================================================
-- EcoRide Database Recovery Script
-- Recovers data from orphaned InnoDB .ibd files
-- Run via: mysql -u root -P 3307 < recover_db.sql
-- ============================================================

SET foreign_key_checks = 0;
SET sql_mode = '';
USE ecoride;

-- ============================================================
-- STEP 1: Drop all orphaned tables (removes stale .frm files)
-- NOTE: .ibd files already backed up before running this
-- ============================================================

DROP TABLE IF EXISTS commentaires;
DROP TABLE IF EXISTS paiements;
DROP TABLE IF EXISTS reponse;
DROP TABLE IF EXISTS reclamations;
DROP TABLE IF EXISTS reservations;
DROP TABLE IF EXISTS declarations;
DROP TABLE IF EXISTS vehicules;
DROP TABLE IF EXISTS destination;
DROP TABLE IF EXISTS sponsors;
DROP TABLE IF EXISTS evenements;
DROP TABLE IF EXISTS trajet;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS admins;
DROP TABLE IF EXISTS app_settings;

-- ============================================================
-- STEP 2: Recreate all tables with matching schema
-- Order: no-FK tables first, then FK-dependent ones
-- ============================================================

CREATE TABLE app_settings (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    setting_key   VARCHAR(100) NOT NULL UNIQUE,
    setting_value LONGTEXT,
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE admins (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    nom        VARCHAR(100) NOT NULL,
    email      VARCHAR(255) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    photo      VARCHAR(255) DEFAULT NULL,
    photo_data LONGBLOB     DEFAULT NULL,
    photo_mime VARCHAR(50)  DEFAULT NULL,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE users (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    prenom           VARCHAR(100) NOT NULL,
    nom              VARCHAR(100) NOT NULL,
    email            VARCHAR(255) NOT NULL UNIQUE,
    telephone        VARCHAR(30)  DEFAULT NULL,
    role             VARCHAR(30)  NOT NULL DEFAULT 'passager',
    statut           VARCHAR(30)  NOT NULL DEFAULT 'actif',
    password         VARCHAR(255) NOT NULL,
    photo            VARCHAR(255) DEFAULT NULL,
    photo_data       LONGBLOB     DEFAULT NULL,
    photo_mime       VARCHAR(50)  DEFAULT NULL,
    created_by_admin INT          DEFAULT NULL,
    created_at       TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE trajet (
    id_T            INT AUTO_INCREMENT PRIMARY KEY,
    id_u            INT          DEFAULT NULL,
    point_depart    VARCHAR(255) NOT NULL,
    point_arrive    VARCHAR(255) NOT NULL,
    prix_total      DECIMAL(10,2) DEFAULT 0.00,
    distance_total  DECIMAL(10,2) DEFAULT 0.00,
    INDEX idx_trajet_user (id_u)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE evenements (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    titre           VARCHAR(255) NOT NULL,
    description     TEXT         DEFAULT NULL,
    type            VARCHAR(100) DEFAULT NULL,
    ville           VARCHAR(150) DEFAULT NULL,
    date_evenement  DATETIME     DEFAULT NULL,
    nb_places       INT          DEFAULT 0,
    statut          VARCHAR(30)  NOT NULL DEFAULT 'ouvert',
    image           VARCHAR(255) DEFAULT 'default.jpg',
    created_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE sponsors (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    nom_entreprise      VARCHAR(255) NOT NULL,
    montant_sponsoring  DECIMAL(12,2) DEFAULT 0.00,
    type_sponsor        VARCHAR(100) DEFAULT NULL,
    statut              VARCHAR(30)  NOT NULL DEFAULT 'en_attente',
    evenement_id        INT          DEFAULT NULL,
    logo                VARCHAR(255) DEFAULT NULL,
    created_at          TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_sponsors_event (evenement_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE destination (
    id_des    INT AUTO_INCREMENT PRIMARY KEY,
    trajet_id INT          DEFAULT NULL,
    nom       VARCHAR(255) DEFAULT NULL,
    distance  DECIMAL(10,2) DEFAULT 0.00,
    ordre     INT          DEFAULT 0,
    prix      DECIMAL(10,2) DEFAULT 0.00,
    INDEX idx_destination_trajet (trajet_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE vehicules (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    user_id          INT          DEFAULT NULL,
    marque           VARCHAR(50)  NOT NULL,
    modele           VARCHAR(50)  NOT NULL,
    immatriculation  VARCHAR(20)  NOT NULL,
    couleur          VARCHAR(50)  DEFAULT NULL,
    capacite         INT          NOT NULL DEFAULT 4,
    climatisation    TINYINT(1)   NOT NULL DEFAULT 0,
    statut           VARCHAR(30)  NOT NULL DEFAULT 'disponible',
    photo            VARCHAR(255) DEFAULT NULL,
    trajet_id        INT          DEFAULT NULL,
    INDEX idx_vehicules_user (user_id),
    INDEX idx_vehicules_trajet_id (trajet_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE reservations (
    id                   INT AUTO_INCREMENT PRIMARY KEY,
    vehicule_id          INT           DEFAULT NULL,
    user_id              INT           DEFAULT NULL,
    trajet_id            INT           DEFAULT NULL,
    date_reservation     TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    date_debut           DATE          DEFAULT NULL,
    date_fin             DATE          DEFAULT NULL,
    heure                TIME          DEFAULT NULL,
    nb_places            INT           NOT NULL DEFAULT 1,
    prix_total           DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    statut               VARCHAR(30)   NOT NULL DEFAULT 'en_attente',
    note                 TEXT          DEFAULT NULL,
    INDEX idx_reserv_vehicule (vehicule_id),
    INDEX idx_reserv_user (user_id),
    INDEX idx_reserv_statut (statut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE paiements (
    id                    INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id        INT           NOT NULL,
    mode_paiement         ENUM('carte','sur_place','virement') NOT NULL,
    montant               DECIMAL(10,2) NOT NULL,
    statut                ENUM('en_attente','paye','echoue','annule') DEFAULT 'en_attente',
    reference_transaction VARCHAR(100)  DEFAULT NULL,
    date_paiement         DATETIME      DEFAULT NULL,
    date_validation       DATETIME      DEFAULT NULL,
    create_at             TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_paiement_reservation (reservation_id),
    INDEX idx_paiement_statut (statut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE reclamations (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT           NOT NULL,
    titre          VARCHAR(255)  NOT NULL,
    description    TEXT          NOT NULL,
    categorie      VARCHAR(100)  NOT NULL DEFAULT 'autre',
    priorite       VARCHAR(30)   NOT NULL DEFAULT 'moyenne',
    statut         VARCHAR(30)   NOT NULL DEFAULT 'en_attente',
    date_creation  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_reclam_utilisateur (utilisateur_id),
    INDEX idx_reclam_statut (statut),
    INDEX idx_reclam_priorite (priorite)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE reponse (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    reclamation_id INT          NOT NULL,
    auteur_admin   VARCHAR(150) NOT NULL DEFAULT 'Admin',
    contenu        TEXT         NOT NULL,
    date_reponse   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_reponse_reclamation (reclamation_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE declarations (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    titre        VARCHAR(255) NOT NULL,
    description  TEXT         DEFAULT NULL,
    categorie    VARCHAR(100) DEFAULT NULL,
    lieu_perte   VARCHAR(255) DEFAULT NULL,
    photo_url    VARCHAR(255) DEFAULT NULL,
    date_perte   DATE         DEFAULT NULL,
    statut       VARCHAR(30)  NOT NULL DEFAULT 'ouvert',
    trajet_id    INT          DEFAULT NULL,
    passager_id  INT          DEFAULT NULL,
    anonyme_nom  VARCHAR(150) DEFAULT NULL,
    user_id      INT          DEFAULT NULL,
    user_nom     VARCHAR(150) DEFAULT NULL,
    created_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_decl_trajet (trajet_id),
    INDEX idx_decl_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE commentaires (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    declaration_id    INT          NOT NULL,
    user_id           INT          DEFAULT NULL,
    user_nom          VARCHAR(150) DEFAULT NULL,
    message           TEXT         NOT NULL,
    parent_comment_id INT          DEFAULT NULL,
    created_at        TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_comm_declaration (declaration_id),
    INDEX idx_comm_parent (parent_comment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET foreign_key_checks = 1;
SELECT 'Tables recreated successfully. Now run the DISCARD/IMPORT script.' AS status;
