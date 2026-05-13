<?php
/**
 * navbar.php - Barre de navigation UNIQUE pour TOUT le frontoffice
 * À inclure dans TOUTES les pages frontoffice
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Déterminer la page active pour le style
$currentFile = basename($_SERVER['PHP_SELF']);
$currentDir = basename(dirname($_SERVER['PHP_SELF']));

// Pour les pages dans /controllers/
$action = $_GET['action'] ?? '';

$isAccueil = ($currentFile === 'index.php');
$isEvenements = ($currentFile === 'events.php' || $currentFile === 'events-detail.php' || $currentFile === 'calendar.php');
$isSponsors = ($currentFile === 'sponsors.php' || $currentFile === 'sponsor-detail.php');
$isLogin = ($currentFile === 'login.php' || $action === 'showLoginForm' || $action === 'showRegister');
$isDashboard = ($currentFile === 'dashboard.php' || $action === 'dashboard');
$isProfile = ($currentFile === 'profile.php');
?>

<nav class="navbar-modern">
    <!-- Logo -->
    <a href="<?= BASE_URL ?>index.php" class="logo">
        <img src="<?= BASE_URL ?>uploads/photos/photo.png" alt="EcoRide Logo" class="logo-img" onerror="this.style.display='none'">
        <div>
            <div class="logo-text">ECO RIDE</div>
            <div class="logo-tagline">Covoiturage Intelligent</div>
        </div>
    </a>
    
    <!-- Menu Burger (mobile) -->
    <button class="menu-toggle" onclick="toggleNavMenu()">
        <i class="fas fa-bars"></i>
    </button>
    
    <!-- Liens de navigation -->
    <ul class="nav-links" id="navLinks">
        <!-- ACCUEIL -->
        <li><a href="<?= BASE_URL ?>index.php" class="<?= $isAccueil ? 'active' : '' ?>">Accueil</a></li>
        
        <!-- ÉVÉNEMENTS -->
        <li><a href="<?= BASE_URL ?>View/frontoffice/events.php" class="<?= $isEvenements ? 'active' : '' ?>">Événements</a></li>
        
        <!-- SPONSORS -->
        <li><a href="<?= BASE_URL ?>View/frontoffice/sponsors.php" class="<?= $isSponsors ? 'active' : '' ?>">Sponsors</a></li>
        
        <!-- AFFICHAGE CONDITIONNEL SELON CONNEXION -->
        <?php if (isset($_SESSION['user_id'])): ?>
            <!-- ========== MENU DÉROULANT PROFIL ========== -->
            <li class="profile-dropdown">
                <button class="profile-btn" onclick="navToggleProfile(event)" aria-haspopup="true">
                    <div class="profile-avatar">
                        <?php if (!empty($_SESSION['user_photo'])): ?>
                            <img src="<?= BASE_URL ?>uploads/photos/<?= htmlspecialchars($_SESSION['user_photo']) ?>"
                                 onerror="this.onerror=null;this.src='<?= BASE_URL ?>serve_image.php?type=user&id=<?= $_SESSION['user_id'] ?>'"
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
                                     onerror="this.onerror=null;this.src='<?= BASE_URL ?>serve_image.php?type=user&id=<?= $_SESSION['user_id'] ?>'"
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

                    <!-- Liens du dropdown -->
                    <div class="dropdown-nav-links">
                        <a href="<?= BASE_URL ?>View/frontoffice/dashboard.php">
                            <i class="fas fa-user-edit"></i> Mon Profil
                        </a>
                        <a href="<?= BASE_URL ?>View/frontoffice/tous_les_trajets.php">
                            <i class="fas fa-car"></i> Covoiturages
                        </a>
                        <a href="<?= BASE_URL ?>View/frontoffice/mes-trajets.php">
                            <i class="fas fa-route"></i> Mes trajets
                        </a>
                        <a href="<?= BASE_URL ?>View/frontoffice/mes-vehicules.php">
                            <i class="fas fa-key"></i> Mes véhicules
                        </a>
                        <a href="<?= BASE_URL ?>View/frontoffice/mon_historique.php">
                            <i class="fas fa-history"></i> Mon historique
                        </a>
                        <a href="<?= BASE_URL ?>View/frontoffice/covoiturage.php">
                            <i class="fas fa-heart"></i> Mes favoris
                        </a>
                        <a href="<?= BASE_URL ?>View/frontoffice/index.php">
                            <i class="fas fa-exclamation-triangle"></i> Réclamations
                        </a>
                        <a href="<?= BASE_URL ?>View/frontoffice/mes_objets_perdus.php">
                            <i class="fas fa-search"></i> Mes objets perdus
                        </a>
                    </div>

                    <div class="dropdown-nav-divider"></div>

                    <a href="<?= BASE_URL ?>Controller/UserController.php?action=logout"
                       class="dropdown-nav-logout">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </a>
                </div>
            </li>
        <?php else: ?>
            <!-- Utilisateur non connecté -->
            <li>
                <a href="<?= BASE_URL ?>Controller/UserController.php?action=showRegister" 
                   class="<?= (($action ?? '') === 'showRegister' || ($_GET['show'] ?? '') === 'register' || ($_GET['show'] ?? '') === 'showRegister') ? 'active' : '' ?>">
                    S'inscrire
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>Controller/UserController.php?action=showLoginForm" 
                   class="<?= (($action ?? '') === 'showLoginForm' || ($_GET['show'] ?? '') === 'login' || ($_GET['show'] ?? '') === 'showLogin' || ($currentFile === 'login.php' && empty($_GET['show']) && empty($action))) ? 'active' : '' ?>">
                    Se connecter
                </a>
            </li>
        <?php endif; ?>
        
        <!-- BOUTON ADMIN (toujours visible) -->
        <li><a href="<?= BASE_URL ?>Controller/AdminController.php?action=showLogin" class="admin-btn">Admin</a></li>
        
        <!-- BOUTON THEME -->
        <li><button class="theme-btn" onclick="toggleTheme()"><i class="fas fa-moon" id="themeIcon"></i></button></li>
    </ul>
</nav>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
/* ========== STYLES NAVBAR ========== */
.navbar-modern {
    font-family: 'Poppins', sans-serif;
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
    flex-direction: row;
    align-items: center;
    gap: 10px;
    text-decoration: none;
}

