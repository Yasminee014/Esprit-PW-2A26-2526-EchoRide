<?php
// Navbar utilisateur avec menu déroulant - EcoRide
if (session_status() === PHP_SESSION_NONE) session_start();
$current_page = basename($_SERVER['PHP_SELF']);
?>
<style>
/* ──────────────────────────────────────────────────────────── */
/* NAVBAR UTILISATEUR AVEC MENU DÉROULANT                       */
/* ──────────────────────────────────────────────────────────── */
.navbar-user {
    background: linear-gradient(90deg, #1976D2, #0F3B6E);
    padding: 0.8rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 4px 20px rgba(0,0,0,.3);
    position: sticky;
    top: 0;
    z-index: 100;
}

.navbar-user .nav-left {
    display: flex;
    align-items: center;
    gap: 2rem;
}

.navbar-user .logo {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 1.3rem;
    font-weight: 700;
    color: #fff;
    text-decoration: none;
    transition: opacity 0.3s;
}

.navbar-user .logo i {
    color: #61B3FA;
    font-size: 1.5rem;
}

.navbar-user .logo:hover {
    opacity: 0.9;
}

/* ── BOUTON MENU DÉROULANT ── */
.navbar-user .dropdown {
    position: relative;
    display: inline-block;
}

.navbar-user .dropdown-btn {
    background: rgba(255,255,255,0.1);
    color: #fff;
    padding: 0.6rem 1.2rem;
    border: 1px solid rgba(97,179,250,.4);
    border-radius: 30px;
    font-size: 0.9rem;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
}

.navbar-user .dropdown-btn:hover {
    background: rgba(255,255,255,0.2);
    border-color: #61B3FA;
    transform: translateY(-1px);
}

/* ── CONTENU DU MENU DÉROULANT ── */
.navbar-user .dropdown-content {
    display: none;
    position: absolute;
    top: 110%;
    left: 0;
    min-width: 240px;
    background: linear-gradient(145deg, #0D1F3A, #122A4A);
    border: 1px solid rgba(97,179,250,.3);
    border-radius: 12px;
    box-shadow: 0 8px 30px rgba(0,0,0,.4);
    z-index: 200;
    overflow: hidden;
}

.navbar-user .dropdown-content.show {
    display: block;
    animation: fadeInDown 0.25s ease;
}

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.navbar-user .dropdown-content a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 0.8rem 1.2rem;
    color: #fff;
    text-decoration: none;
    font-size: 0.85rem;
    transition: all 0.2s;
}

.navbar-user .dropdown-content a i {
    width: 20px;
    color: #61B3FA;
    font-size: 0.9rem;
}

.navbar-user .dropdown-content a:hover {
    background: rgba(97,179,250,.15);
    padding-left: 1.5rem;
}

.navbar-user .dropdown-content a.active {
    background: rgba(25,118,210,.3);
    border-left: 3px solid #61B3FA;
}

.navbar-user .dropdown-divider {
    height: 1px;
    background: rgba(97,179,250,.2);
    margin: 0.3rem 0;
}

.navbar-user .dropdown-content .admin-link i {
    color: #e74c3c;
}

.navbar-user .dropdown-content .logout-link i {
    color: #e74c3c;
}

.navbar-user .dropdown-content .logout-link:hover {
    background: rgba(231,76,60,.15);
}

/* ── PARTIE DROITE ── */
.navbar-user .nav-right .user-info {
    display: flex;
    align-items: center;
    gap: 8px;
    background: rgba(255,255,255,0.1);
    padding: 0.4rem 1rem;
    border-radius: 30px;
    font-size: 0.85rem;
}

.navbar-user .nav-right .user-info i {
    font-size: 1.2rem;
    color: #61B3FA;
}

.navbar-user .nav-right .user-info span {
    color: #fff;
}

/* Responsive */
@media (max-width: 768px) {
    .navbar-user {
        padding: 0.6rem 1rem;
    }
    .navbar-user .logo span {
        display: none;
    }
    .navbar-user .dropdown-btn span {
        display: none;
    }
    .navbar-user .dropdown-btn {
        padding: 0.6rem;
    }
    .navbar-user .user-info span {
        display: none;
    }
}
</style>

<nav class="navbar-user">
    <div class="nav-left">
        <a href="../index.php" class="logo">
            <i class="fas fa-leaf"></i>
            <span>EcoRide</span>
        </a>
        
        <!-- Menu déroulant -->
        <div class="dropdown">
            <button class="dropdown-btn" onclick="toggleUserDropdown()">
                <i class="fas fa-bars"></i>
                <span>Menu</span>
            </button>
            <div class="dropdown-content" id="userDropdownMenu">
                <a href="vehicules_disponibles.php" class="<?= $current_page == 'vehicules_disponibles.php' ? 'active' : '' ?>">
                    <i class="fas fa-car"></i> Covoiturages
                </a>
                <a href="mes_reservations.php" class="<?= $current_page == 'mes_reservations.php' ? 'active' : '' ?>">
                    <i class="fas fa-calendar-check"></i> Mes réservations
                </a>
                <a href="mes_vehicules.php" class="<?= $current_page == 'mes_vehicules.php' ? 'active' : '' ?>">
                    <i class="fas fa-key"></i> Mes véhicules
                </a>
                <a href="mon_historique.php" class="<?= $current_page == 'mon_historique.php' ? 'active' : '' ?>">
                    <i class="fas fa-history"></i> Mon historique
                </a>
                <div class="dropdown-divider"></div>
                <a href="../backoffice/admin.php" class="admin-link">
                    <i class="fas fa-shield-alt"></i> Administration
                </a>
                <a href="logout.php" class="logout-link">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </div>
    </div>
    
    <div class="nav-right">
        <div class="user-info">
            <i class="fas fa-user-circle"></i>
            <span><?= $_SESSION['user_name'] ?? 'Utilisateur' ?></span>
        </div>
    </div>
</nav>

<script>
// Fonction pour ouvrir/fermer le menu déroulant utilisateur
function toggleUserDropdown() {
    var dropdown = document.getElementById("userDropdownMenu");
    dropdown.classList.toggle("show");
}

// Fermer le menu si on clique ailleurs
window.onclick = function(event) {
    if (!event.target.matches('.dropdown-btn') && !event.target.closest('.dropdown-btn')) {
        var dropdowns = document.getElementsByClassName("dropdown-content");
        for (var i = 0; i < dropdowns.length; i++) {
            if (dropdowns[i].classList.contains('show')) {
                dropdowns[i].classList.remove('show');
            }
        }
    }
}

// Fermer le menu avec la touche Echap
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        var dropdown = document.getElementById("userDropdownMenu");
        if (dropdown.classList.contains('show')) {
            dropdown.classList.remove('show');
        }
    }
});
</script>