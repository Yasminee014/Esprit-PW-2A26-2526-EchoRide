<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config.php';
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['is_admin'])) {
    header('Location: ../../Controller/AdminController.php?action=showLogin');
    exit();
}

$profileErrors  = $_SESSION['admin_profile_errors']    ?? [];
$profileOld     = $_SESSION['admin_profile_old_input'] ?? [];
$profileSuccess = $_SESSION['admin_profile_success']   ?? $_SESSION['profile_success'] ?? '';
$profileError   = $_SESSION['profile_error'] ?? '';
unset($_SESSION['admin_profile_errors'], $_SESSION['admin_profile_old_input'],
      $_SESSION['admin_profile_success'], $_SESSION['profile_success'], $_SESSION['profile_error']);
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

        /* ── Main content ── */
        .main-content {
            margin-left: 280px;
            width: calc(100% - 280px);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

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

        /* D�connexion */
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
            .main { margin-left: 0; }
        }

        /* Bouton Mon Profil */
        /* Bouton Admin transparent rouge */
        body.light-mode { background:linear-gradient(135deg,#EDF2F7 0%,#DBEAFE 100%) !important; color:#1A2844 !important; }
        body.light-mode .stat-card { background:rgba(255,255,255,.95) !important; }
        body.light-mode td { color:#1A2844 !important; }

        /* ── Sidebar identique à admin.php ── */
        /* ========== SIDEBAR - NOUVEAU DEGRADE ========== */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #2F76BC 0%, #1E5EA5 50%, #174C8A 100%);
            padding: 1.5rem 0;
            position: fixed;
            top: 0; left: 0;
            height: 100vh;
            overflow-y: auto;
            z-index: 100;
            box-shadow: 4px 0 20px rgba(0,0,0,0.2);
            display: flex;
            flex-direction: column;
        }
        .sidebar-header { padding:1.5rem; border-bottom:1px solid rgba(255,255,255,.15); margin-bottom:1.5rem; text-align:center; }
        .sidebar-header .logo { display:flex; flex-direction:column; align-items:center; gap:6px; text-decoration:none; }
        .sidebar-header .logo-img { width:80px; height:80px; object-fit:contain; filter:drop-shadow(0 4px 14px rgba(97,179,250,.5)); margin-bottom:4px; }
        .sidebar-header .logo-text { font-size:1.3rem; font-weight:700; color:#A9D6FF; letter-spacing:1px; font-family:'Poppins',sans-serif; }
        .sidebar-header .logo-tagline { font-size:.65rem; color:#BFD8F1; margin-top:2px; letter-spacing:2px; opacity:.85; }
        .nav-section { color:#CFE6FF; font-size:.7rem; text-transform:uppercase; letter-spacing:2px; padding:.75rem 1.5rem; margin-top:.5rem; opacity:.8; font-weight:600; }
        .sidebar nav ul { list-style:none; }
        .sidebar nav ul li { margin-bottom:.25rem; }
        .sidebar nav ul li a { display:flex; align-items:center; gap:12px; padding:.7rem 1.5rem; color:#EAF4FF; text-decoration:none; transition:all .3s; font-size:.85rem; margin:0 .5rem; border-radius:10px; font-weight:500; }
        .sidebar nav ul li a i { width:22px; color:#EAF4FF; font-size:1rem; }
        .sidebar nav ul li a:hover { background:rgba(111,168,220,.3); color:#fff; transform:translateX(5px); }
        .sidebar nav ul li a.active { background:linear-gradient(135deg,#6FA8DC,#8FC1F5); color:#fff; box-shadow:0 4px 12px rgba(111,168,220,.3); }
        .sidebar-footer { margin-top:auto; padding:1rem 1.5rem; border-top:1px solid rgba(255,255,255,.1); }
        .sidebar-footer a { display:flex; align-items:center; gap:12px; color:#FFCDD2; text-decoration:none; font-size:.85rem; padding:.5rem 0; transition:all .3s; }
        .sidebar-footer a:hover { color:#FF8A80; transform:translateX(5px); }
        .wrap { display:flex; min-height:100vh; }
        .main { flex:1; margin-left:280px; padding:1.6rem; position:relative; z-index:1; }
        /* ========== HEADER STYLE ========== */
        .admin-header { background:linear-gradient(90deg,#071C2F,#0A2A47,#0D355B); padding:1rem 2rem; display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem; border-radius:12px; border-bottom:1px solid rgba(255,255,255,.08); flex-wrap:wrap; gap:1rem; }
        .admin-logo { display:flex; flex-direction:column; }
        .admin-logo .logo-eco { font-size:1.5rem; font-weight:700; letter-spacing:1px; }
        .admin-logo .logo-eco span:first-child { color:#4EA3FF; }
        .admin-logo .logo-eco span:last-child { color:#6BB8FF; }
        .admin-logo .logo-tagline { font-size:.65rem; color:#A8C1D9; margin-top:2px; }
        .admin-nav { display:flex; gap:.5rem; align-items:center; flex-wrap:wrap; }
        .admin-nav a { text-decoration:none; padding:.5rem 1.2rem; border-radius:30px; font-size:.9rem; font-weight:500; transition:all .3s; background:transparent; color:#CFE6FF; }
        .admin-nav a:hover { background:rgba(255,255,255,.1); color:#fff; }
        .admin-nav .profile-btn { background:#003050; color:#fff; display:flex; align-items:center; gap:10px; padding:.5rem 1.2rem; }
        .profile-avatar { width:36px; height:36px; background:#5FA8FF; border-radius:50%; display:flex; align-items:center; justify-content:center; overflow:hidden; flex-shrink:0; border:2px solid rgba(255,255,255,.3); }
        .profile-avatar img { width:100%; height:100%; object-fit:cover; border-radius:50%; }
        .profile-avatar i { font-size:.9rem; color:#fff; }
        .profile-avatar i { font-size:.8rem; color:#fff; }
        .admin-nav .admin-btn { background:rgba(231,76,60,.2); border:1px solid rgba(231,76,60,.4); color:#e74c3c; }
        .theme-btn { background:rgba(255,255,255,.1); border:none; width:38px; height:38px; border-radius:50%; cursor:pointer; font-size:1.1rem; transition:all .3s; display:flex; align-items:center; justify-content:center; color:#fff; }
        .theme-btn:hover { background:rgba(255,255,255,.2); transform:rotate(15deg); }
        @media (max-width:768px) { .sidebar { transform:translateX(-100%); } .main { margin-left:0; } }
    </style>
</head>
<body>
<div class="wrap">

<!-- SIDEBAR - NOUVEAU DEGRADE -->
<aside class="sidebar">
    <div class="sidebar-header">
        <a href="<?= BASE_URL ?>View/backoffice/admin.php" class="logo">
            <img src="<?= BASE_URL ?>assets/images/photo.png" alt="EcoRide Logo" class="logo-img">
            <div class="logo-text">EcoRide</div>
            <div class="logo-tagline">ADMINISTRATION</div>
        </a>
    </div>
    
    <div class="nav-section">GESTION</div>
    <nav>
        <ul>
            <li><a href="<?= BASE_URL ?>View/backoffice/admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="<?= BASE_URL ?>View/backoffice/admin_trajet.php?page=passagers"><i class="fas fa-users"></i> Passagers</a></li>
            <li><a href="<?= BASE_URL ?>View/backoffice/admin_trajet.php?page=trajets"><i class="fas fa-route"></i> Trajets</a></li>
            <li><a href="<?= BASE_URL ?>View/backoffice/admin_trajet.php?page=destinations"><i class="fas fa-map-pin"></i> Destinations</a></li>
            <li><a href="<?= BASE_URL ?>View/backoffice/dashboard_event.php"><i class="fas fa-calendar-alt"></i> Événements</a></li>
            <li><a href="<?= BASE_URL ?>View/backoffice/admin_reclamations.php"><i class="fas fa-exclamation-triangle"></i> R&eacute;clamations</a></li>
            <li><a href="<?= BASE_URL ?>View/backoffice/admin.php"><i class="fas fa-car"></i> V&eacute;hicules</a></li>
            <li><a href="<?= BASE_URL ?>View/backoffice/lostfound_admin.php"><i class="fas fa-search-location"></i> Objets perdus</a></li>
        </ul>
    </nav>
    
    <div class="sidebar-footer">
        <a href="<?= BASE_URL ?>Controller/AdminController.php?action=logout"><i class="fas fa-sign-out-alt"></i> D&eacute;connexion</a>
    </div>
</aside>

<main class="main">

<!-- HEADER -->
<div class="admin-header">
    <div class="admin-logo">
        <div class="logo-eco">
            <span>ECO</span> <span>RIDE</span>
        </div>
        <div class="logo-tagline">Covoiturage Intelligent</div>
    </div>
    <div class="admin-nav">
        <a href="<?= BASE_URL ?>View/frontoffice/tous_les_trajets.php">Voir site</a>
        
        <!-- BOUTON PROFIL -->
        <a href="../../Controller/AdminController.php?action=showProfile" class="profile-btn">
            <div class="profile-avatar">
                <?php if (!empty($admin['photo'])): ?>
                    <img src="<?= BASE_URL ?>uploads/photos/<?= htmlspecialchars($admin['photo']) ?>" alt="Photo admin" onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='flex';">
                    <i class="fas fa-user-shield" style="display:none"></i>
                <?php elseif (!empty($_SESSION['admin_photo'])): ?>
                    <img src="<?= BASE_URL ?>uploads/photos/<?= htmlspecialchars($_SESSION['admin_photo']) ?>" alt="Photo admin" onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='flex';">
                    <i class="fas fa-user-shield" style="display:none"></i>
                <?php else: ?>
                    <i class="fas fa-user-shield"></i>
                <?php endif; ?>
            </div>
            <span>Profil</span>
        </a>
        
        <a href="<?= BASE_URL ?>View/backoffice/admin.php" class="admin-btn">Admin</a>
        <button class="theme-btn" onclick="toggleTheme()" id="themeBtn">
            <i class="fas fa-moon"></i>
        </button>
    </div>
</div>
            <div class="profile-container">

                <!-- Hero -->
                <div class="profile-hero">
                    <div class="hero-greeting">Espace Administrateur</div>
                    <div class="hero-title">
                        Bonjour, <span><?= htmlspecialchars(explode(' ', $_SESSION['admin_nom'] ?? 'Admin')[0]) ?></span> ?
                    </div>
                    <div class="hero-sub">G�rez vos informations personnelles et votre s�curit�</div>
                </div>

                <!-- Avatar -->
                <div class="avatar-section">
                    <form method="POST"
                          action="<?= BASE_URL ?>Controller/AdminController.php?action=uploadAdminPhoto"
                          enctype="multipart/form-data" id="avatarForm">
                        <div class="avatar-circle">
                            <?php if (!empty($admin['photo'])): ?>
                                <img src="<?= BASE_URL ?>uploads/photos/<?= htmlspecialchars($admin['photo']) ?>" onerror="this.onerror=null;this.src='<?= BASE_URL ?>serve_image.php?type=admin&id=<?= $admin['id'] ?>'" alt="Photo admin">
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
                <?php if (!empty($profileError)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($profileError) ?>
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

                        <form method="POST" action="<?= BASE_URL ?>Controller/AdminController.php?action=updateAdminProfile">
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
                        <form method="POST" action="<?= BASE_URL ?>Controller/AdminController.php?action=changeAdminPassword">
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
                                        ? '<span style="color:#4cff9a">D�finie</span>'
                                        : '<span style="color:#A7A9AC">Non d�finie</span>' ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div><!-- /profile-container -->
        </div><!-- /page-content -->

        <footer>
            &copy; <?= date('Y') ?> EcoRide Administration &mdash; Tous droits r�serv�s
        </footer>

</main>
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
        { pct: '25%',  color: '#ff4444',      label: 'Tr�s faible' },
        { pct: '50%',  color: '#ffa500',      label: 'Moyen' },
        { pct: '75%',  color: '#1976D2',      label: 'Fort' },
        { pct: '100%', color: '#4cff9a',      label: 'Tr�s fort' },
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

<?php require_once __DIR__ . '/ai_helper_widget.php'; ?>
</body>
</html>
