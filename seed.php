<?php
/**
 * EcoRide - Script de peuplement complet de la base de données
 * Accès : http://localhost/ecoride/seed.php
 */

define('BASE_PATH', __DIR__ . DIRECTORY_SEPARATOR);
define('BASE_URL',  'http://localhost/ecoride/');

require_once __DIR__ . '/Config/Database.php';

$pdo = Database::getInstance();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$log = [];
$errors = [];

function run(PDO $pdo, string $sql, array $params = [], string $label = ''): void {
    global $log, $errors;
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        if ($label) $log[] = "✅ $label";
    } catch (PDOException $e) {
        $errors[] = "❌ $label : " . $e->getMessage();
    }
}

$pdo->exec("SET foreign_key_checks = 0");

// ═══════════════════════════════════════════════════════════
// 1. ADMINS
// ═══════════════════════════════════════════════════════════
$pdo->exec("TRUNCATE TABLE admins");

$admins = [
    ['Super Admin',  'admin@ecoride.fr',        'Admin@1234'],
    ['Marie Dupont', 'marie.admin@ecoride.fr',   'Admin@1234'],
];
foreach ($admins as $a) {
    run($pdo,
        "INSERT INTO admins (nom, email, password) VALUES (:nom, :email, :pwd)",
        [':nom' => $a[0], ':email' => $a[1], ':pwd' => password_hash($a[2], PASSWORD_DEFAULT)],
        "Admin: {$a[1]}"
    );
}

// ═══════════════════════════════════════════════════════════
// 2. USERS (passagers & conducteurs)
// ═══════════════════════════════════════════════════════════
$pdo->exec("TRUNCATE TABLE users");

$users = [
    ['Jean',      'Dupont',    'jean.dupont@mail.fr',      '0612345678', 'passager',   'actif'],
    ['Sophie',    'Martin',    'sophie.martin@mail.fr',    '0623456789', 'conducteur', 'actif'],
    ['Lucas',     'Bernard',   'lucas.bernard@mail.fr',    '0634567890', 'passager',   'actif'],
    ['Emma',      'Petit',     'emma.petit@mail.fr',       '0645678901', 'conducteur', 'actif'],
    ['Thomas',    'Moreau',    'thomas.moreau@mail.fr',    '0656789012', 'passager',   'actif'],
    ['Camille',   'Simon',     'camille.simon@mail.fr',    '0667890123', 'conducteur', 'actif'],
    ['Hugo',      'Laurent',   'hugo.laurent@mail.fr',     '0678901234', 'passager',   'actif'],
    ['Léa',       'Lefebvre',  'lea.lefebvre@mail.fr',     '0689012345', 'passager',   'actif'],
    ['Antoine',   'Leroy',     'antoine.leroy@mail.fr',    '0690123456', 'conducteur', 'actif'],
    ['Inès',      'Roux',      'ines.roux@mail.fr',        '0601234567', 'passager',   'inactif'],
    ['Paul',      'Fournier',  'paul.fournier@mail.fr',    '0611111111', 'conducteur', 'actif'],
    ['Chloé',     'Girard',    'chloe.girard@mail.fr',     '0622222222', 'passager',   'actif'],
    ['Mathieu',   'Bonnet',    'mathieu.bonnet@mail.fr',   '0633333333', 'passager',   'actif'],
    ['Julie',     'Dubois',    'julie.dubois@mail.fr',     '0644444444', 'conducteur', 'actif'],
    ['Romain',    'Richard',   'romain.richard@mail.fr',   '0655555555', 'passager',   'actif'],
];

foreach ($users as $u) {
    run($pdo,
        "INSERT INTO users (prenom, nom, email, telephone, role, statut, password, created_at)
         VALUES (:p, :n, :e, :t, :r, :s, :pwd, NOW())",
        [
            ':p'   => $u[0], ':n' => $u[1], ':e' => $u[2],
            ':t'   => $u[3], ':r' => $u[4], ':s' => $u[5],
            ':pwd' => password_hash('User@1234', PASSWORD_DEFAULT),
        ],
        "User: {$u[2]}"
    );
}

