<?php
require_once __DIR__ . '/../Config/Database.php';

class ChatbotModel {

    private PDO $db;
    private string $reclamationsTable;

    public function __construct(PDO $db) {
        $this->db = $db;
        $this->reclamationsTable = $this->resolveTableName(['reclamations', 'reclamation']);
    }

    private function resolveTableName(array $candidates): string {
        foreach ($candidates as $name) {
            if ($this->tableExists($name)) {
                return $name;
            }
        }
        return 'reclamations';
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

    /**
     * Récupère les dernières réclamations
     */
    public function getRecentClaims(int $limit = 5): array {
        $sql = sprintf(
            "SELECT id, titre, description, statut, date_creation FROM %s ORDER BY date_creation DESC LIMIT ?",
            $this->reclamationsTable
        );
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Récupère les statistiques des réclamations
     */
    public function getStats(): array {
        $sql = sprintf(
            "SELECT
                COUNT(*) as total,
                SUM(statut='en_attente') as en_attente,
                SUM(statut='en_cours') as en_cours,
                SUM(statut='resolue') as resolue,
                SUM(statut='rejetee') as rejetee
            FROM %s",
            $this->reclamationsTable
        );
        $stmt = $this->db->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Récupère une réclamation par ID
     */
    public function getClaimById(int $id): ?array {
        $sql = sprintf("SELECT * FROM %s WHERE id = ?", $this->reclamationsTable);
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
?>
