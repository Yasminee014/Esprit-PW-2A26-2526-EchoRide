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

$database    = new Database();
$db          = $database->getConnection();
$destination = new Destination($db);

// =========================================================
// GET — Lire les destinations
//   Sans paramètres   → toutes les destinations (front user.js)
//   Avec ?paginate=1  → réponse paginée (admin)
// =========================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    // ── Mode paginé (admin) ──────────────────────────────
    if (!empty($_GET['paginate'])) {
        $page   = max(1, (int)($_GET['page']   ?? 1));
        $limit  = max(1, (int)($_GET['limit']  ?? 10));
        $search = trim($_GET['search'] ?? '');
        $sort   = trim($_GET['sort']   ?? 'id_des');
        $order  = trim($_GET['order']  ?? 'DESC');

        $total      = $destination->countAll($search);
        $totalPages = (int)ceil($total / $limit);
        $rows       = $destination->readWithPagination($search, $sort, $order, $page, $limit);

        $data = [];
        while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
            $data[] = [
                'id_des'          => $row['id_des']      ?? null,
                'trajet_id'       => $row['trajet_id']   ?? null,
                'id_trajet'       => $row['trajet_id']   ?? null,
                'nom'             => $row['nom']         ?? $row['descente'] ?? '',
                'nom_destination' => $row['nom']         ?? $row['descente'] ?? '',
                'descente'        => $row['nom']         ?? $row['descente'] ?? '',
                'distance'        => $row['distance']    ?? 0,
                'ordre'           => $row['ordre']       ?? 0,
                'point_arrive'    => $row['point_arrive'] ?? '',
                'prix'            => $row['prix']        ?? 0,
            ];
        }

        echo json_encode([
            'data'        => $data,
            'total'       => $total,
            'page'        => $page,
            'limit'       => $limit,
            'total_pages' => $totalPages,
        ]);

    // ── Mode classique (front user.js) ──────────────────
    } else {
        $result = $destination->read();
        $data   = [];
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $data[] = [
                'id_des'          => $row['id_des']      ?? $row['id'] ?? null,
                'trajet_id'       => $row['trajet_id']   ?? null,
                'id_trajet'       => $row['trajet_id']   ?? null,
                'nom'             => $row['nom']         ?? $row['descente'] ?? '',
                'nom_destination' => $row['nom']         ?? $row['descente'] ?? '',
                'descente'        => $row['nom']         ?? $row['descente'] ?? '',
                'distance'        => $row['distance']    ?? 0,
                'ordre'           => $row['ordre']       ?? 0,
                'point_arrive'    => $row['point_arrive'] ?? '',
                'prix'            => $row['prix']        ?? 0,
            ];
        }
        echo json_encode($data);
    }
    exit();
}

// =========================================================
// POST — Créer une réservation (inchangé)
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
    $prix      = floatval($data->prix   ?? 0);

    if (!$trajet_id || empty($descente)) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Données manquantes : trajet_id=" . $trajet_id . ", descente=" . $descente
        ]);
        exit();
    }

    if ($destination->create($trajet_id, $descente, $distance, $prix)) {
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "message" => "Réservation enregistrée pour " . htmlspecialchars($descente)
        ]);
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Erreur SQL lors de l'enregistrement"]);
    }
    exit();
}

// =========================================================
// DELETE — Supprimer une destination (inchangé)
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