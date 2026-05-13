<?php
require_once __DIR__ . '/../config.php';

class AIController {

    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    private function requireAdmin(): void {
        if (!isset($_SESSION['admin_id'])) {
            header('Location: ' . BASE_URL . 'controllers/AdminController.php?action=showLogin');
            exit;
        }
    }

    public function showAssistant(): void {
        $this->requireAdmin();
        require_once __DIR__ . '/../views/backoffice/ai_assistant.php';
    }

    public function chat(): void {
        $this->requireAdmin();
        header('Content-Type: application/json; charset=utf-8');

        $input   = json_decode(file_get_contents('php://input'), true);
        $message = trim($input['message'] ?? '');

        if (empty($message)) {
            echo json_encode(['success' => false, 'reply' => 'Message vide.']);
            exit;
        }

        $users    = $this->getAllUsers();
        $aiResult = $this->callGroq($message, $users);

        if (!$aiResult) {
            echo json_encode(['success' => false, 'reply' => 'Erreur IA. Vérifiez GROQ_API_KEY dans config.php.']);
            exit;
        }

        echo json_encode($this->executeAction($aiResult, $users));
        exit;
    }

    private function getAllUsers(): array {
        $stmt = $this->db->query(
            "SELECT id, prenom, nom, email, telephone, role, statut
             FROM users
             ORDER BY nom"
        );
        return $stmt->fetchAll();
    }

    private function callGroq(string $userMessage, array $users): ?array {
        $usersJson = json_encode($users, JSON_UNESCAPED_UNICODE);

        $systemPrompt = <<<PROMPT
Tu es AI Helper, un assistant IA sympathique pour la plateforme de covoiturage "Eco Ride".
Tu réponds TOUJOURS en français, tu es poli, patient et professionnel.
Tu retournes UNIQUEMENT un objet JSON valide, sans texte avant ou après, sans markdown, sans backticks.

COMPRÉHENSION DU LANGAGE :
- Tu comprends le français correct, le français cassé, l'arabe translittéré (ex: "chkon", "wech", "kifech"), le franglais et l'anglais
- Tu interprètes les fautes de frappe et d'orthographe (ex: "lsite" = "liste", "suprimer" = "supprimer")
- Tu comprends les abréviations (ex: "supp" = supprimer, "modif" = modifier, "aj" = ajouter)
- Tu comprends les commandes mélangées français/anglais et arabe translittéré
- Exemples de commandes cassées :
  * "lste les users" → liste les utilisateurs
  * "bloker user 2" → bloquer utilisateur ID 2
  * "montr tout" → liste tous les utilisateurs
  * "zid user" → ajouter un utilisateur
  * "7yed user 3" → supprimer utilisateur 3
  * "wri les users" → liste les utilisateurs

Liste actuelle des utilisateurs (utilise ces données pour toutes les opérations) :
{$usersJson}

ACTIONS DISPONIBLES :

1. DIALOGUE (salutations, remerciements, questions générales) → action="chat"
2. LISTER tous les utilisateurs → action="list" filter=""
3. RECHERCHER un utilisateur → action="list" filter="mot_a_chercher"
4. TRIER les utilisateurs → action="sort" sort_by="nom|prenom|email|statut|role" order="asc|desc"
5. CRÉER un utilisateur → action="create" data={prenom,nom,email,telephone,role?,statut?}
6. MODIFIER un utilisateur → action="update" id=X data={prenom,nom,email,telephone,role,statut}
7. SUPPRIMER un utilisateur → action="delete" id=X
8. BLOQUER un utilisateur → action="block" id=X statut="inactif"
9. DÉBLOQUER un utilisateur → action="block" id=X statut="actif"
10. INCOMPRÉHENSIBLE → action="unknown"

EXEMPLES DE RÉPONSES JSON :

Commande: "bonjour" ou "salut" ou "slt" ou "hello" ou "salam"
Réponse: {"action":"chat","reply":"Bonjour ! Je suis AI Helper, votre assistant Eco Ride. Comment puis-je vous aider ?"}

Commande: "aide" ou "help"
Réponse: {"action":"chat","reply":"Je peux vous aider à :\n• 📋 Lister et rechercher des utilisateurs\n• ➕ Ajouter un nouvel utilisateur\n• ✏️ Modifier les informations d'un utilisateur\n• 🗑️ Supprimer un utilisateur\n• 🔒 Bloquer / débloquer un utilisateur\n• 🔍 Rechercher par nom, email ou téléphone\n• 🔃 Trier par nom, email, rôle ou statut\n\nDites-moi ce que vous voulez faire !"}

Commande: "liste" ou "voir tous les utilisateurs"
Réponse: {"action":"list","filter":"","reply":"Voici tous vos utilisateurs."}

Commande: "cherche Ali" ou "recherche Mohamed"
Réponse: {"action":"list","filter":"ali","reply":"Recherche de 'Ali' en cours..."}

Commande: "trie par nom"
Réponse: {"action":"sort","sort_by":"nom","order":"asc","reply":"Utilisateurs triés par nom (A→Z)."}

Commande: "trie par statut"
Réponse: {"action":"sort","sort_by":"statut","order":"asc","reply":"Utilisateurs triés par statut."}

Commande: "ajoute Ahmed Ben Ali, email ahmed@mail.com, tel 0612345678"
Réponse: {"action":"create","data":{"prenom":"Ahmed","nom":"Ben Ali","email":"ahmed@mail.com","telephone":"0612345678","role":"passager","statut":"actif"},"reply":"Création de l'utilisateur Ahmed Ben Ali..."}

Commande: "supprime utilisateur ID 3"
Réponse: {"action":"delete","id":3,"reply":"Suppression de l'utilisateur ID 3..."}

Commande: "bloque utilisateur ID 2"
Réponse: {"action":"block","id":2,"statut":"inactif","reply":"Utilisateur ID 2 bloqué."}

Commande: "débloque utilisateur ID 2"
Réponse: {"action":"block","id":2,"statut":"actif","reply":"Utilisateur ID 2 débloqué."}

RÈGLES :
- Pour "update", récupère les données manquantes depuis la liste existante
- Mot de passe par défaut si non précisé : "TempPass123!"
- Les rôles possibles sont : "passager" ou "conducteur"
- Les statuts possibles sont : "actif" ou "inactif"
- Sois toujours sympathique dans les "reply"
- RETOURNE UNIQUEMENT LE JSON, rien d'autre
PROMPT;

        $payload = [
            'model'    => 'llama-3.3-70b-versatile',
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user',   'content' => $userMessage],
            ],
            'max_tokens'  => 512,
            'temperature' => 0.1,
        ];

