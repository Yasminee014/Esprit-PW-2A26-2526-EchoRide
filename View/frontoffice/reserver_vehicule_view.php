<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../Controller/ReservationController.php';

$vehicule = $vehicule ?? null;
if (!$vehicule) {
    header('Location: vehicules_disponibles.php');
    exit;
}

$couleurMap = [
    'rouge'=>'#e74c3c', 'red'=>'#e74c3c', 'bleu'=>'#1976D2', 'blue'=>'#1976D2',
    'vert'=>'#27ae60', 'green'=>'#27ae60', 'noir'=>'#2c3e50', 'black'=>'#2c3e50',
    'blanc'=>'#ecf0f1', 'white'=>'#ecf0f1', 'gris'=>'#7f8c8d', 'jaune'=>'#f1c40f'
];
$couleurNom = strtolower(trim($vehicule['couleur'] ?? 'gris'));
$couleurHex = $couleurMap[$couleurNom] ?? '#7f8c8d';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réserver un véhicule | EcoRide</title>
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
        body.light-mode .navbar {
            background: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        body.light-mode .navbar .logo,
        body.light-mode .navbar .dropdown-btn,
        body.light-mode .navbar .user-info {
            color: #1976D2;
        }
        body.light-mode .dropdown-content {
            background: #fff;
            border: 1px solid #e0e0e0;
        }
        body.light-mode .dropdown-content a {
            color: #333;
        }
        body.light-mode .vehicule-card,
        body.light-mode .form-card {
            background: #fff;
            border-color: #e0e0e0;
        }
        body.light-mode .hero-small {
            background: linear-gradient(135deg, #1565C0, #0D47A1);
        }

        .navbar {
            background: linear-gradient(90deg, #1976D2, #0F3B6E);
            padding: 0.8rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .nav-left { display: flex; align-items: center; gap: 2rem; }
        .logo { display: flex; align-items: center; gap: 8px; font-size: 1.3rem; font-weight: 700; color: #fff; text-decoration: none; }
        .logo i { color: #61B3FA; }
        .dropdown { position: relative; display: inline-block; }
        .dropdown-btn {
            background: rgba(255,255,255,0.1);
            color: #fff;
            padding: 0.6rem 1.2rem;
            border: 1px solid rgba(97,179,250,.4);
            border-radius: 30px;
            font-size: 0.9rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .dropdown-content {
            display: none;
            position: absolute;
            top: 110%;
            left: 0;
            min-width: 220px;
            background: linear-gradient(145deg, #0D1F3A, #122A4A);
            border: 1px solid rgba(97,179,250,.3);
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0,0,0,.4);
            z-index: 200;
            overflow: hidden;
        }
        .dropdown-content.show { display: block; animation: fadeInDown 0.25s ease; }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .dropdown-content a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.8rem 1.2rem;
            color: #fff;
            text-decoration: none;
            font-size: 0.85rem;
            transition: all 0.2s;
        }
        .dropdown-content a i { width: 20px; color: #61B3FA; }
        .dropdown-content a:hover { background: rgba(97,179,250,.15); padding-left: 1.5rem; }
        .dropdown-divider { height: 1px; background: rgba(97,179,250,.2); margin: 0.3rem 0; }
        .nav-right { display: flex; align-items: center; gap: 1rem; }
        .user-info {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,0.1);
            padding: 0.4rem 1rem;
            border-radius: 30px;
            font-size: 0.85rem;
        }
        .theme-btn {
            background: rgba(255,255,255,0.1);
            border: none;
            color: #fff;
            padding: 0.4rem 0.8rem;
            border-radius: 30px;
            cursor: pointer;
        }

        .container { max-width: 1000px; margin: 0 auto; padding: 2rem; }

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

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }
        @media (max-width: 700px) { .form-grid { grid-template-columns: 1fr; } }

        .vehicule-card, .form-card {
            background: rgba(255,255,255,0.07);
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid rgba(97,179,250,0.2);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .vehicule-card:hover, .form-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.3); }

        .car-visual {
            height: 180px;
            overflow: hidden;
            position: relative;
        }
        .car-visual img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .car-badge {
            position: absolute;
            bottom: 10px;
            right: 12px;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(6px);
            border-radius: 20px;
            padding: 0.25rem 0.75rem;
            font-size: 0.7rem;
        }
        .vehicule-info { padding: 1.5rem; }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.8rem;
            margin-top: 1rem;
        }
        .info-item {
            background: rgba(255,255,255,0.05);
            border-radius: 10px;
            padding: 0.7rem;
        }
        .info-item .lbl { font-size: 0.7rem; color: #A7A9AC; text-transform: uppercase; }
        .info-item .val { font-size: 0.85rem; font-weight: 500; }

        .form-card { padding: 2rem; }
        .form-group { margin-bottom: 1.2rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; color: #61B3FA; font-size: 0.85rem; font-weight: 600; }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 0.8rem;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(97,179,250,0.3);
            border-radius: 12px;
            color: #fff;
            font-size: 0.9rem;
            outline: none;
        }
        .form-group input:focus, .form-group textarea:focus { border-color: #61B3FA; }
        .field-error { color: #e74c3c; font-size: 0.75rem; margin-top: 0.25rem; display: block; }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 1.5rem;
        }
        .btn-annuler {
            background: rgba(255,255,255,0.08);
            color: #A7A9AC;
            border: 1px solid rgba(255,255,255,0.2);
            padding: 0.7rem 1.5rem;
            border-radius: 12px;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-confirmer {
            background: linear-gradient(135deg, #1976D2, #61B3FA);
            color: #fff;
            border: none;
            padding: 0.7rem 1.5rem;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
        }
        .btn-confirmer:hover { transform: translateY(-2px); }

        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 0.8rem 1.5rem;
            border-radius: 10px;
            z-index: 1000;
            animation: slideIn 0.3s ease;
        }
        .toast.success { background: #27ae60; color: white; }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @media (max-width: 768px) {
            .navbar { flex-direction: column; gap: 1rem; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="nav-left">
        <a href="../index.php" class="logo"><i class="fas fa-leaf"></i><span>EcoRide</span></a>
        <div class="dropdown">
            <button class="dropdown-btn" onclick="toggleDropdown()"><i class="fas fa-bars"></i><span>Menu</span></button>
            <div class="dropdown-content" id="dropdownMenu">
                <a href="vehicules_disponibles.php"><i class="fas fa-car"></i> Covoiturages</a>
                <a href="mes_reservations.php"><i class="fas fa-calendar-check"></i> Mes réservations</a>
                <a href="mes_vehicules.php"><i class="fas fa-key"></i> Mes véhicules</a>
                <a href="mon_historique.php"><i class="fas fa-history"></i> Mon historique</a>
                <div class="dropdown-divider"></div>
                <a href="../backoffice/admin.php" class="admin-link"><i class="fas fa-shield-alt"></i> Administration</a>
                <a href="logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
            </div>
        </div>
    </div>
    <div class="nav-right">
        <button id="themeToggle" class="theme-btn"><i class="fas fa-moon"></i></button>
        <div class="user-info"><i class="fas fa-user-circle"></i><span><?= $_SESSION['user_name'] ?? 'Utilisateur' ?></span></div>
    </div>
</nav>

<div class="container">

    <div class="hero-small">
        <div class="hero-small-content">
            <h2><i class="fas fa-calendar-plus"></i> Réserver un véhicule</h2>
            <p>Confirmez votre réservation en quelques clics</p>
        </div>
        <div class="hero-small-icon"><i class="fas fa-calendar-check"></i></div>
    </div>

    <div class="form-grid">
        <div class="vehicule-card">
            <div class="car-visual">
                <img src="../assets/generate_car_image.php?marque=<?= urlencode($vehicule['marque']) ?>&modele=<?= urlencode($vehicule['modele']) ?>&couleur=<?= urlencode($vehicule['couleur'] ?? 'bleu') ?>" 
                     alt="<?= htmlspecialchars($vehicule['marque'] . ' ' . $vehicule['modele']) ?>">
                <div class="car-badge">
                    <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:<?= $couleurHex ?>;margin-right:5px;"></span>
                    <?= ucfirst($vehicule['couleur'] ?? '—') ?> · <?= $vehicule['modele'] ?>
                </div>
            </div>
            <div class="vehicule-info">
                <h2><?= htmlspecialchars($vehicule['marque'] . ' ' . $vehicule['modele']) ?></h2>
                <div class="conducteur"><i class="fas fa-user"></i> <?= htmlspecialchars(($vehicule['prenom'] ?? '') . ' ' . ($vehicule['nom'] ?? '')) ?></div>
                <div class="info-grid">
                    <div class="info-item"><div class="lbl">Immatriculation</div><div class="val"><code><?= htmlspecialchars($vehicule['immatriculation']) ?></code></div></div>
                    <div class="info-item"><div class="lbl">Places</div><div class="val"><i class="fas fa-users"></i> <?= $vehicule['capacite'] ?></div></div>
                    <div class="info-item"><div class="lbl">Couleur</div><div class="val"><?= ucfirst($vehicule['couleur'] ?? '—') ?></div></div>
                    <div class="info-item"><div class="lbl">Climatisation</div><div class="val"><?= $vehicule['climatisation'] ? '<i class="fas fa-snowflake"></i> Oui' : '<i class="fas fa-sun"></i> Non' ?></div></div>
                </div>
            </div>
        </div>

        <form method="POST" action="reserver_vehicule.php" id="reservationForm" class="form-card">
            <h3><i class="fas fa-calendar-plus"></i> Détails de la réservation</h3>
            <input type="hidden" name="action" value="reserver">
            <input type="hidden" name="vehicule_id" value="<?= $vehicule['id'] ?>">

            <div class="form-group">
                <label><i class="fas fa-calendar-alt"></i> Date de réservation <span style="color:#e74c3c">*</span></label>
                <input type="text" name="date_reservation_display" id="dateReservation" placeholder="JJ/MM/AAAA" autocomplete="off">
                <input type="hidden" name="date_reservation" id="dateReservationHidden">
                <span class="field-error" id="dateError"></span>
            </div>

            <div class="form-group">
                <label><i class="fas fa-sticky-note"></i> Note (optionnel)</label>
                <textarea name="note" id="note" rows="3" placeholder="Ex: Je prendrai à 8h devant la gare…"></textarea>
            </div>

            <div class="form-actions">
                <a href="vehicules_disponibles.php" class="btn-annuler"><i class="fas fa-times"></i> Annuler</a>
                <button type="submit" class="btn-confirmer"><i class="fas fa-check"></i> Confirmer</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleDropdown() { document.getElementById("dropdownMenu").classList.toggle("show"); }
window.onclick = function(event) {
    if (!event.target.matches('.dropdown-btn')) {
        var dropdowns = document.getElementsByClassName("dropdown-content");
        for (var i = 0; i < dropdowns.length; i++) {
            if (dropdowns[i].classList.contains('show')) dropdowns[i].classList.remove('show');
        }
    }
}

function formatDate(value) {
    let cleaned = value.replace(/\D/g, '');
    if (cleaned.length >= 2 && cleaned.length < 4) cleaned = cleaned.substring(0,2) + '/' + cleaned.substring(2);
    else if (cleaned.length >= 4 && cleaned.length < 6) cleaned = cleaned.substring(0,2) + '/' + cleaned.substring(2,4) + '/' + cleaned.substring(4);
    else if (cleaned.length >= 6) cleaned = cleaned.substring(0,2) + '/' + cleaned.substring(2,4) + '/' + cleaned.substring(4,8);
    return cleaned;
}

function validateDate() {
    const input = document.getElementById('dateReservation');
    const hidden = document.getElementById('dateReservationHidden');
    const error = document.getElementById('dateError');
    let value = input.value.trim();
    if (!value) { error.innerHTML = 'La date est obligatoire.'; return false; }
    const match = value.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
    if (!match) { error.innerHTML = 'Format JJ/MM/AAAA'; return false; }
    const day = parseInt(match[1]), month = parseInt(match[2]), year = parseInt(match[3]);
    if (month < 1 || month > 12) { error.innerHTML = 'Mois invalide'; return false; }
    const daysInMonth = new Date(year, month, 0).getDate();
    if (day < 1 || day > daysInMonth) { error.innerHTML = 'Jour invalide'; return false; }
    const selected = new Date(year, month-1, day);
    const today = new Date(); today.setHours(0,0,0,0);
    if (selected < today) { error.innerHTML = 'Date non passée'; return false; }
    error.innerHTML = '';
    hidden.value = `${year}-${String(month).padStart(2,'0')}-${String(day).padStart(2,'0')}`;
    return true;
}

const dateInput = document.getElementById('dateReservation');
dateInput.addEventListener('input', function() {
    let pos = this.selectionStart;
    let oldLen = this.value.length;
    let formatted = formatDate(this.value);
    this.value = formatted;
    let newLen = formatted.length;
    this.setSelectionRange(pos + (newLen - oldLen), pos + (newLen - oldLen));
    validateDate();
});
dateInput.addEventListener('blur', validateDate);

document.getElementById('reservationForm').addEventListener('submit', function(e) {
    if (!validateDate()) e.preventDefault();
});

function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `<i class="fas fa-check-circle"></i> ${message}`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

const themeToggle = document.getElementById('themeToggle');
if (localStorage.getItem('theme') === 'light') {
    document.body.classList.add('light-mode');
    themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
}
themeToggle.addEventListener('click', () => {
    document.body.classList.toggle('light-mode');
    const isLight = document.body.classList.contains('light-mode');
    localStorage.setItem('theme', isLight ? 'light' : 'dark');
    themeToggle.innerHTML = isLight ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
    showToast(isLight ? 'Mode clair activé' : 'Mode sombre activé', 'success');
});
</script>
</body>
</html>