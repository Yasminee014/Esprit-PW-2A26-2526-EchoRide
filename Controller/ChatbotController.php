<?php
// ChatbotController.php — place this file in the SAME folder as admin_reclamations.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

session_start();

try {
    $pdo = new PDO("mysql:host=localhost;dbname=ecoride", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['reply' => '❌ Erreur base de données : ' . $e->getMessage()]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$message = strtolower(trim($input['message'] ?? ''));
$raw = trim($input['message'] ?? '');

if (empty($message)) {
    echo json_encode(['reply' => '❌ Message vide.']);
    exit;
}

// ── Helper : badge priorité ──────────────────
function prioBadge($p) {
    if ($p === 'elevee')  return '🔴 Élevée';
    if ($p === 'moyenne') return '🟡 Moyenne';
    return '🟢 Faible';
}
function statutBadge($s) {
    $map = ['en_attente'=>'⏳ En attente','en_cours'=>'🔄 En cours','resolue'=>'✅ Résolue','rejetee'=>'❌ Rejetée'];
    return $map[$s] ?? $s;
}
function formatRow($r) {
    return "• #".$r['id']." — ".htmlspecialchars($r['titre'])
        ."\n  Priorité : ".prioBadge($r['priorite'])
        ." | Statut : ".statutBadge($r['statut'])
        ."\n  Utilisateur : ".'User #'.$r['utilisateur_id']
        ."\n  Date : ".date('d/m/Y', strtotime($r['date_creation']));
}

// ── Commandes internes (pas besoin d'IA) ─────

// STATISTIQUES
if (str_contains($message, 'stat') || $message === 'stats') {
    $stats = $pdo->query("SELECT COUNT(*) as total, SUM(statut='en_attente') as attente, SUM(statut='en_cours') as cours, SUM(statut='resolue') as resolue, SUM(statut='rejetee') as rejetee, SUM(priorite='elevee') as elevees FROM reclamations")->fetch();
    $reply  = "📊 **Statistiques réclamations**\n\n";
    $reply .= "📋 Total : ".$stats['total']."\n";
    $reply .= "⏳ En attente : ".$stats['attente']."\n";
    $reply .= "🔄 En cours : ".$stats['cours']."\n";
    $reply .= "✅ Résolues : ".$stats['resolue']."\n";
    $reply .= "❌ Rejetées : ".$stats['rejetee']."\n";
    $reply .= "🔴 Priorité élevée : ".$stats['elevees'];
    echo json_encode(['reply' => $reply]); exit;
}

// RÉCLAMATIONS ÉLEVÉES / URGENTES
if (str_contains($message, 'élev') || str_contains($message, 'elev') || str_contains($message, 'urgent') || str_contains($message, 'priorit')) {
    $rows = $pdo->query("SELECT r.*, u.nom as utilisateur_nom FROM reclamations r WHERE r.priorite = 'elevee' ORDER BY r.date_creation DESC LIMIT 10")->fetchAll();
    if (empty($rows)) { echo json_encode(['reply' => '✅ Aucune réclamation de priorité élevée.']); exit; }
    $reply = "🔴 **Réclamations priorité élevée** (".count($rows)." trouvée(s))\n\n";
    foreach ($rows as $r) $reply .= formatRow($r) . "\n\n";
    echo json_encode(['reply' => trim($reply)]); exit;
}

// DERNIÈRES RÉCLAMATIONS
if (str_contains($message, 'derni') || str_contains($message, 'récent') || str_contains($message, 'recent') || str_contains($message, 'liste') || $message === 'reclamations') {
    $rows = $pdo->query("SELECT r.*, u.nom as utilisateur_nom FROM reclamations r ORDER BY r.date_creation DESC LIMIT 5")->fetchAll();
    if (empty($rows)) { echo json_encode(['reply' => 'Aucune réclamation trouvée.']); exit; }
    $reply = "📋 **5 dernières réclamations**\n\n";
    foreach ($rows as $r) $reply .= formatRow($r) . "\n\n";
    echo json_encode(['reply' => trim($reply)]); exit;
}

// EN ATTENTE
if (str_contains($message, 'attente')) {
    $rows = $pdo->query("SELECT r.*, u.nom as utilisateur_nom FROM reclamations r WHERE r.statut = 'en_attente' ORDER BY r.priorite DESC, r.date_creation ASC LIMIT 8")->fetchAll();
    $reply = "⏳ **En attente** (".count($rows).")\n\n";
    foreach ($rows as $r) $reply .= formatRow($r) . "\n\n";
    echo json_encode(['reply' => trim($reply)]); exit;
}

// EN COURS
if (str_contains($message, 'cours')) {
    $rows = $pdo->query("SELECT r.*, u.nom as utilisateur_nom FROM reclamations r WHERE r.statut = 'en_cours' ORDER BY r.date_creation DESC LIMIT 8")->fetchAll();
    $reply = "🔄 **En cours** (".count($rows).")\n\n";
    foreach ($rows as $r) $reply .= formatRow($r) . "\n\n";
    echo json_encode(['reply' => trim($reply)]); exit;
}

// RÉSOLUES
if (str_contains($message, 'résolu') || str_contains($message, 'resolu')) {
    $rows = $pdo->query("SELECT r.*, u.nom as utilisateur_nom FROM reclamations r WHERE r.statut = 'resolue' ORDER BY r.date_creation DESC LIMIT 8")->fetchAll();
    $reply = "✅ **Résolues** (".count($rows).")\n\n";
    foreach ($rows as $r) $reply .= formatRow($r) . "\n\n";
    echo json_encode(['reply' => trim($reply)]); exit;
}

// CHERCHER [mot]
if (preg_match('/chercher?\s+(.+)/i', $raw, $m) || preg_match('/rechercher?\s+(.+)/i', $raw, $m) || preg_match('/trouver?\s+(.+)/i', $raw, $m)) {
    $term = '%' . trim($m[1]) . '%';
    $stmt = $pdo->prepare("SELECT r.*, u.nom as utilisateur_nom FROM reclamations r WHERE r.titre LIKE ? OR r.description LIKE ? OR u.nom LIKE ? ORDER BY r.date_creation DESC LIMIT 8");
    $stmt->execute([$term, $term, $term]);
    $rows = $stmt->fetchAll();
    if (empty($rows)) { echo json_encode(['reply' => '🔍 Aucun résultat pour "'.htmlspecialchars(trim($m[1])).'".']); exit; }
    $reply = "🔍 **Résultats pour \"".htmlspecialchars(trim($m[1]))."\"** (".count($rows).")\n\n";
    foreach ($rows as $r) $reply .= formatRow($r) . "\n\n";
    echo json_encode(['reply' => trim($reply)]); exit;
}

// STATUT [ID]
if (preg_match('/statut\s+#?(\d+)/i', $raw, $m) || preg_match('/id\s+#?(\d+)/i', $raw, $m) || preg_match('/#?(\d+)/i', $raw, $m)) {
    $id = intval($m[1]);
    $stmt = $pdo->prepare("SELECT r.*, u.nom as utilisateur_nom FROM reclamations r WHERE r.id = ?");
    $stmt->execute([$id]);
    $r = $stmt->fetch();
    if (!$r) { echo json_encode(['reply' => "❌ Réclamation #$id introuvable."]); exit; }
    $reply  = "📄 **Réclamation #".$r['id']."**\n\n";
    $reply .= "Titre : ".htmlspecialchars($r['titre'])."\n";
    $reply .= "Statut : ".statutBadge($r['statut'])."\n";
    $reply .= "Priorité : ".prioBadge($r['priorite'])."\n";
    $reply .= "Catégorie : ".$r['categorie']."\n";
    $reply .= "Utilisateur : ".'User #'.$r['utilisateur_id']."\n";
    $reply .= "Date : ".date('d/m/Y H:i', strtotime($r['date_creation']))."\n";
    if (!empty($r['reponse_admin'])) $reply .= "\n💬 Réponse admin :\n".htmlspecialchars($r['reponse_admin']);
    echo json_encode(['reply' => $reply]); exit;
}

// AIDE
if (str_contains($message, 'aide') || $message === 'help' || $message === '?') {
    $reply  = "🤖 **Commandes disponibles**\n\n";
    $reply .= "• `statistiques` — Vue d'ensemble\n";
    $reply .= "• `urgentes` — Priorité élevée\n";
    $reply .= "• `en attente` — Réclamations en attente\n";
    $reply .= "• `en cours` — En traitement\n";
    $reply .= "• `dernières réclamations` — Les 5 dernières\n";
    $reply .= "• `chercher [mot]` — Recherche\n";
    $reply .= "• `statut [ID]` — Détail d'une réclamation\n\n";
    $reply .= "💡 Posez aussi vos questions librement !";
    echo json_encode(['reply' => $reply]); exit;
}

// ── Fallback : contexte DB → réponse intelligente ───────────────────────────
// Récupérer les stats pour donner du contexte à Claude
$stats = $pdo->query("SELECT COUNT(*) as total, SUM(statut='en_attente') as attente, SUM(statut='en_cours') as cours, SUM(statut='resolue') as resolue, SUM(priorite='elevee') as elevees FROM reclamations")->fetch();

$context = "Tu es l'assistant administrateur d'EcoRide, une application de covoiturage. "
    ."Tu gères les réclamations utilisateurs. "
    ."Statistiques actuelles : {$stats['total']} réclamations au total, {$stats['attente']} en attente, {$stats['cours']} en cours, {$stats['resolue']} résolues, {$stats['elevees']} priorité élevée. "
    ."Réponds en français, de façon concise et professionnelle. "
    ."Si la question concerne des données précises, dis à l'utilisateur d'utiliser les commandes : statistiques, urgentes, chercher [mot], statut [ID].";

// Appel API Anthropic côté serveur (pas de CORS !)
$apiKey = 'VOTRE_CLE_API_ICI'; // ← Remplacez par votre clé sk-ant-...

if ($apiKey === 'VOTRE_CLE_API_ICI') {
    // Pas de clé → réponse générique utile
    $reply  = "💡 Je ne reconnais pas cette commande exacte.\n\n";
    $reply .= "Essayez :\n• `statistiques`\n• `urgentes`\n• `en attente`\n• `chercher [mot]`\n• `aide`";
    echo json_encode(['reply' => $reply]); exit;
}

$apiResponse = @file_get_contents('https://api.anthropic.com/v1/messages', false, stream_context_create([
    'http' => [
        'method'  => 'POST',
        'header'  => implode("\r\n", [
            'Content-Type: application/json',
            'x-api-key: ' . $apiKey,
            'anthropic-version: 2023-06-01',
        ]),
        'content' => json_encode([
            'model'      => 'claude-haiku-4-5-20251001',
            'max_tokens' => 300,
            'system'     => $context,
            'messages'   => [['role' => 'user', 'content' => $raw]],
        ]),
        'timeout' => 10,
    ]
]));

if ($apiResponse === false) {
    echo json_encode(['reply' => "💡 Commande non reconnue. Tapez `aide` pour voir les commandes disponibles."]);
    exit;
}

$data = json_decode($apiResponse, true);
$reply = $data['content'][0]['text'] ?? "❌ Réponse invalide de l'API.";
echo json_encode(['reply' => $reply]);