<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

$host = 'localhost';
$dbname = 'ecoride';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Création table utilisateurs
$pdo->exec("CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL
)");

// Création table reclamations
$pdo->exec("CREATE TABLE IF NOT EXISTS reclamations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT UNSIGNED NOT NULL,
    titre VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    categorie ENUM('technique','paiement','securite','autre') NOT NULL,
    priorite ENUM('faible','moyenne','elevee') NOT NULL DEFAULT 'moyenne',
    statut ENUM('en_attente','en_cours','resolue','rejetee') NOT NULL DEFAULT 'en_attente',
    date_creation DATE NOT NULL,
    date_traitement DATE NULL,
    date_reponse DATE NULL,
    reponse_admin TEXT NULL,
    piece_jointe VARCHAR(255) NULL,
    note_satisfaction INT NULL,
    score_urgence INT NULL DEFAULT 0,
    historique_statut TEXT NULL
)");

// Insertion utilisateurs test
$stmt = $pdo->query("SELECT COUNT(*) FROM utilisateurs");
if ($stmt->fetchColumn() == 0) {
    $pdo->exec("INSERT INTO utilisateurs (id, nom, email) VALUES 
        (1, 'Jean Dupont', 'jean.dupont@email.com'),
        (2, 'Sara Benali', 'sara.benali@email.com')");
}

// Insertion réclamations test
$stmt = $pdo->query("SELECT COUNT(*) FROM reclamations");
if ($stmt->fetchColumn() == 0) {
    $pdo->exec("INSERT INTO reclamations (id, utilisateur_id, titre, description, categorie, priorite, statut, date_creation, note_satisfaction, score_urgence) VALUES 
        (1, 1, 'Problème de paiement', 'Mon paiement a été débité deux fois', 'paiement', 'elevee', 'en_attente', '2025-06-01', NULL, 90),
        (2, 1, 'Application plante', 'L\\'application crash au démarrage', 'technique', 'moyenne', 'resolue', '2025-05-28', 5, 60),
        (3, 2, 'Conducteur absent', 'Le conducteur n\\'est pas venu', 'autre', 'elevee', 'en_cours', '2025-05-20', NULL, 95)");
}

// ═══════════════════════════════════════════════════════
// 🔒 SÉCURITÉ ADMIN - MOT DE PASSE
// ═══════════════════════════════════════════════════════
$admin_password_hash = password_hash('admin123', PASSWORD_DEFAULT);

if (isset($_POST['admin_password'])) {
    if (password_verify($_POST['admin_password'], $admin_password_hash)) {
        $_SESSION['admin_authenticated'] = true;
        $_SESSION['role'] = 'admin';
        $_SESSION['user_id'] = 1;
        unset($_SESSION['pending_admin']);
    } else {
        $login_error = 'Mot de passe incorrect !';
    }
}

if (isset($_GET['switch']) && $_GET['switch'] == 'admin') {
    if (!isset($_SESSION['admin_authenticated']) || $_SESSION['admin_authenticated'] !== true) {
        $_SESSION['pending_admin'] = true;
        header('Location: index.php');
        exit;
    }
}

if (isset($_GET['switch']) && $_GET['switch'] == 'user') {
    $_SESSION['role'] = 'user';
    $_SESSION['admin_authenticated'] = false;
    header('Location: index.php');
    exit;
}

$show_admin_login = isset($_SESSION['pending_admin']) && $_SESSION['pending_admin'] === true;

if (!isset($_SESSION['role'])) {
    $_SESSION['role'] = 'user';
    $_SESSION['user_id'] = 1;
    $_SESSION['admin_authenticated'] = false;
}

$isAdmin = ($_SESSION['role'] === 'admin');
$currentUserId = $_SESSION['user_id'];

if ($isAdmin && $show_admin_login) {
    unset($_SESSION['pending_admin']);
    $show_admin_login = false;
}

// TRAITEMENT POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['admin_password'])) {
    $action = $_POST['action'] ?? '';
    
    if (!$isAdmin && in_array($action, ['reclamation_update', 'reclamation_delete', 'reclamation_statut'])) {
        $_SESSION['err'] = 'Action non autorisée.';
        header('Location: index.php');
        exit;
    }
    
    if ($action === 'create_reclamation') {
        $uid = $isAdmin ? ($_POST['utilisateur_id'] ?? $currentUserId) : $currentUserId;
        $today = date('Y-m-d');
        $sql = "INSERT INTO reclamations (utilisateur_id, titre, description, categorie, priorite, statut, date_creation, note_satisfaction, score_urgence) 
                VALUES (:uid, :titre, :desc, :cat, :prio, 'en_attente', :date_creation, :note, :score)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':uid' => $uid,
            ':titre' => $_POST['titre'],
            ':desc' => $_POST['description'],
            ':cat' => $_POST['categorie'],
            ':prio' => $_POST['priorite'],
            ':date_creation' => $today,
            ':note' => $isAdmin && !empty($_POST['note_satisfaction']) ? $_POST['note_satisfaction'] : null,
            ':score' => $isAdmin ? ($_POST['score_urgence'] ?? 0) : 0
        ]);
        $_SESSION['msg'] = '✅ Réclamation ajoutée avec succès !';
        header('Location: index.php');
        exit;
    }
    
    if ($action === 'reclamation_update' && $isAdmin) {
        $sql = "UPDATE reclamations SET 
                    titre = :titre,
                    description = :desc,
                    categorie = :cat,
                    priorite = :prio,
                    statut = :statut,
                    note_satisfaction = :note,
                    score_urgence = :score
                WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id' => $_POST['id'],
            ':titre' => $_POST['titre'],
            ':desc' => $_POST['description'],
            ':cat' => $_POST['categorie'],
            ':prio' => $_POST['priorite'],
            ':statut' => $_POST['statut'],
            ':note' => !empty($_POST['note_satisfaction']) ? $_POST['note_satisfaction'] : null,
            ':score' => $_POST['score_urgence'] ?? 0
        ]);
        $_SESSION['msg'] = '✅ Réclamation modifiée !';
        header('Location: index.php');
        exit;
    }
    
    if ($action === 'reclamation_delete' && $isAdmin) {
        $stmt = $pdo->prepare("DELETE FROM reclamations WHERE id = :id");
        $stmt->execute([':id' => $_POST['id']]);
        $_SESSION['msg'] = '✅ Réclamation supprimée !';
        header('Location: index.php');
        exit;
    }
    
    if ($action === 'reclamation_statut' && $isAdmin) {
        $stmt = $pdo->prepare("UPDATE reclamations SET statut = :statut WHERE id = :id");
        $stmt->execute([':id' => $_POST['id'], ':statut' => $_POST['statut']]);
        $_SESSION['msg'] = '✅ Statut mis à jour !';
        header('Location: index.php');
        exit;
    }
}

