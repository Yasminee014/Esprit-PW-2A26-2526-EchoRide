<?php
declare(strict_types=1);

require_once __DIR__ . '/../Model/LostFoundFrontRepository.php';
require_once __DIR__ . '/../Model/LostFoundProximityService.php';
require_once __DIR__ . '/../Config/Database.php';

final class LostFoundFrontController
{
    private const NOTIFICATION_EMAIL = 'abdelmalakgafsi@gmail.com';

    private LostFoundProximityService $proximityService;
    private LostFoundFrontRepository $repository;

    public function __construct(LostFoundFrontRepository $repository)
    {
        $this->repository = $repository;
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
            'statut' => isset($input['statut']) ? trim((string) $input['statut']) : null,
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
        $recipient = $this->notificationRecipient();
        if ($recipient === null) {
            return false;
        }

        $textBody = $this->buildDeclarationNotificationBody($action, $id, $data, $targeting);
        $htmlBody = $this->buildDeclarationNotificationHtml($subject, $action, $id, $data, $targeting);
        $boundary = '=_EcoRide_' . bin2hex(random_bytes(8));

        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
            'From: Lost & Found <no-reply@localhost>',
            'Reply-To: no-reply@localhost',
        ];

        $message = "--$boundary\r\n"
            . "Content-Type: text/plain; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: 8bit\r\n\r\n"
            . $textBody . "\r\n\r\n"
            . "--$boundary\r\n"
            . "Content-Type: text/html; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: 8bit\r\n\r\n"
            . $htmlBody . "\r\n\r\n"
            . "--$boundary--";

        $sent = @mail($recipient, $subject, $message, implode("\r\n", $headers));
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

