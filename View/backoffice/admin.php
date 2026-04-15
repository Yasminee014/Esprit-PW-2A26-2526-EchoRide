<?php
// ══════════════════════════════════════════════
//  ADMIN.PHP — Page admin unique EcoRide
//  Sections : Véhicules | Réservations | Historique
// ══════════════════════════════════════════════
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../Config/Database.php';
require_once __DIR__ . '/../../Model/VehiculeModel.php';
require_once __DIR__ . '/../../Model/ReservationModel.php';

$db           = Database::getInstance();
$vModel       = new VehiculeModel();
$rModel       = new ReservationModel();

// ─── Onglet actif ────────────────────────────
$tab = $_GET['tab'] ?? 'vehicules';
if (!in_array($tab, ['vehicules', 'reservations', 'historique'])) $tab = 'vehicules';

// ══════════════════════════════════════════════
//  TRAITEMENT DES ACTIONS POST
// ══════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ── Véhicules ────────────────────────────
    if ($action === 'vehicule_create') {
        $data = sanitizeVehicule($_POST);
        if (empty($data['user_id'])) {
            $first = $db->query("SELECT id FROM users LIMIT 1")->fetch();
            $data['user_id'] = $first ? intval($first['id']) : 1;
        }
        $errors = $vModel->validate($data);
        if (empty($errors)) {
            $vModel->create($data)
                ? ($_SESSION['success'] = 'Véhicule ajouté avec succès.')
                : ($_SESSION['errors']  = ["Erreur lors de l'ajout."]);
        } else {
            $_SESSION['errors'] = $errors;
        }
        header('Location: admin.php?tab=vehicules'); exit;
    }

    if ($action === 'vehicule_update') {
        $id   = intval($_POST['id'] ?? 0);
        $data = sanitizeVehicule($_POST);
        $errors = $vModel->validate($data);
        if (empty($errors)) {
            $vModel->update($id, $data)
                ? ($_SESSION['success'] = 'Véhicule modifié avec succès.')
                : ($_SESSION['errors']  = ['Erreur lors de la modification.']);
        } else {
            $_SESSION['errors'] = $errors;
        }
        header('Location: admin.php?tab=vehicules'); exit;
    }

    if ($action === 'vehicule_statut') {
        $id     = intval($_POST['id'] ?? 0);
        $statut = $_POST['statut'] ?? '';
        if (in_array($statut, ['disponible','indisponible','en_maintenance'])) {
            $vModel->updateStatut($id, $statut);
        }
        header('Location: admin.php?tab=vehicules'); exit;
    }

    if ($action === 'vehicule_delete') {
        $id = intval($_POST['id'] ?? 0);
        $vModel->delete($id)
            ? ($_SESSION['success'] = 'Véhicule supprimé.')
            : ($_SESSION['errors']  = ['Erreur lors de la suppression.']);
        header('Location: admin.php?tab=vehicules'); exit;
    }

    // ── Réservations ─────────────────────────
    if ($action === 'resa_statut') {
        $id     = intval($_POST['id'] ?? 0);
        $statut = $_POST['statut'] ?? '';
        if (in_array($statut, ['en_attente','confirmee','annulee'])) {
            $rModel->updateStatut($id, $statut);
            $_SESSION['success'] = 'Statut mis à jour.';
        }
        header('Location: admin.php?tab=reservations'); exit;
    }

    if ($action === 'resa_delete') {
        $id = intval($_POST['id'] ?? 0);
        $rModel->delete($id)
            ? ($_SESSION['success'] = 'Réservation supprimée.')
            : ($_SESSION['errors']  = ['Erreur lors de la suppression.']);
        header('Location: admin.php?tab=reservations'); exit;
    }

    // ── Modification réservation ─────────────
    if ($action === 'resa_update') {
        $id = intval($_POST['id'] ?? 0);
        
        // Récupérer la réservation existante pour avoir vehicule_id et user_id
        $resa = $rModel->getById($id);
        
        if ($resa) {
            $data = [
                'vehicule_id' => $resa['vehicule_id'],
                'user_id' => $resa['user_id'],
                'trajet_id' => $resa['trajet_id'] ?? null,
                'date_reservation' => $_POST['date_reservation'] ?? '',
                'statut' => $_POST['statut'] ?? 'en_attente'
            ];
            
            $errors = [];
            if (empty($data['date_reservation'])) {
                $errors[] = 'La date est obligatoire.';
            }
            if (!in_array($data['statut'], ['en_attente', 'confirmee', 'annulee'])) {
                $errors[] = 'Statut invalide.';
            }
            
            if (empty($errors)) {
                if ($rModel->update($id, $data)) {
                    $_SESSION['success'] = 'Réservation modifiée avec succès.';
                } else {
                    $_SESSION['errors'] = ['Erreur lors de la modification.'];
                }
            } else {
                $_SESSION['errors'] = $errors;
            }
        } else {
            $_SESSION['errors'] = ['Réservation non trouvée.'];
        }
        header('Location: admin.php?tab=reservations'); exit;
    }
}

