<?php
/**
 * navbar.php - Barre de navigation UNIQUE pour TOUT le frontoffice
 * Styles : views/frontoffice/navbar-front.css (chargé une seule fois)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('ECORIDE_NAVBAR_CSS_LINKED')) {
    echo '<link rel="stylesheet" href="' . htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') . 'views/frontoffice/navbar-front.css">' . "\n";
}

$currentFile = basename($_SERVER['PHP_SELF']);
$action = $_GET['action'] ?? '';

$isAccueil = ($currentFile === 'index.php');
$isEvenements = ($currentFile === 'events.php' || $currentFile === 'events-detail.php' || $currentFile === 'calendar.php');
$isSponsors = ($currentFile === 'sponsors.php' || $currentFile === 'sponsor-detail.php');
$isLogin = ($currentFile === 'login.php' || $action === 'showLoginForm' || $action === 'showRegister');
$isDashboard = ($currentFile === 'dashboard.php' || $action === 'dashboard');
$isProfile = ($currentFile === 'profile.php');
?>

<nav id="eco-front-nav" class="navbar-modern">
    <a href="<?= BASE_URL ?>index.php" class="logo">
        <img src="<?= BASE_URL ?>uploads/photos/photo.png" alt="EcoRide Logo" class="logo-img" width="46" height="46" onerror="this.style.display='none'">
        <div>
            <div class="logo-text">ECO RIDE</div>
            <div class="logo-tagline">Covoiturage Intelligent</div>
        </div>
    </a>

    <button type="button" class="menu-toggle" onclick="toggleNavMenu()" aria-label="Menu">
        <i class="fas fa-bars"></i>
    </button>

    <ul class="nav-links" id="navLinks">
        <li><a href="<?= BASE_URL ?>index.php" class="<?= $isAccueil ? 'active' : '' ?>">Accueil</a></li>
        <li><a href="<?= BASE_URL ?>views/frontoffice/events.php" class="<?= $isEvenements ? 'active' : '' ?>">Événements</a></li>
        <li><a href="<?= BASE_URL ?>views/frontoffice/sponsors.php" class="<?= $isSponsors ? 'active' : '' ?>">Sponsors</a></li>

        <?php if (isset($_SESSION['user_id'])): ?>
            <li class="profile-dropdown">
                <button type="button" class="profile-btn" onclick="navToggleProfile(event)" aria-haspopup="true">
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

                    <div class="dropdown-nav-links">
                        <a href="<?= BASE_URL ?>controllers/UserController.php?action=dashboard">
                            <i class="fas fa-user-edit"></i> Mon Profil
                        </a>
                        <a href="<?= BASE_URL ?>views/frontoffice/covoiturage.php">
                            <i class="fas fa-car"></i> Covoiturages
                        </a>
                        <a href="<?= BASE_URL ?>views/frontoffice/mes-trajets.php">
                            <i class="fas fa-route"></i> Mes trajets
                        </a>
                        <a href="<?= BASE_URL ?>views/frontoffice/mes-vehicules.php">
                            <i class="fas fa-key"></i> Mes véhicules
                        </a>
                        <a href="<?= BASE_URL ?>views/frontoffice/mes-trajets.php">
                            <i class="fas fa-history"></i> Mon historique
                        </a>
                        <a href="<?= BASE_URL ?>views/frontoffice/covoiturage.php">
                            <i class="fas fa-heart"></i> Mes favoris
                        </a>
                        <a href="<?= BASE_URL ?>views/frontoffice/reclamations.php">
                            <i class="fas fa-exclamation-triangle"></i> Réclamations
                        </a>
                        <a href="<?= BASE_URL ?>views/frontoffice/lost-found.php">
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
        <?php else: ?>
            <li>
                <a href="<?= BASE_URL ?>controllers/UserController.php?action=showRegister"
                   class="<?= (($action ?? '') === 'showRegister' || ($_GET['show'] ?? '') === 'register' || ($_GET['show'] ?? '') === 'showRegister') ? 'active' : '' ?>">
                    S'inscrire
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>controllers/UserController.php?action=showLoginForm"
                   class="<?= (($action ?? '') === 'showLoginForm' || ($_GET['show'] ?? '') === 'login' || ($_GET['show'] ?? '') === 'showLogin' || ($currentFile === 'login.php' && empty($_GET['show']) && empty($action))) ? 'active' : '' ?>">
                    Se connecter
                </a>
            </li>
        <?php endif; ?>

        <li><a href="<?= BASE_URL ?>controllers/AdminController.php?action=showLogin" class="admin-btn">Admin</a></li>
        <li><button type="button" class="theme-btn" onclick="toggleTheme()" aria-label="Thème"><i class="fas fa-moon" id="themeIcon"></i></button></li>
    </ul>
</nav>

<script>
function toggleNavMenu() {
    const navLinks = document.getElementById('navLinks');
    if (navLinks) navLinks.classList.toggle('show');
}

document.addEventListener('click', function(e) {
    const navLinks = document.getElementById('navLinks');
    const menuToggle = document.querySelector('#eco-front-nav .menu-toggle');
    const profileDropdown = document.querySelector('#eco-front-nav .profile-dropdown');

    if (navLinks && menuToggle) {
        if (!menuToggle.contains(e.target) && !navLinks.contains(e.target)) {
            navLinks.classList.remove('show');
        }
    }

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
    const dropdown = document.querySelector('#eco-front-nav .profile-dropdown');
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
