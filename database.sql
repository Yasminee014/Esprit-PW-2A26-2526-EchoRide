-- ═══════════════════════════════════════════
--  EcoRide — Module Véhicule
--  Tables : vehicules + reservations
-- ═══════════════════════════════════════════

CREATE TABLE IF NOT EXISTS vehicules (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NOT NULL,                          -- conducteur (clé étrangère vers users)
    marque          VARCHAR(50)  NOT NULL,
    modele          VARCHAR(50)  NOT NULL,
    immatriculation VARCHAR(20)  NOT NULL UNIQUE,
    couleur         VARCHAR(30)  DEFAULT NULL,
    capacite        TINYINT      NOT NULL DEFAULT 4,       -- nombre de places (1-9)
    climatisation   TINYINT(1)   NOT NULL DEFAULT 0,       -- 0 = non, 1 = oui
    statut          ENUM('disponible','indisponible','en_maintenance') NOT NULL DEFAULT 'disponible',
    created_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS reservations (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    vehicule_id      INT NOT NULL,
    user_id          INT NOT NULL,                         -- passager qui réserve
    trajet_id        INT DEFAULT NULL,                     -- lien optionnel avec module Trajet
    date_reservation DATE NOT NULL,
    statut           ENUM('en_attente','confirmee','annulee') NOT NULL DEFAULT 'en_attente',
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vehicule_id) REFERENCES vehicules(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)     REFERENCES users(id)     ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ── Données de test ──────────────────────────────────────
INSERT INTO vehicules (user_id, marque, modele, immatriculation, couleur, capacite, climatisation, statut) VALUES
(1, 'Renault',  'Clio',    'AB-123-CD', 'Rouge',   4, 1, 'disponible'),
(1, 'Peugeot',  '208',     'CD-456-EF', 'Bleue',   5, 1, 'disponible'),
(2, 'Citroën',  'C3',      'EF-789-GH', 'Noire',   4, 0, 'en_maintenance'),
(2, 'Tesla',    'Model 3', 'GH-012-IJ', 'Blanche', 5, 1, 'disponible'),
(3, 'Dacia',    'Sandero', 'IJ-345-KL', 'Grise',   4, 0, 'indisponible');

INSERT INTO reservations (vehicule_id, user_id, date_reservation, statut) VALUES
(1, 3, '2026-04-15', 'confirmee'),
(2, 3, '2026-04-20', 'en_attente'),
(4, 1, '2026-04-18', 'en_attente');