// ═══════════════════════════════════════════════════════════
// 3. TRAJETS
// ═══════════════════════════════════════════════════════════
$pdo->exec("TRUNCATE TABLE trajet");

$trajets = [
    [2,  'Paris',        'Lyon',          45.00, 465.0],
    [4,  'Lyon',         'Marseille',     35.00, 315.0],
    [6,  'Paris',        'Bordeaux',      55.00, 579.0],
    [9,  'Bordeaux',     'Toulouse',      20.00, 244.0],
    [11, 'Marseille',    'Nice',          18.00, 198.0],
    [14, 'Paris',        'Strasbourg',    40.00, 490.0],
    [2,  'Nantes',       'Paris',         30.00, 385.0],
    [4,  'Lille',        'Paris',         15.00, 225.0],
    [6,  'Paris',        'Rennes',        28.00, 350.0],
    [9,  'Lyon',         'Grenoble',      12.00, 104.0],
    [11, 'Toulouse',     'Montpellier',   22.00, 242.0],
    [14, 'Paris',        'Nantes',        32.00, 385.0],
];

foreach ($trajets as $t) {
    run($pdo,
        "INSERT INTO trajet (id_u, point_depart, point_arrive, prix_total, distance_total)
         VALUES (:u, :d, :a, :p, :dist)",
        [':u' => $t[0], ':d' => $t[1], ':a' => $t[2], ':p' => $t[3], ':dist' => $t[4]],
        "Trajet: {$t[1]} → {$t[2]}"
    );
}

// ═══════════════════════════════════════════════════════════
// 4. DESTINATIONS (arrêts intermédiaires)
// ═══════════════════════════════════════════════════════════
$pdo->exec("TRUNCATE TABLE destination");

$destinations = [
    [1, 'Beaune',       1, 155.0, 8.00],
    [1, 'Mâcon',        2, 310.0, 18.00],
    [2, 'Valence',      1, 110.0, 12.00],
    [3, 'Orléans',      1, 130.0, 15.00],
    [3, 'Tours',        2, 240.0, 25.00],
    [4, 'Agen',         1, 142.0, 10.00],
    [5, 'Toulon',       1, 65.0,  8.00],
    [6, 'Metz',         1, 295.0, 20.00],
    [7, 'Le Mans',      1, 200.0, 15.00],
    [10,'Chambéry',     1, 55.0,  6.00],
];

foreach ($destinations as $d) {
    run($pdo,
        "INSERT INTO destination (trajet_id, nom, ordre, distance, prix) VALUES (:t, :n, :o, :d, :p)",
        [':t' => $d[0], ':n' => $d[1], ':o' => $d[2], ':d' => $d[3], ':p' => $d[4]],
        "Destination: {$d[1]}"
    );
}

// ═══════════════════════════════════════════════════════════
// 5. VÉHICULES
// ═══════════════════════════════════════════════════════════
$pdo->exec("TRUNCATE TABLE vehicules");

$vehicules = [
    [2,  'Renault',    'Clio',       'AB-123-CD', 'Bleu',    5, 1, 'disponible',    1],
    [4,  'Peugeot',    '308',        'EF-456-GH', 'Blanc',   5, 1, 'disponible',    2],
    [6,  'Citroën',    'C3',         'IJ-789-KL', 'Rouge',   4, 0, 'disponible',    3],
    [9,  'Toyota',     'Yaris',      'MN-012-OP', 'Gris',    5, 1, 'disponible',    4],
    [11, 'Volkswagen', 'Golf',       'QR-345-ST', 'Noir',    5, 1, 'disponible',    5],
    [14, 'Ford',       'Focus',      'UV-678-WX', 'Argent',  5, 0, 'disponible',    6],
    [2,  'BMW',        'Série 3',    'YZ-901-AB', 'Bleu',    5, 1, 'indisponible',  7],
    [4,  'Mercedes',   'Classe A',   'CD-234-EF', 'Blanc',   5, 1, 'disponible',    8],
    [6,  'Audi',       'A3',         'GH-567-IJ', 'Gris',    5, 1, 'en_maintenance',9],
    [9,  'Opel',       'Corsa',      'KL-890-MN', 'Vert',    4, 0, 'disponible',    10],
    [11, 'Nissan',     'Micra',      'OP-123-QR', 'Jaune',   4, 0, 'disponible',    11],
    [14, 'Honda',      'Civic',      'ST-456-UV', 'Rouge',   5, 1, 'disponible',    12],
];

