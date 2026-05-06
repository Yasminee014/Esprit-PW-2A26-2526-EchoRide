<?php
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../models/Event.php';
require_once __DIR__ . '/../../../models/Sponsor.php';

use Model\Event;
use Model\Sponsor;

$id = isset($_GET['id']) ? $_GET['id'] : null;

if(!$id) {
    header('Location: list.php');
    exit();
}

$eventModel = new Event();
$sponsorModel = new Sponsor();
$event = $eventModel->getWithSponsors($id);

if(!$event) {
    header('Location: list.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Eco Ride - Détail Événement</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../../../style.css">
<style>
.btn-details {
    background: rgba(52, 152, 219, 0.15);
    color: #3498db;
    padding: 0.2rem 0.6rem;
    border-radius: 10px;
    text-decoration: none;
    font-size: 0.75rem;
    display: inline-block;
    margin: 0 0.2rem;
}
.btn-details:hover {
    background: #3498db;
    color: white;
}
.actions {
    white-space: nowrap;
}
.navbar-backoffice {
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
.navbar-backoffice .nav-left {
    display: flex;
    align-items: center;
    gap: 2rem;
}
.navbar-backoffice .logo {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 1.3rem;
    font-weight: 700;
    color: #fff;
    text-decoration: none;
}
.navbar-backoffice .logo i {
    color: #61B3FA;
    font-size: 1.5rem;
}
.navbar-backoffice .dropdown {
    position: relative;
    display: inline-block;
}
.navbar-backoffice .dropdown-btn {
    background: rgba(255,255,255,0.1);
    color: #fff;
    padding: 0.6rem 1.2rem;
    border: 1px solid rgba(97,179,250,.4);
    border-radius: 30px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
}
.navbar-backoffice .dropdown-content {
    display: none;
    position: absolute;
    top: 110%;
    left: 0;
    min-width: 220px;
    background: linear-gradient(145deg, #0D1F3A, #122A4A);
    border: 1px solid rgba(97,179,250,.3);
    border-radius: 12px;
    z-index: 200;
}
.navbar-backoffice .dropdown-content.show {
    display: block;
    animation: fadeInDown 0.25s ease;
}
@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
.navbar-backoffice .dropdown-content a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 0.8rem 1.2rem;
    color: #fff;
    text-decoration: none;
    font-size: 0.85rem;
}
.navbar-backoffice .dropdown-content a i {
    width: 20px;
    color: #61B3FA;
}
.navbar-backoffice .dropdown-content a:hover {
    background: rgba(97,179,250,.15);
    padding-left: 1.5rem;
}
.navbar-backoffice .dropdown-divider {
    height: 1px;
    background: rgba(97,179,250,.2);
    margin: 0.3rem 0;
}
.navbar-backoffice .nav-right .user-info {
    display: flex;
    align-items: center;
    gap: 8px;
    background: rgba(255,255,255,0.1);
    padding: 0.4rem 1rem;
    border-radius: 30px;
}
.navbar-backoffice .nav-right .user-info i {
    color: #61B3FA;
}
.sidebar {
    position: fixed;
    left: 0;
    top: 70px;
    width: 280px;
    height: calc(100vh - 70px);
    background: #0D1F3A;
    border-right: 1px solid rgba(97,179,250,0.15);
    overflow-y: auto;
}
.sidebar-header {
    padding: 1.5rem;
    border-bottom: 1px solid rgba(97,179,250,0.15);
}
.sidebar-header .logo {
    display: flex;
    align-items: center;
    gap: 10px;
}
.sidebar-header .logo i {
    color: #61B3FA;
    font-size: 28px;
}
.sidebar-header .logo h2 {
    font-size: 1.3rem;
    background: linear-gradient(135deg, #61B3FA, #1976D2);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
}
.sidebar-nav {
    padding: 0 1rem;
}
.sidebar-nav .nav-section {
    margin-bottom: 1.5rem;
}
.sidebar-nav .nav-section-title {
    color: #A7A9AC;
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 0.8rem;
    padding-left: 0.5rem;
}
.sidebar-nav a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 0.8rem 1rem;
    color: white;
    text-decoration: none;
    border-radius: 12px;
    transition: all 0.3s;
    margin-bottom: 0.3rem;
}
.sidebar-nav a i {
    width: 24px;
    color: #A7A9AC;
}
.sidebar-nav a:hover {
    background: rgba(97,179,250,0.1);
}
.sidebar-nav a:hover i {
    color: #61B3FA;
}
.sidebar-nav a.active {
    background: linear-gradient(135deg, #1976D2, #0F3B6E);
}
.main-content {
    margin-left: 280px;
    padding: 1.5rem;
    min-height: 100vh;
}
.detail-container {
    max-width: 1000px;
    margin: 0 auto;
}
.detail-card {
    background: rgba(255,255,255,0.05);
    border-radius: 24px;
    padding: 2rem;
    border: 1px solid rgba(97,179,250,0.2);
}
.detail-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid rgba(97,179,250,0.2);
}
.detail-header h1 {
    font-size: 1.8rem;
    color: #61B3FA;
}
.detail-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.info-box {
    background: rgba(255,255,255,0.03);
    border-radius: 16px;
    padding: 1rem;
}
.info-box .label {
    font-size: 0.7rem;
    color: #A7A9AC;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 0.5rem;
}
.info-box .value {
    font-size: 1rem;
    font-weight: 600;
    color: white;
}
.info-box.full-width {
    grid-column: span 2;
}
.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(108,117,125,0.3);
    padding: 0.6rem 1.2rem;
    border-radius: 30px;
    text-decoration: none;
    color: white;
    margin-bottom: 1rem;
    transition: all 0.3s;
}
.btn-back:hover {
    background: rgba(108,117,125,0.5);
}
.btn-edit {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(90deg, #1976D2, #0F3B6E);
    padding: 0.6rem 1.2rem;
    border-radius: 30px;
    text-decoration: none;
    color: white;
    margin-top: 1rem;
    transition: all 0.3s;
}
.btn-edit:hover {
    background: linear-gradient(90deg, #61B3FA, #1976D2);
    transform: translateX(5px);
}
.detail-image {
    text-align: center;
    margin-top: 1rem;
}
.detail-image img {
    max-width: 100%;
    max-height: 250px;
    border-radius: 15px;
    object-fit: cover;
}
.event-tag {
    display: inline-block;
    background: rgba(97,179,250,0.15);
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    margin: 0.3rem;
    font-size: 0.8rem;
}
.sponsor-card {
    background: rgba(10,47,68,0.5);
    padding: 1rem;
    border-radius: 10px;
    text-align: center;
    transition: all 0.3s;
}
.sponsor-card:hover {
    transform: translateY(-3px);
    background: rgba(97,179,250,0.05);
}
.sponsor-card h4 {
    color: #61B3FA;
    margin-bottom: 0.3rem;
}
.sponsor-logo-mini {
    width: 60px;
    height: 60px;
    object-fit: contain;
    margin: 0 auto 0.5rem;
    border-radius: 12px;
    background: white;
    padding: 5px;
}
.sponsor-icon-mini {
    width: 60px;
    height: 60px;
    background: rgba(97,179,250,0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 0.5rem;
}
.sponsor-icon-mini i {
    color: #61B3FA;
    font-size: 1.5rem;
}
.badge-actif {
    background: #2ecc71;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}
.badge-inactif {
    background: #e74c3c;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}
@media (max-width: 768px) {
    .sidebar {
        display: none;
    }
    .main-content {
        margin-left: 0;
    }
    .detail-grid {
        grid-template-columns: 1fr;
    }
    .info-box.full-width {
        grid-column: span 1;
    }
    .detail-header h1 {
        font-size: 1.3rem;
    }
}
footer {
    text-align: center;
    padding: 1.5rem;
    background: #0D1F3A;
    border-top: 1px solid rgba(97,179,250,0.1);
    margin-left: 280px;
}
@media (max-width: 768px) {
    footer {
        margin-left: 0;
    }
}
</style>
</head>
<body>

<nav class="navbar-backoffice">
    <div class="nav-left">
        <a href="../../../index.php" class="logo">
            <i class="fas fa-leaf"></i>
            <span>EcoRide - Admin</span>
        </a>
        <div class="dropdown">
            <button class="dropdown-btn" onclick="toggleMenu()">
                <i class="fas fa-bars"></i>
                <span>Menu</span>
            </button>
            <div class="dropdown-content" id="dropdownMenu">
                <a href="../dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="../events/list.php"><i class="fas fa-calendar-alt"></i> Événements</a>
                <a href="list.php"><i class="fas fa-handshake"></i> Sponsors</a>
                <div class="dropdown-divider"></div>
                <a href="../../../index.php"><i class="fas fa-globe"></i> Voir le site</a>
            </div>
        </div>
    </div>
    <div class="nav-right">
        <div class="user-info">
            <i class="fas fa-user-circle"></i>
            <span>Administrateur</span>
        </div>
    </div>
</nav>

<div class="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <i class="fas fa-leaf"></i>
            <h2>ECO RIDE</h2>
        </div>
    </div>
    <div class="sidebar-nav">
        <div class="nav-section">
            <div class="nav-section-title">ADMINISTRATION</div>
            <a href="../dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="../events/list.php"><i class="fas fa-calendar-alt"></i> Événements</a>
            <a href="list.php" class="active"><i class="fas fa-handshake"></i> Sponsors</a>
        </div>
        <div class="nav-section">
            <div class="nav-section-title">GESTION</div>
            <a href="#"><i class="fas fa-car"></i> Véhicules</a>
            <a href="#"><i class="fas fa-ticket-alt"></i> Réservations</a>
            <a href="#"><i class="fas fa-history"></i> Historique</a>
        </div>
    </div>
</div>

<div class="main-content">
    <div class="detail-container">
        <a href="list.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Retour à la liste
        </a>
        
        <div class="detail-card">
            <div class="detail-header">
                <h1>
                    <i class="fas fa-calendar-alt"></i>
                    <?= htmlspecialchars($event['titre']) ?>
                </h1>
                <?php 
                $statutClass = '';
                switch($event['statut']) {
                    case 'ouvert': $statutClass = 'badge-ouvert'; break;
                    case 'complet': $statutClass = 'badge-complet'; break;
                    case 'annule': $statutClass = 'badge-annule'; break;
                    default: $statutClass = 'badge-ouvert';
                }
                ?>
                <span class="<?= $statutClass ?>"><?= htmlspecialchars(ucfirst($event['statut'])) ?></span>
            </div>

            <div class="detail-grid">
                <div class="info-box">
                    <div class="label">Type</div>
                    <div class="value"><?= htmlspecialchars($event['type']) ?></div>
                </div>
                <div class="info-box">
                    <div class="label">Ville</div>
                    <div class="value"><?= htmlspecialchars($event['ville']) ?></div>
                </div>
                <div class="info-box">
                    <div class="label">Date et heure</div>
                    <div class="value"><?= date('d/m/Y H:i', strtotime($event['date_evenement'])) ?></div>
                </div>
                <div class="info-box">
                    <div class="label">Places disponibles</div>
                    <div class="value"><?= htmlspecialchars($event['nb_places']) ?> places</div>
                </div>
                <?php if(!empty($event['description'])): ?>
                <div class="info-box full-width">
                    <div class="label">Description</div>
                    <div class="value"><?= nl2br(htmlspecialchars($event['description'])) ?></div>
                </div>
                <?php endif; ?>
            </div>

            <?php if(!empty($event['image']) && $event['image'] != 'default.jpg'): ?>
            <div class="detail-image">
                <img src="../../../uploads/events/<?= htmlspecialchars($event['image']) ?>" alt="Image de l'événement">
            </div>
            <?php endif; ?>

            <?php if(!empty($event['sponsors'])): ?>
            <div class="detail-sponsors">
                <h3><i class="fas fa-handshake"></i> Sponsors associés</h3>
                <div class="sponsors-grid">
                    <?php foreach($event['sponsors'] as $sponsor): ?>
                    <div class="sponsor-card">
                        <?php if(!empty($sponsor['logo'])): ?>
                            <img src="../../../uploads/sponsors/<?= htmlspecialchars($sponsor['logo']) ?>" alt="Logo <?= htmlspecialchars($sponsor['nom_entreprise']) ?>" class="sponsor-logo-mini">
                        <?php else: ?>
                            <div class="sponsor-icon-mini"><i class="fas fa-building"></i></div>
                        <?php endif; ?>
                        <h4><?= htmlspecialchars($sponsor['nom_entreprise']) ?></h4>
                        <?php 
                        $type = strtolower(trim($sponsor['type_sponsor'] ?? ''));
                        switch($type) {
                            case 'gold':
                            case 'principal': echo '<span class="sponsor-level gold">🏆 Gold</span>'; break;
                            case 'silver':
                            case 'secondaire': echo '<span class="sponsor-level silver">⭐ Silver</span>'; break;
                            default: echo '<span class="sponsor-level bronze">🤝 Bronze</span>';
                        }
                        ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div style="text-align: center; display: flex; gap: 1rem; justify-content: center;">
                <a href="form.php?id=<?= $event['id'] ?>" class="btn-edit">
                    <i class="fas fa-edit"></i> Modifier cet événement
                </a>
                <a href="delete.php?id=<?= $event['id'] ?>" 
                   class="btn-edit" 
                   style="background: linear-gradient(90deg, #e74c3c, #c0392b);"
                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet événement ?')">
                    <i class="fas fa-trash"></i> Supprimer
                </a>
            </div>
        </div>
    </div>
</div>

<footer>
    <p><i class="fas fa-leaf"></i> Eco Ride © 2025 - Administration</p>
</footer>

<script>
function toggleMenu() { 
    document.getElementById("dropdownMenu").classList.toggle("show"); 
}

window.onclick = function(e) { 
    if (!e.target.matches('.dropdown-btn') && !e.target.closest('.dropdown-btn')) { 
        var d = document.getElementById("dropdownMenu"); 
        if (d && d.classList.contains('show')) {
            d.classList.remove('show');
        }
    } 
}
</script>
</body>
</html>