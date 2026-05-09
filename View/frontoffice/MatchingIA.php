<?php

require_once __DIR__ . '/../../Config/Database.php';

/**
 * MatchingIA — Moteur de matching intelligent passager ↔ trajet
 *
 * Principe :
 *  1. Calcul de distance géodésique (Haversine) entre villes
 *  2. Score de pertinence multi-critères pour chaque trajet
 *  3. Tolérance géographique configurable (rayon en km)
 *  4. Prise en compte des arrêts intermédiaires (table destination)
 *  5. Tri par score décroissant + explication lisible du match
 *
 * Fonctionne entièrement depuis les données existantes :
 *   tables  : trajet, destination, vehicules, users
 *   aucune  modification de schéma requise
 */
class MatchingIA {

    private PDO   $db;

    /* Coordonnées GPS des villes tunisiennes (sous-ensemble) */
    private array $COORDS = [
        'tunis'       => [36.8065, 10.1815],
        'sfax'        => [34.7406, 10.7603],
        'sousse'      => [35.8283, 10.6400],
        'monastir'    => [35.7776, 10.8262],
        'nabeul'      => [36.4561, 10.7376],
        'hammamet'    => [36.4000, 10.6167],
        'bizerte'     => [37.2744,  9.8739],
        'kairouan'    => [35.6812, 10.0969],
        'gabès'       => [33.8833, 10.1000],
        'gafsa'       => [34.4311,  8.7757],
        'tozeur'      => [33.9197,  8.1337],
        'medenine'    => [33.3549, 10.5055],
        'tataouine'   => [32.9210, 10.4510],
        'mahdia'      => [35.5047, 11.0622],
        'jendouba'    => [36.5011,  8.7803],
        'tabarka'     => [36.9544,  8.7581],
        'kasserine'   => [35.1667,  8.8167],
        'sidi bouzid' => [35.0380,  9.4841],
        'zaghouan'    => [36.4028, 10.1422],
        'kebili'      => [33.7044,  8.9694],
        'kef'         => [36.1822,  8.7147],
        'siliana'     => [36.0839,  9.3714],
        'beja'        => [36.7333,  9.1833],
        'enfidha'     => [36.1333, 10.3833],
        'grombalia'   => [36.6167, 10.5000],
        'soliman'     => [36.7000, 10.4833],
        'nabeul'      => [36.4561, 10.7376],
        'korba'       => [36.5833, 10.8667],
        'kelibia'     => [36.8500, 11.1000],
        'djerba'      => [33.8133, 10.8550],
        'zarzis'      => [33.5083, 11.1119],
        'ben gardane' => [33.1389, 11.2222],
        'el ajem'     => [35.2919, 10.5669],
        'msaken'      => [35.7297, 10.5783],
        'hammam sousse' => [35.8617, 10.5900],
        'sfax nord'   => [34.8000, 10.7500],
        'ariana'      => [36.8665, 10.1647],
        'manouba'     => [36.8097, 10.0997],
        'la marsa'    => [36.8775, 10.3222],
        'carthage'    => [36.8528, 10.3233],
        'sidi bou said' => [36.8703, 10.3411],
        'rades'       => [36.7667, 10.2833],
        'el fahs'     => [36.3619,  9.9047],
        'pont du fahs'=> [36.3619,  9.9047],
    ];

    /* Poids des critères de scoring */
    private const W_DEPART_EXACT   = 40;
    private const W_DEPART_PROCHE  = 28;
    private const W_ARRIVE_EXACT   = 40;
    private const W_ARRIVE_PROCHE  = 28;
    private const W_ARRET_DEPART   = 18;
    private const W_ARRET_ARRIVE   = 18;
    private const W_VEHICULE_DISPO = 10;
    private const W_SCORE_TRUST    = 4;

