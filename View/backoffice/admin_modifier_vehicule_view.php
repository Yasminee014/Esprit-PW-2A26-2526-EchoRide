<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../Config/Database.php';
require_once __DIR__ . '/../../Model/VehiculeModel.php';

$vehiculeModel = new VehiculeModel();
$db = Database::getInstance();

$id = intval($_GET['id'] ?? 0);
$vehicule = $vehiculeModel->getById($id);

if (!$vehicule) {
    $_SESSION['errors'] = ['Véhicule introuvable.'];
    header('Location: admin.php');
    exit;
}

$users = $db->query("SELECT id, nom, prenom FROM users ORDER BY nom, prenom")->fetchAll();

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Gérer l'upload de la photo
    $photoName = $vehicule['photo']; // Conserver l'ancienne photo par défaut
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../assets/uploads/vehicules/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $allowedExtensions = ['jpg', 'jpeg', 'png'];
        
        if (in_array(strtolower($extension), $allowedExtensions)) {
            $photoName = uniqid('vehicule_') . '.' . $extension;
            $uploadPath = $uploadDir . $photoName;
            move_uploaded_file($_FILES['photo']['tmp_name'], $uploadPath);
        }
    }
    
    $data = [
        'user_id' => intval($_POST['user_id'] ?? $vehicule['user_id']),
        'marque' => trim($_POST['marque'] ?? ''),
        'modele' => trim($_POST['modele'] ?? ''),
        'immatriculation' => strtoupper(trim($_POST['immatriculation'] ?? '')),
        'couleur' => trim($_POST['couleur'] ?? ''),
        'capacite' => intval($_POST['capacite'] ?? 4),
        'climatisation' => isset($_POST['climatisation']) ? 1 : 0,
        'statut' => $_POST['statut'] ?? 'disponible',
        'photo' => $photoName
    ];
    
    $errors = $vehiculeModel->validate($data);
    
    // Vérifier si l'immatriculation existe déjà (sauf pour ce véhicule)
    if ($vehiculeModel->immatriculationExists($data['immatriculation'], $id)) {
        $errors[] = "Cette immatriculation (" . htmlspecialchars($data['immatriculation']) . ") est déjà utilisée par un autre véhicule.";
    }
    
    if (empty($errors)) {
        if ($vehiculeModel->update($id, $data)) {
            $_SESSION['success'] = 'Véhicule modifié avec succès.';
            header('Location: admin.php');
            exit;
        } else {
            $errors[] = "Erreur lors de la modification.";
        }
    }
    
    $_SESSION['errors'] = $errors;
    header('Location: admin_modifier_vehicule.php?id=' . $id);
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Modifier un véhicule — EcoRide</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
:root{--blue:#1976D2;--blue-light:#61B3FA;--dark:#0A1628;--dark2:#0D1F3A;--dark3:#0F3B6E;--grey:#A7A9AC;--green:#27ae60;--red:#e74c3c;}
body{font-family:'Poppins',sans-serif;background:linear-gradient(135deg,var(--dark),var(--dark2));color:#fff;min-height:100vh;transition:background 0.3s, color 0.3s;}
.wrap{display:flex;min-height:100vh;}

/* ========== SIDEBAR - MEME STYLE QUE ADMIN.PHP ========== */
.sidebar {
    width: 280px;
    background: linear-gradient(180deg, #2F76BC 0%, #1E5EA5 50%, #174C8A 100%);
    padding: 1.5rem 0;
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    overflow-y: auto;
    z-index: 100;
    box-shadow: 4px 0 20px rgba(0,0,0,0.2);
    display: flex;
    flex-direction: column;
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

.sidebar-header .logo-img {
    width: 80px;
    height: 80px;
    object-fit: contain;
    filter: drop-shadow(0 4px 14px rgba(97,179,250,0.5));
    margin-bottom: 4px;
}

.sidebar-header .logo-text {
    font-size: 1.3rem;
    font-weight: 700;
    color: #A9D6FF;
    letter-spacing: 1px;
    font-family: 'Poppins', sans-serif;
}

.sidebar-header .logo-tagline {
    font-size: 0.65rem;
    color: #BFD8F1;
    margin-top: 2px;
    letter-spacing: 2px;
    font-family: 'Poppins', sans-serif;
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
    font-family: 'Poppins', sans-serif;
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
    font-family: 'Poppins', sans-serif;
    font-weight: 500;
}

.sidebar nav ul li a i {
    width: 22px;
    color: #EAF4FF;
    font-size: 1rem;
}

.sidebar nav ul li a:hover {
    background: rgba(111,168,220,0.3);
    color: white;
    transform: translateX(5px);
}

.sidebar nav ul li a.active {
    background: linear-gradient(135deg, #6FA8DC, #8FC1F5);
    color: #FFFFFF;
    box-shadow: 0 4px 12px rgba(111,168,220,0.3);
}

.sidebar nav ul li a.active i {
    color: #FFFFFF;
}

.sidebar-footer {
    margin-top: auto;
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
    font-family: 'Poppins', sans-serif;
}

.sidebar-footer a i {
    width: 22px;
    color: #FFCDD2;
}

.sidebar-footer a:hover {
    color: #FF8A80;
    transform: translateX(5px);
}

/* Main content */
.main {
    flex: 1;
    margin-left: 280px;
    padding: 1.6rem;
    position: relative;
    z-index: 1;
}

/* ========== HEADER STYLE ========== */
.admin-header {
    background: linear-gradient(90deg, #071C2F, #0A2A47, #0D355B);
    padding: 1rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    border-radius: 12px;
    border-bottom: 1px solid rgba(255,255,255,0.08);
    flex-wrap: wrap;
    gap: 1rem;
}

.admin-logo {
    display: flex;
    flex-direction: column;
}

.admin-logo .logo-eco {
    font-size: 1.5rem;
    font-weight: 700;
    letter-spacing: 1px;
    font-family: 'Poppins', sans-serif;
}

.admin-logo .logo-eco span:first-child {
    color: #4EA3FF;
}

.admin-logo .logo-eco span:last-child {
    color: #6BB8FF;
}

.admin-logo .logo-tagline {
    font-size: 0.65rem;
    color: #A8C1D9;
    margin-top: 2px;
}

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
    font-family: 'Poppins', sans-serif;
}

.admin-nav a:hover {
    background: rgba(255,255,255,0.1);
    color: #FFFFFF;
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

.alert{padding:.8rem 1.2rem;border-radius:12px;margin-bottom:1.2rem;display:flex;align-items:center;gap:9px;font-size:.88rem;}
.alert-success{background:rgba(39,174,96,.14);border:1px solid rgba(39,174,96,.35);color:var(--green);}
.alert-error{background:rgba(231,76,60,.14);border:1px solid rgba(231,76,60,.35);color:var(--red);}

/* Formulaire */
.form-container {
    max-width: 700px;
    margin: 0 auto;
    background: rgba(255,255,255,.04);
    border-radius: 20px;
    padding: 2rem;
    border: 1px solid rgba(78,163,255,.15);
}

.form-group {
    margin-bottom: 1.2rem;
}

label {
    display: block;
    margin-bottom: 0.5rem;
    color: #4EA3FF;
    font-size: 0.85rem;
    font-weight: 600;
}

input, select {
    width: 100%;
    padding: 0.8rem;
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(78,163,255,.3);
    border-radius: 12px;
    color: #fff;
    font-size: 0.9rem;
    outline: none;
    font-family: 'Poppins', sans-serif;
}

input:focus, select:focus {
    border-color: #4EA3FF;
    background: rgba(78,163,255,.1);
}

input[type="file"] {
    padding: 0.5rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.checkbox-group {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    background: rgba(255,255,255,.05);
    padding: 0.8rem 1rem;
    border-radius: 12px;
    border: 1px solid rgba(78,163,255,.2);
    margin: 1rem 0;
}

.checkbox-group input {
    width: auto;
}

.checkbox-group label {
    margin: 0;
    cursor: pointer;
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
    justify-content: flex-end;
}

.btn-primary {
    background: linear-gradient(135deg, #1976D2, #4EA3FF);
    color: #fff;
    border: none;
    padding: 0.8rem 2rem;
    border-radius: 30px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
    font-family: 'Poppins', sans-serif;
    font-weight: 500;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(78,163,255,0.4);
}

.btn-secondary {
    background: rgba(255,255,255,.1);
    color: #CFE6FF;
    border: 1px solid rgba(78,163,255,.3);
    padding: 0.8rem 2rem;
    border-radius: 30px;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
    font-family: 'Poppins', sans-serif;
}

.btn-secondary:hover {
    background: rgba(231,76,60,0.2);
    color: #e74c3c;
    border-color: rgba(231,76,60,0.4);
}

.current-photo {
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid rgba(78,163,255,.2);
}

.current-photo label {
    margin-bottom: 0.5rem;
}

.current-photo img {
    width: 120px;
    height: 90px;
    object-fit: cover;
    border-radius: 10px;
    border: 2px solid #4EA3FF;
}

small {
    color: var(--grey);
    font-size: 0.7rem;
    display: block;
    margin-top: 5px;
}

.image-preview {
    margin-top: 10px;
}

.image-preview img {
    max-width: 200px;
    border-radius: 10px;
    border: 2px solid #4EA3FF;
}

body.light-mode .sidebar {
    background: linear-gradient(180deg, #2F76BC, #1E5EA5, #174C8A);
}
body.light-mode .form-container { background: white; }
body.light-mode input, body.light-mode select { background: #f5f5f5; color: #333; }

@media (max-width: 768px) {
    .sidebar { display: none; }
    .main { margin-left: 0; }
    .form-row { grid-template-columns: 1fr; }
}
</style>
</head>
<body>
<div class="wrap">

<!-- SIDEBAR - MEME QUE ADMIN.PHP -->
<aside class="sidebar">
    <div class="sidebar-header">
        <a href="admin.php" class="logo">
            <img src="../../assets/images/photo.png" alt="EcoRide Logo" class="logo-img">
            <div class="logo-text">EcoRide</div>
            <div class="logo-tagline">ADMINISTRATION</div>
        </a>
    </div>
    
    <div class="nav-section">GESTION</div>
    <nav>
        <ul>
            <li><a href="admin.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="passagers.php"><i class="fas fa-users"></i> Passagers</a></li>
            <li><a href="admin_trajet.php?page=trajets"><i class="fas fa-route"></i> Trajets</a></li>
            <li><a href="admin_trajet.php?page=destinations"><i class="fas fa-map-pin"></i> Destinations</a></li>
            <li><a href="evenements.php"><i class="fas fa-calendar-alt"></i> Événements</a></li>
            <li><a href="/ecoride/View/backoffice/admin_reclamations.php"><i class="fas fa-exclamation-triangle"></i> Réclamations</a></li>
            <li><a href="admin.php" class="active"><i class="fas fa-car"></i> Véhicules</a></li>
            <li><a href="lostfound_admin.php"><i class="fas fa-search-location"></i> Objets perdus</a></li>
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
        <div class="logo-eco">
            <span>ECO</span> <span>RIDE</span>
        </div>
        <div class="logo-tagline">Covoiturage Intelligent</div>
    </div>
    <div class="admin-nav">
        <a href="/ecoride/View/frontoffice/tous_les_trajets.php">Voir site</a>
        <a href="profil.php" class="profile-btn"><i class="fas fa-user"></i> Profil</a>
        <a href="admin.php" class="admin-btn">Admin</a>
        <button class="theme-btn" onclick="toggleTheme()" id="themeBtn">
            <i class="fas fa-moon"></i>
        </button>
    </div>
</div>

<?php if (!empty($_SESSION['success'])): ?>
  <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['success']) ?></div>
  <?php unset($_SESSION['success']); endif;
if (!empty($_SESSION['errors'])): ?>
  <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php foreach($_SESSION['errors'] as $e) echo htmlspecialchars($e) . ' '; unset($_SESSION['errors']); ?></div>
<?php endif; ?>

<div class="form-container">
    <form method="POST" enctype="multipart/form-data">
        <div class="form-row">
            <div class="form-group">
                <label>Marque</label>
                <input type="text" name="marque" value="<?= htmlspecialchars($vehicule['marque']) ?>" required>
            </div>
            <div class="form-group">
                <label>Modèle</label>
                <input type="text" name="modele" value="<?= htmlspecialchars($vehicule['modele']) ?>" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Immatriculation</label>
                <input type="text" name="immatriculation" placeholder="AB-123-CD" value="<?= htmlspecialchars($vehicule['immatriculation']) ?>" required>
            </div>
            <div class="form-group">
                <label>Couleur</label>
                <input type="text" name="couleur" value="<?= htmlspecialchars($vehicule['couleur'] ?? '') ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Places (1-9)</label>
                <input type="number" name="capacite" min="1" max="9" value="<?= $vehicule['capacite'] ?>" required>
            </div>
            <div class="form-group">
                <label>Statut</label>
                <select name="statut">
                    <option value="disponible" <?= $vehicule['statut'] === 'disponible' ? 'selected' : '' ?>>Disponible</option>
                    <option value="indisponible" <?= $vehicule['statut'] === 'indisponible' ? 'selected' : '' ?>>Indisponible</option>
                    <option value="en_maintenance" <?= $vehicule['statut'] === 'en_maintenance' ? 'selected' : '' ?>>En maintenance</option>
                </select>
            </div>
        </div>

        <!-- Photo actuelle -->
        <?php if (!empty($vehicule['photo'])): ?>
        <div class="current-photo">
            <label>Photo actuelle</label>
            <div><img src="/ecoride/assets/uploads/vehicules/<?= $vehicule['photo'] ?>" alt="Photo actuelle"></div>
        </div>
        <?php endif; ?>

        <div class="form-group">
            <label>Nouvelle photo (optionnel)</label>
            <input type="file" name="photo" accept="image/jpeg,image/png,image/jpg" id="photoInput">
            <small>Laissez vide pour conserver l'image actuelle</small>
            <div id="photoPreviewContainer" style="display: none; margin-top: 10px;">
                <img id="photoPreview" src="#" alt="Aperçu" style="max-width: 200px; border-radius: 10px; border: 2px solid #4EA3FF;">
            </div>
        </div>

        <div class="checkbox-group">
            <input type="checkbox" name="climatisation" id="climatisation" <?= $vehicule['climatisation'] ? 'checked' : '' ?>>
            <label for="climatisation"><i class="fas fa-snowflake"></i> Climatisation</label>
        </div>

        <div class="form-actions">
            <a href="admin.php" class="btn-secondary"><i class="fas fa-times"></i> Annuler</a>
            <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
        </div>
    </form>
</div>

</main>
</div>

<script>
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

// Aperçu de la photo
document.getElementById('photoInput')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            const preview = document.getElementById('photoPreview');
            const container = document.getElementById('photoPreviewContainer');
            preview.src = event.target.result;
            container.style.display = 'block';
        }
        reader.readAsDataURL(file);
    }
});

// Disparition des alertes
setTimeout(() => {
    document.querySelectorAll('.alert').forEach(a => {
        a.style.transition = 'opacity 0.5s';
        a.style.opacity = '0';
        setTimeout(() => a.remove(), 500);
    });
}, 4000);
</script>
</body>
</html>