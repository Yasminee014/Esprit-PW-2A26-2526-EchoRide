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

$sponsorModel = new Sponsor();
$eventModel = new Event();
$sponsor = null;
$events = $eventModel->getAll();
$error = '';

$uploadDir = __DIR__ . '/../../../uploads/sponsors/';
if(!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

if(isset($_GET['id'])) { $sponsor = $sponsorModel->getById($_GET['id']); }

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $logoName = $sponsor['logo'] ?? null;
    if(isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        if(in_array($ext, $allowed)) {
            $logoName = time() . '_' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['logo']['tmp_name'], $uploadDir . $logoName);
            if(isset($sponsor['logo']) && $sponsor['logo'] && file_exists($uploadDir . $sponsor['logo'])) unlink($uploadDir . $sponsor['logo']);
        } else $error = "Format de logo non autorisé";
    }
    if(empty($error)) {
        if(isset($_POST['id']) && !empty($_POST['id'])) $sponsorModel->update($_POST['id'], $_POST, $logoName);
        else $sponsorModel->add($_POST, $logoName);
        header('Location: list.php?success=' . (isset($_POST['id']) ? 'updated' : 'added'));
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Eco Ride - <?= isset($sponsor) ? 'Modifier' : 'Ajouter' ?> sponsor</title>
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
    
    /* Layout */
    .app-wrapper {
        display: flex;
        width: 100%;
        min-height: 100vh;
    }
    
    /* Sidebar fixe gauche */
    .sidebar {
        width: 260px;
        background: linear-gradient(180deg, #1976D2 0%, #1565C0 40%, #0F3B6E 100%);
        position: fixed;
        left: 0;
        top: 0;
        height: 100vh;
        padding: 2rem 1rem;
        overflow-y: auto;
        z-index: 100;
        display: flex;
        flex-direction: column;
    }
    
    /* Contenu principal droite */
    .main-content {
        margin-left: 260px;
        width: calc(100% - 260px);
        min-height: 100vh;
        padding: 1.5rem 2rem;
    }
    
    /* Top bar */
    .top-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        background: linear-gradient(90deg, #0D2350 0%, #0F3166 50%, #0D2350 100%);
        border-radius: 16px;
        padding: 0.75rem 1.5rem;
        border: 1px solid rgba(97,179,250,0.18);
        box-shadow: 0 4px 24px rgba(0,0,0,0.25);
        position: sticky;
        top: 0;
        z-index: 50;
    }
    
    .navbar-logo strong {
        font-size: 1rem;
        font-weight: 800;
        color: #61B3FA;
        letter-spacing: 0.05em;
    }
    
    .navbar-logo span {
        font-size: 0.62rem;
        color: rgba(255,255,255,0.75);
        letter-spacing: 0.08em;
    }
    
    .top-bar-right {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
    }
    
    .btn-top, .btn-admin-profile, .btn-admin-plain, .btn-theme-toggle {
        background: transparent;
        color: white;
        padding: 0.4rem 1rem;
        border-radius: 25px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
        font-size: 0.85rem;
        border: 1px solid rgba(255,255,255,0.18);
        cursor: pointer;
    }
    
    .btn-top:hover, .btn-admin-profile:hover, .btn-admin-plain:hover {
        background: rgba(255,255,255,0.12);
    }
    
    .btn-admin-plain {
        border-color: rgba(231,76,60,0.45);
        color: #E74C3C;
    }
    
    .btn-theme-toggle {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        justify-content: center;
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
    
    /* Card */
    .card {
        background: rgba(13, 31, 45, 0.9);
        border-radius: 20px;
        padding: 1.5rem;
        border: 1px solid rgba(25,118,210,0.3);
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
        gap: 10px;
    }
    
    /* Form */
    .form-group {
        margin-bottom: 1.2rem;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        color: #61B3FA;
        font-size: 0.85rem;
        font-weight: 500;
    }
    
    .form-group input, 
    .form-group select, 
    .form-group textarea {
        width: 100%;
        padding: 0.8rem 1rem;
        border-radius: 12px;
        border: 1px solid rgba(25,118,210,0.3);
        background: rgba(10, 47, 68, 0.8);
        color: white;
        font-family: inherit;
        font-size: 0.9rem;
    }
    
    .form-group input:focus, 
    .form-group select:focus, 
    .form-group textarea:focus {
        outline: none;
        border-color: #1976D2;
    }
    
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    
    .error {
        color: #ff6b6b;
        font-size: 0.75rem;
        margin-top: 0.25rem;
    }
    
    .preview-logo {
        margin-top: 0.5rem;
    }
    
    .preview-logo img {
        max-width: 100px;
        border-radius: 10px;
    }
    
    .btn-submit {
        background: linear-gradient(135deg, #1976D2, #1565C0);
        color: white;
        padding: 0.8rem 2rem;
        border: none;
        border-radius: 25px;
        cursor: pointer;
        font-size: 1rem;
        font-weight: 600;
        transition: all 0.3s;
        width: 100%;
        margin-top: 1rem;
    }
    
    .btn-submit:hover {
        transform: translateY(-2px);
        background: linear-gradient(135deg, #1e88e5, #1976D2);
    }
    
    .alert-error {
        background: rgba(231,76,60,0.15);
        color: #e74c3c;
        padding: 1rem;
        border-radius: 10px;
        margin-bottom: 1rem;
        border: 1px solid rgba(231,76,60,0.3);
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
    
    body.light-mode .card {
        background: rgba(255,255,255,0.95);
    }
    
    body.light-mode .form-group input, 
    body.light-mode .form-group select, 
    body.light-mode .form-group textarea {
        background: white;
        color: #1A2844;
    }
    
    @media (max-width: 768px) {
        .sidebar {
            transform: translateX(-100%);
            transition: transform 0.3s;
        }
        .sidebar.open {
            transform: translateX(0);
        }
        .main-content {
            margin-left: 0;
            width: 100%;
            padding: 1rem;
        }
        .form-row {
            grid-template-columns: 1fr;
        }
    }
</style>
</head>
<body>

<div class="app-wrapper">
    <?php sidebar_spa('evenements'); ?>
    
    <div class="main-content">
        <!-- Top bar -->
        <div class="top-bar">
            <div class="navbar-logo">
                <strong>ECO RIDE</strong>
                <span>Covoiturage Intelligent</span>
            </div>
            <div class="top-bar-right">
                <a href="<?= BASE_URL ?>controllers/UserController.php?action=showLoginForm#hero" class="btn-top">Voir site</a>
                <a href="<?= BASE_URL ?>controllers/AdminController.php?action=showProfile" class="btn-admin-profile">
                    <div class="admin-avatar-btn">
                        <?php if (!empty($_SESSION['admin_photo'])): ?>
                            <img src="<?= BASE_URL ?>uploads/photos/<?= htmlspecialchars($_SESSION['admin_photo']) ?>" alt="" style="width:24px;height:24px;border-radius:50%;object-fit:cover;">
                        <?php else: ?>
                            <i class="fas fa-user-shield"></i>
                        <?php endif; ?>
                    </div>
                    Profil
                </a>
                <a href="<?= BASE_URL ?>controllers/AdminController.php?action=dashboard" class="btn-admin-plain">Admin</a>
                <button class="btn-theme-toggle" onclick="toggleTheme()">
                    <i class="fas fa-moon themeIcon"></i>
                </button>
            </div>
        </div>
        
        <a href="list.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Retour à la liste
        </a>
        
        <div class="card">
            <div class="card-header">
                <h2><i class="fas <?= isset($sponsor) ? 'fa-edit' : 'fa-plus' ?>"></i> <?= isset($sponsor) ? 'Modifier' : 'Ajouter' ?> un sponsor</h2>
            </div>
            
            <?php if($error): ?>
                <div class="alert-error"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" onsubmit="return validateSponsorForm()">
                <?php if(isset($sponsor)): ?>
                    <input type="hidden" name="id" value="<?= $sponsor['id'] ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label>Nom entreprise *</label>
                    <input type="text" name="nom_entreprise" id="nom_entreprise" value="<?= htmlspecialchars($sponsor['nom_entreprise'] ?? '') ?>">
                    <div id="nomError" class="error"></div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Montant sponsoring (DT) *</label>
                        <input type="number" name="montant_sponsoring" id="montant_sponsoring" step="0.01" value="<?= $sponsor['montant_sponsoring'] ?? '' ?>">
                        <div id="montantError" class="error"></div>
                    </div>
                    <div class="form-group">
                        <label>Type sponsor</label>
                        <select name="type_sponsor">
                            <option value="">Sélectionner</option>
                            <option value="gold" <?= (isset($sponsor['type_sponsor']) && $sponsor['type_sponsor'] == 'gold') ? 'selected' : '' ?>>Gold</option>
                            <option value="silver" <?= (isset($sponsor['type_sponsor']) && $sponsor['type_sponsor'] == 'silver') ? 'selected' : '' ?>>Silver</option>
                            <option value="bronze" <?= (isset($sponsor['type_sponsor']) && $sponsor['type_sponsor'] == 'bronze') ? 'selected' : '' ?>>Bronze</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Statut</label>
                        <select name="statut">
                            <option value="en_attente" <?= (isset($sponsor['statut']) && $sponsor['statut'] == 'en_attente') ? 'selected' : '' ?>>En attente</option>
                            <option value="confirme" <?= (isset($sponsor['statut']) && $sponsor['statut'] == 'confirme') ? 'selected' : '' ?>>Confirmé</option>
                            <option value="refuse" <?= (isset($sponsor['statut']) && $sponsor['statut'] == 'refuse') ? 'selected' : '' ?>>Refusé</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Événement associé</label>
                        <select name="evenement_id">
                            <option value="">Aucun</option>
                            <?php foreach($events as $e): ?>
                            <option value="<?= $e['id'] ?>" <?= (isset($sponsor['evenement_id']) && $sponsor['evenement_id'] == $e['id']) ? 'selected' : '' ?>><?= htmlspecialchars($e['titre']) ?> - <?= date('d/m/Y', strtotime($e['date_evenement'])) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="description" rows="4" placeholder="Description du sponsor..."><?= htmlspecialchars($sponsor['description'] ?? '') ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Logo</label>
                    <input type="file" name="logo" accept="image/*" id="logoInput">
                    <div class="preview-logo" id="logoPreview">
                        <?php if(isset($sponsor['logo']) && $sponsor['logo']): ?>
                            <img src="../../../uploads/sponsors/<?= $sponsor['logo'] ?>">
                        <?php endif; ?>
                    </div>
                </div>
                
                <button type="submit" class="btn-submit"><?= isset($sponsor) ? 'Modifier' : 'Ajouter' ?></button>
            </form>
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

<script>
    document.getElementById('logoInput').addEventListener('change', function(e) {
        const preview = document.getElementById('logoPreview');
        preview.innerHTML = '';
        const file = e.target.files[0];
        if(file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.style.maxWidth = '100px';
                img.style.borderRadius = '10px';
                preview.appendChild(img);
            };
            reader.readAsDataURL(file);
        }
    });
    
    function validateSponsorForm() {
        let isValid = true;
        
        const nom = document.getElementById('nom_entreprise').value;
        if(!nom.trim()) {
            document.getElementById('nomError').innerText = 'Le nom de l\'entreprise est requis';
            isValid = false;
        } else {
            document.getElementById('nomError').innerText = '';
        }
        
        const montant = document.getElementById('montant_sponsoring').value;
        if(!montant || montant < 0) {
            document.getElementById('montantError').innerText = 'Le montant doit être supérieur à 0';
            isValid = false;
        } else {
            document.getElementById('montantError').innerText = '';
        }
        
        return isValid;
    }
    
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