    /* Poids pour la recommandation de véhicules */
    private const W_VEH_HISTORIQUE = 30;
    private const W_VEH_POPULARITE = 20;
    private const W_VEH_CAPACITE   = 25;
    private const W_VEH_DISPO      = 50;
    private const W_VEH_TYPE       = 15;

    /* Rayon de tolérance par défaut (km) */
    private const RAYON_DEFAUT_KM  = 60;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /* ════════════════════════════════════════════════════════
       POINT D'ENTRÉE
    ════════════════════════════════════════════════════════ */

    /**
     * Retourne les trajets matchés et scorés.
     *
     * @param string $depart      Ville de départ saisie
     * @param string $arrivee     Ville d'arrivée saisie
     * @param int    $rayonKm     Rayon de tolérance géographique
     * @param int    $nbPlaces    Nombre de places souhaitées
     * @return array              Trajets triés par score décroissant
     */
    public function matcher(
        string $depart,
        string $arrivee,
        int    $rayonKm  = self::RAYON_DEFAUT_KM,
        int    $nbPlaces = 1
    ): array {

        $depart  = mb_strtolower(trim($depart));
        $arrivee = mb_strtolower(trim($arrivee));

        if ($depart === '' || $arrivee === '') return ['resultats' => [], 'suggestions' => []];

        $trajets = $this->chargerTousTrajets();
        if (empty($trajets)) return ['resultats' => [], 'suggestions' => []];

        $directResults = [];
        foreach ($trajets as $trajet) {
            $directMatch = $this->scoreDirectMatch($depart, $arrivee, $trajet, $rayonKm, $nbPlaces);
            if ($directMatch !== null) {
                $directResults[] = $directMatch;
            }
        }

        $multiResults = $this->matcherMultiLegs($depart, $arrivee, $trajets, $rayonKm);

        // Séparer les résultats exacts des autres
        $exactResults = array_filter($directResults, fn($r) => ($r['type_match'] ?? '') === 'exact');
        $otherResults = array_filter($directResults, fn($r) => ($r['type_match'] ?? '') !== 'exact');

        $allResults = array_merge($exactResults, $otherResults, $multiResults);
        usort($allResults, fn($a, $b) => $b['score_ia'] <=> $a['score_ia']);

        // Générer les suggestions si pas de résultats exacts
        $suggestions = [];
        if (empty($exactResults)) {
            $suggestions = $this->genererSuggestions($depart, $arrivee, $trajets, $rayonKm);
        }

        return [
            'resultats'   => $allResults,
            'suggestions' => $suggestions,
            'has_exact'   => !empty($exactResults)
        ];
    }

    /**
     * Recommande les meilleurs véhicules basés sur les critères IA.
     *
     * @param int    $nbPlaces    Nombre de places souhaitées
     * @param string $typeVehicule Type de véhicule préféré (marque ou catégorie)
     * @return array              Véhicules triés par score décroissant
     */
    public function recommendVehicles(int $nbPlaces = 1, string $typeVehicule = ''): array {
        $vehicules = $this->chargerTousVehicules();
        if (empty($vehicules)) return [];

        $resultats = [];
        foreach ($vehicules as $vehicule) {
            $score = $this->scoreVehicle($vehicule, $nbPlaces, $typeVehicule);
            if ($score > 0) {
                $resultats[] = array_merge($vehicule, [
                    'score_ia' => $score,
                    'pertinence' => min(100, (int)round($score / 100 * 100)), // Assuming max score 100
                    'raisons' => $this->getVehicleRaisons($vehicule, $nbPlaces, $typeVehicule)
                ]);
            }
        }

        usort($resultats, fn($a, $b) => $b['score_ia'] <=> $a['score_ia']);
        return $resultats;
    }

