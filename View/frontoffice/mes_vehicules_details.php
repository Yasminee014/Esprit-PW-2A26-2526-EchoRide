<?php
// Ce fichier : /View/frontoffice/mes_vehicules_details.php
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Eco Ride - Détails du véhicule</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #0A1628;
        color: #fff;
        transition: background 0.3s, color 0.3s;
    }
    
    body.light-mode {
        background: #f5f5f5;
        color: #333;
    }
    
    body.light-mode .detail-card {
        background: #fff;
        border-color: #e0e0e0;
    }
    
    body.light-mode .info-item {
        background: #f5f5f5;
    }
    
    body.light-mode .info-item .value {
        color: #333;
    }
    
    .detail-container {
        max-width: 800px;
        margin: 2rem auto;
        padding: 0 2rem;
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
    
    .detail-info {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .info-item {
        background: rgba(255,255,255,0.03);
        border-radius: 12px;
        padding: 1rem;
    }
    
    .info-item .label {
        font-size: 0.7rem;
        color: #A7A9AC;
        text-transform: uppercase;
        margin-bottom: 0.3rem;
    }
    
    .info-item .value {
        font-size: 1rem;
        font-weight: 600;
        color: white;
    }
    
    .detail-image {
        text-align: center;
        margin: 1rem 0;
    }
    
    .detail-image img {
        max-width: 100%;
        max-height: 300px;
        border-radius: 15px;
    }
    
    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(108,117,125,0.3);
        padding: 0.5rem 1rem;
        border-radius: 30px;
        text-decoration: none;
        color: white;
        margin-bottom: 1rem;
    }
    
    .btn-back:hover {
        background: rgba(108,117,125,0.5);
    }
    
    .badge {
        padding: 0.25rem 0.7rem;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 600;
    }
    
    .badge-dispo { background: rgba(39,174,96,0.2); color: #27ae60; border: 1px solid rgba(39,174,96,0.4); }
    .badge-indispo { background: rgba(231,76,60,0.2); color: #e74c3c; border: 1px solid rgba(231,76,60,0.4); }
    .badge-maint { background: rgba(241,196,15,0.2); color: #f1c40f; border: 1px solid rgba(241,196,15,0.4); }
    
    @media (max-width: 768px) {
        .detail-info { grid-template-columns: 1fr; }
        .detail-container { padding: 0 1rem; }
    }
</style>
</head>
<body>

<!-- ══ NAVBAR ══ -->
<?php include_once __DIR__ . '/partials/navbar.php'; ?>

<div class="detail-container">
    <a href="mes_vehicules.php" class="btn-back">
        <i class="fas fa-arrow-left"></i> Retour à mes véhicules
    </a>
    
    <div class="detail-card">
        <div class="detail-header">
            <h1><i class="fas fa-car"></i> <?= htmlspecialchars($vehicule['marque'] . ' ' . $vehicule['modele']) ?></h1>
            <?php
                $badgeClass = match($vehicule['statut']) {
                    'disponible' => 'badge-dispo',
                    'indisponible' => 'badge-indispo',
                    'en_maintenance' => 'badge-maint',
                    default => 'badge-indispo'
                };
                $statutLabel = match($vehicule['statut']) {
                    'disponible' => '✅ Disponible',
                    'indisponible' => '❌ Indisponible',
                    'en_maintenance' => '🔧 Maintenance',
                    default => $vehicule['statut']
                };
            ?>
            <span class="badge <?= $badgeClass ?>"><?= $statutLabel ?></span>
        </div>
        
        <div class="detail-info">
            <div class="info-item">
                <div class="label">Marque</div>
                <div class="value"><?= htmlspecialchars($vehicule['marque']) ?></div>
            </div>
            <div class="info-item">
                <div class="label">Modèle</div>
                <div class="value"><?= htmlspecialchars($vehicule['modele']) ?></div>
            </div>
            <div class="info-item">
                <div class="label">Immatriculation</div>
                <div class="value"><code style="color:#61B3FA;"><?= htmlspecialchars($vehicule['immatriculation']) ?></code></div>
            </div>
            <div class="info-item">
                <div class="label">Couleur</div>
                <div class="value"><?= htmlspecialchars($vehicule['couleur'] ?? '—') ?></div>
            </div>
            <div class="info-item">
                <div class="label">Capacité</div>
                <div class="value"><i class="fas fa-users"></i> <?= $vehicule['capacite'] ?> places</div>
            </div>
            <div class="info-item">
                <div class="label">Climatisation</div>
                <div class="value"><?= $vehicule['climatisation'] ? '<i class="fas fa-snowflake" style="color:#61B3FA;"></i> Oui' : '<i class="fas fa-sun" style="color:#f1c40f;"></i> Non' ?></div>
            </div>
        </div>
        
        <?php if(!empty($vehicule['photo'])): 
            $photoPath = '/ecoride/assets/uploads/vehicules/' . $vehicule['photo'];
            $fullServerPath = $_SERVER['DOCUMENT_ROOT'] . $photoPath;
            if(file_exists($fullServerPath)):
        ?>
        <div class="detail-image">
            <img src="<?= $photoPath ?>" alt="Photo du véhicule">
        </div>
        <?php endif; endif; ?>
        
        <!-- ========== BOUTON MODIFIER SUPPRIMÉ ========== -->
        
    </div>
</div>

<script>
if (localStorage.getItem('theme') === 'light') {
    document.body.classList.add('light-mode');
}
</script>

</body>
</html>