<?php
if (session_status() === PHP_SESSION_NONE) session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$dbname = 'ecoride';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Erreur DB : " . $e->getMessage());
}

// Création des tables (inchangée)
$pdo->exec("CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL
)");
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
try { $pdo->exec("ALTER TABLE reclamations ADD COLUMN historique_statut TEXT NULL"); } catch(PDOException $e) {}

// Insertion données test
if ($pdo->query("SELECT COUNT(*) FROM utilisateurs")->fetchColumn() == 0) {
    $pdo->exec("INSERT INTO utilisateurs (id, nom, email) VALUES (1, 'Jean Dupont', 'jean@email.com'), (2, 'Sara Benali', 'sara@email.com')");
}
if ($pdo->query("SELECT COUNT(*) FROM reclamations")->fetchColumn() == 0) {
    $pdo->exec("INSERT INTO reclamations (id, utilisateur_id, titre, description, categorie, priorite, statut, date_creation, note_satisfaction, score_urgence, historique_statut) VALUES 
        (1,1,'Problème de paiement','Débit double','paiement','elevee','en_attente','2025-06-01',NULL,90,'[\"en_attente\"]'),
        (2,1,'Application plante','Crash Android','technique','moyenne','en_cours','2025-05-28',NULL,60,'[\"en_attente\",\"en_cours\"]'),
        (3,2,'Conducteur absent','Pas présent','securite','elevee','resolue','2025-05-20',5,95,'[\"en_attente\",\"en_cours\",\"resolue\"]'),
        (4,2,'Remboursement','Non reçu','paiement','moyenne','rejetee','2025-05-15',2,40,'[\"en_attente\",\"rejetee\"]')");
}

// Gestion du mode via le menu (GET)
if (isset($_GET['mode']) && $_GET['mode'] == 'gestion') {
    $_SESSION['view'] = 'gestion';
    header('Location: index.php');
    exit;
} elseif (isset($_GET['mode']) && $_GET['mode'] == 'conducteur') {
    $_SESSION['view'] = 'conducteur';
    header('Location: index.php');
    exit;
}
if (!isset($_SESSION['view'])) $_SESSION['view'] = 'conducteur';
$isGestion = ($_SESSION['view'] === 'gestion');

// Traitement POST (CRUD) inchangé
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create_reclamation') {
        $errors = [];
        if (strlen(trim($_POST['titre'])) < 3) $errors[] = 'Titre (min 3)';
        if (strlen(trim($_POST['description'])) < 10) $errors[] = 'Description (min 10)';
        if (empty($_POST['categorie'])) $errors[] = 'Catégorie';
        if (empty($_POST['priorite'])) $errors[] = 'Priorité';
        if (!empty($errors)) {
            $_SESSION['err'] = implode(' - ', $errors);
        } else {
            $stmt = $pdo->prepare("INSERT INTO reclamations (utilisateur_id, titre, description, categorie, priorite, statut, date_creation, note_satisfaction, score_urgence, historique_statut) 
                VALUES (1, :titre, :desc, :cat, :prio, 'en_attente', CURDATE(), :note, :score, '[\"en_attente\"]')");
            $stmt->execute([
                ':titre' => $_POST['titre'],
                ':desc' => $_POST['description'],
                ':cat' => $_POST['categorie'],
                ':prio' => $_POST['priorite'],
                ':note' => !empty($_POST['note_satisfaction']) ? $_POST['note_satisfaction'] : null,
                ':score' => $_POST['score_urgence'] ?? 0
            ]);
            $_SESSION['msg'] = '✅ Réclamation ajoutée';
        }
        header('Location: index.php');
        exit;
    }
    
    if ($action === 'reclamation_update') {
        $stmt = $pdo->prepare("SELECT historique_statut, statut FROM reclamations WHERE id = :id");
        $stmt->execute([':id' => $_POST['id']]);
        $old = $stmt->fetch();
        $hist = json_decode($old['historique_statut'], true);
        if ($old['statut'] != $_POST['statut']) $hist[] = $_POST['statut'];
        $stmt = $pdo->prepare("UPDATE reclamations SET titre=:titre, description=:desc, categorie=:cat, priorite=:prio, statut=:statut, note_satisfaction=:note, score_urgence=:score, reponse_admin=:rep, historique_statut=:hist WHERE id=:id");
        $stmt->execute([
            ':id' => $_POST['id'], ':titre' => $_POST['titre'], ':desc' => $_POST['description'],
            ':cat' => $_POST['categorie'], ':prio' => $_POST['priorite'], ':statut' => $_POST['statut'],
            ':note' => !empty($_POST['note_satisfaction']) ? $_POST['note_satisfaction'] : null,
            ':score' => $_POST['score_urgence'] ?? 0, ':rep' => $_POST['reponse_admin'] ?? null,
            ':hist' => json_encode($hist)
        ]);
        $_SESSION['msg'] = '✅ Réclamation modifiée';
        header('Location: index.php');
        exit;
    }
    
    if ($action === 'reclamation_delete') {
        $stmt = $pdo->prepare("DELETE FROM reclamations WHERE id = :id");
        $stmt->execute([':id' => $_POST['id']]);
        $_SESSION['msg'] = '✅ Réclamation supprimée';
        header('Location: index.php');
        exit;
    }
    
    if ($action === 'reclamation_statut') {
        $stmt = $pdo->prepare("SELECT historique_statut, statut FROM reclamations WHERE id = :id");
        $stmt->execute([':id' => $_POST['id']]);
        $old = $stmt->fetch();
        $hist = json_decode($old['historique_statut'], true);
        if ($old['statut'] != $_POST['statut']) $hist[] = $_POST['statut'];
        $stmt = $pdo->prepare("UPDATE reclamations SET statut = :statut, historique_statut = :hist WHERE id = :id");
        $stmt->execute([':id' => $_POST['id'], ':statut' => $_POST['statut'], ':hist' => json_encode($hist)]);
        $_SESSION['msg'] = '✅ Statut mis à jour';
        header('Location: index.php');
        exit;
    }
}

