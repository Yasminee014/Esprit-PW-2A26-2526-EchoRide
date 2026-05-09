<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Récupérer l'action (add, edit, delete)
$action = $_GET['action'] ?? $_POST['action'] ?? '';
$vehiculeId = $_GET['id'] ?? null;

// Traitement de l'ajout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add') {
    // Logique d'ajout du véhicule dans la base de données
    // ...
    header('Location: mes_vehicules.php');
    exit;
}

// Traitement de la modification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'edit') {
    $id = $_POST['id'];
    // Logique de modification
    // ...
    header('Location: mes_vehicules.php');
    exit;
}

// Traitement de la suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'delete') {
    $id = $_POST['id'];
    // Logique de suppression
    // ...
    header('Location: mes_vehicules.php');
    exit;
}

// Récupérer les données pour l'édition si action=edit
$vehiculeToEdit = null;
if ($action === 'edit' && $vehiculeId) {
    // Récupérer le véhicule depuis la base
    // $vehiculeToEdit = ...;
}

// Ici tu inclus ta logique métier (récupérer les véhicules, etc.)
// Exemple : require_once __DIR__ . '/../controllers/VehiculeController.php';
// $vehicules = ...;
// $resaCounts = ...;

// Pour l'exemple, je crée des données factices si aucune donnée n'existe
if (!isset($vehicules)) {
    $vehicules = [];
}
if (!isset($resaCounts)) {
    $resaCounts = [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Véhicules | EcoRide</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ========== STYLES SPÉCIFIQUES À LA PAGE MES VÉHICULES ========== */
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
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .hero-small {
            background: linear-gradient(135deg, #1976D2, #0F3B6E);
            border-radius: 20px;
            padding: 1.5rem 2rem;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .hero-small h2 { font-size: 1.5rem; margin-bottom: 0.3rem; }
        .hero-small p { color: rgba(255,255,255,0.8); font-size: 0.85rem; }
        .hero-small-icon { font-size: 3rem; opacity: 0.4; }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .page-header h1 {
            font-size: 1.8rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-add {
            background: linear-gradient(135deg, #1976D2, #61B3FA);
            color: #fff;
            padding: 0.7rem 1.5rem;
            border-radius: 30px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: transform 0.3s;
            border: none;
            cursor: pointer;
        }
        
        .btn-add:hover { transform: translateY(-2px); }
        
        .btn-cancel {
            background: rgba(255,255,255,0.1);
            color: #fff;
            padding: 0.7rem 1.5rem;
            border-radius: 30px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: transform 0.3s;
        }
        
        .btn-cancel:hover { transform: translateY(-2px); background: rgba(255,255,255,0.2); }
        
        /* Formulaire */
        .form-container {
            max-width: 600px;
            margin: 0 auto;
            background: rgba(255,255,255,0.07);
            border-radius: 20px;
            padding: 2rem;
        }
        
        body.light-mode .form-container {
            background: #fff;
            border: 1px solid #e0e0e0;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #A7A9AC;
        }
        
        .form-group input, .form-group select {
            width: 100%;
            padding: 0.8rem;
            border-radius: 10px;
            border: 1px solid rgba(97,179,250,0.3);
            background: rgba(0,0,0,0.3);
            color: #fff;
        }
        
        body.light-mode .form-group input,
        body.light-mode .form-group select {
            background: #f5f5f5;
            color: #333;
            border-color: #ccc;
        }
        
        .form-group input[type="checkbox"] {
            width: auto;
            margin-right: 8px;
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        
        /* Stats grid et autres styles (garde le reste de tes styles existants) */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: rgba(255,255,255,0.07);
            border-radius: 16px;
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            border: 1px solid rgba(97,179,250,0.2);
            transition: transform 0.3s;
        }
        
        body.light-mode .stat-card {
            background: #fff;
            border-color: #e0e0e0;
        }
        
        .stat-card:hover { transform: translateY(-3px); border-color: #61B3FA; }
        
        .stat-icon {
            width: 45px;
            height: 45px;
            background: rgba(97,179,250,0.15);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }
        
        .stat-icon.blue { color: #61B3FA; }
        .stat-icon.gold { color: #f1c40f; }
        .stat-icon.green { color: #27ae60; }
        
        .stat-info .stat-number { font-size: 1.5rem; font-weight: bold; }
        .stat-info .stat-label { font-size: 0.7rem; color: #A7A9AC; }
        
        /* Filters bar */
        .filters-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 0.8rem;
            margin-bottom: 1.5rem;
            background: rgba(255,255,255,0.05);
            padding: 1rem;
            border-radius: 16px;
        }
        
        body.light-mode .filters-bar {
            background: #fff;
            border: 1px solid #e0e0e0;
        }
        
        .filter-group {
            background: rgba(255,255,255,0.08);
            border-radius: 30px;
            padding: 0.5rem 1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        body.light-mode .filter-group {
            background: #f0f0f0;
        }
        
        .filter-group i { color: #61B3FA; }
        
        .filter-group input, .filter-group select {
            background: transparent;
            border: none;
            color: #fff;
            outline: none;
            padding: 0.3rem;
        }
        
        body.light-mode .filter-group input,
        body.light-mode .filter-group select {
            color: #333;
        }
        
        .btn-reset {
            background: rgba(231,76,60,0.2);
            border: 1px solid rgba(231,76,60,0.4);
            color: #e74c3c;
            padding: 0.5rem 1rem;
            border-radius: 30px;
            cursor: pointer;
        }
        
        .vehicules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
        }
        
        .vehicule-card {
            background: rgba(255,255,255,0.07);
            border-radius: 16px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            border: 1px solid rgba(97,179,250,0.15);
        }
        
        body.light-mode .vehicule-card {
            background: #fff;
            border-color: #e0e0e0;
        }
        
        .vehicule-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.3); border-color: #61B3FA; }
        
        .card-image {
            height: 160px;
            overflow: hidden;
            background: #1a1a2e;
        }
        
        .card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }
        
        .vehicule-card:hover .card-image img { transform: scale(1.05); }
        
        .card-header {
            background: linear-gradient(135deg, rgba(25,118,210,0.3), rgba(97,179,250,0.05));
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(97,179,250,0.15);
        }
        
        .card-header h3 { font-size: 1rem; display: flex; align-items: center; gap: 8px; }
        
        .badge {
            padding: 0.25rem 0.7rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        
        .badge-dispo { background: rgba(39,174,96,0.2); color: #27ae60; border: 1px solid rgba(39,174,96,0.4); }
        .badge-indispo { background: rgba(231,76,60,0.2); color: #e74c3c; border: 1px solid rgba(231,76,60,0.4); }
        .badge-maint { background: rgba(241,196,15,0.2); color: #f1c40f; border: 1px solid rgba(241,196,15,0.4); }
        
        .card-body { padding: 1rem; }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .info-item { display: flex; flex-direction: column; gap: 2px; }
        .info-label { font-size: 0.65rem; color: #A7A9AC; text-transform: uppercase; }
        .info-value { font-size: 0.85rem; font-weight: 500; display: flex; align-items: center; gap: 5px; }
        .info-value code { color: #61B3FA; background: rgba(97,179,250,0.1); padding: 2px 5px; border-radius: 5px; }
        
        .resa-badges {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin: 0.8rem 0;
            padding-top: 0.5rem;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        
        .resa-badge {
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: 0.7rem;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        
        .resa-badge.total { background: rgba(97,179,250,0.15); color: #61B3FA; }
        .resa-badge.attente { background: rgba(241,196,15,0.15); color: #f1c40f; }
        .resa-badge.confirmee { background: rgba(39,174,96,0.15); color: #27ae60; }
        
        .card-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.8rem;
        }
        
        .btn-action {
            flex: 1;
            padding: 0.5rem;
            border-radius: 10px;
            font-size: 0.75rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            transition: all 0.3s;
            border: none;
        }
        
        .btn-details { background: rgba(97,179,250,0.15); color: #61B3FA; border: 1px solid rgba(97,179,250,0.4); }
        .btn-details:hover { background: rgba(97,179,250,0.3); transform: translateY(-2px); }
        .btn-edit { background: rgba(241,196,15,0.15); color: #f1c40f; border: 1px solid rgba(241,196,15,0.4); }
        .btn-edit:hover { background: rgba(241,196,15,0.3); transform: translateY(-2px); }
        .btn-delete { background: rgba(231,76,60,0.15); color: #e74c3c; border: 1px solid rgba(231,76,60,0.4); }
        .btn-delete:hover { background: rgba(231,76,60,0.3); transform: translateY(-2px); }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            background: rgba(255,255,255,0.05);
            border-radius: 20px;
        }
        
        @media (max-width: 768px) {
            .container { padding: 1rem; }
            .vehicules-grid { grid-template-columns: 1fr; }
            .card-actions { flex-direction: column; }
            .page-header { flex-direction: column; align-items: stretch; }
            .btn-add { text-align: center; justify-content: center; }
            .form-actions { flex-direction: column; }
        }
    </style>
</head>
<body>

<?php require_once __DIR__ . '/includes/navbar_moderne.php'; ?>

<main class="container">
    
    <?php if ($action === 'add'): ?>
        <!-- FORMULAIRE D'AJOUT -->
        <div class="hero-small">
            <div class="hero-small-content">
                <h2><i class="fas fa-plus"></i> Ajouter un véhicule</h2>
                <p>Remplissez le formulaire ci-dessous</p>
            </div>
            <div class="hero-small-icon">
                <i class="fas fa-car"></i>
            </div>
        </div>

        <div class="form-container">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label>Marque *</label>
                    <input type="text" name="marque" required>
                </div>
                
                <div class="form-group">
                    <label>Modèle *</label>
                    <input type="text" name="modele" required>
                </div>
                
                <div class="form-group">
                    <label>Immatriculation *</label>
                    <input type="text" name="immatriculation" required placeholder="ex: AB-123-CD">
                </div>
                
                <div class="form-group">
                    <label>Couleur</label>
                    <input type="text" name="couleur" placeholder="ex: Rouge">
                </div>
                
                <div class="form-group">
                    <label>Places (1-9) *</label>
                    <input type="number" name="capacite" min="1" max="9" required>
                </div>
                
                <div class="form-group">
                    <label>Statut</label>
                    <select name="statut">
                        <option value="disponible">Disponible</option>
                        <option value="indisponible">Indisponible</option>
                        <option value="en_maintenance">En maintenance</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="climatisation" value="1"> Climatisation
                    </label>
                </div>
                
                <div class="form-group">
                    <label>Photo du véhicule</label>
                    <input type="file" name="photo" accept="image/*">
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-add">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                    <a href="mes_vehicules.php" class="btn-cancel">
                        <i class="fas fa-times"></i> Annuler
                    </a>
                </div>
            </form>
        </div>

    <?php elseif ($action === 'edit' && $vehiculeToEdit): ?>
        <!-- FORMULAIRE DE MODIFICATION -->
        <div class="hero-small">
            <div class="hero-small-content">
                <h2><i class="fas fa-edit"></i> Modifier le véhicule</h2>
                <p>Modifiez les informations ci-dessous</p>
            </div>
            <div class="hero-small-icon">
                <i class="fas fa-car"></i>
            </div>
        </div>

        <div class="form-container">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" value="<?= $vehiculeToEdit['id'] ?>">
                
                <div class="form-group">
                    <label>Marque *</label>
                    <input type="text" name="marque" value="<?= htmlspecialchars($vehiculeToEdit['marque']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Modèle *</label>
                    <input type="text" name="modele" value="<?= htmlspecialchars($vehiculeToEdit['modele']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Immatriculation *</label>
                    <input type="text" name="immatriculation" value="<?= htmlspecialchars($vehiculeToEdit['immatriculation']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Couleur</label>
                    <input type="text" name="couleur" value="<?= htmlspecialchars($vehiculeToEdit['couleur'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label>Places (1-9) *</label>
                    <input type="number" name="capacite" min="1" max="9" value="<?= $vehiculeToEdit['capacite'] ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Statut</label>
                    <select name="statut">
                        <option value="disponible" <?= $vehiculeToEdit['statut'] == 'disponible' ? 'selected' : '' ?>>Disponible</option>
                        <option value="indisponible" <?= $vehiculeToEdit['statut'] == 'indisponible' ? 'selected' : '' ?>>Indisponible</option>
                        <option value="en_maintenance" <?= $vehiculeToEdit['statut'] == 'en_maintenance' ? 'selected' : '' ?>>En maintenance</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="climatisation" value="1" <?= $vehiculeToEdit['climatisation'] ? 'checked' : '' ?>> Climatisation
                    </label>
                </div>
                
                <div class="form-group">
                    <label>Photo du véhicule</label>
                    <input type="file" name="photo" accept="image/*">
                    <?php if (!empty($vehiculeToEdit['photo'])): ?>
                        <small style="color: #A7A9AC;">Photo actuelle: <?= htmlspecialchars($vehiculeToEdit['photo']) ?></small>
                    <?php endif; ?>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-add">
                        <i class="fas fa-save"></i> Mettre à jour
                    </button>
                    <a href="mes_vehicules.php" class="btn-cancel">
                        <i class="fas fa-times"></i> Annuler
                    </a>
                </div>
            </form>
        </div>

    <?php else: ?>
        <!-- AFFICHAGE NORMAL DE LA LISTE DES VÉHICULES -->
        
        <!-- Hero Section -->
        <div class="hero-small">
            <div class="hero-small-content">
                <h2><i class="fas fa-car"></i> Mes Véhicules</h2>
                <p>Gérez votre flotte de véhicules personnels</p>
            </div>
            <div class="hero-small-icon">
                <i class="fas fa-car-side"></i>
            </div>
        </div>

        <!-- Page Header -->
        <div class="page-header">
            <a href="mes_vehicules.php?action=add" class="btn-add">
                <i class="fas fa-plus"></i> Ajouter un véhicule
            </a>
        </div>

        <!-- Statistiques -->
        <?php
            $totalVehicules = count($vehicules ?? []);
            $totalAttente = 0;
            $totalConfirmees = 0;
            foreach ($vehicules ?? [] as $v) {
                $counts = $resaCounts[$v['id']] ?? ['en_attente'=>0, 'confirmee'=>0];
                $totalAttente += $counts['en_attente'];
                $totalConfirmees += $counts['confirmee'];
            }
        ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-car"></i></div>
                <div class="stat-info">
                    <div class="stat-number"><?= $totalVehicules ?></div>
                    <div class="stat-label">Véhicule(s)</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon gold"><i class="fas fa-clock"></i></div>
                <div class="stat-info">
                    <div class="stat-number"><?= $totalAttente ?></div>
                    <div class="stat-label">En attente</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
                <div class="stat-info">
                    <div class="stat-number"><?= $totalConfirmees ?></div>
                    <div class="stat-label">Confirmée(s)</div>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="filters-bar">
            <div class="filter-group">
                <i class="fas fa-search"></i>
                <input type="text" id="searchVehicle" placeholder="Rechercher un véhicule...">
            </div>
            <div class="filter-group">
                <i class="fas fa-filter"></i>
                <select id="filterStatut">
                    <option value="">Tous les statuts</option>
                    <option value="disponible">Disponible</option>
                    <option value="indisponible">Indisponible</option>
                    <option value="en_maintenance">En maintenance</option>
                </select>
            </div>
            <button id="resetFiltersVehicle" class="btn-reset">
                <i class="fas fa-times"></i> Réinitialiser
            </button>
        </div>

        <!-- Liste des véhicules -->
        <?php if (empty($vehicules)): ?>
            <div class="empty-state">
                <i class="fas fa-car-side" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                <p>Vous n'avez pas encore de véhicule enregistré.</p>
                <a href="mes_vehicules.php?action=add" class="btn-add" style="margin-top: 1rem; display: inline-flex;">
                    <i class="fas fa-plus"></i> Ajouter un véhicule
                </a>
            </div>
        <?php else: ?>
            <div class="vehicules-grid" id="vehiculesGrid">
                <?php foreach ($vehicules as $v): ?>
                    <div class="vehicule-card" data-statut="<?= $v['statut'] ?>" data-nom="<?= strtolower($v['marque'] . ' ' . $v['modele']) ?>">
                        
                        <div class="card-image">
                            <?php 
                                $photoPath = '/ecoride/assets/uploads/vehicules/' . ($v['photo'] ?? '');
                                $fullServerPath = $_SERVER['DOCUMENT_ROOT'] . $photoPath;
                                if (!empty($v['photo']) && file_exists($fullServerPath)): 
                            ?>
                                <img src="<?= $photoPath ?>" alt="<?= htmlspecialchars($v['marque'] . ' ' . $v['modele']) ?>">
                            <?php else: ?>
                                <div style="background: linear-gradient(135deg, #1976D2, #0F3B6E); height: 100%; display: flex; align-items: center; justify-content: center; flex-direction: column;">
                                    <i class="fas fa-car" style="font-size: 50px; color: rgba(255,255,255,0.3); margin-bottom: 10px;"></i>
                                    <div style="background: rgba(0,0,0,0.4); padding: 5px 15px; border-radius: 20px;">
                                        <span style="color: white; font-size: 14px;"><?= htmlspecialchars($v['marque']) ?></span>
                                        <span style="color: #61B3FA;"> <?= htmlspecialchars($v['modele']) ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="card-header">
                            <h3><i class="fas fa-car"></i> <?= htmlspecialchars($v['marque'] . ' ' . $v['modele']) ?></h3>
                            <?php
                                $badgeClass = match($v['statut']) {
                                    'disponible' => 'badge-dispo',
                                    'indisponible' => 'badge-indispo',
                                    'en_maintenance' => 'badge-maint',
                                    default => 'badge-indispo'
                                };
                                $statutLabel = match($v['statut']) {
                                    'disponible' => '✅ Disponible',
                                    'indisponible' => '❌ Indisponible',
                                    'en_maintenance' => '🔧 Maintenance',
                                    default => $v['statut']
                                };
                            ?>
                            <span class="badge <?= $badgeClass ?>"><?= $statutLabel ?></span>
                        </div>
                        
                        <div class="card-body">
                            <div class="info-grid">
                                <div class="info-item">
                                    <span class="info-label">Immatriculation</span>
                                    <span class="info-value"><code><?= htmlspecialchars($v['immatriculation']) ?></code></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Couleur</span>
                                    <span class="info-value"><?= htmlspecialchars($v['couleur'] ?? '—') ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Capacité</span>
                                    <span class="info-value"><i class="fas fa-users"></i> <?= $v['capacite'] ?> places</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Climatisation</span>
                                    <span class="info-value"><?= $v['climatisation'] ? '<i class="fas fa-snowflake" style="color:#61B3FA;"></i> Oui' : '<i class="fas fa-sun" style="color:#f1c40f;"></i> Non' ?></span>
                                </div>
                            </div>
                            
                            <?php $counts = $resaCounts[$v['id']] ?? ['en_attente'=>0, 'confirmee'=>0, 'total'=>0]; ?>
                            <div class="resa-badges">
                                <?php if ($counts['total'] == 0): ?>
                                    <span class="resa-badge"><i class="fas fa-calendar-times"></i> Aucune réservation</span>
                                <?php else: ?>
                                    <span class="resa-badge total"><i class="fas fa-calendar-alt"></i> <?= $counts['total'] ?> total</span>
                                    <?php if ($counts['en_attente'] > 0): ?>
                                        <span class="resa-badge attente"><i class="fas fa-clock"></i> <?= $counts['en_attente'] ?> attente</span>
                                    <?php endif; ?>
                                    <?php if ($counts['confirmee'] > 0): ?>
                                        <span class="resa-badge confirmee"><i class="fas fa-check-circle"></i> <?= $counts['confirmee'] ?> confirmée</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            
                          <div class="card-actions">
    <a href="mes_vehicules.php?action=details&id=<?= $v['id'] ?>" class="btn-action btn-details" title="Détails">
        <i class="fas fa-info-circle"></i>
    </a>
    <a href="mes_vehicules.php?action=edit&id=<?= $v['id'] ?>" class="btn-action btn-edit" title="Modifier">
        <i class="fas fa-edit"></i>
    </a>
    <form method="POST" style="margin:0;" onsubmit="return confirm('Supprimer ce véhicule ?')">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" value="<?= $v['id'] ?>">
        <button type="submit" class="btn-action btn-delete" title="Supprimer">
            <i class="fas fa-trash"></i>
        </button>
    </form>
</div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    
</main>

<script>
// Filtres
const searchInput = document.getElementById('searchVehicle');
const filterStatut = document.getElementById('filterStatut');
const resetBtn = document.getElementById('resetFiltersVehicle');
const cards = document.querySelectorAll('.vehicule-card');

function filterVehicles() {
    const searchTerm = searchInput.value.toLowerCase();
    const statut = filterStatut.value;
    cards.forEach(card => {
        let show = true;
        const nom = card.dataset.nom || '';
        if (searchTerm && !nom.includes(searchTerm)) show = false;
        if (statut && card.dataset.statut !== statut) show = false;
        card.style.display = show ? 'block' : 'none';
    });
}

if (searchInput) searchInput.addEventListener('input', filterVehicles);
if (filterStatut) filterStatut.addEventListener('change', filterVehicles);
if (resetBtn) {
    resetBtn.addEventListener('click', () => { 
        if (searchInput) searchInput.value = ''; 
        if (filterStatut) filterStatut.value = ''; 
        filterVehicles(); 
    });
}

// Thème
if (localStorage.getItem('theme') === 'light') {
    document.body.classList.add('light-mode');
}

// Fonction pour le modal des détails
function openDetailsModal(vehicule) {
    const modal = document.createElement('div');
    modal.id = 'detailsModal';
    modal.style.cssText = 'display:flex;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.8);backdrop-filter:blur(5px);z-index:1000;align-items:center;justify-content:center;';
    
    const getColorFromName = (couleur) => {
        const colors = { 
            'rouge': '#e74c3c', 'red': '#e74c3c', 
            'bleu': '#1976D2', 'blue': '#1976D2', 
            'vert': '#27ae60', 'green': '#27ae60', 
            'noir': '#2c3e50', 'black': '#2c3e50', 
            'blanc': '#ecf0f1', 'white': '#ecf0f1', 
            'gris': '#7f8c8d', 'jaune': '#f1c40f', 'yellow': '#f1c40f' 
        };
        return colors[couleur?.toLowerCase()] || '#61B3FA';
    };
    
    const getStatutLabel = (statut) => {
        const statuts = { 
            'disponible': '<span style="color:#27ae60;"><i class="fas fa-check-circle"></i> Disponible</span>', 
            'indisponible': '<span style="color:#e74c3c;"><i class="fas fa-times-circle"></i> Indisponible</span>', 
            'en_maintenance': '<span style="color:#f1c40f;"><i class="fas fa-tools"></i> En maintenance</span>' 
        };
        return statuts[statut] || statut;
    };
    
    modal.innerHTML = `
        <div style="background:linear-gradient(145deg,#0D1F3A,#122A4A);border-radius:20px;width:90%;max-width:450px;padding:1.5rem;border:1px solid rgba(97,179,250,0.3);">
            <div style="display:flex;justify-content:space-between;margin-bottom:1rem;padding-bottom:0.5rem;border-bottom:1px solid rgba(97,179,250,0.2);">
                <h3><i class="fas fa-info-circle" style="color:#61B3FA;"></i> Détails du véhicule</h3>
                <button onclick="this.closest('#detailsModal').remove()" style="background:rgba(255,255,255,0.1);border:none;color:#fff;width:30px;height:30px;border-radius:50%;cursor:pointer;">&times;</button>
            </div>
            <div>
                <div style="display:flex;justify-content:space-between;padding:0.6rem 0;border-bottom:1px solid rgba(255,255,255,0.05);">
                    <span style="color:#A7A9AC;">Marque</span><span>${vehicule.marque || '—'}</span>
                </div>
                <div style="display:flex;justify-content:space-between;padding:0.6rem 0;border-bottom:1px solid rgba(255,255,255,0.05);">
                    <span style="color:#A7A9AC;">Modèle</span><span>${vehicule.modele || '—'}</span>
                </div>
                <div style="display:flex;justify-content:space-between;padding:0.6rem 0;border-bottom:1px solid rgba(255,255,255,0.05);">
                    <span style="color:#A7A9AC;">Immatriculation</span><span><code style="color:#61B3FA;">${vehicule.immatriculation || '—'}</code></span>
                </div>
                <div style="display:flex;justify-content:space-between;padding:0.6rem 0;border-bottom:1px solid rgba(255,255,255,0.05);">
                    <span style="color:#A7A9AC;">Couleur</span>
                    <span><span style="display:inline-block;width:14px;height:14px;border-radius:50%;background:${getColorFromName(vehicule.couleur)};margin-right:8px;"></span>${vehicule.couleur || '—'}</span>
                </div>
                <div style="display:flex;justify-content:space-between;padding:0.6rem 0;border-bottom:1px solid rgba(255,255,255,0.05);">
                    <span style="color:#A7A9AC;">Capacité</span><span>${vehicule.capacite || '—'} places</span>
                </div>
                <div style="display:flex;justify-content:space-between;padding:0.6rem 0;border-bottom:1px solid rgba(255,255,255,0.05);">
                    <span style="color:#A7A9AC;">Climatisation</span>
                    <span>${vehicule.climatisation ? '<i class="fas fa-check-circle" style="color:#27ae60;"></i> Oui' : '<i class="fas fa-times-circle" style="color:#e74c3c;"></i> Non'}</span>
                </div>
                <div style="display:flex;justify-content:space-between;padding:0.6rem 0;">
                    <span style="color:#A7A9AC;">Statut</span><span>${getStatutLabel(vehicule.statut)}</span>
                </div>
            </div>
            <div style="margin-top:1rem;">
                <button onclick="this.closest('#detailsModal').remove()" style="background:rgba(255,255,255,0.1);border:none;color:#fff;padding:0.5rem 1rem;border-radius:10px;cursor:pointer;">Fermer</button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}
</script>

</body>
</html>