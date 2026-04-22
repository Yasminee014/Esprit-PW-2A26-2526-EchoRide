CREATE DATABASE IF NOT EXISTS lostfound_front
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE lostfound_front;

DROP TABLE IF EXISTS signalements_objet;
DROP TABLE IF EXISTS objets_perdus;

CREATE TABLE IF NOT EXISTS declarations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    categorie VARCHAR(50) NOT NULL,
  lieu_perte VARCHAR(120) DEFAULT NULL,
    date_perte DATE NOT NULL,
  statut VARCHAR(20) NOT NULL DEFAULT 'perdu',
    photo_url VARCHAR(255) DEFAULT NULL,
    trajet_id INT NOT NULL DEFAULT 0,
    passager_id INT DEFAULT NULL,
    anonyme_nom VARCHAR(120) DEFAULT NULL,
  user_id INT DEFAULT NULL,
  user_nom VARCHAR(120) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_declarations_statut (statut),
  INDEX idx_declarations_passager (passager_id),
  INDEX idx_declarations_trajet (trajet_id),
  INDEX idx_declarations_date (date_perte)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS commentaires (
    id INT AUTO_INCREMENT PRIMARY KEY,
    declaration_id INT NOT NULL,
  user_id INT DEFAULT NULL,
  user_nom VARCHAR(120) DEFAULT NULL,
    message TEXT NOT NULL,
    parent_comment_id INT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_commentaires_declaration
      FOREIGN KEY (declaration_id) REFERENCES declarations(id)
      ON DELETE CASCADE,
    CONSTRAINT fk_commentaires_parent
      FOREIGN KEY (parent_comment_id) REFERENCES commentaires(id)
      ON DELETE CASCADE,
    INDEX idx_commentaires_declaration (declaration_id),
    INDEX idx_commentaires_parent (parent_comment_id),
    INDEX idx_commentaires_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO declarations (
    id,
    titre,
    description,
    categorie,
    lieu_perte,
    date_perte,
    statut,
    photo_url,
    trajet_id,
    passager_id,
    anonyme_nom,
    user_id,
    user_nom
)
VALUES
  (1, 'Sac de sport rouge', 'Sac rouge avec chaussures et serviette.', 'bagage', 'Gare centrale', '2026-04-12', 'perdu', NULL, 201, 1, NULL, 1, 'Sophie Martin'),
  (2, 'Clavier sans fil', 'Petit clavier noir oublie sur le siege arriere.', 'electronique', 'Campus Nord', '2026-04-13', 'retrouve', NULL, 202, 2, NULL, 2, 'Youssef Belaid'),
  (3, 'Carte etudiante', 'Carte au nom de Camille Bernard.', 'document', 'Hopital Saint-Pierre', '2026-04-11', 'restitue', NULL, 203, 3, NULL, 3, 'Camille Bernard')
ON DUPLICATE KEY UPDATE
  titre = VALUES(titre),
  description = VALUES(description),
  categorie = VALUES(categorie),
  lieu_perte = VALUES(lieu_perte),
  date_perte = VALUES(date_perte),
  statut = VALUES(statut),
  photo_url = VALUES(photo_url),
  trajet_id = VALUES(trajet_id),
  passager_id = VALUES(passager_id),
  anonyme_nom = VALUES(anonyme_nom),
  user_id = VALUES(user_id),
  user_nom = VALUES(user_nom);

INSERT INTO commentaires (id, declaration_id, user_id, user_nom, message, parent_comment_id, created_at)
VALUES
  (1, 1, 4, 'Antoine Girard', 'Je pense avoir vu ce sac au depot.', NULL, '2026-04-12 18:10:00'),
  (2, 1, 1, 'Sophie Martin', 'Merci, je vais verifier demain matin.', 1, '2026-04-12 18:25:00'),
  (3, 2, 5, 'Lea Martin', 'Objet similaire trouve pres de la porte droite.', NULL, '2026-04-13 10:40:00'),
  (4, 3, 2, 'Youssef Belaid', 'Je connais Camille, je peux la contacter.', NULL, '2026-04-11 14:05:00')
ON DUPLICATE KEY UPDATE
  declaration_id = VALUES(declaration_id),
  user_id = VALUES(user_id),
  user_nom = VALUES(user_nom),
  message = VALUES(message),
  parent_comment_id = VALUES(parent_comment_id),
  created_at = VALUES(created_at);
