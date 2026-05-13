<?php
// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: http://localhost/projetadmin/controllers/UserController.php?action=showLoginForm');
    exit();
}

// Définir BASE_URL
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/projetadmin/');
}

$profileErrors  = $_SESSION['profile_errors']    ?? [];
$profileOld     = $_SESSION['profile_old_input'] ?? [];
$profileSuccess = $_SESSION['profile_success']   ?? '';
$pwdErrors      = $_SESSION['pwd_errors']        ?? [];

unset($_SESSION['profile_errors'], $_SESSION['profile_old_input'],
      $_SESSION['profile_success'], $_SESSION['pwd_errors']);

$user = [
    'id'             => $_SESSION['user_id'],
    'prenom'         => $_SESSION['user_prenom'],
    'nom'            => $_SESSION['user_nom'],
    'email'          => $_SESSION['user_email'],
    'role'           => $_SESSION['user_role'],
    'telephone'      => $_SESSION['user_telephone']      ?? '',
    'photo'          => $_SESSION['user_photo']          ?? '',
    'avatar'         => $_SESSION['user_avatar']         ?? '',
    'avatar_options' => $_SESSION['user_avatar_options'] ?? [],
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eco Ride - Mon Profil</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>views/frontoffice/navbar-front.css">
    <?php define('ECORIDE_NAVBAR_CSS_LINKED', true); ?>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Vos styles existants */
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Poppins','Segoe UI',sans-serif; background:linear-gradient(135deg,#0A1628 0%,#0D1F3A 100%); min-height:100vh; color:#fff; }
        .profile-container { max-width:900px; margin:3rem auto; padding:0 2rem; }
        .profile-header { text-align:center; margin-bottom:2.5rem; }
        .avatar-wrapper {
            position: relative;
            width: 110px; height: 110px;
            margin: 0 auto 1rem;
            perspective: 700px;
        }
        .avatar-flipper {
            width: 100%; height: 100%;
            position: relative;
            transform-style: preserve-3d;
            transition: transform 0.7s cubic-bezier(.4,0,.2,1);
        }
        .avatar-flipper.flipped { transform: rotateY(180deg); }
        .avatar-face {
            position: absolute; inset: 0;
            backface-visibility: hidden;
            border-radius: 50%;
            border: 3px solid #1976D2;
            overflow: hidden;
            background: rgba(25,118,210,0.15);
            display: flex; align-items: center; justify-content: center;
        }
        .avatar-face.back { transform: rotateY(180deg); }
        .avatar-face img { width:100%; height:100%; object-fit:cover; }
        .avatar-face i { font-size:2.8rem; color:#1976D2; }
        .avatar-face svg { width:80%; height:80%; }

        /* Indicateur photo/avatar */
        .avatar-indicators {
            display: flex; justify-content: center; gap: 6px; margin-bottom: 0.6rem;
        }
        .avatar-dot {
            width: 8px; height: 8px; border-radius: 50%;
            background: rgba(255,255,255,.25); cursor: pointer;
            transition: background 0.3s;
        }
        .avatar-dot.active { background: #1976D2; }

        /* Boutons photo/avatar */
        .avatar-btns { display:flex; gap:0.6rem; justify-content:center; flex-wrap:wrap; margin-bottom:0.5rem; }
        .upload-photo-label { display:inline-flex; align-items:center; gap:6px; color:#1976D2; font-size:0.82rem; cursor:pointer; padding:0.32rem 0.85rem; border-radius:20px; border:1px solid rgba(25,118,210,0.35); background:rgba(25,118,210,0.1); transition:all 0.3s; }
        .upload-photo-label:hover { background:rgba(25,118,210,0.25); }
        .upload-photo-label input { display:none; }
        .btn-choose-avatar { display:inline-flex; align-items:center; gap:6px; color:#a0aab4; font-size:0.82rem; cursor:pointer; padding:0.32rem 0.85rem; border-radius:20px; border:1px solid rgba(160,170,180,0.35); background:rgba(160,170,180,0.1); transition:all 0.3s; }
        .btn-choose-avatar:hover { background:rgba(160,170,180,0.22); }

        /* Modal avatar picker */
        .avatar-modal-overlay {
            display:none; position:fixed; inset:0;
            background:rgba(0,0,0,0.65); z-index:500;
            align-items:center; justify-content:center;
        }
        .avatar-modal-overlay.open { display:flex; }
        .avatar-modal {
            background:#0d1f3c; border:1px solid rgba(25,118,210,0.35);
            border-radius:20px; padding:2rem; width:92%; max-width:480px;
            box-shadow:0 20px 60px rgba(0,0,0,0.6);
        }
        .avatar-modal h3 { color:#1976D2; margin-bottom:1.2rem; display:flex; align-items:center; gap:8px; }
        .avatar-category-label {
            font-size:0.72rem; font-weight:600; letter-spacing:0.08em;
            color:#61B3FA; text-transform:uppercase; margin:0.8rem 0 0.5rem;
            display:flex; align-items:center; gap:6px;
        }
        .avatar-category-label:first-child { margin-top:0; }
        .avatar-grid {
            display:grid; grid-template-columns:repeat(5,1fr); gap:0.7rem;
            margin-bottom:0.5rem;
        }
        .avatar-grid.robots, .avatar-grid.games {
            grid-template-columns:repeat(3,1fr);
        }
        .avatar-opt {
            width:60px; height:60px; border-radius:50%;
            border:2px solid rgba(255,255,255,.12);
            overflow:hidden; cursor:pointer; transition:all 0.2s;
            background:rgba(25,118,210,0.1);
            display:flex; align-items:center; justify-content:center;
        }
        .avatar-opt:hover, .avatar-opt.selected { border-color:#1976D2; transform:scale(1.08); }
        .avatar-opt img { width:100%; height:100%; object-fit:cover; }
        .avatar-opt svg { width:70%; height:70%; }
        .avatar-modal-btns { display:flex; gap:0.8rem; justify-content:flex-end; margin-top:1rem; }
        .btn-cancel-av { padding:0.5rem 1.2rem; border-radius:20px; border:1px solid rgba(255,255,255,.2); background:transparent; color:#aaa; cursor:pointer; font-size:0.88rem; }
        .btn-save-av { padding:0.5rem 1.4rem; border-radius:20px; border:none; background:#1976D2; color:#fff; cursor:pointer; font-size:0.88rem; font-weight:600; }

        /* Tabs modal */
        .modal-tabs { display:flex; gap:4px; margin-bottom:1.2rem; background:rgba(0,0,0,0.25); border-radius:12px; padding:4px; }
        .modal-tab { flex:1; padding:0.42rem 0; border-radius:9px; border:none; background:transparent; color:#aaa; font-size:0.78rem; font-weight:500; cursor:pointer; transition:all 0.2s; font-family:'Poppins',sans-serif; }
        .modal-tab.active { background:#1976D2; color:#fff; }
        .tab-panel { display:none; }
        .tab-panel.active { display:block; }

        /* Personnalisateur */
        .custom-preview { width:90px; height:90px; border-radius:50%; border:3px solid #1976D2; margin:0 auto 1rem; overflow:hidden; background:rgba(25,118,210,0.15); }
        .custom-preview img { width:100%; height:100%; object-fit:cover; }
        .custom-row { margin-bottom:0.85rem; }
        .custom-row label { display:block; font-size:0.74rem; color:#61B3FA; margin-bottom:0.35rem; font-weight:500; }
        .custom-swatches { display:flex; gap:5px; flex-wrap:wrap; }
        .swatch { width:26px; height:26px; border-radius:50%; cursor:pointer; border:2px solid transparent; transition:all 0.18s; flex-shrink:0; }
        .swatch:hover { transform:scale(1.15); }
        .swatch.active { border-color:#fff; box-shadow:0 0 0 2px #1976D2; }
        .custom-select { width:100%; padding:0.42rem 0.75rem; border-radius:10px; border:1px solid rgba(25,118,210,0.3); background:rgba(10,22,40,0.8); color:#fff; font-size:0.8rem; font-family:'Poppins',sans-serif; }
        .custom-select:focus { outline:none; border-color:#1976D2; }
        .profile-header h2 { font-size:1.8rem; }
        .profile-header p { color:#61B3FA; }
        .role-badge { display:inline-block; background:rgba(25,118,210,0.2); border:1px solid #1976D2; color:#1976D2; padding:0.3rem 0.8rem; border-radius:20px; font-size:0.8rem; margin-top:0.5rem; }
        .profile-grid { display:grid; grid-template-columns:1fr 1fr; gap:2rem; }
        @media(max-width:768px) { .profile-grid { grid-template-columns:1fr; } }
        .card { background:rgba(13,31,58,0.9); border-radius:20px; padding:2rem; border:1px solid rgba(25,118,210,0.2); }
        .card h3 { color:#1976D2; margin-bottom:1.5rem; display:flex; align-items:center; gap:10px; font-size:1.2rem; }
        .form-group { margin-bottom:1.2rem; }
        .form-group label { display:block; margin-bottom:0.5rem; color:#61B3FA; font-size:0.9rem; }
        .form-group label i { margin-right:8px; color:#1976D2; }
        .form-group input { width:100%; padding:0.8rem 1rem; border-radius:12px; border:1px solid rgba(25,118,210,0.3); background:rgba(10,22,40,0.8); color:white; font-size:0.95rem; }
        .form-group input:focus { outline:none; border-color:#1976D2; box-shadow:0 0 10px rgba(25,118,210,0.2); }
        .form-group input.error-field { border-color:#ff4444; }
        .error-msg { color:#ff6b6b; font-size:0.8rem; margin-top:4px; display:block; }
        .alert { padding:0.8rem 1.2rem; border-radius:12px; margin-bottom:1.5rem; display:flex; align-items:center; gap:10px; font-size:0.9rem; }
        .alert-success { background:rgba(0,200,100,0.15); border:1px solid rgba(0,200,100,0.4); color:#4cff9a; }
        .alert-error { background:rgba(255,68,68,0.15); border:1px solid rgba(255,68,68,0.4); color:#ff6b6b; }
        .btn-submit { background:linear-gradient(135deg,#1976D2,#1976D2); color:white; padding:0.8rem 1.5rem; border:none; border-radius:30px; cursor:pointer; font-weight:600; width:100%; font-size:1rem; transition:all 0.3s; margin-top:0.5rem; }
        .btn-submit:hover { transform:translateY(-2px); box-shadow:0 5px 20px rgba(25,118,210,0.3); }
        .btn-back-dashboard { display:inline-flex; align-items:center; justify-content:center; gap:0.5rem; margin:2rem auto 0; padding:0.85rem 1.5rem; background:rgba(25,118,210,0.15); border:1px solid rgba(25,118,210,0.35); color:#fff; border-radius:30px; text-decoration:none; font-weight:600; transition:all 0.3s; }
        .btn-back-dashboard:hover { background:rgba(25,118,210,0.3); transform:translateY(-2px); }
        .info-text { color:#A7A9AC; font-size:0.78rem; margin-bottom:1rem; }
        .info-text i { color:#1976D2; }
        footer { text-align:center; padding:2rem; border-top:1px solid rgba(25,118,210,0.2); color:#A7A9AC; margin-top:4rem; }
    </style>
    <style>
        /* Light Mode Styles */
        body.light-mode {
            background: linear-gradient(135deg, #E3F2FD 0%, #BBDEFB 100%);
            color: #263238;
        }
        body.light-mode .profile-container {
            color: #263238;
        }
        body.light-mode .avatar-face {
            background: rgba(25,118,210,0.1);
            border-color: #1976D2;
        }
        body.light-mode .avatar-face i {
            color: #1976D2;
        }
        body.light-mode .avatar-indicators .avatar-dot {
            background: rgba(38,50,56,0.3);
        }
        body.light-mode .avatar-indicators .avatar-dot.active {
            background: #1976D2;
        }
        body.light-mode .upload-photo-label {
            color: #1976D2;
            border-color: rgba(25,118,210,0.3);
            background: rgba(25,118,210,0.1);
        }
        body.light-mode .upload-photo-label:hover {
            background: rgba(25,118,210,0.2);
        }
        body.light-mode .btn-choose-avatar {
            color: #37474F;
            border-color: rgba(55,71,79,0.3);
            background: rgba(55,71,79,0.1);
        }
        body.light-mode .btn-choose-avatar:hover {
            background: rgba(55,71,79,0.2);
        }
        body.light-mode .avatar-modal {
            background: #FAFAFA;
            border-color: rgba(25,118,210,0.3);
        }
        body.light-mode .avatar-modal h3 {
            color: #1976D2;
        }
        body.light-mode .avatar-category-label {
            color: #1976D2;
        }
        body.light-mode .avatar-opt {
            border-color: rgba(255,255,255,0.3);
            background: rgba(25,118,210,0.05);
        }
        body.light-mode .avatar-opt:hover,
        body.light-mode .avatar-opt.selected {
            border-color: #1976D2;
        }
        body.light-mode .modal-tabs {
            background: rgba(0,0,0,0.1);
        }
        body.light-mode .modal-tab {
            color: #37474F;
        }
        body.light-mode .modal-tab.active {
            background: #1976D2;
            color: #fff;
        }
        body.light-mode .custom-select {
            background: rgba(255,255,255,0.9);
            border-color: rgba(25,118,210,0.3);
            color: #263238;
        }
        body.light-mode .profile-header h2 {
            color: #263238;
        }
        body.light-mode .profile-header p {
            color: #1976D2;
        }
        body.light-mode .role-badge {
            background: rgba(25,118,210,0.2);
            border-color: #1976D2;
            color: #1976D2;
        }
        body.light-mode .card {
            background: rgba(255,255,255,0.95);
            border-color: rgba(25,118,210,0.2);
        }
        body.light-mode .card h3 {
            color: #1976D2;
        }
        body.light-mode .form-group label {
            color: #1976D2;
        }
        body.light-mode .form-group input {
            background: rgba(255,255,255,0.9);
            border-color: rgba(25,118,210,0.3);
            color: #263238;
        }
        body.light-mode .form-group input:focus {
            border-color: #1976D2;
            box-shadow: 0 0 10px rgba(25,118,210,0.2);
        }
        body.light-mode .alert-success {
            background: rgba(76,175,80,0.15);
            border-color: rgba(76,175,80,0.4);
            color: #2E7D32;
        }
        body.light-mode .alert-error {
            background: rgba(244,67,54,0.15);
            border-color: rgba(244,67,54,0.4);
            color: #C62828;
        }
        body.light-mode .btn-submit {
            background: linear-gradient(135deg, #1976D2, #1976D2);
            color: white;
        }
        body.light-mode .btn-submit:hover {
            box-shadow: 0 5px 20px rgba(25,118,210,0.3);
        }
        body.light-mode .info-text {
            color: #546E7A;
        }
        body.light-mode .info-text i {
            color: #1976D2;
        }
        body.light-mode footer {
            border-top-color: rgba(25,118,210,0.2);
            color: #546E7A;
        }
        body.light-mode .btn-cancel-av {
            border-color: rgba(255,255,255,0.3);
            background: rgba(255,255,255,0.9);
            color: #37474F;
        }
        body.light-mode .btn-save-av {
            background: #1976D2;
            color: #fff;
        }
    </style>
</head>
<body>

<?php
// ── Navbar partagée frontoffice ──
include __DIR__ . '/navbar.php';
?>

<div class="profile-container">
    <div class="profile-header">

        <?php
        // ── Avatars cartoon/illustrés (aucune photo réelle) ──────────────────
        // "avataaars"  = style cartoon Bitmoji — personnages avec visage/cheveux/vêtements
        // "micah"      = portraits flat illustrés colorés
        // "big-smile"  = personnages cartoon souriants
        // "bottts"     = robots (conservés)
        // "pixel-art"  = jeux pixel (conservés)
        $avatars = [
            // ── Personnages cartoon ───────────────────────────────
            'av1'  => 'https://api.dicebear.com/8.x/avataaars/svg?seed=Amira&backgroundColor=1565c0&clothesColor=262e33&eyes=default&eyebrows=default&mouth=smile',
            'av2'  => 'https://api.dicebear.com/8.x/avataaars/svg?seed=Nour&backgroundColor=6a1b9a&clothesColor=3c4a6e&eyes=happy&mouth=smile',
            'av3'  => 'https://api.dicebear.com/8.x/avataaars/svg?seed=Karim&backgroundColor=00695c&clothesColor=1b5e20&eyes=default&mouth=default',
            'av4'  => 'https://api.dicebear.com/8.x/avataaars/svg?seed=Omar&backgroundColor=1a237e&clothesColor=0d47a1&eyes=wink&mouth=smile',
            'av5'  => 'https://api.dicebear.com/8.x/avataaars/svg?seed=Lina&backgroundColor=b71c1c&clothesColor=4a148c&eyes=default&mouth=smile',
            'av6'  => 'https://api.dicebear.com/8.x/avataaars/svg?seed=Adam&backgroundColor=004d40&clothesColor=1b5e20&eyes=happy&mouth=default',
            'av7'  => 'https://api.dicebear.com/8.x/micah/svg?seed=Sofia&backgroundColor=880e4f&baseColor=f5d0b5',
            'av8'  => 'https://api.dicebear.com/8.x/micah/svg?seed=Emma&backgroundColor=e65100&baseColor=d4a574',
            'av9'  => 'https://api.dicebear.com/8.x/micah/svg?seed=Zara&backgroundColor=33691e&baseColor=c68642',
            // ── Robots (conservés) ────────────────────────────────
            'av10' => 'https://api.dicebear.com/8.x/bottts/svg?seed=RoboGamer&backgroundColor=001a33',
            'av11' => 'https://api.dicebear.com/8.x/bottts/svg?seed=CyberBot&backgroundColor=1a0033',
            'av12' => 'https://api.dicebear.com/8.x/bottts/svg?seed=MegaDroid&backgroundColor=0a1628',
            // ── Jeux pixel-art (conservés) ────────────────────────
            'av13' => 'https://api.dicebear.com/8.x/pixel-art/svg?seed=Gamer1&backgroundColor=1b0033',
            'av14' => 'https://api.dicebear.com/8.x/pixel-art/svg?seed=NinjaX&backgroundColor=0a1628',
            'av15' => 'https://api.dicebear.com/8.x/pixel-art/svg?seed=PixelHero&backgroundColor=002200',
        ];
        $currentAvatar = $user['avatar'] ?? '';
        $photoSrc = !empty($user['photo'])
            ? BASE_URL . 'uploads/photos/' . htmlspecialchars($user['photo'])
            : '';
        ?>

        <!-- Flipper photo ↔ avatar -->
        <div class="avatar-wrapper">
            <div class="avatar-flipper" id="avatarFlipper">
                <!-- Face avant : photo réelle -->
                <div class="avatar-face front">
                    <?php if ($photoSrc): ?>
                        <img src="<?= $photoSrc ?>" alt="Photo">
                    <?php else: ?>
                        <i class="fas fa-user"></i>
                    <?php endif; ?>
                </div>
                <!-- Face arrière : avatar choisi -->
                <div class="avatar-face back" id="avatarBackFace">
                    <?php if ($currentAvatar === 'custom' && !empty($user['avatar_options'])): ?>
                        <?php
                            $opts = $user['avatar_options'];
                            $customUrl = 'https://api.dicebear.com/8.x/avataaars/svg?seed=' . urlencode($opts['seed'] ?? 'User')
                                . '&backgroundColor=' . urlencode($opts['backgroundColor'] ?? '1565c0')
                                . '&skinColor=' . urlencode($opts['skinColor'] ?? 'ffdbb4')
                                . '&hairColor=' . urlencode($opts['hairColor'] ?? '2c1b18')
                                . '&top=' . urlencode($opts['top'] ?? 'shortHairShortFlat')
                                . '&clothesType=' . urlencode($opts['clothesType'] ?? 'hoodie')
                                . '&clothesColor=' . urlencode($opts['clothesColor'] ?? '3c4a6e')
                                . '&eyes=default&mouth=smile';
                        ?>
                        <img src="<?= $customUrl ?>" alt="Avatar personnalisé">
                    <?php elseif ($currentAvatar && isset($avatars[$currentAvatar])): ?>
                        <img src="<?= $avatars[$currentAvatar] ?>" alt="Avatar">
                    <?php else: ?>
                        <i class="fas fa-robot"></i>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Indicateurs -->
        <div class="avatar-indicators">
            <div class="avatar-dot active" id="dot0" onclick="showFace(0)" title="Photo"></div>
            <div class="avatar-dot" id="dot1" onclick="showFace(1)" title="Avatar"></div>
        </div>

        <!-- Boutons -->
        <div class="avatar-btns">
            <form method="POST" action="<?= BASE_URL ?>controllers/UserController.php?action=uploadPhoto" enctype="multipart/form-data" style="display:inline;">
                <label class="upload-photo-label">
                    <i class="fas fa-camera"></i> Changer la photo
                    <input type="file" name="photo" accept="image/jpeg,image/png,image/gif,image/webp" onchange="this.form.submit()">
                </label>
            </form>
            <span class="btn-choose-avatar" onclick="openAvatarModal()">
                <i class="fas fa-robot"></i> Choisir un avatar
            </span>
        </div>

        <h2><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></h2>
        <p><?= htmlspecialchars($user['email']) ?></p>
        <span class="role-badge">
            <?php if ($user['role'] === 'conducteur'): ?>
                <i class="fas fa-car"></i> Conducteur
            <?php else: ?>
                <i class="fas fa-user"></i> Passager
            <?php endif; ?>
        </span>
    </div>

    <!-- Modal sélecteur d'avatar -->
    <div class="avatar-modal-overlay" id="avatarModalOverlay" onclick="closeAvatarModalOutside(event)">
        <div class="avatar-modal" style="max-height:90vh;overflow-y:auto;">
            <h3><i class="fas fa-robot"></i> Mon Avatar</h3>

            <!-- Choisir un avatar prédéfini -->
            <div id="tab-choose">
                <div class="avatar-category-label"><i class="fas fa-user-circle"></i> Personnages</div>
                <div class="avatar-grid" id="avatarGrid">
                    <?php foreach (['av1','av2','av3','av4','av5','av6','av7','av8','av9'] as $key): ?>
                        <div class="avatar-opt <?= ($currentAvatar === $key) ? 'selected' : '' ?>"
                             data-key="<?= $key ?>" data-url="<?= $avatars[$key] ?>"
                             onclick="selectAvatar(this)">
                            <img src="<?= $avatars[$key] ?>" alt="avatar" loading="lazy">
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="avatar-category-label"><i class="fas fa-robot"></i> Robots</div>
                <div class="avatar-grid robots">
                    <?php foreach (['av10','av11','av12'] as $key): ?>
                        <div class="avatar-opt <?= ($currentAvatar === $key) ? 'selected' : '' ?>"
                             data-key="<?= $key ?>" data-url="<?= $avatars[$key] ?>"
                             onclick="selectAvatar(this)">
                            <img src="<?= $avatars[$key] ?>" alt="avatar" loading="lazy">
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="avatar-category-label"><i class="fas fa-gamepad"></i> Jeux pixel</div>
                <div class="avatar-grid games">
                    <?php foreach (['av13','av14','av15'] as $key): ?>
                        <div class="avatar-opt <?= ($currentAvatar === $key) ? 'selected' : '' ?>"
                             data-key="<?= $key ?>" data-url="<?= $avatars[$key] ?>"
                             onclick="selectAvatar(this)">
                            <img src="<?= $avatars[$key] ?>" alt="avatar" loading="lazy">
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="avatar-modal-btns">
                    <button class="btn-cancel-av" onclick="closeAvatarModal()">Annuler</button>
                    <button class="btn-save-av" onclick="saveAvatar()"><i class="fas fa-check"></i> Appliquer</button>
                </div>
            </div>


        </div>
    </div>

    <?php if ($profileSuccess): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($profileSuccess) ?></div>
    <?php endif; ?>

    <div class="profile-grid">
        <!-- MODIFIER PROFIL -->
        <div class="card">
            <h3><i class="fas fa-user-edit"></i> Modifier mes informations</h3>
            <?php if (!empty($profileErrors['global'])): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($profileErrors['global']) ?></div>
            <?php endif; ?>
            <form method="POST" action="<?= BASE_URL ?>controllers/UserController.php?action=updateProfile">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Prénom *</label>
                    <input type="text" name="prenom" value="<?= htmlspecialchars($profileOld['prenom'] ?? $user['prenom']) ?>">
                    <?php if (isset($profileErrors['prenom'])): ?>
                        <span class="error-msg"><?= htmlspecialchars($profileErrors['prenom']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Nom *</label>
                    <input type="text" name="nom" value="<?= htmlspecialchars($profileOld['nom'] ?? $user['nom']) ?>">
                    <?php if (isset($profileErrors['nom'])): ?>
                        <span class="error-msg"><?= htmlspecialchars($profileErrors['nom']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Email *</label>
                    <input type="text" name="email" value="<?= htmlspecialchars($profileOld['email'] ?? $user['email']) ?>">
                    <?php if (isset($profileErrors['email'])): ?>
                        <span class="error-msg"><?= htmlspecialchars($profileErrors['email']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-phone"></i> Téléphone *</label>
                    <input type="text" name="telephone" value="<?= htmlspecialchars($profileOld['telephone'] ?? $user['telephone']) ?>">
                    <?php if (isset($profileErrors['telephone'])): ?>
                        <span class="error-msg"><?= htmlspecialchars($profileErrors['telephone']) ?></span>
                    <?php endif; ?>
                </div>
                <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Sauvegarder</button>
            </form>
        </div>

        <!-- CHANGER MOT DE PASSE -->
        <div class="card">
            <h3><i class="fas fa-key"></i> Changer le mot de passe</h3>
            <p class="info-text"><i class="fas fa-info-circle"></i> Le mot de passe doit contenir au moins 8 caractères, une majuscule et un chiffre.</p>
            <?php if (!empty($pwdErrors['global'])): ?>
                <div class="alert alert-error"><?= htmlspecialchars($pwdErrors['global']) ?></div>
            <?php endif; ?>
            <form method="POST" action="<?= BASE_URL ?>controllers/UserController.php?action=changePassword">
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Mot de passe actuel *</label>
                    <input type="password" name="current_password">
                    <?php if (isset($pwdErrors['current_password'])): ?>
                        <span class="error-msg"><?= htmlspecialchars($pwdErrors['current_password']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Nouveau mot de passe *</label>
                    <input type="password" name="new_password">
                    <?php if (isset($pwdErrors['new_password'])): ?>
                        <span class="error-msg"><?= htmlspecialchars($pwdErrors['new_password']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Confirmer le nouveau *</label>
                    <input type="password" name="confirm_password">
                    <?php if (isset($pwdErrors['confirm_password'])): ?>
                        <span class="error-msg"><?= htmlspecialchars($pwdErrors['confirm_password']) ?></span>
                    <?php endif; ?>
                </div>
                <button type="submit" class="btn-submit"><i class="fas fa-key"></i> Changer le mot de passe</button>
            </form>
        </div>
    </div>
    <a href="<?= BASE_URL ?>controllers/UserController.php?action=dashboard" class="btn-back-dashboard">
        <i class="fas fa-arrow-left"></i> Retour au dashboard
    </a>
</div>

<footer>
    <p><svg width="16" height="16" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align:middle"><path d="M22 4C22 4 8 10 8 24C8 31.732 14.268 38 22 38C29.732 38 36 31.732 36 24C36 14 28 8 22 4Z" fill="#61B3FA" opacity="0.9"/></svg> Eco Ride by Echo Group © 2025</p>
</footer>
<script src="<?= BASE_URL ?>views/frontoffice/js/profile.validation.js"></script>
<script>
// ── Dropdown profil ───────────────────────────────────────
function toggleDropdown(e) {
    e.stopPropagation();
    document.getElementById('profileDropMenu').classList.toggle('open');
}
document.addEventListener('click', function() {
    var menu = document.getElementById('profileDropMenu');
    if (menu) menu.classList.remove('open');
});

// ── Toggle mode sombre / clair ────────────────────────────
// Removed: handled by navbar.js
// Appliquer le thème sauvegardé au chargement
if (localStorage.getItem('ecoride_theme') === 'light') {
    document.body.classList.add('light-mode');
    // Update navbar icon if exists
    var icon = document.getElementById('ecoThemeIcon');
    if (icon) icon.className = 'fas fa-sun';
}
</script>
<script>
// ── Flip auto photo → avatar ──────────────────────────────
var currentFace = 0;
var flipTimer = null;
var hasAvatar = <?= (($currentAvatar && isset($avatars[$currentAvatar])) || ($currentAvatar === 'custom' && !empty($user['avatar_options']))) ? 'true' : 'false' ?>;
var hasPhoto  = <?= ($photoSrc) ? 'true' : 'false' ?>;

function showFace(idx) {
    clearTimeout(flipTimer);
    currentFace = idx;
    var flipper = document.getElementById('avatarFlipper');
    flipper.classList.toggle('flipped', idx === 1);
    document.getElementById('dot0').classList.toggle('active', idx === 0);
    document.getElementById('dot1').classList.toggle('active', idx === 1);
    // Relancer le timer auto
    if (hasAvatar && hasPhoto) scheduleFlip();
}

function scheduleFlip() {
    clearTimeout(flipTimer);
    flipTimer = setTimeout(function() {
        showFaceAuto();
    }, 3000);
}

function showFaceAuto() {
    currentFace = currentFace === 0 ? 1 : 0;
    var flipper = document.getElementById('avatarFlipper');
    flipper.classList.toggle('flipped', currentFace === 1);
    document.getElementById('dot0').classList.toggle('active', currentFace === 0);
    document.getElementById('dot1').classList.toggle('active', currentFace === 1);
    scheduleFlip();
}

// Lancer uniquement si les deux existent
if (hasAvatar && hasPhoto) scheduleFlip();

// ── Modal sélecteur d'avatar ──────────────────────────────
var selectedAvatarKey = '<?= $currentAvatar ?>';
var selectedAvatarUrl = '<?= ($currentAvatar && isset($avatars[$currentAvatar])) ? $avatars[$currentAvatar] : "" ?>';

// Options personnalisateur

function openAvatarModal() {
    document.getElementById('avatarModalOverlay').classList.add('open');
}
function closeAvatarModal() {
    document.getElementById('avatarModalOverlay').classList.remove('open');
}
function closeAvatarModalOutside(e) {
    if (e.target === document.getElementById('avatarModalOverlay')) closeAvatarModal();
}
// Mapping des avatars prédéfinis → paramètres personnalisateur

function selectAvatar(el) {
    document.querySelectorAll('.avatar-opt').forEach(function(o){ o.classList.remove('selected'); });
    el.classList.add('selected');
    selectedAvatarKey = el.dataset.key;
    selectedAvatarUrl = el.dataset.url;

    // Si c'est un avatar "avataaars" (av1-av9), pré-remplir le personnalisateur
    var preset = avatarPresetParams[selectedAvatarKey];
    if (preset) {
        customOpts.seed          = preset.seed;
        customOpts.skinColor     = preset.skinColor;
        customOpts.hairColor     = preset.hairColor;
        customOpts.top           = preset.top;
        customOpts.clothesType   = preset.clothesType;
        customOpts.clothesColor  = preset.clothesColor;
        customOpts.backgroundColor = preset.backgroundColor;

        // Mettre à jour les swatches visuellement
        function syncSwatch(groupId, val) {
            document.querySelectorAll('#' + groupId + ' .swatch').forEach(function(s){
                s.classList.toggle('active', s.dataset.val === val);
            });
        }
        syncSwatch('swatchSkin',    preset.skinColor);
        syncSwatch('swatchHair',    preset.hairColor);
        syncSwatch('swatchClothes', preset.clothesColor);
        syncSwatch('swatchBg',      preset.backgroundColor);

        // Mettre à jour les selects
        var topEl = document.getElementById('selHairTop');
        var clothEl = document.getElementById('selClothes');
        if (topEl)   topEl.value   = preset.top;
        if (clothEl) clothEl.value = preset.clothesType;
    }
}
function applyAvatarToProfile(url) {
    var backFace = document.getElementById('avatarBackFace');
    backFace.innerHTML = '<img src="' + url + '" alt="Avatar">';
    var flipper = document.getElementById('avatarFlipper');
    flipper.classList.add('flipped');
    document.getElementById('dot0').classList.remove('active');
    document.getElementById('dot1').classList.add('active');
    currentFace = 1;
    hasAvatar = true;
    closeAvatarModal();
    if (hasPhoto) scheduleFlip();
}
function saveAvatar() {
    if (!selectedAvatarKey) return;
    applyAvatarToProfile(selectedAvatarUrl);
    fetch('<?= BASE_URL ?>controllers/UserController.php?action=saveAvatar', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'avatar=' + encodeURIComponent(selectedAvatarKey)
    }).then(function(r){ return r.json(); });
}

</script>
<?php require_once __DIR__ . '/chatbot_widget.php'; ?>
</body>
</html>