    /**
     * Score direct pour un trajet unique.
     */
    private function scoreDirectMatch(string $depart, string $arrivee, array $trajet, int $rayonKm, int $nbPlaces = 1): ?array {
        $departMatch  = $this->matchRoutePoint($depart, $trajet, $rayonKm, 'depart');
        $arriveeMatch = $this->matchRoutePoint($arrivee, $trajet, $rayonKm, 'arrivee');

        if (!$departMatch || !$arriveeMatch) {
            return null;
        }

        $score   = $departMatch['score'] + $arriveeMatch['score'];
        $raisons = [$departMatch['raison'], $arriveeMatch['raison']];

        if (($trajet['vehicule_statut'] ?? '') === 'disponible') {
            $score   += self::W_VEHICULE_DISPO;
            $raisons[] = ['type' => 'info', 'texte' => 'Véhicule disponible'];
        }

        $trustScore = (int)($trajet['trust_score'] ?? 0);
        $score     += (int)round($trustScore / 100 * self::W_SCORE_TRUST);

        // Ajouter le score du véhicule
        $vehicleScoreData = $this->scoreVehicleForTrajet($trajet, 1); // nbPlaces par défaut 1, peut être passé en param
        $score += $vehicleScoreData['score'];
        $raisons = array_merge($raisons, $vehicleScoreData['raisons']);

        if ($score <= 0) {
            return null;
        }

        $scoreMax   = self::W_DEPART_EXACT + self::W_ARRIVE_EXACT + self::W_VEHICULE_DISPO + self::W_SCORE_TRUST + self::W_VEH_HISTORIQUE + self::W_VEH_POPULARITE + self::W_VEH_CAPACITE + self::W_VEH_DISPO + self::W_VEH_TYPE;
        $pertinence = min(100, (int)round($score / $scoreMax * 100));
        $typeMatch  = ($departMatch['type'] === 'exact' && $arriveeMatch['type'] === 'exact') ? 'exact' : 'partiel';

        return array_merge($trajet, [
            'score_ia'   => $score,
            'pertinence' => $pertinence,
            'type_match' => $typeMatch,
            'raisons'    => $raisons,
            'vehicle_score' => $vehicleScoreData['score'],
            'vehicle_raisons' => $vehicleScoreData['raisons'],
            'legs'       => [[
                'point_depart' => $trajet['point_depart'],
                'point_arrive' => $trajet['point_arrive'],
                'marque'       => $trajet['marque'] ?? null,
                'modele'       => $trajet['modele'] ?? null,
                'vehicule_id'  => $trajet['vehicule_id'] ?? null,
            ]],
        ]);
    }

    private function matchRoutePoint(string $point, array $trajet, int $rayonKm, string $role): ?array {
        $pointNorm = $this->normaliser($point);
        $coords     = $this->coordonnees($point);
        $matchables = [];

        if ($role === 'depart') {
            $matchables[] = ['name' => $trajet['point_depart'] ?? '', 'weight_exact' => self::W_DEPART_EXACT, 'weight_proche' => self::W_DEPART_PROCHE, 'weight_arret' => self::W_ARRET_DEPART, 'label' => 'Départ'];
        } else {
            $matchables[] = ['name' => $trajet['point_arrive'] ?? '', 'weight_exact' => self::W_ARRIVE_EXACT, 'weight_proche' => self::W_ARRIVE_PROCHE, 'weight_arret' => self::W_ARRET_ARRIVE, 'label' => 'Arrivée'];
        }

        foreach ($trajet['arrets'] ?? [] as $arret) {
            $matchables[] = ['name' => $arret['nom'] ?? '', 'weight_exact' => 0, 'weight_proche' => 0, 'weight_arret' => ($role === 'depart' ? self::W_ARRET_DEPART : self::W_ARRET_ARRIVE), 'label' => 'Arrêt'];
        }

        foreach ($matchables as $node) {
            if ($node['name'] === '') {
                continue;
            }

            $nodeNorm = $this->normaliser($node['name']);

            if ($this->exactMatch($pointNorm, $nodeNorm)) {
                return [
                    'type'   => ($node['weight_exact'] > 0 ? 'exact' : 'arret'),
                    'score'  => $node['weight_exact'] > 0 ? $node['weight_exact'] : $node['weight_arret'],
                    'raison' => ['type' => 'exact', 'texte' => "{$node['label']} exact : {$node['name']}"],
                ];
            }

            if ($coords && ($coordsNode = $this->coordonnees($node['name']))) {
                $distance = $this->haversine($coords, $coordsNode);
                if ($distance <= $rayonKm) {
                    if ($node['weight_exact'] > 0) {
                        return [
                            'type'   => 'proche',
                            'score'  => (int)round($node['weight_proche'] * (1 - $distance / $rayonKm)),
                            'raison' => ['type' => 'proche', 'texte' => sprintf("%s proche (%.0f km de %s)", $node['label'], $distance, $node['name'])],
                        ];
                    }
                    return [
                        'type'   => 'arret',
                        'score'  => $node['weight_arret'],
                        'raison' => ['type' => 'arret', 'texte' => "{$node['label']} utilisable : {$node['name']}"],
                    ];
                }
            }
        }

        return null;
    }

