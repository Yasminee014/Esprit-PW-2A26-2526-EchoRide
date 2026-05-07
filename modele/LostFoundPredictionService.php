<?php
declare(strict_types=1);

final class LostFoundPredictionService
{
    private const RESOLVED_STATUS = ['retrouve', 'restitue'];

    /**
     * @param array<int, array<string, mixed>> $declarations
     * @param array<int, array<string, mixed>> $comments
     * @return array<int, array<string, mixed>>
     */
    public function enrichDeclarations(array $declarations, array $comments): array
    {
        $commentsByDeclaration = $this->groupCommentsByDeclaration($comments);
        $conducteurStats = $this->buildConducteurStats($declarations, $commentsByDeclaration);
        $categoryStats = $this->buildCategoryStats($declarations, $commentsByDeclaration);

        $enriched = [];
        foreach ($declarations as $declaration) {
            $prediction = $this->predictForDeclaration($declaration, $commentsByDeclaration, $categoryStats, $conducteurStats);
            $enriched[] = array_merge($declaration, $prediction);
        }

        return $enriched;
    }

    /**
     * @param array<int, array<string, mixed>> $comments
     * @return array<int, array<int, array<string, mixed>>>
     */
    private function groupCommentsByDeclaration(array $comments): array
    {
        $grouped = [];

        foreach ($comments as $comment) {
            $declarationId = (int) ($comment['declaration_id'] ?? 0);
            if ($declarationId <= 0) {
                continue;
            }

            if (!isset($grouped[$declarationId])) {
                $grouped[$declarationId] = [];
            }

            $grouped[$declarationId][] = $comment;
        }

        return $grouped;
    }

    /**
     * @param array<int, array<string, mixed>> $declarations
     * @param array<int, array<int, array<string, mixed>>> $commentsByDeclaration
     * @return array<string, array<string, float|int>>
     */
    private function buildCategoryStats(array $declarations, array $commentsByDeclaration): array
    {
        $stats = [];

        foreach ($declarations as $declaration) {
            $category = trim((string) ($declaration['categorie'] ?? 'autre'));
            if ($category === '') {
                $category = 'autre';
            }

            if (!isset($stats[$category])) {
                $stats[$category] = [
                    'total' => 0,
                    'resolved' => 0,
                    'resolved_48h' => 0,
                ];
            }

            $stats[$category]['total']++;
            $isResolved = $this->isResolved((string) ($declaration['statut'] ?? 'perdu'));
            if (!$isResolved) {
                continue;
            }

            $stats[$category]['resolved']++;
            $declarationId = (int) ($declaration['id'] ?? 0);
            $lostDate = $this->parseLostDate($declaration['date_perte'] ?? null);
            $firstCommentAt = $this->getFirstCommentDateTime($commentsByDeclaration[$declarationId] ?? []);

            if ($lostDate !== null && $firstCommentAt !== null) {
                $hours = ($firstCommentAt->getTimestamp() - $lostDate->getTimestamp()) / 3600;
                if ($hours >= 0 && $hours <= 48) {
                    $stats[$category]['resolved_48h']++;
                }
            }
        }

        return $stats;
    }

    /**
     * @param array<int, array<string, mixed>> $declarations
     * @param array<int, array<int, array<string, mixed>>> $commentsByDeclaration
     * @return array<int, array<string, float|int>>
     */
    private function buildConducteurStats(array $declarations, array $commentsByDeclaration): array
    {
        $declarationById = [];
        foreach ($declarations as $declaration) {
            $id = (int) ($declaration['id'] ?? 0);
            if ($id > 0) {
                $declarationById[$id] = $declaration;
            }
        }

        $stats = [];

        foreach ($commentsByDeclaration as $declarationId => $comments) {
            $declaration = $declarationById[$declarationId] ?? null;
            if ($declaration === null) {
                continue;
            }

            $isResolved = $this->isResolved((string) ($declaration['statut'] ?? 'perdu'));

            foreach ($comments as $comment) {
                $conducteurId = (int) ($comment['user_id'] ?? 0);
                if ($conducteurId <= 0) {
                    continue;
                }

                if (!isset($stats[$conducteurId])) {
                    $stats[$conducteurId] = [
                        'touched' => 0,
                        'resolved_touched' => 0,
                    ];
                }

                $stats[$conducteurId]['touched']++;
                if ($isResolved) {
                    $stats[$conducteurId]['resolved_touched']++;
                }
            }
        }

        return $stats;
    }

