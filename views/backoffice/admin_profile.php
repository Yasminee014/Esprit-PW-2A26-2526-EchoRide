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
            margin-left: 240px;
            width: calc(100% - 240px);
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
            .main-content { margin-left: 0; width: 100%; }
        }

        /* Bouton Mon Profil */
        /* Bouton Admin transparent rouge */
        body.light-mode { background:linear-gradient(135deg,#EDF2F7 0%,#DBEAFE 100%) !important; color:#1A2844 !important; }
        body.light-mode .stat-card { background:rgba(255,255,255,.95) !important; }
        body.light-mode td { color:#1A2844 !important; }

    </style>
    <?php require_once __DIR__ . '/partials/partials.php'; sidebar_dashboard('dashboard'); ?>
<?php render_nav_css(); ?>
</head>
<body>
<div style="display:flex; width:100%; overflow-x:hidden;">

    <?php require_once __DIR__ . '/partials/partials.php'; sidebar_dashboard('dashboard'); ?>

    <!-- ══ Main Content ══ -->
    <div class="main-content" id="mainContent">

        <!-- ── Page Content ── -->
        <div class="page-content">

        <?php navbar_dashboard(); ?>

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

<?php require_once __DIR__ . '/ai_helper_widget.php'; ?>
</body>
</html>
