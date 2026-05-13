<?php
// ============================================================
// Controller/AdminController.php
// Contrôleur BackOffice : Dashboard + CRUD Passagers
// Contient toute la logique métier (requêtes SQL) liée aux
// admins et aux passagers, auparavant dans models/User.php
// ============================================================

require_once __DIR__ . '/../Model/User.php';

class AdminController {

    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // ════════════════════════════════════════════════════════
    // MÉTHODES D'ACCÈS BASE DE DONNÉES (ex-modèles User/Admin)
    // ════════════════════════════════════════════════════════

    // ─── Connexion admin ──────────────────────────────────────
    private function findAdminByEmail(string $email): ?array {
        $stmt = $this->db->prepare("SELECT * FROM admins WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    private function findAdminById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM admins WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    // ─── Page profil admin ────────────────────────────────────
    public function showProfile(): void {
        $this->requireAdmin();
        $admin = $this->findAdminById($_SESSION['admin_id']);
        require_once __DIR__ . '/../View/backoffice/admin_profile.php';
    }

    // ─── Mettre à jour profil admin ───────────────────────────
    public function updateAdminProfile(): void {
        $this->requireAdmin();
        $errors = [];

        $nom   = trim($_POST['nom']   ?? '');
        // Email en lecture seule : on ignore le POST et on conserve l'email actuel
        $email = $_SESSION['admin_email'] ?? '';

        if (empty($nom) || strlen($nom) < 2) {
            $errors['nom'] = 'Nom invalide (min. 2 caractères).';
        }
        // Email est fig� en session, pas de validation n�cessaire

        if (!empty($errors)) {
            $_SESSION['admin_profile_errors']    = $errors;
            $_SESSION['admin_profile_old_input'] = $_POST;
            header('Location: ' . BASE_URL . 'Controller/AdminController.php?action=showProfile');
            exit;
        }

        $stmt = $this->db->prepare("UPDATE admins SET nom = :nom, email = :email WHERE id = :id");
        $stmt->execute([':nom' => $nom, ':email' => $email, ':id' => $_SESSION['admin_id']]);

        $_SESSION['admin_nom']             = $nom;
        $_SESSION['admin_email']           = $email;
        $_SESSION['admin_profile_success'] = 'Profil mis � jour avec succ�s !';

        header('Location: ' . BASE_URL . 'Controller/AdminController.php?action=showProfile');
        exit;
    }

    // ─── Statistiques dashboard ───────────────────────────────
    private function getDashboardStats(): array {
        $stats = [];

        $stmt = $this->db->query("SELECT COUNT(*) as total FROM users WHERE role = 'passager'");
        $stats['total_passagers'] = $stmt->fetch()['total'];

        $stmt = $this->db->query("SELECT COUNT(*) as total FROM users WHERE role = 'passager' AND statut = 'actif'");
        $stats['active_passagers'] = $stmt->fetch()['total'];

        $stmt = $this->db->query("SELECT COUNT(*) as total FROM users WHERE role = 'passager' AND statut = 'inactif'");
        $stats['inactive_passagers'] = $stmt->fetch()['total'];

        $stmt = $this->db->query("SELECT COUNT(*) as total FROM admins");
        $stats['total_admins'] = $stmt->fetch()['total'];

        return $stats;
    }

    // ─── Liste de tous les passagers ──────────────────────────
    private function getAllPassagers(): array {
        $stmt = $this->db->query("SELECT * FROM users WHERE role = 'passager' ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    // ─── Trouver un utilisateur par ID ────────────────────────
    private function findUserById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    // ─── Vérifier si un email est déjà utilisé ────────────────
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

    // ─── Mettre à jour un utilisateur ─────────────────────────
    private function updateUser(int $id, array $data): bool {
        $stmt = $this->db->prepare(
            "UPDATE users SET prenom = :prenom, nom = :nom, email = :email,
             telephone = :telephone, statut = :statut WHERE id = :id"
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

    // ─── Créer un utilisateur (backoffice) ────────────────────
    private function createUser(User $user): bool {
        $stmt = $this->db->prepare(
            "INSERT INTO users (prenom, nom, email, telephone, role, statut, password, created_by_admin, created_at)
             VALUES (:prenom, :nom, :email, :telephone, :role, :statut, :password, :admin_id, NOW())"
        );
        return $stmt->execute([
            ':prenom'    => $user->getPrenom(),
            ':nom'       => $user->getNom(),
            ':email'     => $user->getEmail(),
            ':telephone' => $user->getTelephone(),
            ':role'      => $user->getRole(),
            ':statut'    => $user->getStatut(),
            ':password'  => password_hash($user->getPassword(), PASSWORD_DEFAULT),
            ':admin_id'  => $_SESSION['admin_id'],
        ]);
    }

    // ─── Lister tous les utilisateurs ─────────────────────────
    private function findAllUsers(): array {
        $stmt = $this->db->query("SELECT * FROM users ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    // ─── Bannir un utilisateur ────────────────────────────────
    private function banUser(int $id): bool {
        $stmt = $this->db->prepare("UPDATE users SET statut = 'inactif' WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    // ─── Réactiver un utilisateur ─────────────────────────────
    private function unbanUser(int $id): bool {
        $stmt = $this->db->prepare("UPDATE users SET statut = 'actif' WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    // ─── Supprimer un utilisateur ─────────────────────────────
    private function removeUserFromDb(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    // ─── Mettre à jour le mot de passe ────────────────────────
    private function updatePassword(int $id, string $newPassword): bool {
        $stmt = $this->db->prepare("UPDATE users SET password = :password WHERE id = :id");
        return $stmt->execute([
            ':password' => password_hash($newPassword, PASSWORD_DEFAULT),
            ':id'       => $id,
        ]);
    }

    // ─── Données liées : véhicules ────────────────────────────
    private function getUserVehicles(int $userId): array {
        try {
            $stmt = $this->db->prepare("SELECT * FROM vehicles WHERE user_id = :user_id");
            $stmt->execute([':user_id' => $userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) { return []; }
    }

    // ─── Données liées : trajets ──────────────────────────────
    private function getUserTrips(int $userId): array {
        try {
            $stmt = $this->db->prepare("SELECT * FROM trips WHERE driver_id = :driver_id ORDER BY date DESC");
            $stmt->execute([':driver_id' => $userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) { return []; }
    }

    // ─── Données liées : réclamations ─────────────────────────
    private function getUserReclamations(int $userId): array {
        try {
            $check = $this->db->query("SHOW TABLES LIKE 'reclamations'");
            if ($check->rowCount() > 0) {
                $stmt = $this->db->prepare("SELECT * FROM reclamations WHERE user_id = :user_id ORDER BY created_at DESC");
                $stmt->execute([':user_id' => $userId]);
                return $stmt->fetchAll();
            }
        } catch (PDOException $e) {}
        return [];
    }

    // ─── Données liées : événements ───────────────────────────
    private function getUserEvents(int $userId): array {
        try {
            $check = $this->db->query("SHOW TABLES LIKE 'events'");
            if ($check->rowCount() > 0) {
                $stmt = $this->db->prepare("SELECT * FROM events WHERE user_id = :user_id ORDER BY date DESC");
                $stmt->execute([':user_id' => $userId]);
                return $stmt->fetchAll();
            }
        } catch (PDOException $e) {}
        return [];
    }

    // ─── Données liées : objets perdus/trouvés ────────────────
    private function getUserLostFound(int $userId): array {
        try {
            $check = $this->db->query("SHOW TABLES LIKE 'lost_found'");
            if ($check->rowCount() > 0) {
                $stmt = $this->db->prepare("SELECT * FROM lost_found WHERE user_id = :user_id ORDER BY created_at DESC");
                $stmt->execute([':user_id' => $userId]);
                return $stmt->fetchAll();
            }
        } catch (PDOException $e) {}
        return [];
    }

    // ════════════════════════════════════════════════════════
    // ACTIONS DU CONTRÔLEUR
    // ════════════════════════════════════════════════════════

    // ─── Page connexion admin ─────────────────────────────────
    public function showLogin(): void {
        require_once __DIR__ . '/../View/backoffice/admin_login.php';
    }

    // ─── Authentifier admin ───────────────────────────────────
    public function login(): void {
        $errors = [];

        $email    = trim($_POST['email']    ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email)) {
            $errors['email'] = 'L\'email est obligatoire.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Format d\'email invalide.';
        }

        if (empty($password)) {
            $errors['password'] = 'Le mot de passe est obligatoire.';
        }

        if (!empty($errors)) {
            $_SESSION['admin_login_errors']    = $errors;
            $_SESSION['admin_login_old_input'] = $_POST;
            header('Location: ' . BASE_URL . 'Controller/AdminController.php?action=showLogin');
            exit;
        }

        $admin = $this->findAdminByEmail($email);

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id']    = $admin['id'];
            $_SESSION['admin_nom']   = $admin['nom'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['admin_photo'] = $admin['photo'] ?? '';
            header('Location: ' . BASE_URL . 'Controller/AdminController.php?action=dashboard');
        } else {
            $_SESSION['admin_login_errors']['global'] = 'Email ou mot de passe incorrect.';
            header('Location: ' . BASE_URL . 'Controller/AdminController.php?action=showLogin');
        }
        exit;
    }

    // ─── Déconnexion admin ────────────────────────────────────
    public function logout(): void {
        session_start();
        unset($_SESSION['admin_id'], $_SESSION['admin_nom'], $_SESSION['admin_email']);
        session_destroy();
        header('Location: ' . BASE_URL . 'Controller/AdminController.php?action=showLogin');
        exit;
    }

    // ─── Dashboard ────────────────────────────────────────────
    public function dashboard(): void {
        $this->requireAdmin();
        header('Content-Type: text/html; charset=utf-8');
        $stats     = $this->getDashboardStats();
        $passagers = $this->getAllPassagers();
        require_once __DIR__ . '/../View/backoffice/admin_dashboard.php';
    }

    // ─── Liste des utilisateurs ───────────────────────────────
    public function listUsers(): void {
        $this->requireAdmin();
        $users = $this->findAllUsers();
        require_once __DIR__ . '/../View/backoffice/users_list.php';
    }

    // ─── Afficher formulaire ajout utilisateur ────────────────
    public function showAddUser(): void {
        $this->requireAdmin();
        require_once __DIR__ . '/../View/backoffice/add_user.php';
    }

    // ─── Ajouter un utilisateur ───────────────────────────────
    public function addUser(): void {
        $this->requireAdmin();
        $errors = [];

        $prenom    = trim($_POST['prenom']           ?? '');
        $nom       = trim($_POST['nom']              ?? '');
        $email     = trim($_POST['email']            ?? '');
        $telephone = trim($_POST['telephone']        ?? '');
        $role      = $_POST['role']                  ?? 'passager';
        $statut    = $_POST['statut']                ?? 'actif';
        $password  = $_POST['password']              ?? '';
        $confirm   = $_POST['confirm_password']      ?? '';

        if (empty($prenom) || strlen($prenom) < 2) {
            $errors['prenom'] = 'Le prénom doit contenir au moins 2 caractères.';
        }
        if (empty($nom) || strlen($nom) < 2) {
            $errors['nom'] = 'Le nom doit contenir au moins 2 caractères.';
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email invalide.';
        } elseif ($this->emailExists($email)) {
            $errors['email'] = 'Cet email est déjà utilisé.';
        }
        if (empty($telephone) || !preg_match('/^[0-9\+\-\s]{8,15}$/', $telephone)) {
            $errors['telephone'] = 'Numéro de téléphone invalide (8-15 chiffres).';
        }
        if (empty($password) || strlen($password) < 8
            || !preg_match('/[A-Z]/', $password)
            || !preg_match('/[0-9]/', $password)) {
            $errors['password'] = 'Mot de passe invalide (min. 8 caractères, 1 majuscule, 1 chiffre).';
        }
        if ($password !== $confirm) {
            $errors['confirm_password'] = 'Les mots de passe ne correspondent pas.';
        }

        if (!empty($errors)) {
            $_SESSION['add_errors']    = $errors;
            $_SESSION['add_old_input'] = $_POST;
            header('Location: ' . BASE_URL . 'Controller/AdminController.php?action=showAddUser');
            exit;
        }

        $user = new User();
        $user->setPrenom($prenom);
        $user->setNom($nom);
        $user->setEmail($email);
        $user->setTelephone($telephone);
        $user->setRole($role);
        $user->setStatut($statut);
        $user->setPassword($password);

        if ($this->createUser($user)) {
            $_SESSION['success'] = 'Utilisateur créé avec succès.';
            header('Location: ' . BASE_URL . 'Controller/AdminController.php?action=listUsers');
        } else {
            $_SESSION['add_errors']['global'] = 'Erreur lors de la création.';
            header('Location: ' . BASE_URL . 'Controller/AdminController.php?action=showAddUser');
        }
        exit;
    }

    // ─── Afficher formulaire édition utilisateur ──────────────
    public function showEditUser(): void {
        $this->requireAdmin();
        $id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $user = $this->findUserById($id);
        if (!$user) {
            header('Location: ' . BASE_URL . 'Controller/AdminController.php?action=listUsers');
            exit;
        }
        require_once __DIR__ . '/../View/backoffice/edit_user.php';
    }

    // ─── Modifier un utilisateur ──────────────────────────────
    public function editUser(): void {
        $this->requireAdmin();
        $errors = [];

        $id        = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $prenom    = trim($_POST['prenom']    ?? '');
        $nom       = trim($_POST['nom']       ?? '');
        $email     = trim($_POST['email']     ?? '');
        $telephone = trim($_POST['telephone'] ?? '');
        $statut    = $_POST['statut']         ?? 'actif';
        $password  = $_POST['password']       ?? '';
        $confirm   = $_POST['confirm_password'] ?? '';

        if (empty($prenom) || strlen($prenom) < 2) {
            $errors['prenom'] = 'Prenom invalide (min. 2 caractères).';
        }
        if (empty($nom) || strlen($nom) < 2) {
            $errors['nom'] = 'Nom invalide (min. 2 caractères).';
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email invalide.';
        } elseif ($this->emailExists($email, $id)) {
            $errors['email'] = 'Cet email est déjà utilisé.';
        }
        if (!empty($telephone) && !preg_match('/^[0-9\+\-\s]{8,15}$/', $telephone)) {
            $errors['telephone'] = 'Numéro de téléphone invalide.';
        }
        if (!empty($password)) {
            if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
                $errors['password'] = 'Mot de passe invalide (min. 8 caractères, 1 majuscule, 1 chiffre).';
            } elseif ($password !== $confirm) {
                $errors['confirm_password'] = 'Les mots de passe ne correspondent pas.';
            }
        }

        if (!empty($errors)) {
            $_SESSION['edit_errors']    = $errors;
            $_SESSION['edit_old_input'] = $_POST;
            header('Location: ' . BASE_URL . 'Controller/AdminController.php?action=showEditUser&id=' . $id);
            exit;
        }

        $this->updateUser($id, compact('prenom', 'nom', 'email', 'telephone', 'statut'));

        if (!empty($password)) {
            $this->updatePassword($id, $password);
        }

        $_SESSION['success'] = 'Utilisateur modifié avec succès.';
        header('Location: ' . BASE_URL . 'Controller/AdminController.php?action=listUsers');
        exit;
    }

    // ─── Supprimer un utilisateur ─────────────────────────────
    public function deleteUser(): void {
        $this->requireAdmin();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id > 0 && $this->removeUserFromDb($id)) {
            $_SESSION['success'] = 'Utilisateur supprimé avec succès.';
        } else {
            $_SESSION['error'] = 'Erreur lors de la suppression.';
        }
        header('Location: ' . BASE_URL . 'Controller/AdminController.php?action=listUsers');
        exit;
    }

    // ─── Récupérer un passager (AJAX) ─────────────────────────
    public function getPassager(): void {
        $this->requireAdmin();
        $id       = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $passager = $this->findUserById($id);

        if ($passager) {
            header('Content-Type: application/json');
            echo json_encode($passager);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Passager non trouvé']);
        }
        exit;
    }

    // ─── Page dédiée Modifier un passager ────────────────────
    public function showEditPassager(): void {
        $this->requireAdmin();
        $id       = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $passager = $this->findUserById($id);

        if (!$passager) {
            $_SESSION['admin_error'] = 'Passager introuvable.';
            header('Location: ' . BASE_URL . 'Controller/AdminController.php?action=dashboard');
            exit;
        }

        require_once __DIR__ . '/../View/backoffice/edit_passager.php';
    }

    // ─── Page dédiée Détails d'un passager ───────────────────
    public function showPassagerDetailsPage(): void {
        $this->requireAdmin();
        $id       = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $passager = $this->findUserById($id);

        if (!$passager) {
            $_SESSION['admin_error'] = 'Passager introuvable.';
            header('Location: ' . BASE_URL . 'Controller/AdminController.php?action=dashboard');
            exit;
        }

        $vehicles     = $this->getUserVehicles($id);
        $trips        = $this->getUserTrips($id);
        $reclamations = $this->getUserReclamations($id);
        $events       = $this->getUserEvents($id);
        $lost_found   = $this->getUserLostFound($id);

        require_once __DIR__ . '/../View/backoffice/passager_details.php';
    }

    // ─── Détails complets d'un passager (AJAX) ────────────────
    public function getPassagerDetails(): void {
        $this->requireAdmin();
        $id       = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $passager = $this->findUserById($id);

        if ($passager) {
            $details = [
                'user'         => $passager,
                'vehicles'     => $this->getUserVehicles($id),
                'trips'        => $this->getUserTrips($id),
                'reclamations' => $this->getUserReclamations($id),
                'events'       => $this->getUserEvents($id),
                'lost_found'   => $this->getUserLostFound($id),
            ];
            header('Content-Type: application/json');
            echo json_encode($details);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Passager non trouvé']);
        }
        exit;
    }

    // ─── Modifier un passager (via modal dashboard) ───────────
    public function editPassager(): void {
        $this->requireAdmin();
        $errors = [];

        $id        = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $prenom    = trim($_POST['prenom']    ?? '');
        $nom       = trim($_POST['nom']       ?? '');
        $email     = trim($_POST['email']     ?? '');
        $telephone = trim($_POST['telephone'] ?? '');
        $statut    = $_POST['statut']         ?? 'actif';

        if (empty($prenom) || strlen($prenom) < 2) {
            $errors['prenom'] = 'Prénom invalide (min. 2 caractères).';
        }
        if (empty($nom) || strlen($nom) < 2) {
            $errors['nom'] = 'Nom invalide (min. 2 caractères).';
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email invalide.';
        } elseif ($this->emailExists($email, $id)) {
            $errors['email'] = 'Cet email est déjà utilisé.';
        }

        if (!empty($errors)) {
            $_SESSION['admin_error'] = implode(', ', $errors);
            if ($redirectTo === 'passager_details') {
                header('Location: ' . BASE_URL . 'Controller/AdminController.php?action=showEditPassager&id=' . $id);
            } else {
                header('Location: ' . BASE_URL . 'Controller/AdminController.php?action=dashboard');
            }
            exit;
        }

        $redirectTo = $_POST['redirect_to'] ?? '';

        if ($this->updateUser($id, compact('prenom', 'nom', 'email', 'telephone', 'statut'))) {
            $_SESSION['admin_success'] = 'Passager modifié avec succès !';
        } else {
            $_SESSION['admin_error'] = 'Erreur lors de la modification.';
        }

        if ($redirectTo === 'passager_details') {
            header('Location: ' . BASE_URL . 'Controller/AdminController.php?action=dashboard&tab=passagers');
        } else {
            header('Location: ' . BASE_URL . 'Controller/AdminController.php?action=dashboard');
        }
        exit;
    }

    // ─── Bannir un passager ───────────────────────────────────
    public function banPassager(): void {
        $this->requireAdmin();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            $_SESSION['admin_error'] = 'ID invalide.';
        } elseif ($this->banUser($id)) {
            $_SESSION['admin_success'] = 'Passager banni avec succès.';
        } else {
            $_SESSION['admin_error'] = 'Erreur lors du bannissement.';
        }

        header('Location: ' . BASE_URL . 'Controller/AdminController.php?action=dashboard');
        exit;
    }

    // ─── Réactiver un passager ────────────────────────────────
    public function unbanPassager(): void {
        $this->requireAdmin();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            $_SESSION['admin_error'] = 'ID invalide.';
        } elseif ($this->unbanUser($id)) {
            $_SESSION['admin_success'] = 'Passager réactivé avec succès.';
        } else {
            $_SESSION['admin_error'] = 'Erreur lors de la réactivation.';
        }

        header('Location: ' . BASE_URL . 'Controller/AdminController.php?action=dashboard');
        exit;
    }

    // ─── Upload photo admin ───────────────────────────────────
    public function uploadAdminPhoto(): void {
        $this->requireAdmin();

        if (!isset($_FILES['admin_photo']) || $_FILES['admin_photo']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['profile_error'] = 'Erreur lors du telechargement.';
            header('Location: ' . BASE_URL . 'Controller/AdminController.php?action=showProfile');
            exit;
        }

        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo   = finfo_open(FILEINFO_MIME_TYPE);
        $mime    = finfo_file($finfo, $_FILES['admin_photo']['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowed)) {
            $_SESSION['profile_error'] = 'Format non autorise. Utilisez JPG, PNG, GIF ou WEBP.';
            header('Location: ' . BASE_URL . 'Controller/AdminController.php?action=showProfile');
            exit;
        }

        if ($_FILES['admin_photo']['size'] > 2 * 1024 * 1024) {
            $_SESSION['profile_error'] = 'Image trop grande (max. 2 Mo).';
            header('Location: ' . BASE_URL . 'Controller/AdminController.php?action=showProfile');
            exit;
        }

        $uploadDir = __DIR__ . '/../uploads/photos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (!empty($_SESSION['admin_photo'])) {
            $oldFile = $uploadDir . $_SESSION['admin_photo'];
            if (file_exists($oldFile)) unlink($oldFile);
        }

        $ext      = strtolower(pathinfo($_FILES['admin_photo']['name'], PATHINFO_EXTENSION));
        $filename = 'admin_' . $_SESSION['admin_id'] . '_' . time() . '.' . $ext;
        $dest     = $uploadDir . $filename;

        if (!move_uploaded_file($_FILES['admin_photo']['tmp_name'], $dest)) {
            $_SESSION['profile_error'] = 'Impossible de sauvegarder l image.';
            header('Location: ' . BASE_URL . 'Controller/AdminController.php?action=showProfile');
            exit;
        }

        $stmt = $this->db->prepare('UPDATE admins SET photo = :photo WHERE id = :id');
        $stmt->execute([':photo' => $filename, ':id' => $_SESSION['admin_id']]);

        $_SESSION['admin_photo']     = $filename;
        $_SESSION['profile_success'] = 'Photo de profil mise a jour !';

        header('Location: ' . BASE_URL . 'Controller/AdminController.php?action=showProfile');
        exit;
    }

    public function changeAdminPassword(): void {
        $this->requireAdmin();

        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password']     ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $admin = $this->findAdminById($_SESSION['admin_id']);

        if (!$admin || !password_verify($current, $admin['password'])) {
            $_SESSION['admin_profile_errors']['global'] = 'Mot de passe actuel incorrect.';
            header('Location: ' . BASE_URL . 'Controller/AdminController.php?action=showProfile');
            exit;
        }

        if (strlen($new) < 8 || !preg_match('/[A-Z]/', $new) || !preg_match('/[0-9]/', $new)) {
            $_SESSION['admin_profile_errors']['global'] = 'Nouveau mot de passe invalide (min. 8 caract�res, 1 majuscule, 1 chiffre).';
            header('Location: ' . BASE_URL . 'Controller/AdminController.php?action=showProfile');
            exit;
        }

        if ($new !== $confirm) {
            $_SESSION['admin_profile_errors']['global'] = 'Les mots de passe ne correspondent pas.';
            header('Location: ' . BASE_URL . 'Controller/AdminController.php?action=showProfile');
            exit;
        }

        $stmt = $this->db->prepare("UPDATE admins SET password = :pwd WHERE id = :id");
        $stmt->execute([':pwd' => password_hash($new, PASSWORD_DEFAULT), ':id' => $_SESSION['admin_id']]);

        $_SESSION['admin_profile_success'] = 'Mot de passe mis � jour avec succ�s !';
        header('Location: ' . BASE_URL . 'Controller/AdminController.php?action=showProfile');
        exit;
    }

    // ─── Protection route admin ───────────────────────────────
    private function requireAdmin(): void {
        if (!isset($_SESSION['admin_id'])) {
            header('Location: ' . BASE_URL . 'Controller/AdminController.php?action=showLogin');
            exit;
        }
    }

    // ─── Export PDF Tableau des Passagers ────────────────────
    public function exportPassagersPDF(): void {
        $this->requireAdmin();

        $passagers = $this->getAllPassagers();
        $stats     = $this->getDashboardStats();
        $dateGen   = date('d/m/Y à H:i');

        // Génération HTML → PDF via bibliothèque native PHP (sans dépendance externe)
        // On utilise une page HTML imprimable renvoyée avec les bons headers
        header('Content-Type: text/html; charset=UTF-8');
        ?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Liste des Passagers – Eco Ride</title>
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family: Arial, Helvetica, sans-serif; font-size:11px; color:#1a1a2e; background:#fff; }
  .cover { text-align:center; padding:40px 30px 20px; border-bottom:3px solid #1976D2; margin-bottom:20px; }
  .cover h1 { font-size:22px; color:#1976D2; margin-bottom:6px; }
  .cover p  { color:#555; font-size:11px; }
  .stats-summary { display:flex; gap:16px; justify-content:center; margin:18px 0; }
  .stat-box { background:#f0f7ff; border:1px solid #bbd6f5; border-radius:8px; padding:10px 22px; text-align:center; }
  .stat-box .num { font-size:20px; font-weight:700; color:#1565C0; }
  .stat-box .lbl { font-size:9px; color:#666; margin-top:2px; }
  table { width:100%; border-collapse:collapse; margin:0 auto; }
  thead tr { background:#1976D2; color:#fff; }
  thead th { padding:7px 9px; text-align:left; font-size:10px; font-weight:600; }
  tbody tr:nth-child(even) { background:#f5f9ff; }
  tbody tr:hover { background:#e8f1fb; }
  tbody td { padding:6px 9px; border-bottom:1px solid #dde8f5; font-size:10px; }
  .badge { padding:2px 8px; border-radius:10px; font-size:9px; font-weight:700; }
  .badge-actif   { background:#dcfce7; color:#16a34a; }
  .badge-banni   { background:#fee2e2; color:#dc2626; }
  .footer { text-align:center; margin-top:24px; font-size:9px; color:#aaa; border-top:1px solid #e5e7eb; padding-top:10px; }
  @media print {
    body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .no-print { display:none; }
  }
</style>
</head>
<body>
<div class="cover">
  <h1>🚗 Eco Ride – Liste des Passagers</h1>
  <p>Généré le <?= $dateGen ?> &nbsp;|&nbsp; Panel Administrateur</p>
</div>

<div class="stats-summary">
  <div class="stat-box"><div class="num"><?= $stats['total_passagers'] ?></div><div class="lbl">Total Passagers</div></div>
  <div class="stat-box"><div class="num" style="color:#16a34a"><?= $stats['active_passagers'] ?></div><div class="lbl">Actifs</div></div>
  <div class="stat-box"><div class="num" style="color:#dc2626"><?= $stats['inactive_passagers'] ?></div><div class="lbl">Bannis</div></div>
  <div class="stat-box"><div class="num" style="color:#1976D2"><?= $stats['total_admins'] ?></div><div class="lbl">Admins</div></div>
</div>

<table>
  <thead>
    <tr>
      <th>#ID</th>
      <th>Nom complet</th>
      <th>Email</th>
      <th>Téléphone</th>
      <th>Statut</th>
      <th>Date d'inscription</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($passagers as $p): ?>
    <tr>
      <td><?= (int)$p['id'] ?></td>
      <td><?= htmlspecialchars($p['prenom'] . ' ' . $p['nom']) ?></td>
      <td><?= htmlspecialchars($p['email']) ?></td>
      <td><?= htmlspecialchars($p['telephone'] ?? '–') ?></td>
      <td><span class="badge <?= $p['statut'] === 'actif' ? 'badge-actif' : 'badge-banni' ?>"><?= $p['statut'] === 'actif' ? 'Actif' : 'Banni' ?></span></td>
      <td><?= date('d/m/Y', strtotime($p['created_at'])) ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<div class="footer">Eco Ride by Echo Group © 2025 – Panel Administrateur – Page générée automatiquement</div>

<div class="no-print" style="text-align:center;margin:24px 0;">
  <button onclick="window.print()" style="background:#1976D2;color:#fff;border:none;padding:10px 28px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">
    🖨️ Imprimer / Sauvegarder en PDF
  </button>
  <button onclick="window.close()" style="background:#6b7280;color:#fff;border:none;padding:10px 20px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;margin-left:10px;">
    Fermer
  </button>
  <p style="color:#888;font-size:11px;margin-top:10px;">Astuce : Sélectionner "Enregistrer en PDF" dans les options d'impression du navigateur.</p>
</div>

<script>
  // Auto-ouvrir la boîte d'impression si pas en mode dev
  // window.onload = function(){ window.print(); };
</script>
</body>
</html>
        <?php
        exit;
    }

    // ─── Face Verification admin : retourne la photo en base64 ──────────────
    public function getFaceImage(): void {
        header('Content-Type: application/json');

        $email = trim($_POST['email'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'image' => null]);
            exit;
        }

        $row = $this->findAdminByEmail($email);

        if (!$row || empty($row['photo'])) {
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
        $action = $_GET['action'] ?? 'showLogin';

        switch ($action) {
            case 'showLogin':          $this->showLogin();          break;
            case 'login':              $this->login();              break;
            case 'logout':             $this->logout();             break;
            case 'dashboard':          $this->dashboard();             break;
            case 'showProfile':        $this->showProfile();           break;
            case 'updateAdminProfile': $this->updateAdminProfile();    break;
            case 'changeAdminPassword': $this->changeAdminPassword();  break;
            case 'listUsers':          $this->listUsers();          break;
            case 'showAddUser':        $this->showAddUser();        break;
            case 'addUser':            $this->addUser();            break;
            case 'showEditUser':       $this->showEditUser();       break;
            case 'editUser':           $this->editUser();           break;
            case 'deleteUser':         $this->deleteUser();         break;
            case 'getPassager':        $this->getPassager();        break;
            case 'showPassagerDetailsPage': $this->showPassagerDetailsPage(); break;
            case 'showEditPassager':       $this->showEditPassager();        break;
            case 'getPassagerDetails': $this->getPassagerDetails(); break;
            case 'editPassager':       $this->editPassager();       break;
            case 'banPassager':        $this->banPassager();        break;
            case 'unbanPassager':      $this->unbanPassager();      break;
            case 'uploadAdminPhoto':   $this->uploadAdminPhoto();   break;
            case 'exportPassagersPDF': $this->exportPassagersPDF(); break;
            case 'getFaceImage':       $this->getFaceImage();       break;
            default:
                http_response_code(404);
                echo '<h1>404 - Action non trouvée</h1>';
        }
    }
}

if (basename($_SERVER['SCRIPT_FILENAME']) === 'AdminController.php') {
    $controller = new AdminController();
    $controller->handleRequest();
}
