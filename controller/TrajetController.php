<?php
error_reporting(E_ALL);
ini_set('display_errors', 0); // CRITICAL: never output PHP errors as HTML

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../model/Trajet.php";
require_once __DIR__ . "/../model/Destination.php";

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$database = new Database();
$db       = $database->getConnection();

if (!$db) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Connexion base de données échouée"]);
    exit();
}

$trajet      = new Trajet($db);
$destination = new Destination($db);

// =========================================================
// GET — Lire tous les trajets
// =========================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $result = $trajet->read();
        $data   = [];
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            // Count stops for this trip
            $nbArrets = 0;
            try {
                $stmtArr = $db->prepare(
                    "SELECT COUNT(*) FROM destination WHERE trajet_id = :id AND ordre != 999"
                );
                $stmtArr->bindParam(':id', $row['id_T']);
                $stmtArr->execute();
                $nbArrets = (int) $stmtArr->fetchColumn();
            } catch (Exception $e) {}

            $data[] = [
                'id_T'           => $row['id_T'],
                'point_depart'   => $row['point_depart']   ?? '',
                'point_arrive'   => $row['point_arrive']   ?? '',
                'prix_total'     => $row['prix_total']     ?? 0,
                'distance_total' => $row['distance_total'] ?? 0,
                'nb_arrets'      => $nbArrets,
            ];
        }
        echo json_encode($data);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
    exit();
}

// =========================================================
// POST — Créer un trajet + ses arrêts
// =========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents("php://input");
    $data  = json_decode($input);

    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "JSON invalide"]);
        exit();
    }

    $depart   = trim($data->depart        ?? '');
    $arrivee  = trim($data->arrivee       ?? '');
    $prix     = floatval($data->prix_total ?? 0);
    $distance = floatval($data->distance_total ?? 0);
    $arrets   = $data->arrets ?? [];

    if (empty($depart) || empty($arrivee) || $prix <= 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Champs obligatoires manquants"]);
        exit();
    }

    try {
        // Insert trajet
        $stmt = $db->prepare(
            "INSERT INTO trajet (point_depart, point_arrive, prix_total, distance_total)
             VALUES (:depart, :arrivee, :prix, :distance)"
        );
        $stmt->bindParam(':depart',   $depart);
        $stmt->bindParam(':arrivee',  $arrivee);
        $stmt->bindParam(':prix',     $prix);
        $stmt->bindParam(':distance', $distance);
        $stmt->execute();
        $newId = $db->lastInsertId();

        // Insert arrêts
        // Dans la boucle foreach des arrêts (POST) :
foreach ($arrets as $arret) {
    $nom       = trim($arret->nom      ?? '');
    $ord       = intval($arret->ordre  ?? 1);
    $dist      = floatval($arret->distance ?? 0);
    $prixArret = floatval($arret->prix ?? 0); // ← était absent en POST !
    if ($nom !== '') {
        $destination->addArret($newId, $nom, $ord, $dist, $prixArret);
    }
}

        http_response_code(200);
        echo json_encode([
            "success" => true,
            "message" => "Trajet publié avec succès",
            "id"      => $newId
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
    exit();
}

// =========================================================
// PUT — Modifier un trajet existant
// =========================================================
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = file_get_contents("php://input");
    $data  = json_decode($input);

    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "JSON invalide"]);
        exit();
    }

    $id       = intval($data->id           ?? 0);
    $depart   = trim($data->depart         ?? '');
    $arrivee  = trim($data->arrivee        ?? '');
    $prix     = floatval($data->prix_total ?? 0);
    $distance = floatval($data->distance_total ?? 0);
    $arrets   = $data->arrets ?? [];

    if (!$id || empty($depart) || empty($arrivee) || $prix <= 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Données invalides pour la mise à jour"]);
        exit();
    }

    try {
        $stmt = $db->prepare(
            "UPDATE trajet
             SET point_depart    = :depart,
                 point_arrive    = :arrivee,
                 prix_total      = :prix,
                 distance_total  = :distance
             WHERE id_T = :id"
        );
        $stmt->bindParam(':depart',   $depart);
        $stmt->bindParam(':arrivee',  $arrivee);
        $stmt->bindParam(':prix',     $prix);
        $stmt->bindParam(':distance', $distance);
        $stmt->bindParam(':id',       $id);
        $stmt->execute();

        // Supprimer les anciens arrêts intermédiaires (garder ordre=999 = réservations)
        $stmtDel = $db->prepare(
            "DELETE FROM destination WHERE trajet_id = :id AND ordre != 999"
        );
        $stmtDel->bindParam(':id', $id);
        $stmtDel->execute();

        // Réinsérer les nouveaux arrêts
        foreach ($arrets as $arret) {
            $nom       = trim($arret->nom      ?? '');
            $ord       = intval($arret->ordre  ?? 1);
            $dist      = floatval($arret->distance ?? 0);
            $prixArret = floatval($arret->prix ?? 0);
            if ($nom !== '') {
                // ✅ On utilise $id (l'id du trajet existant), plus $newId qui n'existe pas ici
                $destination->addArret($id, $nom, $ord, $dist, $prixArret);
            }
        }

        http_response_code(200);
        echo json_encode(["success" => true, "message" => "Trajet et arrêts mis à jour"]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
    exit();
}

// =========================================================
// DELETE — Supprimer un trajet et ses destinations
// =========================================================
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = intval($_GET['id'] ?? 0);

    if (!$id) {
        $input = file_get_contents("php://input");
        $body  = json_decode($input);
        $id    = intval($body->id ?? 0);
    }

    if (!$id) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "ID manquant"]);
        exit();
    }

    try {
        // Delete linked destinations first
        $destination->deleteByTrajet($id);

        // Delete trajet
        $stmt = $db->prepare("DELETE FROM trajet WHERE id_T = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        http_response_code(200);
        echo json_encode(["success" => true, "message" => "Trajet supprimé"]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
    exit();
}

http_response_code(405);
echo json_encode(["success" => false, "message" => "Méthode non autorisée"]);