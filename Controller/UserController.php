<?php
// ============================================================
// Controller/UserController.php
// Contrôleur : Inscription, Connexion, Profil, Dashboard User
// Contient toute la logique métier (requêtes SQL) liée aux
// utilisateurs, auparavant dans models/User.php
// ============================================================

ob_start(); // Bufferiser la sortie pour éviter les erreurs "headers already sent"

require_once __DIR__ . '/../Model/User.php';

class UserController {

    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
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
            header('Location: ' . BASE_URL . 'Controller/UserController.php?action=dashboard');
            exit;
        }
        require_once __DIR__ . '/../View/frontoffice/login.php';
    }

    // ─── Afficher formulaire inscription ──────────────────────
    public function showRegister(): void {
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'Controller/UserController.php?action=dashboard');
            exit;
        }
        $_GET['show'] = 'register';
        require_once __DIR__ . '/../View/frontoffice/login.php';
    }

    // ─── Afficher formulaire connexion ────────────────────────
    public function showLoginForm(): void {
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'Controller/UserController.php?action=dashboard');
            exit;
        }
        $_GET['show'] = 'login';
        require_once __DIR__ . '/../View/frontoffice/login.php';
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
            header('Location: ' . BASE_URL . 'Controller/UserController.php?action=showRegister');
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
            header('Location: ' . BASE_URL . 'View/frontoffice/tous_les_trajets.php');
        } else {
            $_SESSION['errors']['global'] = 'Erreur lors de l\'inscription. Réessayez.';
            header('Location: ' . BASE_URL . 'Controller/UserController.php?action=showRegister');
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
            header('Location: ' . BASE_URL . 'Controller/UserController.php?action=showLoginForm');
            exit;
        }

        $user = $this->findByEmail($email);

        if ($user && $this->verifyPassword($password, $user['password'])) {
            if ($user['statut'] === 'inactif') {
                $_SESSION['login_errors']['global'] = 'Votre compte est désactivé. Contactez l\'administrateur.';
                header('Location: ' . BASE_URL . 'Controller/UserController.php?action=showLoginForm');
                exit;
            }

            $_SESSION['user_id']        = $user['id'];
            $_SESSION['user_prenom']    = $user['prenom'];
            $_SESSION['user_nom']       = $user['nom'];
            $_SESSION['user_email']     = $user['email'];
            $_SESSION['user_role']      = $user['role'];
            $_SESSION['user_telephone'] = $user['telephone'] ?? '';
            $_SESSION['user_photo']     = $user['photo'] ?? '';

            // ── Charger avatar + options depuis la BDD dès la connexion ──
            try {
                $stmtAv = $this->db->prepare("SELECT avatar, avatar_options FROM users WHERE id = :id");
                $stmtAv->execute([':id' => $user['id']]);
                $rowAv = $stmtAv->fetch();
                $_SESSION['user_avatar'] = ($rowAv && !empty($rowAv['avatar'])) ? $rowAv['avatar'] : '';
                // Recharger les options de l'avatar personnalisé si elles existent
                if ($rowAv && !empty($rowAv['avatar_options'])) {
                    $opts = json_decode($rowAv['avatar_options'], true);
                    $_SESSION['user_avatar_options'] = is_array($opts) ? $opts : [];
                } else {
                    $_SESSION['user_avatar_options'] = [];
                }
            } catch (\Exception $e) {
                // Colonne avatar_options absente → fallback sans options custom
                try {
                    $stmtAv2 = $this->db->prepare("SELECT avatar FROM users WHERE id = :id");
                    $stmtAv2->execute([':id' => $user['id']]);
                    $rowAv2 = $stmtAv2->fetch();
                    $_SESSION['user_avatar'] = ($rowAv2 && !empty($rowAv2['avatar'])) ? $rowAv2['avatar'] : '';
                } catch (\Exception $e2) {}
                $_SESSION['user_avatar_options'] = [];
            }

            header('Location: ' . BASE_URL . 'View/frontoffice/tous_les_trajets.php');
        } else {
            $_SESSION['login_errors']['global'] = 'Email ou mot de passe incorrect.';
            header('Location: ' . BASE_URL . 'Controller/UserController.php?action=showLoginForm');
        }
        exit;
    }

    // ─── Déconnexion ──────────────────────────────────────────
    public function logout(): void {
        // 1. Vider toutes les variables de session
        $_SESSION = [];

        // 2. Supprimer le cookie de session côté navigateur
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), '',
                time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }

        // 3. Détruire la session serveur
        session_destroy();

        // 4. Démarrer une session propre pour la prochaine visite
        session_start();
        session_regenerate_id(true);

        header('Location: ' . BASE_URL . 'Controller/UserController.php?action=showLoginForm');
        exit;
    }

    // ─── Dashboard utilisateur ────────────────────────────────
    public function dashboard(): void {
        $this->requireLogin();
        $user = $this->findById($_SESSION['user_id']);

        // ── Resynchroniser la photo depuis la BDD ─────────────
        if (!empty($user['photo'])) {
            $_SESSION['user_photo'] = $user['photo'];
        }

        // ── Resynchroniser avatar + options depuis la BDD ───
        try {
            $stmtAv = $this->db->prepare("SELECT avatar, avatar_options FROM users WHERE id = :id");
            $stmtAv->execute([':id' => $_SESSION['user_id']]);
            $rowAv = $stmtAv->fetch();
            $_SESSION['user_avatar'] = ($rowAv && !empty($rowAv['avatar'])) ? $rowAv['avatar'] : '';
            if ($rowAv && !empty($rowAv['avatar_options'])) {
                $opts = json_decode($rowAv['avatar_options'], true);
                $_SESSION['user_avatar_options'] = is_array($opts) ? $opts : [];
            } else {
                $_SESSION['user_avatar_options'] = $_SESSION['user_avatar_options'] ?? [];
            }
        } catch (\Exception $e) {
            // Fallback si avatar_options absent de la BDD
            try {
                $stmtAv2 = $this->db->prepare("SELECT avatar FROM users WHERE id = :id");
                $stmtAv2->execute([':id' => $_SESSION['user_id']]);
                $rowAv2 = $stmtAv2->fetch();
                if ($rowAv2 && !empty($rowAv2['avatar'])) {
                    $_SESSION['user_avatar'] = $rowAv2['avatar'];
                }
            } catch (\Exception $e2) {}
        }

        require_once __DIR__ . '/../View/frontoffice/dashboard.php';
    }

    // ─── Profil ───────────────────────────────────────────────
    public function profile(): void {
        $this->requireLogin();
        $user = $this->findById($_SESSION['user_id']);

        // ── Resynchroniser photo depuis la BDD ────────────────
        if (!empty($user['photo'])) {
            $_SESSION['user_photo'] = $user['photo'];
        }

        // ── Toujours recharger avatar + options depuis la BDD ──
        try {
            $stmt = $this->db->prepare("SELECT avatar, avatar_options FROM users WHERE id = :id");
            $stmt->execute([':id' => $_SESSION['user_id']]);
            $row = $stmt->fetch();
            $_SESSION['user_avatar'] = ($row && !empty($row['avatar'])) ? $row['avatar'] : '';
            if ($row && !empty($row['avatar_options'])) {
                $opts = json_decode($row['avatar_options'], true);
                $_SESSION['user_avatar_options'] = is_array($opts) ? $opts : [];
            } else {
                $_SESSION['user_avatar_options'] = [];
            }
        } catch (\Exception $e) {
            // Fallback si avatar_options n'existe pas encore en BDD
            try {
                $stmt2 = $this->db->prepare("SELECT avatar FROM users WHERE id = :id");
                $stmt2->execute([':id' => $_SESSION['user_id']]);
                $row2 = $stmt2->fetch();
                $_SESSION['user_avatar'] = ($row2 && !empty($row2['avatar'])) ? $row2['avatar'] : '';
            } catch (\Exception $e2) {}
        }

        require __DIR__ . '/../View/frontoffice/profile.php';
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
            header('Location: ' . BASE_URL . 'Controller/UserController.php?action=profile');
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

        header('Location: ' . BASE_URL . 'Controller/UserController.php?action=profile');
        exit;
    }

    // ─── Upload photo de profil ───────────────────────────────
    public function uploadPhoto(): void {
        $this->requireLogin();

        if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['profile_errors']['global'] = 'Erreur lors du téléchargement de l\'image.';
            header('Location: ' . BASE_URL . 'Controller/UserController.php?action=profile');
            exit;
        }

        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo   = finfo_open(FILEINFO_MIME_TYPE);
        $mime    = finfo_file($finfo, $_FILES['photo']['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowed)) {
            $_SESSION['profile_errors']['global'] = 'Format non autorisé. Utilisez JPG, PNG, GIF ou WEBP.';
            header('Location: ' . BASE_URL . 'Controller/UserController.php?action=profile');
            exit;
        }

        if ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
            $_SESSION['profile_errors']['global'] = 'Image trop grande (max. 2 Mo).';
            header('Location: ' . BASE_URL . 'Controller/UserController.php?action=profile');
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

        $tmpFilePath = $_FILES['photo']['tmp_name'];
        if (!move_uploaded_file($tmpFilePath, $dest)) {
            $_SESSION['profile_errors']['global'] = 'Impossible de sauvegarder l\'image.';
            header('Location: ' . BASE_URL . 'Controller/UserController.php?action=profile');
            exit;
        }

        // ── Sauvegarde en BDD (BLOB) pour persistance garantie ──
        require_once __DIR__ . '/../helpers/ImageHelper.php';
        ImageHelper::saveUserPhotoDB((int)$_SESSION['user_id'], $dest, $mime);

        $stmt = $this->db->prepare("UPDATE users SET photo = :photo WHERE id = :id");
        $stmt->execute([':photo' => $filename, ':id' => $_SESSION['user_id']]);

        $_SESSION['user_photo']      = $filename;
        $_SESSION['profile_success'] = 'Photo de profil mise à jour !';

        header('Location: ' . BASE_URL . 'Controller/UserController.php?action=profile');
        exit;
    }

    // ─── Sauvegarder l'avatar choisi ─────────────────────────
    public function saveAvatar(): void {
        $this->requireLogin();
        header('Content-Type: application/json');

        $allowed = ['av1','av2','av3','av4','av5','av6','av7','av8','av9','av10','av11','av12','av13','av14','av15'];
        $avatar  = trim($_POST['avatar'] ?? '');

        if (!in_array($avatar, $allowed)) {
            echo json_encode(['success' => false, 'error' => 'Avatar invalide.']);
            exit;
        }

        // Sauvegarder en session (et en BDD si la colonne existe)
        $_SESSION['user_avatar']         = $avatar;
        $_SESSION['user_avatar_options'] = []; // effacer avatar personnalisé

        try {
            $stmt = $this->db->prepare("UPDATE users SET avatar = :avatar WHERE id = :id");
            $stmt->execute([':avatar' => $avatar, ':id' => $_SESSION['user_id']]);
        } catch (\Exception $e) {
            // Colonne avatar peut ne pas exister encore, on ignore
        }

        echo json_encode(['success' => true]);
        exit;
    }

    // ─── Sauvegarder les options avatar personnalisé ──────────
    public function saveAvatarOptions(): void {
        $this->requireLogin();
        header('Content-Type: application/json');

        $raw = file_get_contents('php://input');
        $opts = json_decode($raw, true);

        if (!$opts || !isset($opts['seed'])) {
            echo json_encode(['success' => false, 'error' => 'Options invalides.']);
            exit;
        }

        // Valider les champs autorisés
        $allowed_skins   = ['ffdbb4','edb98a','d08b5b','ae5d29','614335','f8d25c'];
        $allowed_tops    = ['shortHairShortFlat','shortHairShortWaved','shortHairDreads01','longHairStraight','longHairCurvy','longHairBigHair','longHairBob','hat','hijab','winterHat1'];
        $allowed_clothes = ['hoodie','blazerAndSweater','collarAndSweater','graphicShirt','shirtCrewNeck','shirtVNeck','overall'];

        $clean = [
            'seed'            => 'CustomUser' . $_SESSION['user_id'],
            'skinColor'       => in_array($opts['skinColor'] ?? '', $allowed_skins)   ? $opts['skinColor']   : 'ffdbb4',
            'hairColor'       => preg_match('/^[0-9a-fA-F]{6}$/', $opts['hairColor'] ?? '') ? $opts['hairColor'] : '2c1b18',
            'top'             => in_array($opts['top'] ?? '', $allowed_tops)           ? $opts['top']         : 'shortHairShortFlat',
            'clothesType'     => in_array($opts['clothesType'] ?? '', $allowed_clothes)? $opts['clothesType'] : 'hoodie',
            'clothesColor'    => preg_match('/^[0-9a-fA-F]{6}$/', $opts['clothesColor'] ?? '') ? $opts['clothesColor'] : '3c4a6e',
            'backgroundColor' => preg_match('/^[0-9a-fA-F]{6}$/', $opts['backgroundColor'] ?? '') ? $opts['backgroundColor'] : '1565c0',
            'eyes'            => 'default',
            'mouth'           => 'smile',
        ];

        $_SESSION['user_avatar_options'] = $clean;
        $_SESSION['user_avatar']         = ''; // effacer avatar prédéfini

        try {
            // Sauvegarder 'custom' dans avatar ET les options JSON dans avatar_options
            $stmt = $this->db->prepare(
                "UPDATE users SET avatar = :avatar, avatar_options = :opts WHERE id = :id"
            );
            $stmt->execute([
                ':avatar' => 'custom',
                ':opts'   => json_encode($clean),
                ':id'     => $_SESSION['user_id'],
            ]);
        } catch (\Exception $e) {
            // Si avatar_options n'existe pas encore, fallback sans cette colonne
            try {
                $stmt = $this->db->prepare("UPDATE users SET avatar = :avatar WHERE id = :id");
                $stmt->execute([':avatar' => 'custom', ':id' => $_SESSION['user_id']]);
            } catch (\Exception $e2) { /* ignore */ }
        }

        echo json_encode(['success' => true]);
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
            header('Location: ' . BASE_URL . 'Controller/UserController.php?action=profile');
            exit;
        }

        if ($this->updatePassword($_SESSION['user_id'], $new)) {
            session_destroy();
            session_start();
            $_SESSION['success'] = 'Mot de passe modifié avec succès ! Connectez-vous avec votre nouveau mot de passe.';
            header('Location: ' . BASE_URL . 'Controller/UserController.php?action=showLoginForm');
        } else {
            $_SESSION['pwd_errors']['global'] = 'Erreur lors du changement de mot de passe.';
            header('Location: ' . BASE_URL . 'Controller/UserController.php?action=profile');
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
            echo json_encode(['success' => false, 'message' => 'Aucun compte trouvé avec cet email.']);
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
            header('Location: ' . BASE_URL . 'View/frontoffice/forgot_password.php');
            exit;
        }

        if (time() > $_SESSION['reset_expires']) {
            unset($_SESSION['reset_code'], $_SESSION['reset_email'], $_SESSION['reset_expires']);
            $_SESSION['forgot_error'] = 'Le code a expiré. Veuillez recommencer.';
            header('Location: ' . BASE_URL . 'View/frontoffice/forgot_password.php');
            exit;
        }

        if ($code !== $_SESSION['reset_code']) {
            $_SESSION['forgot_error'] = 'Code invalide.';
            header('Location: ' . BASE_URL . 'View/frontoffice/forgot_password.php');
            exit;
        }

        if ($email !== $_SESSION['reset_email']) {
            $_SESSION['forgot_error'] = 'Email invalide.';
            header('Location: ' . BASE_URL . 'View/frontoffice/forgot_password.php');
            exit;
        }

        if (empty($newPassword) || strlen($newPassword) < 8
            || !preg_match('/[A-Z]/', $newPassword)
            || !preg_match('/[0-9]/', $newPassword)) {
            $_SESSION['forgot_error'] = 'Mot de passe invalide (min. 8 caractères, 1 majuscule, 1 chiffre).';
            header('Location: ' . BASE_URL . 'View/frontoffice/forgot_password.php');
            exit;
        }

        if ($newPassword !== $confirm) {
            $_SESSION['forgot_error'] = 'Les mots de passe ne correspondent pas.';
            header('Location: ' . BASE_URL . 'View/frontoffice/forgot_password.php');
            exit;
        }

        $user = $this->findByEmail($email);
        if ($user) {
            $this->updatePassword($user['id'], $newPassword);
            unset($_SESSION['reset_code'], $_SESSION['reset_email'], $_SESSION['reset_expires']);
            // ── CORRECTION : utiliser $_SESSION['success'] lu par login.php ──
            $_SESSION['success'] = 'Mot de passe modifié avec succès ! Vous pouvez vous connecter.';
            header('Location: ' . BASE_URL . 'View/frontoffice/login.php');
            ob_end_flush();
            exit;
        } else {
            $_SESSION['forgot_error'] = 'Utilisateur non trouvé.';
            header('Location: ' . BASE_URL . 'View/frontoffice/forgot_password.php');
            ob_end_flush();
            exit;
        }
    }

    // ─── Protection de route ──────────────────────────────────
    private function requireLogin(): void {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /ecoride/View/frontoffice/login.php');
            exit;
        }
    }

    // ─── Face Verification : retourne la photo de profil en base64 ──────────
    // Permet la vérification faciale côté client (JS + face-api.js).
    // Si l'utilisateur n'a pas de photo, on autorise la connexion sans vérification.
    public function getFaceImage(): void {
        header('Content-Type: application/json');

        $email = trim($_POST['email'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'image' => null]);
            exit;
        }

        $row = $this->findByEmail($email);

        if (!$row || empty($row['photo'])) {
            // Pas de compte ou pas de photo → autoriser sans vérification faciale
            echo json_encode(['success' => true, 'image' => null]);
            exit;
        }

        $picPath = __DIR__ . '/../uploads/photos/' . $row['photo'];

        if (!file_exists($picPath)) {
            echo json_encode(['success' => true, 'image' => null]);
            exit;
        }

        $imageData = base64_encode(file_get_contents($picPath));
        echo json_encode(['success' => true, 'image' => $imageData]);
        exit;
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
            case 'saveAvatar':         $this->saveAvatar();        break;
            case 'saveAvatarOptions':  $this->saveAvatarOptions(); break;
            case 'changePassword':  $this->changePassword(); break;
            case 'sendResetCode':   $this->sendResetCode();  break;
            case 'resetPassword':   $this->resetPassword();  break;
            case 'getFaceImage':    $this->getFaceImage();   break;
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
