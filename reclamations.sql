-- ══════════════════════════════════════════════
-- EcoRide — Table `reclamations`
-- Module MVC · PDO · POO
-- ══════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `reclamations` (
    `id`              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `utilisateur_id`  INT UNSIGNED    NOT NULL,
    `titre`           VARCHAR(150)    NOT NULL,
    `description`     TEXT            NOT NULL,
    `categorie`       ENUM('technique','paiement','securite','autre') NOT NULL,
    `priorite`        ENUM('faible','moyenne','elevee')               NOT NULL DEFAULT 'faible',
    `statut`          ENUM('en_attente','en_cours','resolue','rejetee') NOT NULL DEFAULT 'en_attente',
    `reponse_admin`   TEXT                  NULL DEFAULT NULL,
    `date_creation`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `date_modification` DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_utilisateur`  (`utilisateur_id`),
    INDEX `idx_statut`       (`statut`),
    INDEX `idx_priorite`     (`priorite`),
    CONSTRAINT `fk_reclamation_user`
        FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Données de test ──────────────────────────
INSERT INTO `reclamations` (`utilisateur_id`, `titre`, `description`, `categorie`, `priorite`, `statut`) VALUES
(1, 'Problème de paiement lors de la réservation', 'Mon paiement a été débité deux fois lors de ma réservation du 10 avril.', 'paiement', 'elevee', 'en_attente'),
(1, 'Application inaccessible ce matin', 'Impossible de me connecter pendant 2 heures ce matin (8h–10h).', 'technique', 'moyenne', 'resolue'),
(2, 'Conducteur non présenté au point de rendez-vous', 'Le conducteur n\'était pas présent à l\'heure convenue.', 'autre', 'elevee', 'en_cours'),
(2, 'Demande de remboursement', 'Suite à l\'annulation de mon trajet, je n\'ai pas reçu mon remboursement.', 'paiement', 'moyenne', 'en_attente');
