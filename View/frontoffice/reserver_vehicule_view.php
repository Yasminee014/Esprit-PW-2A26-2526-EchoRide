<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../Controller/ReservationController.php';

// Récupérer les données du véhicule depuis le contrôleur
$vehicule = $vehicule ?? null;
if (!$vehicule) {
    header('Location: vehicules_disponibles.php');
    exit;
}

// Couleur hex approximative selon le nom
$couleurMap = [
    'rouge'=>'#e74c3c', 'red'=>'#e74c3c', 'bleu'=>'#1976D2', 'blue'=>'#1976D2',
    'vert'=>'#27ae60', 'green'=>'#27ae60', 'noir'=>'#2c3e50', 'black'=>'#2c3e50',
    'blanc'=>'#ecf0f1', 'white'=>'#ecf0f1', 'gris'=>'#7f8c8d', 'gray'=>'#7f8c8d', 'grey'=>'#7f8c8d',
    'jaune'=>'#f1c40f', 'yellow'=>'#f1c40f', 'orange'=>'#e67e22',
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
        * { margin:0; padding:0; box-sizing:border-box; }
        :root { --bleu-fonce:#1976D2; --bleu-clair:#61B3FA; --gris:#A7A9AC; --dark-bg:#0A1628; }
        body { font-family:'Poppins','Segoe UI',sans-serif; background:linear-gradient(135deg,var(--dark-bg) 0%,#0D1F3A 100%); color:#fff; min-height:100vh; }

        /* ── Navbar ── */
        .navbar { background:linear-gradient(90deg,var(--bleu-fonce),#0F3B6E); padding:1rem 2rem; display:flex; justify-content:space-between; align-items:center; box-shadow:0 4px 20px rgba(0,0,0,.3); position:sticky; top:0; z-index:100; }
        .navbar .logo { display:flex; align-items:center; gap:10px; font-size:1.3rem; font-weight:700; color:#fff; text-decoration:none; }
        .navbar .logo i { color:var(--bleu-clair); }
        .navbar nav a { color:#fff !important; text-decoration:none !important; padding:.5rem 1.2rem; border-radius:25px; font-size:.88rem; font-weight:500; transition:all .3s; border:1px solid rgba(97,179,250,.35); background:rgba(255,255,255,.08); display:inline-flex; align-items:center; gap:8px; margin:0 2px; }
        .navbar nav a:hover { background:rgba(25,118,210,.3) !important; border-color:#61B3FA !important; }
        .navbar nav a.active { background:rgba(25,118,210,.35) !important; border-color:#61B3FA !important; color:#61B3FA !important; }

        /* ── Layout ── */
        .container { max-width:900px; margin:0 auto; padding:3rem 2rem; }

        /* ── Breadcrumb ── */
        .breadcrumb { display:flex; align-items:center; gap:.5rem; font-size:.82rem; color:var(--gris); margin-bottom:2rem; }
        .breadcrumb a { color:var(--bleu-clair); text-decoration:none; }
        .breadcrumb a:hover { text-decoration:underline; }
        .breadcrumb i { font-size:.7rem; }

        /* ── Page title ── */
        .page-title { font-size:1.8rem; display:flex; align-items:center; gap:12px; margin-bottom:.5rem; }
        .page-title i { color:var(--bleu-clair); }
        .page-subtitle { color:var(--gris); font-size:.9rem; margin-bottom:2.5rem; }

        /* ── Grid ── */
        .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:2rem; }
        @media(max-width:700px){ .form-grid { grid-template-columns:1fr; } }

        /* ── Card véhicule ── */
        .vehicule-card { background:rgba(255,255,255,.06); border:1px solid rgba(97,179,250,.25); border-radius:20px; overflow:hidden; }
        .car-visual { width:100%; height:180px; display:flex; align-items:center; justify-content:center; background:linear-gradient(135deg,rgba(25,118,210,.3),rgba(97,179,250,.1)); position:relative; }
        .car-visual svg { width:220px; height:110px; filter:drop-shadow(0 8px 24px rgba(0,0,0,.5)); }
        .car-badge { position:absolute; bottom:10px; right:12px; background:rgba(0,0,0,.5); backdrop-filter:blur(6px); border:1px solid rgba(255,255,255,.15); border-radius:20px; padding:.25rem .75rem; font-size:.75rem; color:#fff; display:flex; align-items:center; gap:5px; }
        .color-dot { width:10px; height:10px; border-radius:50%; display:inline-block; }

        .vehicule-info { padding:1.5rem; }
        .vehicule-info h2 { font-size:1.3rem; margin-bottom:.3rem; }
        .vehicule-info h2 span { color:var(--gris); font-weight:400; }
        .vehicule-info .conducteur { color:var(--gris); font-size:.85rem; margin-bottom:1.2rem; display:flex; align-items:center; gap:6px; }
        .info-grid { display:grid; grid-template-columns:1fr 1fr; gap:.8rem; }
        .info-item { background:rgba(255,255,255,.05); border-radius:10px; padding:.7rem .9rem; }
        .info-item .lbl { font-size:.72rem; color:var(--gris); text-transform:uppercase; letter-spacing:.5px; display:block; margin-bottom:3px; }
        .info-item .val { font-size:.9rem; font-weight:500; display:flex; align-items:center; gap:6px; }
        .info-item .val code { color:var(--bleu-clair); font-family:monospace; }
        .info-item .val .clim-yes { color:var(--bleu-clair); }
        .info-item .val .clim-no  { color:#f1c40f; }

        /* ── Formulaire ── */
        .form-card { background:rgba(255,255,255,.06); border:1px solid rgba(97,179,250,.25); border-radius:20px; padding:2rem; display:flex; flex-direction:column; gap:1.5rem; }
        .form-card h3 { font-size:1.1rem; color:var(--bleu-clair); display:flex; align-items:center; gap:8px; padding-bottom:1rem; border-bottom:1px solid rgba(97,179,250,.15); }

        .form-group { display:flex; flex-direction:column; gap:.5rem; }
        .form-group label { font-size:.85rem; color:var(--bleu-clair); display:flex; align-items:center; gap:7px; font-weight:500; }
        .form-group input, .form-group textarea {
            background:rgba(255,255,255,.08); border:1px solid rgba(97,179,250,.3);
            color:#fff; padding:.8rem 1rem; border-radius:12px;
            font-size:.92rem; outline:none; transition:all .3s; font-family:inherit;
        }
        .form-group input:focus, .form-group textarea:focus { border-color:var(--bleu-clair); background:rgba(97,179,250,.08); }
        .form-group textarea { resize:vertical; min-height:90px; }
        .form-group input::placeholder, .form-group textarea::placeholder { color:rgba(167,169,172,.6); }
        .field-error { color:#e74c3c; font-size:.78rem; margin-top:.25rem; display:block; }

        /* ── Alerte ── */
        .alert { padding:1rem 1.5rem; border-radius:14px; margin-bottom:1.5rem; display:flex; align-items:center; gap:10px; }
        .alert-error { background:rgba(231,76,60,.15); border:1px solid rgba(231,76,60,.4); color:#e74c3c; }

        /* ── Boutons ── */
        .form-actions { display:flex; gap:1rem; justify-content:flex-end; margin-top:.5rem; }
        .btn-annuler {
            background:rgba(255,255,255,.08); color:var(--gris);
            border:1px solid rgba(255,255,255,.2); padding:.85rem 1.8rem;
            border-radius:12px; font-size:.95rem; cursor:pointer;
            display:inline-flex; align-items:center; gap:8px; text-decoration:none;
            transition:all .3s; font-family:inherit;
        }
        .btn-annuler:hover { background:rgba(231,76,60,.15); color:#e74c3c; border-color:rgba(231,76,60,.4); }
        .btn-confirmer {
            background:linear-gradient(135deg,var(--bleu-fonce),var(--bleu-clair));
            color:#fff; border:none; padding:.85rem 2rem; border-radius:12px;
            font-size:.95rem; cursor:pointer; display:inline-flex; align-items:center;
            gap:8px; transition:all .3s; font-family:inherit; font-weight:600;
        }
        .btn-confirmer:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(25,118,210,.4); }
    </style>
</head>
<body>

<!-- Navbar -->
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

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="vehicules_disponibles.php"><i class="fas fa-car"></i> Covoiturages</a>
        <i class="fas fa-chevron-right"></i>
        <span>Réserver un véhicule</span>
    </div>

    <!-- Titre -->
    <h1 class="page-title"><i class="fas fa-calendar-plus"></i> Réserver un véhicule</h1>
    <p class="page-subtitle">Remplissez le formulaire ci-dessous pour confirmer votre réservation.</p>

    <?php if (!empty($_SESSION['errors'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php foreach ($_SESSION['errors'] as $e): ?><?= htmlspecialchars($e) ?> <?php endforeach; unset($_SESSION['errors']); ?>
        </div>
    <?php endif; ?>

    <div class="form-grid">

        <!-- Carte véhicule -->
        <div class="vehicule-card">
            <div class="car-visual">
                <svg viewBox="0 0 240 100" xmlns="http://www.w3.org/2000/svg">
                    <g fill="<?= htmlspecialchars($couleurHex) ?>">
                        <rect x="10" y="55" width="220" height="30" rx="10"/>
                        <path d="M55 55 Q65 28 90 22 L155 22 Q175 22 185 55 Z"/>
                        <rect x="200" y="62" width="28" height="14" rx="5"/>
                        <rect x="12" y="62" width="28" height="14" rx="5"/>
                    </g>
                    <path d="M92 53 Q97 32 112 27 L148 27 Q160 27 168 53 Z" fill="rgba(97,179,250,0.5)"/>
                    <circle cx="65" cy="84" r="14" fill="#1a2030"/>
                    <circle cx="65" cy="84" r="7" fill="#3a4a60"/>
                    <circle cx="175" cy="84" r="14" fill="#1a2030"/>
                    <circle cx="175" cy="84" r="7" fill="#3a4a60"/>
                    <rect x="205" y="60" width="10" height="6" rx="2" fill="#f1c40f" opacity=".8"/>
                    <rect x="25" y="60" width="10" height="6" rx="2" fill="#e74c3c" opacity=".6"/>
                </svg>
                <div class="car-badge">
                    <span class="color-dot" style="background:<?= htmlspecialchars($couleurHex) ?>"></span>
                    <?= htmlspecialchars(ucfirst($vehicule['couleur'] ?? '—')) ?> · <?= htmlspecialchars($vehicule['modele']) ?>
                </div>
            </div>
            <div class="vehicule-info">
                <h2>
                    <strong><?= htmlspecialchars($vehicule['marque']) ?></strong>
                    <span> <?= htmlspecialchars($vehicule['modele']) ?></span>
                </h2>
                <div class="conducteur">
                    <i class="fas fa-user" style="color:var(--bleu-clair)"></i>
                    <?= htmlspecialchars(($vehicule['prenom'] ?? '') . ' ' . ($vehicule['nom'] ?? '')) ?>
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="lbl">Immatriculation</span>
                        <span class="val"><code><?= htmlspecialchars($vehicule['immatriculation']) ?></code></span>
                    </div>
                    <div class="info-item">
                        <span class="lbl">Places</span>
                        <span class="val"><i class="fas fa-users" style="color:var(--bleu-clair)"></i> <?= $vehicule['capacite'] ?></span>
                    </div>
                    <div class="info-item">
                        <span class="lbl">Couleur</span>
                        <span class="val">
                            <span class="color-dot" style="background:<?= htmlspecialchars($couleurHex) ?>; border:1px solid rgba(255,255,255,.2)"></span>
                            <?= htmlspecialchars(ucfirst($vehicule['couleur'] ?? '—')) ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="lbl">Climatisation</span>
                        <span class="val">
                            <?php if ($vehicule['climatisation']): ?>
                                <i class="fas fa-snowflake clim-yes"></i> Oui
                            <?php else: ?>
                                <i class="fas fa-sun clim-no"></i> Non
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulaire -->
        <form method="POST" action="reserver_vehicule.php" id="reservationForm" novalidate>
            <div class="form-card">
                <h3><i class="fas fa-calendar-plus"></i> Détails de la réservation</h3>

                <input type="hidden" name="action" value="reserver">
                <input type="hidden" name="vehicule_id" value="<?= $vehicule['id'] ?>">

                <div class="form-group">
                    <label><i class="fas fa-calendar-alt"></i> Date de réservation <span style="color:#e74c3c">*</span></label>
                    <input type="text" name="date_reservation" id="dateReservation" placeholder="JJ/MM/AAAA" autocomplete="off">
                    <span class="field-error" id="dateError"></span>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-sticky-note"></i> Note (optionnel)</label>
                    <textarea name="note" id="note" placeholder="Ex: Je prendrai à 8h devant la gare…"><?= htmlspecialchars($_POST['note'] ?? '') ?></textarea>
                    <span class="field-error" id="noteError"></span>
                </div>

                <div class="form-actions">
                    <a href="vehicules_disponibles.php" class="btn-annuler">
                        <i class="fas fa-times"></i> Annuler
                    </a>
                    <button type="submit" class="btn-confirmer">
                        <i class="fas fa-check"></i> Confirmer la réservation
                    </button>
                </div>
            </div>
        </form>

    </div>
</div>

<script>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réserver un véhicule | EcoRide</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="../assets/js/validation.js"></script>  <!-- ← AJOUTER CETTE LIGNE -->
</head>
</body>
</html>