    /**
     * @param array<string, mixed> $declaration
     * @param array<int, array<int, array<string, mixed>>> $commentsByDeclaration
     * @param array<string, array<string, float|int>> $categoryStats
     * @param array<int, array<string, float|int>> $conducteurStats
     * @return array<string, int|string>
     */
    private function predictForDeclaration(
        array $declaration,
        array $commentsByDeclaration,
        array $categoryStats,
        array $conducteurStats
    ): array {
        $category = trim((string) ($declaration['categorie'] ?? 'autre'));
        if ($category === '') {
            $category = 'autre';
        }

        $description = trim((string) ($declaration['description'] ?? ''));
        $status = (string) ($declaration['statut'] ?? 'perdu');
        $declarationId = (int) ($declaration['id'] ?? 0);
        $declarationComments = $commentsByDeclaration[$declarationId] ?? [];

        $score = 55.0;
        $stat = $categoryStats[$category] ?? ['total' => 0, 'resolved' => 0, 'resolved_48h' => 0];
        $total = (int) ($stat['total'] ?? 0);

        if ($total > 0) {
            $resolved = (int) ($stat['resolved'] ?? 0);
            $resolved48h = (int) ($stat['resolved_48h'] ?? 0);
            $resolveRate = $resolved / $total;
            $fastRate = $resolved > 0 ? ($resolved48h / $resolved) : 0.0;
            $score = 40 + ($resolveRate * 35) + ($fastRate * 25);
        }

        $keywordBoost = $this->computeKeywordBoost($description, $category);
        $score += $keywordBoost;

        $score += min(10, count($declarationComments) * 2);

        $score += $this->computeConducteurBoost($declarationComments, $conducteurStats);

        $daysSinceLoss = $this->computeDaysSinceLoss($declaration['date_perte'] ?? null);
        if ($daysSinceLoss > 0) {
            $score -= min(25, $daysSinceLoss * 1.2);
        }

        if ($status === 'retrouve') {
            $score = max($score, 90);
        }

        if ($status === 'restitue') {
            $score = 99;
        }

        $score = max(5, min(99, $score));
        $confidence = (int) round($score);

        $priority = $this->determinePriority($confidence, $description, $category);
        $etaHours = $this->estimateEtaHours($confidence);
        $etaLabel = $etaHours <= 48 ? 'sous 48h' : ($etaHours <= 96 ? 'sous 96h' : 'plus de 4 jours');
        $message = sprintf('%d%% de chances que cet objet soit restitue %s', $confidence, $etaLabel);

        return [
            'ml_confidence_score' => $confidence,
            'ml_eta_hours' => $etaHours,
            'ml_eta_label' => $etaLabel,
            'ml_priority' => $priority,
            'ml_message' => $message,
        ];
    }

    private function computeKeywordBoost(string $description, string $category): float
    {
        $text = mb_strtolower($description);

        $highValueKeywords = [
            'passeport' => 22,
            'carte identite' => 14,
            'carte d identite' => 14,
            'documents officiels' => 16,
            'cles' => 8,
            'cle' => 8,
            'portefeuille' => 10,
        ];

        $boost = 0.0;
        foreach ($highValueKeywords as $keyword => $value) {
            if (str_contains($text, $keyword)) {
                $boost += $value;
            }
        }

        if ($category === 'document') {
            $boost += 8;
        }

        return $boost;
    }

    /**
     * @param array<int, array<string, mixed>> $comments
     * @param array<int, array<string, float|int>> $conducteurStats
     */
    private function computeConducteurBoost(array $comments, array $conducteurStats): float
    {
        $boost = 0.0;

        foreach ($comments as $comment) {
            $conducteurId = (int) ($comment['user_id'] ?? 0);
            if ($conducteurId <= 0 || !isset($conducteurStats[$conducteurId])) {
                continue;
            }

            $touched = (int) ($conducteurStats[$conducteurId]['touched'] ?? 0);
            $resolvedTouched = (int) ($conducteurStats[$conducteurId]['resolved_touched'] ?? 0);
            if ($touched < 1) {
                continue;
            }

            $ratio = $resolvedTouched / $touched;
            $boost = max($boost, $ratio * 14);
        }

        return $boost;
    }

    private function determinePriority(int $confidence, string $description, string $category): string
    {
        $text = mb_strtolower($description);
        $isCriticalDocument = $category === 'document' && (
            str_contains($text, 'passeport') ||
            str_contains($text, 'carte identite') ||
            str_contains($text, 'carte d identite')
        );

        if ($isCriticalDocument || $confidence >= 85) {
            return 'high';
        }

        if ($confidence >= 60) {
            return 'medium';
        }

        return 'low';
    }

    private function estimateEtaHours(int $confidence): int
    {
        if ($confidence >= 90) {
            return 24;
        }

        if ($confidence >= 75) {
            return 48;
        }

        if ($confidence >= 55) {
            return 96;
        }

        return 168;
    }

    private function computeDaysSinceLoss(mixed $rawDate): int
    {
        $lostDate = $this->parseLostDate($rawDate);
        if ($lostDate === null) {
            return 0;
        }

        $today = new DateTimeImmutable('today');
        $diff = $lostDate->diff($today);

        return max(0, (int) $diff->days);
    }

    /**
     * @param array<int, array<string, mixed>> $comments
     */
    private function getFirstCommentDateTime(array $comments): ?DateTimeImmutable
    {
        $first = null;

        foreach ($comments as $comment) {
            $candidate = $this->parseDateTime($comment['created_at'] ?? null);
            if ($candidate === null) {
                continue;
            }

            if ($first === null || $candidate < $first) {
                $first = $candidate;
            }
        }

        return $first;
    }

    private function parseLostDate(mixed $rawDate): ?DateTimeImmutable
    {
        $value = trim((string) $rawDate);
        if ($value === '') {
            return null;
        }

        $parsed = DateTimeImmutable::createFromFormat('Y-m-d', $value);
        if ($parsed instanceof DateTimeImmutable && $parsed->format('Y-m-d') === $value) {
            return $parsed;
        }

        return null;
    }

    private function parseDateTime(mixed $rawDateTime): ?DateTimeImmutable
    {
        $value = trim((string) $rawDateTime);
        if ($value === '') {
            return null;
        }

        try {
            return new DateTimeImmutable($value);
        } catch (Exception) {
            return null;
        }
    }

    private function isResolved(string $status): bool
    {
        return in_array($status, self::RESOLVED_STATUS, true);
    }
}
