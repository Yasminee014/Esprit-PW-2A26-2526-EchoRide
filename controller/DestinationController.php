<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once "../config/database.php";
require_once "../model/Destination.php";
require_once "../model/Trajet.php";

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$database = new Database();
$db       = $database->getConnection();
$destination = new Destination($db);

// =========================================================
// GET - Lire toutes les destinations
// =========================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $result = $destination->read();
    $data   = [];

    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        // Normaliser les champs pour le frontend admin :
        // id_des, trajet_id, nom (destination), distance, ordre
        // + point_arrive provient du trajet parent
        $data[] = [
            'id_des'          => $row['id_des']   ?? $row['id'] ?? null,
            'trajet_id'       => $row['trajet_id'] ?? null,
            'id_trajet'       => $row['trajet_id'] ?? null,  // alias
            'nom'             => $row['nom']       ?? $row['descente'] ?? '',
            'nom_destination' => $row['nom']       ?? $row['descente'] ?? '', // alias admin
            'descente'        => $row['nom']       ?? $row['descente'] ?? '', // alias front
            'distance'        => $row['distance']  ?? 0,
            'ordre'           => $row['ordre']     ?? 0,
            'point_arrive'    => $row['point_arrive'] ?? '',  // si JOIN existe
            'prix'            => $row['prix']      ?? 0,
        ];
    }

    echo json_encode($data);
    exit();
}

// =========================================================
// POST - Créer une réservation (point de descente)
// =========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents("php://input");
    $data  = json_decode($input);

    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "JSON invalide"]);
        exit();
    }

    $trajet_id = intval($data->trajet_id ?? 0);
    $descente  = trim($data->descente   ?? '');
    $distance  = isset($data->distance) ? floatval($data->distance) : null;
    $prix     = floatval($data->prix ?? 0);

    if (!$trajet_id || empty($descente)) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Données manquantes : trajet_id=" . $trajet_id . ", descente=" . $descente
        ]);
        exit();
    }

    if ($destination->create($trajet_id, $descente, $distance , $prix)) {
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "message" => "Réservation enregistrée pour " . htmlspecialchars($descente)
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Erreur SQL lors de l'enregistrement"
        ]);
    }
    exit();
}

// =========================================================
// DELETE - Supprimer une destination
// =========================================================
// =========================================================
// DELETE - Supprimer une destination
// =========================================================
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = null;

    if (!empty($_GET['id'])) {
        $id = intval($_GET['id']);
    } else {
        $input = file_get_contents("php://input");
        if (!empty($input)) {
            $data = json_decode($input);
            if ($data && !empty($data->id)) {
                $id = intval($data->id);
            }
        }
    }

    if (!$id) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "ID manquant"]);
        exit();
    }

    if ($destination->delete($id)) {
        http_response_code(200);
        echo json_encode(["success" => true, "message" => "Destination supprimée"]);
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Erreur lors de la suppression"]);
    }
    exit();
}
http_response_code(405);
echo json_encode(["error" => true, "message" => "Méthode non autorisée"]);