
<?php
// Navbar frontoffice - PAS de session_start() ici
$current_page = basename($_SERVER['PHP_SELF']);
?>
<style>
/* ========== NAVBAR ========== */
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
    flex-direction: column;
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

.navbar-modern .logo {
    flex-direction: row !important;
    align-items: center;
    gap: 10px;
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

/* Tous les boutons normaux */
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

/* Bouton actif */
.nav-links li a.active {
    background: #0A1628;
    color: white;
    box-shadow: 0 2px 8px rgba(10,22,40,0.3);
}

/* ========== BOUTON ADMIN - STYLE ROUGE SEMI-TRANSPARENT ========== */
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

/* ========== BOUTON PROFIL ========== */
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

/* ========== BOUTON MODE SOMBRE/CLAIR ========== */
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

/* ========== MENU DÉROULANT PROFIL ========== */
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

/* En-tête du profil */
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

/* Liens du menu */
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
</style>

<nav class="navbar-modern">
    <a href="../../index.php" class="logo">
        <div>
            <div class="logo-text">ECO RIDE</div>
            <div class="logo-tagline">Covoiturage Intelligent</div>
        </div>
    </a>
    
    <button class="menu-toggle" onclick="toggleMenu()">
        <i class="fas fa-bars"></i>
    </button>
    
    <ul class="nav-links" id="navLinks">
        <!-- Bouton Accueil -->
        <li><a href="../../index.php" class="<?= $current_page == 'index.php' ? 'active' : '' ?>">Accueil</a></li>
        
        <!-- LIENS CORRIGÉS -->
        <li><a href="/projet-event/views/frontoffice/events.php" class="<?= $current_page == 'events.php' ? 'active' : '' ?>">Événements</a></li>
        <li><a href="/projet-event/views/frontoffice/sponsors.php" class="<?= $current_page == 'sponsors.php' ? 'active' : '' ?>">Sponsors</a></li>
        <li><a href="#">Covoiturage</a></li>
        <li><a href="#">Lost & Found</a></li>
        
        <li class="profile-dropdown">
            <button class="profile-btn" onclick="toggleProfileDropdown(event)">
                <div class="profile-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <span>Profil</span>
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="dropdown-menu" id="profileDropdown">
                <div class="dropdown-header">
                    <div class="avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="user-info">
                        <div class="user-name">Utilisateur</div>
                        <div class="user-role">Membre EcoRide</div>
                    </div>
                </div>
                
                <div class="dropdown-links">
                    <a href="#"><i class="fas fa-car"></i> Covoiturages</a>
                    <a href="#"><i class="fas fa-route"></i> Tous les trajets</a>
                    <a href="#"><i class="fas fa-map-marker-alt"></i> Mes trajets</a>
                    <a href="#"><i class="fas fa-key"></i> Mes véhicules</a>
                    <a href="#"><i class="fas fa-history"></i> Mon historique</a>
                    <a href="#"><i class="fas fa-heart"></i> Mes favoris</a>
                    
                    <a href="#"><i class="fas fa-exclamation-triangle"></i> Réclamations</a>
                    <a href="#"><i class="fas fa-search"></i> Mes objets perdus</a>
                </div>
                
                <div class="dropdown-divider"></div>
                
                <div class="dropdown-actions">
                    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
                </div>
            </div>
        </li>
        
        <!-- Bouton Admin -->
        <li><a href="../BackOffice/dashboard.php" class="admin-btn">Admin</a></li>
        
        <li class="theme-li">
            <button class="theme-btn" onclick="toggleTheme()" id="themeBtn">
                <i class="fas fa-moon"></i>
            </button>
        </li>
    </ul>
</nav>

<script>
function toggleMenu() {
    document.getElementById('navLinks').classList.toggle('show');
}

function toggleProfileDropdown(event) {
    event.stopPropagation();
    document.getElementById('profileDropdown').classList.toggle('show');
}

function toggleTheme() {
    document.body.classList.toggle('light-mode');
    const isLight = document.body.classList.contains('light-mode');
    localStorage.setItem('theme', isLight ? 'light' : 'dark');
    
    const themeBtn = document.getElementById('themeBtn');
    const icon = themeBtn.querySelector('i');
    
    if (isLight) {
        icon.className = 'fas fa-sun';
    } else {
        icon.className = 'fas fa-moon';
    }
}

if (localStorage.getItem('theme') === 'light') {
    document.body.classList.add('light-mode');
    const themeBtn = document.getElementById('themeBtn');
    if (themeBtn) {
        const icon = themeBtn.querySelector('i');
        icon.className = 'fas fa-sun';
    }
}

window.onclick = function(event) {
    if (!event.target.closest('.profile-dropdown')) {
        var dropdown = document.getElementById('profileDropdown');
        if (dropdown && dropdown.classList.contains('show')) {
            dropdown.classList.remove('show');
        }
    }
}
</script>