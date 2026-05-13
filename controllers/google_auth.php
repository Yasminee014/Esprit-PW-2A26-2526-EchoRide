<?php
// ============================================================
// controllers/google_auth.php
// Connexion via Google OAuth 2.0
// ============================================================

require_once __DIR__ . '/../config.php';

// ─── CONFIGURATION GOOGLE OAUTH ─────────────────────────────
// 1. Allez sur https://console.cloud.google.com/
// 2. Créez un projet → Identifiants → ID client OAuth 2.0
// 3. URI de redirection autorisée : http://localhost/projetadmin/controllers/google_auth.php
// 4. Remplacez les valeurs ci-dessous par vos vraies clés

define('GOOGLE_CLIENT_ID',     '356236064828-cv47pjl584q0cg7richtrtdoqgvkt6dn.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');
define('GOOGLE_REDIRECT_URI',  BASE_URL . 'controllers/google_auth.php');

// ─── ÉTAPE 1 : Rediriger vers Google ────────────────────────
if (!isset($_GET['code'])) {

    $state = bin2hex(random_bytes(16));
    $_SESSION['oauth_state'] = $state;

    $params = http_build_query([
        'client_id'     => GOOGLE_CLIENT_ID,
        'redirect_uri'  => GOOGLE_REDIRECT_URI,
        'response_type' => 'code',
        'scope'         => 'openid email profile',
        'state'         => $state,
        'access_type'   => 'online',
        'prompt'        => 'select_account',
    ]);

    header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . $params);
    exit();
}

// ─── ÉTAPE 2 : Callback Google ──────────────────────────────

// Vérification CSRF
if (!isset($_GET['state']) || $_GET['state'] !== ($_SESSION['oauth_state'] ?? '')) {
    $_SESSION['login_errors']['global'] = 'Erreur de sécurité OAuth. Veuillez réessayer.';
    header('Location: ' . BASE_URL . 'views/frontoffice/login.php');
    exit();
}
unset($_SESSION['oauth_state']);

if (isset($_GET['error'])) {
    $_SESSION['login_errors']['global'] = 'Connexion Google annulée.';
    header('Location: ' . BASE_URL . 'views/frontoffice/login.php');
    exit();
}

// ─── Échange du code contre un access_token ─────────────────
$tokenResponse = file_get_contents('https://oauth2.googleapis.com/token', false, stream_context_create([
    'http' => [
        'method'  => 'POST',
        'header'  => 'Content-Type: application/x-www-form-urlencoded',
        'content' => http_build_query([
            'code'          => $_GET['code'],
            'client_id'     => GOOGLE_CLIENT_ID,
            'client_secret' => GOOGLE_CLIENT_SECRET,
            'redirect_uri'  => GOOGLE_REDIRECT_URI,
            'grant_type'    => 'authorization_code',
        ]),
    ],
]));

if (!$tokenResponse) {
    $_SESSION['login_errors']['global'] = 'Impossible de contacter Google. Réessayez.';
    header('Location: ' . BASE_URL . 'views/frontoffice/login.php');
    exit();
}

$token = json_decode($tokenResponse, true);

if (empty($token['access_token'])) {
    $_SESSION['login_errors']['global'] = 'Authentification Google échouée.';
    header('Location: ' . BASE_URL . 'views/frontoffice/login.php');
    exit();
}

// ─── Récupérer les infos de l'utilisateur Google ────────────
$userInfoResponse = file_get_contents('https://www.googleapis.com/oauth2/v3/userinfo', false, stream_context_create([
    'http' => [
        'header' => 'Authorization: Bearer ' . $token['access_token'],
    ],
]));

$googleUser = json_decode($userInfoResponse, true);

if (empty($googleUser['email'])) {
    $_SESSION['login_errors']['global'] = 'Impossible de récupérer votre email Google.';
    header('Location: ' . BASE_URL . 'views/frontoffice/login.php');
    exit();
}

$email     = $googleUser['email'];
$firstName = $googleUser['given_name']  ?? '';
$lastName  = $googleUser['family_name'] ?? '';
$avatar    = $googleUser['picture']     ?? '';

// ─── Chercher ou créer l'utilisateur en BDD ─────────────────
try {
    $db  = Database::getInstance()->getConnection();

    // Chercher l'utilisateur par email
    $stmt = $db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // Créer un nouveau compte (sans mot de passe, connexion Google uniquement)
        $stmt = $db->prepare('
            INSERT INTO users (nom, prenom, email, password, statut, role, created_at)
            VALUES (:nom, :prenom, :email, :password, :statut, :role, NOW())
        ');
        $stmt->execute([
            ':nom'      => $lastName  ?: $email,
            ':prenom'   => $firstName ?: '',
            ':email'    => $email,
            ':password' => '',          // Pas de mot de passe pour les comptes Google
            ':statut'   => 'actif',
            ':role'     => 'user',
        ]);
        $userId = $db->lastInsertId();

        // Relire l'utilisateur créé
        $stmt = $db->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Vérifier que le compte est actif
    if (($user['statut'] ?? 'actif') !== 'actif') {
        $_SESSION['login_errors']['global'] = 'Votre compte est désactivé. Contactez l\'administrateur.';
        header('Location: ' . BASE_URL . 'views/frontoffice/login.php');
        exit();
    }

    // ─── Créer la session ────────────────────────────────────
    $_SESSION['user_id']     = $user['id'];
    $_SESSION['user_email']  = $user['email'];
    $_SESSION['user_nom']    = $user['nom'];
    $_SESSION['user_prenom'] = $user['prenom'] ?? '';
    $_SESSION['user_role']   = $user['role'] ?? 'user';
    $_SESSION['user_photo']  = $user['photo'] ?? '';
    $_SESSION['auth_type']   = 'google';

    header('Location: ' . BASE_URL . 'controllers/UserController.php?action=dashboard');
    exit();

} catch (PDOException $e) {
    error_log('Google OAuth DB Error: ' . $e->getMessage());
    $_SESSION['login_errors']['global'] = 'Erreur serveur. Veuillez réessayer.';
    header('Location: ' . BASE_URL . 'views/frontoffice/login.php');
    exit();
}
