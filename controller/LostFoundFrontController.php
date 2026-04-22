<?php
declare(strict_types=1);

require_once __DIR__ . '/../modele/LostFoundFrontRepository.php';

final class LostFoundFrontController
{
    public function __construct(private readonly LostFoundFrontRepository $repository)
    {
    }

    public function listAll(): array
    {
        return $this->repository->findPublished();
    }

    public function listByPassenger(int $passagerId): array
    {
        if ($passagerId <= 0) {
            return [];
        }

        return $this->repository->findByPassenger($passagerId);
    }
}