.navbar-modern .logo-img {
    width: 46px;
    height: 46px;
    object-fit: contain;
}

.navbar-modern .logo-text {
    font-size: 1.6rem;
    font-weight: 700;
    letter-spacing: 1px;
    color: white;
    line-height: 1.3;
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
    font-size: 1.1rem;
    padding: 0.45rem 0.9rem;
    border-radius: 25px;
    cursor: pointer;
    display: none;
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

.nav-links li a, 
.nav-links li button.profile-btn {
    text-decoration: none;
    padding: 0.5rem 1.2rem;
    border-radius: 30px;
    font-size: 0.9rem;
    font-weight: 500;
    font-family: 'Poppins', sans-serif;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: transparent;
    color: white;
    border: none;
    cursor: pointer;
}

.nav-links li a:hover, 
.nav-links li button.profile-btn:hover {
    background: rgba(255,255,255,0.2);
    transform: translateY(-2px);
}

.nav-links li a.active,
.nav-links li a.active:hover {
    background: #0A1628;
    color: white;
    box-shadow: 0 2px 8px rgba(10,22,40,0.35);
}

/* Style spécifique pour le bouton Admin */
.nav-links .admin-btn {
    background: rgba(231,76,60,0.2);
    border: 1px solid rgba(231,76,60,0.4);
    color: #e74c3c;
    padding: 0.5rem 1.2rem;
    border-radius: 30px;
    font-size: 0.9rem;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s;
}

.nav-links .admin-btn:hover {
    background: rgba(231,76,60,0.35);
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(231,76,60,0.2);
}

/* Bouton thème */
.theme-btn {
    background: rgba(255,255,255,0.12);
    border: 1px solid rgba(255,255,255,0.2);
    color: white;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.theme-btn:hover {
    background: rgba(255,255,255,0.28);
}

/* ========== MENU DÉROULANT PROFIL ========== */
.profile-dropdown {
    position: relative;
    list-style: none;
}

.profile-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    overflow: hidden;
    background: rgba(255,255,255,0.15);
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.profile-avatar img,
.profile-avatar i {
    width: 100%;
    height: 100%;
    object-fit: cover;
    font-size: 1rem;
    color: white;
}

.profile-btn .chevron {
    font-size: 0.7rem;
    transition: transform 0.3s ease;
    margin-left: 4px;
}

.profile-dropdown.open .profile-btn .chevron {
    transform: rotate(180deg);
}

/* Menu déroulant */
.dropdown-menu-nav {
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    width: 280px;
    background: #0f172e;
    border-radius: 16px;
    box-shadow: 0 12px 28px rgba(0,0,0,0.5);
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.2s ease;
    z-index: 1001;
    border: 1px solid rgba(97,179,250,0.2);
    backdrop-filter: blur(8px);
}

.profile-dropdown.open .dropdown-menu-nav {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.dropdown-nav-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 1rem;
    border-bottom: 1px solid rgba(97,179,250,0.15);
}

.dropdown-nav-avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    overflow: hidden;
    background: rgba(97,179,250,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
}

.dropdown-nav-avatar img,
.dropdown-nav-avatar i {
    width: 100%;
    height: 100%;
    object-fit: cover;
    font-size: 1.4rem;
    color: #61B3FA;
}

.dropdown-nav-name {
    display: block;
    font-weight: 600;
    font-size: 0.9rem;
    color: #fff;
}

.dropdown-nav-role {
    display: block;
    font-size: 0.7rem;
    color: #61B3FA;
    margin-top: 2px;
}

.dropdown-nav-links {
    padding: 0.5rem 0;
}

.dropdown-nav-links a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 0.7rem 1rem;
    color: #e0e0e0;
    text-decoration: none;
    font-size: 0.85rem;
    transition: all 0.2s;
}

.dropdown-nav-links a i {
    width: 20px;
    font-size: 1rem;
    color: #61B3FA;
}

.dropdown-nav-links a:hover {
    background: rgba(97,179,250,0.1);
    color: white;
}

.dropdown-nav-divider {
    height: 1px;
    background: rgba(97,179,250,0.15);
    margin: 0.5rem 0;
}

.dropdown-nav-logout {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 0.7rem 1rem;
    color: #ff6b6b;
    text-decoration: none;
    font-size: 0.85rem;
    transition: all 0.2s;
    border-radius: 0 0 16px 16px;
}

.dropdown-nav-logout i {
    width: 20px;
    font-size: 1rem;
}

.dropdown-nav-logout:hover {
    background: rgba(255,107,107,0.1);
    color: #ff5252;
}

/* Responsive : adaptation du menu déroulant sur mobile */
@media (max-width: 860px) {
    .menu-toggle {
        display: flex;
        align-items: center;
    }
    .nav-links {
        display: none;
        width: 100%;
        flex-direction: column;
        margin-top: 0.8rem;
    }
    .nav-links.show {
        display: flex;
    }
    .nav-links li {
        width: 100%;
    }
    .nav-links li a,
    .nav-links li button.profile-btn,
    .nav-links .admin-btn,
    .theme-btn {
        width: 100%;
        text-align: center;
        justify-content: center;
    }
    
    /* Sur mobile, le dropdown prend toute la largeur */
    .profile-dropdown {
        width: 100%;
    }
    
    .dropdown-menu-nav {
        position: static;
        width: 100%;
        margin-top: 8px;
        box-shadow: none;
        background: rgba(15,23,46,0.95);
    }
    
    .profile-dropdown.open .dropdown-menu-nav {
        transform: none;
    }
    
    .profile-btn {
        justify-content: space-between;
    }
}

/* Light mode */
body.light-mode .navbar-modern {
    background: linear-gradient(135deg, #1565C0, #0D47A1);
}

body.light-mode .nav-links li a.active {
    background: #E3F2FD;
    color: #1565C0;
}

body.light-mode .nav-links .admin-btn {
    color: #FF5252;
}

body.light-mode .dropdown-menu-nav {
    background: #ffffff;
    border-color: rgba(25,118,210,0.2);
    box-shadow: 0 12px 28px rgba(0,0,0,0.1);
}

body.light-mode .dropdown-nav-header {
    border-bottom-color: rgba(25,118,210,0.15);
}

body.light-mode .dropdown-nav-name {
    color: #263238;
}

body.light-mode .dropdown-nav-role {
    color: #1976D2;
}

body.light-mode .dropdown-nav-links a {
    color: #37474F;
}

body.light-mode .dropdown-nav-links a i {
    color: #1976D2;
}

body.light-mode .dropdown-nav-links a:hover {
    background: rgba(25,118,210,0.08);
    color: #0D47A1;
}

body.light-mode .dropdown-nav-divider {
    background: rgba(25,118,210,0.15);
}

body.light-mode .dropdown-nav-logout {
    color: #d32f2f;
}

body.light-mode .dropdown-nav-logout:hover {
    background: rgba(211,47,47,0.08);
    color: #b71c1c;
}
</style>

<script>
function toggleNavMenu() {
    const navLinks = document.getElementById('navLinks');
    if (navLinks) navLinks.classList.toggle('show');
}

document.addEventListener('click', function(e) {
    const navLinks = document.getElementById('navLinks');
    const menuToggle = document.querySelector('.menu-toggle');
    const profileDropdown = document.querySelector('.profile-dropdown');
    
    if (navLinks && menuToggle) {
        if (!menuToggle.contains(e.target) && !navLinks.contains(e.target)) {
            navLinks.classList.remove('show');
        }
    }
    
    // Fermer le dropdown profil si on clique à l'extérieur
    if (profileDropdown && !profileDropdown.contains(e.target)) {
        profileDropdown.classList.remove('open');
    }
});

function toggleTheme() {
    document.body.classList.toggle('light-mode');
    const isLight = document.body.classList.contains('light-mode');
    const icon = document.getElementById('themeIcon');
    if (icon) icon.className = isLight ? 'fas fa-sun' : 'fas fa-moon';
    localStorage.setItem('ecoride_theme', isLight ? 'light' : 'dark');
}

function navToggleProfile(event) {
    event.stopPropagation();
    const dropdown = document.querySelector('.profile-dropdown');
    if (dropdown) dropdown.classList.toggle('open');
}

(function() {
    if (localStorage.getItem('ecoride_theme') === 'light') {
        document.body.classList.add('light-mode');
        const icon = document.getElementById('themeIcon');
        if (icon) icon.className = 'fas fa-sun';
    }
})();
</script>