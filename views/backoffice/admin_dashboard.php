<?php
// Vérifier si l'admin est connecté
if (!isset($_SESSION['admin_id'])) {
    header('Location: ' . BASE_URL . 'controllers/AdminController.php?action=showLogin');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eco Ride - Dashboard Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bleu-fonce:  #1976D2;
            --bleu-clair:  #61B3FA;
            --gris:        #A7A9AC;
            --dark-bg:     #0A1628;
            --sidebar-bg:  #0D1F3A;
            --text:        #F4F5F7;
            --border:      rgba(97,179,250,.25);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Poppins', 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #0A1628 0%, #0D1F3A 100%);
            min-height: 100vh;
            color: #F4F5F7;
        }

        /* ── Sidebar ── */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #1976D2 0%, #1565C0 40%, #0F3B6E 100%);
            position: fixed;
            height: 100vh;
            padding: 2rem 1rem;
            overflow-y: auto;
            border-right: none;
            z-index: 100;
            display: flex;
            flex-direction: column;
        }

        .sidebar .logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .sidebar .logo i {
            font-size: 48px;
            color: #61B3FA;
        }

        .sidebar .logo h2 {
            color: #61B3FA;
            margin-top: 10px;
        }

        .sidebar nav ul {
            list-style: none;
        }

        .sidebar nav ul li {
            margin-bottom: 0.5rem;
        }

        .sidebar nav ul li a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.8rem 1rem;
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
        }

        .sidebar nav ul li a:hover,
        .sidebar nav ul li a.active {
            background: rgba(255,255,255,.18);
        }

        .sidebar-footer {
            margin-top: auto;
            border-top: 1px solid rgba(255,255,255,.15);
            padding-top: 1rem;
        }

        .logout-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.8rem 1rem;
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
        }

        .logout-btn:hover {
            background: rgba(255,255,255,.15);
        }

        /* Main Content */
        .main-content {
            margin-left: 280px;
            padding: 0;
            min-height: 100vh;
        }


        /* ── Page content padding ── */
        .page-content {
            padding: 2rem;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #1976D2;
        }

        .top-bar h1 {
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .top-bar h1 i { color: #61B3FA; }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .btn-top {
            background: rgba(25,118,210, 0.2);
            color: #61B3FA;
            padding: 0.5rem 1.2rem;
            border-radius: 25px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            font-size: 0.9rem;
            border: 1px solid rgba(97,179,250,.3);
        }

        .btn-top:hover {
            background: rgba(25,118,210, 0.35);
        }

        .top-bar-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .btn-admin-profile {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(25,118,210, 0.15);
            color: #61B3FA;
            border: 1px solid rgba(97,179,250, 0.4);
            padding: 0.4rem 1rem 0.4rem 0.5rem;
            border-radius: 25px;
            font-size: 0.9rem;
            cursor: pointer;
            font-family: inherit;
            font-weight: 500;
            transition: all 0.3s;
            text-decoration: none;
        }

        .btn-admin-profile:hover {
            background: rgba(25,118,210, 0.3);
        }

        .btn-admin-profile .admin-avatar-btn {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(25,118,210, 0.3);
            border: 2px solid #61B3FA;
            flex-shrink: 0;
        }

        .btn-admin-profile .admin-avatar-btn img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .btn-admin-profile .admin-avatar-btn i {
            font-size: 0.9rem;
            color: #61B3FA;
        }

        /* Modal Profil Admin */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.65);
            backdrop-filter: blur(6px);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-admin-profile {
            background: linear-gradient(160deg, #0D1F3A 0%, #0A1628 100%);
            border: 1px solid rgba(25,118,210, 0.35);
            border-radius: 24px;
            padding: 0;
            width: 100%;
            max-width: 380px;
            position: relative;
            box-shadow: 0 25px 70px rgba(0,0,0,0.6), 0 0 40px rgba(25,118,210,0.08);
            overflow: hidden;
        }

        .modal-header-banner {
            background: linear-gradient(135deg, rgba(25,118,210,0.25), rgba(25,118,210,0.2));
            padding: 2rem 2rem 3.5rem;
            text-align: center;
            position: relative;
            border-bottom: 1px solid rgba(25,118,210,0.15);
        }

        .modal-header-banner::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0; right: 0;
            height: 30px;
            background: linear-gradient(160deg, #0D1F3A 0%, #0A1628 100%);
            clip-path: ellipse(55% 100% at 50% 100%);
        }

        .modal-greeting {
            font-size: 0.85rem;
            color: #61B3FA;
            margin-bottom: 0.3rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .modal-admin-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #fff;
        }

        .modal-admin-title span { color: #1976D2; }

        .modal-close {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(255,255,255,0.1);
            border: none;
            color: #fff;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            transition: background 0.2s;
            z-index: 2;
        }

        .modal-close:hover { background: rgba(255,68,68,0.4); color: #ff6b6b; }

        .modal-avatar-wrap {
            position: relative;
            width: 90px;
            height: 90px;
            margin: 0 auto;
            margin-top: -1rem;
        }

        .modal-avatar-circle {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            border: 3px solid #1976D2;
            overflow: hidden;
            background: rgba(25,118,210,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 20px rgba(25,118,210,0.3);
        }

        .modal-avatar-circle img {
            width: 100%; height: 100%; object-fit: cover;
        }

        .modal-avatar-circle i { font-size: 2.2rem; color: #1976D2; }

        .modal-avatar-upload {
            position: absolute;
            bottom: 0; right: 0;
            width: 26px; height: 26px;
            background: #1976D2;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            transition: background 0.2s;
            border: 2px solid #0A1628;
        }

        .modal-avatar-upload:hover { background: #1976D2; }
        .modal-avatar-upload i { font-size: 0.7rem; color: #fff; }
        .modal-avatar-upload input { display: none; }

        .modal-body {
            padding: 1.2rem 2rem 2rem;
            text-align: center;
        }

        .modal-admin-name {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 0.2rem;
        }

        .modal-admin-email {
            color: #61B3FA;
            font-size: 0.85rem;
            margin-bottom: 1rem;
        }

        .modal-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(25,118,210,0.12);
            border: 1px solid rgba(25,118,210,0.4);
            color: #1976D2;
            padding: 0.25rem 0.9rem;
            border-radius: 20px;
            font-size: 0.78rem;
            margin-bottom: 1.5rem;
        }

        .modal-divider {
            border: none;
            border-top: 1px solid rgba(255,255,255,0.08);
            margin: 0 0 1.2rem;
        }

        .modal-logout-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            color: #ff6b6b;
            text-decoration: none;
            padding: 0.65rem 1rem;
            border-radius: 12px;
            background: rgba(255,68,68,0.08);
            border: 1px solid rgba(255,68,68,0.2);
            transition: background 0.2s;
            font-size: 0.9rem;
        }

        .modal-logout-btn:hover { background: rgba(255,68,68,0.22); }

        /* Stats Cards */
        .stats-grid {
            display: flex;
            flex-wrap: nowrap;
            gap: 1.5rem;
            margin-bottom: 2rem;
            justify-content: flex-start;
            overflow-x: auto;
        }

        .stat-card {
            background: rgba(13, 31, 45, 0.9);
            border-radius: 20px;
            padding: 1.5rem;
            text-align: center;
            border: 1px solid rgba(25,118,210, 0.3);
            transition: all 0.3s;
            min-width: 180px;
            flex: 1;
        }

        .stat-card:hover {
            border-color: #1976D2;
            transform: translateY(-5px);
        }

        .stat-card i {
            font-size: 2rem;
            color: #1976D2;
            margin-bottom: 0.5rem;
        }

        .stat-card .number {
            font-size: 2rem;
            font-weight: bold;
            color: #1976D2;
        }

        .stat-card .label {
            color: #61B3FA;
            margin-top: 0.5rem;
            font-size: 0.85rem;
        }

        /* Actions Bar */
        .actions-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .search-box {
            position: relative;
            flex: 1;
            max-width: 300px;
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #A7A9AC;
        }

        .search-box input {
            width: 100%;
            padding: 0.8rem 1rem 0.8rem 2.8rem;
            border-radius: 30px;
            border: 1px solid rgba(25,118,210, 0.3);
            background: rgba(13, 31, 45, 0.8);
            color: white;
        }

        .filter-select {
            padding: 0.8rem 1rem;
            border-radius: 30px;
            border: 1px solid rgba(25,118,210, 0.3);
            background: rgba(13, 31, 45, 0.8);
            color: white;
            cursor: pointer;
        }

        /* Table */
        .table-container {
            background: rgba(13, 31, 45, 0.8);
            border-radius: 20px;
            overflow-x: auto;
            border: 1px solid rgba(25,118,210, 0.3);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
        }

        th, td {
            padding: 1rem;
            text-align: left;
        }

        th {
            background: rgba(25,118,210, 0.15);
            color: #1976D2;
            font-weight: 600;
        }

        tr {
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        tr:hover {
            background: rgba(25,118,210, 0.05);
        }

        /* Badges */
        .badge {
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: 0.7rem;
            display: inline-block;
        }

        .badge-active {
            background: rgba(0, 255, 136, 0.15);
            color: #00ff88;
        }

        .badge-inactive {
            background: rgba(255, 68, 68, 0.15);
            color: #ff6666;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .btn-icon {
            background: transparent;
            border: none;
            padding: 0.3rem 0.6rem;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.8rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-icon.edit {
            color: #1976D2;
        }

        .btn-icon.edit:hover {
            background: rgba(25,118,210, 0.2);
        }

        .btn-icon.details {
            color: #ffa500;
        }

        .btn-icon.details:hover {
            background: rgba(255, 165, 0, 0.2);
        }

        .btn-icon.ban {
            color: #ff4444;
        }

        .btn-icon.ban:hover {
            background: rgba(255, 68, 68, 0.2);
        }

        .btn-icon.unban {
            color: #00ff88;
        }

        .btn-icon.unban:hover {
            background: rgba(0, 255, 136, 0.2);
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(8px);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: #0A1628;
            padding: 2rem;
            border-radius: 24px;
            width: 90%;
            max-width: 800px;
            border: 1px solid #1976D2;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-content h2 {
            color: #1976D2;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .modal-section {
            margin-bottom: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 1rem;
        }

        .modal-section h3 {
            color: #1976D2;
            margin-bottom: 0.8rem;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .modal-section .empty {
            color: #A7A9AC;
            font-style: italic;
            padding: 0.5rem;
        }

        .detail-item {
            background: rgba(10, 47, 68, 0.5);
            padding: 0.5rem;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            font-size: 0.85rem;
        }

        .detail-item strong {
            color: #1976D2;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #61B3FA;
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 0.8rem;
            border-radius: 12px;
            border: 1px solid rgba(25,118,210, 0.3);
            background: rgba(10, 47, 68, 0.8);
            color: white;
        }

        .modal-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .btn-save {
            background: linear-gradient(135deg, #1976D2, #1976D2);
            color: white;
            padding: 0.7rem 1.5rem;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            flex: 1;
        }

        .btn-cancel, .close-modal {
            background: rgba(255, 68, 68, 0.2);
            border: 1px solid #ff4444;
            color: #ff4444;
            padding: 0.7rem 1.5rem;
            border-radius: 25px;
            cursor: pointer;
            flex: 1;
        }

        .detail-badge {
            display: inline-block;
            padding: 0.2rem 0.5rem;
            border-radius: 12px;
            font-size: 0.7rem;
            margin-left: 0.5rem;
        }

        .badge-vehicle { background: rgba(25,118,210,0.2); color: #1976D2; }
        .badge-trip { background: rgba(0,255,136,0.2); color: #00ff88; }
        .badge-reclamation { background: rgba(255,165,0,0.2); color: #ffa500; }
        .badge-event { background: rgba(255,68,68,0.2); color: #ff6666; }
        .badge-lost { background: rgba(255,68,68,0.2); color: #ff6666; }

        .toast {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: linear-gradient(135deg, #1976D2, #1976D2);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            z-index: 1100;
            animation: slideIn 0.3s, fadeOut 0.3s 2.7s;
        }

        .toast.error {
            background: linear-gradient(135deg, #ff4444, #cc0000);
        }

        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @keyframes fadeOut {
            to { opacity: 0; visibility: hidden; }
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #A7A9AC;
        }

        footer {
            text-align: center;
            padding: 2rem;
            border-top: 1px solid rgba(25,118,210, 0.2);
            color: #A7A9AC;
            margin-top: 2rem;
        }

        .sidebar-hidden {
            transform: translateX(-100%);
        }
        .sidebar {
            transition: transform 0.3s ease;
        }

        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }
            .main-content {
                margin-left: 0;
            }
            .stats-grid {
                flex-wrap: wrap;
            }
            .stat-card {
                min-width: auto;
            }
            .actions-bar {
                flex-direction: column;
                align-items: stretch;
            }
            .search-box {
                max-width: 100%;
            }
            .top-bar {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div style="display: flex;">
        <!-- Sidebar Gauche -->
        <div class="sidebar">
            <div>
                <div class="logo">
                    <i class="fas fa-leaf"></i>
                    <h2>EcoRide</h2>
                    <div style="font-size:0.62rem;letter-spacing:0.2em;color:rgba(255,255,255,.6);text-transform:uppercase;margin-top:4px;">Administration</div>
                </div>
                <div style="height:1px;background:rgba(255,255,255,.15);margin-bottom:1.2rem;"></div>
                <div style="font-size:0.62rem;font-weight:700;letter-spacing:0.18em;color:rgba(255,255,255,.55);text-transform:uppercase;padding:0 1rem 0.5rem;">Gestion</div>
                <nav>
                    <ul>
                        <li>
                            <a href="#" class="active" data-page="dashboard">
                                <i class="fas fa-chart-line"></i> Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="#" data-page="passagers">
                                <i class="fas fa-users"></i> Passagers
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
            <div class="sidebar-footer">
                <a href="<?= BASE_URL ?>controllers/AdminController.php?action=logout" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">


            <!-- Page Content -->
            <div class="page-content">
            <div class="top-bar">
                <h1 id="pageTitle"><i class="fas fa-chart-line"></i> Dashboard</h1>
                <div class="top-bar-right">
                    <a href="<?= BASE_URL ?>controllers/AdminController.php?action=showProfile" class="btn-admin-profile">
                        <div class="admin-avatar-btn">
                            <?php if (!empty($_SESSION['admin_photo'])): ?>
                                <img src="<?= BASE_URL ?>uploads/photos/<?= htmlspecialchars($_SESSION['admin_photo']) ?>" alt="">
                            <?php else: ?>
                                <i class="fas fa-user-shield"></i>
                            <?php endif; ?>
                        </div>
                        <?= htmlspecialchars($_SESSION['admin_nom'] ?? 'Admin') ?>
                        <i class="fas fa-chevron-right" style="font-size:0.7rem"></i>
                    </a>
                    <a href="<?= BASE_URL ?>controllers/UserController.php?action=showLoginForm" class="btn-top">
                        <i class="fas fa-home"></i> Retour accueil
                    </a>
                </div>
            </div>

            <!-- Dashboard Page -->
            <div id="dashboardPage">
                <div class="stats-grid">
                    <div class="stat-card">
                        <i class="fas fa-users"></i>
                        <div class="number"><?= $stats['total_passagers'] ?? 0 ?></div>
                        <div class="label">Passagers total</div>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-user-check"></i>
                        <div class="number"><?= $stats['active_passagers'] ?? 0 ?></div>
                        <div class="label">Passagers actifs</div>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-user-slash"></i>
                        <div class="number"><?= $stats['inactive_passagers'] ?? 0 ?></div>
                        <div class="label">Passagers inactifs</div>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-user-shield"></i>
                        <div class="number"><?= $stats['total_admins'] ?? 0 ?></div>
                        <div class="label">Administrateurs</div>
                    </div>
                </div>
            </div>

            <!-- Passagers Page -->
            <div id="passagersPage" style="display: none;">
                <div class="actions-bar">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Rechercher un passager...">
                    </div>
                    <div class="filter-group">
                        <select id="statusFilter" class="filter-select">
                            <option value="all">Tous les statuts</option>
                            <option value="actif">Actifs</option>
                            <option value="inactif">Inactifs</option>
                        </select>
                    </div>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom complet</th>
                                <th>Email</th>
                                <th>Téléphone</th>
                                <th>Statut</th>
                                <th>Date d'inscription</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="passagersTableBody">
                            <?php foreach ($passagers as $passager): ?>
                            <tr data-status="<?= $passager['statut'] ?>">
                                <td><?= $passager['id'] ?></td>
                                <td><strong><?= htmlspecialchars($passager['prenom'] . ' ' . $passager['nom']) ?></strong></td>
                                <td><?= htmlspecialchars($passager['email']) ?></td>
                                <td><?= htmlspecialchars($passager['telephone'] ?? '-') ?></td>
                                <td>
                                    <span class="badge <?= $passager['statut'] === 'actif' ? 'badge-active' : 'badge-inactive' ?>">
                                        <?= $passager['statut'] === 'actif' ? 'Actif' : 'Banni' ?>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y', strtotime($passager['created_at'])) ?></td>
                                <td class="action-buttons">
                                    <button class="btn-icon details" onclick="showPassagerDetails(<?= $passager['id'] ?>)">
                                        <i class="fas fa-info-circle"></i> Détails
                                    </button>
                                    <button class="btn-icon edit" onclick="editPassager(<?= $passager['id'] ?>)">
                                        <i class="fas fa-edit"></i> Modifier
                                    </button>
                                    <?php if ($passager['statut'] === 'actif'): ?>
                                        <button class="btn-icon ban" onclick="banPassager(<?= $passager['id'] ?>)">
                                            <i class="fas fa-ban"></i> Bannir
                                        </button>
                                    <?php else: ?>
                                        <button class="btn-icon unban" onclick="unbanPassager(<?= $passager['id'] ?>)">
                                            <i class="fas fa-check-circle"></i> Réactiver
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($passagers)): ?>
                            <tr>
                                <td colspan="7"><div class="empty-state">Aucun passager inscrit</div></td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div><!-- /page-content -->
    </div><!-- /main-content -->
</div>

    <footer>
        <p><svg width="16" height="16" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align:middle"><path d="M22 4C22 4 8 10 8 24C8 31.732 14.268 38 22 38C29.732 38 36 31.732 36 24C36 14 28 8 22 4Z" fill="#61B3FA" opacity="0.9"/></svg> Eco Ride by Echo Group © 2025 - Panel Administrateur</p>
    </footer>

    <!-- Modal Détails Passager -->
    <div id="detailsModal" class="modal">
        <div class="modal-content">
            <h2><i class="fas fa-user-circle"></i> Détails du passager</h2>
            <div id="detailsContent"></div>
            <button class="close-modal" onclick="closeDetailsModal()">Fermer</button>
        </div>
    </div>

    <!-- Modal Modifier Passager -->
    <div id="passagerModal" class="modal">
        <div class="modal-content">
            <h2 id="modalTitle"><i class="fas fa-user-edit"></i> Modifier le passager</h2>
            <form id="passagerForm" method="POST" action="<?= BASE_URL ?>controllers/AdminController.php?action=editPassager">
                <input type="hidden" id="passagerId" name="id">
                <div class="form-group">
                    <label>Prénom *</label>
                    <input type="text" id="prenom" name="prenom" >
                </div>
                <div class="form-group">
                    <label>Nom *</label>
                    <input type="text" id="nom" name="nom" >
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="text" id="email" name="email" >
                </div>
                <div class="form-group">
                    <label>Téléphone</label>
                    <input type="text" id="telephone" name="telephone">
                </div>
                <div class="form-group">
                    <label>Statut</label>
                    <select id="statut" name="statut">
                        <option value="actif">Actif</option>
                        <option value="inactif">Banni</option>
                    </select>
                </div>
                <div class="modal-buttons">
                    <button type="submit" class="btn-save">Enregistrer</button>
                    <button type="button" class="btn-cancel" onclick="closePassagerModal()">Annuler</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Navigation entre les pages
        document.querySelectorAll('.sidebar nav ul li a').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelectorAll('.sidebar nav ul li a').forEach(nav => nav.classList.remove('active'));
                this.classList.add('active');
                
                const page = this.dataset.page;
                
                document.getElementById('dashboardPage').style.display = page === 'dashboard' ? 'block' : 'none';
                document.getElementById('passagersPage').style.display = page === 'passagers' ? 'block' : 'none';
                
                const titles = {
                    dashboard: 'Dashboard',
                    passagers: 'Gestion des Passagers'
                };
                document.getElementById('pageTitle').innerHTML = `<i class="fas ${page === 'dashboard' ? 'fa-chart-line' : 'fa-users'}"></i> ${titles[page]}`;
            });
        });

        // Filtres
        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');

        function filterTable() {
            const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
            const statusValue = statusFilter ? statusFilter.value : 'all';
            const rows = document.querySelectorAll('#passagersTableBody tr');
            
            rows.forEach(row => {
                if (row.cells.length < 2) return;
                const name = row.cells[1]?.innerText.toLowerCase() || '';
                const email = row.cells[2]?.innerText.toLowerCase() || '';
                const status = row.getAttribute('data-status') || '';
                
                let matchesSearch = name.includes(searchTerm) || email.includes(searchTerm);
                let matchesStatus = statusValue === 'all' || status === statusValue;
                
                row.style.display = matchesSearch && matchesStatus ? '' : 'none';
            });
        }

        if (searchInput) searchInput.addEventListener('input', filterTable);
        if (statusFilter) statusFilter.addEventListener('change', filterTable);

        // Afficher les détails
        function showPassagerDetails(userId) {
            fetch('<?= BASE_URL ?>controllers/AdminController.php?action=getPassagerDetails&id=' + userId)
                .then(response => response.json())
                .then(data => {
                    const content = document.getElementById('detailsContent');
                    const user = data.user;
                    
                    let html = `
                        <div class="modal-section">
                            <h3><i class="fas fa-user"></i> Informations personnelles</h3>
                            <div class="detail-item"><strong>ID:</strong> ${user.id}</div>
                            <div class="detail-item"><strong>Nom:</strong> ${user.prenom} ${user.nom}</div>
                            <div class="detail-item"><strong>Email:</strong> ${user.email}</div>
                            <div class="detail-item"><strong>Téléphone:</strong> ${user.telephone || 'Non renseigné'}</div>
                            <div class="detail-item"><strong>Statut:</strong> ${user.statut === 'actif' ? 'Actif' : 'Banni'}</div>
                            <div class="detail-item"><strong>Date d'inscription:</strong> ${new Date(user.created_at).toLocaleDateString('fr-FR')}</div>
                        </div>
                    `;
                    
                    // Véhicules
                    html += `<div class="modal-section"><h3><i class="fas fa-car"></i> Véhicules <span class="detail-badge badge-vehicle">${data.vehicles.length}</span></h3>`;
                    if (data.vehicles.length > 0) {
                        data.vehicles.forEach(v => {
                            html += `<div class="detail-item"><strong>${v.brand} ${v.model}</strong> - ${v.plate} - ${v.color} - ${v.seats} places</div>`;
                        });
                    } else {
                        html += `<div class="empty">Aucun véhicule enregistré</div>`;
                    }
                    html += `</div>`;
                    
                    // Trajets
                    html += `<div class="modal-section"><h3><i class="fas fa-route"></i> Trajets <span class="detail-badge badge-trip">${data.trips.length}</span></h3>`;
                    if (data.trips.length > 0) {
                        data.trips.forEach(t => {
                            html += `<div class="detail-item"><strong>${t.departure} → ${t.arrival}</strong> - ${t.date} ${t.time} - ${t.price} DT - ${t.available}/${t.seats} places</div>`;
                        });
                    } else {
                        html += `<div class="empty">Aucun trajet proposé</div>`;
                    }
                    html += `</div>`;
                    
                    // Réclamations
                    html += `<div class="modal-section"><h3><i class="fas fa-exclamation-triangle"></i> Réclamations <span class="detail-badge badge-reclamation">${data.reclamations.length}</span></h3>`;
                    if (data.reclamations.length > 0) {
                        data.reclamations.forEach(r => {
                            html += `<div class="detail-item"><strong>${r.title || 'Sans titre'}</strong> - ${r.status || 'En attente'} - ${new Date(r.created_at).toLocaleDateString('fr-FR')}</div>`;
                        });
                    } else {
                        html += `<div class="empty">Aucune réclamation</div>`;
                    }
                    html += `</div>`;
                    
                    // Événements
                    html += `<div class="modal-section"><h3><i class="fas fa-calendar-alt"></i> Événements <span class="detail-badge badge-event">${data.events.length}</span></h3>`;
                    if (data.events.length > 0) {
                        data.events.forEach(e => {
                            html += `<div class="detail-item"><strong>${e.title || 'Sans titre'}</strong> - ${e.date || 'Date non définie'}</div>`;
                        });
                    } else {
                        html += `<div class="empty">Aucun événement</div>`;
                    }
                    html += `</div>`;
                    
                    // Objets perdus/trouvés
                    html += `<div class="modal-section"><h3><i class="fas fa-search"></i> Objets perdus/trouvés <span class="detail-badge badge-lost">${data.lost_found.length}</span></h3>`;
                    if (data.lost_found.length > 0) {
                        data.lost_found.forEach(l => {
                            html += `<div class="detail-item"><strong>${l.item_name || 'Objet'}</strong> - ${l.status || 'En cours'} - ${new Date(l.created_at).toLocaleDateString('fr-FR')}</div>`;
                        });
                    } else {
                        html += `<div class="empty">Aucun objet signalé</div>`;
                    }
                    html += `</div>`;
                    
                    content.innerHTML = html;
                    document.getElementById('detailsModal').style.display = 'flex';
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Erreur lors du chargement des détails');
                });
        }

        function closeDetailsModal() {
            document.getElementById('detailsModal').style.display = 'none';
        }

        // Modification passager
        function editPassager(id) {
            fetch('<?= BASE_URL ?>controllers/AdminController.php?action=getPassager&id=' + id)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-user-edit"></i> Modifier le passager';
                    document.getElementById('passagerId').value = data.id;
                    document.getElementById('prenom').value = data.prenom;
                    document.getElementById('nom').value = data.nom;
                    document.getElementById('email').value = data.email;
                    document.getElementById('telephone').value = data.telephone || '';
                    document.getElementById('statut').value = data.statut;
                    document.getElementById('passagerModal').style.display = 'flex';
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Erreur lors du chargement des données');
                });
        }

        // Bannir un passager
        function banPassager(id) {
            if (confirm('Êtes-vous sûr de vouloir BANNIR ce passager ? Il ne pourra plus se connecter.')) {
                window.location.href = '<?= BASE_URL ?>controllers/AdminController.php?action=banPassager&id=' + id;
            }
        }

        // Réactiver un passager banni
        function unbanPassager(id) {
            if (confirm('Êtes-vous sûr de vouloir RÉACTIVER ce passager ? Il pourra à nouveau se connecter.')) {
                window.location.href = '<?= BASE_URL ?>controllers/AdminController.php?action=unbanPassager&id=' + id;
            }
        }

        function closePassagerModal() {
            document.getElementById('passagerModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const passagerModal = document.getElementById('passagerModal');
            const detailsModal = document.getElementById('detailsModal');
            if (event.target === passagerModal) passagerModal.style.display = 'none';
            if (event.target === detailsModal) detailsModal.style.display = 'none';
        }

        // Toast messages
        <?php if (isset($_SESSION['admin_success'])): ?>
            showToast('<?= $_SESSION['admin_success'] ?>');
            <?php unset($_SESSION['admin_success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['admin_error'])): ?>
            showToast('<?= $_SESSION['admin_error'] ?>', 'error');
            <?php unset($_SESSION['admin_error']); ?>
        <?php endif; ?>

        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = 'toast';
            if (type === 'error') toast.classList.add('error');
            toast.innerHTML = `<i class="fas ${type === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle'}"></i> ${message}`;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }
        
        // Toggle sidebar (bouton Menu navbar)
        document.getElementById('toggleSidebar')?.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('sidebar-hidden');
            const mc = document.querySelector('.main-content');
            mc.style.marginLeft = document.querySelector('.sidebar').classList.contains('sidebar-hidden') ? '0' : '280px';
        });
    </script>
    <script src="<?= BASE_URL ?>views/backoffice/js/admin_dashboard.validation.js"></script>
</body>
</html>