foreach ($vehicules as $v) {
    run($pdo,
        "INSERT INTO vehicules (user_id, marque, modele, immatriculation, couleur, capacite, climatisation, statut, trajet_id)
         VALUES (:uid, :ma, :mo, :im, :co, :ca, :cl, :st, :ti)",
        [
            ':uid' => $v[0], ':ma' => $v[1], ':mo' => $v[2], ':im' => $v[3],
            ':co'  => $v[4], ':ca' => $v[5], ':cl' => $v[6], ':st' => $v[7], ':ti' => $v[8],
        ],
        "Véhicule: {$v[1]} {$v[2]}"
    );
}

// ═══════════════════════════════════════════════════════════
// 6. RÉSERVATIONS
// ═══════════════════════════════════════════════════════════
$pdo->exec("TRUNCATE TABLE reservations");

$reservations = [
    [1,  1,  1,  '2026-06-01', '2026-06-01', '08:00', 2, 90.00,  'confirmee'],
    [2,  3,  2,  '2026-06-02', '2026-06-02', '09:30', 1, 35.00,  'confirmee'],
    [3,  5,  3,  '2026-06-03', '2026-06-03', '07:00', 3, 165.00, 'en_attente'],
    [4,  7,  4,  '2026-06-04', '2026-06-04', '10:00', 1, 20.00,  'confirmee'],
    [5,  8,  5,  '2026-06-05', '2026-06-05', '06:30', 2, 36.00,  'confirmee'],
    [6,  1,  6,  '2026-06-06', '2026-06-06', '08:00', 1, 40.00,  'annulee'],
    [7,  3,  7,  '2026-06-07', '2026-06-07', '11:00', 2, 60.00,  'confirmee'],
    [8,  5,  8,  '2026-06-08', '2026-06-08', '09:00', 1, 28.00,  'en_attente'],
    [1,  7,  1,  '2026-05-20', '2026-05-20', '08:00', 2, 90.00,  'confirmee'],
    [2,  8,  2,  '2026-05-18', '2026-05-18', '10:00', 1, 35.00,  'confirmee'],
    [3,  12, 3,  '2026-05-15', '2026-05-15', '07:30', 2, 110.00, 'annulee'],
    [4,  13, 4,  '2026-05-10', '2026-05-10', '09:00', 1, 20.00,  'confirmee'],
    [5,  15, 5,  '2026-05-05', '2026-05-05', '08:00', 3, 54.00,  'confirmee'],
    [10, 1,  10, '2026-06-10', '2026-06-10', '07:00', 1, 12.00,  'en_attente'],
    [11, 3,  11, '2026-06-12', '2026-06-12', '08:30', 2, 44.00,  'confirmee'],
];

foreach ($reservations as $r) {
    run($pdo,
        "INSERT INTO reservations (vehicule_id, user_id, trajet_id, date_debut, date_fin, heure, nb_places, prix_total, statut)
         VALUES (:vi, :ui, :ti, :dd, :df, :h, :nb, :pt, :st)",
        [
            ':vi' => $r[0], ':ui' => $r[1], ':ti' => $r[2],
            ':dd' => $r[3], ':df' => $r[4], ':h'  => $r[5],
            ':nb' => $r[6], ':pt' => $r[7], ':st' => $r[8],
        ],
        "Réservation v{$r[0]} u{$r[1]}"
    );
}

