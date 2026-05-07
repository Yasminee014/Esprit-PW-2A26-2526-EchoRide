<?php
declare(strict_types=1);

require_once __DIR__ . '/../modele/LostFoundFrontRepository.php';
require_once __DIR__ . '/../modele/LostFoundProximityService.php';
require_once __DIR__ . '/../modele/EmailNotificationService.php';

final class LostFoundFrontController
{
    private const NOTIFICATION_EMAIL = 'abdelmalakgafsi@gmail.com';

    private readonly LostFoundProximityService $proximityService;

    public function __construct(private readonly LostFoundFrontRepository $repository)
    {
        $this->proximityService = new LostFoundProximityService();
    }

    public function listAll(): array
    {
        return $this->repository->findPublished();
    }

    public function create(array $input): array
    {
        $payload = [
            'titre' => trim((string) ($input['titre'] ?? '')),
            'description' => trim((string) ($input['description'] ?? '')),
            'categorie' => trim((string) ($input['categorie'] ?? '')),
            'lieu_perte' => trim((string) ($input['lieu_perte'] ?? '')),
            'photo_url' => trim((string) ($input['photo_url'] ?? '')),
            'date_perte' => trim((string) ($input['date_perte'] ?? '')),
            'statut' => trim((string) ($input['statut'] ?? 'perdu')),
            'trajet_id' => (int) ($input['trajet_id'] ?? 0),
            'passager_id' => isset($input['passager_id']) ? (int) $input['passager_id'] : null,
            'anonyme_nom' => trim((string) ($input['anonyme_nom'] ?? '')),
            'user_id' => isset($input['passager_id']) ? (int) $input['passager_id'] : null,
            'user_nom' => null,
        ];

        $id = $this->repository->create($payload);
        $targeting = $this->proximityService->buildTargetedAlertData($payload);
        $mailSent = $this->sendNotification('Nouvelle declaration publiee', 'cree', $id, $payload, $targeting);

        return ['ok' => true, 'id' => $id, 'mail_sent' => $mailSent];
    }

    public function listByPassenger(int $passagerId): array
    {
        if ($passagerId <= 0) {
            return [];
        }

        return $this->repository->findByPassenger($passagerId);
    }

    public function addComment(int $declarationId, ?int $conducteurId, string $message): int
    {
        return $this->repository->addComment($declarationId, $conducteurId, null, $message, null);
    }

    public function updateDeclaration(array $input): bool
    {
        $payload = [
            'id' => (int) ($input['id'] ?? 0),
            'titre' => trim((string) ($input['titre'] ?? '')),
            'description' => trim((string) ($input['description'] ?? '')),
            'categorie' => trim((string) ($input['categorie'] ?? '')),
            'lieu_perte' => trim((string) ($input['lieu_perte'] ?? '')),
            'date_perte' => trim((string) ($input['date_perte'] ?? '')),
        ];

        $updated = $this->repository->update($payload);
        if ($updated) {
            $targeting = $this->proximityService->buildTargetedAlertData($payload);
            $this->sendNotification('Declaration mise a jour', 'mise a jour', (int) $payload['id'], $payload, $targeting);
        }

        return $updated;
    }

    public function deleteDeclaration(int $id): bool
    {
        $row = $this->repository->findById($id);
        $deleted = $this->repository->delete($id);

        if ($deleted && $row !== null) {
            $this->sendNotification('Declaration supprimee', 'supprimee', $id, $row, null);
        }

        return $deleted;
    }

    public function findCommentsByDeclaration(int $declarationId): array
    {
        return $this->repository->findCommentsByDeclaration($declarationId);
    }

    public function listComments(): array
    {
        return $this->repository->findAllComments();
    }

    private function sendNotification(string $subject, string $action, int $id, array $data, ?array $targeting): bool
    {
        return EmailNotificationService::sendDeclarationNotification(
            $subject,
            $action,
            $id,
            $data,
            $targeting,
            self::NOTIFICATION_EMAIL
        );
    }
}
