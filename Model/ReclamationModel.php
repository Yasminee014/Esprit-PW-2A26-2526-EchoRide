<?php
require_once __DIR__ . '/../Config/Database.php';

class ReclamationModel {
    private $db;
    private string $reclamationsTable;
    private string $reponseTable;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->initSchema();
    }

    private function initSchema(): void {
        $this->reclamationsTable = $this->resolveTableName(['reclamations', 'reclamation']);
        if ($this->reclamationsTable === '') {
            $this->createReclamationsTable();
            $this->reclamationsTable = 'reclamations';
        }

        $this->reponseTable = $this->resolveTableName(['reponse', 'reponses']);
        if ($this->reponseTable === '') {
            $this->createReponseTable($this->reclamationsTable);
            $this->reponseTable = 'reponse';
        }
    }

    private function resolveTableName(array $candidates): string {
        foreach ($candidates as $name) {
            if ($this->tableExists($name)) {
                return $name;
            }
        }
        return '';
    }

    private function tableExists(string $table): bool {
        try {
            $stmt = $this->db->prepare('SHOW TABLES LIKE :t');
            $stmt->execute([':t' => $table]);
            return (bool)$stmt->fetchColumn();
        } catch (Throwable $e) {
            return false;
        }
    }

    private function createReclamationsTable(): void {
        $sql = "CREATE TABLE IF NOT EXISTS reclamations (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    utilisateur_id INT NOT NULL,
                    titre VARCHAR(255) NOT NULL,
                    description TEXT NOT NULL,
                    categorie VARCHAR(100) NOT NULL DEFAULT 'autre',
                    priorite VARCHAR(30) NOT NULL DEFAULT 'moyenne',
                    statut VARCHAR(30) NOT NULL DEFAULT 'en_attente',
                    date_creation TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_reclam_utilisateur (utilisateur_id),
                    INDEX idx_reclam_statut (statut),
                    INDEX idx_reclam_priorite (priorite)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $this->db->exec($sql);
    }

    private function createReponseTable(string $reclamTable): void {
        $sql = "CREATE TABLE IF NOT EXISTS reponse (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    reclamation_id INT NOT NULL,
                    auteur_admin VARCHAR(150) NOT NULL DEFAULT 'Admin',
                    contenu TEXT NOT NULL,
                    date_reponse TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE KEY uq_reponse_reclamation (reclamation_id),
                    CONSTRAINT fk_reponse_reclamation
                        FOREIGN KEY (reclamation_id) REFERENCES {$reclamTable}(id)
                        ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $this->db->exec($sql);
    }

    // ── CREATE ──────────────────────────────────────────────────
    public function create(array $data): bool {
        $sql = "INSERT INTO {$this->reclamationsTable}
                    (utilisateur_id, titre, description, categorie, priorite, statut)
                VALUES
                    (:utilisateur_id, :titre, :description, :categorie, :priorite, :statut)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':utilisateur_id' => $data['utilisateur_id'],
            ':titre'          => $data['titre'],
            ':description'    => $data['description'],
            ':categorie'      => $data['categorie'],
            ':priorite'       => $data['priorite'],
            ':statut'         => $data['statut'] ?? 'en_attente',
        ]);
    }

    // ── READ ALL (LEFT JOIN reponse) ─────────────────────────────
    public function getAll(): array {
        $sql = "SELECT
                    r.*,
                    rp.id            AS reponse_id,
                    rp.auteur_admin,
                    rp.contenu       AS reponse_admin,   -- alias conservé pour la vue
                    rp.date_reponse
                FROM {$this->reclamationsTable} r
                LEFT JOIN {$this->reponseTable} rp ON rp.reclamation_id = r.id
                ORDER BY r.date_creation DESC";
        return $this->db->query($sql)->fetchAll();
    }

    // ── READ BY USER ─────────────────────────────────────────────
    public function getByUserId(int $userId): array {
        $sql = "SELECT
                    r.*,
                    rp.id            AS reponse_id,
                    rp.auteur_admin,
                    rp.contenu       AS reponse_admin,
                    rp.date_reponse
                FROM {$this->reclamationsTable} r
                LEFT JOIN {$this->reponseTable} rp ON rp.reclamation_id = r.id
                WHERE r.utilisateur_id = :user_id
                ORDER BY r.date_creation DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    // ── READ ONE ─────────────────────────────────────────────────
    public function getById(int $id): array|false {
        $sql = "SELECT
                    r.*,
                    rp.id            AS reponse_id,
                    rp.auteur_admin,
                    rp.contenu       AS reponse_admin,
                    rp.date_reponse
                FROM {$this->reclamationsTable} r
                LEFT JOIN {$this->reponseTable} rp ON rp.reclamation_id = r.id
                WHERE r.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    // ── UPDATE RECLAMATION ───────────────────────────────────────
    public function update(int $id, array $data): bool {
        $sql = "UPDATE {$this->reclamationsTable} SET
                    titre       = :titre,
                    description = :description,
                    categorie   = :categorie,
                    priorite    = :priorite,
                    statut      = :statut
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id'          => $id,
            ':titre'       => $data['titre'],
            ':description' => $data['description'],
            ':categorie'   => $data['categorie'],
            ':priorite'    => $data['priorite'],
            ':statut'      => $data['statut'] ?? 'en_attente',
        ]);
    }

    // ── UPDATE STATUT ONLY ───────────────────────────────────────
    public function updateStatut(int $id, string $statut): bool {
        $stmt = $this->db->prepare(
            "UPDATE {$this->reclamationsTable} SET statut = :statut WHERE id = :id"
        );
        return $stmt->execute([':id' => $id, ':statut' => $statut]);
    }

    // ── DELETE RECLAMATION ───────────────────────────────────────
    // La réponse liée est supprimée automatiquement (ON DELETE CASCADE)
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM {$this->reclamationsTable} WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    // ────────────────────────────────────────────────────────────
    // MÉTHODES LIÉES À LA TABLE reponse
    // ────────────────────────────────────────────────────────────

    /**
     * Ajoute ou met à jour la réponse admin d'une réclamation.
     * Si une réponse existe déjà → UPDATE, sinon → INSERT.
     */
    public function upsertReponse(int $reclamationId, string $contenu, string $auteur = 'Admin'): bool {
        // Vérifier si une réponse existe déjà
        $check = $this->db->prepare(
            "SELECT id FROM {$this->reponseTable} WHERE reclamation_id = :rid"
        );
        $check->execute([':rid' => $reclamationId]);
        $existing = $check->fetch();

        if ($existing) {
            $stmt = $this->db->prepare(
                "UPDATE {$this->reponseTable}
                 SET contenu = :contenu, auteur_admin = :auteur, date_reponse = NOW()
                 WHERE reclamation_id = :rid"
            );
        } else {
            $stmt = $this->db->prepare(
                "INSERT INTO {$this->reponseTable} (reclamation_id, auteur_admin, contenu)
                 VALUES (:rid, :auteur, :contenu)"
            );
        }

        return $stmt->execute([
            ':rid'     => $reclamationId,
            ':auteur'  => $auteur,
            ':contenu' => $contenu,
        ]);
    }

    /**
     * Récupère la réponse d'une réclamation (ou false).
     */
    public function getReponse(int $reclamationId): array|false {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->reponseTable} WHERE reclamation_id = :rid"
        );
        $stmt->execute([':rid' => $reclamationId]);
        return $stmt->fetch();
    }

    /**
     * Supprime uniquement la réponse (sans toucher la réclamation).
     */
    public function deleteReponse(int $reclamationId): bool {
        $stmt = $this->db->prepare(
            "DELETE FROM {$this->reponseTable} WHERE reclamation_id = :rid"
        );
        return $stmt->execute([':rid' => $reclamationId]);
    }

    // ── STATS ────────────────────────────────────────────────────
    public function getStats(): array {
        $sql = "SELECT
                    COUNT(*)                    AS total,
                    SUM(statut='en_attente')    AS en_attente,
                    SUM(statut='en_cours')      AS en_cours,
                    SUM(statut='resolue')       AS resolue,
                    SUM(statut='rejetee')       AS rejetee,
                    SUM(priorite='elevee')      AS priorite_elevee
                FROM {$this->reclamationsTable}";
        return $this->db->query($sql)->fetch();
    }
}
?>