// ═══════════════════════════════════════════════════════════
// 7. PAIEMENTS
// ═══════════════════════════════════════════════════════════
$pdo->exec("TRUNCATE TABLE paiements");

$paiements = [
    [1,  'carte',     90.00,  'paye',      'TXN-001-2026'],
    [2,  'carte',     35.00,  'paye',      'TXN-002-2026'],
    [4,  'sur_place', 20.00,  'paye',      null],
    [5,  'virement',  36.00,  'paye',      'VIR-005-2026'],
    [7,  'carte',     60.00,  'paye',      'TXN-007-2026'],
    [9,  'carte',     90.00,  'paye',      'TXN-009-2026'],
    [10, 'carte',     35.00,  'paye',      'TXN-010-2026'],
    [12, 'sur_place', 20.00,  'paye',      null],
    [13, 'carte',     54.00,  'paye',      'TXN-013-2026'],
    [3,  'carte',     165.00, 'en_attente',null],
    [8,  'carte',     28.00,  'en_attente',null],
    [14, 'carte',     12.00,  'en_attente',null],
];

foreach ($paiements as $p) {
    run($pdo,
        "INSERT INTO paiements (reservation_id, mode_paiement, montant, statut, reference_transaction, date_paiement)
         VALUES (:ri, :mo, :mt, :st, :ref, NOW())",
        [':ri' => $p[0], ':mo' => $p[1], ':mt' => $p[2], ':st' => $p[3], ':ref' => $p[4]],
        "Paiement rés#{$p[0]}"
    );
}

// ═══════════════════════════════════════════════════════════
// 8. ÉVÉNEMENTS
// ═══════════════════════════════════════════════════════════
$pdo->exec("TRUNCATE TABLE evenements");

$evenements = [
    ['EcoRide Summit 2026',       'Grand rassemblement annuel des usagers du covoiturage écologique.',      'conference', 'Paris',     '2026-06-20 09:00:00', 200, 'ouvert',  'event1.jpg'],
    ['Green Mobility Expo',        'Salon des mobilités vertes et durables.',                                'salon',      'Lyon',      '2026-07-10 10:00:00', 500, 'ouvert',  'event2.jpg'],
    ['Atelier Covoiturage',        'Atelier pratique pour optimiser vos trajets en covoiturage.',            'atelier',    'Bordeaux',  '2026-06-15 14:00:00', 30,  'ouvert',  'event3.jpg'],
    ['Forum Mobilité Durable',     'Débats et conférences sur la mobilité de demain.',                       'forum',      'Marseille', '2026-08-05 09:00:00', 150, 'ouvert',  'event4.jpg'],
    ['Journée Sans Voiture',       'Événement national de sensibilisation aux alternatives à la voiture.',  'sensibilisation','Nantes', '2026-09-22 08:00:00', 1000,'ouvert', 'event5.jpg'],
    ['Conférence IA & Transport',  'Comment l\'IA transforme les transports et le covoiturage.',             'conference', 'Toulouse',  '2026-07-25 10:00:00', 80,  'ouvert',  'event6.jpg'],
    ['Meet & Ride Paris',          'Rencontre conviviale entre conducteurs et passagers EcoRide.',           'meetup',     'Paris',     '2026-06-28 18:00:00', 60,  'ouvert',  'event7.jpg'],
    ['Hackathon Mobilité Verte',   'Challenge de 48h pour créer des solutions de mobilité écologique.',     'hackathon',  'Strasbourg','2026-10-03 08:00:00', 120, 'ouvert',  'event8.jpg'],
    ['Webinaire Sécurité Route',   'Conseils de sécurité pour les conducteurs et passagers.',                'webinaire',  'En ligne',  '2026-06-05 20:00:00', 500, 'complet', 'event9.jpg'],
    ['Fête EcoRide Anniversaire',  'Célébration des 3 ans de la plateforme EcoRide.',                        'fête',       'Paris',     '2026-11-15 16:00:00', 300, 'ouvert',  'event10.jpg'],
];

