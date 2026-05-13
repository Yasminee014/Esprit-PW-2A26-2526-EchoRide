<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/includes/auth_guard.php';

require_once __DIR__ . '/../../Config/Database.php';
require_once __DIR__ . '/../../Controller/LostFoundFrontController.php';
require_once __DIR__ . '/../../Model/LostFoundFrontRepository.php';

$pdo = Database::getInstance();
$controller = new LostFoundFrontController(new LostFoundFrontRepository($pdo));

$userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;

if ($userId <= 0) {
    header('Location: /ecoride/View/frontoffice/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim((string) ($_POST['action'] ?? ''));

    if ($action === 'add_comment') {
        $controller->addComment(
            (int) ($_POST['declaration_id'] ?? 0),
            $userId,
            isset($_SESSION['user_name']) ? (string) $_SESSION['user_name'] : 'Utilisateur',
            trim((string) ($_POST['message'] ?? ''))
        );
        header('Location: mes_objets_perdus.php');
        exit;
    }

    if ($action === 'update_status') {
        $controller->updateDeclaration([
            'id' => (int) ($_POST['declaration_id'] ?? 0),
            'statut' => trim((string) ($_POST['statut'] ?? 'perdu')),
        ]);
        header('Location: mes_objets_perdus.php');
        exit;
    }

    if ($action === 'delete_declaration') {
        $controller->deleteDeclaration((int) ($_POST['id'] ?? 0));
        header('Location: mes_objets_perdus.php');
        exit;
    }
}

$userDeclarations = $controller->listByUserWithHistory($userId);

// Calculate statistics
$totalDeclarations = count($userDeclarations);
$openDeclarations = count(array_filter($userDeclarations, static fn(array $obj): bool => (string) ($obj['statut'] ?? 'perdu') === 'perdu'));
$resolvedDeclarations = count(array_filter($userDeclarations, static fn(array $obj): bool => in_array((string) ($obj['statut'] ?? 'perdu'), ['retrouve', 'restitue'], true)));

$userName = isset($_SESSION['user_name']) ? (string) $_SESSION['user_name'] : 'Utilisateur';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Objets Perdus | EcoRide</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
        <link rel="stylesheet" href="../../assets/css/lostfound_front.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }

        /* HERO */
        .mop-hero {
          background: linear-gradient(135deg, #1976D2, #0F3B6E);
          border-radius: 20px;
          padding: 2rem 2.5rem;
          margin-bottom: 2rem;
          display: flex;
          justify-content: space-between;
          align-items: center;
          gap: 1.5rem;
        }
        .mop-hero-content h1 { font-size: 1.9rem; margin-bottom: .4rem; font-weight: 700; }
        .mop-highlight { color: #61B3FA; }
        .mop-hero-content p { color: rgba(255,255,255,.8); font-size: .88rem; }
        .mop-hero-icon { font-size: 4rem; opacity: .35; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0F1729 0%, #1A2844 100%);
            color: #fff;
            min-height: 100vh;
        }


        .navbar-modern {
            background: linear-gradient(135deg, #1976D2 0%, #0F3B6E 100%);
            padding: 1.2rem 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
            flex-wrap: wrap;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .navbar-modern .logo {
            display: flex;
            flex-direction: row !important;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .navbar-modern .logo-text {
            font-size: 1.6rem;
            font-weight: 700;
            letter-spacing: 1px;
            color: white;
            line-height: 1.3;
        }

        .navbar-modern .logo-img {
            width: 52px;
            height: 52px;
            object-fit: contain;
            filter: drop-shadow(0 2px 6px rgba(0,0,0,0.4));
            transition: transform 0.3s ease;
        }

        .navbar-modern .logo:hover .logo-img {
            transform: scale(1.08) rotate(-3deg);
        }

        .navbar-modern .logo-tagline {
            font-size: 0.65rem;
            color: rgba(255,255,255,0.7);
            letter-spacing: 0.5px;
        }

        .menu-toggle {
            background: rgba(255,255,255,0.15);
            border: none;
            color: white;
            font-size: 1.2rem;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            cursor: pointer;
            display: none;
            transition: all 0.3s;
        }

        .menu-toggle:hover {
            background: rgba(255,255,255,0.25);
        }

        .nav-links {
            display: flex;
            gap: 0.8rem;
            list-style: none;
            margin: 0;
            padding: 0;
            align-items: center;
            flex-wrap: wrap;
        }

        .nav-links li a {
            text-decoration: none;
            padding: 0.5rem 1.2rem;
            border-radius: 30px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: transparent;
            color: white;
            border: none;
            cursor: pointer;
        }

        .nav-links li a:hover {
            background: rgba(255,255,255,0.2);
            transform: translateY(-2px);
        }

        .nav-links li a.active {
            background: #0A1628;
            color: white;
            box-shadow: 0 2px 8px rgba(10,22,40,0.3);
        }

        .nav-links .admin-btn {
            background: rgba(231,76,60,0.2);
            border: 1px solid rgba(231,76,60,0.4);
            color: #e74c3c;
            padding: 0.5rem 1.2rem;
            border-radius: 30px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s;
        }

        .nav-links .admin-btn:hover {
            background: rgba(231,76,60,0.35);
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(231,76,60,0.2);
        }

        .profile-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #2F6FA5;
            border: none;
            padding: 0.5rem 1.2rem;
            border-radius: 30px;
            cursor: pointer;
            transition: all 0.3s;
            color: #FFFFFF;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .profile-btn:hover {
            background: #3C82C4;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(60,130,196,0.3);
        }

        .profile-avatar {
            width: 28px;
            height: 28px;
            background: #5FA8E0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .profile-avatar i {
            font-size: 0.8rem;
            color: #FFFFFF;
        }

        .profile-btn span {
            color: #FFFFFF;
        }

        .profile-btn i.fa-chevron-down {
            font-size: 0.7rem;
            margin-left: 5px;
            color: #FFFFFF;
        }

        .theme-btn {
            background: rgba(255,255,255,0.15);
            border: none;
            color: white;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.1rem;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .theme-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: rotate(15deg);
        }

        .profile-dropdown {
            position: relative;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            width: 280px;
            background: #0F2A44;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            z-index: 1000;
            overflow: hidden;
            margin-top: 10px;
            backdrop-filter: blur(10px);
        }

        .dropdown-menu.show {
            display: block;
            animation: fadeIn 0.2s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .dropdown-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 1rem;
            background: #163A5C;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .dropdown-header .avatar {
            width: 45px;
            height: 45px;
            background: #5FA8E0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .dropdown-header .avatar i {
            font-size: 1.2rem;
            color: white;
        }

        .dropdown-header .user-info {
            display: flex;
            flex-direction: column;
        }

        .dropdown-header .user-name {
            font-size: 0.95rem;
            font-weight: 600;
            color: #CFE6FF;
        }

        .dropdown-header .user-role {
            font-size: 0.65rem;
            color: rgba(207,230,255,0.7);
        }

        .dropdown-links {
            padding: 0.5rem 0;
        }

        .dropdown-links a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.7rem 1rem;
            margin: 0 0.5rem;
            border-radius: 10px;
            color: #CFE6FF;
            background: transparent;
            text-decoration: none;
            font-size: 0.85rem;
            transition: all 0.2s;
        }

        .dropdown-links a i {
            width: 22px;
            color: #5FA8E0;
            font-size: 1rem;
        }

        .dropdown-links a:hover {
            background: rgba(255,255,255,0.05);
        }

        .dropdown-links a.active {
            background: #1E4F7A;
            position: relative;
        }

        .dropdown-links a.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background: #5FA8E0;
            border-radius: 0 3px 3px 0;
        }

        .dropdown-divider {
            height: 1px;
            background: rgba(255,255,255,0.08);
            margin: 0.5rem 0;
        }

        .dropdown-actions a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.7rem 1rem;
            margin: 0 0.5rem 0.5rem 0.5rem;
            border-radius: 10px;
            color: #FF5C5C;
            background: transparent;
            text-decoration: none;
            font-size: 0.85rem;
            transition: all 0.2s;
        }

        .dropdown-actions a i {
            width: 22px;
            color: #FF5C5C;
            font-size: 1rem;
        }

        .dropdown-actions a:hover {
            background: rgba(255,92,92,0.15);
        }

        body.light-mode {
            background: #f5f5f5;
            color: #333;
        }

        body.light-mode .navbar-modern {
            background: linear-gradient(135deg, #1565C0, #0D47A1);
        }

        @media (max-width: 768px) {
            .navbar-modern {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
                padding: 1rem;
            }
            
            .menu-toggle {
                display: block;
                position: absolute;
                top: 1rem;
                right: 1rem;
            }
            
            .nav-links {
                display: none;
                width: 100%;
                flex-direction: column;
                margin-top: 1rem;
                gap: 0.8rem;
            }
            
            .nav-links.show {
                display: flex;
            }
            
            .nav-links li a,
            .nav-links .admin-btn,
            .profile-btn,
            .theme-btn {
                padding: 0.7rem 1rem;
                display: block;
                text-align: center;
                width: 100%;
                border-radius: 30px;
            }
            
            .profile-dropdown {
                width: 100%;
            }
            
            .dropdown-menu {
                position: static;
                width: 100%;
                margin-top: 8px;
            }
            
            .dropdown-header {
                justify-content: center;
            }
        }


        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 5%;
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            color: #61B3FA;
        }

        .header p {
            font-size: 1rem;
            color: rgba(255,255,255,0.7);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: rgba(25, 118, 210, 0.15);
            border: 1px solid rgba(97, 179, 250, 0.3);
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            background: rgba(25, 118, 210, 0.25);
            border-color: rgba(97, 179, 250, 0.6);
            transform: translateY(-5px);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #61B3FA;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 0.9rem;
            color: rgba(255,255,255,0.7);
        }

        .declarations-container {
            display: grid;
            gap: 2rem;
        }

        .declaration-card {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(97, 179, 250, 0.2);
            border-radius: 12px;
            padding: 2rem;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .declaration-card:hover {
            border-color: rgba(97, 179, 250, 0.5);
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }

        .declaration-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .declaration-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #61B3FA;
        }

        .declaration-meta {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
        }

        .badge-category {
            background: rgba(97, 179, 250, 0.2);
            color: #61B3FA;
            border: 1px solid rgba(97, 179, 250, 0.3);
        }

        .badge-status {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-weight: 600;
        }

        .badge-perdu {
            background: rgba(255, 152, 0, 0.2);
            color: #FFB74D;
            border: 1px solid rgba(255, 152, 0, 0.3);
        }

        .badge-retrouve {
            background: rgba(76, 175, 80, 0.2);
            color: #81C784;
            border: 1px solid rgba(76, 175, 80, 0.3);
        }

        .badge-restitue {
            background: rgba(66, 133, 244, 0.2);
            color: #42A5F5;
            border: 1px solid rgba(66, 133, 244, 0.3);
        }

        .declaration-description {
            color: rgba(255,255,255,0.8);
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        .timeline {
            margin-top: 1.5rem;
            border-left: 2px solid rgba(97, 179, 250, 0.3);
            padding-left: 1.5rem;
        }

        .timeline-item {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -2.25rem;
            top: 0.5rem;
            width: 1rem;
            height: 1rem;
            background: #1976D2;
            border: 3px solid rgba(25, 118, 210, 0.2);
            border-radius: 50%;
            box-shadow: 0 0 10px rgba(97, 179, 250, 0.3);
        }

        .timeline-date {
            font-size: 0.8rem;
            color: rgba(97, 179, 250, 0.8);
            font-weight: 600;
            margin-bottom: 0.3rem;
        }

        .timeline-title {
            font-weight: 600;
            color: #61B3FA;
            margin-bottom: 0.3rem;
        }

        .timeline-description {
            color: rgba(255,255,255,0.7);
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .timeline-meta {
            font-size: 0.8rem;
            color: rgba(255,255,255,0.5);
            margin-top: 0.3rem;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
            flex-wrap: wrap;
        }

        .btn {
            padding: 0.55rem;
            width: 2.4rem;
            height: 2.4rem;
            border-radius: 8px;
            border: none;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, #1976D2, #1565C0);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(25, 118, 210, 0.4);
        }

        .btn-secondary {
            background: rgba(97, 179, 250, 0.2);
            color: #61B3FA;
            border: 1px solid rgba(97, 179, 250, 0.3);
        }

        .btn-secondary:hover {
            background: rgba(97, 179, 250, 0.3);
        }

        .btn-danger {
            background: rgba(231, 76, 60, 0.2);
            color: #FF6B6B;
            border: 1px solid rgba(231, 76, 60, 0.3);
        }

        .btn-danger:hover {
            background: rgba(231, 76, 60, 0.3);
        }

        .comment-section {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(97, 179, 250, 0.2);
        }

        .comment-form {
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: rgba(255,255,255,0.8);
            font-weight: 500;
        }

        .form-group textarea {
            width: 100%;
            padding: 0.8rem;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(97, 179, 250, 0.2);
            border-radius: 8px;
            color: white;
            font-family: 'Segoe UI', sans-serif;
            resize: vertical;
            min-height: 80px;
        }

        .form-group textarea:focus {
            outline: none;
            border-color: rgba(97, 179, 250, 0.5);
            box-shadow: 0 0 10px rgba(97, 179, 250, 0.2);
        }

        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            background: rgba(255,255,255,0.03);
            border-radius: 12px;
            border: 1px dashed rgba(97, 179, 250, 0.2);
        }

        .empty-state i {
            font-size: 3rem;
            color: rgba(97, 179, 250, 0.5);
            margin-bottom: 1rem;
        }

        .empty-state h2 {
            color: rgba(255,255,255,0.7);
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: rgba(255,255,255,0.5);
        }

        .empty-state a {
            display: inline-block;
            margin-top: 1rem;
            padding: 0.8rem 1.5rem;
            background: #1976D2;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .empty-state a:hover {
            background: #1565C0;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .container { padding: 1rem 5%; }
            .header h1 { font-size: 1.8rem; }
            .declaration-header { flex-direction: column; }
            .stats-grid { grid-template-columns: 1fr; }
            .action-buttons { flex-direction: column; }
            .btn { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>
<?php include_once __DIR__ . '/partials/navbar.php'; ?>

    <div class="container">
        <!-- HERO -->
        <div class="mop-hero">
          <div class="mop-hero-content">
            <h1>Mes <span class="mop-highlight">objets perdus</span></h1>
            <p>Historique et suivi de vos déclarations d'objets perdus</p>
          </div>
          <div class="mop-hero-icon"><i class="fas fa-box-open"></i></div>
        </div>

        <?php if (count($userDeclarations) === 0): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h2>Aucune déclaration</h2>
                <p>Vous n'avez pas encore déclaré d'objets perdus.</p>
                <a href="/ecoride/View/frontoffice/lostfound_front.php">
                    <i class="fas fa-plus"></i> Déclarer un objet perdu
                </a>
            </div>
        <?php else: ?>
            <div class="declarations-container">
                <?php foreach ($userDeclarations as $declaration): 
                    $decWithHistory = $controller->getDeclarationWithHistory((int) $declaration['id']);
                    if ($decWithHistory === null) {
                        continue;
                    }
                    $history = $decWithHistory['history'];
                    $statut = (string) ($declaration['statut'] ?? 'perdu');
                    $statusBadgeClass = 'badge-' . $statut;
                ?>
                    <div class="declaration-card">
                        <div class="declaration-header">
                            <div>
                                <div class="declaration-title"><?php echo htmlspecialchars((string) ($declaration['titre'] ?? 'Sans titre')); ?></div>
                                <div class="declaration-meta">
                                    <span class="badge badge-category">
                                        <i class="fas fa-tag"></i> <?php echo htmlspecialchars((string) ($declaration['categorie'] ?? 'Général')); ?>
                                    </span>
                                    <span class="badge badge-status <?php echo $statusBadgeClass; ?>">
                                        <i class="fas fa-circle"></i> 
                                        <?php 
                                            $statusLabel = [
                                                'perdu' => 'Perdu',
                                                'retrouve' => 'Retrouvé',
                                                'restitue' => 'Restitué'
                                            ][$statut] ?? 'Inconnu';
                                            echo htmlspecialchars($statusLabel);
                                        ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="declaration-description">
                            <strong>Description:</strong><br>
                            <?php echo htmlspecialchars((string) ($declaration['description'] ?? 'Aucune description')); ?>
                        </div>

                        <?php if (!empty($declaration['lieu_perte'])): ?>
                            <div class="declaration-description">
                                <strong>Lieu de perte:</strong> <?php echo htmlspecialchars((string) $declaration['lieu_perte']); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($declaration['photo_url'])): ?>
                            <div class="declaration-description">
                                <strong>Photo:</strong><br>
                                <img src="<?php echo htmlspecialchars((string) $declaration['photo_url']); ?>" alt="Photo" style="max-width: 100%; max-height: 250px; border-radius: 8px; margin-top: 0.5rem;">
                            </div>
                        <?php endif; ?>

                        <div class="timeline">
                            <div style="margin-bottom: 1.5rem; font-weight: 600; color: #61B3FA;">
                                <i class="fas fa-history"></i> Historique
                            </div>
                            <?php foreach ($history as $event): ?>
                                <div class="timeline-item">
                                    <div class="timeline-date">
                                        <i class="fas fa-calendar-alt"></i>
                                        <?php 
                                            $date = strtotime($event['date']);
                                            echo htmlspecialchars(date('d/m/Y H:i', $date));
                                        ?>
                                    </div>
                                    <div class="timeline-title">
                                        <?php 
                                            if ($event['type'] === 'creation') {
                                                echo '📢 ' . htmlspecialchars($event['title']);
                                            } else {
                                                echo '💬 ' . htmlspecialchars($event['title']);
                                            }
                                        ?>
                                    </div>
                                    <div class="timeline-description">
                                        <?php echo htmlspecialchars($event['description']); ?>
                                    </div>
                                    <?php if ($event['type'] === 'comment' && !empty($event['user_nom'])): ?>
                                        <div class="timeline-meta">
                                            Par: <?php echo htmlspecialchars((string) $event['user_nom']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="action-buttons">
                            <form method="post" style="margin: 0; display: inline;">
                                <input type="hidden" name="action" value="delete_declaration">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars((string) $declaration['id']); ?>">
                                <button type="submit" class="btn btn-danger" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette déclaration ?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>



                        <div class="comment-section">
                            <h3 style="color: #61B3FA; margin-bottom: 1rem;">
                                <i class="fas fa-comment-dots"></i> Ajouter une mise à jour
                            </h3>
                            <form method="post" class="comment-form">
                                <input type="hidden" name="action" value="add_comment">
                                <input type="hidden" name="declaration_id" value="<?php echo htmlspecialchars((string) $declaration['id']); ?>">
                                <div class="form-group">
                                    <label for="message-<?php echo htmlspecialchars((string) $declaration['id']); ?>">Message</label>
                                    <textarea 
                                        id="message-<?php echo htmlspecialchars((string) $declaration['id']); ?>" 
                                        name="message" 
                                        placeholder="Décrivez la mise à jour de cet objet..." 
                                        required
                                    ></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary" title="Envoyer">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

<script>
function toggleMenu() { document.getElementById('navLinks').classList.toggle('show'); }
function toggleProfileDropdown(event) { event.stopPropagation(); document.getElementById('profileDropdown').classList.toggle('show'); }
function toggleTheme() {
    document.body.classList.toggle('light-mode');
    const isLight = document.body.classList.contains('light-mode');
    localStorage.setItem('theme', isLight ? 'light' : 'dark');
    document.getElementById('themeToggle').querySelector('i').className = isLight ? 'fas fa-sun' : 'fas fa-moon';
}
if (localStorage.getItem('theme') === 'light') {
    document.body.classList.add('light-mode');
    const btn = document.getElementById('themeToggle');
    if (btn) btn.querySelector('i').className = 'fas fa-sun';
}
window.onclick = function(e) {
    if (!e.target.closest('.profile-dropdown')) {
        var d = document.getElementById('profileDropdown');
        if (d && d.classList.contains('show')) d.classList.remove('show');
    }
}
</script>
</body>
</html>
