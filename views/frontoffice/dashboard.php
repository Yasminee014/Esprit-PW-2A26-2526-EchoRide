<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eco Ride - Mon Espace</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Poppins','Segoe UI',sans-serif; background:linear-gradient(135deg,#0A1628 0%,#0D1F3A 100%); min-height:100vh; color:#fff; }
        .navbar { background:linear-gradient(90deg,#1565C0 0%,#0F3B6E 100%); padding:0 2rem; height:56px; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid rgba(97,179,250,.15); position:sticky; top:0; z-index:100; }
        .logo { display:flex; align-items:center; gap:10px; text-decoration:none; }
        .logo i { font-size:1.2rem; color:#61B3FA; }
        .logo h1 { font-size:1.3rem; font-weight:400; color:#fff; background:none; -webkit-background-clip:unset; background-clip:unset; }
        .nav-right { display:flex; align-items:center; gap:1rem; }
        .user-badge { background:rgba(255,255,255,.1); padding:0.38rem 1rem; border-radius:20px; border:1px solid rgba(255,255,255,.18); font-size:0.88rem; color:#fff; }
        .btn-nav { display:inline-flex; align-items:center; gap:6px; padding:0.38rem 1rem; border-radius:20px; text-decoration:none; font-size:0.88rem; font-weight:500; color:#fff; background:rgba(255,255,255,.1); border:1px solid rgba(255,255,255,.18); transition:background 0.2s; } .btn-logout { background:rgba(220,53,69,.25) !important; border-color:rgba(220,53,69,.4) !important; color:#ff8080 !important; }
        
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
        .stat-card h3 { font-size:2rem; margin-bottom:0.3rem; }
        .stat-card p { color:#A7A9AC; font-size:0.9rem; }
        .actions-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(250px,1fr)); gap:1.5rem; }
        .action-card { background:rgba(13,31,58,0.9); border-radius:20px; padding:2rem; border:1px solid rgba(25,118,210,0.2); transition:all 0.3s; text-decoration:none; color:white; display:block; }
        .action-card:hover { border-color:#1976D2; transform:translateY(-3px); box-shadow:0 8px 25px rgba(25,118,210,0.15); }
        .action-card i { font-size:2rem; color:#1976D2; margin-bottom:1rem; display:block; }
        .action-card h3 { margin-bottom:0.5rem; }
        .action-card p { color:#61B3FA; font-size:0.85rem; }
        footer { text-align:center; padding:2rem; border-top:1px solid rgba(25,118,210,0.2); color:#A7A9AC; margin-top:4rem; }
    </style>
</head>
<body>
<!-- Dans le navbar du dashboard -->
<nav class="navbar">
    <a class="logo" href="#"><i class="fas fa-leaf" style="font-size:1.5rem;color:#61B3FA;"></i> <span style="font-family:'Poppins',sans-serif;font-size:1.3rem;font-weight:400;color:#fff;letter-spacing:0.01em;">Eco<strong>Ride</strong></span></a>
    <div class="nav-right">
        <a href="<?= BASE_URL ?>controllers/UserController.php?action=profile" class="btn-nav btn-profile">
            <i class="fas fa-user-circle"></i> Mon Profil
        </a>
        <a href="<?= BASE_URL ?>controllers/UserController.php?action=logout" class="btn-nav btn-logout">
            <i class="fas fa-sign-out-alt"></i> Déconnexion
        </a>
    </div>
</nav>

<div class="dashboard-container">

    <!-- Welcome card alignée à gauche avec photo -->
    <div class="welcome-card">
        <!-- Avatar / Photo de profil -->
        <div class="welcome-avatar">
            <?php if (!empty($_SESSION['user_photo'])): ?>
                <img src="<?= BASE_URL ?>uploads/photos/<?= htmlspecialchars($_SESSION['user_photo']) ?>" alt="Photo profil">
            <?php else: ?>
                <i class="fas fa-user"></i>
            <?php endif; ?>
        </div>

        <!-- Texte de bienvenue -->
        <div class="welcome-text">
            <h2>Bonjour, <span class="highlight"><?= htmlspecialchars($_SESSION['user_prenom']) ?> !</span> 👋</h2>
            <p>Bienvenue sur votre espace Eco Ride</p>
            <div class="role-badge">
                <?php if ($_SESSION['user_role'] === 'conducteur'): ?>
                    <i class="fas fa-car"></i> Conducteur
                <?php else: ?>
                    <i class="fas fa-user"></i> Passager
                <?php endif; ?>
            </div>
        </div>

        <!-- Boutons rapides -->
        <div class="welcome-actions">
            <a href="<?= BASE_URL ?>controllers/UserController.php?action=profile" class="btn-profile-quick">
                <i class="fas fa-user-edit"></i> Mon Profil
            </a>
            <a href="<?= BASE_URL ?>controllers/UserController.php?action=logout" class="btn-profile-quick" style="color:#ff6b6b;border-color:rgba(255,68,68,0.35);background:rgba(255,68,68,0.1);">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </div>
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

    <div class="actions-grid">
        <a href="#" class="action-card">
            <i class="fas fa-search"></i>
            <h3>Rechercher un trajet</h3>
            <p>Trouvez un covoiturage près de chez vous</p>
        </a>
        <?php if ($_SESSION['user_role'] === 'conducteur'): ?>
        <a href="#" class="action-card">
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
        <a href="#" class="action-card">
            <i class="fas fa-history"></i>
            <h3>Historique</h3>
            <p>Voir vos trajets et réservations passés</p>
        </a>
    </div>
</div>

<footer>
    <p><svg width="16" height="16" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align:middle"><path d="M22 4C22 4 8 10 8 24C8 31.732 14.268 38 22 38C29.732 38 36 31.732 36 24C36 14 28 8 22 4Z" fill="#61B3FA" opacity="0.9"/></svg> Eco Ride by Echo Group © 2025 - Covoiturage Intelligent et Écologique</p>
</footer>
</body>
</html>