foreach ($evenements as $e) {
    run($pdo,
        "INSERT INTO evenements (titre, description, type, ville, date_evenement, nb_places, statut, image)
         VALUES (:ti, :de, :ty, :vi, :da, :nb, :st, :im)",
        [
            ':ti' => $e[0], ':de' => $e[1], ':ty' => $e[2], ':vi' => $e[3],
            ':da' => $e[4], ':nb' => $e[5], ':st' => $e[6], ':im' => $e[7],
        ],
        "Événement: {$e[0]}"
    );
}

// ═══════════════════════════════════════════════════════════
// 9. SPONSORS
// ═══════════════════════════════════════════════════════════
$pdo->exec("TRUNCATE TABLE sponsors");

$sponsors = [
    ['TotalEnergies',   15000.00, 'platine',  'confirme', 1],
    ['Renault Group',   10000.00, 'or',       'confirme', 1],
    ['SNCF Connect',    8000.00,  'or',       'confirme', 2],
    ['BlaBlaCar',       5000.00,  'argent',   'confirme', 2],
    ['Michelin',        4000.00,  'argent',   'confirme', 3],
    ['Vinci Autoroutes',3000.00,  'bronze',   'confirme', 4],
    ['Décathlon',       2500.00,  'bronze',   'confirme', 5],
    ['Groupama',        2000.00,  'bronze',   'en_attente',null],
    ['Enedis',          1500.00,  'bronze',   'en_attente',null],
    ['CentraleSupélec', 1000.00,  'bronze',   'confirme', 8],
];

foreach ($sponsors as $s) {
    run($pdo,
        "INSERT INTO sponsors (nom_entreprise, montant_sponsoring, type_sponsor, statut, evenement_id)
         VALUES (:ne, :mo, :ty, :st, :ei)",
        [':ne' => $s[0], ':mo' => $s[1], ':ty' => $s[2], ':st' => $s[3], ':ei' => $s[4]],
        "Sponsor: {$s[0]}"
    );
}

// ═══════════════════════════════════════════════════════════
// 10. RÉCLAMATIONS
// ═══════════════════════════════════════════════════════════
$pdo->exec("TRUNCATE TABLE reclamations");

$reclamations = [
    [1,  'Conducteur en retard',          'Le conducteur avait 45 min de retard sans prévenir.',                      'retard',     'haute',   'resolue'],
    [3,  'Véhicule en mauvais état',      'La voiture avait un problème de climatisation malgré l\'annonce.',         'vehicule',   'moyenne', 'en_cours'],
    [5,  'Annulation de dernière minute', 'Le conducteur a annulé 1h avant le départ sans remboursement.',            'annulation', 'haute',   'resolue'],
    [7,  'Comportement inapproprié',      'Le conducteur a tenu des propos déplacés durant le trajet.',               'comportement','urgente','en_cours'],
    [8,  'Problème de paiement',          'J\'ai été débité deux fois pour la même réservation.',                     'paiement',   'haute',   'resolue'],
    [12, 'Trajet non effectué',           'La réservation était confirmée mais le conducteur ne s\'est pas présenté.','absence',    'haute',   'en_attente'],
    [13, 'Application buguée',            'L\'application plante lors de la réservation sur mobile.',                 'technique',  'moyenne', 'en_cours'],
    [15, 'Prix différent',                'Le prix final était supérieur à celui affiché lors de la réservation.',    'paiement',   'moyenne', 'en_attente'],
    [1,  'Objet oublié',                  'J\'ai oublié mon sac à dos dans le véhicule du conducteur.',               'objet',      'faible',  'resolue'],
    [3,  'Note injuste',                  'J\'ai reçu une mauvaise note sans raison valable.',                        'note',       'faible',  'en_attente'],
];

