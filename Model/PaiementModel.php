<?php

require_once __DIR__ . '/../Config/Database.php';

class PaiementModel {
    
    private PDO $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->creerTablePaiements();
    }
    
    /**
     * Crée la table des paiements si elle n'existe pas
     */
    private function creerTablePaiements(): void {
        $sql = "
            CREATE TABLE IF NOT EXISTS paiements (
                id INT AUTO_INCREMENT PRIMARY KEY,
                reservation_id INT NOT NULL,
                mode_paiement ENUM('carte', 'sur_place', 'virement') NOT NULL,
                montant DECIMAL(10,2) NOT NULL,
                statut ENUM('en_attente', 'paye', 'echoue', 'annule') DEFAULT 'en_attente',
                reference_transaction VARCHAR(100),
                date_paiement DATETIME,
                date_validation DATETIME,
                create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE CASCADE,
                INDEX idx_reservation (reservation_id),
                INDEX idx_statut (statut)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ";
        $this->db->exec($sql);
    }
    
    /**
     * Enregistre une demande de paiement
     */
    public function enregistrerPaiement(int $reservationId, string $mode, float $montant): int|false {
        $stmt = $this->db->prepare("
            INSERT INTO paiements (reservation_id, mode_paiement, montant, statut, date_paiement)
            VALUES (:rid, :mode, :montant, 'en_attente', NOW())
        ");
        
        $success = $stmt->execute([
            ':rid' => $reservationId,
            ':mode' => $mode,
            ':montant' => $montant
        ]);
        
        return $success ? (int)$this->db->lastInsertId() : false;
    }
    
    /**
     * Valide un paiement
     */
    public function validerPaiement(int $paiementId, string $reference = null): bool {
        $stmt = $this->db->prepare("
            UPDATE paiements 
            SET statut = 'paye', 
                date_validation = NOW(),
                reference_transaction = COALESCE(:ref, reference_transaction)
            WHERE id = :id
        ");
        return $stmt->execute([':id' => $paiementId, ':ref' => $reference]);
    }
    
    /**
     * Annule un paiement
     */
    public function annulerPaiement(int $paiementId): bool {
        $stmt = $this->db->prepare("UPDATE paiements SET statut = 'annule' WHERE id = :id");
        return $stmt->execute([':id' => $paiementId]);
    }
    
    /**
     * Récupère le paiement d'une réservation
     */
    public function getPaiementByReservation(int $reservationId): array|false {
        $stmt = $this->db->prepare("SELECT * FROM paiements WHERE reservation_id = :rid ORDER BY id DESC LIMIT 1");
        $stmt->execute([':rid' => $reservationId]);
        return $stmt->fetch();
    }
    
    /**
     * Vérifie si une réservation est déjà payée
     */
    public function estPayee(int $reservationId): bool {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM paiements 
            WHERE reservation_id = :rid AND statut = 'paye'
        ");
        $stmt->execute([':rid' => $reservationId]);
        return $stmt->fetchColumn() > 0;
    }
}