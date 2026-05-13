<?php require_once __DIR__ . '/../../config.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eco Ride - Mon Espace</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>views/frontoffice/navbar-front.css">
    <?php define('ECORIDE_NAVBAR_CSS_LINKED', true); ?>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Poppins','Segoe UI',sans-serif; background:linear-gradient(135deg,#0A1628 0%,#0D1F3A 100%); min-height:100vh; color:#fff; }
        .dashboard-container { max-width:1100px; margin:2.5rem auto; padding:0 2rem; }

        /* ── Welcome card ── */
        .welcome-card {
            display: flex;
            align-items: center;
            gap: 2rem;
            background: rgba(13,31,58,0.85);
            border: 1px solid rgba(25,118,210,0.25);
            border-radius: 24px;
            padding: 2rem 2.5rem;
            margin-bottom: 2.5rem;
            position: relative;
            overflow: hidden;
        }
        .welcome-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(135deg, rgba(25,118,210,0.07) 0%, transparent 60%);
            pointer-events: none;
        }
        .welcome-avatar {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            border: 3px solid #1976D2;
            overflow: hidden;
            background: rgba(25,118,210,0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 0 20px rgba(25,118,210,0.3);
        }
        .welcome-avatar img { width:100%; height:100%; object-fit:cover; }
        .welcome-avatar i { font-size:2.5rem; color:#1976D2; }
        .welcome-text { flex: 1; }
        .welcome-text h2 { font-size:2rem; margin-bottom:0.3rem; }
        .welcome-text p { color:#61B3FA; font-size:1rem; margin-bottom:0.6rem; }
        .highlight { color:#1976D2; }
        .role-badge { display:inline-flex; align-items:center; gap:6px; background:rgba(25,118,210,0.15); border:1px solid #1976D2; color:#1976D2; padding:0.3rem 0.9rem; border-radius:20px; font-size:0.8rem; }
        .welcome-actions { display:flex; flex-direction:column; gap:0.7rem; flex-shrink:0; }
        .btn-profile-quick { display:inline-flex; align-items:center; gap:7px; background:rgba(25,118,210,0.15); color:#1976D2; border:1px solid rgba(25,118,210,0.35); padding:0.5rem 1.1rem; border-radius:20px; text-decoration:none; font-size:0.85rem; transition:all 0.3s; }
        .btn-profile-quick:hover { background:rgba(25,118,210,0.3); }
        @media(max-width:650px){
            .welcome-card { flex-direction:column; text-align:center; }
            .welcome-actions { flex-direction:row; flex-wrap:wrap; justify-content:center; }
        }

        .stats-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:1.5rem; margin-bottom:2.5rem; }
        .stat-card { background:rgba(13,31,58,0.9); border-radius:20px; padding:1.5rem; border:1px solid rgba(25,118,210,0.2); text-align:center; transition:all 0.3s; }
        .stat-card:hover { border-color:#1976D2; transform:translateY(-3px); box-shadow:0 8px 25px rgba(25,118,210,0.15); }
        .stat-card i { font-size:2.5rem; color:#1976D2; margin-bottom:1rem; display:block; }
        .stat-card h3 { font-size:2rem; margin:0 0 0.8rem; }
        .stat-card p { color:#A7A9AC; font-size:0.9rem; line-height:1.6; }
        .actions-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(250px,1fr)); gap:1.5rem; margin-bottom:2.5rem; }
        .action-card { background:rgba(13,31,58,0.9); border-radius:20px; padding:2rem; border:1px solid rgba(25,118,210,0.2); transition:all 0.3s; text-decoration:none; color:white; display:block; }
        .action-card:hover { border-color:#1976D2; transform:translateY(-3px); box-shadow:0 8px 25px rgba(25,118,210,0.15); }
        .action-card i { font-size:2rem; color:#1976D2; margin-bottom:1rem; display:block; }
        .action-card h3 { margin-bottom:0.5rem; }
        .action-card p { color:#61B3FA; font-size:0.85rem; }
        footer { text-align:center; padding:2rem; border-top:1px solid rgba(25,118,210,0.2); color:#A7A9AC; margin-top:4rem; }
    </style>
</head>
<body>
<?php
// ── Navbar partagée frontoffice ──
require_once __DIR__ . '/../../config.php';

include __DIR__ . '/navbar.php';
?>

<div class="dashboard-container">

    <!-- Welcome card alignée à gauche avec photo -->
    <div class="welcome-card">
        <!-- Avatar / Photo de profil avec flip -->
        <?php
        // ── Calcul des URLs avatar et photo ────────────────────
        $dashAvatars = [
            'av1'  => 'https://api.dicebear.com/8.x/avataaars/svg?seed=Amira&backgroundColor=1565c0&mouth=smile',
            'av2'  => 'https://api.dicebear.com/8.x/avataaars/svg?seed=Nour&backgroundColor=6a1b9a&eyes=happy&mouth=smile',
            'av3'  => 'https://api.dicebear.com/8.x/avataaars/svg?seed=Karim&backgroundColor=00695c',
            'av4'  => 'https://api.dicebear.com/8.x/avataaars/svg?seed=Omar&backgroundColor=1a237e&eyes=wink&mouth=smile',
            'av5'  => 'https://api.dicebear.com/8.x/avataaars/svg?seed=Lina&backgroundColor=b71c1c&mouth=smile',
            'av6'  => 'https://api.dicebear.com/8.x/avataaars/svg?seed=Adam&backgroundColor=004d40&eyes=happy',
            'av7'  => 'https://api.dicebear.com/8.x/micah/svg?seed=Sofia&backgroundColor=880e4f&baseColor=f5d0b5',
            'av8'  => 'https://api.dicebear.com/8.x/micah/svg?seed=Emma&backgroundColor=e65100&baseColor=d4a574',
            'av9'  => 'https://api.dicebear.com/8.x/micah/svg?seed=Zara&backgroundColor=33691e&baseColor=c68642',
            'av10' => 'https://api.dicebear.com/8.x/bottts/svg?seed=RoboGamer&backgroundColor=001a33',
            'av11' => 'https://api.dicebear.com/8.x/bottts/svg?seed=CyberBot&backgroundColor=1a0033',
            'av12' => 'https://api.dicebear.com/8.x/bottts/svg?seed=MegaDroid&backgroundColor=0a1628',
            'av13' => 'https://api.dicebear.com/8.x/pixel-art/svg?seed=Gamer1&backgroundColor=1b0033',
            'av14' => 'https://api.dicebear.com/8.x/pixel-art/svg?seed=NinjaX&backgroundColor=0a1628',
            'av15' => 'https://api.dicebear.com/8.x/pixel-art/svg?seed=PixelHero&backgroundColor=002200',
        ];

        // Photo réelle
        $dashPhoto = !empty($_SESSION['user_photo'])
            ? BASE_URL . 'uploads/photos/' . htmlspecialchars($_SESSION['user_photo'])
            : '';

        // Avatar : custom personnalisé OU prédéfini
        $dashAvatarUrl = '';
        $dashAvOpts = $_SESSION['user_avatar_options'] ?? [];
        $dashAvKey  = $_SESSION['user_avatar'] ?? '';
        if (!empty($dashAvOpts) && isset($dashAvOpts['seed'])) {
            // Avatar personnalisé
            $o = $dashAvOpts;
            $dashAvatarUrl = 'https://api.dicebear.com/8.x/avataaars/svg'
                . '?seed='            . urlencode($o['seed'])
                . '&backgroundColor=' . ($o['backgroundColor'] ?? '1565c0')
                . '&skinColor='       . ($o['skinColor']       ?? 'ffdbb4')
                . '&hairColor='       . ($o['hairColor']        ?? '2c1b18')
                . '&top='             . ($o['top']              ?? 'shortHairShortFlat')
                . '&clothesType='     . ($o['clothesType']      ?? 'hoodie')
                . '&clothesColor='    . ($o['clothesColor']     ?? '3c4a6e')
                . '&eyes=default&mouth=smile';
        } elseif ($dashAvKey && isset($dashAvatars[$dashAvKey])) {
            // Avatar prédéfini
            $dashAvatarUrl = $dashAvatars[$dashAvKey];
        }
        ?>
        <div class="welcome-avatar" id="dashAvatarWrap" style="perspective:600px;cursor:pointer;" onclick="dashFlip()">
            <div id="dashFlipper" style="width:100%;height:100%;position:relative;transform-style:preserve-3d;transition:transform 0.7s cubic-bezier(.4,0,.2,1);">
                <!-- Face avant : PHOTO réelle -->
                <div style="position:absolute;inset:0;backface-visibility:hidden;border-radius:50%;overflow:hidden;display:flex;align-items:center;justify-content:center;background:rgba(25,118,210,0.12);">
                    <?php if ($dashPhoto): ?>
                        <img src="<?= $dashPhoto ?>" alt="Photo" style="width:100%;height:100%;object-fit:cover;">
                    <?php else: ?>
                        <i class="fas fa-user" style="font-size:2.5rem;color:#1976D2;"></i>
                    <?php endif; ?>
                </div>
                <!-- Face arrière : AVATAR choisi (exact) -->
                <div id="dashAvatarBack" style="position:absolute;inset:0;backface-visibility:hidden;transform:rotateY(180deg);border-radius:50%;overflow:hidden;display:flex;align-items:center;justify-content:center;background:rgba(25,118,210,0.15);">
                    <?php if ($dashAvatarUrl): ?>
                        <img src="<?= htmlspecialchars($dashAvatarUrl) ?>" alt="Avatar" style="width:100%;height:100%;object-fit:cover;">
                    <?php else: ?>
                        <i class="fas fa-robot" style="font-size:2.5rem;color:#1976D2;"></i>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Texte de bienvenue -->
        <div class="welcome-text">
            <h2>Bonjour, <span class="highlight"><?= htmlspecialchars($_SESSION['user_prenom'] ?? '') ?> !</span> 👋</h2>
            <p>Bienvenue sur votre espace Eco Ride</p>
            <div class="role-badge">
                <?php if ($_SESSION['user_role'] === 'conducteur'): ?>
                    <i class="fas fa-car"></i> Conducteur
                <?php else: ?>
                    <i class="fas fa-user"></i> Passager
                <?php endif; ?>
            </div>
        </div>


    </div>

    <div class="actions-grid">
        <a href="<?= BASE_URL ?>views/frontoffice/covoiturage.php" class="action-card">
            <i class="fas fa-search"></i>
            <h3>Rechercher un trajet</h3>
            <p>Trouvez un covoiturage près de chez vous</p>
        </a>
        <?php if ($_SESSION['user_role'] === 'conducteur'): ?>
        <a href="<?= BASE_URL ?>views/frontoffice/mes-vehicules.php" class="action-card">
            <i class="fas fa-plus-circle"></i>
            <h3>Proposer un trajet</h3>
            <p>Partagez votre trajet avec des passagers</p>
        </a>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>controllers/UserController.php?action=profile" class="action-card">
            <i class="fas fa-user-edit"></i>
            <h3>Mon Profil</h3>
            <p>Modifier vos informations personnelles</p>
        </a>
        <a href="<?= BASE_URL ?>views/frontoffice/mes-trajets.php" class="action-card">
            <i class="fas fa-history"></i>
            <h3>Historique</h3>
            <p>Voir vos trajets et réservations passés</p>
        </a>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <i class="fas fa-route"></i>
            <h3>0</h3>
            <p>Trajets effectués</p>
        </div>
        <div class="stat-card">
            <i class="fas fa-calendar-check"></i>
            <h3>0</h3>
            <p>Réservations</p>
        </div>
        <div class="stat-card">
            <i class="fas fa-star"></i>
            <h3>—</h3>
            <p>Note moyenne</p>
        </div>
        <div class="stat-card">
            <svg width="32" height="32" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg" style="filter:drop-shadow(0 0 8px rgba(97,179,250,.45))"><path d="M22 4C22 4 8 10 8 24C8 31.732 14.268 38 22 38C29.732 38 36 31.732 36 24C36 14 28 8 22 4Z" fill="url(#lg_s)" opacity="0.95"/><path d="M22 38L22 12" stroke="rgba(255,255,255,0.3)" stroke-width="1.2" stroke-linecap="round"/><defs><linearGradient id="lg_s" x1="12" y1="4" x2="36" y2="38" gradientUnits="userSpaceOnUse"><stop offset="0%" stop-color="#61B3FA"/><stop offset="100%" stop-color="#1976D2"/></linearGradient></defs></svg>
            <h3>0 kg</h3>
            <p>CO₂ économisé</p>
        </div>
    </div>
</div>

<footer>
    <p><svg width="16" height="16" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align:middle"><path d="M22 4C22 4 8 10 8 24C8 31.732 14.268 38 22 38C29.732 38 36 31.732 36 24C36 14 28 8 22 4Z" fill="#61B3FA" opacity="0.9"/></svg> Eco Ride by Echo Group © 2025 - Covoiturage Intelligent et Écologique</p>
</footer>
<script>
function toggleDropdown(e) {
    e.stopPropagation();
    document.getElementById('profileDropMenu').classList.toggle('open');
}
document.addEventListener('click', function() {
    document.getElementById('profileDropMenu').classList.remove('open');
});

// ── Flip avatar dashboard ──────────────────────────
var dashFace = 0;
var dashHasPhoto  = <?= $dashPhoto      ? 'true' : 'false' ?>;
var dashHasAvatar = <?= $dashAvatarUrl  ? 'true' : 'false' ?>;
var dashTimer = null;

function dashFlip() {
    clearTimeout(dashTimer);
    dashFace = dashFace === 0 ? 1 : 0;
    document.getElementById('dashFlipper').style.transform = dashFace === 1 ? 'rotateY(180deg)' : '';
    if (dashHasPhoto && dashHasAvatar) scheduleDashFlip();
}
function scheduleDashFlip() {
    clearTimeout(dashTimer);
    dashTimer = setTimeout(dashFlip, 3500);
}
// Démarrer auto uniquement si les deux existent
if (dashHasPhoto && dashHasAvatar) scheduleDashFlip();

    // ── Dark / Light mode ──
    function toggleThemeFront() {
        document.body.classList.toggle('light-mode');
        const isLight = document.body.classList.contains('light-mode');
        document.querySelectorAll('.themeIconFront').forEach(i => {
            i.className = isLight ? 'fas fa-sun themeIconFront' : 'fas fa-moon themeIconFront';
        });
        localStorage.setItem('ecoride_theme', isLight ? 'light' : 'dark');
    }
    (function() {
        if (localStorage.getItem('ecoride_theme') === 'light') {
            document.body.classList.add('light-mode');
            document.querySelectorAll('.themeIconFront').forEach(i => { i.className = 'fas fa-sun themeIconFront'; });
        }
    })();

    </script>
<?php require_once __DIR__ . '/chatbot_widget.php'; ?>
</body>
</html>