    private function matcherMultiLegs(string $depart, string $arrivee, array $trajets, int $rayonKm): array {
        $multiResults = [];
        $nbTrajets = count($trajets);

        for ($i = 0; $i < $nbTrajets; $i++) {
            $premierTrajet = $trajets[$i];
            $departMatch   = $this->matchRoutePoint($depart, $premierTrajet, $rayonKm, 'depart');
            if (!$departMatch) {
                continue;
            }

            for ($j = 0; $j < $nbTrajets; $j++) {
                if ($i === $j) {
                    continue;
                }

                $secondTrajet = $trajets[$j];
                $arriveeMatch = $this->matchRoutePoint($arrivee, $secondTrajet, $rayonKm, 'arrivee');
                if (!$arriveeMatch) {
                    continue;
                }

                $connection = $this->findConnectionPoint($premierTrajet, $secondTrajet, $rayonKm);
                if (!$connection) {
                    continue;
                }

                $score = $departMatch['score'] + $arriveeMatch['score'] + $connection['score'];
                if (($premierTrajet['vehicule_statut'] ?? '') === 'disponible' || ($secondTrajet['vehicule_statut'] ?? '') === 'disponible') {
                    $score += self::W_VEHICULE_DISPO;
                }

                $trustScore = ((int)($premierTrajet['trust_score'] ?? 0) + (int)($secondTrajet['trust_score'] ?? 0)) / 2;
                $score += (int)round($trustScore / 100 * self::W_SCORE_TRUST);
                $score -= 12; // pénalité de transfert

                if ($score <= 0) {
                    continue;
                }

                $scoreMax   = self::W_DEPART_EXACT + self::W_ARRIVE_EXACT + self::W_VEHICULE_DISPO + self::W_SCORE_TRUST + 20;
                $pertinence = min(100, (int)round($score / $scoreMax * 100));

                $multiResults[] = [
                    'id_T'          => $premierTrajet['id_T'] . '_' . $secondTrajet['id_T'],
                    'point_depart'  => $premierTrajet['point_depart'],
                    'point_arrive'  => $secondTrajet['point_arrive'],
                    'prix_total'    => (string)((float)($premierTrajet['prix_total'] ?? 0) + (float)($secondTrajet['prix_total'] ?? 0)),
                    'distance_total'=> (string)((float)($premierTrajet['distance_total'] ?? 0) + (float)($secondTrajet['distance_total'] ?? 0)),
                    'marque'        => $premierTrajet['marque'] ?? null,
                    'modele'        => $premierTrajet['modele'] ?? null,
                    'couleur'       => $premierTrajet['couleur'] ?? null,
                    'capacite'      => $premierTrajet['capacite'] ?? null,
                    'climatisation' => $premierTrajet['climatisation'] ?? null,
                    'photo'         => $premierTrajet['photo'] ?? null,
                    'vehicule_statut'=> ($premierTrajet['vehicule_statut'] ?? '') === 'disponible' || ($secondTrajet['vehicule_statut'] ?? '') === 'disponible' ? 'disponible' : 'indisponible',
                    'conducteur_nom'=> $premierTrajet['conducteur_nom'] ?? null,
                    'conducteur_prenom'=> $premierTrajet['conducteur_prenom'] ?? null,
                    'trust_score'   => (int)round($trustScore),
                    'score_ia'      => $score,
                    'pertinence'    => $pertinence,
                    'type_match'    => 'multi',
                    'connection_point' => $connection['nom'],
                    'raisons'       => [
                        $departMatch['raison'],
                        $connection['raison'],
                        $arriveeMatch['raison'],
                    ],
                    'legs' => [
                        [
                            'point_depart' => $premierTrajet['point_depart'],
                            'point_arrive' => $premierTrajet['point_arrive'],
                            'marque'       => $premierTrajet['marque'] ?? null,
                            'modele'       => $premierTrajet['modele'] ?? null,
                            'vehicule_id'  => $premierTrajet['vehicule_id'] ?? null,
                        ],
                        [
                            'point_depart' => $secondTrajet['point_depart'],
                            'point_arrive' => $secondTrajet['point_arrive'],
                            'marque'       => $secondTrajet['marque'] ?? null,
                            'modele'       => $secondTrajet['modele'] ?? null,
                            'vehicule_id'  => $secondTrajet['vehicule_id'] ?? null,
                        ],
                    ],
                ];
            }
        }

        return $multiResults;
    }

