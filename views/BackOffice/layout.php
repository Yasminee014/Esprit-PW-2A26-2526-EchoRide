<?php
// layout.php - Fichier commun pour toutes les pages backoffice
if (session_status() === PHP_SESSION_NONE) session_start();
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>EcoRide - Administration</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
:root{--blue:#1976D2;--blue-light:#61B3FA;--dark:#0A1628;--dark2:#0D1F3A;--dark3:#0F3B6E;--grey:#A7A9AC;--green:#27ae60;--red:#e74c3c;}
body{font-family:'Poppins',sans-serif;background:linear-gradient(135deg,var(--dark),var(--dark2));color:#fff;min-height:100vh;}
.wrap{display:flex;min-height:100vh;}

/* ========== SIDEBAR ========== */
.sidebar {
    width: 280px;
    background: linear-gradient(180deg, #2F76BC 0%, #1E5EA5 50%, #174C8A 100%);
    padding: 1.5rem 0;
    position: fixed;
    height: 100vh;
    overflow-y: auto;
    z-index: 100;
    box-shadow: 4px 0 20px rgba(0,0,0,0.2);
}

.sidebar-header {
    padding: 1.5rem;
    border-bottom: 1px solid rgba(255,255,255,0.15);
    margin-bottom: 1.5rem;
    text-align: center;
}

.sidebar-header .logo {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    text-decoration: none;
}

.sidebar-header .logo-text {
    font-size: 1.3rem;
    font-weight: 700;
    color: #A9D6FF;
    letter-spacing: 1px;
}

.sidebar-header .logo-tagline {
    font-size: 0.65rem;
    color: #BFD8F1;
    letter-spacing: 2px;
    opacity: 0.85;
}

.nav-section {
    color: #CFE6FF;
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 2px;
    padding: 0.75rem 1.5rem;
    margin-top: 0.5rem;
    opacity: 0.8;
    font-weight: 600;
}

.sidebar nav ul {
    list-style: none;
}

.sidebar nav ul li {
    margin-bottom: 0.25rem;
}

.sidebar nav ul li a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 0.7rem 1.5rem;
    color: #EAF4FF;
    text-decoration: none;
    transition: all 0.3s;
    font-size: 0.85rem;
    margin: 0 0.5rem;
    border-radius: 10px;
    font-weight: 500;
}

.sidebar nav ul li a i {
    width: 22px;
    color: #EAF4FF;
    font-size: 1rem;
}

.sidebar nav ul li a:hover {
    background: rgba(111,168,220,0.3);
    transform: translateX(5px);
}

.sidebar nav ul li a.active {
    background: linear-gradient(135deg, #6FA8DC, #8FC1F5);
    color: #FFFFFF;
    box-shadow: 0 4px 12px rgba(111,168,220,0.3);
}

.sidebar-footer {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 1rem 1.5rem;
    border-top: 1px solid rgba(255,255,255,0.1);
}

.sidebar-footer a {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #FFCDD2;
    text-decoration: none;
    font-size: 0.85rem;
    padding: 0.5rem 0;
    border-radius: 10px;
    transition: all 0.3s;
}

.sidebar-footer a i {
    width: 22px;
    color: #FFCDD2;
}

.sidebar-footer a:hover {
    color: #FF8A80;
    transform: translateX(5px);
}

/* ========== MAIN CONTENT ========== */
.main {
    flex: 1;
    margin-left: 280px;
    padding: 1.6rem;
    position: relative;
    z-index: 1;
}

/* ========== HEADER ========== */
.admin-header {
    background: linear-gradient(90deg, #071C2F, #0A2A47, #0D355B);
    padding: 1rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    border-radius: 12px;
    gap: 1rem;
    flex-wrap: wrap;
}

.admin-logo .logo-eco {
    font-size: 1.5rem;
    font-weight: 700;
}

.admin-logo .logo-eco span:first-child { color: #4EA3FF; }
.admin-logo .logo-eco span:last-child { color: #6BB8FF; }
.admin-logo .logo-tagline { font-size: 0.65rem; color: #A8C1D9; }

.admin-nav {
    display: flex;
    gap: 0.5rem;
    align-items: center;
    flex-wrap: wrap;
}

.admin-nav a {
    text-decoration: none;
    padding: 0.5rem 1.2rem;
    border-radius: 30px;
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.3s;
    background: transparent;
    color: #CFE6FF;
}

.admin-nav a:hover {
    background: rgba(255,255,255,0.1);
    color: #FFFFFF;
}

.admin-nav .profile-btn {
    background: #003050;
    color: #FFFFFF;
    display: flex;
    align-items: center;
    gap: 10px;
}

.admin-nav .profile-btn:hover {
    background: #002050;
    transform: translateY(-2px);
}

.profile-avatar {
    width: 28px;
    height: 28px;
    background: #5FA8FF;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.admin-nav .admin-btn {
    background: rgba(231,76,60,0.2);
    border: 1px solid rgba(231,76,60,0.4);
    color: #e74c3c;
}

.admin-nav .admin-btn:hover {
    background: rgba(231,76,60,0.35);
}

.theme-btn {
    background: rgba(255,255,255,0.1);
    border: none;
    width: 38px;
    height: 38px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 1.1rem;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.theme-btn:hover {
    background: rgba(255,255,255,0.2);
    transform: rotate(15deg);
}

/* ========== CARDS ========== */
.card {
    background: rgba(255,255,255,0.05);
    border-radius: 16px;
    padding: 1.5rem;
    border: 1px solid rgba(97,179,250,0.15);
    margin-bottom: 1.5rem;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.card-header h2 {
    color: #61B3FA;
    font-size: 1.3rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* ========== TABLEAUX ========== */
.table-wrapper {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th {
    text-align: left;
    padding: 1rem;
    background: rgba(97,179,250,0.1);
    color: #61B3FA;
    font-weight: 600;
    font-size: 0.85rem;
}

td {
    padding: 1rem;
    border-bottom: 1px solid rgba(97,179,250,0.1);
    color: #fff;
    font-size: 0.85rem;
}

tr:hover td {
    background: rgba(97,179,250,0.05);
}

/* ========== BOUTONS ========== */
.btn-primary {
    background: linear-gradient(135deg, #1976D2, #0F3B6E);
    color: white;
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 25px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-edit {
    background: rgba(97,179,250,0.2);
    color: #61B3FA;
    padding: 0.3rem 0.8rem;
    border-radius: 15px;
    text-decoration: none;
    font-size: 0.75rem;
}

.btn-delete {
    background: rgba(231,76,60,0.2);
    color: #e74c3c;
    padding: 0.3rem 0.8rem;
    border-radius: 15px;
    text-decoration: none;
    font-size: 0.75rem;
}

.btn-details {
    background: rgba(52,152,219,0.2);
    color: #3498db;
    padding: 0.3rem 0.8rem;
    border-radius: 15px;
    text-decoration: none;
    font-size: 0.75rem;
}

/* ========== BADGES ========== */
.badge-ouvert { background: rgba(39,174,96,0.2); color: #27ae60; padding: 0.2rem 0.6rem; border-radius: 20px; font-size: 0.7rem; display: inline-block; }
.badge-complet { background: rgba(241,196,15,0.2); color: #f1c40f; padding: 0.2rem 0.6rem; border-radius: 20px; font-size: 0.7rem; display: inline-block; }
.badge-annule { background: rgba(231,76,60,0.2); color: #e74c3c; padding: 0.2rem 0.6rem; border-radius: 20px; font-size: 0.7rem; display: inline-block; }

/* ========== PAGINATION ========== */
.pagination {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 1.5rem;
    flex-wrap: wrap;
}

.pagination a, .pagination span {
    padding: 0.5rem 1rem;
    border-radius: 8px;
    background: rgba(255,255,255,0.08);
    text-decoration: none;
    color: white;
}

.pagination a:hover, .pagination .active {
    background: #1976D2;
}

/* ========== ALERTES ========== */
.alert {
    padding: 0.8rem 1.2rem;
    border-radius: 12px;
    margin-bottom: 1.2rem;
    display: flex;
    align-items: center;
    gap: 9px;
}
.alert-success { background: rgba(39,174,96,0.15); border: 1px solid rgba(39,174,96,0.3); color: #27ae60; }
.alert-error { background: rgba(231,76,60,0.15); border: 1px solid rgba(231,76,60,0.3); color: #e74c3c; }

/* ========== RECHERCHE ========== */
.search-bar {
    display: flex;
    gap: 0.5rem;
}

.search-bar input {
    padding: 0.6rem 1rem;
    border-radius: 25px;
    border: 1px solid rgba(97,179,250,0.2);
    background: rgba(255,255,255,0.08);
    color: white;
    width: 250px;
}

.search-bar button {
    padding: 0.6rem 1.2rem;
    background: #1976D2;
    border: none;
    border-radius: 25px;
    color: white;
    cursor: pointer;
}

/* ========== FOOTER ========== */
footer {
    text-align: center;
    padding: 1.5rem;
    color: #A7A9AC;
    border-top: 1px solid rgba(97,179,250,0.1);
    margin-top: 2rem;
}

/* ========== RESPONSIVE ========== */
@media (max-width: 768px) {
    .sidebar { display: none; }
    .main { margin-left: 0; }
    .admin-header { flex-direction: column; text-align: center; }
    .admin-nav { justify-content: center; }
}

/* Mode clair */
body.light-mode {
    background: #f5f5f5;
    color: #333;
}
body.light-mode .sidebar {
    background: linear-gradient(180deg, #2F76BC, #1E5EA5, #174C8A);
}
body.light-mode .card {
    background: white;
    color: #333;
}
body.light-mode td {
    color: #333;
}
</style>
</head>
<body>
<div class="wrap">

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-header">
        <a href="dashboard.php" class="logo">
            <div class="logo-text">EcoRide</div>
            <div class="logo-tagline">ADMINISTRATION</div>
        </a>
    </div>
    
    <div class="nav-section">GESTION</div>
    <nav>
        <ul>
            <li><a href="dashboard.php" class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="events/list.php" class="<?= $current_dir == 'events' ? 'active' : '' ?>"><i class="fas fa-calendar-alt"></i> Événements</a></li>
            <li><a href="sponsors/list.php" class="<?= $current_dir == 'sponsors' ? 'active' : '' ?>"><i class="fas fa-handshake"></i> Sponsors</a></li>
        </ul>
    </nav>
    
    <div class="sidebar-footer">
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
    </div>
</aside>

<main class="main">

<!-- HEADER -->
<div class="admin-header">
    <div class="admin-logo">
        <div class="logo-eco"><span>ECO</span> <span>RIDE</span></div>
        <div class="logo-tagline">Covoiturage Intelligent</div>
    </div>
    <div class="admin-nav">
        <a href="../../index.php">Accueil site</a>
        <a href="../frontoffice/events.php">Événements</a>
        <a href="../frontoffice/sponsors.php">Sponsors</a>
        
        <a href="profil.php" class="profile-btn">
            <div class="profile-avatar"><i class="fas fa-user"></i></div>
            <span>Profil</span>
        </a>
        
        <a href="dashboard.php" class="admin-btn">Admin</a>
        <button class="theme-btn" onclick="toggleTheme()" id="themeBtn">
            <i class="fas fa-moon"></i>
        </button>
    </div>
</div>

<!-- CONTENU DYNAMIQUE -->
<?php
// Le contenu spécifique de chaque page sera inclus ici
// À utiliser dans chaque fichier : require_once 'layout.php'; et entre $content
?>