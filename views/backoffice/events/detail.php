<?php
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../models/Event.php';
require_once __DIR__ . '/../../../models/Sponsor.php';
require_once __DIR__ . '/../partials/partials.php';

use Model\Event;
use Model\Sponsor;

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

$eventModel = new Event();
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
<title>Détail Événement - EcoRide</title>
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
        margin-bottom: 1.5rem;
        border-bottom: 1px solid rgba(97,179,250,0.2);
        padding-bottom: 1rem;
    }
    
    .detail-header h1 {
        color: #61B3FA;
        font-size: 1.8rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .detail-header span {
        background: rgba(25,118,210,0.2);
        padding: 0.3rem 1rem;
        border-radius: 20px;
        font-size: 0.85rem;
        color: #1976D2;
    }
    
    .detail-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }
    
    .info-box {
        background: rgba(255,255,255,0.03);
        padding: 1rem;
        border-radius: 12px;
    }
    
    .label {
        font-size: 0.7rem;
        color: #A7A9AC;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .value {
        font-weight: bold;
        margin-top: 5px;
        font-size: 1rem;
    }
    
    .full-width {
        grid-column: span 2;
    }
    
    .detail-image {
        margin-top: 1.5rem;
        text-align: center;
    }
    
    .detail-image img {
        max-width: 100%;
        border-radius: 15px;
        max-height: 400px;
        object-fit: cover;
    }
    
    .sponsors-section {
        margin-top: 2rem;
    }
    
    .sponsors-section h3 {
        color: #61B3FA;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .sponsors-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
    }
    
    .sponsor-card {
        background: rgba(255,255,255,0.05);
        padding: 1rem;
        border-radius: 12px;
        text-align: center;
        border: 1px solid rgba(25,118,210,0.2);
    }
    
    .sponsor-card h4 {
        color: #1976D2;
        margin-bottom: 0.3rem;
    }
    
    .action-buttons {
        margin-top: 2rem;
        display: flex;
        gap: 1rem;
        justify-content: center;
    }
    
    .btn-edit, .btn-delete {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 0.6rem 1.5rem;
        border-radius: 30px;
        text-decoration: none;
        transition: all 0.3s;
        font-weight: 500;
    }
    
    .btn-edit {
        background: linear-gradient(135deg, #1976D2, #1565C0);
        color: white;
    }
    
    .btn-edit:hover {
        transform: translateY(-2px);
    }
    
    .btn-delete {
        background: rgba(231,76,60,0.2);
        color: #ff6b6b;
        border: 1px solid rgba(231,76,60,0.3);
    }
    
    .btn-delete:hover {
        background: rgba(231,76,60,0.3);
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
    
    body.light-mode .sponsor-card {
        background: rgba(0,0,0,0.03);
    }
    
    @media (max-width: 768px) {
        .detail-grid {
            grid-template-columns: 1fr;
        }
        .full-width {
            grid-column: span 1;
        }
        .sponsors-grid {
            grid-template-columns: 1fr;
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
                    <h1><i class="fas fa-calendar-alt"></i> <?= htmlspecialchars($event['titre']) ?></h1>
                    <span><?= htmlspecialchars($event['statut']) ?></span>
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
                        <div class="label">Date</div>
                        <div class="value"><?= date('d/m/Y H:i', strtotime($event['date_evenement'])) ?></div>
                    </div>
                    
                    <div class="info-box">
                        <div class="label">Places</div>
                        <div class="value"><?= htmlspecialchars($event['nb_places']) ?></div>
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
                    <img src="../../../uploads/events/<?= htmlspecialchars($event['image']) ?>" alt="<?= htmlspecialchars($event['titre']) ?>">
                </div>
                <?php endif; ?>
                
                <?php if(!empty($event['sponsors'])): ?>
                <div class="sponsors-section">
                    <h3><i class="fas fa-handshake"></i> Sponsors</h3>
                    <div class="sponsors-grid">
                        <?php foreach($event['sponsors'] as $sponsor): ?>
                        <div class="sponsor-card">
                            <h4><?= htmlspecialchars($sponsor['nom_entreprise']) ?></h4>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="action-buttons">
                    <a href="form.php?id=<?= $event['id'] ?>" class="btn-edit">
                        <i class="fas fa-edit"></i> Modifier
                    </a>
                    <a href="list.php?delete=<?= $event['id'] ?>" class="btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet événement ?')">
                        <i class="fas fa-trash"></i> Supprimer
                    </a>
                </div>
            </div>
        </div>
        
        <footer>
            <p>
                <svg width="16" height="16" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align:middle">
                    <path d="M22 4C22 4 8 10 8 24C8 31.732 14.268 38 22 38C29.732 38 36 31.732 36 24C36 14 28 8 22 4Z" fill="#61B3FA" opacity="0.9"/>
                </svg> 
                Eco Ride by Echo Group © 2025 - Gestion des Événements
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