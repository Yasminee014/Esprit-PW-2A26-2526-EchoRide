<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eco Ride - Administration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family:'Poppins','Segoe UI',sans-serif;
            background:#0A1628;
            min-height:100vh; color:#fff;
            display:flex; flex-direction:column; align-items:center;
        }
        .navbar { width: 100%; }

        /* ── Navbar ── */
        .navbar {
            background: linear-gradient(135deg, #1976D2 0%, #0F3B6E 100%);
            padding: 0.9rem 4%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(97,179,250,.15);
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 12px rgba(0,0,0,0.2);
        }
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        .nav-links {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            list-style: none;
            margin: 0; padding: 0;
        }
        .nav-links li a {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 0.45rem 1.1rem;
            border-radius: 30px;
            text-decoration: none;
            font-family: 'Poppins', sans-serif;
            font-size: 0.88rem;
            font-weight: 500;
            color: #fff;
            background: transparent;
            border: none;
            transition: background 0.2s;
            cursor: pointer;
        }
        .nav-links li a:hover { background: rgba(255,255,255,.12); }
        .theme-btn {
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.2);
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.25s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .theme-btn:hover { background: rgba(255,255,255,0.28); transform: rotate(15deg); }

        /* ── Login wrapper ── */
        .login-wrapper { width:100%; max-width:550px; padding:2rem; margin-top:3rem; }
        .logo-inner { text-align:center; margin-bottom:2rem; }
        .logo-inner i { font-size:50px; color:#1976D2; display:block; margin-bottom:1rem; }
        .logo-inner h1 { font-size:2rem; background:linear-gradient(135deg,#1976D2,#61B3FA); -webkit-background-clip:text; background-clip:text; color:transparent; }
        .logo-inner p { color:#A7A9AC; font-size:0.9rem; margin-top:0.3rem; }
        .admin-badge { display:inline-block; background:rgba(255,165,0,0.2); border:1px solid rgba(255,165,0,0.5); color:#ffa500; padding:0.3rem 0.8rem; border-radius:20px; font-size:0.8rem; margin-top:0.5rem; }
        .card { background:rgba(13,31,58,0.95); border-radius:24px; padding:3.5rem 2.5rem; border:1px solid rgba(25,118,210,0.3); box-shadow:0 20px 60px rgba(0,0,0,0.4); min-height: 420px; display: flex; flex-direction: column; justify-content: center; }
        .card h2 { color:#1976D2; margin-bottom:2rem; display:flex; align-items:center; gap:10px; font-size: 1.6rem; }
        .form-group { margin-bottom:1.8rem; }
        .form-group label { display:block; margin-bottom:0.7rem; color:#61B3FA; font-size:0.95rem; }
        .form-group label i { margin-right:8px; color:#1976D2; }
        .form-group input { width:100%; padding:1.1rem 1.2rem; border-radius:12px; border:1px solid rgba(25,118,210,0.3); background:rgba(10,22,40,0.8); color:white; font-size:1rem; transition:all 0.3s; }
        .form-group input:focus { outline:none; border-color:#1976D2; box-shadow:0 0 10px rgba(25,118,210,0.2); }
        .form-group input.error-field { border-color:#ff4444; }
        .error-msg { color:#ff6b6b; font-size:0.8rem; margin-top:6px; display:block; }
        .alert { padding:1rem 1.2rem; border-radius:12px; margin-bottom:1.8rem; display:flex; align-items:center; gap:10px; font-size:0.95rem; }
        .alert-error { background:rgba(255,68,68,0.15); border:1px solid rgba(255,68,68,0.4); color:#ff6b6b; }
        .btn-submit { background:linear-gradient(135deg,#1976D2,#1976D2); color:white; padding:1.1rem 1.5rem; border:none; border-radius:30px; cursor:pointer; font-weight:600; width:100%; font-size:1.1rem; transition:all 0.3s; margin-top:1rem; }
        .btn-submit:hover { transform:translateY(-2px); box-shadow:0 5px 20px rgba(25,118,210,0.3); }
        .back-link { text-align:center; margin-top:1.5rem; }
        .back-link a { color:#A7A9AC; text-decoration:none; font-size:0.85rem; transition:color 0.3s; }
        .back-link a:hover { color:#1976D2; }

        /* ── Face verification overlay ─────────────────────── */
        #faceModal {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.85); z-index: 9999;
            align-items: center; justify-content: center; flex-direction: column;
        }
        #faceModal.active { display: flex; }
        .face-card {
            background: rgba(13,31,58,0.97);
            border: 1px solid rgba(255,165,0,0.4);
            border-radius: 20px; padding: 2rem;
            max-width: 460px; width: 95%; text-align: center; color: #e0e0e0;
        }
        .face-card h3 { color: #ffa500; margin-bottom: 0.4rem; font-size: 1.2rem; }
        .face-card p  { font-size: 0.85rem; color: #aaa; margin-bottom: 1rem; }
        #cameraWrap {
            position: relative; display: inline-block;
            border-radius: 12px; overflow: hidden;
            border: 2px solid rgba(255,165,0,0.5); margin-bottom: 1rem;
        }
        #videoEl       { display: block; width: 320px; height: 240px; object-fit: cover; }
        #overlayCanvas { position: absolute; top: 0; left: 0; width: 320px; height: 240px; pointer-events: none; }
        #faceStatus {
            min-height: 38px; font-size: 0.9rem; margin-bottom: 1rem;
            display: flex; align-items: center; justify-content: center; gap: 0.4rem;
        }
        #faceStatus.ok   { color: #00e676; }
        #faceStatus.err  { color: #ff5252; }
        #faceStatus.info { color: #ffa500; }
        #scanProgress { width:100%; height:4px; background:rgba(255,255,255,0.1); border-radius:4px; overflow:hidden; margin-bottom:1rem; }
        #scanBar { height:100%; width:0%; background:linear-gradient(90deg,#ffa500,#ffcc44); border-radius:4px; transition:width 0.3s ease; }
        .face-actions { display:flex; gap:0.8rem; justify-content:center; flex-wrap:wrap; }
        #btnVerify {
            background: linear-gradient(135deg,#ffa500,#ffcc44); color: #0A1628;
            border: none; padding: 0.7rem 1.6rem; border-radius: 10px;
            font-weight: 700; cursor: pointer; font-size: 0.9rem; transition: transform 0.2s;
        }
        #btnVerify:hover:not(:disabled) { transform: translateY(-2px); }
        #btnVerify:disabled { opacity: 0.5; cursor: not-allowed; }
        #btnCancel {
            background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.2);
            color: #fff; padding: 0.7rem 1.4rem; border-radius: 10px; cursor: pointer; font-size: 0.9rem;
        }
        #btnCancel:hover { background: rgba(255,255,255,0.15); }
        .spinner-icon { animation: spin 1s linear infinite; display: inline-block; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>

    <!-- face-api.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
</head>
<body>

<nav class="navbar">
    <a class="logo" href="<?= BASE_URL ?>controllers/UserController.php?action=index">
        <img src="<?= BASE_URL ?>uploads/photos/photo.png" onerror="this.onerror=null;this.src=\'<?= BASE_URL ?>serve_image.php?type=logo\'"
             alt="EcoRide Logo"
             style="width:54px;height:54px;object-fit:contain;background:transparent;vertical-align:middle;flex-shrink:0;">
        <span style="font-family:'Poppins',sans-serif;font-size:1.3rem;font-weight:400;color:#fff;letter-spacing:0.01em;vertical-align:middle;">Eco<strong>Ride</strong></span>
    </a>
    <ul class="nav-links">
        <li><a href="<?= BASE_URL ?>controllers/UserController.php?action=index#hero">Accueil</a></li>
        <li><a href="<?= BASE_URL ?>controllers/UserController.php?action=index#evenements">Événements</a></li>
        <li><a href="<?= BASE_URL ?>controllers/UserController.php?action=index#sponsors">Sponsors</a></li>
        <li><a href="<?= BASE_URL ?>controllers/UserController.php?action=showRegister">S'inscrire</a></li>
        <li><a href="<?= BASE_URL ?>controllers/UserController.php?action=showLoginForm">Se connecter</a></li>
        <!-- Bouton toggle thème -->
        <li>
            <button class="theme-btn" onclick="toggleThemeAdmin()" id="adminThemeBtn" title="Mode sombre / clair">
                <i class="fas fa-moon" id="adminThemeIcon"></i>
            </button>
        </li>
    </ul>
</nav>
<?php
$errors  = $_SESSION['admin_login_errors']    ?? [];
$oldInput= $_SESSION['admin_login_old_input'] ?? [];
unset($_SESSION['admin_login_errors'], $_SESSION['admin_login_old_input']);
?>

<div class="login-wrapper">
    <div class="logo-inner">
        <h1>Eco<span style="color:white">Ride</span></h1>
        <p>Panneau d'Administration</p>
        <span class="admin-badge"><i class="fas fa-shield-alt"></i> Accès Restreint</span>
    </div>

    <div class="card">
        <h2><i class="fas fa-lock"></i> Connexion Admin</h2>

        <?php if (!empty($errors['global'])): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($errors['global']) ?></div>
        <?php endif; ?>

        <form id="adminLoginForm" method="POST" action="<?= BASE_URL ?>controllers/AdminController.php?action=login" novalidate>

            <div class="form-group">
                <label><i class="fas fa-envelope"></i> Email administrateur *</label>
                <input type="text" name="email"
                       value="<?= htmlspecialchars($oldInput['email'] ?? '') ?>"
                       placeholder="admin@ecoride.fr"
                       class="<?= isset($errors['email']) ? 'error-field' : '' ?>">
                <?php if (isset($errors['email'])): ?>
                    <span class="error-msg"><i class="fas fa-times-circle"></i> <?= htmlspecialchars($errors['email']) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label><i class="fas fa-key"></i> Mot de passe *</label>
                <input type="password" name="password"
                       placeholder="••••••••"
                       class="<?= isset($errors['password']) ? 'error-field' : '' ?>">
                <?php if (isset($errors['password'])): ?>
                    <span class="error-msg"><i class="fas fa-times-circle"></i> <?= htmlspecialchars($errors['password']) ?></span>
                <?php endif; ?>
            </div>

            <div style="display:flex; flex-direction:column; gap:0.5rem; margin-top: 0.5rem;">
                <button type="submit" class="btn-submit" style="margin-top:0;">
                    <i class="fas fa-sign-in-alt"></i> Connexion
                </button>
                <button type="button" id="btnTriggerFaceId" class="btn-submit" style="margin-top:0; background:#6c757d; color:white;">
                    <i class="fas fa-camera"></i> Connexion avec Face ID
                </button>
            </div>
        </form>
    </div>

    <div class="back-link">
        <a href="<?= BASE_URL ?>controllers/UserController.php?action=index">
            <i class="fas fa-arrow-left"></i> Retour au site
        </a>
    </div>
</div>
<script src="<?= BASE_URL ?>views/backoffice/js/admin_login.validation.js"></script>

<!-- ══════════════ FACE VERIFICATION MODAL (Admin) ══════════════ -->
<div id="faceModal">
    <div class="face-card">
        <h3><i class="fas fa-shield-alt"></i> Vérification Administrateur</h3>
        <p>Regardez la caméra pour confirmer votre identité</p>
        <div id="cameraWrap">
            <video id="videoEl" autoplay muted playsinline></video>
            <canvas id="overlayCanvas"></canvas>
        </div>
        <div id="scanProgress"><div id="scanBar"></div></div>
        <div id="faceStatus" class="info">
            <i class="fas fa-spinner spinner-icon"></i> Chargement des modèles IA...
        </div>
        <div class="face-actions">
            <button id="btnVerify" disabled><i class="fas fa-check-circle"></i> Vérifier mon identité</button>
            <button id="btnCancel"><i class="fas fa-times"></i> Annuler</button>
        </div>
    </div>
</div>

<script>
// ════════════════════════════════════════════════════════
//  FACE VERIFICATION LOGIC — EcoRide Admin
// ════════════════════════════════════════════════════════
const MODELS_URL  = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/';
const THRESHOLD   = 0.55;

let stream = null, modelsLoaded = false, pendingForm = null, profileDescriptor = null;

const faceModal     = document.getElementById('faceModal');
const videoEl       = document.getElementById('videoEl');
const overlayCanvas = document.getElementById('overlayCanvas');
const faceStatus    = document.getElementById('faceStatus');
const scanBar       = document.getElementById('scanBar');
const btnVerify     = document.getElementById('btnVerify');
const btnCancel     = document.getElementById('btnCancel');
const adminForm     = document.getElementById('adminLoginForm');

function setStatus(type, html) { faceStatus.className = type; faceStatus.innerHTML = html; }

async function loadModels() {
    try {
        await Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri(MODELS_URL),
            faceapi.nets.faceLandmark68TinyNet.loadFromUri(MODELS_URL),
            faceapi.nets.faceRecognitionNet.loadFromUri(MODELS_URL),
        ]);
        modelsLoaded = true;
        setStatus('info', '<i class="fas fa-video"></i> Modèles chargés. Activation de la caméra...');
    } catch(e) {
        setStatus('err', '<i class="fas fa-exclamation-triangle"></i> Erreur chargement modèles IA');
    }
}

async function startCamera() {
    try {
        stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false });
        videoEl.srcObject = stream;
        await new Promise(r => videoEl.onloadedmetadata = r);
        setStatus('info', '<i class="fas fa-smile"></i> Positionnez votre visage dans le cadre');
        btnVerify.disabled = false;
        startLiveDetection();
    } catch(e) {
        setStatus('err', '<i class="fas fa-ban"></i> Accès caméra refusé. Veuillez autoriser l\'accès.');
    }
}

let detectionLoop = null;
function startLiveDetection() {
    const opts = new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.4 });
    detectionLoop = setInterval(async () => {
        const det = await faceapi.detectSingleFace(videoEl, opts);
        const ctx = overlayCanvas.getContext('2d');
        ctx.clearRect(0, 0, overlayCanvas.width, overlayCanvas.height);
        if (det) {
            const { x, y, width, height } = det.box;
            ctx.strokeStyle = '#ffa500'; ctx.lineWidth = 2;
            ctx.strokeRect(x, y, width, height);
        }
    }, 300);
}

async function loadProfileDescriptor(email) {
    const res  = await fetch('<?= BASE_URL ?>controllers/AdminController.php?action=getFaceImage', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'email=' + encodeURIComponent(email)
    });
    const data = await res.json();
    if (!data.success || !data.image) return null;
    const img  = await faceapi.fetchImage('data:image/jpeg;base64,' + data.image);
    const opts = new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.4 });
    const det  = await faceapi.detectSingleFace(img, opts).withFaceLandmarks(true).withFaceDescriptor();
    return det ? det.descriptor : null;
}

async function verifyFace() {
    btnVerify.disabled = true;
    setStatus('info', '<i class="fas fa-spinner spinner-icon"></i> Analyse en cours...');
    scanBar.style.width = '30%';
    const opts = new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.4 });
    const det  = await faceapi.detectSingleFace(videoEl, opts).withFaceLandmarks(true).withFaceDescriptor();
    scanBar.style.width = '70%';
    if (!det) {
        setStatus('err', '<i class="fas fa-frown"></i> Aucun visage détecté. Rapprochez-vous.');
        btnVerify.disabled = false; scanBar.style.width = '0%'; return;
    }
    if (!profileDescriptor) {
        setStatus('ok', '<i class="fas fa-check-circle"></i> Pas de photo enregistrée. Connexion...');
        scanBar.style.width = '100%'; setTimeout(submitPendingForm, 800); return;
    }
    const distance = faceapi.euclideanDistance(det.descriptor, profileDescriptor);
    scanBar.style.width = '100%';
    if (distance <= THRESHOLD) {
        setStatus('ok', '<i class="fas fa-check-circle"></i> Identité confirmée ! Connexion...');
        setTimeout(submitPendingForm, 900);
    } else {
        setStatus('err', '<i class="fas fa-user-slash"></i> Ce n\'est pas vous ! Accès refusé.');
        btnVerify.disabled = false; scanBar.style.width = '0%';
        setTimeout(() => { setStatus('info', '<i class="fas fa-redo"></i> Réessayez ou annulez.'); btnVerify.disabled = false; }, 3000);
    }
}

function submitPendingForm() {
    stopCamera();
    if (!pendingForm) return;
    const realForm = document.createElement('form');
    realForm.method = 'POST';
    realForm.action = '<?= BASE_URL ?>controllers/AdminController.php?action=login';
    for (const [k, v] of pendingForm.entries()) {
        const inp = document.createElement('input');
        inp.type = 'hidden'; inp.name = k; inp.value = v;
        realForm.appendChild(inp);
    }
    document.body.appendChild(realForm);
    realForm.submit();
}

function stopCamera() {
    clearInterval(detectionLoop);
    if (stream) { stream.getTracks().forEach(t => t.stop()); stream = null; }
    faceModal.classList.remove('active');
}

async function openFaceModal(formData, email) {
    pendingForm = formData; profileDescriptor = null;
    scanBar.style.width = '0%'; btnVerify.disabled = true;
    faceModal.classList.add('active');
    setStatus('info', '<i class="fas fa-spinner spinner-icon"></i> Chargement des modèles IA...');
    if (!modelsLoaded) await loadModels();
    if (!modelsLoaded) return;
    setStatus('info', '<i class="fas fa-spinner spinner-icon"></i> Récupération de votre photo...');
    try { profileDescriptor = await loadProfileDescriptor(email); } catch(e) { profileDescriptor = null; }
    await startCamera();
}

btnCancel.addEventListener('click', () => { stopCamera(); pendingForm = null; });
btnVerify.addEventListener('click', verifyFace);

const btnTriggerFaceId = document.getElementById('btnTriggerFaceId');
if (btnTriggerFaceId) {
    btnTriggerFaceId.addEventListener('click', async function() {
        const emailInput    = adminForm.querySelector('input[name="email"]');
        const passwordInput = adminForm.querySelector('input[name="password"]');
        const email    = emailInput ? emailInput.value.trim() : '';
        const password = passwordInput ? passwordInput.value.trim() : '';
        
        if (!email || !password) { 
            adminForm.reportValidity();
            if (adminForm.checkValidity()) adminForm.submit();
            return; 
        }
        
        const fd = new FormData(adminForm);
        openFaceModal(fd, email);
    });
}
</script>
<script>
function toggleThemeAdmin() {
    document.body.classList.toggle('light-mode');
    var isLight = document.body.classList.contains('light-mode');
    var icon = document.getElementById('adminThemeIcon');
    if (icon) icon.className = isLight ? 'fas fa-sun' : 'fas fa-moon';
    localStorage.setItem('ecoride_theme', isLight ? 'light' : 'dark');
}
(function() {
    if (localStorage.getItem('ecoride_theme') === 'light') {
        document.body.classList.add('light-mode');
        var icon = document.getElementById('adminThemeIcon');
        if (icon) icon.className = 'fas fa-sun';
    }
})();
</script>
</body>
</html>