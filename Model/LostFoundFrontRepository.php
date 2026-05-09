<?php
declare(strict_types=1);

require_once __DIR__ . '/../Config/Database.php';

final class LostFoundFrontRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findPublished(): array
    {
        $sql = 'SELECT id, titre, description, categorie, lieu_perte, photo_url, date_perte, statut, trajet_id, passager_id, anonyme_nom
                FROM declarations
                ORDER BY id DESC';

        $stmt = $this->pdo->query($sql);

        return $stmt->fetchAll();
    }

    public function findByPassenger(int $passagerId): array
    {
        $sql = 'SELECT id, titre, description, categorie, lieu_perte, photo_url, date_perte, statut, trajet_id, passager_id, anonyme_nom
                FROM declarations
                WHERE passager_id = :passager_id
                ORDER BY id DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':passager_id' => $passagerId]);

        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $sql = 'SELECT id, titre, description, categorie, lieu_perte, photo_url, date_perte, statut, trajet_id, passager_id, anonyme_nom
                FROM declarations
                WHERE id = :id
                LIMIT 1';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO declarations (titre, description, categorie, lieu_perte, photo_url, date_perte, statut, trajet_id, passager_id, anonyme_nom, user_id, user_nom)
                VALUES (:titre, :description, :categorie, :lieu_perte, :photo_url, :date_perte, :statut, :trajet_id, :passager_id, :anonyme_nom, :user_id, :user_nom)';

        $stmt = $this->pdo->prepare($sql);
        
        $providedTrajetId = isset($data['trajet_id']) ? (int) $data['trajet_id'] : 0;
        $trajetId = null;
        if ($providedTrajetId > 0 && $this->trajetExists($providedTrajetId)) {
            $trajetId = $providedTrajetId;
        } else {
            $any = $this->getAnyTrajetId();
            if ($any !== null) {
                $trajetId = $any;
            } else {
                $trajetId = null;
            }
        }

        $stmt->execute([
            ':titre' => $data['titre'],
            ':description' => $data['description'],
            ':categorie' => $data['categorie'],
            ':lieu_perte' => $data['lieu_perte'] ?? null,
            ':photo_url' => $data['photo_url'] ?? null,
            ':date_perte' => $data['date_perte'],
            ':statut' => $data['statut'] ?? 'perdu',
            ':trajet_id' => $trajetId,
            ':passager_id' => $data['passager_id'] ?? null,
            ':anonyme_nom' => $data['anonyme_nom'] ?? null,
            ':user_id' => $data['user_id'] ?? null,
            ':user_nom' => $data['user_nom'] ?? null,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    private function trajetExists(int $id): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM trajet WHERE id_T = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        return (bool) $stmt->fetchColumn();
    }

    private function getAnyTrajetId(): ?int
    {
        $stmt = $this->pdo->query('SELECT id_T FROM trajet LIMIT 1');
        $row = $stmt->fetch();
        if ($row === false) {
            return null;
        }
        return isset($row['id_T']) ? (int) $row['id_T'] : null;
    }

    public function update(array $data): bool
    {
        $updates = [];
        $params = [];
        
        // Build dynamic SQL based on provided fields
        if (isset($data['titre'])) {
            $updates[] = 'titre = :titre';
            $params[':titre'] = $data['titre'];
        }
        if (isset($data['description'])) {
            $updates[] = 'description = :description';
            $params[':description'] = $data['description'];
        }
        if (isset($data['categorie'])) {
            $updates[] = 'categorie = :categorie';
            $params[':categorie'] = $data['categorie'];
        }
        if (isset($data['lieu_perte'])) {
            $updates[] = 'lieu_perte = :lieu_perte';
            $params[':lieu_perte'] = $data['lieu_perte'] ?? null;
        }
        if (isset($data['date_perte'])) {
            $updates[] = 'date_perte = :date_perte';
            $params[':date_perte'] = $data['date_perte'];
        }
        if (isset($data['statut'])) {
            $updates[] = 'statut = :statut';
            $params[':statut'] = $data['statut'];
        }
        
        if (empty($updates)) {
            return false;
        }
        
        $sql = 'UPDATE declarations SET ' . implode(', ', $updates) . ' WHERE id = :id';
        $params[':id'] = $data['id'];
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM declarations WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    public function findCommentsByDeclaration(int $declarationId): array
    {
        $sql = 'SELECT id, declaration_id, user_id, user_nom, message, parent_comment_id, created_at
                FROM commentaires
                WHERE declaration_id = :declaration_id
                ORDER BY created_at DESC, id DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':declaration_id' => $declarationId]);

        return $stmt->fetchAll();
    }

    public function findAllComments(): array
    {
        $stmt = $this->pdo->query('SELECT id, declaration_id, user_id, user_nom, message, parent_comment_id, created_at
                FROM commentaires
                ORDER BY id DESC');

        return $stmt->fetchAll();
    }

    public function addComment(
        int $declarationId,
        ?int $userId,
        ?string $userNom,
        string $message,
        ?int $parentCommentId = null
    ): int {
        $sql = 'INSERT INTO commentaires (declaration_id, user_id, user_nom, message, parent_comment_id)
                VALUES (:declaration_id, :user_id, :user_nom, :message, :parent_comment_id)';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':declaration_id' => $declarationId,
            ':user_id' => $userId,
            ':user_nom' => $userNom,
            ':message' => $message,
            ':parent_comment_id' => $parentCommentId,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findByUserWithHistory(int $userId): array
    {
        $sql = 'SELECT id, titre, description, categorie, lieu_perte, photo_url, date_perte, statut, trajet_id, passager_id, anonyme_nom
                FROM declarations
                WHERE user_id = :user_id OR passager_id = :passager_id
                ORDER BY id DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId, ':passager_id' => $userId]);

        return $stmt->fetchAll();
    }

    public function findDeclarationWithHistory(int $declarationId): ?array
    {
        $declaration = $this->findById($declarationId);
        if ($declaration === null) {
            return null;
        }

        $comments = $this->findCommentsByDeclaration($declarationId);
        
        $history = [];
        
        // Add creation event
        $history[] = [
            'type' => 'creation',
            'date' => $declaration['date_perte'] ?? date('Y-m-d'),
            'title' => 'Déclaration créée',
            'description' => 'Objet déclaré perdu',
            'statut' => 'perdu'
        ];
        
        // Add comments as timeline events
        foreach ($comments as $comment) {
            $history[] = [
                'type' => 'comment',
                'date' => $comment['created_at'] ?? date('Y-m-d H:i:s'),
                'title' => 'Mise à jour',
                'description' => $comment['message'] ?? '',
                'user_nom' => $comment['user_nom'] ?? 'Anonyme',
                'user_id' => $comment['user_id'] ?? null
            ];
        }
        
        // Sort by date
        usort($history, function($a, $b) {
            return strtotime($a['date']) - strtotime($b['date']);
        });
        
        return [
            'declaration' => $declaration,
            'history' => $history
        ];
    }
}