foreach ($reclamations as $r) {
    run($pdo,
        "INSERT INTO reclamations (utilisateur_id, titre, description, categorie, priorite, statut)
         VALUES (:ui, :ti, :de, :ca, :pr, :st)",
        [
            ':ui' => $r[0], ':ti' => $r[1], ':de' => $r[2],
            ':ca' => $r[3], ':pr' => $r[4], ':st' => $r[5],
        ],
        "Réclamation: {$r[1]}"
    );
}

// ═══════════════════════════════════════════════════════════
// 11. RÉPONSES AUX RÉCLAMATIONS
// ═══════════════════════════════════════════════════════════
$pdo->exec("TRUNCATE TABLE reponse");

$reponses = [
    [1, 'Admin', 'Nous avons contacté le conducteur. Un avertissement a été émis et un bon de réduction vous a été accordé.'],
    [3, 'Marie Dupont', 'Après vérification, le conducteur a été sanctionné. Votre remboursement de 50% a été effectué.'],
    [5, 'Admin', 'Remboursement intégral effectué. Le conducteur a été suspendu temporairement.'],
    [7, 'Admin', 'Enquête en cours. Nous prenons ce type de comportement très au sérieux.'],
    [8, 'Marie Dupont', 'Le double débit a été confirmé et remboursé. Nous nous excusons pour le désagrément.'],
    [9, 'Admin', 'Nous avons transmis votre message au conducteur pour récupérer votre sac.'],
];

foreach ($reponses as $r) {
    run($pdo,
        "INSERT INTO reponse (reclamation_id, auteur_admin, contenu) VALUES (:ri, :au, :co)",
        [':ri' => $r[0], ':au' => $r[1], ':co' => $r[2]],
        "Réponse réclamation #{$r[0]}"
    );
}

// ═══════════════════════════════════════════════════════════
// 12. DÉCLARATIONS (objets perdus/trouvés)
// ═══════════════════════════════════════════════════════════
$pdo->exec("TRUNCATE TABLE declarations");

$declarations = [
    ['Sac à dos bleu',        'Sac à dos bleu marine avec fermeture éclair dorée, contient des livres.',    'sac',          'Paris Gare de Lyon',   '2026-05-10', 'ouvert',  1, 1,  null,       1, 'Jean Dupont'],
    ['Clés de voiture',       'Trousseau de clés avec porte-clé Eiffel Tower.',                              'cles',         'Lyon Part-Dieu',       '2026-05-08', 'trouve',  2, 3,  null,       3, 'Lucas Bernard'],
    ['Lunettes de soleil',    'Lunettes Ray-Ban noires trouvées sur le siège arrière.',                      'accessoire',   'Bordeaux',             '2026-05-12', 'ouvert',  3, 5,  null,       5, 'Thomas Moreau'],
    ['Téléphone Samsung',     'Samsung Galaxy S24 noir, fond d\'écran avec un chien.',                       'electronique', 'Marseille',            '2026-05-06', 'rendu',   4, 7,  null,       7, 'Hugo Laurent'],
    ['Veste en cuir',         'Veste en cuir marron taille M, marque Zara.',                                  'vetement',     'Paris Montparnasse',   '2026-05-14', 'ouvert',  5, 8,  null,       8, 'Léa Lefebvre'],
    ['Carnet de notes',       'Carnet Moleskine noir avec notes personnelles et schémas.',                    'document',     'Toulouse',             '2026-05-09', 'ouvert',  6, 12, null,       12,'Chloé Girard'],
    ['Casque audio',          'Casque Sony WH-1000XM5 noir avec pochette de rangement.',                     'electronique', 'Nice',                 '2026-05-11', 'trouve',  null,null,'Anonyme', null,'Anonyme'],
    ['Portefeuille',          'Portefeuille marron en cuir avec pièces d\'identité à l\'intérieur.',          'portefeuille', 'Strasbourg',           '2026-05-07', 'rendu',   8, 13, null,       13,'Mathieu Bonnet'],
    ['Livre de poche',        'Roman "Le Petit Prince", édition spéciale avec dédicace.',                     'livre',        'Nantes',               '2026-05-13', 'ouvert',  9, 15, null,       15,'Romain Richard'],
    ['Parapluie bleu',        'Parapluie automatique bleu avec manche en bois.',                              'accessoire',   'Rennes',               '2026-05-15', 'ouvert',  10, 1, null,       1, 'Jean Dupont'],
];

