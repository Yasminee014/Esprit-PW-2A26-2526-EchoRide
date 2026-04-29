<?php
if (!isset($_SESSION['admin_id'])) {
    header('Location: ' . BASE_URL . 'controllers/AdminController.php?action=showLogin');
    exit();
}

$profileErrors  = $_SESSION['admin_profile_errors']    ?? [];
$profileOld     = $_SESSION['admin_profile_old_input'] ?? [];
$profileSuccess = $_SESSION['admin_profile_success']   ?? '';
unset($_SESSION['admin_profile_errors'], $_SESSION['admin_profile_old_input'], $_SESSION['admin_profile_success']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eco Ride - Profil Administrateur</title>
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
            width: 240px;
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

        .sidebar nav ul li { margin-bottom: 0.5rem; }

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
            transition: background 0.2s;
        }

        .logout-btn:hover {
            background: rgba(255,255,255,.15);
        }

        /* ── Main content ── */
        .main-content {
            margin-left: 240px;
            width: calc(100% - 240px);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── Top bar / Navbar ── */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            background: linear-gradient(90deg, #0D2350 0%, #0F3166 50%, #0D2350 100%);
            border-radius: 16px;
            padding: 0.75rem 1.5rem;
            border: 1px solid rgba(97,179,250,0.18);
            box-shadow: 0 4px 24px rgba(0,0,0,0.25);
            flex-wrap: nowrap;
            width: 100%;
            box-sizing: border-box;
        }

        .top-bar-left {
            display: flex;
            align-items: center;
            gap: 0.7rem;
            flex-shrink: 0;
        }

        .top-bar-left h1 {
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }

        .top-bar-left h1 i { color: #61B3FA; }

        .top-bar-right {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            flex-wrap: nowrap;
            flex-shrink: 0;
        }

        /* Boutons navbar */
        .btn-nav {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 0.4rem 1rem;
            border-radius: 20px;
            text-decoration: none;
            font-family: 'Poppins', sans-serif;
            font-size: 0.85rem;
            font-weight: 500;
            color: #fff;
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.15);
            transition: background 0.2s;
            white-space: nowrap;
        }
        .btn-nav:hover { background: rgba(255,255,255,.18); }

        /* Retour accueil */
        .btn-home {
            background: rgba(25,118,210,0.15);
            border-color: rgba(97,179,250,0.35);
            color: #61B3FA;
        }
        .btn-home:hover { background: rgba(25,118,210,0.3); }

        /* Retour dashboard */
        .btn-dashboard {
            background: rgba(25,118,210,0.15);
            border-color: rgba(97,179,250,0.35);
            color: #61B3FA;
        }
        .btn-dashboard:hover { background: rgba(25,118,210,0.3); }

        /* Admin (actif) */
        .btn-admin-active {
            background: rgba(25,118,210,0.35);
            border-color: #1976D2;
            color: #fff;
            cursor: default;
        }

        /* Déconnexion */
        .btn-logout {
            background: rgba(220,53,69,.2);
            border-color: rgba(220,53,69,.4);
            color: #ff8080;
        }
        .btn-logout:hover { background: rgba(220,53,69,.35); }

        /* Hamburger menu */
        .btn-menu {
            background: none;
            border: none;
            color: #61B3FA;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 6px;
            transition: background 0.2s;
        }
        .btn-menu:hover { background: rgba(97,179,250,0.1); }

        /* ── Page container ── */
        .page-content {
            padding: 2rem 3rem;
            flex: 1;
            width: 100%;
            box-sizing: border-box;
        }

        /* ── Hero ── */
        .profile-hero {
            background: linear-gradient(135deg, rgba(25,118,210,0.12) 0%, rgba(25,118,210,0.06) 100%);
            border: 1px solid rgba(25,118,210,0.15);
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            margin-bottom: 2rem;
        }

        .hero-greeting {
            font-size: 0.85rem;
            color: #61B3FA;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 0.3rem;
        }

        .hero-title {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.2rem;
        }

        .hero-title span { color: #1976D2; }

        .hero-sub { color: #61B3FA; font-size: 0.88rem; }

        /* ── Avatar ── */
        .avatar-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 2rem;
            position: relative;
        }

        .avatar-circle {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            border: 4px solid #1976D2;
            overflow: hidden;
            background: rgba(25,118,210,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 30px rgba(25,118,210,0.35), 0 8px 30px rgba(0,0,0,0.4);
            position: relative;
        }

        .avatar-circle img { width:100%; height:100%; object-fit:cover; }
        .avatar-circle i { font-size:3rem; color:#1976D2; }

        .avatar-upload-overlay {
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 38px;
            background: rgba(0,0,0,0.55);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.2s;
        }

        .avatar-upload-overlay:hover { background: rgba(25,118,210,0.6); }
        .avatar-upload-overlay i { font-size: 0.9rem; color: #fff; }
        .avatar-upload-overlay input { display:none; }

        .avatar-name { margin-top: 1rem; font-size: 1.3rem; font-weight: 700; }

        .avatar-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(25,118,210,0.12);
            border: 1px solid rgba(25,118,210,0.4);
            color: #1976D2;
            padding: 0.25rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            margin-top: 0.4rem;
        }

        /* ── Layout ── */
        .profile-container {
            width: 100%;
            max-width: 100%;
            padding: 0;
        }

        .profile-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2.5rem;
            width: 100%;
        }

        @media (max-width: 700px) { .profile-grid { grid-template-columns: 1fr; } }

        /* ── Cards ── */
        .card {
            background: rgba(13,31,58,0.92);
            border-radius: 20px;
            padding: 2.5rem 3rem;
            border: 1px solid rgba(25,118,210,0.18);
            box-shadow: 0 8px 30px rgba(0,0,0,0.3);
        }

        .card h3 {
            color: #1976D2;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.05rem;
            padding-bottom: 0.8rem;
            border-bottom: 1px solid rgba(25,118,210,0.15);
        }

        .form-group { margin-bottom: 1.2rem; }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #61B3FA;
            font-size: 0.88rem;
        }

        .form-group label i { margin-right: 7px; color: #1976D2; }

        .form-group input {
            width: 100%;
            padding: 0.8rem 1rem;
            border-radius: 12px;
            border: 1px solid rgba(25,118,210,0.25);
            background: rgba(10,22,40,0.8);
            color: #fff;
            font-size: 0.93rem;
            font-family: inherit;
            transition: all 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #1976D2;
            box-shadow: 0 0 10px rgba(25,118,210,0.2);
        }

        .form-group input.error-field { border-color: #ff4444; }

        .error-msg { color: #ff6b6b; font-size: 0.8rem; margin-top: 4px; display: block; }

        .btn-submit {
            width: 100%;
            padding: 0.85rem;
            background: linear-gradient(135deg, #1976D2, #1565C0);
            color: #fff;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.95rem;
            font-family: inherit;
            margin-top: 0.5rem;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(25,118,210,0.35);
        }

        /* ── Alert ── */
        .alert {
            padding: 0.8rem 1.2rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9rem;
        }

        .alert-success {
            background: rgba(0,200,100,0.12);
            border: 1px solid rgba(0,200,100,0.35);
            color: #4cff9a;
        }

        .alert-error {
            background: rgba(255,68,68,0.12);
            border: 1px solid rgba(255,68,68,0.35);
            color: #ff6b6b;
        }

        /* ── Info card ── */
        .info-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }

        .info-row:last-child { border-bottom: none; }

        .info-row .icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: rgba(25,118,210,0.12);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1976D2;
            flex-shrink: 0;
        }

        .info-row .label { font-size: 0.78rem; color: #A7A9AC; }
        .info-row .value { font-size: 0.93rem; font-weight: 500; }

        /* ── Password strength ── */
        .strength-bar {
            height: 4px;
            border-radius: 2px;
            background: rgba(255,255,255,0.1);
            margin-top: 6px;
            overflow: hidden;
        }

        .strength-fill {
            height: 100%;
            border-radius: 2px;
            transition: width 0.3s, background 0.3s;
            width: 0%;
        }

        .strength-text { font-size: 0.75rem; margin-top: 4px; }

        footer {
            text-align: center;
            padding: 1.5rem;
            border-top: 1px solid rgba(25,118,210,0.1);
            color: #A7A9AC;
            font-size: 0.85rem;
            margin-top: 2rem;
        }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; width: 100%; }
            .top-bar { padding: 0.8rem 1rem; }
            .btn-nav span { display: none; }
        }

        /* ── Top-bar style ── */
        .navbar-logo { display:flex; flex-direction:column; line-height:1.2; }
        .navbar-logo strong { font-size:1rem; font-weight:800; color:#61B3FA; letter-spacing:0.05em; }
        .navbar-logo span { font-size:0.62rem; color:rgba(255,255,255,0.75); letter-spacing:0.08em; }
        .btn-top, .btn-nav.btn-home {
            background: transparent !important;
            color: #fff !important;
            padding: 0.35rem 0.8rem !important;
            border-radius: 20px !important;
            text-decoration: none !important;
            font-size: 0.85rem !important;
            font-weight: 500 !important;
            border: none !important;
            transition: background 0.2s !important;
            white-space: nowrap;
        }
        .btn-top:hover, .btn-nav.btn-home:hover { background: rgba(255,255,255,0.12) !important; }
        /* Bouton Mon Profil */
        .btn-admin-profile, .btn-admin-active {
            display: inline-flex !important; align-items: center !important; gap: 8px !important;
            background: rgba(255,255,255,0.1) !important; color: #fff !important;
            border: 1px solid rgba(255,255,255,0.18) !important;
            padding: 0.3rem 1rem 0.3rem 0.4rem !important;
            border-radius: 25px !important; font-size: 0.88rem !important;
            cursor: pointer !important; font-weight: 500 !important;
            transition: all 0.2s !important; text-decoration: none !important;
        }
        .btn-admin-profile:hover, .btn-admin-active:hover {
            background: rgba(255,255,255,0.22) !important;
            box-shadow: none !important;
        }
        .btn-admin-profile .admin-avatar-btn,
        .btn-admin-active .admin-avatar-btn {
            width:30px; height:30px; border-radius:50%; overflow:hidden;
            display:flex; align-items:center; justify-content:center;
            background:rgba(25,118,210,0.35); border:2px solid rgba(97,179,250,0.5); flex-shrink:0;
        }
        .btn-admin-profile .admin-avatar-btn img,
        .btn-admin-active .admin-avatar-btn img { width:100%; height:100%; object-fit:cover; }
        .btn-admin-profile .admin-avatar-btn i,
        .btn-admin-active .admin-avatar-btn i { font-size:0.85rem; color:#61B3FA; }
        /* Bouton Admin transparent rouge */
        .btn-admin-plain {
            display: inline-flex !important; align-items: center !important; gap: 6px !important;
            background: transparent !important; color: #E74C3C !important;
            border: 1px solid rgba(231,76,60,0.45) !important;
            padding: 0.4rem 1.1rem !important; border-radius: 25px !important;
            font-size: 0.9rem !important; font-weight: 700 !important;
            text-decoration: none !important; transition: all 0.2s !important;
            letter-spacing: 0.02em !important;
        }
        .btn-admin-plain:hover {
            background: rgba(231,76,60,0.12) !important;
            border-color: #E74C3C !important; color: #FF6B6B !important;
        }
        .btn-theme-toggle {
            width:34px; height:34px; border-radius:50%;
            background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.22);
            color:#fff; font-size:0.92rem;
            display:inline-flex; align-items:center; justify-content:center;
            cursor:pointer; transition:all 0.25s; flex-shrink:0;
        }
        .btn-theme-toggle:hover { background:rgba(255,255,255,0.18); }
        body.light-mode { background:linear-gradient(135deg,#EDF2F7 0%,#DBEAFE 100%) !important; color:#1A2844 !important; }
        body.light-mode .section-card, body.light-mode .form-card,
        body.light-mode .stat-card { background:rgba(255,255,255,.95) !important; }
        body.light-mode td { color:#1A2844 !important; }

    </style>
</head>
<body>
<div style="display:flex; width:100%; overflow-x:hidden;">

    <!-- Sidebar Gauche -->
    <div class="sidebar" id="sidebar">
        <div>
            <div class="logo">
                <img src="<?= BASE_URL ?>uploads/photos/photo.png" alt="EcoRide Logo" style="width:60px;height:60px;object-fit:contain;background:transparent;vertical-align:middle;">
                <h2>EcoRide</h2>
                <div style="font-size:0.62rem;letter-spacing:0.2em;color:rgba(255,255,255,.6);text-transform:uppercase;margin-top:4px;">Administration</div>
            </div>
            <div style="height:1px;background:rgba(255,255,255,.15);margin-bottom:1.2rem;"></div>
            <div style="font-size:0.62rem;font-weight:700;letter-spacing:0.18em;color:rgba(255,255,255,.55);text-transform:uppercase;padding:0 1rem 0.5rem;">Gestion</div>
            <nav>
                <ul>
                    <li>
                        <a href="<?= BASE_URL ?>controllers/AdminController.php?action=dashboard">
                            <i class="fas fa-gauge-high"></i> Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>controllers/AdminController.php?action=dashboard#passagers">
                            <i class="fas fa-users"></i> Passagers
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>controllers/AdminController.php?action=dashboard#trajets">
                            <i class="fas fa-route"></i> Trajets
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>controllers/AdminController.php?action=dashboard">
                            <i class="fas fa-map-pin"></i> Destinations
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>controllers/AdminController.php?action=dashboard#evenements">
                            <i class="fas fa-calendar-alt"></i> Événements
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>controllers/AdminController.php?action=dashboard#reclamations">
                            <i class="fas fa-triangle-exclamation"></i> Réclamations
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>controllers/AdminController.php?action=dashboard#vehicules">
                            <i class="fas fa-car"></i> Véhicules
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>controllers/AdminController.php?action=dashboard#objets_perdus">
                            <i class="fas fa-magnifying-glass"></i> Lost &amp; Found
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

    <!-- ══ Main Content ══ -->
    <div class="main-content" id="mainContent">

        <!-- ── Page Content ── -->
        <div class="page-content">

        <!-- ── Navbar / Top-bar ── -->
        <div class="top-bar">
            <div class="navbar-logo">
                <strong>ECO RIDE</strong>
                <span>Covoiturage Intelligent</span>
            </div>
            <div class="top-bar-right">
                    <a href="<?= BASE_URL ?>controllers/UserController.php?action=showLoginForm#hero" class="btn-top">Accueil</a>
                    <a href="<?= BASE_URL ?>controllers/UserController.php?action=showLoginForm#evenements" class="btn-top">Événements</a>
                    <a href="<?= BASE_URL ?>controllers/UserController.php?action=showLoginForm#sponsors" class="btn-top">Sponsors</a>
                    <a href="<?= BASE_URL ?>controllers/UserController.php?action=covoiturage" class="btn-top">Covoiturage</a>
                    <a href="<?= BASE_URL ?>controllers/UserController.php?action=lostFound" class="btn-top">Lost &amp; Found</a>
                    <a href="<?= BASE_URL ?>controllers/AdminController.php?action=showProfile" class="btn-admin-profile">
                        <div class="admin-avatar-btn">
                            <?php if (!empty($_SESSION['admin_photo'])): ?>
                                <img src="<?= BASE_URL ?>uploads/photos/<?= htmlspecialchars($_SESSION['admin_photo']) ?>" alt="">
                            <?php else: ?>
                                <i class="fas fa-user-shield"></i>
                            <?php endif; ?>
                        </div>
                        Profil
                    </a>
                    <a href="<?= BASE_URL ?>controllers/AdminController.php?action=dashboard" class="btn-admin-plain">Admin</a>
                    <button class="btn-theme-toggle" onclick="toggleTheme()" title="Mode sombre / clair">
                        <i class="fas fa-moon themeIcon"></i>
                    </button>
            </div>
        </div>

            <div class="profile-container">

                <!-- Hero -->
                <div class="profile-hero">
                    <div class="hero-greeting">Espace Administrateur</div>
                    <div class="hero-title">
                        Bonjour, <span><?= htmlspecialchars(explode(' ', $_SESSION['admin_nom'] ?? 'Admin')[0]) ?></span> 👋
                    </div>
                    <div class="hero-sub">Gérez vos informations personnelles et votre sécurité</div>
                </div>

                <!-- Avatar -->
                <div class="avatar-section">
                    <form method="POST"
                          action="<?= BASE_URL ?>controllers/AdminController.php?action=uploadAdminPhoto"
                          enctype="multipart/form-data" id="avatarForm">
                        <div class="avatar-circle">
                            <?php if (!empty($admin['photo'])): ?>
                                <img src="<?= BASE_URL ?>uploads/photos/<?= htmlspecialchars($admin['photo']) ?>" alt="Photo admin">
                            <?php else: ?>
                                <i class="fas fa-user-shield"></i>
                            <?php endif; ?>
                            <label class="avatar-upload-overlay" title="Changer la photo">
                                <i class="fas fa-camera"></i>
                                <input type="file" name="admin_photo" accept="image/jpeg,image/png,image/gif,image/webp"
                                       onchange="document.getElementById('avatarForm').submit()">
                            </label>
                        </div>
                    </form>
                    <div class="avatar-name"><?= htmlspecialchars($_SESSION['admin_nom'] ?? 'Admin') ?></div>
                    <div class="avatar-badge"><i class="fas fa-shield-alt"></i> Administrateur</div>
                </div>

                <?php if ($profileSuccess): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($profileSuccess) ?>
                    </div>
                <?php endif; ?>

                <div class="profile-grid">

                    <!-- Modifier infos -->
                    <div class="card">
                        <h3><i class="fas fa-user-edit"></i> Modifier mes informations</h3>

                        <?php if (!empty($profileErrors['global'])): ?>
                            <div class="alert alert-error">
                                <i class="fas fa-exclamation-circle"></i>
                                <?= htmlspecialchars($profileErrors['global']) ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="<?= BASE_URL ?>controllers/AdminController.php?action=updateAdminProfile">
                            <div class="form-group">
                                <label><i class="fas fa-user"></i> Nom complet *</label>
                                <input type="text" name="nom"
                                       value="<?= htmlspecialchars($profileOld['nom'] ?? $admin['nom'] ?? $_SESSION['admin_nom'] ?? '') ?>"
                                       <?php if (isset($profileErrors['nom'])): ?>class="error-field"<?php endif; ?>>
                                <?php if (isset($profileErrors['nom'])): ?>
                                    <span class="error-msg"><?= htmlspecialchars($profileErrors['nom']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-envelope"></i> Email</label>
                                <input type="email" name="email"
                                       value="<?= htmlspecialchars($admin['email'] ?? $_SESSION['admin_email'] ?? '') ?>"
                                       readonly
                                       style="opacity:0.65;cursor:not-allowed;background:rgba(10,22,40,0.5);">
                            </div>
                            <button type="submit" class="btn-submit">
                                <i class="fas fa-save"></i> Enregistrer les modifications
                            </button>
                        </form>
                    </div>

                    <!-- Changer mot de passe -->
                    <div class="card">
                        <h3><i class="fas fa-lock"></i> Changer le mot de passe</h3>
                        <form method="POST" action="<?= BASE_URL ?>controllers/AdminController.php?action=changeAdminPassword">
                            <div class="form-group">
                                <label><i class="fas fa-key"></i> Mot de passe actuel *</label>
                                <input type="password" name="current_password" placeholder="••••••••">
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-lock"></i> Nouveau mot de passe *</label>
                                <input type="password" name="new_password" id="newPwd" placeholder="••••••••"
                                       oninput="checkStrength(this.value)">
                                <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                                <div class="strength-text" id="strengthText" style="color:#A7A9AC;"></div>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-check-circle"></i> Confirmer le mot de passe *</label>
                                <input type="password" name="confirm_password" placeholder="••••••••">
                            </div>
                            <button type="submit" class="btn-submit"
                                    style="background:rgba(160,170,180,0.15);border:1px solid rgba(160,170,180,0.35);color:#a0aab4;">
                                <i class="fas fa-shield-alt"></i> Mettre à jour le mot de passe
                            </button>
                        </form>
                    </div>

                </div><!-- /profile-grid -->

                <!-- Infos compte -->
                <div class="card" style="margin-top:2rem;">
                    <h3><i class="fas fa-info-circle"></i> Informations du compte</h3>
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:0.5rem;">
                        <div class="info-row">
                            <div class="icon"><i class="fas fa-id-badge"></i></div>
                            <div>
                                <div class="label">ID Administrateur</div>
                                <div class="value">#<?= htmlspecialchars($_SESSION['admin_id'] ?? '') ?></div>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="icon"><i class="fas fa-shield-alt"></i></div>
                            <div>
                                <div class="label">Rôle</div>
                                <div class="value">Administrateur</div>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="icon"><i class="fas fa-envelope"></i></div>
                            <div>
                                <div class="label">Email</div>
                                <div class="value"><?= htmlspecialchars($_SESSION['admin_email'] ?? '') ?></div>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="icon"><i class="fas fa-image"></i></div>
                            <div>
                                <div class="label">Photo de profil</div>
                                <div class="value">
                                    <?= !empty($admin['photo'])
                                        ? '<span style="color:#4cff9a">Définie</span>'
                                        : '<span style="color:#A7A9AC">Non définie</span>' ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div><!-- /profile-container -->
        </div><!-- /page-content -->

        <footer>
            &copy; <?= date('Y') ?> EcoRide Administration &mdash; Tous droits réservés
        </footer>

    </div><!-- /main-content -->
</div>

<script>
// Toggle sidebar
const sidebar    = document.getElementById('sidebar');
const mainContent = document.getElementById('mainContent');

document.getElementById('menuToggle').addEventListener('click', () => {
    sidebar.classList.toggle('sidebar-hidden');
    mainContent.style.marginLeft =
        sidebar.classList.contains('sidebar-hidden') ? '0' : '240px';
});

// Password strength
function checkStrength(pwd) {
    const fill = document.getElementById('strengthFill');
    const text = document.getElementById('strengthText');
    let score = 0;
    if (pwd.length >= 8) score++;
    if (/[A-Z]/.test(pwd)) score++;
    if (/[0-9]/.test(pwd)) score++;
    if (/[^A-Za-z0-9]/.test(pwd)) score++;

    const levels = [
        { pct: '0%',   color: 'transparent', label: '' },
        { pct: '25%',  color: '#ff4444',      label: 'Très faible' },
        { pct: '50%',  color: '#ffa500',      label: 'Moyen' },
        { pct: '75%',  color: '#1976D2',      label: 'Fort' },
        { pct: '100%', color: '#4cff9a',      label: 'Très fort' },
    ];
    fill.style.width      = levels[score].pct;
    fill.style.background = levels[score].color;
    text.textContent      = levels[score].label;
    text.style.color      = levels[score].color;
}

    function toggleTheme() {
        document.body.classList.toggle('light-mode');
        const isLight = document.body.classList.contains('light-mode');
        document.querySelectorAll('.themeIcon').forEach(i => {
            i.className = isLight ? 'fas fa-sun themeIcon' : 'fas fa-moon themeIcon';
        });
        localStorage.setItem('ecoride_theme', isLight ? 'light' : 'dark');
    }
    (function() {
        if (localStorage.getItem('ecoride_theme') === 'light') {
            document.body.classList.add('light-mode');
            document.querySelectorAll('.themeIcon').forEach(i => { i.className = 'fas fa-sun themeIcon'; });
        }
    })();

    </script>

</body>
</html>
