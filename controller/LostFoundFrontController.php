<?php
declare(strict_types=1);

require_once __DIR__ . '/../modele/LostFoundFrontRepository.php';

final class LostFoundFrontController
{
    private const NOTIFICATION_EMAIL = 'abdelmalakgafsi@gmail.com';

    public function __construct(private readonly LostFoundFrontRepository $repository)
    {
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
        $mailSent = $this->sendNotification('Nouvelle declaration publiee', $this->buildDeclarationNotificationBody('cree', $id, $payload));

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
            $this->sendNotification('Declaration mise a jour', $this->buildDeclarationNotificationBody('mise a jour', (int) $payload['id'], $payload));
        }

        return $updated;
    }

    public function deleteDeclaration(int $id): bool
    {
        $row = $this->repository->findById($id);
        $deleted = $this->repository->delete($id);

        if ($deleted && $row !== null) {
            $this->sendNotification('Declaration supprimee', $this->buildDeclarationNotificationBody('supprimee', $id, $row));
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

    private function sendNotification(string $subject, string $body): bool
    {
        $recipient = $this->notificationRecipient();
        if ($recipient === null) {
            return false;
        }

        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'From: Lost & Found <no-reply@localhost>',
            'Reply-To: no-reply@localhost',
        ];

        $sent = @mail($recipient, $subject, $body, implode("\r\n", $headers));
        if (!$sent) {
            error_log('LostFoundFrontController: email sending failed for ' . $recipient);
        }

        return $sent;
    }

    private function notificationRecipient(): ?string
    {
        $candidates = [
            self::NOTIFICATION_EMAIL,
            getenv('LOSTFOUND_NOTIFICATION_EMAIL') ?: null,
            $_SERVER['SERVER_ADMIN'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            $candidate = trim((string) $candidate);
            if ($candidate !== '' && filter_var($candidate, FILTER_VALIDATE_EMAIL)) {
                return $candidate;
            }
        }

        return null;
    }

    private function buildDeclarationNotificationBody(string $action, int $id, array $data): string
    {
        $titre = (string) ($data['titre'] ?? $data['title'] ?? '');
        $description = (string) ($data['description'] ?? '');
        $categorie = (string) ($data['categorie'] ?? '');
        $lieu = (string) ($data['lieu_perte'] ?? '');
        $datePerte = (string) ($data['date_perte'] ?? '');
        $statut = (string) ($data['statut'] ?? 'perdu');

        return "Une declaration a ete $action dans Lost & Found.\n\n"
            . "ID: $id\n"
            . "Titre: $titre\n"
            . "Description: $description\n"
            . "Categorie: $categorie\n"
            . "Lieu: $lieu\n"
            . "Date de perte: $datePerte\n"
            . "Statut: $statut\n\n"
            . "Message genere automatiquement par le module front.";
    }
}