foreach ($declarations as $d) {
    run($pdo,
        "INSERT INTO declarations (titre, description, categorie, lieu_perte, date_perte, statut, trajet_id, passager_id, anonyme_nom, user_id, user_nom)
         VALUES (:ti, :de, :ca, :li, :dp, :st, :tr, :pa, :an, :ui, :un)",
        [
            ':ti' => $d[0], ':de' => $d[1], ':ca' => $d[2], ':li' => $d[3],
            ':dp' => $d[4], ':st' => $d[5], ':tr' => $d[6], ':pa' => $d[7],
            ':an' => $d[8], ':ui' => $d[9], ':un' => $d[10],
        ],
        "Déclaration: {$d[0]}"
    );
}

// ═══════════════════════════════════════════════════════════
// 13. COMMENTAIRES (sur déclarations)
// ═══════════════════════════════════════════════════════════
$pdo->exec("TRUNCATE TABLE commentaires");

$commentaires = [
    [1, 3,  'Lucas Bernard',  'J\'ai peut-être vu ce sac dans mon trajet Paris-Lyon, pouvez-vous me contacter ?'],
    [1, 5,  'Thomas Moreau',  'Même description que celui trouvé à la gare hier !'],
    [2, 1,  'Jean Dupont',    'Ce sont peut-être mes clés ! Comment puis-je les récupérer ?'],
    [3, 9,  'Antoine Leroy',  'J\'ai trouvé des lunettes similaires, contactez-moi.'],
    [5, 13, 'Mathieu Bonnet', 'Une veste comme celle-là a été déposée à l\'accueil de la gare.'],
    [6, 15, 'Romain Richard', 'J\'ai trouvé un carnet similaire, est-ce que votre nom est écrit dedans ?'],
    [7, 1,  'Jean Dupont',    'J\'ai un casque Sony que j\'ai trouvé, contactez l\'admin pour le récupérer.'],
    [9, 7,  'Hugo Laurent',   'Le Petit Prince avec dédicace ? Très beau livre, j\'espère que vous le retrouverez.'],
    [10, 3, 'Lucas Bernard',  'Parapluie bleu déposé à la mairie de Rennes en objet trouvé.'],
    [2, 4,  'Emma Petit',     '@Jean Dupont - Je peux faire la liaison pour vous si besoin !', 3],
];

foreach ($commentaires as $c) {
    run($pdo,
        "INSERT INTO commentaires (declaration_id, user_id, user_nom, message, parent_comment_id)
         VALUES (:di, :ui, :un, :me, :pa)",
        [':di' => $c[0], ':ui' => $c[1], ':un' => $c[2], ':me' => $c[3], ':pa' => $c[4] ?? null],
        "Commentaire décl#{$c[0]}"
    );
}

// ═══════════════════════════════════════════════════════════
// 14. APP SETTINGS
// ═══════════════════════════════════════════════════════════
$pdo->exec("TRUNCATE TABLE app_settings");

run($pdo,
    "INSERT INTO app_settings (setting_key, setting_value) VALUES ('site_name', 'EcoRide')",
    [], "Setting: site_name"
);
run($pdo,
    "INSERT INTO app_settings (setting_key, setting_value) VALUES ('site_email', 'contact@ecoride.fr')",
    [], "Setting: site_email"
);
run($pdo,
    "INSERT INTO app_settings (setting_key, setting_value) VALUES ('maintenance_mode', '0')",
    [], "Setting: maintenance_mode"
);