    private function buildDeclarationNotificationBody(string $action, int $id, array $data, ?array $targeting): string
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
                . ($targeting ? "\n" . (string) ($targeting['message'] ?? '') : '')
                . "\n\nMessage genere automatiquement par le module front.";
    }

            private function buildDeclarationNotificationHtml(string $subject, string $action, int $id, array $data, ?array $targeting): string
    {
        $titre = htmlspecialchars((string) ($data['titre'] ?? $data['title'] ?? ''), ENT_QUOTES, 'UTF-8');
        $description = htmlspecialchars((string) ($data['description'] ?? ''), ENT_QUOTES, 'UTF-8');
        $categorie = htmlspecialchars((string) ($data['categorie'] ?? ''), ENT_QUOTES, 'UTF-8');
        $lieu = htmlspecialchars((string) ($data['lieu_perte'] ?? ''), ENT_QUOTES, 'UTF-8');
        $datePerte = htmlspecialchars((string) ($data['date_perte'] ?? ''), ENT_QUOTES, 'UTF-8');
        $statut = htmlspecialchars((string) ($data['statut'] ?? 'perdu'), ENT_QUOTES, 'UTF-8');
        $safeSubject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
        $safeAction = htmlspecialchars($action, ENT_QUOTES, 'UTF-8');
        $safeId = htmlspecialchars((string) $id, ENT_QUOTES, 'UTF-8');
        $logoUrl = htmlspecialchars($this->notificationLogoUrl(), ENT_QUOTES, 'UTF-8');
        $adminUrl = htmlspecialchars($this->notificationAdminUrl(), ENT_QUOTES, 'UTF-8');
        $targetingHtml = $this->buildTargetingHtml($targeting);

        return '<!DOCTYPE html>'
            . '<html lang="fr">'
            . '<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>'
            . '<body style="margin:0;padding:0;background:#eef3f8;font-family:Segoe UI,Arial,sans-serif;color:#1d2b3a;">'
            . '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#eef3f8;padding:24px 0;">'
            . '<tr><td align="center">'
            . '<table role="presentation" width="640" cellspacing="0" cellpadding="0" style="width:640px;max-width:92%;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #dbe5ef;">'
            . '<tr><td style="background:linear-gradient(135deg,#1976D2,#0F3B6E);padding:20px 24px;">'
            . '<table role="presentation" width="100%" cellspacing="0" cellpadding="0"><tr>'
            . '<td style="vertical-align:middle;">'
            . '<img src="' . $logoUrl . '" alt="EcoRide" width="52" height="52" style="display:block;border:0;outline:none;">'
            . '</td>'
            . '<td style="vertical-align:middle;padding-left:12px;color:#ffffff;">'
            . '<div style="font-size:22px;font-weight:700;letter-spacing:.5px;">ECO RIDE</div>'
            . '<div style="font-size:12px;opacity:.9;">Notification Lost &amp; Found</div>'
            . '</td>'
            . '</tr></table>'
            . '</td></tr>'
            . '<tr><td style="padding:22px 24px 10px 24px;">'
            . '<h1 style="margin:0 0 8px 0;font-size:22px;color:#0F3B6E;">' . $safeSubject . '</h1>'
            . '<p style="margin:0;font-size:14px;color:#4b5f74;">Une declaration a ete <strong>' . $safeAction . '</strong> dans le module EcoRide.</p>'
            . '</td></tr>'
            . '<tr><td style="padding:10px 24px 24px 24px;">'
            . '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;background:#f8fbff;border:1px solid #dbe7f3;border-radius:10px;overflow:hidden;">'
            . '<tr><td style="padding:12px 14px;border-bottom:1px solid #e3edf7;font-size:13px;color:#4d6075;width:38%;">ID declaration</td><td style="padding:12px 14px;border-bottom:1px solid #e3edf7;font-size:13px;font-weight:600;color:#11283f;">#' . $safeId . '</td></tr>'
            . '<tr><td style="padding:12px 14px;border-bottom:1px solid #e3edf7;font-size:13px;color:#4d6075;">Titre</td><td style="padding:12px 14px;border-bottom:1px solid #e3edf7;font-size:13px;font-weight:600;color:#11283f;">' . $titre . '</td></tr>'
            . '<tr><td style="padding:12px 14px;border-bottom:1px solid #e3edf7;font-size:13px;color:#4d6075;">Description</td><td style="padding:12px 14px;border-bottom:1px solid #e3edf7;font-size:13px;color:#11283f;">' . $description . '</td></tr>'
            . '<tr><td style="padding:12px 14px;border-bottom:1px solid #e3edf7;font-size:13px;color:#4d6075;">Categorie</td><td style="padding:12px 14px;border-bottom:1px solid #e3edf7;font-size:13px;color:#11283f;">' . $categorie . '</td></tr>'
            . '<tr><td style="padding:12px 14px;border-bottom:1px solid #e3edf7;font-size:13px;color:#4d6075;">Lieu de perte</td><td style="padding:12px 14px;border-bottom:1px solid #e3edf7;font-size:13px;color:#11283f;">' . $lieu . '</td></tr>'
            . '<tr><td style="padding:12px 14px;border-bottom:1px solid #e3edf7;font-size:13px;color:#4d6075;">Date de perte</td><td style="padding:12px 14px;border-bottom:1px solid #e3edf7;font-size:13px;color:#11283f;">' . $datePerte . '</td></tr>'
            . '<tr><td style="padding:12px 14px;font-size:13px;color:#4d6075;">Statut</td><td style="padding:12px 14px;font-size:13px;color:#11283f;font-weight:700;">' . $statut . '</td></tr>'
            . '</table>'
            . '<div style="margin-top:18px;text-align:center;">'
            . '<a href="' . $adminUrl . '" style="display:inline-block;padding:11px 18px;border-radius:22px;background:#1976D2;color:#ffffff;text-decoration:none;font-size:13px;font-weight:600;">Ouvrir le Back Office EcoRide</a>'
            . '</div>'
            . $targetingHtml
            . '<p style="margin:18px 0 0 0;font-size:12px;color:#6c8197;text-align:center;">Message automatique genere par EcoRide Lost &amp; Found.</p>'
            . '</td></tr>'
            . '</table>'
            . '</td></tr>'
            . '</table>'
            . '</body></html>';
    }

    private function buildTargetingHtml(?array $targeting): string
    {
        if ($targeting === null) {
            return '';
        }

        $message = htmlspecialchars((string) ($targeting['message'] ?? ''), ENT_QUOTES, 'UTF-8');
        $radius = htmlspecialchars(number_format((float) ($targeting['radius_km'] ?? 2.0), 1, '.', ''), ENT_QUOTES, 'UTF-8');
        $window = htmlspecialchars((string) ($targeting['window_minutes'] ?? 60), ENT_QUOTES, 'UTF-8');

        if ($message === '') {
            return '';
        }

        return '<div style="margin-top:18px;padding:14px 16px;border-radius:10px;background:#f3f8ff;border:1px solid #cfe1f7;">'
            . '<div style="font-size:12px;font-weight:700;color:#0F3B6E;margin-bottom:6px;">Alerte de proximite intelligente</div>'
            . '<div style="font-size:13px;color:#20354d;line-height:1.5;">' . $message . '</div>'
            . '<div style="font-size:11px;color:#5e748a;margin-top:6px;">Rayon: ' . $radius . ' km | Fenetre: ' . $window . ' min</div>'
            . '</div>';
    }

    private function notificationLogoUrl(): string
    {
        $envLogo = trim((string) (getenv('LOSTFOUND_EMAIL_LOGO_URL') ?: ''));
        if ($envLogo !== '') {
            return $envLogo;
        }

        return 'http://localhost/objet_perdu1/objet_perdu1/objet_perdu1/objet_perdu/view/assets/photo.png';
    }

    private function notificationAdminUrl(): string
    {
        $envUrl = trim((string) (getenv('LOSTFOUND_ADMIN_URL') ?: ''));
        if ($envUrl !== '') {
            return $envUrl;
        }

        return 'http://localhost/objet_perdu1/objet_perdu1/objet_perdu1/objet_perdu/view/Back%20office/lostfound_admin.php';
    }

    public function listByUserWithHistory(int $userId): array
    {
        if ($userId <= 0) {
            return [];
        }

        return $this->repository->findByUserWithHistory($userId);
    }

    public function getDeclarationWithHistory(int $declarationId): ?array
    {
        if ($declarationId <= 0) {
            return null;
        }

        return $this->repository->findDeclarationWithHistory($declarationId);
    }
}
