<?php
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../models/Sponsor.php';
require_once __DIR__ . '/../../../models/Event.php';
require_once __DIR__ . '/../partials/partials.php';

use Model\Sponsor;
use Model\Event;

// Vérifier si l'admin est connecté
if (!isset($_SESSION['admin_id'])) {
    header('Location: ' . BASE_URL . 'controllers/AdminController.php?action=showLogin');
    exit();
}

$id = isset($_GET['id']) ? $_GET['id'] : null;

if(!$id) {
    header('Location: list.php');
    exit();
}

$sponsorModel = new Sponsor();
$eventModel = new Event();

$sponsor = $sponsorModel->getById($id);

if(!$sponsor) {
    header('Location: list.php');
    exit();
}

$event = null;
if(!empty($sponsor['evenement_id'])) {
    $event = $eventModel->getById($sponsor['evenement_id']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Eco Ride - Détail Sponsor</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<?php render_nav_css(); ?>
<style>
    /* Reset et base */
    * { margin:0; padding:0; box-sizing:border-box; }
    body {
        font-family: 'Poppins', 'Segoe UI', sans-serif;
        background: linear-gradient(135deg, #0A1628 0%, #0D1F3A 100%);
        min-height: 100vh;
        color: #F4F5F7;
    }
    
    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(108,117,125,0.3);
        padding: 0.5rem 1.2rem;
        border-radius: 20px;
        text-decoration: none;
        color: white;
        margin-bottom: 1rem;
        font-size: 0.85rem;
    }
    
    /* Detail card */
    .detail-container {
        max-width: 1000px;
        margin: 0 auto;
    }
    
    .detail-card {
        background: rgba(13, 31, 45, 0.9);
        border-radius: 20px;
        padding: 2rem;
        border: 1px solid rgba(25,118,210,0.3);
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
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .sponsor-level {
        display: inline-block;
        padding: 0.3rem 1rem;
        border-radius: 30px;
        font-size: 0.8rem;
        font-weight: bold;
    }
    
    .sponsor-level.gold {
        background: linear-gradient(135deg, #FFD700, #FFA500);
        color: #333;
    }
    
    .sponsor-level.silver {
        background: linear-gradient(135deg, #C0C0C0, #A9A9A9);
        color: #333;
    }
    
    .sponsor-level.bronze {
        background: linear-gradient(135deg, #CD7F32, #B8860B);
        color: white;
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
    }
    
    .info-box.full-width {
        grid-column: span 2;
    }
    
    .detail-logo {
        text-align: center;
        margin-top: 1rem;
    }
    
    .detail-logo img {
        max-width: 120px;
        max-height: 120px;
        border-radius: 20px;
        background: white;
        padding: 10px;
    }
    
    .event-link {
        color: #61B3FA;
        text-decoration: none;
    }
    
    .event-link:hover {
        text-decoration: underline;
    }
    
    .btn-edit {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: linear-gradient(135deg, #1976D2, #1565C0);
        padding: 0.6rem 1.5rem;
        border-radius: 30px;
        text-decoration: none;
        color: white;
        margin-top: 1rem;
        transition: all 0.3s;
    }
    
    .btn-edit:hover {
        transform: translateX(5px);
        background: linear-gradient(135deg, #1e88e5, #1976D2);
    }
    
    footer {
        text-align: center;
        padding: 2rem;
        border-top: 1px solid rgba(25,118,210, 0.2);
        color: #A7A9AC;
        margin-top: 2rem;
    }
    
    /* Light mode */
    body.light-mode {
        background: linear-gradient(135deg, #EDF2F7 0%, #DBEAFE 100%) !important;
        color: #1A2844 !important;
    }
    
    body.light-mode .detail-card {
        background: rgba(255,255,255,0.95);
    }
    
    body.light-mode .info-box {
        background: rgba(0,0,0,0.02);
    }
    
    @media (max-width: 768px) {
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
</style>
</head>
<body>

<div class="app-wrapper">
    <?php sidebar_spa('evenements'); ?>

    <div class="main-content">
        <div class="page-content">
        <?php navbar_dashboard(); ?>

        <div class="detail-container">
            <a href="list.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Retour à la liste
            </a>
            
            <div class="detail-card">
                <div class="detail-header">
                    <h1><i class="fas fa-building"></i> <?= htmlspecialchars($sponsor['nom_entreprise']) ?></h1>
                    <?php 
                    $type = strtolower(trim($sponsor['type_sponsor'] ?? ''));
                    switch($type) {
                        case 'gold':
                        case 'principal': echo '<span class="sponsor-level gold">🏆 Sponsor Gold</span>'; break;
                        case 'silver':
                        case 'secondaire': echo '<span class="sponsor-level silver">⭐ Sponsor Silver</span>'; break;
                        default: echo '<span class="sponsor-level bronze">🤝 Sponsor Bronze</span>';
                    }
                    ?>
                </div>
                
                <div class="detail-grid">
                    <div class="info-box">
                        <div class="label">ID SPONSOR</div>
                        <div class="value">#<?= $sponsor['id'] ?></div>
                    </div>
                    <div class="info-box">
                        <div class="label">STATUT</div>
                        <div class="value">
                            <?php if($sponsor['statut'] == 'confirme'): ?>
                                <span style="color: #27ae60;">✅ Confirmé</span>
                            <?php elseif($sponsor['statut'] == 'en_attente'): ?>
                                <span style="color: #f1c40f;">⏳ En attente</span>
                            <?php else: ?>
                                <span style="color: #e74c3c;">❌ Refusé</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="info-box">
                        <div class="label">MONTANT SPONSORING</div>
                        <div class="value"><?= number_format($sponsor['montant_sponsoring'], 0, ',', ' ') ?> DT</div>
                    </div>
                    <div class="info-box">
                        <div class="label">DATE D'AJOUT</div>
                        <div class="value"><?= date('d/m/Y', strtotime($sponsor['created_at'] ?? 'now')) ?></div>
                    </div>
                    
                    <?php if(!empty($event)): ?>
                    <div class="info-box full-width">
                        <div class="label">ÉVÉNEMENT ASSOCIÉ</div>
                        <div class="value">
                            <a href="../events/detail.php?id=<?= $event['id'] ?>" class="event-link">
                                <i class="fas fa-calendar-alt"></i> <?= htmlspecialchars($event['titre']) ?> (<?= date('d/m/Y', strtotime($event['date_evenement'])) ?>)
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if(!empty($sponsor['description'])): ?>
                    <div class="info-box full-width">
                        <div class="label">DESCRIPTION</div>
                        <div class="value"><?= nl2br(htmlspecialchars($sponsor['description'])) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php if(!empty($sponsor['logo'])): ?>
                <div class="detail-logo">
                    <img src="../../../uploads/sponsors/<?= $sponsor['logo'] ?>" alt="Logo <?= htmlspecialchars($sponsor['nom_entreprise']) ?>">
                </div>
                <?php endif; ?>
                
                <div style="text-align: center;">
                    <a href="form.php?id=<?= $sponsor['id'] ?>" class="btn-edit">
                        <i class="fas fa-edit"></i> Modifier ce sponsor
                    </a>
                </div>
            </div>
        </div>
        
        <footer>
            <p>
                <svg width="16" height="16" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align:middle">
                    <path d="M22 4C22 4 8 10 8 24C8 31.732 14.268 38 22 38C29.732 38 36 31.732 36 24C36 14 28 8 22 4Z" fill="#61B3FA" opacity="0.9"/>
                </svg> 
                Eco Ride by Echo Group © 2025 - Gestion des Sponsors
            </p>
        </footer>
        </div>
    </div>
</div>


<script>
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
<?php require_once __DIR__ . '/../ai_helper_widget.php'; ?>
</body>
</html>