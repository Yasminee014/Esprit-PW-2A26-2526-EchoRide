<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../Config/Database.php';
require_once __DIR__ . '/../Model/ChatbotModel.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$userMessage = trim(strtolower($input['message'] ?? ''));
$model = new ChatbotModel(Database::getInstance());

if ($userMessage === '') {
    echo json_encode(['reply' => "🤖 Bonjour admin ! Tapez 'aide' pour commencer."]);
    exit;
}

$reply = "🤖 Je n'ai pas compris. Tapez 'aide' pour voir les commandes disponibles.";

if (str_contains($userMessage, 'aide') || str_contains($userMessage, 'help')) {
    $reply = "🤖 Commandes disponibles :\n• aide\n• statistiques\n• dernières réclamations\n• statut [ID]";
} elseif (preg_match('/\bstatut\s+(\d+)/', $userMessage, $matches)) {
    $claimId = (int)$matches[1];
    $claim = $model->getClaimById($claimId);
    if ($claim) {
        $reply = sprintf(
            "📋 Réclamation #%d : %s\nStatut: %s\nCatégorie: %s\nPriorité: %s\nDate: %s\n\nDescription : %s",
            $claim['id'], $claim['titre'], $claim['statut'], $claim['categorie'], $claim['priorite'],
            date('d/m/Y H:i', strtotime($claim['date_creation'] ?? $claim['created_at'] ?? '')), 
            substr($claim['description'], 0, 180)
        );
    } else {
        $reply = "❌ Aucune réclamation trouvée avec l'ID {$claimId}.";
    }
} elseif (str_contains($userMessage, 'statistiques') || str_contains($userMessage, 'stats')) {
    $stats = $model->getStats();
    $reply = sprintf(
        "📊 Statistiques actuelles :\nTotal: %d\nEn attente: %d\nEn cours: %d\nRésolues: %d\nRejetées: %d",
        $stats['total'] ?? 0,
        $stats['en_attente'] ?? 0,
        $stats['en_cours'] ?? 0,
        $stats['resolue'] ?? 0,
        $stats['rejetee'] ?? 0
    );
} elseif (str_contains($userMessage, 'derni') || str_contains($userMessage, 'recent')) {
    $claims = $model->getRecentClaims(5);
    if (empty($claims)) {
        $reply = "🕐 Aucune réclamation disponible pour le moment.";
    } else {
        $lines = array_map(function ($claim) {
            return sprintf("#%d - %s (%s)", $claim['id'], $claim['titre'], $claim['statut']);
        }, $claims);
        $reply = "🕐 Dernières réclamations :\n" . implode("\n", $lines);
    }
}

echo json_encode(['reply' => $reply]);
?>