    private function findConnectionPoint(array $first, array $second, int $rayonKm): ?array {
        $firstStops  = $this->routeConnectionPoints($first, ['arrivee', 'arret']);
        $secondStops = $this->routeConnectionPoints($second, ['depart', 'arret']);

        foreach ($firstStops as $p1) {
            foreach ($secondStops as $p2) {
                if ($p1['name'] === '' || $p2['name'] === '') {
                    continue;
                }

                if ($this->exactMatch($p1['norm'], $p2['norm'])) {
                    return [
                        'nom'   => $p1['name'],
                        'score' => 20,
                        'raison'=> ['type' => 'exact', 'texte' => "Transfert possible à {$p1['name']}"],
                    ];
                }

                $coords1 = $this->coordonnees($p1['name']);
                $coords2 = $this->coordonnees($p2['name']);
                if ($coords1 && $coords2) {
                    $distance = $this->haversine($coords1, $coords2);
                    if ($distance <= $rayonKm) {
                        return [
                            'nom'   => $p1['name'],
                            'score' => 12,
                            'raison'=> ['type' => 'proche', 'texte' => sprintf("Transfert possible autour de %s (%.0f km)", $p1['name'], $distance)],
                        ];
                    }
                }
            }
        }

        return null;
    }

    /**
     * Génère des suggestions IA quand aucun trajet direct n'est trouvé
     */
    private function genererSuggestions(string $depart, string $arrivee, array $trajets, int $rayonKm): array {
        $suggestions = [
            'trajets_passant' => [],
            'trajets_proches' => [],
            'multi_trajets'   => []
        ];

        // 1. Trajets qui passent par la destination
        foreach ($trajets as $trajet) {
            $routePoints = $this->routePoints($trajet, ['depart', 'arrivee', 'arret']);
            foreach ($routePoints as $point) {
                if ($this->exactMatch($point['norm'], $this->normaliser($arrivee))) {
                    $suggestions['trajets_passant'][] = array_merge($trajet, [
                        'point_match' => $point['name'],
                        'type' => 'passe_par'
                    ]);
                    break; // Un seul par trajet
                }
            }
        }

        // 2. Trajets proches (distance < tolérance)
        $coordsArrivee = $this->coordonnees($arrivee);
        if ($coordsArrivee) {
            foreach ($trajets as $trajet) {
                $routePoints = $this->routePoints($trajet, ['arrivee', 'arret']);
                foreach ($routePoints as $point) {
                    $coordsPoint = $this->coordonnees($point['name']);
                    if ($coordsPoint) {
                        $distance = $this->haversine($coordsArrivee, $coordsPoint);
                        if ($distance <= $rayonKm) {
                            $suggestions['trajets_proches'][] = array_merge($trajet, [
                                'point_match' => $point['name'],
                                'distance' => round($distance, 1),
                                'type' => 'proche'
                            ]);
                            break; // Un seul par trajet
                        }
                    }
                }
            }
        }

        // 3. Multi-trajets (combinaisons)
        $multiResults = $this->matcherMultiLegs($depart, $arrivee, $trajets, $rayonKm);
        $suggestions['multi_trajets'] = array_slice($multiResults, 0, 3); // Limiter à 3

        // Limiter chaque catégorie à 3 suggestions max
        $suggestions['trajets_passant'] = array_slice($suggestions['trajets_passant'], 0, 3);
        $suggestions['trajets_proches'] = array_slice($suggestions['trajets_proches'], 0, 3);

        return $suggestions;
    }

