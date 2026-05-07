<?php
require_once __DIR__ . '/Database.php';

/**
 * Model : ReclamationModel
 * Toutes les opérations CRUD sur la table `reclamations` via PDO.
 */
class ReclamationModel
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    /* ══════════════════════════════════════════
       READ
    ══════════════════════════════════════════ */

    /**
     * Retourne toutes les réclamations avec filtres optionnels.
     */
    public function getAll(
        string $search   = '',
        string $statut   = '',
        string $priorite = '',
        string $categorie = ''
    ): array {
        $conditions = [];
        $params     = [];

        if ($search !== '') {
            $conditions[] = "(r.titre LIKE :search OR r.description LIKE :search OR u.nom LIKE :search OR u.prenom LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }
        if ($statut !== '') {
            $conditions[] = "r.statut = :statut";
            $params[':statut'] = $statut;
        }
        if ($priorite !== '') {
            $conditions[] = "r.priorite = :priorite";
            $params[':priorite'] = $priorite;
        }
        if ($categorie !== '') {
            $conditions[] = "r.categorie = :categorie";
            $params[':categorie'] = $categorie;
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $sql = "SELECT r.*,
                       CONCAT(u.prenom, ' ', u.nom) AS nom_utilisateur,
                       u.email AS email_utilisateur
                FROM reclamations r
                LEFT JOIN utilisateurs u ON r.utilisateur_id = u.id
                {$where}
                ORDER BY r.date_creation DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Réclamations d'un utilisateur connecté (FrontOffice).
     */
    public function getByUser(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM reclamations
             WHERE utilisateur_id = :uid
             ORDER BY date_creation DESC"
        );
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Une seule réclamation par ID.
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT r.*, CONCAT(u.prenom,' ',u.nom) AS nom_utilisateur, u.email AS email_utilisateur
             FROM reclamations r
             LEFT JOIN utilisateurs u ON r.utilisateur_id = u.id
             WHERE r.id = :id"
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Comptage par statut pour le tableau de bord.
     */
    public function countByStatut(): array
    {
        $stmt = $this->pdo->query(
            "SELECT statut, COUNT(*) AS total FROM reclamations GROUP BY statut"
        );
        $counts = [
            'total'      => 0,
            'en_attente' => 0,
            'en_cours'   => 0,
            'resolue'    => 0,
            'rejetee'    => 0,
        ];
        foreach ($stmt->fetchAll() as $row) {
            $key = $row['statut'];
            if (isset($counts[$key])) {
                $counts[$key] = (int)$row['total'];
            }
            $counts['total'] += (int)$row['total'];
        }
        return $counts;
    }

    /* ══════════════════════════════════════════
       CREATE
    ══════════════════════════════════════════ */

    public function create(array $data): bool
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO reclamations
                (utilisateur_id, titre, description, categorie, priorite, statut, date_creation)
             VALUES
                (:uid, :titre, :description, :categorie, :priorite, 'en_attente', NOW())"
        );
        return $stmt->execute([
            ':uid'         => $data['utilisateur_id'],
            ':titre'       => $data['titre'],
            ':description' => $data['description'],
            ':categorie'   => $data['categorie'],
            ':priorite'    => $data['priorite'],
        ]);
    }

    /* ══════════════════════════════════════════
       UPDATE
    ══════════════════════════════════════════ */

    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE reclamations
             SET titre=:titre, description=:description,
                 categorie=:categorie, priorite=:priorite,
                 statut=:statut, reponse_admin=:reponse
             WHERE id=:id"
        );
        return $stmt->execute([
            ':titre'       => $data['titre'],
            ':description' => $data['description'],
            ':categorie'   => $data['categorie'],
            ':priorite'    => $data['priorite'],
            ':statut'      => $data['statut'],
            ':reponse'     => $data['reponse_admin'] ?? null,
            ':id'          => $id,
        ]);
    }

    public function updateStatut(int $id, string $statut): bool
    {
        $allowed = ['en_attente', 'en_cours', 'resolue', 'rejetee'];
        if (!in_array($statut, $allowed, true)) return false;

        $stmt = $this->pdo->prepare(
            "UPDATE reclamations SET statut=:s WHERE id=:id"
        );
        return $stmt->execute([':s' => $statut, ':id' => $id]);
    }

    /* ══════════════════════════════════════════
       DELETE
    ══════════════════════════════════════════ */

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM reclamations WHERE id=:id");
        return $stmt->execute([':id' => $id]);
    }
}
