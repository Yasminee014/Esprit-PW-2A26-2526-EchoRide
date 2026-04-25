<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

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
// GET — Lire les trajets
//   Sans paramètres   → tous les trajets (comportement original,
//                        utilisé par le front user.js)
//   Avec ?paginate=1  → réponse paginée pour l'admin
// =========================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // ── Mode paginé (admin) ──────────────────────────────
        if (!empty($_GET['paginate'])) {
            $page   = max(1, (int)($_GET['page']   ?? 1));
            $limit  = max(1, (int)($_GET['limit']  ?? 10));
            $search = trim($_GET['search'] ?? '');
            $sort   = trim($_GET['sort']   ?? 'id_T');
            $order  = trim($_GET['order']  ?? 'DESC');

            $total      = $trajet->countAll($search);
            $totalPages = (int)ceil($total / $limit);
            $rows       = $trajet->readWithPagination($search, $sort, $order, $page, $limit);

            $data = [];
            while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
                $nbArrets = 0;
                try {
                    $s = $db->prepare("SELECT COUNT(*) FROM destination WHERE trajet_id = :id AND ordre != 999");
                    $s->bindParam(':id', $row['id_T']);
                    $s->execute();
                    $nbArrets = (int)$s->fetchColumn();
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

            echo json_encode([
                'data'        => $data,
                'total'       => $total,
                'page'        => $page,
                'limit'       => $limit,
                'total_pages' => $totalPages,
            ]);

        // ── Mode classique (front user.js) ──────────────────
        } else {
            $result = $trajet->read();
            $data   = [];
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $nbArrets = 0;
                try {
                    $s = $db->prepare("SELECT COUNT(*) FROM destination WHERE trajet_id = :id AND ordre != 999");
                    $s->bindParam(':id', $row['id_T']);
                    $s->execute();
                    $nbArrets = (int)$s->fetchColumn();
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
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
    exit();
}

// =========================================================
// POST — Créer un trajet + ses arrêts (inchangé)
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

        foreach ($arrets as $arret) {
            $nom       = trim($arret->nom      ?? '');
            $ord       = intval($arret->ordre  ?? 1);
            $dist      = floatval($arret->distance ?? 0);
            $prixArret = floatval($arret->prix ?? 0);
            if ($nom !== '') {
                $destination->addArret($newId, $nom, $ord, $dist, $prixArret);
            }
        }

        http_response_code(200);
        echo json_encode(["success" => true, "message" => "Trajet publié avec succès", "id" => $newId]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
    exit();
}

// =========================================================
// PUT — Modifier un trajet existant (inchangé)
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
             SET point_depart   = :depart,
                 point_arrive   = :arrivee,
                 prix_total     = :prix,
                 distance_total = :distance
             WHERE id_T = :id"
        );
        $stmt->bindParam(':depart',   $depart);
        $stmt->bindParam(':arrivee',  $arrivee);
        $stmt->bindParam(':prix',     $prix);
        $stmt->bindParam(':distance', $distance);
        $stmt->bindParam(':id',       $id);
        $stmt->execute();

        $stmtDel = $db->prepare("DELETE FROM destination WHERE trajet_id = :id AND ordre != 999");
        $stmtDel->bindParam(':id', $id);
        $stmtDel->execute();

        foreach ($arrets as $arret) {
            $nom       = trim($arret->nom      ?? '');
            $ord       = intval($arret->ordre  ?? 1);
            $dist      = floatval($arret->distance ?? 0);
            $prixArret = floatval($arret->prix ?? 0);
            if ($nom !== '') {
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
// DELETE — Supprimer un trajet (inchangé)
// =========================================================
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = null;

    if (isset($_GET['id']) && !empty($_GET['id'])) {
        $id = intval($_GET['id']);
    } else {
        $input = file_get_contents("php://input");
        if (!empty($input)) {
            $body = json_decode($input);
            if ($body && isset($body->id)) {
                $id = intval($body->id);
            }
        }
    }

    if (!$id) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "ID manquant"]);
        exit();
    }

    try {
        $destination->deleteByTrajet($id);
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