<?php
/**
 * navbar.php — Barre de navigation partagée (frontoffice)
 * Projet : EcoRide (projetG)
 * Inclure via : <?php include __DIR__ . '/navbar.php'; ?>
 *
 * Requiert que BASE_URL soit défini (via config.php)
 * Requiert que la session soit démarrée
 */

// Sécurité : ne pas inclure sans session active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Page courante pour marquer l'onglet actif
$currentPage = basename($_SERVER['PHP_SELF']);
$currentAction = $_GET['action'] ?? '';
// Détection dashboard : via filename OU via action=dashboard
$isDashboard = ($currentPage === 'dashboard.php') || ($currentAction === 'dashboard');
?>
<style>
/* ========================================================
   NAVBAR ECORIDE — FRONTOFFICE
   ======================================================== */

/* ── Google Translate — masquer la barre du haut ── */
.goog-te-banner-frame,
.goog-te-banner-frame.skiptranslate {
    display: none !important;
    height: 0 !important;
}
body { top: 0 !important; position: relative !important; }

#google_translate_element {
    position: fixed !important;
    bottom: 20px !important;
    right: 20px !important;
    z-index: 9999 !important;
}
.goog-te-gadget-simple {
    background: linear-gradient(135deg, #1976D2, #0F3B6E) !important;
    border: 1px solid rgba(97,179,250,0.3) !important;
    border-radius: 30px !important;
    padding: 6px 14px !important;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
    font-size: 13px !important;
    box-shadow: 0 4px 15px rgba(25,118,210,0.4) !important;
    cursor: pointer !important;
}
.goog-te-gadget-simple span,
.goog-te-gadget-simple a,
.goog-te-gadget-simple a span {
    color: white !important;
    font-family: inherit !important;
}
.goog-te-gadget-simple img,
.goog-te-gadget-simple .goog-te-gadget-icon { display: none !important; }

/* ========== NAVBAR ========== */
.navbar-modern {
    background: linear-gradient(135deg, #1976D2 0%, #0F3B6E 100%);
    padding: 0.9rem 4%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 1000;
    flex-wrap: wrap;
    box-shadow: 0 2px 12px rgba(0,0,0,0.2);
}

/* Logo */
.navbar-modern .logo {
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 10px;
    text-decoration: none;
}
.navbar-modern .logo-img {
    width: 46px;
    height: 46px;
    object-fit: contain;
    filter: drop-shadow(0 2px 6px rgba(0,0,0,0.4));
    transition: transform 0.3s;
}
.navbar-modern .logo:hover .logo-img { transform: scale(1.08) rotate(-3deg); }
.navbar-modern .logo-text {
    font-size: 1.4rem;
    font-weight: 700;
    letter-spacing: 1px;
    color: white;
    line-height: 1.3;
}
.navbar-modern .logo-tagline {
    font-size: 0.62rem;
    color: rgba(255,255,255,0.7);
    letter-spacing: 0.5px;
}

/* Toggle mobile */
.menu-toggle {
    background: rgba(255,255,255,0.15);
    border: none;
    color: white;
    font-size: 1.1rem;
    padding: 0.45rem 0.9rem;
    border-radius: 25px;
    cursor: pointer;
    display: none;
    transition: all 0.3s;
}
.menu-toggle:hover { background: rgba(255,255,255,0.25); }

/* Liste de liens */
.nav-links {
    display: flex;
    gap: 0.6rem;
    list-style: none;
    margin: 0;
    padding: 0;
    align-items: center;
    flex-wrap: wrap;
}

/* Liens standard */
.nav-links li a {
    text-decoration: none;
    padding: 0.45rem 1.1rem;
    border-radius: 30px;
    font-size: 0.88rem;
    font-weight: 500;
    transition: all 0.25s;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: transparent;
    color: white;
    border: none;
    cursor: pointer;
}
.nav-links li a:hover { background: rgba(255,255,255,0.18); transform: translateY(-2px); }
.nav-links li a.active {
    background: #0A1628;
    color: white;
    box-shadow: 0 2px 8px rgba(10,22,40,0.35);
}

/* Bouton Admin */
.nav-links .admin-btn {
    background: rgba(231,76,60,0.18);
    border: 1px solid rgba(231,76,60,0.4);
    color: #e74c3c;
    padding: 0.45rem 1.1rem;
    border-radius: 30px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    font-size: 0.88rem;
    font-weight: 500;
    transition: all 0.25s;
}
.nav-links .admin-btn:hover {
    background: rgba(231,76,60,0.32);
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(231,76,60,0.2);
}

/* ========== BOUTON PROFIL ========== */
.profile-btn {
    display: flex;
    align-items: center;
    gap: 9px;
    background: rgba(255,255,255,0.12);
    border: 1px solid rgba(255,255,255,0.2);
    padding: 0.38rem 1rem 0.38rem 0.5rem;
    border-radius: 30px;
    cursor: pointer;
    transition: all 0.25s;
    color: #FFFFFF;
    font-size: 0.88rem;
    font-weight: 500;
}
.profile-btn:hover { background: rgba(255,255,255,0.24); transform: translateY(-2px); }

.profile-avatar {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(25,118,210,0.35);
    border: 2px solid rgba(97,179,250,0.5);
    flex-shrink: 0;
}
.profile-avatar img { width: 100%; height: 100%; object-fit: cover; }
.profile-avatar i { font-size: 0.82rem; color: #61B3FA; }
.profile-btn .chevron { font-size: 0.68rem; opacity: 0.75; margin-left: 2px; }

/* ========== BOUTON MODE SOMBRE/CLAIR ========== */
.theme-btn {
    background: rgba(255,255,255,0.12);
    border: 1px solid rgba(255,255,255,0.2);
    color: white;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 1rem;
    transition: all 0.25s;
    display: flex;
    align-items: center;
    justify-content: center;
}
.theme-btn:hover { background: rgba(255,255,255,0.28); transform: rotate(15deg); }

/* ========== MENU DÉROULANT PROFIL ========== */
.profile-dropdown { position: relative; }
.dropdown-menu-nav {
    display: none;
    position: absolute;
    top: calc(100% + 10px);
    right: 0;
    width: 270px;
    background: #0D1F3A;
    border: 1px solid rgba(25,118,210,0.3);
    border-radius: 16px;
    box-shadow: 0 12px 40px rgba(0,0,0,0.45);
    z-index: 1001;
    overflow: hidden;
    backdrop-filter: blur(10px);
}
.dropdown-menu-nav.show { display: block; animation: navDropIn 0.2s ease; }

@keyframes navDropIn {
    from { opacity: 0; transform: translateY(-8px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* En-tête dropdown */
.dropdown-nav-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 1rem 1.2rem 0.85rem;
    background: #163A5C;
    border-bottom: 1px solid rgba(255,255,255,0.07);
}
.dropdown-nav-avatar {
    width: 40px; height: 40px;
    border-radius: 50%;
    background: rgba(25,118,210,0.3);
    border: 2px solid rgba(97,179,250,0.4);
    display: flex; align-items: center; justify-content: center;
    overflow: hidden; flex-shrink: 0;
}
.dropdown-nav-avatar img { width: 100%; height: 100%; object-fit: cover; }
.dropdown-nav-avatar i { font-size: 1.1rem; color: #61B3FA; }
.dropdown-nav-name { font-size: 0.9rem; font-weight: 600; color: #CFE6FF; display: block; }
.dropdown-nav-role { font-size: 0.72rem; color: rgba(97,179,250,0.85); display: block; }

/* Liens dropdown */
.dropdown-nav-links { padding: 0.4rem 0; }
.dropdown-nav-links a {
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 0.68rem 1.1rem;
    margin: 0 0.4rem;
    border-radius: 10px;
    color: #C8D8F0;
    text-decoration: none;
    font-size: 0.84rem;
    transition: all 0.18s;
    border-bottom: none;
}
.dropdown-nav-links a i { width: 18px; color: #1976D2; font-size: 0.95rem; }
.dropdown-nav-links a:hover { background: rgba(255,255,255,0.06); color: #fff; }

.dropdown-nav-divider { height: 1px; background: rgba(255,255,255,0.07); margin: 0.3rem 0; }

.dropdown-nav-logout {
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 0.68rem 1.1rem;
    margin: 0 0.4rem 0.4rem;
    border-radius: 10px;
    color: #ff6b6b !important;
    text-decoration: none;
    font-size: 0.84rem;
    transition: all 0.18s;
}
.dropdown-nav-logout i { width: 18px; color: #ff6b6b !important; font-size: 0.95rem; }
.dropdown-nav-logout:hover { background: rgba(220,53,69,0.15) !important; }

/* Light mode overrides */
body.light-mode .navbar-modern { background: linear-gradient(135deg, #1565C0, #0D47A1); }

/* ========== GLOBAL LIGHT MODE STYLES ========== */
body.light-mode {
    background: linear-gradient(135deg, #E3F2FD 0%, #BBDEFB 100%);
    color: #263238;
}

/* Cards and containers in light mode */
body.light-mode .card,
body.light-mode .profile-container,
body.light-mode .dashboard-container {
    background: rgba(255,255,255,0.95);
    color: #263238;
}

/* Form elements in light mode */
body.light-mode input,
body.light-mode select,
body.light-mode textarea {
    background: rgba(255,255,255,0.9);
    border-color: rgba(25,118,210,0.3);
    color: #263238;
}

body.light-mode input:focus,
body.light-mode select:focus,
body.light-mode textarea:focus {
    border-color: #1976D2;
    box-shadow: 0 0 10px rgba(25,118,210,0.2);
}

/* Buttons in light mode */
body.light-mode .btn-submit,
body.light-mode .btn-primary {
    background: linear-gradient(135deg, #1976D2, #1976D2);
    color: white;
}

body.light-mode .btn-submit:hover,
body.light-mode .btn-primary:hover {
    box-shadow: 0 5px 20px rgba(25,118,210,0.3);
}

/* Alerts in light mode */
body.light-mode .alert-success {
    background: rgba(76,175,80,0.15);
    border-color: rgba(76,175,80,0.4);
    color: #2E7D32;
}

body.light-mode .alert-error {
    background: rgba(244,67,54,0.15);
    border-color: rgba(244,67,54,0.4);
    color: #C62828;
}

/* Text colors in light mode */
body.light-mode h1,
body.light-mode h2,
body.light-mode h3,
body.light-mode h4 {
    color: #263238;
}

body.light-mode p,
body.light-mode .info-text {
    color: #546E7A;
}

/* Links and accents in light mode */
body.light-mode a {
    color: #1976D2;
}

body.light-mode .text-primary,
body.light-mode .role-badge {
    color: #1976D2;
}

/* Tables in light mode */
body.light-mode table {
    background: rgba(255,255,255,0.9);
    color: #263238;
}

body.light-mode th,
body.light-mode td {
    border-color: rgba(25,118,210,0.2);
}

/* Footer in light mode */
body.light-mode footer {
    background: rgba(255,255,255,0.9);
    color: #546E7A;
    border-top-color: rgba(25,118,210,0.2);
}

/* ========== RESPONSIVE ========== */
@media (max-width: 860px) {
    .menu-toggle { display: flex; align-items: center; }
    .nav-links {
        display: none;
        width: 100%;
        flex-direction: column;
        margin-top: 0.8rem;
        gap: 0.6rem;
        padding-bottom: 0.5rem;
    }
    .nav-links.show { display: flex; }
    .nav-links li,
    .nav-links .profile-dropdown { width: 100%; }
    .nav-links li a,
    .nav-links .admin-btn,
    .profile-btn,
    .theme-btn {
        width: 100%;
        border-radius: 30px;
        justify-content: center;
    }
    .dropdown-menu-nav { position: static; width: 100%; margin-top: 6px; }
    .navbar-modern { flex-wrap: wrap; position: relative; }
}
</style>

<nav class="navbar-modern">

    <!-- ── Logo ── -->
    <a href="<?= BASE_URL ?>views/frontoffice/dashboard.php" class="logo">
        <img src="<?= BASE_URL ?>uploads/photos/photo.png"
             alt="EcoRide Logo" class="logo-img"
             onerror="this.style.display='none'">
        <div>
            <div class="logo-text">ECO RIDE</div>
            <div class="logo-tagline">Covoiturage Intelligent</div>
        </div>
    </a>

    <!-- ── Burger mobile ── -->
    <button class="menu-toggle" onclick="navToggleMenu()" aria-label="Menu">
        <i class="fas fa-bars"></i>
    </button>

    <!-- ── Liens ── -->
    <ul class="nav-links" id="ecoNavLinks">

        <li>
            <a href="<?= BASE_URL ?>views/frontoffice/dashboard.php"
               <?= $currentPage === 'dashboard.php' ? 'class="active"' : '' ?>>
                Accueil
            </a>
        </li>

        <li>
            <a href="<?= BASE_URL ?>controllers/UserController.php?action=evenements"
               <?= $currentPage === 'evenements.php' ? 'class="active"' : '' ?>>
                Événements
            </a>
        </li>

        <li>
            <a href="<?= BASE_URL ?>controllers/UserController.php?action=sponsors"
               <?= $currentPage === 'sponsors.php' ? 'class="active"' : '' ?>>
                Sponsors
            </a>
        </li>

        <li>
            <a href="<?= BASE_URL ?>controllers/UserController.php?action=covoiturage"
               <?= $currentPage === 'vehicules_disponibles.php' ? 'class="active"' : '' ?>>
                Covoiturage
            </a>
        </li>

        <li>
            <a href="<?= BASE_URL ?>controllers/UserController.php?action=lostFound"
               <?= $currentPage === 'lost_found.php' ? 'class="active"' : '' ?>>
                Lost &amp; Found
            </a>
        </li>

        <!-- ── Profil avec dropdown ── -->
        <li class="profile-dropdown">
            <button class="profile-btn" onclick="navToggleProfile(event)" aria-haspopup="true">
                <div class="profile-avatar">
                    <?php if (!empty($_SESSION['user_photo'])): ?>
                        <img src="<?= BASE_URL ?>uploads/photos/<?= htmlspecialchars($_SESSION['user_photo']) ?>"
                             alt="Photo profil">
                    <?php else: ?>
                        <i class="fas fa-user"></i>
                    <?php endif; ?>
                </div>
                <span><?= htmlspecialchars($_SESSION['user_prenom'] ?? 'Profil') ?></span>
                <i class="fas fa-chevron-down chevron"></i>
            </button>

            <div class="dropdown-menu-nav" id="ecoProfileDropdown">
                <!-- En-tête -->
                <div class="dropdown-nav-header">
                    <div class="dropdown-nav-avatar">
                        <?php if (!empty($_SESSION['user_photo'])): ?>
                            <img src="<?= BASE_URL ?>uploads/photos/<?= htmlspecialchars($_SESSION['user_photo']) ?>"
                                 alt="Photo">
                        <?php else: ?>
                            <i class="fas fa-user"></i>
                        <?php endif; ?>
                    </div>
                    <div>
                        <span class="dropdown-nav-name">
                            <?= htmlspecialchars(($_SESSION['user_prenom'] ?? '') . ' ' . ($_SESSION['user_nom'] ?? '')) ?>
                        </span>
                        <span class="dropdown-nav-role">
                            <?= ($_SESSION['user_role'] ?? 'passager') === 'conducteur' ? '🚗 Conducteur' : '👤 Passager' ?>
                        </span>
                    </div>
                </div>

                <!-- Liens -->
                <div class="dropdown-nav-links">
                    <a href="<?= BASE_URL ?>controllers/UserController.php?action=dashboard">
                        <i class="fas fa-user-edit"></i> Mon Profil
                    </a>
                    <a href="<?= BASE_URL ?>controllers/UserController.php?action=covoiturage">
                        <i class="fas fa-car"></i> Covoiturages
                    </a>
                    <a href="<?= BASE_URL ?>controllers/UserController.php?action=myTrajets">
                        <i class="fas fa-route"></i> Mes trajets
                    </a>
                    <a href="<?= BASE_URL ?>controllers/UserController.php?action=myVehicules">
                        <i class="fas fa-key"></i> Mes véhicules
                    </a>
                    <a href="<?= BASE_URL ?>controllers/UserController.php?action=myHistorique">
                        <i class="fas fa-history"></i> Mon historique
                    </a>
                    <a href="<?= BASE_URL ?>controllers/UserController.php?action=myFavoris">
                        <i class="fas fa-heart"></i> Mes favoris
                    </a>
                    <a href="<?= BASE_URL ?>controllers/UserController.php?action=myReclamations">
                        <i class="fas fa-exclamation-triangle"></i> Réclamations
                    </a>
                    <a href="<?= BASE_URL ?>controllers/UserController.php?action=myObjets">
                        <i class="fas fa-search"></i> Mes objets perdus
                    </a>
                </div>

                <div class="dropdown-nav-divider"></div>

                <a href="<?= BASE_URL ?>controllers/UserController.php?action=logout"
                   class="dropdown-nav-logout">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </li>

        <!-- ── Bouton Admin (visible seulement si admin) ── -->
        <?php if (isset($_SESSION['admin_id']) || isset($_SESSION['is_admin'])): ?>
        <li>
            <a href="<?= BASE_URL ?>controllers/AdminController.php?action=dashboard"
               class="admin-btn">
                <i class="fas fa-shield-alt"></i> Admin
            </a>
        </li>
        <?php endif; ?>

        <!-- ── Toggle thème ── -->
        <li>
            <button class="theme-btn" onclick="navToggleTheme()" id="ecoThemeBtn" title="Mode sombre / clair">
                <i class="fas fa-moon" id="ecoThemeIcon"></i>
            </button>
        </li>

    </ul>

    <!-- Google Translate widget -->
    <div id="google_translate_element"></div>
</nav>

<script>
/* ── Google Translate ── */
function googleTranslateElementInit() {
    new google.translate.TranslateElement({
        pageLanguage: 'fr',
        includedLanguages: 'fr,en,ar,it,de,es',
        layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
        autoDisplay: false
    }, 'google_translate_element');
}
(function removeBanner() {
    var b = document.querySelector('.goog-te-banner-frame');
    if (b) b.style.display = 'none';
    document.body.style.top = '0px';
    setTimeout(removeBanner, 800);
})();
</script>
<script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit" defer></script>

<script>
/* ── Menu burger mobile ── */
function navToggleMenu() {
    document.getElementById('ecoNavLinks').classList.toggle('show');
}

/* ── Dropdown profil ── */
function navToggleProfile(e) {
    e.stopPropagation();
    document.getElementById('ecoProfileDropdown').classList.toggle('show');
}
document.addEventListener('click', function(e) {
    if (!e.target.closest('.profile-dropdown')) {
        var d = document.getElementById('ecoProfileDropdown');
        if (d) d.classList.remove('show');
    }
});

/* ── Toggle thème dark/light ── */
function navToggleTheme() {
    document.body.classList.toggle('light-mode');
    var isLight = document.body.classList.contains('light-mode');
    localStorage.setItem('ecoride_theme', isLight ? 'light' : 'dark');
    document.getElementById('ecoThemeIcon').className = isLight ? 'fas fa-sun' : 'fas fa-moon';
}
/* Appliquer le thème sauvegardé au chargement */
(function() {
    if (localStorage.getItem('ecoride_theme') === 'light') {
        document.body.classList.add('light-mode');
        var icon = document.getElementById('ecoThemeIcon');
        if (icon) icon.className = 'fas fa-sun';
    }
})();
</script>