$pdo->exec("SET foreign_key_checks = 1");

// ═══════════════════════════════════════════════════════════
// AFFICHAGE DU RÉSULTAT
// ═══════════════════════════════════════════════════════════

// Compter les enregistrements
$counts = [];
$tables = ['admins','users','trajet','destination','vehicules','reservations','paiements','evenements','sponsors','reclamations','reponse','declarations','commentaires','app_settings'];
foreach ($tables as $t) {
    try {
        $counts[$t] = $pdo->query("SELECT COUNT(*) FROM $t")->fetchColumn();
    } catch (Exception $e) {
        $counts[$t] = 'ERREUR';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>EcoRide - Seed Database</title>
<style>
  body { font-family: 'Segoe UI', sans-serif; background: #0d1b2a; color: #e0e0e0; padding: 2rem; }
  h1   { color: #4fc3f7; margin-bottom: 1.5rem; }
  h2   { color: #81d4fa; margin-top: 2rem; }
  .card { background: #1a2a3a; border-radius: 10px; padding: 1.5rem; margin-bottom: 1rem; }
  .ok  { color: #69f0ae; }
  .err { color: #ff5252; }
  table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
  th, td { padding: 0.6rem 1rem; text-align: left; border-bottom: 1px solid #2a3a4a; }
  th { color: #4fc3f7; }
  td.num { color: #69f0ae; font-weight: bold; font-size: 1.1rem; }
  .btn { display: inline-block; margin-top: 1.5rem; padding: 0.8rem 2rem; background: #1976D2;
         color: white; text-decoration: none; border-radius: 8px; font-weight: bold; }
  .btn:hover { background: #1565C0; }
  .cred { background: #0a2a1a; border: 1px solid #2a5a3a; border-radius: 8px; padding: 1rem 1.5rem; margin-top: 1rem; }
  .cred code { color: #69f0ae; font-size: 1rem; }
</style>
</head>
<body>
<h1>🌿 EcoRide - Peuplement de la base de données</h1>

<?php if ($errors): ?>
<div class="card">
  <h2>⚠️ Erreurs</h2>
  <?php foreach ($errors as $e): ?>
    <p class="err"><?= htmlspecialchars($e) ?></p>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="card">
  <h2>✅ Résultat par table</h2>
  <table>
    <tr><th>Table</th><th>Enregistrements insérés</th></tr>
    <?php foreach ($counts as $table => $count): ?>
    <tr><td><?= $table ?></td><td class="num"><?= $count ?></td></tr>
    <?php endforeach; ?>
  </table>
</div>

<div class="card">
  <h2>🔑 Identifiants de connexion</h2>
  <div class="cred">
    <p><strong>Admin backoffice :</strong><br>
       Email : <code>admin@ecoride.fr</code><br>
       Mot de passe : <code>Admin@1234</code>
    </p>
  </div>
  <div class="cred" style="margin-top:0.8rem">
    <p><strong>Utilisateur frontoffice :</strong><br>
       Email : <code>jean.dupont@mail.fr</code><br>
       Mot de passe : <code>User@1234</code>
    </p>
  </div>
</div>

<?php if (count($log) > 0): ?>
<div class="card">
  <h2>📋 Log détaillé (<?= count($log) ?> insertions)</h2>
  <?php foreach ($log as $l): ?>
    <p class="ok" style="margin:2px 0;font-size:0.85rem"><?= htmlspecialchars($l) ?></p>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<a class="btn" href="<?= BASE_URL ?>Controller/AdminController.php?action=showLogin">
  → Aller au back-office
</a>
&nbsp;
<a class="btn" href="<?= BASE_URL ?>" style="background:#2e7d32">
  → Aller au front-office
</a>
</body>
</html>