    private function routeConnectionPoints(array $trajet, array $allowedTypes): array {
        $points = [];

        $points[] = [
            'name' => $trajet['point_depart'] ?? '',
            'norm' => $this->normaliser($trajet['point_depart'] ?? ''),
            'type' => 'depart',
        ];
        $points[] = [
            'name' => $trajet['point_arrive'] ?? '',
            'norm' => $this->normaliser($trajet['point_arrive'] ?? ''),
            'type' => 'arrivee',
        ];

        foreach ($trajet['arrets'] ?? [] as $arret) {
            $points[] = [
                'name' => $arret['nom'] ?? '',
                'norm' => $this->normaliser($arret['nom'] ?? ''),
                'type' => 'arret',
            ];
        }

        return array_filter($points, fn($p) => in_array($p['type'], $allowedTypes, true));
    }

    private function routePoints(array $trajet, array $allowedTypes): array {
        return $this->routeConnectionPoints($trajet, $allowedTypes);
    }

    /* ════════════════════════════════════════════════════════
       MÉTHODES PRIVÉES
    ════════════════════════════════════════════════════════ */

    /** Charge tous les trajets avec leurs arrêts et le véhicule associé */
    private function chargerTousTrajets(): array {
        /* Trajets + véhicule + conducteur + réservations */
        $stmt = $this->db->query("
            SELECT t.*,
                   v.id         AS vehicule_id,
                   v.marque, v.modele, v.couleur,
                   v.capacite, v.climatisation, v.photo,
                   v.statut     AS vehicule_statut,
                   COUNT(r.id)  AS nb_reservations,
                   u.nom        AS conducteur_nom,
                   u.prenom     AS conducteur_prenom
            FROM trajet t
            LEFT JOIN vehicules v ON v.trajet_id = t.id_T
            LEFT JOIN users     u ON u.id = v.user_id
            LEFT JOIN reservations r ON r.vehicule_id = v.id
            GROUP BY t.id_T
            ORDER BY t.id_T DESC
        ");
        $trajets = $stmt->fetchAll();

        if (empty($trajets)) return [];

        /* Charger tous les arrêts en une requête */
        $allArrets = $this->db->query("
            SELECT * FROM destination
            WHERE ordre != 999
            ORDER BY trajet_id, ordre ASC
        ")->fetchAll();

        /* Indexer les arrêts par trajet_id */
        $arretsByTrajet = [];
        foreach ($allArrets as $a) {
            $arretsByTrajet[$a['trajet_id']][] = $a;
        }

        /* Injecter trust score et arrêts dans chaque trajet */
        foreach ($trajets as &$t) {
            $t['arrets']      = $arretsByTrajet[$t['id_T']] ?? [];
            $t['trust_score'] = $this->calculerTrustScore($t);
        }
        unset($t);

        return $trajets;
    }

    /** Charge tous les véhicules avec leur historique de réservations */
    private function chargerTousVehicules(): array {
        $stmt = $this->db->query("
            SELECT v.*, u.nom, u.prenom,
                   COUNT(r.id) AS nb_reservations
            FROM vehicules v
            LEFT JOIN users u ON v.user_id = u.id
            LEFT JOIN reservations r ON v.id = r.vehicule_id
            GROUP BY v.id
            ORDER BY v.id DESC
        ");
        $vehicules = $stmt->fetchAll();
        return array_map([$this, 'augmenterVehicule'], $vehicules);
    }

    private function augmenterVehicule(array $vehicule): array {
        $vehicule['nb_reservations'] = (int)($vehicule['nb_reservations'] ?? 0);
        $vehicule['popularite'] = $vehicule['nb_reservations']; // Pour l'instant, popularité = historique
        return $vehicule;
    }

    /** Score un véhicule dans le contexte d'un trajet */
    private function scoreVehicleForTrajet(array $trajet, int $nbPlaces): array {
        $score = 0;
        $raisons = [];

        // Historique des réservations
        $nbResa = (int)($trajet['nb_reservations'] ?? 0);
        if ($nbResa > 0) {
            $score += min(self::W_VEH_HISTORIQUE, $nbResa * 5);
            $raisons[] = ['type' => 'info', 'texte' => "{$nbResa} réservation(s) historique(s)"];
        }

        // Popularité (même que historique)
        $pop = $nbResa; // Pour l'instant
        if ($pop > 0) {
            $score += min(self::W_VEH_POPULARITE, $pop * 4);
        }

        // Capacité
        $capacite = (int)($trajet['capacite'] ?? 4);
        if ($capacite >= $nbPlaces) {
            $score += self::W_VEH_CAPACITE;
            $raisons[] = ['type' => 'success', 'texte' => "Capacité suffisante ({$capacite} places)"];
        } elseif ($capacite >= $nbPlaces - 1) {
            $score += self::W_VEH_CAPACITE * 0.8;
            $raisons[] = ['type' => 'warning', 'texte' => "Capacité limitée ({$capacite} places)"];
        } else {
            $raisons[] = ['type' => 'error', 'texte' => "Capacité insuffisante ({$capacite} places)"];
        }

        // Disponibilité
        if (($trajet['vehicule_statut'] ?? '') === 'disponible') {
            $score += self::W_VEH_DISPO;
            $raisons[] = ['type' => 'success', 'texte' => 'Véhicule disponible'];
        } else {
            $raisons[] = ['type' => 'error', 'texte' => 'Véhicule indisponible'];
        }

        // Type véhicule (pas de type spécifique demandé, donc pas de bonus)

        return ['score' => (int)round($score), 'raisons' => $raisons];
    }

    /** Génère les raisons pour le score du véhicule */
    private function getVehicleRaisons(array $vehicule, int $nbPlaces, string $typeVehicule): array {
        $raisons = [];

        $nbResa = $vehicule['nb_reservations'] ?? 0;
        if ($nbResa > 0) {
            $raisons[] = ['type' => 'info', 'texte' => "{$nbResa} réservation(s) historique(s)"];
        }

        $capacite = (int)($vehicule['capacite'] ?? 4);
        if ($capacite >= $nbPlaces) {
            $raisons[] = ['type' => 'success', 'texte' => "Capacité suffisante ({$capacite} places)"];
        } else {
            $raisons[] = ['type' => 'warning', 'texte' => "Capacité limitée ({$capacite} places)"];
        }

        if (($vehicule['statut'] ?? '') === 'disponible') {
            $raisons[] = ['type' => 'success', 'texte' => 'Véhicule disponible'];
        } else {
            $raisons[] = ['type' => 'error', 'texte' => 'Véhicule indisponible'];
        }

        $marque = $vehicule['marque'] ?? '';
        if ($typeVehicule !== '' && str_contains(mb_strtolower($marque), mb_strtolower($typeVehicule))) {
            $raisons[] = ['type' => 'info', 'texte' => "Type préféré : {$marque}"];
        }

        return $raisons;
    }

    /** Formule Haversine — distance en km entre deux points GPS */
    private function haversine(array $a, array $b): float {
        [$lat1, $lon1] = $a;
        [$lat2, $lon2] = $b;
        $R   = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $h   = sin($dLat / 2) ** 2
             + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        return 2 * $R * asin(sqrt($h));
    }

    /** Retourne les coordonnées [lat, lon] d'une ville, ou null */
    private function coordonnees(string $ville): ?array {
        $ville = mb_strtolower(trim($ville));
        return $this->COORDS[$ville] ?? null;
    }

    /** Match exact ou quasi-exact (normalisation, accents) */
    private function exactMatch(string $a, string $b): bool {
        return $this->normaliser($a) === $this->normaliser($b)
            || str_contains($this->normaliser($b), $this->normaliser($a))
            || str_contains($this->normaliser($a), $this->normaliser($b));
    }

    private function normaliser(string $s): string {
        $s = mb_strtolower(trim($s));
        $s = strtr($s, ['é'=>'e','è'=>'e','ê'=>'e','ë'=>'e',
                         'à'=>'a','â'=>'a','ä'=>'a',
                         'î'=>'i','ï'=>'i',
                         'ô'=>'o','ö'=>'o',
                         'ù'=>'u','û'=>'u','ü'=>'u',
                         'ç'=>'c']);
        return $s;
    }

    /** Détermine le label du type de match */
    private function determinerTypeMatch(array $raisons): string {
        $types = array_column($raisons, 'type');
        if (in_array('exact', $types) && count(array_filter($types, fn($t) => $t === 'exact')) >= 2) {
            return 'parfait';
        }
        if (in_array('arret', $types)) return 'via_arret';
        if (in_array('proche', $types)) return 'proche';
        if (in_array('exact', $types))  return 'partiel';
        return 'indirect';
    }

    /** Trust score simplifié (réservations confirmées + statut) */
    private function calculerTrustScore(array $t): int {
        $base = 50;
        if (($t['vehicule_statut'] ?? '') === 'disponible') $base += 20;
        if (!empty($t['photo']))       $base += 15;
        if (!empty($t['climatisation'])) $base += 15;
        return min(100, $base);
    }

    /* ════════════════════════════════════════════════════════
       API JSON — appelée par le front
    ════════════════════════════════════════════════════════ */

    public function handleApiRequest(): void {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');

        $depart   = trim($_GET['depart']   ?? '');
        $arrivee  = trim($_GET['arrivee']  ?? '');
        $rayon    = max(10, min(300, (int)($_GET['rayon']  ?? self::RAYON_DEFAUT_KM)));
        $places   = max(1,  (int)($_GET['places'] ?? 1));

        if ($depart === '' || $arrivee === '') {
            echo json_encode(['success' => false, 'message' => 'Départ et arrivée requis.', 'resultats' => []]);
            exit;
        }

        $resultats = $this->matcher($depart, $arrivee, $rayon, $places);

        echo json_encode([
            'success'     => true,
            'depart'      => $depart,
            'arrivee'     => $arrivee,
            'rayon_km'    => $rayon,
            'total'       => count($resultats['resultats']),
            'resultats'   => $resultats['resultats'],
            'suggestions' => $resultats['suggestions'],
            'has_exact'   => $resultats['has_exact'],
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function handleVehicleApiRequest(): void {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');

        $places   = max(1,  (int)($_GET['places'] ?? 1));
        $type     = trim($_GET['type'] ?? '');

        $resultats = $this->recommendVehicles($places, $type);

        echo json_encode([
            'success'     => true,
            'places'      => $places,
            'type'        => $type,
            'total'       => count($resultats),
            'resultats'   => $resultats,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
?>