        $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . GROQ_API_KEY,
            ],
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!$response || $httpCode !== 200) return null;

        $data = json_decode($response, true);
        $text = $data['choices'][0]['message']['content'] ?? '';
        $text = trim(preg_replace('/```json|```/i', '', $text));
        $parsed = json_decode($text, true);
        return is_array($parsed) ? $parsed : null;
    }

    private function executeAction(array $ai, array $users): array {
        $action = $ai['action'] ?? 'unknown';
        $reply  = $ai['reply']  ?? '';

        switch ($action) {

            case 'chat':
                return ['success'=>true,'action'=>'chat','reply'=>$reply?:'Bonjour ! Comment puis-je vous aider ?'];

            case 'sort':
                $sort_by = in_array($ai['sort_by']??'', ['nom','prenom','email','statut','role','id']) ? $ai['sort_by'] : 'nom';
                $order   = ($ai['order']??'asc') === 'desc' ? 'desc' : 'asc';
                $sorted  = $users;
                usort($sorted, function($a, $b) use ($sort_by, $order) {
                    $va = strtolower($a[$sort_by] ?? '');
                    $vb = strtolower($b[$sort_by] ?? '');
                    $cmp = strcmp($va, $vb);
                    return $order === 'desc' ? -$cmp : $cmp;
                });
                return ['success'=>true,'action'=>'list','reply'=>$reply,'clients'=>$sorted];

            case 'list':
            case 'search':
                $filter  = strtolower($ai['filter'] ?? '');
                $results = array_values(array_filter($users, function($u) use ($filter) {
                    if (empty($filter)) return true;
                    return str_contains(
                        strtolower($u['prenom'].' '.$u['nom'].' '.$u['email'].' '.($u['telephone']??'')),
                        $filter
                    );
                }));
                return ['success'=>true,'action'=>'list','reply'=>$reply,'clients'=>$results];

            case 'create':
                $d = $ai['data'] ?? [];
                if (empty($d['email']) || empty($d['nom']) || empty($d['prenom'])) {
                    return ['success'=>false,'action'=>'error','reply'=>'⚠️ Informations manquantes : nom, prénom et email sont requis.'];
                }
                $ex = $this->db->prepare("SELECT id FROM users WHERE email = :e");
                $ex->execute([':e' => $d['email']]);
                if ($ex->fetch()) return ['success'=>false,'action'=>'error','reply'=>'⚠️ Un utilisateur avec cet email existe déjà.'];
                $mdp   = $d['password'] ?? 'TempPass123!';
                $role  = in_array($d['role']??'', ['passager','conducteur']) ? $d['role'] : 'passager';
                $statut = in_array($d['statut']??'', ['actif','inactif']) ? $d['statut'] : 'actif';
                $stmt  = $this->db->prepare(
                    "INSERT INTO users (prenom, nom, email, telephone, role, statut, password, created_at)
                     VALUES (:p, :n, :e, :t, :r, :s, :m, NOW())"
                );
                $ok   = $stmt->execute([
                    ':p' => $d['prenom'], ':n' => $d['nom'], ':e' => $d['email'],
                    ':t' => $d['telephone'] ?? '', ':r' => $role, ':s' => $statut,
                    ':m' => password_hash($mdp, PASSWORD_DEFAULT),
                ]);
                $newId = $this->db->lastInsertId();
                return [
                    'success' => $ok,
                    'action'  => 'create',
                    'reply'   => $ok ? "✅ Utilisateur **{$d['prenom']} {$d['nom']}** créé (ID: $newId). Mot de passe : `$mdp`" : '❌ Erreur création.',
                    'clients' => $this->getAllUsers(),
                ];

            case 'update':
                $id = (int)($ai['id']??0); $d = $ai['data']??[];
                if (!$id) return ['success'=>false,'action'=>'error','reply'=>'⚠️ ID manquant pour la modification.'];
                $role   = in_array($d['role']??'', ['passager','conducteur']) ? $d['role'] : 'passager';
                $statut = in_array($d['statut']??'', ['actif','inactif']) ? $d['statut'] : 'actif';
                $stmt = $this->db->prepare(
                    "UPDATE users SET prenom=:p, nom=:n, email=:e, telephone=:t, role=:r, statut=:s WHERE id=:id"
                );
                $ok = $stmt->execute([
                    ':p'=>$d['prenom']??'', ':n'=>$d['nom']??'', ':e'=>$d['email']??'',
                    ':t'=>$d['telephone']??'', ':r'=>$role, ':s'=>$statut, ':id'=>$id,
                ]);
                return ['success'=>$ok,'action'=>'update','reply'=>$ok?"✅ Utilisateur ID $id modifié.":'❌ Erreur modification.','clients'=>$this->getAllUsers()];

            case 'delete':
                $id = (int)($ai['id']??0);
                if (!$id) return ['success'=>false,'action'=>'error','reply'=>'⚠️ ID manquant pour la suppression.'];
                $r = $this->db->prepare("SELECT nom, prenom FROM users WHERE id=:id"); $r->execute([':id'=>$id]);
                $user = $r->fetch();
                if (!$user) return ['success'=>false,'action'=>'error','reply'=>"⚠️ Aucun utilisateur trouvé avec l'ID $id."];
                $stmt = $this->db->prepare("DELETE FROM users WHERE id=:id");
                $ok = $stmt->execute([':id'=>$id]);
                return ['success'=>$ok,'action'=>'delete','reply'=>$ok?"✅ Utilisateur **{$user['prenom']} {$user['nom']}** supprimé.":'❌ Erreur suppression.','clients'=>$this->getAllUsers()];

            case 'block':
                $id     = (int)($ai['id']??0);
                $statut = ($ai['statut']??'inactif') === 'actif' ? 'actif' : 'inactif';
                if (!$id) return ['success'=>false,'action'=>'error','reply'=>'⚠️ ID manquant.'];
                $stmt = $this->db->prepare("UPDATE users SET statut=:s WHERE id=:id");
                $ok   = $stmt->execute([':s'=>$statut, ':id'=>$id]);
                $label = $statut === 'inactif' ? 'bloqué' : 'débloqué';
                return ['success'=>$ok,'action'=>'block','reply'=>$ok?"✅ Utilisateur ID $id $label.":'❌ Erreur.','clients'=>$this->getAllUsers()];

            default:
                return ['success'=>true,'action'=>'unknown','reply'=>$reply?:"Je n'ai pas compris. Exemples :\n• 'Liste tous les utilisateurs'\n• 'Ajoute Ahmed Ben Ali, email ahmed@mail.com, tél 0612345678'\n• 'Supprime l'utilisateur ID 3'\n• 'Bloque l'utilisateur ID 2'"];
        }
    }
}

$controller = new AIController();
$action = $_GET['action'] ?? 'showAssistant';
if (in_array($action, ['showAssistant', 'chat'])) {
    $controller->$action();
} else {
    $controller->showAssistant();
}
