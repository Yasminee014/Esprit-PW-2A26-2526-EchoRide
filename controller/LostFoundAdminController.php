<?php
declare(strict_types=1);

require_once __DIR__ . '/../modele/LostFoundRepository.php';

final class LostFoundAdminController
{
    private const ALLOWED_STATUS = ['perdu', 'retrouve', 'restitue'];

    public function __construct(private readonly LostFoundRepository $repository)
    {
    }

    public function list(): array
    {
        return $this->repository->findAll();
    }

    public function create(array $input): array
    {
        $errors = $this->validateCreatePayload($input);

        if (!empty($errors)) {
            return ['ok' => false, 'errors' => $errors];
        }

        $id = $this->repository->create([
            'titre' => trim((string) $input['titre']),
            'description' => trim((string) $input['description']),
            'categorie' => trim((string) $input['categorie']),
            'lieu_perte' => isset($input['lieu_perte']) ? trim((string) $input['lieu_perte']) : null,
            'photo_url' => $input['photo_url'] ?? null,
            'date_perte' => (string) $input['date_perte'],
            'statut' => trim((string) $input['statut']),
            'trajet_id' => (int) $input['trajet_id'],
            'passager_id' => (int) $input['passager_id'],
            'anonyme_nom' => $input['anonyme_nom'] ?? null,
            'user_id' => isset($input['user_id']) && filter_var($input['user_id'], FILTER_VALIDATE_INT) !== false
                ? (int) $input['user_id']
                : null,
            'user_nom' => isset($input['user_nom']) ? trim((string) $input['user_nom']) : null,
        ]);

        return ['ok' => true, 'id' => $id];
    }

    public function changeStatus(int $id, string $status): array
    {
        $status = trim($status);

        if (!in_array($status, self::ALLOWED_STATUS, true)) {
            return ['ok' => false, 'errors' => ['statut' => 'Statut invalide.']];
        }

        return ['ok' => $this->repository->updateStatus($id, $status)];
    }

    private function validateCreatePayload(array $input): array
    {
        $errors = [];

        $title = trim((string) ($input['titre'] ?? ''));
        if ($title === '' || mb_strlen($title) < 3 || mb_strlen($title) > 100) {
            $errors['titre'] = 'Le titre doit contenir entre 3 et 100 caracteres.';
        }

        $description = trim((string) ($input['description'] ?? ''));
        if ($description === '' || mb_strlen($description) < 10 || mb_strlen($description) > 1000) {
            $errors['description'] = 'La description doit contenir entre 10 et 1000 caracteres.';
        }

        $category = trim((string) ($input['categorie'] ?? ''));
        if ($category === '') {
            $errors['categorie'] = 'La categorie est obligatoire.';
        }

        $status = trim((string) ($input['statut'] ?? ''));
        if (!in_array($status, self::ALLOWED_STATUS, true)) {
            $errors['statut'] = 'Le statut est invalide.';
        }

        $date = (string) ($input['date_perte'] ?? '');
        $dateObj = DateTimeImmutable::createFromFormat('Y-m-d', $date);
        $today = new DateTimeImmutable('today');
        if (!$dateObj || $dateObj->format('Y-m-d') !== $date || $dateObj > $today) {
            $errors['date_perte'] = 'La date de perte est invalide.';
        }

        if (!isset($input['trajet_id']) || !filter_var($input['trajet_id'], FILTER_VALIDATE_INT)) {
            $errors['trajet_id'] = 'Le trajet est obligatoire.';
        }

        if (!isset($input['passager_id']) || !filter_var($input['passager_id'], FILTER_VALIDATE_INT)) {
            $errors['passager_id'] = 'Le passager est obligatoire.';
        }

        return $errors;
    }
}