// Récupération des données
if ($isAdmin) {
    $stmt = $pdo->query("SELECT r.*, u.nom as utilisateur_nom, u.email as utilisateur_email 
                         FROM reclamations r 
                         LEFT JOIN utilisateurs u ON r.utilisateur_id = u.id 
                         ORDER BY r.date_creation DESC");
    $reclamations = $stmt->fetchAll();
    
    $stmt = $pdo->query("SELECT 
        COUNT(*) as total, 
        SUM(statut='en_attente') as en_attente, 
        SUM(statut='en_cours') as en_cours, 
        SUM(statut='resolue') as resolue, 
        SUM(statut='rejetee') as rejetee,
        SUM(priorite='elevee') as priorite_elevee
        FROM reclamations");
    $stats = $stmt->fetch();
} else {
    $stmt = $pdo->prepare("SELECT r.*, u.nom as utilisateur_nom, u.email as utilisateur_email 
                           FROM reclamations r 
                           LEFT JOIN utilisateurs u ON r.utilisateur_id = u.id 
                           WHERE r.utilisateur_id = :uid 
                           ORDER BY r.date_creation DESC");
    $stmt->execute([':uid' => $currentUserId]);
    $reclamations = $stmt->fetchAll();
    $stats = null;
}

$msg = $_SESSION['msg'] ?? null;
$err = $_SESSION['err'] ?? null;
unset($_SESSION['msg'], $_SESSION['err']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isAdmin ? 'Admin - Réclamations' : 'Mes Réclamations' ?> | EcoRide</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --bleu-fonce: #1976D2;
            --bleu-clair: #61B3FA;
            --blanc: #F4F5F7;
            --gris: #A7A9AC;
            --dark-bg: #0A1628;
        }

        body {
            font-family: 'Poppins', 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, var(--dark-bg) 0%, #0D1F3A 100%);
            color: #FFFFFF;
            min-height: 100vh;
        }

        .admin-container { display: flex; min-height: 100vh; }

        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, var(--bleu-fonce) 0%, #0F3B6E 100%);
            backdrop-filter: blur(12px);
            padding: 2rem 1rem;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 4px 0 20px rgba(0,0,0,0.3);
            z-index: 100;
        }
        .sidebar .logo {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--bleu-clair);
            text-align: center;
        }
        .sidebar .logo i { font-size: 48px; color: var(--bleu-clair); margin-bottom: 10px; }
        .sidebar .logo h2 {
            background: linear-gradient(135deg, #FFF, var(--bleu-clair));
            -webkit-background-clip: text; background-clip: text; color: transparent;
            font-size: 1.5rem;
        }
        .sidebar .logo p { color: var(--gris); font-size: 0.8rem; }
        .sidebar nav ul { list-style: none; }
        .sidebar nav ul li { margin-bottom: 0.5rem; }
        .sidebar nav ul li a {
            display: flex; align-items: center; gap: 12px;
            padding: 0.8rem 1rem; color: #FFF; text-decoration: none;
            border-radius: 12px; transition: all 0.3s;
        }
        .sidebar nav ul li a i { width: 24px; color: var(--bleu-clair); }
        .sidebar nav ul li a:hover,
        .sidebar nav ul li a.active {
            background: rgba(255,255,255,0.15);
            border-left: 3px solid var(--bleu-clair);
        }
        .main-content { flex: 1; margin-left: 280px; padding: 2rem; }

        .top-bar {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 2rem; padding-bottom: 1rem;
            border-bottom: 2px solid rgba(97,179,250,0.3);
        }
        .top-bar h1 { font-size: 1.8rem; display: flex; align-items: center; gap: 10px; color: var(--blanc); }
        .top-bar h1 i { color: var(--bleu-clair); }
        .admin-badge {
            background: rgba(25,118,210,0.3); border: 1px solid var(--bleu-clair);
            color: var(--bleu-clair); padding: 0.5rem 1.2rem; border-radius: 25px;
            display: flex; align-items: center; gap: 8px;
        }
        .btn-switch {
            background: #f39c12; padding: 0.5rem 1.2rem; border-radius: 25px;
            color: #fff; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;
            transition: 0.3s;
        }
        .btn-switch:hover { background: #e67e22; transform: translateY(-2px); }

        .stats-row {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1.5rem; margin-bottom: 2rem;
        }
        .stat-box {
            background: rgba(255,255,255,0.08); backdrop-filter: blur(10px);
            border-radius: 20px; padding: 1.5rem; text-align: center;
            transition: all 0.3s; border: 1px solid rgba(97,179,250,0.2);
            cursor: pointer;
        }
        .stat-box:hover { transform: translateY(-5px); border-color: var(--bleu-clair); box-shadow: 0 10px 30px rgba(25,118,210,0.2); }
        .stat-box i { font-size: 2rem; color: var(--bleu-clair); margin-bottom: 0.5rem; }
        .stat-box .number {
            font-size: 2.2rem; font-weight: bold;
            background: linear-gradient(135deg, var(--bleu-clair), #FFF);
            -webkit-background-clip: text; background-clip: text; color: transparent;
        }
        .stat-box .label { color: var(--gris); margin-top: 0.3rem; font-size: 0.85rem; }

        .actions-bar {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;
        }
        .filters { display: flex; gap: 0.8rem; flex-wrap: wrap; align-items: center; }

        .search-box { position: relative; display: flex; align-items: center; }
        .search-icon-btn {
            position: absolute; left: 0; width: 46px; height: 46px;
            background: linear-gradient(135deg, var(--bleu-fonce), var(--bleu-clair));
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            cursor: pointer; box-shadow: 0 4px 15px rgba(25,118,210,0.45);
            transition: all 0.3s;
        }
        .search-icon-btn:hover { transform: rotate(15deg) scale(1.1); }
        .search-icon-btn i { color: #fff; }
        .search-box input {
            padding: 0.75rem 1.1rem 0.75rem 3.4rem;
            border-radius: 30px; border: 1.5px solid rgba(97,179,250,0.35);
            background: rgba(255,255,255,0.09); color: white; width: 270px;
            transition: all 0.35s;
        }
        .search-box input:focus { outline: none; border-color: var(--bleu-clair); width: 310px; background: rgba(255,255,255,0.14); }

        .filter-wrap { position: relative; display: flex; align-items: center; }
        .filter-wrap .filter-icon {
            position: absolute; left: 12px;
            color: var(--bleu-clair); font-size: 0.8rem;
            pointer-events: none;
        }
        .filter-select {
            padding: 0.7rem 1rem 0.7rem 2.2rem; border-radius: 20px;
            border: 1.5px solid rgba(97,179,250,0.3);
            background: rgba(255,255,255,0.08); color: white;
            cursor: pointer;
        }
        .filter-select:hover { border-color: var(--bleu-clair); background: rgba(255,255,255,0.12); }

        .btn-add-wrap { display: flex; align-items: center; gap: 10px; }
        .btn-add-circle {
            width: 46px; height: 46px;
            background: linear-gradient(135deg, var(--bleu-fonce), var(--bleu-clair));
            border: none; border-radius: 50%; cursor: pointer;
            transition: all 0.3s; box-shadow: 0 4px 15px rgba(25,118,210,0.45);
        }
        .btn-add-circle:hover { transform: rotate(90deg) scale(1.12); }
        .btn-add-circle i { color: #fff; font-size: 1.1rem; }
        .btn-add-label {
            background: linear-gradient(135deg, var(--bleu-fonce), var(--bleu-clair));
            color: white; padding: 0.72rem 1.5rem; border: none; border-radius: 30px;
            cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 8px;
            transition: all 0.3s;
        }
        .btn-add-label:hover { transform: translateY(-2px); box-shadow: 0 6px 22px rgba(25,118,210,0.5); }

        .table-container {
            background: rgba(255,255,255,0.05); backdrop-filter: blur(10px);
            border-radius: 20px; overflow-x: auto;
            border: 1px solid rgba(97,179,250,0.2);
        }
        .data-table { width: 100%; border-collapse: collapse; min-width: 1200px; }
        .data-table th, .data-table td { padding: 0.9rem 1rem; text-align: left; }
        .data-table th {
            background: rgba(25,118,210,0.3); color: var(--bleu-clair);
            font-weight: 600; border-bottom: 2px solid var(--bleu-clair);
            white-space: nowrap;
        }
        .data-table tr { border-bottom: 1px solid rgba(97,179,250,0.1); transition: all 0.3s; }
        .data-table tr:hover { background: rgba(25,118,210,0.1); }

        .badge {
            padding: 0.3rem 0.75rem; border-radius: 20px;
            font-size: 0.78rem; font-weight: 600; display: inline-block;
            white-space: nowrap;
        }
        .badge-en_attente { background: rgba(241,196,15,0.15); color: #f1c40f; border: 1px solid #f1c40f; }
        .badge-en_cours { background: rgba(52,152,219,0.15); color: #3498db; border: 1px solid #3498db; }
        .badge-resolue { background: rgba(46,204,113,0.15); color: #2ecc71; border: 1px solid #2ecc71; }
        .badge-rejetee { background: rgba(231,76,60,0.15); color: #e74c3c; border: 1px solid #e74c3c; }
        .badge-faible { background: rgba(149,165,166,0.15); color: #95a5a6; border: 1px solid #95a5a6; }
        .badge-moyenne { background: rgba(230,126,34,0.15); color: #e67e22; border: 1px solid #e67e22; }
        .badge-elevee { background: rgba(231,76,60,0.15); color: #e74c3c; border: 1px solid #e74c3c; }
        .badge-technique { background: rgba(97,179,250,0.15); color: var(--bleu-clair); border: 1px solid var(--bleu-clair); }
        .badge-paiement { background: rgba(155,89,182,0.15); color: #9b59b6; border: 1px solid #9b59b6; }
        .badge-securite { background: rgba(231,76,60,0.15); color: #e74c3c; border: 1px solid #e74c3c; }
        .badge-autre { background: rgba(149,165,166,0.15); color: #95a5a6; border: 1px solid #95a5a6; }

        .score-bar {
            display: flex; align-items: center; gap: 6px;
        }
        .score-bar-progress {
            width: 60px; height: 6px; background: rgba(255,255,255,0.1); border-radius: 3px; overflow: hidden;
        }
        .score-bar-fill {
            height: 100%; border-radius: 3px;
        }
        .score-text { font-size: 0.75rem; font-weight: bold; }

        .statut-select {
            background: rgba(25,118,210,0.2); border: 1px solid var(--bleu-clair);
            color: white; padding: 0.3rem 0.6rem; border-radius: 15px; cursor: pointer;
            font-size: 0.8rem;
        }
        .action-buttons { display: flex; gap: 0.5rem; }
        .btn-icon {
            background: transparent; border: none; padding: 0.45rem 0.6rem;
            border-radius: 8px; cursor: pointer; transition: all 0.3s;
        }
        .btn-icon.view { color: #2ecc71; }
        .btn-icon.view:hover { background: rgba(46,204,113,0.2); transform: scale(1.1); }
        .btn-icon.edit { color: var(--bleu-clair); }
        .btn-icon.edit:hover { background: rgba(97,179,250,0.2); transform: scale(1.1); }
        .btn-icon.delete { color: #e74c3c; }
        .btn-icon.delete:hover { background: rgba(231,76,60,0.2); transform: scale(1.1); }

        .user-info { display: flex; flex-direction: column; }
        .user-name { font-weight: 600; }
        .user-email { font-size: 0.75rem; color: var(--gris); }

        .modal {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.8); backdrop-filter: blur(8px);
            z-index: 1000; justify-content: center; align-items: center;
        }
        .modal-content {
            background: linear-gradient(135deg, #1A2A3A, #0F1A2A);
            padding: 2rem; border-radius: 24px; width: 92%; max-width: 600px;
            border: 1px solid var(--bleu-clair);
            animation: modalSlideIn 0.3s ease;
            max-height: 90vh; overflow-y: auto;
        }
        @keyframes modalSlideIn {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .modal-content h2 { color: var(--bleu-clair); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.3rem; color: var(--gris); font-size: 0.85rem; display: flex; align-items: center; gap: 6px; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; padding: 0.7rem 1rem; border-radius: 12px;
            border: 1px solid rgba(97,179,250,0.3);
            background: rgba(255,255,255,0.08); color: white;
            font-family: inherit;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none; border-color: var(--bleu-clair);
            background: rgba(255,255,255,0.12);
        }
        .modal-buttons { display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1.5rem; }
        .btn-save {
            background: linear-gradient(135deg, var(--bleu-fonce), var(--bleu-clair));
            color: white; padding: 0.7rem 1.8rem; border: none; border-radius: 30px;
            cursor: pointer; font-weight: 600; transition: all 0.3s;
        }
        .btn-save:hover { transform: scale(1.02); box-shadow: 0 5px 15px rgba(25,118,210,0.4); }
        .btn-cancel {
            background: rgba(231,76,60,0.2); color: #e74c3c; padding: 0.7rem 1.5rem;
            border: 1px solid #e74c3c; border-radius: 30px; cursor: pointer;
            transition: all 0.3s;
        }
        .btn-cancel:hover { background: rgba(231,76,60,0.35); }

        .toast {
            position: fixed; bottom: 2rem; right: 2rem;
            background: linear-gradient(135deg, var(--bleu-fonce), #0F3B6E);
            border: 1px solid var(--bleu-clair); color: white;
            padding: 1rem 1.5rem; border-radius: 15px;
            display: flex; align-items: center; gap: 10px;
            animation: toastIn 0.3s ease; z-index: 9999;
        }
        @keyframes toastIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        .alert-success {
            background: rgba(46,204,113,0.2); border: 1px solid #2ecc71;
            padding: 0.8rem; border-radius: 12px; margin-bottom: 1rem;
            color: #2ecc71;
        }
        .alert-error {
            background: rgba(231,76,60,0.2); border: 1px solid #e74c3c;
            padding: 0.8rem; border-radius: 12px; margin-bottom: 1rem;
            color: #e74c3c;
        }

        footer { text-align: center; padding: 2rem 0 1rem; color: var(--gris); font-size: 0.85rem; }
        footer i { color: var(--bleu-clair); }
        .empty-state { text-align: center; padding: 3rem; color: var(--gris); }
        .empty-state i { font-size: 3rem; margin-bottom: 1rem; }

        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); }
        ::-webkit-scrollbar-thumb { background: var(--bleu-fonce); border-radius: 10px; }
    </style>
</head>
<body>
<div class="admin-container">

    <?php if($show_admin_login): ?>
    <div class="modal" style="display:flex; z-index:10000;">
        <div class="modal-content" style="max-width:400px;">
            <h2><i class="fas fa-shield-alt"></i> Accès Administrateur</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Mot de passe administrateur</label>
                    <input type="password" name="admin_password" placeholder="Entrez le mot de passe" required autofocus>
                    <?php if(isset($login_error)): ?>
                    <p style="color:#e74c3c; margin-top:5px;"><?= $login_error ?></p>
                    <?php endif; ?>
                </div>
                <div class="modal-buttons">
                    <a href="?switch=user" class="btn-cancel" style="text-decoration:none;">Annuler</a>
                    <button type="submit" class="btn-save"><i class="fas fa-unlock-alt"></i> Se connecter</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- ══════════════ SIDEBAR ══════════════ -->
    <aside class="sidebar">
        <div class="logo">
            <i class="fas fa-leaf"></i>
            <h2>EcoRide</h2>
            <p>Panneau d'administration</p>
        </div>
        <nav>
            <ul>
                <li><a href="#"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a></li>
                <li><a href="#"><i class="fas fa-users"></i> Utilisateurs</a></li>
                <li><a href="#"><i class="fas fa-car-side"></i> Véhicules</a></li>
                <li><a href="#"><i class="fas fa-route"></i> Trajets</a></li>
                <li><a href="#" class="active"><i class="fas fa-exclamation-circle"></i> Réclamations</a></li>
                <li><a href="#"><i class="fas fa-search"></i> Lost & Found</a></li>
                <li><a href="#"><i class="fas fa-calendar-alt"></i> Événements</a></li>
                <li><a href="#"><i class="fas fa-cog"></i> Paramètres</a></li>
                <li><a href="#"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
            </ul>
        </nav>
    </aside>

    <!-- ══════════════ MAIN CONTENT ══════════════ -->
    <main class="main-content">

        <div class="top-bar">
            <h1><i class="fas fa-exclamation-circle"></i> Gestion des Réclamations</h1>
            <div style="display: flex; gap: 1rem;">
                <?php if($isAdmin): ?>
                <a href="?switch=user" class="btn-switch" style="background:#e74c3c;">
                    <i class="fas fa-sign-out-alt"></i> Quitter le mode Admin
                </a>
                <?php else: ?>
                <a href="?switch=admin" class="btn-switch">
                    <i class="fas fa-shield-alt"></i> Mode Admin
                </a>
                <?php endif; ?>
                <div class="admin-badge">
                    <i class="fas <?= $isAdmin ? 'fa-shield-alt' : 'fa-user' ?>"></i> 
                    <?= $isAdmin ? 'Administrateur' : 'Utilisateur' ?>
                </div>
            </div>
        </div>

        <?php if($msg): ?>
        <div class="alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>
        <?php if($err): ?>
        <div class="alert-error"><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($err) ?></div>
        <?php endif; ?>

        <?php if($isAdmin && isset($stats)): ?>
        <div class="stats-row">
            <div class="stat-box"><i class="fas fa-clipboard-list"></i><div class="number"><?= $stats['total'] ?? 0 ?></div><div class="label">Total réclamations</div></div>
            <div class="stat-box"><i class="fas fa-clock"></i><div class="number"><?= $stats['en_attente'] ?? 0 ?></div><div class="label">En attente</div></div>
            <div class="stat-box"><i class="fas fa-spinner"></i><div class="number"><?= $stats['en_cours'] ?? 0 ?></div><div class="label">En cours</div></div>
            <div class="stat-box"><i class="fas fa-check-circle"></i><div class="number"><?= $stats['resolue'] ?? 0 ?></div><div class="label">Résolues</div></div>
            <div class="stat-box"><i class="fas fa-times-circle"></i><div class="number"><?= $stats['rejetee'] ?? 0 ?></div><div class="label">Rejetées</div></div>
            <div class="stat-box"><i class="fas fa-fire"></i><div class="number"><?= $stats['priorite_elevee'] ?? 0 ?></div><div class="label">Priorité élevée</div></div>
        </div>
        <?php endif; ?>

        <div class="actions-bar">
            <div class="filters">
                <div class="search-box">
                    <div class="search-icon-btn" onclick="document.getElementById('searchInput').focus()">
                        <i class="fas fa-search"></i>
                    </div>
                    <input type="text" id="searchInput" placeholder="Rechercher une réclamation...">
                </div>

                <?php if($isAdmin): ?>
                <div class="filter-wrap">
                    <i class="fas fa-chart-line filter-icon"></i>
                    <select class="filter-select" id="filterStatut">
                        <option value="">Tous statuts</option>
                        <option value="en_attente">En attente</option>
                        <option value="en_cours">En cours</option>
                        <option value="resolue">Résolue</option>
                        <option value="rejetee">Rejetée</option>
                    </select>
                </div>
                <div class="filter-wrap">
                    <i class="fas fa-fire filter-icon"></i>
                    <select class="filter-select" id="filterPriorite">
                        <option value="">Toutes priorités</option>
                        <option value="faible">Faible</option>
                        <option value="moyenne">Moyenne</option>
                        <option value="elevee">Élevée</option>
                    </select>
                </div>
                <div class="filter-wrap">
                    <i class="fas fa-tag filter-icon"></i>
                    <select class="filter-select" id="filterCategorie">
                        <option value="">Toutes catégories</option>
                        <option value="technique">Technique</option>
                        <option value="paiement">Paiement</option>
                        <option value="securite">Sécurité</option>
                        <option value="autre">Autre</option>
                    </select>
                </div>
                <?php endif; ?>
            </div>

            <div class="btn-add-wrap">
                <button class="btn-add-circle" onclick="openAddModal()" title="Nouvelle réclamation">
                    <i class="fas fa-plus"></i>
                </button>
                <button class="btn-add-label" onclick="openAddModal()">
                    <i class="fas fa-plus-circle"></i> Nouvelle réclamation
                </button>
            </div>
        </div>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag"></i> ID</th>
                        <th><i class="fas fa-heading"></i> Titre</th>
                        <th><i class="fas fa-user"></i> Utilisateur</th>
                        <th><i class="fas fa-tag"></i> Catégorie</th>
                        <th><i class="fas fa-calendar"></i> Date création</th>
                        <th><i class="fas fa-fire"></i> Priorité</th>
                        <th><i class="fas fa-brain"></i> Score</th>
                        <th><i class="fas fa-chart-line"></i> Statut</th>
                        <th><i class="fas fa-star"></i> Note</th>
                        <th><i class="fas fa-cog"></i> Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php if(empty($reclamations)): ?>
                    <tr><td colspan="10"><div class="empty-state"><i class="fas fa-inbox"></i><p>Aucune réclamation trouvée</p></div></td></tr>
                    <?php else: foreach($reclamations as $r): ?>
                    <tr data-id="<?= $r['id'] ?>" data-statut="<?= $r['statut'] ?>" data-priorite="<?= $r['priorite'] ?>" data-categorie="<?= $r['categorie'] ?>">
                        <td><code style="color:var(--bleu-clair)">#<?= $r['id'] ?></code></td>
                        <td><strong><?= htmlspecialchars($r['titre']) ?></strong></td>
                        <td>
                            <div class="user-info">
                                <span class="user-name"><?= htmlspecialchars($r['utilisateur_nom'] ?? 'ID:'.$r['utilisateur_id']) ?></span>
                                <span class="user-email"><?= htmlspecialchars($r['utilisateur_email'] ?? '') ?></span>
                            </div>
                        </td>
                        <td><span class="badge badge-<?= $r['categorie'] ?>"><?= $r['categorie'] ?></span></td>
                        <td><?= date('d/m/Y', strtotime($r['date_creation'])) ?></td>
                        <td><span class="badge badge-<?= $r['priorite'] ?>"><?= $r['priorite'] ?></span></td>
                        <td>
                            <?php 
                            $score = $r['score_urgence'] ?? 0;
                            $color = $score >= 80 ? '#e74c3c' : ($score >= 50 ? '#f1c40f' : '#2ecc71');
                            ?>
                            <div class="score-bar">
                                <div class="score-bar-progress">
                                    <div class="score-bar-fill" style="width: <?= $score ?>%; background: <?= $color ?>;"></div>
                                </div>
                                <span class="score-text" style="color: <?= $color ?>;"><?= $score ?></span>
                            </div>
                        </td>
                        <td>
                            <?php if($isAdmin): ?>
                            <form method="POST" style="margin:0" class="statut-form">
                                <input type="hidden" name="action" value="reclamation_statut">
                                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                <select name="statut" class="statut-select" onchange="this.form.submit()">
                                    <option value="en_attente" <?= $r['statut']=='en_attente'?'selected':'' ?>>En attente</option>
                                    <option value="en_cours" <?= $r['statut']=='en_cours'?'selected':'' ?>>En cours</option>
                                    <option value="resolue" <?= $r['statut']=='resolue'?'selected':'' ?>>Résolue</option>
                                    <option value="rejetee" <?= $r['statut']=='rejetee'?'selected':'' ?>>Rejetée</option>
                                </select>
                            </form>
                            <?php else: ?>
                            <span class="badge badge-<?= $r['statut'] ?>"><?= str_replace('_',' ',$r['statut']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php 
                            $note = $r['note_satisfaction'] ?? null;
                            if($note && is_numeric($note) && $note >= 1 && $note <= 5): 
                                echo $note . '/5';
                            else: 
                                echo '—';
                            endif; 
                            ?>
                        </td>
                        <td class="action-buttons">
                            <button class="btn-icon view" onclick="viewDetails(<?= $r['id'] ?>)" title="Voir détail"><i class="fas fa-eye"></i></button>
                            <?php if($isAdmin): ?>
                            <button class="btn-icon edit" onclick='openEditModal(<?= json_encode($r) ?> )' title="Modifier"><i class="fas fa-edit"></i></button>
                            <form method="POST" style="margin:0;display:inline-block" onsubmit="return confirm('Supprimer cette réclamation ?')">
                                <input type="hidden" name="action" value="reclamation_delete">
                                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                <button type="submit" class="btn-icon delete" title="Supprimer"><i class="fas fa-trash"></i></button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

        <footer>
            <p><i class="fas fa-leaf"></i> EcoRide - Covoiturage intelligent et écologique | Moins de CO₂, plus de partage</p>
        </footer>
    </main>
</div>

<!-- MODAL AJOUT/MODIFIER -->
<div id="reclamationModal" class="modal">
    <div class="modal-content">
        <h2 id="modalTitle"><i class="fas fa-plus-circle"></i> Nouvelle réclamation</h2>
        <form method="POST" id="reclamationForm">
            <input type="hidden" name="action" id="formAction" value="create_reclamation">
            <input type="hidden" name="id" id="editId">
            
            <div class="form-group">
                <label><i class="fas fa-heading"></i> Titre *</label>
                <input type="text" name="titre" id="titre" required placeholder="Ex: Problème de connexion">
            </div>
            <div class="form-group">
                <label><i class="fas fa-align-left"></i> Description *</label>
                <textarea name="description" id="description" rows="3" required placeholder="Décrivez votre problème en détail..."></textarea>
            </div>
            
            <?php if($isAdmin): ?>
            <div class="form-group">
                <label><i class="fas fa-id-badge"></i> ID Utilisateur</label>
                <input type="number" name="utilisateur_id" id="utilisateur_id" value="1">
            </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label><i class="fas fa-tag"></i> Catégorie *</label>
                <select name="categorie" id="categorie" required>
                    <option value="technique">Technique</option>
                    <option value="paiement">Paiement</option>
                    <option value="securite">Sécurité</option>
                    <option value="autre">Autre</option>
                </select>
            </div>
            <div class="form-group">
                <label><i class="fas fa-fire"></i> Priorité *</label>
                <select name="priorite" id="priorite" required>
                    <option value="faible">Faible</option>
                    <option value="moyenne">Moyenne</option>
                    <option value="elevee">Élevée</option>
                </select>
            </div>
            
            <?php if($isAdmin): ?>
            <div class="form-group">
                <label><i class="fas fa-chart-line"></i> Statut</label>
                <select name="statut" id="statut">
                    <option value="en_attente">En attente</option>
                    <option value="en_cours">En cours</option>
                    <option value="resolue">Résolue</option>
                    <option value="rejetee">Rejetée</option>
                </select>
            </div>
            <div class="form-group">
                <label><i class="fas fa-star"></i> Note satisfaction (1-5)</label>
                <input type="number" name="note_satisfaction" id="note_satisfaction" min="1" max="5" placeholder="1 à 5">
            </div>
            <div class="form-group">
                <label><i class="fas fa-brain"></i> Score urgence (0-100)</label>
                <input type="number" name="score_urgence" id="score_urgence" min="0" max="100" placeholder="0-100">
            </div>
            <div class="form-group">
                <label><i class="fas fa-calendar-alt"></i> Date création</label>
                <input type="date" name="date_creation" id="date_creation" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="form-group">
                <label><i class="fas fa-reply"></i> Réponse admin</label>
                <textarea name="reponse_admin" id="reponse_admin" rows="2" placeholder="Votre réponse à l'utilisateur..."></textarea>
            </div>
            <?php else: ?>
            <input type="hidden" name="statut" value="en_attente">
            <input type="hidden" name="note_satisfaction" value="">
            <input type="hidden" name="score_urgence" value="0">
            <input type="hidden" name="date_creation" value="<?= date('Y-m-d') ?>">
            <?php endif; ?>
            
            <div class="modal-buttons">
                <button type="button" class="btn-cancel" onclick="closeModal()"><i class="fas fa-times"></i> Annuler</button>
                <button type="submit" class="btn-save"><i class="fas fa-save"></i> Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL DETAIL -->
<div id="detailModal" class="modal">
    <div class="modal-content">
        <h2><i class="fas fa-eye"></i> Détail de la réclamation</h2>
        <div id="detailContent"></div>
        <div class="modal-buttons">
            <button class="btn-cancel" onclick="closeDetailModal()"><i class="fas fa-times"></i> Fermer</button>
        </div>
    </div>
</div>

<script>
function showToast(message) {
    let toast = document.createElement('div');
    toast.className = 'toast';
    toast.innerHTML = '<i class="fas fa-check-circle"></i> ' + message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

function openAddModal() {
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus-circle"></i> Nouvelle réclamation';
    document.getElementById('formAction').value = 'create_reclamation';
    document.getElementById('reclamationForm').reset();
    document.getElementById('reclamationModal').style.display = 'flex';
}

function openEditModal(r) {
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit"></i> Modifier la réclamation';
    document.getElementById('formAction').value = 'reclamation_update';
    document.getElementById('editId').value = r.id;
    document.getElementById('titre').value = r.titre;
    document.getElementById('description').value = r.description;
    document.getElementById('categorie').value = r.categorie;
    document.getElementById('priorite').value = r.priorite;
    if(document.getElementById('utilisateur_id')) document.getElementById('utilisateur_id').value = r.utilisateur_id;
    if(document.getElementById('statut')) document.getElementById('statut').value = r.statut;
    if(document.getElementById('reponse_admin')) document.getElementById('reponse_admin').value = r.reponse_admin || '';
    if(document.getElementById('note_satisfaction')) document.getElementById('note_satisfaction').value = r.note_satisfaction || '';
    if(document.getElementById('score_urgence')) document.getElementById('score_urgence').value = r.score_urgence || '';
    if(document.getElementById('date_creation')) document.getElementById('date_creation').value = r.date_creation || '';
    document.getElementById('reclamationModal').style.display = 'flex';
}

function closeModal() { document.getElementById('reclamationModal').style.display = 'none'; }
function closeDetailModal() { document.getElementById('detailModal').style.display = 'none'; }

function viewDetails(id) {
    let rows = document.querySelectorAll('#tableBody tr');
    let row = null;
    for(let r of rows) {
        if(r.cells && r.cells[0] && r.cells[0].innerText == '#'+id) {
            row = r;
            break;
        }
    }
    if(row) {
        document.getElementById('detailContent').innerHTML = `
            <div class="form-group"><label>Titre</label><p><strong>${row.cells[1]?.innerText || ''}</strong></p></div>
            <div class="form-group"><label>Utilisateur</label><p>${row.cells[2]?.innerText || ''}</p></div>
            <div class="form-group"><label>Catégorie</label><p>${row.cells[3]?.innerText || ''}</p></div>
            <div class="form-group"><label>Date création</label><p>${row.cells[4]?.innerText || ''}</p></div>
            <div class="form-group"><label>Priorité</label><p>${row.cells[5]?.innerText || ''}</p></div>
            <div class="form-group"><label>Score urgence</label><p>${row.cells[6]?.innerText || ''}</p></div>
            <div class="form-group"><label>Statut</label><p>${row.cells[7]?.innerText || ''}</p></div>
            <div class="form-group"><label>Note satisfaction</label><p>${row.cells[8]?.innerText || '—'}</p></div>
        `;
        document.getElementById('detailModal').style.display = 'flex';
    }
}

document.getElementById('searchInput')?.addEventListener('keyup', function() {
    let search = this.value.toLowerCase();
    let rows = document.querySelectorAll('#tableBody tr');
    rows.forEach(row => {
        if(row.cells) {
            let text = row.innerText.toLowerCase();
            row.style.display = text.includes(search) ? '' : 'none';
        }
    });
});

<?php if($isAdmin): ?>
function filterTable() {
    let statut = document.getElementById('filterStatut').value;
    let priorite = document.getElementById('filterPriorite').value;
    let categorie = document.getElementById('filterCategorie').value;
    let rows = document.querySelectorAll('#tableBody tr');
    
    rows.forEach(row => {
        if(row.cells) {
            let rowStatut = row.getAttribute('data-statut') || '';
            let rowPriorite = row.getAttribute('data-priorite') || '';
            let rowCategorie = row.getAttribute('data-categorie') || '';
            let show = true;
            if(statut && rowStatut !== statut) show = false;
            if(priorite && rowPriorite !== priorite) show = false;
            if(categorie && rowCategorie !== categorie) show = false;
            row.style.display = show ? '' : 'none';
        }
    });
}

document.getElementById('filterStatut')?.addEventListener('change', filterTable);
document.getElementById('filterPriorite')?.addEventListener('change', filterTable);
document.getElementById('filterCategorie')?.addEventListener('change', filterTable);
<?php endif; ?>

window.onclick = function(e) {
    if(e.target.classList && e.target.classList.contains('modal')) {
        e.target.style.display = 'none';
    }
}

<?php if($msg): ?>
showToast('<?= addslashes($msg) ?>');
<?php endif; ?>
</script>
</body>
</html>