// ══════════════════════════════════════════════
//  DONNÉES SELON ONGLET
// ══════════════════════════════════════════════

// ── Véhicules ────────────────────────────────
$search   = trim($_GET['search'] ?? '');
$vehicules = $search ? $vModel->search($search) : $vModel->getAll();
$vStats   = [
    'total'         => $vModel->countAll(),
    'disponibles'   => $vModel->countByStatut('disponible'),
    'maintenance'   => $vModel->countByStatut('en_maintenance'),
    'indisponibles' => $vModel->countByStatut('indisponible'),
];
$users = $db->query("SELECT id, nom, prenom FROM users ORDER BY nom, prenom")->fetchAll();

// ── Réservations ─────────────────────────────
$rFilter       = $_GET['statut_r'] ?? '';
$reservations  = $rFilter ? $rModel->filterByStatut($rFilter) : $rModel->getAll();
$rStats = [
    'total'      => $rModel->countAll(),
    'confirmees' => $rModel->countByStatut('confirmee'),
    'annulees'   => $rModel->countByStatut('annulee'),
    'en_attente' => $rModel->countByStatut('en_attente'),
];

// ── Historique ───────────────────────────────
$hStatut    = $_GET['statut_h']   ?? '';
$hDateDebut = $_GET['date_debut'] ?? '';
$hDateFin   = $_GET['date_fin']   ?? '';
$historique = $rModel->getHistoriqueAdmin($hStatut, $hDateDebut, $hDateFin);
$hStats     = $rModel->statsHistoriqueAdmin();