// Récupération des données
$reclamations = $pdo->query("SELECT r.*, u.nom as utilisateur_nom, u.email as utilisateur_email FROM reclamations r LEFT JOIN utilisateurs u ON r.utilisateur_id = u.id ORDER BY r.date_creation DESC")->fetchAll();
$stats = $pdo->query("SELECT COUNT(*) as total, SUM(statut='en_attente') as en_attente, SUM(statut='en_cours') as en_cours, SUM(statut='resolue') as resolue, SUM(statut='rejetee') as rejetee, SUM(priorite='elevee') as priorite_elevee FROM reclamations")->fetch();
$statsCategorie = $pdo->query("SELECT categorie, COUNT(*) as count FROM reclamations GROUP BY categorie")->fetchAll();
$statsStatut = $pdo->query("SELECT statut, COUNT(*) as count FROM reclamations GROUP BY statut")->fetchAll();
$statsMois = $pdo->query("SELECT DATE_FORMAT(date_creation, '%Y-%m') as mois, COUNT(*) as count FROM reclamations GROUP BY mois ORDER BY mois")->fetchAll();

$msg = $_SESSION['msg'] ?? null;
$err = $_SESSION['err'] ?? null;
unset($_SESSION['msg'], $_SESSION['err']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoRide - Réclamations</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0A1628;
            color: #fff;
            transition: background 0.3s, color 0.3s;
        }
        /* MODE CLAIR */
        body.light-mode {
            background: #f5f5f5;
            color: #333;
        }
        body.light-mode .navbar {
            background: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        body.light-mode .navbar .logo,
        body.light-mode .navbar .dropdown-btn,
        body.light-mode .navbar .user-info {
            color: #1976D2;
        }
        body.light-mode .dropdown-content {
            background: #fff;
            border: 1px solid #e0e0e0;
        }
        body.light-mode .dropdown-content a {
            color: #333;
        }
        body.light-mode .sidebar {
            background: #fff;
            border-right: 1px solid #e0e0e0;
        }
        body.light-mode .sidebar nav ul li a {
            color: #333;
        }
        body.light-mode .sidebar nav ul li a i {
            color: #1976D2;
        }
        body.light-mode .form-card,
        body.light-mode .stat-box,
        body.light-mode .table-container,
        body.light-mode .dashboard-container,
        body.light-mode .chart-card,
        body.light-mode .modal-content {
            background: #fff;
            border-color: #e0e0e0;
            color: #333;
        }
        body.light-mode .form-group input,
        body.light-mode .form-group select,
        body.light-mode .form-group textarea {
            background: #f5f5f5;
            color: #333;
            border-color: #ccc;
        }
        body.light-mode .btn-submit {
            background: #1976D2;
            color: white;
        }
        body.light-mode .alert-success {
            background: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }
        body.light-mode .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
        /* NAVBAR (mode conducteur) */
        .navbar {
            background: linear-gradient(90deg, #1976D2, #0F3B6E);
            padding: 0.8rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .nav-left { display: flex; align-items: center; gap: 2rem; }
        .logo { display: flex; align-items: center; gap: 8px; font-size: 1.3rem; font-weight: 700; color: #fff; text-decoration: none; }
        .logo i { color: #61B3FA; }
        .dropdown { position: relative; display: inline-block; }
        .dropdown-btn {
            background: rgba(255,255,255,0.1);
            color: #fff;
            padding: 0.6rem 1.2rem;
            border: 1px solid rgba(97,179,250,.4);
            border-radius: 30px;
            font-size: 0.9rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .dropdown-btn:hover { background: rgba(255,255,255,0.2); }
        .dropdown-content {
            display: none;
            position: absolute;
            top: 110%;
            left: 0;
            min-width: 220px;
            background: linear-gradient(145deg, #0D1F3A, #122A4A);
            border: 1px solid rgba(97,179,250,.3);
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0,0,0,.4);
            z-index: 200;
            overflow: hidden;
        }
        .dropdown-content.show { display: block; animation: fadeInDown 0.25s ease; }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .dropdown-content a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.8rem 1.2rem;
            color: #fff;
            text-decoration: none;
            font-size: 0.85rem;
            transition: all 0.2s;
        }
        .dropdown-content a i { width: 20px; color: #61B3FA; }
        .dropdown-content a:hover { background: rgba(97,179,250,.15); padding-left: 1.5rem; }
        .dropdown-divider { height: 1px; background: rgba(97,179,250,.2); margin: 0.3rem 0; }
        .nav-right { display: flex; align-items: center; gap: 1rem; }
        .user-info {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,0.1);
            padding: 0.4rem 1rem;
            border-radius: 30px;
            font-size: 0.85rem;
        }
        .theme-btn {
            background: rgba(255,255,255,0.1);
            border: none;
            color: #fff;
            padding: 0.4rem 0.8rem;
            border-radius: 30px;
            cursor: pointer;
        }
        /* SIDEBAR (mode gestionnaire) */
        .admin-container { display: flex; min-height: 100vh; }
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #1976D2 0%, #0F3B6E 100%);
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
            border-bottom: 2px solid #61B3FA;
            text-align: center;
        }
        .sidebar .logo i { font-size: 48px; color: #61B3FA; margin-bottom: 10px; }
        .sidebar .logo h2 {
            background: linear-gradient(135deg, #FFF, #61B3FA);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            font-size: 1.5rem;
        }
        .sidebar .logo p { color: #A7A9AC; font-size: 0.8rem; }
        .sidebar nav ul { list-style: none; }
        .sidebar nav ul li { margin-bottom: 0.5rem; }
        .sidebar nav ul li a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.8rem 1rem;
            color: #FFF;
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s;
        }
        .sidebar nav ul li a i { width: 24px; color: #61B3FA; }
        .sidebar nav ul li a:hover,
        .sidebar nav ul li a.active {
            background: rgba(255,255,255,0.15);
            border-left: 3px solid #61B3FA;
        }
        .main-content-gestion {
            flex: 1;
            margin-left: 280px;
            padding: 2rem;
        }
        /* Conteneur conducteur */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }
        /* Cartes stats */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-box {
            background: rgba(255,255,255,0.08);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 1.2rem;
            text-align: center;
            border: 1px solid rgba(97,179,250,0.2);
            transition: 0.3s;
        }
        .stat-box:hover { transform: translateY(-5px); border-color: #61B3FA; }
        .stat-box .number {
            font-size: 2rem;
            font-weight: bold;
            background: linear-gradient(135deg, #61B3FA, #FFF);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .stat-box .label {
            color: #A7A9AC;
            font-size: 0.85rem;
            margin-top: 0.5rem;
        }
        /* Formulaire */
        .form-card {
            background: rgba(255,255,255,0.06);
            border-radius: 24px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(97,179,250,0.2);
            max-width: 800px;
            margin: 0 auto;
        }
        .form-card h2 {
            color: #61B3FA;
            margin-bottom: 1rem;
            font-size: 1.2rem;
            border-bottom: 1px solid rgba(97,179,250,0.2);
            padding-bottom: 0.5rem;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .form-group-full { grid-column: 1 / -1; }
        .form-group label {
            display: block;
            margin-bottom: 0.3rem;
            color: #A7A9AC;
            font-size: 0.8rem;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 0.6rem 0.8rem;
            border-radius: 12px;
            border: 1px solid rgba(97,179,250,0.3);
            background: rgba(255,255,255,0.08);
            color: #fff;
            font-size: 0.85rem;
        }
        .btn-submit {
            background: linear-gradient(135deg, #1976D2, #61B3FA);
            border: none;
            padding: 0.7rem 1.5rem;
            border-radius: 30px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 1rem;
            width: 100%;
            justify-content: center;
        }
        .error-msg {
            color: #e74c3c;
            font-size: 0.7rem;
            margin-top: 0.2rem;
            display: none;
        }
        /* Top bar (gestionnaire) sans boutons de mode */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        /* Tableau */
        .table-container {
            background: rgba(255,255,255,0.05);
            border-radius: 20px;
            overflow-x: auto;
            border: 1px solid rgba(97,179,250,0.2);
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1000px;
        }
        .data-table th, .data-table td {
            padding: 0.8rem 1rem;
            text-align: left;
            font-size: 0.8rem;
        }
        .data-table th {
            background: rgba(25,118,210,0.3);
            color: #61B3FA;
            border-bottom: 2px solid #61B3FA;
        }
        .data-table tr { border-bottom: 1px solid rgba(97,179,250,0.1); }
        .data-table tr:hover { background: rgba(25,118,210,0.1); }
        .badge {
            padding: 0.2rem 0.5rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            display: inline-block;
        }
        .badge-en_attente { background: rgba(241,196,15,0.15); color: #f1c40f; border: 1px solid #f1c40f; }
        .badge-en_cours { background: rgba(52,152,219,0.15); color: #3498db; border: 1px solid #3498db; }
        .badge-resolue { background: rgba(46,204,113,0.15); color: #2ecc71; border: 1px solid #2ecc71; }
        .badge-rejetee { background: rgba(231,76,60,0.15); color: #e74c3c; border: 1px solid #e74c3c; }
        .badge-faible { background: rgba(149,165,166,0.15); color: #95a5a6; border: 1px solid #95a5a6; }
        .badge-moyenne { background: rgba(230,126,34,0.15); color: #e67e22; border: 1px solid #e67e22; }
        .badge-elevee { background: rgba(231,76,60,0.15); color: #e74c3c; border: 1px solid #e74c3c; }
        .score-bar { display: flex; align-items: center; gap: 5px; }
        .score-bar-progress { width: 50px; height: 5px; background: rgba(255,255,255,0.1); border-radius: 3px; overflow: hidden; }
        .score-bar-fill { height: 100%; border-radius: 3px; }
        .statut-select {
            background: rgba(25,118,210,0.2);
            border: 1px solid #61B3FA;
            color: white;
            padding: 0.2rem 0.5rem;
            border-radius: 15px;
            cursor: pointer;
            font-size: 0.7rem;
        }
        .action-buttons { display: flex; gap: 0.5rem; flex-wrap: wrap; }
        .btn-icon {
            background: transparent;
            border: none;
            padding: 0.3rem 0.5rem;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.7rem;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: 0.2s;
        }
        .btn-icon.view { color: #2ecc71; }
        .btn-icon.edit { color: #61B3FA; }
        .btn-icon.delete { color: #e74c3c; }
        .btn-icon.history { color: #9b59b6; }
        .btn-icon:hover { background: rgba(255,255,255,0.1); transform: scale(1.02); }
        .stars { color: #f1c40f; font-size: 0.8rem; letter-spacing: 2px; }
        /* Dashboard */
        .dashboard-container {
            background: rgba(255,255,255,0.05);
            border-radius: 20px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        .chart-card {
            background: rgba(255,255,255,0.05);
            border-radius: 16px;
            padding: 1rem;
            text-align: center;
        }
        /* Modals */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            backdrop-filter: blur(8px);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background: linear-gradient(135deg, #1A2A3A, #0F1A2A);
            padding: 1.5rem;
            border-radius: 20px;
            width: 90%;
            max-width: 550px;
            border: 1px solid #61B3FA;
            max-height: 85vh;
            overflow-y: auto;
        }
        .modal-content h2 { color: #61B3FA; margin-bottom: 1rem; font-size: 1.1rem; }
        .detail-row { display: flex; padding: 0.4rem 0; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 0.8rem; }
        .detail-label { width: 110px; color: #A7A9AC; }
        .detail-value { flex: 1; }
        .historique-item { display: flex; align-items: center; gap: 10px; padding: 0.4rem 0; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .historique-dot { width: 8px; height: 8px; border-radius: 50%; }
        .modal-buttons { display: flex; gap: 0.8rem; justify-content: flex-end; margin-top: 1rem; }
        .btn-save, .btn-cancel { padding: 0.4rem 0.8rem; border-radius: 20px; cursor: pointer; font-size: 0.8rem; }
        .btn-save { background: linear-gradient(135deg, #1976D2, #61B3FA); border: none; color: #fff; }
        .btn-cancel { background: rgba(231,76,60,0.2); border: 1px solid #e74c3c; color: #e74c3c; }
        .alert-success { background: rgba(46,204,113,0.2); border: 1px solid #2ecc71; padding: 0.5rem 1rem; border-radius: 10px; margin-bottom: 1rem; color: #2ecc71; }
        .alert-error { background: rgba(231,76,60,0.2); border: 1px solid #e74c3c; padding: 0.5rem 1rem; border-radius: 10px; margin-bottom: 1rem; color: #e74c3c; }
        footer { text-align: center; padding: 1.5rem 0; color: #A7A9AC; font-size: 0.7rem; margin-top: 2rem; }
        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main-content-gestion { margin-left: 0; }
            .navbar { flex-direction: column; gap: 1rem; }
            .stats-row { grid-template-columns: 1fr 1fr; }
            .form-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<?php if($isGestion): ?>
    <!-- MODE GESTIONNAIRE : Sidebar -->
    <div class="admin-container">
        <aside class="sidebar">
            <div class="logo">
                <i class="fas fa-leaf"></i>
                <h2>EcoRide</h2>
                <p>Panneau d'administration</p>
            </div>
            <nav>
                <ul>
                    <li><a href="#"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a></li>
                    <li><a href="#" class="active"><i class="fas fa-exclamation-circle"></i> Réclamations</a></li>
                    <li><a href="#"><i class="fas fa-star"></i> Avis</a></li>
                    <li><a href="#"><i class="fas fa-cog"></i> Paramètres</a></li>
                    <li><a href="?mode=conducteur"><i class="fas fa-arrow-left"></i> Mes réclamations</a></li>
                    <li><a href="#"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content-gestion">
            <div class="top-bar">
                <h1><i class="fas fa-exclamation-circle"></i> Gestion des Réclamations</h1>
                <!-- Plus de boutons de mode -->
            </div>
<?php else: ?>
    <!-- MODE CONDUCTEUR : Navbar avec menu -->
    <nav class="navbar">
        <div class="nav-left">
            <a href="#" class="logo"><i class="fas fa-leaf"></i><span>EcoRide</span></a>
            <div class="dropdown">
                <button class="dropdown-btn" onclick="toggleDropdown()"><i class="fas fa-bars"></i><span>Menu</span></button>
                <div class="dropdown-content" id="dropdownMenu">
                    <a href="?mode=conducteur"><i class="fas fa-exclamation-circle"></i> Mes réclamations</a>
                    <div class="dropdown-divider"></div>
                    <a href="?mode=gestion" class="admin-link"><i class="fas fa-shield-alt"></i> Administration</a>
                    <a href="#" class="logout-link"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
                </div>
            </div>
        </div>
        <div class="nav-right">
            <button id="themeToggle" class="theme-btn"><i class="fas fa-moon"></i></button>
            <div class="user-info"><i class="fas fa-user-circle"></i><span>Utilisateur</span></div>
        </div>
    </nav>
    <div class="container">
        <div class="top-bar">
            <h1><i class="fas fa-exclamation-circle"></i> Nouvelle réclamation</h1>
        </div>
<?php endif; ?>

    <?php if($msg): ?><div class="alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <?php if($err): ?><div class="alert-error"><?= htmlspecialchars($err) ?></div><?php endif; ?>

    <?php if(!$isGestion): ?>
        <!-- Mode Conducteur : Formulaire -->
        <div class="form-card">
            <h2><i class="fas fa-plus-circle"></i> Nouvelle réclamation</h2>
            <form method="POST" id="reclamationForm">
                <input type="hidden" name="action" value="create_reclamation">
                <div class="form-grid">
                    <div class="form-group form-group-full"><label><i class="fas fa-heading"></i> Titre *</label><input type="text" name="titre" id="titre" placeholder="Ex: Problème de connexion"><div class="error-msg" id="errTitre">Min 3 caractères</div></div>
                    <div class="form-group form-group-full"><label><i class="fas fa-align-left"></i> Description *</label><textarea name="description" id="description" rows="3" placeholder="Décrivez votre problème..."></textarea><div class="error-msg" id="errDescription">Min 10 caractères</div></div>
                    <div class="form-group"><label><i class="fas fa-tag"></i> Catégorie *</label><select name="categorie" id="categorie"><option value="">-- Choisir --</option><option value="technique">Technique</option><option value="paiement">Paiement</option><option value="securite">Sécurité</option><option value="autre">Autre</option></select><div class="error-msg" id="errCategorie">Choisissez une catégorie</div></div>
                    <div class="form-group"><label><i class="fas fa-fire"></i> Priorité *</label><select name="priorite" id="priorite"><option value="">-- Choisir --</option><option value="faible">Faible</option><option value="moyenne">Moyenne</option><option value="elevee">Élevée</option></select><div class="error-msg" id="errPriorite">Choisissez une priorité</div></div>
                    <div class="form-group"><label><i class="fas fa-star"></i> Note (1-5)</label><input type="number" name="note_satisfaction" id="note_satisfaction" min="1" max="5" placeholder="1 à 5"></div>
                    <div class="form-group"><label><i class="fas fa-brain"></i> Score (0-100)</label><input type="number" name="score_urgence" id="score_urgence" min="0" max="100" value="0"></div>
                </div>
                <button type="submit" class="btn-submit"><i class="fas fa-paper-plane"></i> Soumettre</button>
            </form>
        </div>
    <?php else: ?>
        <!-- Mode Gestionnaire : Statistiques + Tableau + Graphiques -->
        <div class="stats-row">
            <div class="stat-box"><div class="number"><?= $stats['total'] ?? 0 ?></div><div class="label">Total</div></div>
            <div class="stat-box"><div class="number"><?= $stats['en_attente'] ?? 0 ?></div><div class="label">En attente</div></div>
            <div class="stat-box"><div class="number"><?= $stats['en_cours'] ?? 0 ?></div><div class="label">En cours</div></div>
            <div class="stat-box"><div class="number"><?= $stats['resolue'] ?? 0 ?></div><div class="label">Résolues</div></div>
            <div class="stat-box"><div class="number"><?= $stats['rejetee'] ?? 0 ?></div><div class="label">Rejetées</div></div>
        </div>

        <div style="text-align: right; margin-bottom: 1rem;">
            <button id="showDashboardBtn" class="btn-switch" style="background: linear-gradient(135deg, #9b59b6, #8e44ad); border: none; padding: 0.5rem 1rem; border-radius: 25px; color: #fff; cursor: pointer;"><i class="fas fa-chart-line"></i> 📊 Tableau de bord</button>
            <button id="showTableBtn" class="btn-switch" style="display: none; background: linear-gradient(135deg, #9b59b6, #8e44ad); border: none; padding: 0.5rem 1rem; border-radius: 25px; color: #fff; cursor: pointer;"><i class="fas fa-table"></i> 📋 Retour au tableau</button>
        </div>

        <div id="tableView">
            <div class="table-container">
                <table class="data-table">
                    <thead><tr><th>ID</th><th>Titre</th><th>Utilisateur</th><th>Catégorie</th><th>Date création</th><th>Priorité</th><th>Score</th><th>Statut</th><th>Note</th><th>Actions</th></tr></thead>
                    <tbody id="reclamationTableBody">
                        <?php foreach($reclamations as $r): ?>
                        <tr data-id="<?= $r['id'] ?>" data-statut="<?= $r['statut'] ?>" data-priorite="<?= $r['priorite'] ?>" data-categorie="<?= $r['categorie'] ?>">
                            <td><code>#<?= $r['id'] ?></code></td>
                            <td><strong><?= htmlspecialchars(substr($r['titre'], 0, 35)) ?>...</strong></td>
                            <td><div><span><?= htmlspecialchars($r['utilisateur_nom'] ?? 'ID:'.$r['utilisateur_id']) ?></span><br><span style="font-size:0.65rem;color:var(--gris)"><?= htmlspecialchars($r['utilisateur_email'] ?? '') ?></span></div></td>
                            <td><span class="badge"><?= $r['categorie'] ?></span></td>
                            <td><?= date('d/m/Y', strtotime($r['date_creation'])) ?></td>
                            <td><span class="badge badge-<?= $r['priorite'] ?>"><?= $r['priorite'] ?></span></td>
                            <td><?php $score = $r['score_urgence'] ?? 0; $color = $score >= 80 ? '#e74c3c' : ($score >= 50 ? '#f1c40f' : '#2ecc71'); ?>
                            <div class="score-bar"><div class="score-bar-progress"><div class="score-bar-fill" style="width:<?= $score ?>%; background:<?= $color ?>"></div></div><span style="color:<?= $color ?>"><?= $score ?></span></div></td>
                            <td><form method="POST" style="margin:0"><input type="hidden" name="action" value="reclamation_statut"><input type="hidden" name="id" value="<?= $r['id'] ?>"><select name="statut" class="statut-select" onchange="this.form.submit()"><option value="en_attente" <?= $r['statut']=='en_attente'?'selected':'' ?>>En attente</option><option value="en_cours" <?= $r['statut']=='en_cours'?'selected':'' ?>>En cours</option><option value="resolue" <?= $r['statut']=='resolue'?'selected':'' ?>>Résolue</option><option value="rejetee" <?= $r['statut']=='rejetee'?'selected':'' ?>>Rejetée</option></select></form></td>
                            <td><?php $note = $r['note_satisfaction']; if($note && $note>=1 && $note<=5) echo '<span class="stars">'.str_repeat('★', $note).str_repeat('☆', 5-$note).'</span>'; else echo '<span style="color:var(--gris)">—</span>'; ?></td>
                            <td class="action-buttons">
                                <button class="btn-icon view" onclick='viewDetails(<?= json_encode($r) ?>)'><i class="fas fa-eye"></i> Détails</button>
                                <button class="btn-icon edit" onclick='openEditModal(<?= json_encode($r) ?>)'><i class="fas fa-edit"></i></button>
                                <button class="btn-icon history" onclick='showHistory(<?= json_encode($r) ?>)'><i class="fas fa-history"></i></button>
                                <form method="POST" style="display:inline-block" onsubmit="return confirm('Supprimer ?')"><input type="hidden" name="action" value="reclamation_delete"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button type="submit" class="btn-icon delete"><i class="fas fa-trash"></i></button></form>
                            </div>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="dashboardView" style="display: none;">
            <div class="dashboard-container">
                <h3><i class="fas fa-chart-pie"></i> Statistiques détaillées</h3>
                <div class="charts-grid">
                    <div class="chart-card"><canvas id="chartStatut" width="300" height="200"></canvas><p style="margin-top:0.5rem;color:var(--gris);font-size:0.7rem;">Par statut</p></div>
                    <div class="chart-card"><canvas id="chartCategorie" width="300" height="200"></canvas><p style="margin-top:0.5rem;color:var(--gris);font-size:0.7rem;">Par catégorie</p></div>
                    <div class="chart-card"><canvas id="chartMois" width="300" height="200"></canvas><p style="margin-top:0.5rem;color:var(--gris);font-size:0.7rem;">Évolution mensuelle</p></div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <footer><i class="fas fa-leaf"></i> EcoRide - Covoiturage intelligent et écologique</footer>

<?php if($isGestion): ?>
        </main>
    </div>
<?php else: ?>
    </div>
<?php endif; ?>

<!-- Modales -->
<div id="reclamationModal" class="modal"><div class="modal-content"><h2 id="modalTitle">Nouvelle réclamation</h2><form id="reclamationFormModal" onsubmit="saveReclamation(event)"><input type="hidden" id="recId"><div class="form-group"><label>Titre</label><input type="text" id="titreModal" required></div><div class="form-group"><label>Description</label><textarea id="descriptionModal" rows="3" required></textarea></div><div class="form-group"><label>Catégorie</label><select id="categorieModal" required><option value="technique">Technique</option><option value="paiement">Paiement</option><option value="securite">Sécurité</option><option value="autre">Autre</option></select></div><div class="form-group"><label>Priorité</label><select id="prioriteModal" required><option value="faible">Faible</option><option value="moyenne">Moyenne</option><option value="elevee">Élevée</option></select></div><div class="form-group"><label>Statut</label><select id="statutModal"><option value="en_attente">En attente</option><option value="en_cours">En cours</option><option value="resolue">Résolue</option><option value="rejetee">Rejetée</option></select></div><div class="form-group"><label>Note (1-5)</label><input type="number" id="noteModal" min="1" max="5"></div><div class="form-group"><label>Score (0-100)</label><input type="number" id="scoreModal" min="0" max="100"></div><div class="form-group"><label>Réponse admin</label><textarea id="reponseModal" rows="2"></textarea></div><div class="modal-buttons"><button type="button" class="btn-cancel" onclick="closeModal()">Annuler</button><button type="submit" class="btn-save">Enregistrer</button></div></form></div></div>
<div id="detailModal" class="modal"><div class="modal-content"><h2>Détail de la réclamation</h2><div id="detailContent"></div><div class="modal-buttons"><button class="btn-cancel" onclick="closeDetailModal()">Fermer</button></div></div></div>
<div id="globalHistoryModal" class="modal"><div class="modal-content"><h2>Historique complet</h2><div id="globalHistoryContent"></div><div class="modal-buttons"><button class="btn-cancel" onclick="closeGlobalHistoryModal()">Fermer</button></div></div></div>

<script>
const reclamationsData = <?= json_encode($reclamations) ?>;
let chartsInitialized = false;

function toggleDropdown() { document.getElementById("dropdownMenu").classList.toggle("show"); }
window.onclick = function(event) {
    if (!event.target.matches('.dropdown-btn')) {
        var dropdowns = document.getElementsByClassName("dropdown-content");
        for (var i = 0; i < dropdowns.length; i++) {
            if (dropdowns[i].classList.contains('show')) dropdowns[i].classList.remove('show');
        }
    }
    if (event.target.classList?.contains('modal')) event.target.style.display = 'none';
}

const themeToggle = document.getElementById('themeToggle');
if (themeToggle) {
    if (localStorage.getItem('theme') === 'light') {
        document.body.classList.add('light-mode');
        themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
    }
    themeToggle.addEventListener('click', () => {
        document.body.classList.toggle('light-mode');
        const isLight = document.body.classList.contains('light-mode');
        localStorage.setItem('theme', isLight ? 'light' : 'dark');
        themeToggle.innerHTML = isLight ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
    });
}

function openAddModal() { document.getElementById('modalTitle').innerHTML = 'Nouvelle réclamation'; document.getElementById('reclamationFormModal').reset(); document.getElementById('recId').value = ''; document.getElementById('reclamationModal').style.display = 'flex'; }
function openEditModal(r) { document.getElementById('modalTitle').innerHTML = 'Modifier'; document.getElementById('recId').value = r.id; document.getElementById('titreModal').value = r.titre; document.getElementById('descriptionModal').value = r.description; document.getElementById('categorieModal').value = r.categorie; document.getElementById('prioriteModal').value = r.priorite; document.getElementById('statutModal').value = r.statut; document.getElementById('noteModal').value = r.note_satisfaction || ''; document.getElementById('scoreModal').value = r.score_urgence || 0; document.getElementById('reponseModal').value = r.reponse_admin || ''; document.getElementById('reclamationModal').style.display = 'flex'; }
function saveReclamation(e) { e.preventDefault(); let id = document.getElementById('recId').value; let fd = new FormData(); fd.append('action', id ? 'reclamation_update' : 'create_reclamation'); if(id) fd.append('id', id); fd.append('titre', document.getElementById('titreModal').value); fd.append('description', document.getElementById('descriptionModal').value); fd.append('categorie', document.getElementById('categorieModal').value); fd.append('priorite', document.getElementById('prioriteModal').value); fd.append('statut', document.getElementById('statutModal').value); fd.append('note_satisfaction', document.getElementById('noteModal').value); fd.append('score_urgence', document.getElementById('scoreModal').value); fd.append('reponse_admin', document.getElementById('reponseModal').value); fetch('index.php', { method:'POST', body:fd }).then(() => location.reload()); }
function viewDetails(r) { let statutText = { 'en_attente':'En attente','en_cours':'En cours','resolue':'Résolue','rejetee':'Rejetée' }; let prioriteText = { 'faible':'Faible','moyenne':'Moyenne','elevee':'Élevée' }; let categorieText = { 'technique':'Technique','paiement':'Paiement','securite':'Sécurité','autre':'Autre' }; let hist = r.historique_statut ? JSON.parse(r.historique_statut) : [r.statut]; let histHtml = ''; for(let i=0;i<hist.length;i++){ let col = '#61B3FA'; if(hist[i]=='en_attente') col='#f1c40f'; else if(hist[i]=='en_cours') col='#3498db'; else if(hist[i]=='resolue') col='#2ecc71'; else if(hist[i]=='rejetee') col='#e74c3c'; histHtml += `<div class="historique-item"><div class="historique-dot" style="background:${col}"></div><span>${statutText[hist[i]]}</span><span style="color:var(--gris);margin-left:auto">Étape ${i+1}</span></div>`; }
    document.getElementById('detailContent').innerHTML = `<div class="detail-row"><div class="detail-label">ID :</div><div class="detail-value">#${r.id}</div></div><div class="detail-row"><div class="detail-label">Titre :</div><div class="detail-value"><strong>${r.titre}</strong></div></div><div class="detail-row"><div class="detail-label">Description :</div><div class="detail-value">${r.description}</div></div><div class="detail-row"><div class="detail-label">Utilisateur :</div><div class="detail-value">${r.utilisateur_nom || 'ID:'+r.utilisateur_id}</div></div><div class="detail-row"><div class="detail-label">Catégorie :</div><div class="detail-value">${categorieText[r.categorie]}</div></div><div class="detail-row"><div class="detail-label">Priorité :</div><div class="detail-value"><span class="badge badge-${r.priorite}">${prioriteText[r.priorite]}</span></div></div><div class="detail-row"><div class="detail-label">Statut :</div><div class="detail-value"><span class="badge badge-${r.statut}">${statutText[r.statut]}</span></div></div><div class="detail-row"><div class="detail-label">Date :</div><div class="detail-value">${new Date(r.date_creation).toLocaleDateString('fr-FR')}</div></div><div class="detail-row"><div class="detail-label">Score :</div><div class="detail-value">${r.score_urgence || 0}%</div></div><div class="detail-row"><div class="detail-label">Note :</div><div class="detail-value">${r.note_satisfaction ? '★'.repeat(r.note_satisfaction)+'☆'.repeat(5-r.note_satisfaction) : '—'}</div></div>${r.reponse_admin ? `<div class="detail-row"><div class="detail-label">Réponse :</div><div class="detail-value">${r.reponse_admin}</div></div>` : ''}<div class="detail-row"><div class="detail-label">Historique :</div><div class="detail-value">${histHtml}</div></div>`;
    document.getElementById('detailModal').style.display = 'flex'; }
function showHistory(r) { let statutText = { 'en_attente':'En attente','en_cours':'En cours','resolue':'Résolue','rejetee':'Rejetée' }; let hist = r.historique_statut ? JSON.parse(r.historique_statut) : [r.statut]; let html = `<div style="margin-bottom:0.8rem"><strong>📋 #${r.id} - ${r.titre}</strong></div>`; for(let i=0;i<hist.length;i++){ let col = '#61B3FA'; if(hist[i]=='en_attente') col='#f1c40f'; else if(hist[i]=='en_cours') col='#3498db'; else if(hist[i]=='resolue') col='#2ecc71'; else if(hist[i]=='rejetee') col='#e74c3c'; html += `<div class="historique-item"><div class="historique-dot" style="background:${col}"></div><span>${statutText[hist[i]]}</span><span style="color:var(--gris);margin-left:auto">Étape ${i+1}</span></div>`; } document.getElementById('globalHistoryContent').innerHTML = html; document.getElementById('globalHistoryModal').style.display = 'flex'; }
function showGlobalHistory() { let statutText = { 'en_attente':'En attente','en_cours':'En cours','resolue':'Résolue','rejetee':'Rejetée' }; let html = ''; for(let r of reclamationsData){ let hist = r.historique_statut ? JSON.parse(r.historique_statut) : [r.statut]; html += `<div style="margin-bottom:1rem;padding:0.5rem;background:rgba(255,255,255,0.03);border-radius:10px;"><div style="font-weight:bold;color:var(--bleu-clair);margin-bottom:0.3rem">📋 #${r.id} - ${r.titre}</div>`; for(let i=0;i<hist.length;i++){ let col = '#61B3FA'; if(hist[i]=='en_attente') col='#f1c40f'; else if(hist[i]=='en_cours') col='#3498db'; else if(hist[i]=='resolue') col='#2ecc71'; else if(hist[i]=='rejetee') col='#e74c3c'; html += `<div class="historique-item"><div class="historique-dot" style="background:${col}"></div><span>${statutText[hist[i]]}</span><span style="color:var(--gris);margin-left:auto">Étape ${i+1}</span></div>`; } html += `</div>`; } document.getElementById('globalHistoryContent').innerHTML = html || '<div class="empty-state">Aucun historique</div>'; document.getElementById('globalHistoryModal').style.display = 'flex'; }
function closeModal() { document.getElementById('reclamationModal').style.display = 'none'; }
function closeDetailModal() { document.getElementById('detailModal').style.display = 'none'; }
function closeGlobalHistoryModal() { document.getElementById('globalHistoryModal').style.display = 'none'; }

<?php if($isGestion): ?>
const showDashboardBtn = document.getElementById('showDashboardBtn');
const showTableBtn = document.getElementById('showTableBtn');
const tableView = document.getElementById('tableView');
const dashboardView = document.getElementById('dashboardView');
if (showDashboardBtn && showTableBtn && tableView && dashboardView) {
    showDashboardBtn.addEventListener('click', function() {
        tableView.style.display = 'none';
        dashboardView.style.display = 'block';
        showDashboardBtn.style.display = 'none';
        showTableBtn.style.display = 'inline-flex';
        if (!chartsInitialized) {
            const ctxStatut = document.getElementById('chartStatut')?.getContext('2d');
            if(ctxStatut) new Chart(ctxStatut, { type: 'doughnut', data: { labels: <?= json_encode(array_column($statsStatut, 'statut')) ?>, datasets: [{ data: <?= json_encode(array_column($statsStatut, 'count')) ?>, backgroundColor: ['#f1c40f', '#3498db', '#2ecc71', '#e74c3c'], borderWidth: 0 }] }, options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { position: 'bottom', labels: { color: '#fff' } } } } });
            const ctxCat = document.getElementById('chartCategorie')?.getContext('2d');
            if(ctxCat) new Chart(ctxCat, { type: 'bar', data: { labels: <?= json_encode(array_column($statsCategorie, 'categorie')) ?>, datasets: [{ label: 'Nombre', data: <?= json_encode(array_column($statsCategorie, 'count')) ?>, backgroundColor: '#61B3FA', borderRadius: 6 }] }, options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { display: false } }, scales: { y: { ticks: { color: '#fff' }, grid: { color: 'rgba(255,255,255,0.1)' } }, x: { ticks: { color: '#fff' } } } } });
            const ctxMois = document.getElementById('chartMois')?.getContext('2d');
            if(ctxMois) new Chart(ctxMois, { type: 'line', data: { labels: <?= json_encode(array_column($statsMois, 'mois')) ?>, datasets: [{ label: 'Réclamations', data: <?= json_encode(array_column($statsMois, 'count')) ?>, borderColor: '#61B3FA', backgroundColor: 'rgba(97,179,250,0.1)', fill: true, tension: 0.3 }] }, options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { labels: { color: '#fff' } } }, scales: { y: { ticks: { color: '#fff' }, grid: { color: 'rgba(255,255,255,0.1)' } }, x: { ticks: { color: '#fff' } } } } });
            chartsInitialized = true;
        }
    });
    showTableBtn.addEventListener('click', function() {
        dashboardView.style.display = 'none';
        tableView.style.display = 'block';
        showTableBtn.style.display = 'none';
        showDashboardBtn.style.display = 'inline-flex';
    });
}
<?php endif; ?>

const form = document.getElementById('reclamationForm');
if(form) {
    form.addEventListener('submit', function(e) {
        let ok = true;
        document.querySelectorAll('.error-msg').forEach(el => el.style.display = 'none');
        if(document.getElementById('titre').value.trim().length < 3) { document.getElementById('errTitre').style.display = 'block'; ok = false; }
        if(document.getElementById('description').value.trim().length < 10) { document.getElementById('errDescription').style.display = 'block'; ok = false; }
        if(!document.getElementById('categorie').value) { document.getElementById('errCategorie').style.display = 'block'; ok = false; }
        if(!document.getElementById('priorite').value) { document.getElementById('errPriorite').style.display = 'block'; ok = false; }
        if(!ok) e.preventDefault();
    });
}
</script>
</body>
</html>