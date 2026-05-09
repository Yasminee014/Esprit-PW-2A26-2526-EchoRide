<?php
declare(strict_types=1);

final class LostFoundProximityService
{
    private const DEFAULT_RADIUS_KM = 2.0;
    private const DEFAULT_WINDOW_MINUTES = 60;

    /**
     * @var array<int, string>
     */
    private array $routeMap = [
        201 => 'Paris -> Lyon',
        202 => 'Lille -> Bruxelles',
        203 => 'Marseille -> Nice',
        204 => 'Bordeaux -> Toulouse',
        205 => 'Nantes -> Rennes',
    ];

    /**
     * @var array<string, array<int, string>>
     */
    private array $zoneKeywords = [
        'gare' => ['gare', 'station', 'train', 'sncf', 'rail', 'quai'],
        'centre' => ['centre', 'central', 'centre-ville', 'downtown'],
        'aeroport' => ['aeroport', 'airport', 'terminal', 'vol'],
        'universite' => ['universite', 'faculte', 'campus'],
        'port' => ['port', 'quai', 'marina'],
        'metro' => ['metro', 'station', 'tram', 'bus'],
    ];

    /**
     * @return array<int, array<string, mixed>>
     */
    public function findNearbyConductors(array $data): array
    {
        $location = $this->normalizeText((string) ($data['lieu_perte'] ?? ''));
        $description = $this->normalizeText((string) ($data['description'] ?? ''));
        $trips = [];

        foreach ($this->routeMap as $trajetId => $label) {
            $score = $this->scoreRouteMatch($location, $description, $label);
            if ($score < 35) {
                continue;
            }

            $trips[] = [
                'trajet_id' => $trajetId,
                'trajet_label' => $label,
                'match_score' => $score,
                'radius_km' => $this->currentRadiusKm(),
                'window_minutes' => $this->currentWindowMinutes(),
                'reason' => $this->buildReason($location, $label),
            ];
        }

        usort($trips, static fn(array $a, array $b): int => $b['match_score'] <=> $a['match_score']);

        return $trips;
    }

    public function buildTargetedAlertText(array $data): string
    {
        $matches = $this->findNearbyConductors($data);
        if ($matches === []) {
            return 'Aucun conducteur cible de proximite detecte pour cette declaration.';
        }

        $parts = [];
        foreach ($matches as $match) {
            $parts[] = sprintf(
                '#%d %s (proximite %d%%, rayon %.1f km, fenetre %d min)',
                (int) $match['trajet_id'],
                (string) $match['trajet_label'],
                (int) $match['match_score'],
                (float) $match['radius_km'],
                (int) $match['window_minutes']
            );
        }

        return 'Conducteurs cibles de proximite: ' . implode(' | ', $parts) . '.';
    }

    /**
     * @return array<string, mixed>
     */
    public function buildTargetedAlertData(array $data): array
    {
        $matches = $this->findNearbyConductors($data);
        $best = $matches[0] ?? null;

        return [
            'radius_km' => $this->currentRadiusKm(),
            'window_minutes' => $this->currentWindowMinutes(),
            'matches' => $matches,
            'best_match' => $best,
            'message' => $this->buildTargetedAlertText($data),
        ];
    }

    private function currentRadiusKm(): float
    {
        $value = (float) (getenv('LOSTFOUND_GEO_RADIUS_KM') ?: self::DEFAULT_RADIUS_KM);
        return $value > 0 ? $value : self::DEFAULT_RADIUS_KM;
    }

    private function currentWindowMinutes(): int
    {
        $value = (int) (getenv('LOSTFOUND_GEO_WINDOW_MINUTES') ?: self::DEFAULT_WINDOW_MINUTES);
        return $value > 0 ? $value : self::DEFAULT_WINDOW_MINUTES;
    }

    private function normalizeText(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = str_replace(['-', '_', '/', '\\', ','], ' ', $value);
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;

        return $value;
    }

    private function scoreRouteMatch(string $location, string $description, string $routeLabel): int
    {
        $haystack = $location . ' ' . $description . ' ' . $this->normalizeText($routeLabel);
        $score = 0;

        foreach ($this->zoneKeywords as $zone => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($haystack, $keyword)) {
                    $score += 8;
                }
            }
        }

        foreach ($this->extractRouteKeywords($routeLabel) as $keyword) {
            if ($keyword !== '' && str_contains($haystack, $keyword)) {
                $score += 12;
            }
        }

        if (str_contains($haystack, 'gare') && str_contains($this->normalizeText($routeLabel), 'paris')) {
            $score += 10;
        }

        if (str_contains($location, 'gare') || str_contains($description, 'gare')) {
            $score += 7;
        }

        return min(100, $score);
    }

    /**
     * @return array<int, string>
     */
    private function extractRouteKeywords(string $routeLabel): array
    {
        $normalized = $this->normalizeText($routeLabel);
        $parts = preg_split('/\s+|\-\>|->/', $normalized) ?: [];
        $keywords = [];

        foreach ($parts as $part) {
            $part = trim((string) $part);
            if ($part !== '' && mb_strlen($part) > 2) {
                $keywords[] = $part;
            }
        }

        return array_values(array_unique($keywords));
    }

    private function buildReason(string $location, string $routeLabel): string
    {
        if ($location === '') {
            return 'Zone de perte non precisee, rapprochement base sur le trajet.';
        }

        $routeNorm = $this->normalizeText($routeLabel);
        if (str_contains($location, 'gare') || str_contains($routeNorm, 'gare')) {
            return 'Zone gare detectee, trajet compatible avec la zone de perte.';
        }

        return 'Lieu de perte et trajet partagent des mots-clefs de zone.';
    }
}
