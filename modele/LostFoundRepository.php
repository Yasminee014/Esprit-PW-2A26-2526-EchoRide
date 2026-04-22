<?php
declare(strict_types=1);

require_once __DIR__ . '/Database.php';

final class LostFoundRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function findAll(): array
    {
        $sql = 'SELECT id, titre, description, categorie, lieu_perte, photo_url, date_perte, statut, trajet_id, passager_id, anonyme_nom
                FROM declarations
                ORDER BY id DESC';

        $stmt = $this->pdo->query($sql);

        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO declarations (titre, description, categorie, lieu_perte, photo_url, date_perte, statut, trajet_id, passager_id, anonyme_nom, user_id, user_nom)
            VALUES (:titre, :description, :categorie, :lieu_perte, :photo_url, :date_perte, :statut, :trajet_id, :passager_id, :anonyme_nom, :user_id, :user_nom)';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':titre' => $data['titre'],
            ':description' => $data['description'],
            ':categorie' => $data['categorie'],
            ':lieu_perte' => $data['lieu_perte'] ?? null,
            ':photo_url' => $data['photo_url'] ?? null,
            ':date_perte' => $data['date_perte'],
            ':statut' => $data['statut'],
            ':trajet_id' => $data['trajet_id'],
            ':passager_id' => $data['passager_id'],
            ':anonyme_nom' => $data['anonyme_nom'] ?? null,
            ':user_id' => $data['user_id'] ?? null,
            ':user_nom' => $data['user_nom'] ?? null,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function updateStatus(int $id, string $status): bool
    {
        $sql = 'UPDATE declarations SET statut = :statut WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':statut' => $status,
            ':id' => $id,
        ]);
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
}