// ══════════════════════════════════════════════
//  HELPER SANITIZE
// ══════════════════════════════════════════════
function sanitizeVehicule(array $p): array {
    return [
        'user_id'         => intval($p['user_id']        ?? 0),
        'marque'          => htmlspecialchars(trim($p['marque']         ?? ''), ENT_QUOTES, 'UTF-8'),
        'modele'          => htmlspecialchars(trim($p['modele']         ?? ''), ENT_QUOTES, 'UTF-8'),
        'immatriculation' => strtoupper(trim($p['immatriculation']      ?? '')),
        'couleur'         => htmlspecialchars(trim($p['couleur']         ?? ''), ENT_QUOTES, 'UTF-8'),
        'capacite'        => intval($p['capacite']        ?? 4),
        'climatisation'   => isset($p['climatisation']) ? 1 : 0,
        'statut'          => $p['statut']                ?? 'disponible',
    ];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Administration — EcoRide</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
:root{
  --blue:#1976D2;--blue-light:#61B3FA;
  --dark:#0A1628;--dark2:#0D1F3A;--dark3:#0F3B6E;
  --white:#F4F5F7;--grey:#A7A9AC;
  --green:#27ae60;--red:#e74c3c;--yellow:#f1c40f;--orange:#e67e22;
}
body{font-family:'Poppins','Segoe UI',sans-serif;background:linear-gradient(135deg,var(--dark) 0%,var(--dark2) 100%);color:#fff;min-height:100vh;}

/* ── SIDEBAR ── */
.wrap{display:flex;min-height:100vh;}
.sidebar{width:260px;background:linear-gradient(180deg,var(--blue) 0%,var(--dark3) 100%);padding:1.5rem 1rem;position:fixed;height:100vh;overflow-y:auto;box-shadow:4px 0 20px rgba(0,0,0,.4);z-index:50;}
.logo{text-align:center;margin-bottom:1.5rem;padding-bottom:1rem;border-bottom:2px solid var(--blue-light);}
.logo i{font-size:40px;color:var(--blue-light);display:block;margin-bottom:6px;}
.logo h2{background:linear-gradient(135deg,#fff,var(--blue-light));-webkit-background-clip:text;background-clip:text;color:transparent;font-size:1.35rem;font-weight:700;}
.logo p{color:var(--grey);font-size:.72rem;letter-spacing:1px;text-transform:uppercase;}
.nav-section{color:var(--grey);font-size:.68rem;text-transform:uppercase;letter-spacing:1.5px;padding:.7rem 1rem .25rem;font-weight:600;}
nav ul{list-style:none;}
nav ul li{margin-bottom:.25rem;}
nav ul li a{display:flex;align-items:center;gap:11px;padding:.72rem 1rem;color:#fff;text-decoration:none;border-radius:10px;transition:all .25s;font-size:.88rem;}
nav ul li a i{width:18px;color:var(--blue-light);font-size:.9rem;}
nav ul li a:hover,nav ul li a.active{background:rgba(255,255,255,.15);border-left:3px solid var(--blue-light);}
nav ul li a:hover i,nav ul li a.active i{color:#fff;}
.sidebar-sep{border:none;border-top:1px solid rgba(97,179,250,.2);margin:.75rem 0;}

/* ── MAIN ── */
.main{flex:1;margin-left:260px;padding:1.6rem;}

/* ── TOPBAR ── */
.topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:1.6rem;padding-bottom:1rem;border-bottom:1px solid rgba(97,179,250,.2);}
.topbar h1{font-size:1.5rem;display:flex;align-items:center;gap:9px;color:var(--white);}
.topbar h1 i{color:var(--blue-light);}
.pill{background:rgba(255,255,255,.08);border:1px solid rgba(97,179,250,.3);color:#fff;padding:.4rem .9rem;border-radius:20px;font-size:.8rem;display:inline-flex;align-items:center;gap:6px;}
.pill-user{background:rgba(255,255,255,.08);border:1px solid rgba(97,179,250,.3);color:#fff;padding:.4rem .9rem;border-radius:20px;font-size:.8rem;display:inline-flex;align-items:center;gap:6px;text-decoration:none;transition:all .25s;}
.pill-user:hover{background:rgba(25,118,210,.3);border-color:#61b3fa;color:#61b3fa;}

/* ── ALERTS ── */
.alert{padding:.8rem 1.2rem;border-radius:12px;margin-bottom:1.2rem;display:flex;align-items:center;gap:9px;font-size:.88rem;}
.alert-success{background:rgba(39,174,96,.14);border:1px solid rgba(39,174,96,.35);color:var(--green);}
.alert-error  {background:rgba(231,76,60,.14);border:1px solid rgba(231,76,60,.35);color:var(--red);}

/* ── STATS ── */
.stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;margin-bottom:1.6rem;}
.stat{background:rgba(255,255,255,.07);border:1px solid rgba(97,179,250,.16);border-radius:14px;padding:1.2rem;text-align:center;transition:all .3s;}
.stat:hover{transform:translateY(-4px);border-color:var(--blue-light);box-shadow:0 8px 22px rgba(25,118,210,.18);}
.stat i{font-size:1.8rem;color:var(--blue-light);margin-bottom:.35rem;display:block;}
.stat .num{font-size:2rem;font-weight:700;background:linear-gradient(135deg,var(--blue-light),#fff);-webkit-background-clip:text;background-clip:text;color:transparent;}
.stat .lbl{color:var(--grey);font-size:.75rem;margin-top:.2rem;}

/* ── TOOLBAR ── */
.toolbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:.7rem;}
.search-box{position:relative;flex:1;max-width:300px;}
.search-box input{width:100%;background:rgba(255,255,255,.08);border:1px solid rgba(97,179,250,.3);color:#fff;padding:.5rem .9rem .5rem 2.2rem;border-radius:18px;font-size:.84rem;outline:none;transition:all .25s;font-family:inherit;}
.search-box input::placeholder{color:var(--grey);}
.search-box input:focus{border-color:var(--blue-light);background:rgba(97,179,250,.08);}
.search-box i{position:absolute;left:.75rem;top:50%;transform:translateY(-50%);color:var(--grey);font-size:.82rem;}

/* ── BUTTONS ── */
.btn{padding:.5rem 1.1rem;border-radius:18px;font-size:.84rem;font-family:inherit;cursor:pointer;border:none;display:inline-flex;align-items:center;gap:6px;transition:all .25s;font-weight:500;text-decoration:none;}
.btn-primary{background:var(--blue);color:#fff;}
.btn-primary:hover{background:#1565C0;transform:translateY(-1px);}
.btn-outline{background:rgba(255,255,255,.08);border:1px solid rgba(97,179,250,.3);color:#fff;}
.btn-outline:hover{background:rgba(25,118,210,.25);border-color:var(--blue-light);}

/* ── FILTERS ── */
.filters{display:flex;gap:.5rem;margin-bottom:1rem;flex-wrap:wrap;}
.f{background:rgba(255,255,255,.08);border:1px solid rgba(97,179,250,.2);color:var(--grey);padding:.38rem .9rem;border-radius:14px;font-size:.8rem;cursor:pointer;text-decoration:none;transition:all .25s;}
.f:hover,.f.on{background:rgba(25,118,210,.22);border-color:var(--blue-light);color:var(--blue-light);}

/* ── TABLE ── */
.tbl-wrap{background:rgba(255,255,255,.04);border-radius:14px;overflow:hidden;border:1px solid rgba(97,179,250,.1);}
table{width:100%;border-collapse:collapse;}
thead{background:rgba(25,118,210,.22);}
thead th{padding:.85rem 1rem;text-align:left;font-size:.76rem;text-transform:uppercase;letter-spacing:.7px;color:var(--blue-light);font-weight:600;}
tbody tr{border-bottom:1px solid rgba(255,255,255,.04);transition:background .18s;}
tbody tr:last-child{border-bottom:none;}
tbody tr:hover{background:rgba(97,179,250,.05);}
tbody td{padding:.8rem 1rem;font-size:.87rem;vertical-align:middle;}
code{color:var(--blue-light);font-family:monospace;font-size:.84rem;}

/* ── BADGES ── */
.badge{display:inline-block;padding:.18rem .65rem;border-radius:11px;font-size:.73rem;font-weight:600;}
.b-dispo   {background:rgba(39,174,96,.17);color:#27ae60;border:1px solid rgba(39,174,96,.32);}
.b-indispo {background:rgba(231,76,60,.17);color:#e74c3c;border:1px solid rgba(231,76,60,.32);}
.b-maint   {background:rgba(230,126,34,.17);color:#e67e22;border:1px solid rgba(230,126,34,.32);}
.b-attente {background:rgba(241,196,15,.17);color:#f1c40f;border:1px solid rgba(241,196,15,.32);}
.b-conf    {background:rgba(39,174,96,.17);color:#27ae60;border:1px solid rgba(39,174,96,.32);}
.b-annul   {background:rgba(231,76,60,.17);color:#e74c3c;border:1px solid rgba(231,76,60,.32);}

/* ── SELECTS INLINE ── */
.st-sel{background:rgba(255,255,255,.08);border:1px solid rgba(97,179,250,.25);color:#fff;padding:.28rem .5rem;border-radius:7px;font-size:.79rem;cursor:pointer;outline:none;font-family:inherit;}
.st-sel option{background:#0D1F3A;}

/* ── ACTION ICONS ── */
.acts{display:flex;gap:5px;}
.ic{width:30px;height:30px;border:none;border-radius:7px;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:.82rem;transition:all .22s;}
.ic:hover{transform:scale(1.12);}
.ic-edit{background:rgba(25,118,210,.2);color:var(--blue-light);}
.ic-del {background:rgba(231,76,60,.18);color:#e74c3c;}

/* ── EMPTY ── */
.empty{text-align:center;padding:2.5rem;color:var(--grey);}
.empty i{font-size:2.2rem;color:rgba(97,179,250,.22);margin-bottom:.7rem;display:block;}

/* ── DATE FILTERS ── */
.date-row{display:flex;align-items:center;gap:.7rem;flex-wrap:wrap;margin-bottom:1rem;}
.date-row label{color:var(--grey);font-size:.8rem;}
.date-row input[type=date]{background:rgba(255,255,255,.08);border:1px solid rgba(97,179,250,.25);color:#fff;padding:.42rem .75rem;border-radius:9px;font-size:.8rem;outline:none;font-family:inherit;color-scheme:dark;}
.date-row input[type=date]:focus{border-color:var(--blue-light);}

/* ── BOUTON MODIFIER (lien) ── */
.btn-edit-link {
    background:rgba(25,118,210,.2);
    color:var(--blue-light);
    padding:0.4rem 0.8rem;
    border-radius:7px;
    font-size:.82rem;
    text-decoration:none;
    display:inline-flex;
    align-items:center;
    gap:5px;
    transition:all .22s;
}
.btn-edit-link:hover {
    background:rgba(25,118,210,.4);
    transform:scale(1.05);
}

/* ── MODAL ── */
.overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.65);backdrop-filter:blur(3px);z-index:999;align-items:center;justify-content:center;}
.overlay.open{display:flex;}
.modal{background:linear-gradient(145deg,#0D1F3A,#122A4A);border:1px solid rgba(97,179,250,.22);border-radius:18px;padding:1.8rem;width:90%;max-width:500px;max-height:92vh;overflow-y:auto;animation:mIn .28s ease;}
@keyframes mIn{from{opacity:0;transform:translateY(-18px)}to{opacity:1;transform:translateY(0)}}
.modal h2{font-size:1.15rem;margin-bottom:1.3rem;display:flex;align-items:center;gap:9px;color:var(--white);}
.modal h2 i{color:var(--blue-light);}
.fgrid{display:grid;grid-template-columns:1fr 1fr;gap:.9rem;margin-bottom:.9rem;}
.fg{margin-bottom:.9rem;}
.fg label{display:block;font-size:.76rem;color:var(--grey);margin-bottom:.32rem;text-transform:uppercase;letter-spacing:.5px;}
.fg input,.fg select{width:100%;background:rgba(255,255,255,.07);border:1px solid rgba(97,179,250,.22);color:#fff;padding:.55rem .8rem;border-radius:9px;font-size:.86rem;font-family:inherit;outline:none;transition:all .22s;}
.fg input:focus,.fg select:focus{border-color:var(--blue-light);}
.fg input::placeholder{color:var(--grey);}
.fg select option{background:#0D1F3A;}
.chk{display:flex;align-items:center;gap:7px;margin-top:.4rem;}
.chk input{width:17px;height:17px;cursor:pointer;accent-color:var(--blue);}
.chk label{text-transform:none;letter-spacing:0;color:#fff;font-size:.86rem;}
.mfooter{display:flex;justify-content:flex-end;gap:.7rem;margin-top:1.4rem;}
.ferr{color:#e74c3c;font-size:.75rem;margin-top:.2rem;display:block;}
</style>
</head>
<body>
<div class="wrap">

<!-- ══ SIDEBAR ══ -->
<aside class="sidebar">
  <div class="logo">
    <i class="fas fa-leaf"></i>
    <h2>EcoRide</h2>
    <p>Administration</p>
  </div>
  <nav>
    <div class="nav-section">Gestion</div>
    <ul>
      <li><a href="?tab=vehicules"    class="<?= $tab==='vehicules'   ?'active':'' ?>"><i class="fas fa-car"></i> Véhicules</a></li>
      <li><a href="?tab=reservations" class="<?= $tab==='reservations'?'active':'' ?>"><i class="fas fa-calendar-check"></i> Réservations</a></li>
      <li><a href="?tab=historique"   class="<?= $tab==='historique'  ?'active':'' ?>"><i class="fas fa-chart-line"></i> Historique</a></li>
    </ul>
    <hr class="sidebar-sep">
    <ul>
      <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
    </ul>
  </nav>
</aside>

<!-- ══ MAIN ══ -->
<main class="main">

<?php
// ══════════════════════════════════════════════
//  ALERTS GLOBALES
// ══════════════════════════════════════════════
if (!empty($_SESSION['success'])): ?>
  <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['success']) ?></div>
  <?php unset($_SESSION['success']); endif;
if (!empty($_SESSION['errors'])): ?>
  <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i>
    <?php foreach($_SESSION['errors'] as $e): ?><?= htmlspecialchars($e) ?> <?php endforeach; ?>
  </div>
  <?php unset($_SESSION['errors']); endif; ?>

<?php // ══════════════════════════════════════
//  ONGLET VÉHICULES
// ══════════════════════════════════════════
if ($tab === 'vehicules'): ?>

<div class="topbar">
  <h1><i class="fas fa-car"></i> Gestion des Véhicules</h1>
  <a href="../frontoffice/vehicules_disponibles.php" target="_blank" class="pill-user"><i class="fas fa-user"></i> Espace utilisateur</a>
  <span class="pill"><i class="fas fa-shield-alt"></i> Admin</span>
</div>

<div class="stats">
  <div class="stat"><i class="fas fa-car"></i><div class="num"><?= $vStats['total'] ?></div><div class="lbl">Total</div></div>
  <div class="stat"><i class="fas fa-check-circle"></i><div class="num"><?= $vStats['disponibles'] ?></div><div class="lbl">Disponibles</div></div>
  <div class="stat"><i class="fas fa-wrench"></i><div class="num"><?= $vStats['maintenance'] ?></div><div class="lbl">En maintenance</div></div>
  <div class="stat"><i class="fas fa-ban"></i><div class="num"><?= $vStats['indisponibles'] ?></div><div class="lbl">Indisponibles</div></div>
</div>

<div class="toolbar">
  <form method="GET" style="display:flex;align-items:center;gap:.6rem;">
    <input type="hidden" name="tab" value="vehicules">
    <div class="search-box">
      <i class="fas fa-search"></i>
      <input type="text" name="search" placeholder="Marque, modèle, immat…" value="<?= htmlspecialchars($search) ?>" id="searchInput">
    </div>
    <?php if ($search): ?>
      <a href="?tab=vehicules" class="btn btn-outline"><i class="fas fa-times"></i> Effacer</a>
    <?php endif; ?>
  </form>
  <a href="admin_ajouter_vehicule.php" class="btn btn-primary">
    <i class="fas fa-plus"></i> Ajouter un véhicule
  </a>
</div>

<div class="tbl-wrap">
  <table>
    <thead>
      <tr>
        <th>#</th><th>Conducteur</th><th>Marque / Modèle</th><th>Immatriculation</th>
        <th>Places</th><th>Clim</th><th>Statut</th><th>Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php if (empty($vehicules)): ?>
      <tr><td colspan="8"><div class="empty"><i class="fas fa-car-side"></i><p>Aucun véhicule trouvé</p></div></td></tr>
    <?php else: foreach ($vehicules as $v): ?>
      <tr>
        <td><?= $v['id'] ?></td>
        <td><?= htmlspecialchars(($v['prenom'] ?? '') . ' ' . ($v['nom'] ?? '')) ?></td>
        <td><strong><?= htmlspecialchars($v['marque']) ?></strong> <?= htmlspecialchars($v['modele']) ?>
            <?php if ($v['couleur']): ?><small style="color:var(--grey)"> · <?= htmlspecialchars($v['couleur']) ?></small><?php endif; ?></td>
        <td><code><?= htmlspecialchars($v['immatriculation']) ?></code></td>
        <td><?= $v['capacite'] ?></td>
        <td><?= $v['climatisation'] ? '<i class="fas fa-snowflake" style="color:var(--blue-light)"></i>' : '<i class="fas fa-minus" style="color:var(--grey)"></i>' ?></td>
        <td>
          <form method="POST" style="margin:0;">
            <input type="hidden" name="action" value="vehicule_statut">
            <input type="hidden" name="id" value="<?= $v['id'] ?>">
            <select name="statut" class="st-sel" onchange="this.form.submit()">
              <option value="disponible"    <?= $v['statut']==='disponible'    ?'selected':'' ?>>✓ Disponible</option>
              <option value="indisponible"  <?= $v['statut']==='indisponible'  ?'selected':'' ?>>✗ Indisponible</option>
              <option value="en_maintenance"<?= $v['statut']==='en_maintenance'?'selected':'' ?>>⚙ Maintenance</option>
            </select>
          </form>
        </td>
        <td><div class="acts">
          <a href="admin_modifier_vehicule.php?id=<?= $v['id'] ?>" class="btn-edit-link" title="Modifier">
            <i class="fas fa-pen"></i> Modifier
          </a>
          <form method="POST" style="margin:0;" onsubmit="return confirm('Supprimer ce véhicule ?')">
            <input type="hidden" name="action" value="vehicule_delete">
            <input type="hidden" name="id" value="<?= $v['id'] ?>">
            <button type="submit" class="ic ic-del" title="Supprimer"><i class="fas fa-trash"></i></button>
          </form>
        </div></td>
      </tr>
    <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>

<?php // ══════════════════════════════════════
//  ONGLET RÉSERVATIONS
// ══════════════════════════════════════════
elseif ($tab === 'reservations'): ?>

<div class="topbar">
  <h1><i class="fas fa-calendar-check"></i> Gestion des Réservations</h1>
  <a href="../frontoffice/vehicules_disponibles.php" target="_blank" class="pill-user"><i class="fas fa-user"></i> Espace utilisateur</a>
  <span class="pill"><i class="fas fa-shield-alt"></i> Admin</span>
</div>

<div class="stats">
  <div class="stat"><i class="fas fa-calendar-alt"></i><div class="num"><?= $rStats['total'] ?></div><div class="lbl">Total</div></div>
  <div class="stat"><i class="fas fa-clock"></i><div class="num"><?= $rStats['en_attente'] ?></div><div class="lbl">En attente</div></div>
  <div class="stat"><i class="fas fa-check-circle"></i><div class="num"><?= $rStats['confirmees'] ?></div><div class="lbl">Confirmées</div></div>
  <div class="stat"><i class="fas fa-times-circle"></i><div class="num"><?= $rStats['annulees'] ?></div><div class="lbl">Annulées</div></div>
</div>

<div class="filters">
  <a href="?tab=reservations"            class="f <?= $rFilter===''          ?'on':'' ?>">Toutes</a>
  <a href="?tab=reservations&statut_r=en_attente"  class="f <?= $rFilter==='en_attente' ?'on':'' ?>">⏳ En attente</a>
  <a href="?tab=reservations&statut_r=confirmee"   class="f <?= $rFilter==='confirmee'  ?'on':'' ?>">✅ Confirmées</a>
  <a href="?tab=reservations&statut_r=annulee"     class="f <?= $rFilter==='annulee'    ?'on':'' ?>">❌ Annulées</a>
</div>

<div class="tbl-wrap">
  <table>
    <thead>
      <tr>
        <th>#</th><th>Passager</th><th>Véhicule</th><th>Immatriculation</th>
        <th>Date</th><th>Statut</th><th>Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php if (empty($reservations)): ?>
      <tr><td colspan="7"><div class="empty"><i class="fas fa-calendar-times"></i><p>Aucune réservation trouvée</p></div></td></tr>
    <?php else: foreach ($reservations as $r): ?>
      <tr>
        <td><?= $r['id'] ?></td>
        <td><?= htmlspecialchars(($r['passager_prenom'] ?? '') . ' ' . ($r['passager_nom'] ?? '')) ?></td>
        <td><strong><?= htmlspecialchars($r['marque'] ?? '—') ?></strong> <?= htmlspecialchars($r['modele'] ?? '') ?></td>
        <td><code><?= htmlspecialchars($r['immatriculation'] ?? '—') ?></code></td>
        <td><?= date('d/m/Y', strtotime($r['date_reservation'])) ?></td>
        <td>
          <form method="POST" style="margin:0;">
            <input type="hidden" name="action" value="resa_statut">
            <input type="hidden" name="id"     value="<?= $r['id'] ?>">
            <select name="statut" class="st-sel" onchange="this.form.submit()">
              <option value="en_attente" <?= $r['statut']==='en_attente'?'selected':'' ?>>⏳ En attente</option>
              <option value="confirmee"  <?= $r['statut']==='confirmee' ?'selected':'' ?>>✅ Confirmée</option>
              <option value="annulee"    <?= $r['statut']==='annulee'   ?'selected':'' ?>>❌ Annulée</option>
            </select>
          </form>
        </td>
        <td class="acts">
          <button class="ic ic-edit" title="Modifier la réservation"
            onclick="openEditReservation(<?= $r['id'] ?>, '<?= $r['date_reservation'] ?>', '<?= $r['statut'] ?>')">
            <i class="fas fa-pen"></i>
          </button>
          <form method="POST" style="margin:0;" onsubmit="return confirm('Supprimer cette réservation ?')">
            <input type="hidden" name="action" value="resa_delete">
            <input type="hidden" name="id" value="<?= $r['id'] ?>">
            <button type="submit" class="ic ic-del" title="Supprimer"><i class="fas fa-trash"></i></button>
          </form>
         </td>
      </tr>
    <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>

<!-- MODALE MODIFIER RÉSERVATION -->
<div class="overlay" id="editResaModal">
  <div class="modal">
    <h2><i class="fas fa-edit"></i> Modifier la réservation</h2>
    <form method="POST" id="editResaForm">
      <input type="hidden" name="action" value="resa_update">
      <input type="hidden" name="id" id="editResaId">
      
      <div class="fg">
        <label>Date de réservation</label>
        <input type="date" name="date_reservation" id="editResaDate" required>
      </div>
      
      <div class="fg">
        <label>Statut</label>
        <select name="statut" id="editResaStatut" class="st-sel" style="width:100%;">
          <option value="en_attente">⏳ En attente</option>
          <option value="confirmee">✅ Confirmée</option>
          <option value="annulee">❌ Annulée</option>
        </select>
      </div>
      
      <div class="mfooter">
        <button type="button" class="btn btn-outline" onclick="closeEditResaModal()">Annuler</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
      </div>
    </form>
  </div>
</div>

<?php // ══════════════════════════════════════
//  ONGLET HISTORIQUE
// ══════════════════════════════════════════
elseif ($tab === 'historique'): ?>

<div class="topbar">
  <h1><i class="fas fa-chart-line"></i> Historique Global</h1>
  <a href="../frontoffice/vehicules_disponibles.php" target="_blank" class="pill-user"><i class="fas fa-user"></i> Espace utilisateur</a>
  <span class="pill"><i class="fas fa-shield-alt"></i> Admin</span>
</div>

<div class="stats">
  <div class="stat"><i class="fas fa-calendar-alt"></i><div class="num"><?= $hStats['total'] ?? 0 ?></div><div class="lbl">Total</div></div>
  <div class="stat"><i class="fas fa-check-circle"></i><div class="num"><?= $hStats['confirmees'] ?? 0 ?></div><div class="lbl">Confirmées</div></div>
  <div class="stat"><i class="fas fa-times-circle"></i><div class="num"><?= $hStats['annulees'] ?? 0 ?></div><div class="lbl">Annulées</div></div>
  <div class="stat"><i class="fas fa-users"></i><div class="num"><?= $hStats['passagers'] ?? 0 ?></div><div class="lbl">Passagers</div></div>
  <div class="stat"><i class="fas fa-car"></i><div class="num"><?= $hStats['vehicules'] ?? 0 ?></div><div class="lbl">Véhicules actifs</div></div>
</div>

<form method="GET">
  <input type="hidden" name="tab" value="historique">
  <div class="date-row">
    <div class="filters" style="margin-bottom:0;">
      <button type="submit" name="statut_h" value=""          class="f <?= $hStatut===''          ?'on':'' ?>">Tous</button>
      <button type="submit" name="statut_h" value="en_attente"class="f <?= $hStatut==='en_attente'?'on':'' ?>">⏳ En attente</button>
      <button type="submit" name="statut_h" value="confirmee" class="f <?= $hStatut==='confirmee' ?'on':'' ?>">✅ Confirmées</button>
      <button type="submit" name="statut_h" value="annulee"   class="f <?= $hStatut==='annulee'   ?'on':'' ?>">❌ Annulées</button>
    </div>
    <label>Du</label>
    <input type="date" name="date_debut" value="<?= htmlspecialchars($hDateDebut) ?>">
    <label>Au</label>
    <input type="date" name="date_fin"   value="<?= htmlspecialchars($hDateFin) ?>">
    <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filtrer</button>
    <?php if ($hStatut || $hDateDebut || $hDateFin): ?>
      <a href="?tab=historique" class="btn btn-outline"><i class="fas fa-times"></i> Réinitialiser</a>
    <?php endif; ?>
  </div>
</form>

<div class="tbl-wrap">
  <table>
    <thead>
      <tr>
        <th>#</th><th>Passager</th><th>Véhicule</th><th>Immatriculation</th>
        <th>Date réservation</th><th>Statut</th>
      </tr>
    </thead>
    <tbody>
    <?php if (empty($historique)): ?>
      <tr><td colspan="6"><div class="empty"><i class="fas fa-history"></i><p>Aucun historique trouvé</p></div></td></tr>
    <?php else: foreach ($historique as $h): ?>
      <?php
        $bs = match($h['statut']) {
          'confirmee'  => 'b-conf',
          'annulee'    => 'b-annul',
          default      => 'b-attente'
        };
        $bl = match($h['statut']) {
          'confirmee'  => '✅ Confirmée',
          'annulee'    => '❌ Annulée',
          default      => '⏳ En attente'
        };
      ?>
      <tr>
        <td><?= $h['id'] ?></td>
        <td><?= htmlspecialchars(($h['passager_prenom'] ?? '') . ' ' . ($h['passager_nom'] ?? '')) ?></td>
        <td><strong><?= htmlspecialchars($h['marque'] ?? '—') ?></strong> <?= htmlspecialchars($h['modele'] ?? '') ?></td>
        <td><code><?= htmlspecialchars($h['immatriculation'] ?? '—') ?></code></td>
        <td><?= date('d/m/Y', strtotime($h['date_reservation'])) ?></td>
        <td><span class="badge <?= $bs ?>"><?= $bl ?></span></td>
      </tr>
    <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>

<?php endif; ?>

</main>
</div>

<script>
// Live search
const si = document.getElementById('searchInput');
if(si) si.addEventListener('input', function(){ clearTimeout(this._t); this._t=setTimeout(()=>this.form.submit(),450); });

// Auto-dismiss alerts
document.querySelectorAll('.alert').forEach(a => {
  setTimeout(()=>{ a.style.transition='opacity .5s'; a.style.opacity='0'; },4000);
  setTimeout(()=>a.remove(),4600);
});

// Modal modification réservation
const editResaModal = document.getElementById('editResaModal');

function openEditReservation(id, date, statut) {
    document.getElementById('editResaId').value = id;
    document.getElementById('editResaDate').value = date;
    document.getElementById('editResaStatut').value = statut;
    if(editResaModal) editResaModal.classList.add('open');
}

function closeEditResaModal() {
    if(editResaModal) editResaModal.classList.remove('open');
}

if(editResaModal) {
    editResaModal.addEventListener('click', function(e) {
        if(e.target === editResaModal) closeEditResaModal();
    });
}
</script>
</body>
</html>