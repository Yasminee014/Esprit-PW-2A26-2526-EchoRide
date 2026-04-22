<?php
// ============================================================
// controllers/UserController.php
// Contrôleur : Inscription, Connexion, Profil, Dashboard User
// Contient toute la logique métier (requêtes SQL) liée aux
// utilisateurs, auparavant dans models/User.php
// ============================================================

ob_start(); // Bufferiser la sortie pour éviter les erreurs "headers already sent"

require_once __DIR__ . '/../models/User.php';

class UserController {

    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // ════════════════════════════════════════════════════════
    // MÉTHODES D'ACCÈS BASE DE DONNÉES (ex-model User)
    // ════════════════════════════════════════════════════════

    // ─── Trouver un utilisateur par ID ────────────────────────
    private function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    // ─── Trouver un utilisateur par email ─────────────────────
    private function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    // ─── Vérifier si un email existe déjà ─────────────────────
    private function emailExists(string $email, ?int $excludeId = null): bool {
        if ($excludeId !== null) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = :email AND id != :id");
            $stmt->execute([':email' => $email, ':id' => $excludeId]);
        } else {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);
        }
        return (int)$stmt->fetchColumn() > 0;
    }

    // ─── Créer un utilisateur ─────────────────────────────────
    private function createUser(User $user): bool {
        $stmt = $this->db->prepare(
            "INSERT INTO users (prenom, nom, email, telephone, role, statut, password, created_at)
             VALUES (:prenom, :nom, :email, :telephone, :role, :statut, :password, NOW())"
        );
        return $stmt->execute([
            ':prenom'    => $user->getPrenom(),
            ':nom'       => $user->getNom(),
            ':email'     => $user->getEmail(),
            ':telephone' => $user->getTelephone(),
            ':role'      => $user->getRole(),
            ':statut'    => $user->getStatut(),
            ':password'  => password_hash($user->getPassword(), PASSWORD_DEFAULT),
        ]);
    }

    // ─── Mettre à jour le profil ──────────────────────────────
    private function updateUser(int $id, array $data): bool {
        $stmt = $this->db->prepare(
            "UPDATE users SET prenom = :prenom, nom = :nom, email = :email,
             telephone = :telephone, statut = :statut
             WHERE id = :id"
        );
        return $stmt->execute([
            ':prenom'    => $data['prenom'],
            ':nom'       => $data['nom'],
            ':email'     => $data['email'],
            ':telephone' => $data['telephone'],
            ':statut'    => $data['statut'] ?? 'actif',
            ':id'        => $id,
        ]);
    }

    // ─── Mettre à jour le mot de passe ────────────────────────
    private function updatePassword(int $id, string $newPassword): bool {
        $stmt = $this->db->prepare("UPDATE users SET password = :password WHERE id = :id");
        return $stmt->execute([
            ':password' => password_hash($newPassword, PASSWORD_DEFAULT),
            ':id'       => $id,
        ]);
    }

    // ─── Vérifier un mot de passe ─────────────────────────────
    private function verifyPassword(string $input, string $hash): bool {
        return password_verify($input, $hash);
    }

    // ════════════════════════════════════════════════════════
    // ACTIONS DU CONTRÔLEUR
    // ════════════════════════════════════════════════════════

    // ─── Afficher page connexion / inscription ─────────────────
    public function showIndex(): void {
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'controllers/UserController.php?action=dashboard');
            exit;
        }
        require_once __DIR__ . '/../views/frontoffice/login.php';
    }

    // ─── Afficher formulaire inscription ──────────────────────
    public function showRegister(): void {
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'controllers/UserController.php?action=dashboard');
            exit;
        }
        $_GET['show'] = 'register';
        require_once __DIR__ . '/../views/frontoffice/login.php';
    }

    // ─── Afficher formulaire connexion ────────────────────────
    public function showLoginForm(): void {
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'controllers/UserController.php?action=dashboard');
            exit;
        }
        $_GET['show'] = 'login';
        require_once __DIR__ . '/../views/frontoffice/login.php';
    }

    // ─── Inscription ──────────────────────────────────────────
    public function register(): void {
        $errors = [];

        $prenom    = trim($_POST['prenom']           ?? '');
        $nom       = trim($_POST['nom']              ?? '');
        $email     = trim($_POST['email']            ?? '');
        $telephone = trim($_POST['telephone']        ?? '');
        $role      = 'passager';
        $password  = $_POST['password']              ?? '';
        $confirm   = $_POST['confirm_password']      ?? '';

        if (empty($prenom)) {
            $errors['prenom'] = 'Le prénom est obligatoire.';
        } elseif (strlen($prenom) < 2) {
            $errors['prenom'] = 'Le prénom doit contenir au moins 2 caractères.';
        } elseif (!preg_match('/^[a-zA-ZÀ-ÿ\s\-\']+$/u', $prenom)) {
            $errors['prenom'] = 'Le prénom ne doit contenir que des lettres.';
        }

        if (empty($nom)) {
            $errors['nom'] = 'Le nom est obligatoire.';
        } elseif (strlen($nom) < 2) {
            $errors['nom'] = 'Le nom doit contenir au moins 2 caractères.';
        } elseif (!preg_match('/^[a-zA-ZÀ-ÿ\s\-\']+$/u', $nom)) {
            $errors['nom'] = 'Le nom ne doit contenir que des lettres.';
        }

        if (empty($email)) {
            $errors['email'] = 'L\'email est obligatoire.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Format d\'email invalide.';
        } elseif ($this->emailExists($email)) {
            $errors['email'] = 'Cet email est déjà utilisé.';
        }

        if (empty($telephone)) {
            $errors['telephone'] = 'Le téléphone est obligatoire.';
        } elseif (!preg_match('/^[0-9\+\-\s]{8,15}$/', $telephone)) {
            $errors['telephone'] = 'Numéro de téléphone invalide (8-15 chiffres).';
        }

        if (empty($password)) {
            $errors['password'] = 'Le mot de passe est obligatoire.';
        } elseif (strlen($password) < 8) {
            $errors['password'] = 'Le mot de passe doit contenir au moins 8 caractères.';
        } elseif (!preg_match('/[A-Z]/', $password)) {
            $errors['password'] = 'Le mot de passe doit contenir au moins une majuscule.';
        } elseif (!preg_match('/[0-9]/', $password)) {
            $errors['password'] = 'Le mot de passe doit contenir au moins un chiffre.';
        }

        if ($password !== $confirm) {
            $errors['confirm_password'] = 'Les mots de passe ne correspondent pas.';
        }

        if (!empty($errors)) {
            $_SESSION['errors']    = $errors;
            $_SESSION['old_input'] = $_POST;
            header('Location: ' . BASE_URL . 'controllers/UserController.php?action=showRegister');
            exit;
        }

        $user = new User();
        $user->setPrenom($prenom);
        $user->setNom($nom);
        $user->setEmail($email);
        $user->setTelephone($telephone);
        $user->setRole($role);
        $user->setStatut('actif');
        $user->setPassword($password);

        if ($this->createUser($user)) {
            $created = $this->findByEmail($email);
            $_SESSION['user_id']        = $created['id'];
            $_SESSION['user_prenom']    = $created['prenom'];
            $_SESSION['user_nom']       = $created['nom'];
            $_SESSION['user_email']     = $created['email'];
            $_SESSION['user_role']      = $created['role'];
            $_SESSION['user_telephone'] = $created['telephone'] ?? '';
            header('Location: ' . BASE_URL . 'controllers/UserController.php?action=dashboard');
        } else {
            $_SESSION['errors']['global'] = 'Erreur lors de l\'inscription. Réessayez.';
            header('Location: ' . BASE_URL . 'controllers/UserController.php?action=showRegister');
        }
        exit;
    }

    // ─── Connexion ────────────────────────────────────────────
    public function login(): void {
        $errors = [];

        $email    = trim($_POST['email']    ?? '');
        $password = $_POST['password'] ?? '';

        // ── Vérification reCAPTCHA v2 ─────────────────────────
        $recaptchaSecret   = '6Leid74sAAAAACI3CPfyWGoOQ2fJESmFtJXh0BJ3';
        $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';

        if (empty($recaptchaResponse)) {
            $errors['recaptcha'] = 'Veuillez cocher "Je ne suis pas un robot".';
        } else {
            $verify = @file_get_contents(
                'https://www.google.com/recaptcha/api/siteverify?secret='
                . urlencode($recaptchaSecret)
                . '&response=' . urlencode($recaptchaResponse)
            );
            if ($verify !== false) {
                $recaptchaData = json_decode($verify, true);
                if (empty($recaptchaData['success'])) {
                    $errors['recaptcha'] = 'Verification reCAPTCHA echouee. Reessayez.';
                }
            }
        }

        if (empty($email)) {
            $errors['email'] = 'L\'email est obligatoire.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Format d\'email invalide.';
        }

        if (empty($password)) {
            $errors['password'] = 'Le mot de passe est obligatoire.';
        }

        if (!empty($errors)) {
            $_SESSION['login_errors']    = $errors;
            $_SESSION['login_old_input'] = $_POST;
            header('Location: ' . BASE_URL . 'controllers/UserController.php?action=showLoginForm');
            exit;
        }

        $user = $this->findByEmail($email);

        if ($user && $this->verifyPassword($password, $user['password'])) {
            if ($user['statut'] === 'inactif') {
                $_SESSION['login_errors']['global'] = 'Votre compte est désactivé. Contactez l\'administrateur.';
                header('Location: ' . BASE_URL . 'controllers/UserController.php?action=showLoginForm');
                exit;
            }

            $_SESSION['user_id']        = $user['id'];
            $_SESSION['user_prenom']    = $user['prenom'];
            $_SESSION['user_nom']       = $user['nom'];
            $_SESSION['user_email']     = $user['email'];
            $_SESSION['user_role']      = $user['role'];
            $_SESSION['user_telephone'] = $user['telephone'] ?? '';
            $_SESSION['user_photo']     = $user['photo'] ?? '';

            header('Location: ' . BASE_URL . 'controllers/UserController.php?action=dashboard');
        } else {
            $_SESSION['login_errors']['global'] = 'Email ou mot de passe incorrect.';
            header('Location: ' . BASE_URL . 'controllers/UserController.php?action=showLoginForm');
        }
        exit;
    }

    // ─── Déconnexion ──────────────────────────────────────────
    public function logout(): void {
        session_destroy();
        header('Location: ' . BASE_URL . 'controllers/UserController.php?action=showLoginForm');
        exit;
    }

    // ─── Dashboard utilisateur ────────────────────────────────
    public function dashboard(): void {
        $this->requireLogin();
        $user = $this->findById($_SESSION['user_id']);
        require_once __DIR__ . '/../views/frontoffice/dashboard.php';
    }

    // ─── Profil ───────────────────────────────────────────────
    public function profile(): void {
        $this->requireLogin();
        $user = $this->findById($_SESSION['user_id']);
        require_once __DIR__ . '/../views/frontoffice/profile.php';
    }

    // ─── Mettre à jour le profil ──────────────────────────────
    public function updateProfile(): void {
        $this->requireLogin();
        $errors = [];

        $prenom    = trim($_POST['prenom']    ?? '');
        $nom       = trim($_POST['nom']       ?? '');
        $email     = trim($_POST['email']     ?? '');
        $telephone = trim($_POST['telephone'] ?? '');

        if (empty($prenom) || strlen($prenom) < 2) {
            $errors['prenom'] = 'Prénom invalide (min. 2 caractères).';
        } elseif (!preg_match('/^[a-zA-ZÀ-ÿ\s\-\']+$/u', $prenom)) {
            $errors['prenom'] = 'Le prénom ne doit contenir que des lettres.';
        }

        if (empty($nom) || strlen($nom) < 2) {
            $errors['nom'] = 'Nom invalide (min. 2 caractères).';
        } elseif (!preg_match('/^[a-zA-ZÀ-ÿ\s\-\']+$/u', $nom)) {
            $errors['nom'] = 'Le nom ne doit contenir que des lettres.';
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email invalide.';
        } elseif ($this->emailExists($email, $_SESSION['user_id'])) {
            $errors['email'] = 'Cet email est déjà utilisé.';
        }

        if (empty($telephone) || !preg_match('/^[0-9\+\-\s]{8,15}$/', $telephone)) {
            $errors['telephone'] = 'Numéro de téléphone invalide (8-15 chiffres).';
        }

        if (!empty($errors)) {
            $_SESSION['profile_errors']    = $errors;
            $_SESSION['profile_old_input'] = $_POST;
            header('Location: ' . BASE_URL . 'controllers/UserController.php?action=profile');
            exit;
        }

        $updated = $this->updateUser($_SESSION['user_id'], [
            'prenom'    => $prenom,
            'nom'       => $nom,
            'email'     => $email,
            'telephone' => $telephone,
            'statut'    => 'actif',
        ]);

        if ($updated) {
            $_SESSION['user_prenom']     = $prenom;
            $_SESSION['user_nom']        = $nom;
            $_SESSION['user_email']      = $email;
            $_SESSION['user_telephone']  = $telephone;
            $_SESSION['profile_success'] = 'Profil mis à jour avec succès !';
        } else {
            $_SESSION['profile_errors']['global'] = 'Erreur lors de la mise à jour.';
        }

        header('Location: ' . BASE_URL . 'controllers/UserController.php?action=profile');
        exit;
    }

    // ─── Upload photo de profil ───────────────────────────────
    public function uploadPhoto(): void {
        $this->requireLogin();

        if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['profile_errors']['global'] = 'Erreur lors du téléchargement de l\'image.';
            header('Location: ' . BASE_URL . 'controllers/UserController.php?action=profile');
            exit;
        }

        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo   = finfo_open(FILEINFO_MIME_TYPE);
        $mime    = finfo_file($finfo, $_FILES['photo']['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowed)) {
            $_SESSION['profile_errors']['global'] = 'Format non autorisé. Utilisez JPG, PNG, GIF ou WEBP.';
            header('Location: ' . BASE_URL . 'controllers/UserController.php?action=profile');
            exit;
        }

        if ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
            $_SESSION['profile_errors']['global'] = 'Image trop grande (max. 2 Mo).';
            header('Location: ' . BASE_URL . 'controllers/UserController.php?action=profile');
            exit;
        }

        $uploadDir = __DIR__ . '/../uploads/photos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Supprimer l'ancienne photo si elle existe
        if (!empty($_SESSION['user_photo'])) {
            $oldFile = $uploadDir . $_SESSION['user_photo'];
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
        }

        $ext      = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $filename = 'user_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
        $dest     = $uploadDir . $filename;

        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $dest)) {
            $_SESSION['profile_errors']['global'] = 'Impossible de sauvegarder l\'image.';
            header('Location: ' . BASE_URL . 'controllers/UserController.php?action=profile');
            exit;
        }

        $stmt = $this->db->prepare("UPDATE users SET photo = :photo WHERE id = :id");
        $stmt->execute([':photo' => $filename, ':id' => $_SESSION['user_id']]);

        $_SESSION['user_photo']      = $filename;
        $_SESSION['profile_success'] = 'Photo de profil mise à jour !';

        header('Location: ' . BASE_URL . 'controllers/UserController.php?action=profile');
        exit;
    }

    // ─── Changer le mot de passe ──────────────────────────────
    public function changePassword(): void {
        $this->requireLogin();
        $errors = [];

        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password']     ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $user = $this->findById($_SESSION['user_id']);

        if (empty($current)) {
            $errors['current_password'] = 'Le mot de passe actuel est obligatoire.';
        } elseif (!$this->verifyPassword($current, $user['password'])) {
            $errors['current_password'] = 'Mot de passe actuel incorrect.';
        }

        if (empty($new)) {
            $errors['new_password'] = 'Le nouveau mot de passe est obligatoire.';
        } elseif (strlen($new) < 8) {
            $errors['new_password'] = 'Le nouveau mot de passe doit contenir au moins 8 caractères.';
        } elseif (!preg_match('/[A-Z]/', $new)) {
            $errors['new_password'] = 'Le mot de passe doit contenir au moins une majuscule.';
        } elseif (!preg_match('/[0-9]/', $new)) {
            $errors['new_password'] = 'Le mot de passe doit contenir au moins un chiffre.';
        }

        if ($new !== $confirm) {
            $errors['confirm_password'] = 'Les mots de passe ne correspondent pas.';
        }

        if (!empty($errors)) {
            $_SESSION['pwd_errors'] = $errors;
            header('Location: ' . BASE_URL . 'controllers/UserController.php?action=profile');
            exit;
        }

        if ($this->updatePassword($_SESSION['user_id'], $new)) {
            session_destroy();
            session_start();
            $_SESSION['success'] = 'Mot de passe modifié avec succès ! Connectez-vous avec votre nouveau mot de passe.';
            header('Location: ' . BASE_URL . 'controllers/UserController.php?action=showLoginForm');
        } else {
            $_SESSION['pwd_errors']['global'] = 'Erreur lors du changement de mot de passe.';
            header('Location: ' . BASE_URL . 'controllers/UserController.php?action=profile');
        }
        exit;
    }

    // ─── Mot de passe oublié : envoyer le code ────────────────
    public function sendResetCode(): void {
        // Endpoint AJAX - retourne du JSON
        header('Content-Type: application/json');

        $email = trim($_POST['email'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Email invalide.']);
            exit;
        }

        $user = $this->findByEmail($email);
        if (!$user) {
            // Neutre pour ne pas exposer si l'email existe
            echo json_encode(['success' => true, 'message' => 'Si cet email existe, un code a ete envoye.']);
            exit;
        }

        $code = sprintf('%06d', mt_rand(100000, 999999));
        $_SESSION['reset_code']    = $code;
        $_SESSION['reset_email']   = $email;
        $_SESSION['reset_expires'] = time() + 900;

        $name      = $user['prenom'] . ' ' . $user['nom'];
        $emailSent = sendResetCodeEmail($email, $code, $name);

        if ($emailSent) {
            echo json_encode(['success' => true, 'message' => 'Code envoye a votre adresse email.']);
        } else {
            // Mode dev : retourner le code dans la reponse
            echo json_encode(['success' => true, 'message' => 'Code envoye.', 'dev_code' => $code]);
        }
        exit;
    }

    // ─── Réinitialiser le mot de passe ────────────────────────
    public function resetPassword(): void {
        $email       = trim($_POST['email']            ?? '');
        $code        = trim($_POST['code']             ?? '');
        $newPassword = $_POST['new_password']          ?? '';
        $confirm     = $_POST['confirm_password']      ?? '';

        if (!isset($_SESSION['reset_code'], $_SESSION['reset_email'], $_SESSION['reset_expires'])) {
            $_SESSION['forgot_error'] = 'Session expirée. Veuillez recommencer.';
            header('Location: ' . BASE_URL . 'views/frontoffice/forgot_password.php');
            exit;
        }

        if (time() > $_SESSION['reset_expires']) {
            unset($_SESSION['reset_code'], $_SESSION['reset_email'], $_SESSION['reset_expires']);
            $_SESSION['forgot_error'] = 'Le code a expiré. Veuillez recommencer.';
            header('Location: ' . BASE_URL . 'views/frontoffice/forgot_password.php');
            exit;
        }

        if ($code !== $_SESSION['reset_code']) {
            $_SESSION['forgot_error'] = 'Code invalide.';
            header('Location: ' . BASE_URL . 'views/frontoffice/forgot_password.php');
            exit;
        }

        if ($email !== $_SESSION['reset_email']) {
            $_SESSION['forgot_error'] = 'Email invalide.';
            header('Location: ' . BASE_URL . 'views/frontoffice/forgot_password.php');
            exit;
        }

        if (empty($newPassword) || strlen($newPassword) < 8
            || !preg_match('/[A-Z]/', $newPassword)
            || !preg_match('/[0-9]/', $newPassword)) {
            $_SESSION['forgot_error'] = 'Mot de passe invalide (min. 8 caractères, 1 majuscule, 1 chiffre).';
            header('Location: ' . BASE_URL . 'views/frontoffice/forgot_password.php');
            exit;
        }

        if ($newPassword !== $confirm) {
            $_SESSION['forgot_error'] = 'Les mots de passe ne correspondent pas.';
            header('Location: ' . BASE_URL . 'views/frontoffice/forgot_password.php');
            exit;
        }

        $user = $this->findByEmail($email);
        if ($user) {
            $this->updatePassword($user['id'], $newPassword);
            unset($_SESSION['reset_code'], $_SESSION['reset_email'], $_SESSION['reset_expires']);
            // ── CORRECTION : utiliser $_SESSION['success'] lu par login.php ──
            $_SESSION['success'] = 'Mot de passe modifié avec succès ! Vous pouvez vous connecter.';
            header('Location: ' . BASE_URL . 'views/frontoffice/login.php');
            ob_end_flush();
            exit;
        } else {
            $_SESSION['forgot_error'] = 'Utilisateur non trouvé.';
            header('Location: ' . BASE_URL . 'views/frontoffice/forgot_password.php');
            ob_end_flush();
            exit;
        }
    }

    // ─── Protection de route ──────────────────────────────────
    private function requireLogin(): void {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'controllers/UserController.php?action=showLoginForm');
            exit;
        }
    }

    // ─── Routeur ──────────────────────────────────────────────
    public function handleRequest(): void {
        $action = $_GET['action'] ?? 'showLoginForm';

        switch ($action) {
            case 'showLoginForm':
            case 'login_form':      $this->showLoginForm();  break;
            case 'showRegister':
            case 'register_form':   $this->showRegister();   break;
            case 'index':           $this->showIndex();      break;
            case 'register':        $this->register();       break;
            case 'login':           $this->login();          break;
            case 'logout':          $this->logout();         break;
            case 'dashboard':       $this->dashboard();      break;
            case 'profile':         $this->profile();        break;
            case 'updateProfile':   $this->updateProfile();  break;
            case 'uploadPhoto':     $this->uploadPhoto();    break;
            case 'changePassword':  $this->changePassword(); break;
            case 'sendResetCode':   $this->sendResetCode();  break;
            case 'resetPassword':   $this->resetPassword();  break;
            default:
                http_response_code(404);
                echo '<h1>404 - Page non trouvée</h1>';
        }
    }
}

if (basename($_SERVER['SCRIPT_FILENAME']) === 'UserController.php') {
    $controller = new UserController();
    $controller->handleRequest();
}
