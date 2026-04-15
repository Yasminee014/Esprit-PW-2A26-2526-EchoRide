<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Véhicules Disponibles | EcoRide</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        :root { --bleu-fonce:#1976D2; --bleu-clair:#61B3FA; --blanc:#F4F5F7; --gris:#A7A9AC; --dark-bg:#0A1628; }
        body { font-family:'Poppins','Segoe UI',sans-serif; background:linear-gradient(135deg,var(--dark-bg) 0%,#0D1F3A 100%); color:#fff; min-height:100vh; }

        .navbar { background:linear-gradient(90deg,var(--bleu-fonce),#0F3B6E); padding:1rem 2rem; display:flex; justify-content:space-between; align-items:center; box-shadow:0 4px 20px rgba(0,0,0,.3); position:sticky; top:0; z-index:100; }
        .navbar .logo { display:flex; align-items:center; gap:10px; font-size:1.3rem; font-weight:700; color:#fff; text-decoration:none; }
        .navbar .logo i { color:var(--bleu-clair); }
        .navbar nav a { color:#fff !important; text-decoration:none !important; padding:.5rem 1.2rem; border-radius:25px; font-size:.88rem; font-weight:500; transition:all .3s; border:1px solid rgba(97,179,250,.35); background:rgba(255,255,255,.08); display:inline-flex; align-items:center; gap:8px; margin:0 2px; }
        .navbar nav a:hover { background:rgba(25,118,210,.3) !important; border-color:#61B3FA !important; color:#fff !important; }
        .navbar nav a.active { background:rgba(255,255,255,.08) !important; border-color:rgba(97,179,250,.35) !important; color:#fff !important; }
        .navbar nav a.admin-nav { background:rgba(255,255,255,.08) !important; border-color:rgba(97,179,250,.35) !important; color:#fff !important; }
        .navbar nav a.admin-nav:hover { background:rgba(25,118,210,.3) !important; border-color:#61B3FA !important; color:#61B3FA !important; }

        .container { max-width:1200px; margin:0 auto; padding:2rem; }

        .page-header { margin-bottom:2rem; }
        .page-header h1 { font-size:1.8rem; display:flex; align-items:center; gap:10px; margin-bottom:.5rem; }
        .page-header h1 i { color:var(--bleu-clair); }
        .page-header p { color:var(--gris); }

        .vehicules-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(320px,1fr)); gap:1.5rem; }

        .vehicule-card { background:rgba(255,255,255,.07); border-radius:20px; border:1px solid rgba(97,179,250,.2); overflow:hidden; transition:all .3s; }
        .vehicule-card:hover { transform:translateY(-5px); border-color:var(--bleu-clair); box-shadow:0 10px 30px rgba(25,118,210,.2); }

        .card-header { background:linear-gradient(135deg,rgba(25,118,210,.4),rgba(97,179,250,.1)); padding:1.2rem 1.5rem; }
        .card-header h3 { font-size:1.1rem; display:flex; align-items:center; gap:8px; }
        .card-header h3 i { color:var(--bleu-clair); }

        /* ── Silhouette véhicule ── */
        .car-visual {
            width:100%; height:160px; display:flex; align-items:center; justify-content:center;
            position:relative; overflow:hidden;
        }
        .car-visual .car-bg {
            position:absolute; inset:0;
            background:linear-gradient(135deg,rgba(25,118,210,.15),rgba(97,179,250,.05));
        }
        .car-visual svg { position:relative; z-index:1; width:240px; height:120px; filter:drop-shadow(0 8px 24px rgba(0,0,0,.4)); transition:transform .3s; }
        .vehicule-card:hover .car-visual svg { transform:scale(1.05) translateY(-4px); }
        .car-visual .car-badge {
            position:absolute; bottom:10px; right:14px; z-index:2;
            background:rgba(0,0,0,.45); backdrop-filter:blur(6px);
            border:1px solid rgba(255,255,255,.15); border-radius:20px;
            padding:.25rem .75rem; font-size:.75rem; font-weight:600; color:#fff;
            display:flex; align-items:center; gap:5px;
        }
        .color-dot { width:10px; height:10px; border-radius:50%; border:1px solid rgba(255,255,255,.3); display:inline-block; }
        .conducteur { font-size:.82rem; color:var(--gris); margin-top:.3rem; }

        .card-body { padding:1.5rem; }
        .card-info { display:grid; grid-template-columns:1fr 1fr; gap:.8rem; margin-bottom:1.2rem; }
        .info-item .info-label { font-size:.75rem; color:var(--gris); text-transform:uppercase; letter-spacing:.5px; display:block; }
        .info-item .info-value { font-size:.95rem; font-weight:500; display:block; margin-top:3px; }
        .info-item .info-value code { color:var(--bleu-clair); font-family:monospace; }

        .btn-reserver {
            width:100%; background:linear-gradient(135deg,var(--bleu-fonce),var(--bleu-clair));
            color:#fff; border:none; padding:.9rem; border-radius:12px;
            font-size:.95rem; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px;
            transition:all .3s; margin-top:1rem;
        }
        .btn-reserver:hover { transform:translateY(-2px); box-shadow:0 8px 20px rgba(25,118,210,.4); }

        .empty-state { text-align:center; padding:4rem 2rem; background:rgba(255,255,255,.05); border-radius:20px; border:1px dashed rgba(97,179,250,.3); }
        .empty-state i { font-size:4rem; color:rgba(97,179,250,.3); margin-bottom:1rem; display:block; }
        .empty-state p { color:var(--gris); }

        .alert { padding:1rem 1.5rem; border-radius:14px; margin-bottom:1.5rem; display:flex; align-items:center; gap:10px; }
        .alert-success { background:rgba(39,174,96,.15); border:1px solid rgba(39,174,96,.4); color:#27ae60; }
        .alert-error   { background:rgba(231,76,60,.15);  border:1px solid rgba(231,76,60,.4);  color:#e74c3c; }

        /* Modal réservation */
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.7); z-index:1000; align-items:center; justify-content:center; }
        .modal-overlay.open { display:flex; }
        .modal { background:linear-gradient(135deg,#0D1F3A,#1a2f50); border:1px solid rgba(97,179,250,.3); border-radius:24px; padding:2rem; width:480px; max-width:95vw; }
        .modal-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; padding-bottom:1rem; border-bottom:2px solid rgba(97,179,250,.3); }
        .modal-header h3 { font-size:1.2rem; color:var(--bleu-clair); display:flex; align-items:center; gap:8px; }
        .modal-close { background:none; border:none; color:var(--gris); font-size:1.4rem; cursor:pointer; }
        .modal-close:hover { color:#e74c3c; }

        .modal-info { background:rgba(255,255,255,.06); border-radius:12px; padding:1rem; margin-bottom:1.2rem; }
        .modal-info p { font-size:.9rem; color:var(--gris); display:flex; align-items:center; gap:8px; margin-bottom:.5rem; }
        .modal-info p:last-child { margin-bottom:0; }
        .modal-info i { color:var(--bleu-clair); width:16px; }
        .modal-info strong { color:#fff; }

        .form-group { display:flex; flex-direction:column; gap:.5rem; margin-bottom:1rem; }
        .form-group label { font-size:.85rem; color:var(--bleu-clair); display:flex; align-items:center; gap:6px; }
        .form-group input { background:rgba(255,255,255,.08); border:1px solid rgba(97,179,250,.3); color:#fff; padding:.7rem 1rem; border-radius:12px; font-size:.9rem; outline:none; transition:all .3s; }
        .form-group input:focus { border-color:var(--bleu-clair); }
        .field-error { color:#e74c3c; font-size:.78rem; }

        .modal-buttons { display:flex; gap:1rem; margin-top:1.5rem; justify-content:flex-end; }
        .btn-save { background:linear-gradient(135deg,var(--bleu-fonce),var(--bleu-clair)); color:#fff; border:none; padding:.8rem 2rem; border-radius:12px; font-size:.95rem; cursor:pointer; display:flex; align-items:center; gap:8px; transition:all .3s; }
        .btn-save:hover { transform:translateY(-2px); }
        .btn-cancel { background:rgba(255,255,255,.1); color:var(--gris); border:1px solid rgba(255,255,255,.2); padding:.8rem 1.5rem; border-radius:12px; font-size:.95rem; cursor:pointer; display:flex; align-items:center; gap:8px; transition:all .3s; }
        .btn-cancel:hover { background:rgba(231,76,60,.2); color:#e74c3c; }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="../index.php" class="logo"><i class="fas fa-leaf"></i> EcoRide</a>
    <nav>
        <a href="vehicules_disponibles.php" class="active"><i class="fas fa-car"></i> Covoiturages</a>
        <a href="mes_reservations.php"><i class="fas fa-calendar-check"></i> Réservations</a>
        <a href="mes_vehicules.php"><i class="fas fa-key"></i> Mes véhicules</a>
        <a href="mon_historique.php"><i class="fas fa-history"></i> Mon historique</a>
        
        <a href="../backoffice/admin.php" class="admin-nav"><i class="fas fa-shield-alt"></i> Admin</a>

        <a href="logout.php"><i class="fas fa-sign-out-alt"></i></a>
    </nav>
</nav>

<div class="container">

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['errors'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php foreach ($_SESSION['errors'] as $e): ?><?= htmlspecialchars($e) ?><?php endforeach; ?>
        </div>
        <?php unset($_SESSION['errors']); ?>
    <?php endif; ?>

    <div class="page-header">
        <h1><i class="fas fa-car"></i> Véhicules Disponibles</h1>
        <p><?= count($vehicules) ?> véhicule<?= count($vehicules) > 1 ? 's' : '' ?> disponible<?= count($vehicules) > 1 ? 's' : '' ?></p>
    </div>

    <?php if (empty($vehicules)): ?>
        <div class="empty-state">
            <i class="fas fa-car-side"></i>
            <p>Aucun véhicule disponible pour le moment.</p>
        </div>
    <?php else: ?>
        <div class="vehicules-grid">
            <?php foreach ($vehicules as $v): ?>
            <div class="vehicule-card">
                <?php
                    // Couleur CSS selon la couleur en base
                    $couleurNom = strtolower(trim($v['couleur'] ?? ''));
                    $couleurMap = [
                        'rouge'      => '#e74c3c', 'red'        => '#e74c3c',
                        'bleu'       => '#2980b9', 'blue'       => '#2980b9',
                        'vert'       => '#27ae60', 'green'      => '#27ae60',
                        'noir'       => '#2c3e50', 'black'      => '#2c3e50',
                        'blanc'      => '#ecf0f1', 'white'      => '#ecf0f1',
                        'gris'       => '#7f8c8d', 'grey'       => '#7f8c8d', 'gray' => '#7f8c8d',
                        'jaune'      => '#f1c40f', 'yellow'     => '#f1c40f',
                        'orange'     => '#e67e22',
                        'violet'     => '#8e44ad', 'purple'     => '#8e44ad',
                        'marron'     => '#795548', 'brown'      => '#795548',
                        'beige'      => '#d4a96a',
                        'argent'     => '#bdc3c7', 'silver'     => '#bdc3c7',
                    ];
                    $carColor  = $couleurMap[$couleurNom] ?? '#61B3FA';
                    $carColorDark = $carColor . 'bb';

                    // Forme SVG selon le modèle/marque (berline, SUV, citadine, etc.)
                    $modele = strtolower($v['modele'] ?? '');
                    $marque = strtolower($v['marque'] ?? '');

                    // Détection type véhicule
                    $suv     = preg_match('/suv|4x4|duster|captur|kadjar|tucson|qashqai|kuga|tiguan|rav4|cx5|sportage|kona|2008|3008|5008|yaris cross|t-roc|t roc|scenic/', $modele . ' ' . $marque);
                    $berline = preg_match('/508|607|407|laguna|talisman|passat|accord|camry|model s|c class|e class|a4|a6/', $modele . ' ' . $marque);
                    $break   = preg_match('/break|touring|combi|estate|sw/', $modele);

                    // SVG citadine (défaut)
                    $svgCitadine = <<<SVG
                    <svg viewBox="0 0 240 100" xmlns="http://www.w3.org/2000/svg">
                      <defs>
                        <linearGradient id="bodyGrad{$v['id']}" x1="0%" y1="0%" x2="0%" y2="100%">
                          <stop offset="0%" style="stop-color:{$carColor};stop-opacity:1"/>
                          <stop offset="100%" style="stop-color:{$carColorDark};stop-opacity:1"/>
                        </linearGradient>
                        <linearGradient id="glassGrad{$v['id']}" x1="0%" y1="0%" x2="0%" y2="100%">
                          <stop offset="0%" style="stop-color:#a8d8f0;stop-opacity:.9"/>
                          <stop offset="100%" style="stop-color:#5bb3d0;stop-opacity:.6"/>
                        </linearGradient>
                      </defs>
                      <!-- Ombre -->
                      <ellipse cx="120" cy="95" rx="95" ry="6" fill="rgba(0,0,0,.3)"/>
                      <!-- Carrosserie basse -->
                      <rect x="15" y="62" width="210" height="22" rx="8" fill="url(#bodyGrad{$v['id']})"/>
                      <!-- Carrosserie haute (toit citadine arrondi) -->
                      <path d="M55 62 Q70 32 100 28 L145 28 Q175 32 188 62 Z" fill="url(#bodyGrad{$v['id']})"/>
                      <!-- Vitre arrière -->
                      <path d="M62 60 Q72 36 98 32 L105 32 L100 60 Z" fill="url(#glassGrad{$v['id']})" opacity=".85"/>
                      <!-- Vitre avant -->
                      <path d="M182 60 Q172 36 148 32 L140 32 L144 60 Z" fill="url(#glassGrad{$v['id']})" opacity=".85"/>
                      <!-- Vitre centrale -->
                      <rect x="104" y="32" width="36" height="28" fill="url(#glassGrad{$v['id']})" opacity=".85"/>
                      <!-- Reflet toit -->
                      <path d="M90 34 Q115 29 145 34 Q130 30 120 30 Q105 30 90 34 Z" fill="white" opacity=".25"/>
                      <!-- Roue arrière -->
                      <circle cx="60" cy="84" r="16" fill="#1a1a2e" stroke="#444" stroke-width="1.5"/>
                      <circle cx="60" cy="84" r="10" fill="#2d2d44" stroke="#555" stroke-width="1"/>
                      <circle cx="60" cy="84" r="4"  fill="#666"/>
                      <!-- Roue avant -->
                      <circle cx="178" cy="84" r="16" fill="#1a1a2e" stroke="#444" stroke-width="1.5"/>
                      <circle cx="178" cy="84" r="10" fill="#2d2d44" stroke="#555" stroke-width="1"/>
                      <circle cx="178" cy="84" r="4"  fill="#666"/>
                      <!-- Phare avant -->
                      <ellipse cx="218" cy="67" rx="8" ry="5" fill="#fffbe6" opacity=".9"/>
                      <ellipse cx="218" cy="67" rx="5" ry="3" fill="white"/>
                      <!-- Phare arrière -->
                      <ellipse cx="22" cy="67" rx="7" ry="4" fill="#e74c3c" opacity=".8"/>
                      <!-- Poignée -->
                      <rect x="108" y="68" width="18" height="3" rx="1.5" fill="rgba(255,255,255,.25)"/>
                      <!-- Ligne carrosserie -->
                      <path d="M20 70 L220 70" stroke="rgba(255,255,255,.15)" stroke-width="1"/>
                    </svg>
                    SVG;

                    $svgSuv = <<<SVG
                    <svg viewBox="0 0 240 110" xmlns="http://www.w3.org/2000/svg">
                      <defs>
                        <linearGradient id="bodyGrad{$v['id']}" x1="0%" y1="0%" x2="0%" y2="100%">
                          <stop offset="0%" style="stop-color:{$carColor};stop-opacity:1"/>
                          <stop offset="100%" style="stop-color:{$carColorDark};stop-opacity:1"/>
                        </linearGradient>
                        <linearGradient id="glassGrad{$v['id']}" x1="0%" y1="0%" x2="0%" y2="100%">
                          <stop offset="0%" style="stop-color:#a8d8f0;stop-opacity:.9"/>
                          <stop offset="100%" style="stop-color:#5bb3d0;stop-opacity:.6"/>
                        </linearGradient>
                      </defs>
                      <!-- Ombre -->
                      <ellipse cx="120" cy="105" rx="100" ry="6" fill="rgba(0,0,0,.3)"/>
                      <!-- Carrosserie basse (SUV plus haute) -->
                      <rect x="12" y="60" width="216" height="32" rx="6" fill="url(#bodyGrad{$v['id']})"/>
                      <!-- Toit SUV carré/haut -->
                      <path d="M45 60 L50 25 L192 25 L196 60 Z" fill="url(#bodyGrad{$v['id']})"/>
                      <!-- Vitre arrière -->
                      <path d="M52 58 L56 28 L80 28 L76 58 Z" fill="url(#glassGrad{$v['id']})" opacity=".85"/>
                      <!-- Vitre avant -->
                      <path d="M190 58 L186 28 L162 28 L166 58 Z" fill="url(#glassGrad{$v['id']})" opacity=".85"/>
                      <!-- Vitre centrale -->
                      <rect x="80" y="28" width="82" height="30" fill="url(#glassGrad{$v['id']})" opacity=".85"/>
                      <!-- Reflet toit -->
                      <rect x="80" y="26" width="82" height="6" rx="2" fill="white" opacity=".2"/>
                      <!-- Barre de toit -->
                      <rect x="55" y="23" width="132" height="4" rx="2" fill="rgba(0,0,0,.3)"/>
                      <!-- Roue arrière -->
                      <circle cx="58" cy="92" r="18" fill="#1a1a2e" stroke="#444" stroke-width="1.5"/>
                      <circle cx="58" cy="92" r="11" fill="#2d2d44" stroke="#555" stroke-width="1"/>
                      <circle cx="58" cy="92" r="4"  fill="#666"/>
                      <!-- Roue avant -->
                      <circle cx="180" cy="92" r="18" fill="#1a1a2e" stroke="#444" stroke-width="1.5"/>
                      <circle cx="180" cy="92" r="11" fill="#2d2d44" stroke="#555" stroke-width="1"/>
                      <circle cx="180" cy="92" r="4"  fill="#666"/>
                      <!-- Phare avant LED -->
                      <rect x="208" y="68" width="14" height="6" rx="3" fill="#fffbe6" opacity=".9"/>
                      <rect x="210" y="70" width="10" height="3" rx="1.5" fill="white"/>
                      <!-- Phare arrière -->
                      <rect x="16" y="68" width="12" height="6" rx="3" fill="#e74c3c" opacity=".8"/>
                      <!-- Marche-pied -->
                      <rect x="40" y="90" width="162" height="5" rx="2" fill="rgba(0,0,0,.35)"/>
                      <!-- Ligne carrosserie -->
                      <path d="M18 75 L222 75" stroke="rgba(255,255,255,.15)" stroke-width="1"/>
                    </svg>
                    SVG;

                    $svgBerline = <<<SVG
                    <svg viewBox="0 0 240 100" xmlns="http://www.w3.org/2000/svg">
                      <defs>
                        <linearGradient id="bodyGrad{$v['id']}" x1="0%" y1="0%" x2="0%" y2="100%">
                          <stop offset="0%" style="stop-color:{$carColor};stop-opacity:1"/>
                          <stop offset="100%" style="stop-color:{$carColorDark};stop-opacity:1"/>
                        </linearGradient>
                        <linearGradient id="glassGrad{$v['id']}" x1="0%" y1="0%" x2="0%" y2="100%">
                          <stop offset="0%" style="stop-color:#a8d8f0;stop-opacity:.9"/>
                          <stop offset="100%" style="stop-color:#5bb3d0;stop-opacity:.6"/>
                        </linearGradient>
                      </defs>
                      <!-- Ombre -->
                      <ellipse cx="120" cy="95" rx="100" ry="6" fill="rgba(0,0,0,.3)"/>
                      <!-- Carrosserie basse (berline longue) -->
                      <rect x="10" y="62" width="220" height="22" rx="6" fill="url(#bodyGrad{$v['id']})"/>
                      <!-- Toit berline profil bas -->
                      <path d="M48 62 Q58 30 90 26 L155 26 Q185 30 196 62 Z" fill="url(#bodyGrad{$v['id']})"/>
                      <!-- Vitre arrière inclinée -->
                      <path d="M55 60 Q62 34 88 30 L96 30 L90 60 Z" fill="url(#glassGrad{$v['id']})" opacity=".85"/>
                      <!-- Vitre avant inclinée -->
                      <path d="M187 60 Q182 34 158 30 L150 30 L154 60 Z" fill="url(#glassGrad{$v['id']})" opacity=".85"/>
                      <!-- Vitre centrale -->
                      <rect x="94" y="30" width="56" height="30" fill="url(#glassGrad{$v['id']})" opacity=".85"/>
                      <!-- Reflet toit -->
                      <path d="M95 28 Q122 23 152 28 Q132 25 120 25 Q108 25 95 28 Z" fill="white" opacity=".3"/>
                      <!-- Roue arrière -->
                      <circle cx="58" cy="84" r="16" fill="#1a1a2e" stroke="#444" stroke-width="1.5"/>
                      <circle cx="58" cy="84" r="10" fill="#2d2d44" stroke="#555" stroke-width="1"/>
                      <circle cx="58" cy="84" r="4"  fill="#666"/>
                      <!-- Roue avant -->
                      <circle cx="182" cy="84" r="16" fill="#1a1a2e" stroke="#444" stroke-width="1.5"/>
                      <circle cx="182" cy="84" r="10" fill="#2d2d44" stroke="#555" stroke-width="1"/>
                      <circle cx="182" cy="84" r="4"  fill="#666"/>
                      <!-- Phare avant -->
                      <ellipse cx="222" cy="68" rx="8" ry="4" fill="#fffbe6" opacity=".9"/>
                      <!-- Phare arrière -->
                      <ellipse cx="18" cy="68" rx="7" ry="4" fill="#e74c3c" opacity=".8"/>
                      <!-- Ligne de style -->
                      <path d="M15 70 Q120 65 225 70" stroke="rgba(255,255,255,.2)" stroke-width="1" fill="none"/>
                    </svg>
                    SVG;

                    $svgFinal = $suv ? $svgSuv : ($berline ? $svgBerline : $svgCitadine);
                ?>
                <!-- Silhouette voiture -->
                <div class="car-visual">
                    <div class="car-bg"></div>
                    <?= $svgFinal ?>
                    <div class="car-badge">
                        <span class="color-dot" style="background:<?= $carColor ?>"></span>
                        <?= htmlspecialchars(ucfirst($v['couleur'] ?? '—')) ?>
                        &nbsp;·&nbsp;
                        <?= $suv ? 'SUV' : ($berline ? 'Berline' : 'Citadine') ?>
                    </div>
                </div>

                <div class="card-header">
                    <h3><i class="fas fa-car"></i> <?= htmlspecialchars($v['marque'] . ' ' . $v['modele']) ?></h3>
                    <p class="conducteur"><i class="fas fa-user"></i> <?= htmlspecialchars($v['prenom'] . ' ' . $v['nom']) ?></p>
                </div>
                <div class="card-body">
                    <div class="card-info">
                        <div class="info-item">
                            <span class="info-label">Immatriculation</span>
                            <span class="info-value"><code><?= htmlspecialchars($v['immatriculation']) ?></code></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Couleur</span>
                            <span class="info-value"><?= htmlspecialchars($v['couleur'] ?? '—') ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Places</span>
                            <span class="info-value"><i class="fas fa-users" style="color:var(--bleu-clair)"></i> <?= $v['capacite'] ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Climatisation</span>
                            <span class="info-value">
                                <?= $v['climatisation']
                                    ? '<i class="fas fa-snowflake" style="color:var(--bleu-clair)"></i> Oui'
                                    : '<i class="fas fa-sun" style="color:#f1c40f"></i> Non' ?>
                            </span>
                        </div>
                    </div>
                    <a href="reserver_vehicule.php?vehicule_id=<?= $v['id'] ?>" class="btn-reserver">
                        <i class="fas fa-calendar-plus"></i> Réserver ce véhicule
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- MODAL RÉSERVATION -->
<div class="modal-overlay" id="reservationModal">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fas fa-calendar-plus"></i> Réserver un véhicule</h3>
            <button class="modal-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
        </div>

        <!-- Infos véhicule -->
        <div class="modal-info" id="vehiculeInfo"></div>

        <form method="POST" id="reservationForm" novalidate>
            <input type="hidden" name="action" value="reserver">
            <input type="hidden" name="vehicule_id" id="vehiculeIdInput">

            <div class="form-group">
                <label><i class="fas fa-calendar-alt"></i> Date de réservation</label>
                <input type="date" name="date_reservation" id="fieldDate" min="<?= date('Y-m-d') ?>">
                <span class="field-error" id="errDate"></span>
            </div>

            <div class="form-group">
                <label><i class="fas fa-sticky-note"></i> Note (optionnel)</label>
                <input type="text" name="note" id="fieldNote" placeholder="Ex: Je prendrai à 8h devant la gare">
            </div>

            <div class="modal-buttons">
                <button type="button" class="btn-cancel" onclick="closeModal()">
                    <i class="fas fa-times"></i> Annuler
                </button>
                <button type="submit" class="btn-save">
                    <i class="fas fa-check"></i> Confirmer la réservation
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const modal = document.getElementById('reservationModal');

    function openReservation(id, nom, conducteur, capacite, clim) {
        document.getElementById('vehiculeIdInput').value = id;
        document.getElementById('vehiculeInfo').innerHTML = `
            <p><i class="fas fa-car"></i> Véhicule : <strong>${nom}</strong></p>
            <p><i class="fas fa-user"></i> Conducteur : <strong>${conducteur}</strong></p>
            <p><i class="fas fa-users"></i> Places : <strong>${capacite}</strong></p>
            <p><i class="fas fa-snowflake"></i> Climatisation : <strong>${clim ? 'Oui' : 'Non'}</strong></p>
        `;
        document.getElementById('reservationForm').reset();
        document.getElementById('vehiculeIdInput').value = id;
        // Date minimum = aujourd'hui
        document.getElementById('fieldDate').min = new Date().toISOString().split('T')[0];
        modal.classList.add('open');
    }

    function closeModal() { modal.classList.remove('open'); }
    window.addEventListener('click', e => { if (e.target === modal) closeModal(); });

    document.getElementById('reservationForm').addEventListener('submit', function(e) {
        const dateInput = document.getElementById('fieldDate');
        const noteInput = document.getElementById('fieldNote');
        const errDate   = document.getElementById('errDate');
        const date      = dateInput ? dateInput.value : '';
        const note      = noteInput ? noteInput.value : '';

        let hasError = false;

        // Vérification : date obligatoire
        if (!date) {
            errDate.textContent = 'Veuillez choisir une date.';
            dateInput.style.borderColor = '#e74c3c';
            hasError = true;
        } else {
            // Vérification : date pas dans le passé
            const selected = new Date(date);
            const today    = new Date();
            today.setHours(0, 0, 0, 0);
            if (selected < today) {
                errDate.textContent = 'La date ne peut pas être dans le passé.';
                dateInput.style.borderColor = '#e74c3c';
                hasError = true;
            } else {
                errDate.textContent = '';
                dateInput.style.borderColor = '';
            }
        }

        // Vérification : note max 500 caractères
        if (note.trim().length > 500) {
            alert('❌ La note ne doit pas dépasser 500 caractères.');
            hasError = true;
        }

        if (hasError) e.preventDefault();
    });

    document.querySelectorAll('.alert').forEach(a => {
        setTimeout(() => a.style.opacity = '0', 4000);
        setTimeout(() => a.remove(), 4500);
    });
</script>
</body>
</html>