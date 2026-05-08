<?php
// View/backoffice/admin_reclamations.php
// Variables injectées par ReclamationController :
//   $reclamations (array), $stats (array), $msg (string|null), $err (string|null)
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Réclamations — EcoRide Admin</title>
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
.alert-error{background:rgba(231,76,60,.14);border:1px solid rgba(231,76,60,.35);color:var(--red);}

/* ── STATS ── */
.stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:1rem;margin-bottom:1.6rem;}
.stat{background:rgba(255,255,255,.07);border:1px solid rgba(97,179,250,.16);border-radius:14px;padding:1.2rem;text-align:center;transition:all .3s;cursor:pointer;}
.stat:hover{transform:translateY(-4px);border-color:var(--blue-light);box-shadow:0 8px 22px rgba(25,118,210,.18);}
.stat i{font-size:1.8rem;color:var(--blue-light);margin-bottom:.35rem;display:block;}
.stat .num{font-size:2rem;font-weight:700;background:linear-gradient(135deg,var(--blue-light),#fff);-webkit-background-clip:text;background-clip:text;color:transparent;}
.stat .lbl{color:var(--grey);font-size:.75rem;margin-top:.2rem;}

/* ── TOOLBAR ── */
.toolbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:.8rem;flex-wrap:wrap;gap:.7rem;}

/* Loupe stylée */
.search-box{position:relative;display:flex;align-items:center;flex:1;max-width:300px;}
.search-icon{position:absolute;left:0;top:50%;transform:translateY(-50%);width:36px;height:36px;background:linear-gradient(135deg,var(--blue),var(--blue-light));border-radius:50%;display:flex;align-items:center;justify-content:center;pointer-events:none;box-shadow:0 3px 10px rgba(25,118,210,.4);transition:box-shadow .25s;z-index:2;}
.search-icon i{color:#fff;font-size:.78rem;}
.search-box:focus-within .search-icon{box-shadow:0 3px 16px rgba(97,179,250,.65);}
.search-box input{width:100%;background:rgba(255,255,255,.08);border:1px solid rgba(97,179,250,.3);color:#fff;padding:.5rem .9rem .5rem 2.6rem;border-radius:18px;font-size:.84rem;outline:none;transition:all .25s;font-family:inherit;}
.search-box input::placeholder{color:var(--grey);}
.search-box input:focus{border-color:var(--blue-light);background:rgba(97,179,250,.08);}

/* Bouton Ajouter stylé */
.btn-add{display:inline-flex;align-items:center;gap:0;background:linear-gradient(135deg,var(--blue),var(--blue-light));color:#fff;border:none;border-radius:22px;padding:.38rem 1.1rem .38rem .38rem;font-size:.84rem;font-weight:600;cursor:pointer;text-decoration:none;transition:all .28s;box-shadow:0 3px 12px rgba(25,118,210,.35);font-family:inherit;}
.btn-add:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(25,118,210,.5);}
.btn-add-icon{width:30px;height:30px;background:rgba(255,255,255,.22);border-radius:50%;display:flex;align-items:center;justify-content:center;margin-right:8px;transition:all .28s;}
.btn-add:hover .btn-add-icon{background:rgba(255,255,255,.38);transform:rotate(90deg);}
.btn-add-icon i{font-size:.82rem;}

/* ── FILTERS ── */
.filters{display:flex;gap:.5rem;margin-bottom:1rem;flex-wrap:wrap;}
.f{background:rgba(255,255,255,.07);border:1px solid rgba(97,179,250,.2);color:var(--grey);padding:.35rem .85rem;border-radius:14px;font-size:.8rem;cursor:pointer;text-decoration:none;transition:all .25s;font-family:inherit;}
.f:hover,.f.on{background:rgba(25,118,210,.22);border-color:var(--blue-light);color:var(--blue-light);}

/* ── TABLE ── */
.tbl-wrap{background:rgba(255,255,255,.04);border-radius:14px;overflow:hidden;border:1px solid rgba(97,179,250,.1);overflow-x:auto;}
table{width:100%;border-collapse:collapse;min-width:900px;}
thead{background:rgba(25,118,210,.22);}
thead th{padding:.85rem 1rem;text-align:left;font-size:.75rem;text-transform:uppercase;letter-spacing:.7px;color:var(--blue-light);font-weight:600;white-space:nowrap;}
tbody tr{border-bottom:1px solid rgba(255,255,255,.04);transition:background .18s;}
tbody tr:last-child{border-bottom:none;}
tbody tr:hover{background:rgba(97,179,250,.05);}
tbody td{padding:.75rem 1rem;font-size:.86rem;vertical-align:middle;}

/* ── BADGES ── */
.badge{display:inline-flex;align-items:center;gap:4px;padding:.18rem .65rem;border-radius:11px;font-size:.72rem;font-weight:600;white-space:nowrap;}
.b-attente{background:rgba(241,196,15,.15);color:var(--yellow);border:1px solid rgba(241,196,15,.3);}
.b-cours  {background:rgba(97,179,250,.15);color:var(--blue-light);border:1px solid rgba(97,179,250,.3);}
.b-resolue{background:rgba(39,174,96,.15);color:var(--green);border:1px solid rgba(39,174,96,.3);}
.b-rejetee{background:rgba(231,76,60,.15);color:var(--red);border:1px solid rgba(231,76,60,.3);}
.b-faible {background:rgba(39,174,96,.12);color:var(--green);border:1px solid rgba(39,174,96,.25);}
.b-moyenne{background:rgba(241,196,15,.12);color:var(--yellow);border:1px solid rgba(241,196,15,.25);}
.b-elevee {background:rgba(231,76,60,.12);color:var(--red);border:1px solid rgba(231,76,60,.25);}
.b-cat    {background:rgba(97,179,250,.12);color:var(--blue-light);border:1px solid rgba(97,179,250,.25);}

/* ── SELECTS INLINE ── */
.st-sel{background:rgba(255,255,255,.08);border:1px solid rgba(97,179,250,.25);color:#fff;padding:.28rem .5rem;border-radius:7px;font-size:.79rem;cursor:pointer;outline:none;font-family:inherit;}
.st-sel option{background:#0D1F3A;}

/* ── ACTION ICONS ── */
.acts{display:flex;gap:5px;flex-wrap:nowrap;}
.ic{width:30px;height:30px;border:none;border-radius:7px;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:.8rem;transition:all .22s;}
.ic:hover{transform:scale(1.12);}
.ic-edit{background:rgba(25,118,210,.2);color:var(--blue-light);}
.ic-del {background:rgba(231,76,60,.18);color:var(--red);}
.ic-view{background:rgba(39,174,96,.18);color:var(--green);}

/* ── EMPTY ── */
.empty{text-align:center;padding:2.5rem;color:var(--grey);}
.empty i{font-size:2.5rem;color:rgba(97,179,250,.2);margin-bottom:.8rem;display:block;}

/* ── MODAL OVERLAY ── */
.overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);backdrop-filter:blur(4px);z-index:999;align-items:center;justify-content:center;}
.overlay.open{display:flex;}
.modal{background:linear-gradient(145deg,#0D1F3A,#122A4A);border:1px solid rgba(97,179,250,.22);border-radius:18px;padding:1.8rem;width:92%;max-width:560px;max-height:92vh;overflow-y:auto;animation:mIn .28s ease;}
@keyframes mIn{from{opacity:0;transform:translateY(-18px)}to{opacity:1;transform:translateY(0)}}
.modal h2{font-size:1.15rem;margin-bottom:1.3rem;display:flex;align-items:center;gap:9px;color:var(--white);}
.modal h2 i{color:var(--blue-light);}
.modal-close{background:none;border:none;color:var(--grey);font-size:1.2rem;cursor:pointer;float:right;margin-top:-2.5rem;}
.modal-close:hover{color:var(--red);}

/* Formulaire modal */
.fgrid{display:grid;grid-template-columns:1fr 1fr;gap:.9rem;}
.fg{margin-bottom:.9rem;}
.fg.full{grid-column:1/-1;}
.fg label{display:block;font-size:.75rem;color:var(--grey);margin-bottom:.32rem;text-transform:uppercase;letter-spacing:.5px;}
.fg input,.fg select,.fg textarea{width:100%;background:rgba(255,255,255,.07);border:1px solid rgba(97,179,250,.22);color:#fff;padding:.55rem .8rem;border-radius:9px;font-size:.86rem;font-family:inherit;outline:none;transition:all .22s;resize:vertical;}
.fg input:focus,.fg select:focus,.fg textarea:focus{border-color:var(--blue-light);background:rgba(97,179,250,.06);}
.fg input::placeholder,.fg textarea::placeholder{color:var(--grey);}
.fg select option{background:#0D1F3A;}
.ferr{color:var(--red);font-size:.74rem;margin-top:.22rem;display:none;}
.ferr.show{display:block;}
.mfooter{display:flex;justify-content:flex-end;gap:.7rem;margin-top:1.4rem;}

/* Boutons modal */
.btn{padding:.52rem 1.2rem;border-radius:14px;font-size:.84rem;font-family:inherit;cursor:pointer;border:none;display:inline-flex;align-items:center;gap:6px;transition:all .25s;font-weight:500;text-decoration:none;}
.btn-primary{background:var(--blue);color:#fff;}
.btn-primary:hover{background:#1565C0;transform:translateY(-1px);}
.btn-outline{background:rgba(255,255,255,.07);border:1px solid rgba(97,179,250,.25);color:#fff;}
.btn-outline:hover{background:rgba(25,118,210,.22);border-color:var(--blue-light);}
.btn-danger{background:rgba(231,76,60,.15);border:1px solid rgba(231,76,60,.3);color:var(--red);}
.btn-danger:hover{background:rgba(231,76,60,.3);}

/* Panneau détail */
.detail-panel{background:rgba(97,179,250,.04);border:1px solid rgba(97,179,250,.15);border-radius:12px;padding:1rem;margin-bottom:1rem;}
.detail-row{display:flex;justify-content:space-between;padding:.4rem 0;border-bottom:1px solid rgba(255,255,255,.04);font-size:.86rem;}
.detail-row:last-child{border-bottom:none;}
.detail-lbl{color:var(--grey);}
.detail-val{color:#fff;font-weight:500;}

/* ── FOOTER ── */
footer{margin-top:2rem;padding:1rem 0;text-align:center;color:var(--grey);border-top:1px solid rgba(97,179,250,.15);font-size:.8rem;}
footer i{color:var(--blue-light);}

/* ── SCROLLBAR ── */
::-webkit-scrollbar{width:5px;height:5px;}
::-webkit-scrollbar-track{background:rgba(255,255,255,.04);}
::-webkit-scrollbar-thumb{background:var(--blue);border-radius:10px;}
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
      <li><a href="admin.php?tab=vehicules"><i class="fas fa-car"></i> Véhicules</a></li>
      <li><a href="admin.php?tab=reservations"><i class="fas fa-calendar-check"></i> Réservations</a></li>
      <li><a href="admin_reclamations.php" class="active"><i class="fas fa-exclamation-circle"></i> Réclamations</a></li>
      <li><a href="admin.php?tab=historique"><i class="fas fa-chart-line"></i> Historique</a></li>
    </ul>
    <hr class="sidebar-sep">
    <ul>
      <li><a href="../frontoffice/mes_reclamations.php"><i class="fas fa-user"></i> Espace utilisateur</a></li>
      <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
    </ul>
  </nav>
</aside>

<!-- ══ MAIN ══ -->
<main class="main">

  <!-- Topbar -->
  <div class="topbar">
    <h1><i class="fas fa-exclamation-circle"></i> Gestion des Réclamations</h1>
    <div style="display:flex;gap:.6rem;align-items:center;">
      <a href="../frontoffice/mes_reclamations.php" class="pill-user"><i class="fas fa-user"></i> Espace utilisateur</a>
      <span class="pill"><i class="fas fa-shield-alt"></i> Admin</span>
    </div>
  </div>

  <!-- Alertes -->
  <?php if ($msg): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg) ?></div>
  <?php endif; ?>
  <?php if ($err): ?>
    <div class="alert alert-error"><i class="fas fa-times-circle"></i> <?= htmlspecialchars($err) ?></div>
  <?php endif; ?>

  <!-- Stats -->
  <div class="stats">
    <div class="stat">
      <i class="fas fa-inbox"></i>
      <div class="num"><?= $stats['total'] ?></div>
      <div class="lbl">Total</div>
    </div>
    <div class="stat" onclick="filterByStatut('en_attente')" style="cursor:pointer">
      <i class="fas fa-clock"></i>
      <div class="num"><?= $stats['en_attente'] ?></div>
      <div class="lbl">En attente</div>
    </div>
    <div class="stat" onclick="filterByStatut('en_cours')" style="cursor:pointer">
      <i class="fas fa-spinner"></i>
      <div class="num"><?= $stats['en_cours'] ?></div>
      <div class="lbl">En cours</div>
    </div>
    <div class="stat" onclick="filterByStatut('resolue')" style="cursor:pointer">
      <i class="fas fa-check-circle"></i>
      <div class="num"><?= $stats['resolue'] ?></div>
      <div class="lbl">Résolues</div>
    </div>
    <div class="stat" onclick="filterByStatut('rejetee')" style="cursor:pointer">
      <i class="fas fa-ban"></i>
      <div class="num"><?= $stats['rejetee'] ?></div>
      <div class="lbl">Rejetées</div>
    </div>
  </div>

  <!-- Toolbar -->
  <div class="toolbar">
    <form method="GET" style="display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;flex:1;" id="searchForm">
      <div class="search-box">
        <div class="search-icon"><i class="fas fa-search"></i></div>
        <input type="text" name="search" id="searchInput"
               placeholder="Titre, utilisateur…"
               value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
      </div>
      <select name="statut" class="f" onchange="document.getElementById('searchForm').submit()">
        <option value="">Tous statuts</option>
        <?php foreach (['en_attente'=>'En attente','en_cours'=>'En cours','resolue'=>'Résolue','rejetee'=>'Rejetée'] as $v=>$l): ?>
          <option value="<?= $v ?>" <?= ($_GET['statut']??'')===$v?'selected':'' ?>><?= $l ?></option>
        <?php endforeach; ?>
      </select>
      <select name="priorite" class="f" onchange="document.getElementById('searchForm').submit()">
        <option value="">Toutes priorités</option>
        <?php foreach (['faible'=>'Faible','moyenne'=>'Moyenne','elevee'=>'Élevée'] as $v=>$l): ?>
          <option value="<?= $v ?>" <?= ($_GET['priorite']??'')===$v?'selected':'' ?>><?= $l ?></option>
        <?php endforeach; ?>
      </select>
      <select name="categorie" class="f" onchange="document.getElementById('searchForm').submit()">
        <option value="">Toutes catégories</option>
        <?php foreach (['technique'=>'Technique','paiement'=>'Paiement','securite'=>'Sécurité','autre'=>'Autre'] as $v=>$l): ?>
          <option value="<?= $v ?>" <?= ($_GET['categorie']??'')===$v?'selected':'' ?>><?= $l ?></option>
        <?php endforeach; ?>
      </select>
    </form>
    <button class="btn-add" onclick="openModal('addModal')">
      <span class="btn-add-icon"><i class="fas fa-plus"></i></span>
      Nouvelle réclamation
    </button>
  </div>

  <!-- Table -->
  <div class="tbl-wrap">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Titre</th>
          <th>Utilisateur</th>
          <th>Catégorie</th>
          <th>Priorité</th>
          <th>Statut</th>
          <th>Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($reclamations)): ?>
          <tr><td colspan="8">
            <div class="empty">
              <i class="fas fa-folder-open"></i>
              <p>Aucune réclamation trouvée.</p>
            </div>
          </td></tr>
        <?php else: ?>
          <?php foreach ($reclamations as $r): ?>
          <tr>
            <td><code>#<?= $r['id'] ?></code></td>
            <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
              <?= htmlspecialchars($r['titre']) ?>
            </td>
            <td><?= htmlspecialchars($r['nom_utilisateur'] ?? '—') ?></td>
            <td><span class="badge b-cat"><?= htmlspecialchars(ucfirst($r['categorie'])) ?></span></td>
            <td>
              <?php
                $pc = ['faible'=>'b-faible','moyenne'=>'b-moyenne','elevee'=>'b-elevee'];
                $pl = ['faible'=>'Faible','moyenne'=>'Moyenne','elevee'=>'Élevée'];
                $prio = $r['priorite'];
              ?>
              <span class="badge <?= $pc[$prio] ?? '' ?>"><?= $pl[$prio] ?? $prio ?></span>
            </td>
            <td>
              <form method="POST" style="margin:0;">
                <input type="hidden" name="action" value="reclamation_statut">
                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                <select name="statut" class="st-sel" onchange="this.form.submit()">
                  <?php foreach (['en_attente'=>'⏳ En attente','en_cours'=>'🔄 En cours','resolue'=>'✅ Résolue','rejetee'=>'❌ Rejetée'] as $v=>$l): ?>
                    <option value="<?= $v ?>" <?= $r['statut']===$v?'selected':'' ?>><?= $l ?></option>
                  <?php endforeach; ?>
                </select>
              </form>
            </td>
            <td style="white-space:nowrap;color:var(--grey);font-size:.8rem;">
              <?= date('d/m/Y', strtotime($r['date_creation'])) ?>
            </td>
            <td>
              <div class="acts">
                <!-- Voir / Répondre -->
                <button class="ic ic-view" title="Voir / Répondre"
                  onclick="openDetail(
                    <?= $r['id'] ?>,
                    <?= htmlspecialchars(json_encode($r['titre']), ENT_QUOTES) ?>,
                    <?= htmlspecialchars(json_encode($r['description']), ENT_QUOTES) ?>,
                    <?= htmlspecialchars(json_encode($r['nom_utilisateur'] ?? ''), ENT_QUOTES) ?>,
                    <?= htmlspecialchars(json_encode($r['statut']), ENT_QUOTES) ?>,
                    <?= htmlspecialchars(json_encode($r['reponse_admin'] ?? ''), ENT_QUOTES) ?>
                  )">
                  <i class="fas fa-eye"></i>
                </button>
                <!-- Modifier -->
                <button class="ic ic-edit" title="Modifier"
                  onclick="openEdit(
                    <?= $r['id'] ?>,
                    <?= htmlspecialchars(json_encode($r['titre']), ENT_QUOTES) ?>,
                    <?= htmlspecialchars(json_encode($r['description']), ENT_QUOTES) ?>,
                    <?= htmlspecialchars(json_encode($r['categorie']), ENT_QUOTES) ?>,
                    <?= htmlspecialchars(json_encode($r['priorite']), ENT_QUOTES) ?>,
                    <?= htmlspecialchars(json_encode($r['statut']), ENT_QUOTES) ?>,
                    <?= htmlspecialchars(json_encode($r['reponse_admin'] ?? ''), ENT_QUOTES) ?>
                  )">
                  <i class="fas fa-pen"></i>
                </button>
                <!-- Supprimer -->
                <form method="POST" style="margin:0;" onsubmit="return confirmDelete()">
                  <input type="hidden" name="action" value="reclamation_delete">
                  <input type="hidden" name="id" value="<?= $r['id'] ?>">
                  <button type="submit" class="ic ic-del" title="Supprimer"><i class="fas fa-trash"></i></button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <footer>
    <p><i class="fas fa-leaf"></i> EcoRide — Administration · Gestion des réclamations</p>
  </footer>
</main>
</div>

<!-- ══ MODAL : AJOUTER ══ -->
<div class="overlay" id="addModal">
  <div class="modal">
    <h2><i class="fas fa-plus-circle"></i> Nouvelle réclamation
      <button class="modal-close" onclick="closeModal('addModal')"><i class="fas fa-times"></i></button>
    </h2>
    <form method="POST" id="addForm" novalidate>
      <input type="hidden" name="action" value="reclamation_update">
      <input type="hidden" name="id" value="0">
      <div class="fgrid">
        <div class="fg full">
          <label>Titre *</label>
          <input type="text" name="titre" id="add_titre" placeholder="Titre de la réclamation">
          <span class="ferr" id="add_err_titre"></span>
        </div>
        <div class="fg full">
          <label>Description *</label>
          <textarea name="description" id="add_desc" rows="4" placeholder="Décrivez le problème…"></textarea>
          <span class="ferr" id="add_err_desc"></span>
        </div>
        <div class="fg">
          <label>Catégorie *</label>
          <select name="categorie" id="add_cat">
            <option value="">— Choisir —</option>
            <option value="technique">Technique</option>
            <option value="paiement">Paiement</option>
            <option value="securite">Sécurité</option>
            <option value="autre">Autre</option>
          </select>
          <span class="ferr" id="add_err_cat"></span>
        </div>
        <div class="fg">
          <label>Priorité *</label>
          <select name="priorite" id="add_prio">
            <option value="">— Choisir —</option>
            <option value="faible">Faible</option>
            <option value="moyenne">Moyenne</option>
            <option value="elevee">Élevée</option>
          </select>
          <span class="ferr" id="add_err_prio"></span>
        </div>
        <div class="fg">
          <label>Statut</label>
          <select name="statut" id="add_statut">
            <option value="en_attente">En attente</option>
            <option value="en_cours">En cours</option>
            <option value="resolue">Résolue</option>
            <option value="rejetee">Rejetée</option>
          </select>
        </div>
        <div class="fg">
          <label>ID Utilisateur *</label>
          <input type="text" name="utilisateur_id" id="add_uid" placeholder="Ex: 3">
          <span class="ferr" id="add_err_uid"></span>
        </div>
        <div class="fg full">
          <label>Réponse admin</label>
          <textarea name="reponse_admin" id="add_reponse" rows="3" placeholder="Réponse éventuelle…"></textarea>
        </div>
      </div>
      <div class="mfooter">
        <button type="button" class="btn btn-outline" onclick="closeModal('addModal')"><i class="fas fa-times"></i> Annuler</button>
        <button type="submit" class="btn btn-primary" onclick="return validateAddForm()"><i class="fas fa-save"></i> Enregistrer</button>
      </div>
    </form>
  </div>
</div>

<!-- ══ MODAL : MODIFIER ══ -->
<div class="overlay" id="editModal">
  <div class="modal">
    <h2><i class="fas fa-pen"></i> Modifier la réclamation
      <button class="modal-close" onclick="closeModal('editModal')"><i class="fas fa-times"></i></button>
    </h2>
    <form method="POST" id="editForm" novalidate>
      <input type="hidden" name="action" value="reclamation_update">
      <input type="hidden" name="id" id="edit_id">
      <div class="fgrid">
        <div class="fg full">
          <label>Titre *</label>
          <input type="text" name="titre" id="edit_titre">
          <span class="ferr" id="edit_err_titre"></span>
        </div>
        <div class="fg full">
          <label>Description *</label>
          <textarea name="description" id="edit_desc" rows="4"></textarea>
          <span class="ferr" id="edit_err_desc"></span>
        </div>
        <div class="fg">
          <label>Catégorie *</label>
          <select name="categorie" id="edit_cat">
            <option value="technique">Technique</option>
            <option value="paiement">Paiement</option>
            <option value="securite">Sécurité</option>
            <option value="autre">Autre</option>
          </select>
        </div>
        <div class="fg">
          <label>Priorité *</label>
          <select name="priorite" id="edit_prio">
            <option value="faible">Faible</option>
            <option value="moyenne">Moyenne</option>
            <option value="elevee">Élevée</option>
          </select>
        </div>
        <div class="fg">
          <label>Statut</label>
          <select name="statut" id="edit_statut">
            <option value="en_attente">En attente</option>
            <option value="en_cours">En cours</option>
            <option value="resolue">Résolue</option>
            <option value="rejetee">Rejetée</option>
          </select>
        </div>
        <div class="fg full">
          <label>Réponse admin</label>
          <textarea name="reponse_admin" id="edit_reponse" rows="3"></textarea>
          <span class="ferr" id="edit_err_reponse"></span>
        </div>
      </div>
      <div class="mfooter">
        <button type="button" class="btn btn-outline" onclick="closeModal('editModal')"><i class="fas fa-times"></i> Annuler</button>
        <button type="submit" class="btn btn-primary" onclick="return validateEditForm()"><i class="fas fa-save"></i> Enregistrer</button>
      </div>
    </form>
  </div>
</div>

<!-- ══ MODAL : DÉTAIL / RÉPONDRE ══ -->
<div class="overlay" id="detailModal">
  <div class="modal">
    <h2><i class="fas fa-eye"></i> Détail de la réclamation
      <button class="modal-close" onclick="closeModal('detailModal')"><i class="fas fa-times"></i></button>
    </h2>
    <div class="detail-panel" id="detailContent"></div>
    <form method="POST" id="reponseForm" novalidate>
      <input type="hidden" name="action" value="reclamation_reponse">
      <input type="hidden" name="id" id="detail_id">
      <div class="fg">
        <label>Réponse admin *</label>
        <textarea name="reponse_admin" id="detail_reponse" rows="4" placeholder="Saisissez votre réponse…"></textarea>
        <span class="ferr" id="detail_err_rep"></span>
      </div>
      <div class="fg">
        <label>Changer le statut</label>
        <select name="statut" id="detail_statut" class="st-sel" style="width:100%;border-radius:9px;padding:.55rem .8rem;">
          <option value="en_attente">En attente</option>
          <option value="en_cours">En cours</option>
          <option value="resolue">Résolue</option>
          <option value="rejetee">Rejetée</option>
        </select>
      </div>
      <div class="mfooter">
        <button type="button" class="btn btn-outline" onclick="closeModal('detailModal')"><i class="fas fa-times"></i> Fermer</button>
        <button type="submit" class="btn btn-primary" onclick="return validateReponseForm()"><i class="fas fa-reply"></i> Envoyer la réponse</button>
      </div>
    </form>
  </div>
</div>

<script>
/* ══ Modaux ══ */
function openModal(id) {
    document.getElementById(id).classList.add('open');
}
function closeModal(id) {
    document.getElementById(id).classList.remove('open');
    clearErrors();
}
document.querySelectorAll('.overlay').forEach(o => {
    o.addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('open');
    });
});

/* ══ Ouvrir modale Modifier ══ */
function openEdit(id, titre, desc, cat, prio, statut, reponse) {
    document.getElementById('edit_id').value      = id;
    document.getElementById('edit_titre').value   = titre;
    document.getElementById('edit_desc').value    = desc;
    document.getElementById('edit_cat').value     = cat;
    document.getElementById('edit_prio').value    = prio;
    document.getElementById('edit_statut').value  = statut;
    document.getElementById('edit_reponse').value = reponse;
    openModal('editModal');
}

/* ══ Ouvrir modale Détail ══ */
function openDetail(id, titre, desc, user, statut, reponse) {
    document.getElementById('detail_id').value      = id;
    document.getElementById('detail_reponse').value = reponse;
    document.getElementById('detail_statut').value  = statut;

    const labelsStatut = {
        en_attente: '<span class="badge b-attente">⏳ En attente</span>',
        en_cours:   '<span class="badge b-cours">🔄 En cours</span>',
        resolue:    '<span class="badge b-resolue">✅ Résolue</span>',
        rejetee:    '<span class="badge b-rejetee">❌ Rejetée</span>',
    };

    document.getElementById('detailContent').innerHTML = `
        <div class="detail-row"><span class="detail-lbl">ID</span><span class="detail-val">#${id}</span></div>
        <div class="detail-row"><span class="detail-lbl">Titre</span><span class="detail-val">${titre}</span></div>
        <div class="detail-row"><span class="detail-lbl">Utilisateur</span><span class="detail-val">${user || '—'}</span></div>
        <div class="detail-row"><span class="detail-lbl">Statut</span><span class="detail-val">${labelsStatut[statut] || statut}</span></div>
        <div class="detail-row"><span class="detail-lbl">Description</span><span class="detail-val" style="max-width:320px;">${desc}</span></div>
        ${reponse ? `<div class="detail-row"><span class="detail-lbl">Réponse admin</span><span class="detail-val" style="color:var(--blue-light);">${reponse}</span></div>` : ''}
    `;
    openModal('detailModal');
}

/* ══ Filtrer par statut (clic sur stat card) ══ */
function filterByStatut(statut) {
    const url = new URL(window.location.href);
    url.searchParams.set('statut', statut);
    window.location.href = url.toString();
}

/* ══ Live search ══ */
const si = document.getElementById('searchInput');
if (si) si.addEventListener('input', function() {
    clearTimeout(this._t);
    this._t = setTimeout(() => document.getElementById('searchForm').submit(), 450);
});

/* ══ Auto-dismiss alerts ══ */
document.querySelectorAll('.alert').forEach(a => {
    setTimeout(() => { a.style.transition = 'opacity .5s'; a.style.opacity = '0'; }, 4000);
    setTimeout(() => a.remove(), 4600);
});

/* ══ Confirmation suppression ══ */
function confirmDelete() {
    return confirm('Supprimer cette réclamation ? Cette action est irréversible.');
}

/* ══ VALIDATION JS — sans HTML5 ══ */
function clearErrors() {
    document.querySelectorAll('.ferr').forEach(e => {
        e.textContent = '';
        e.classList.remove('show');
    });
}

function showError(id, msg) {
    const el = document.getElementById(id);
    if (el) { el.textContent = msg; el.classList.add('show'); }
}

function validateAddForm() {
    clearErrors();
    let ok = true;

    const titre = document.getElementById('add_titre').value.trim();
    if (titre.length < 3) {
        showError('add_err_titre', 'Le titre doit contenir au moins 3 caractères.');
        ok = false;
    } else if (titre.length > 150) {
        showError('add_err_titre', 'Le titre ne peut pas dépasser 150 caractères.');
        ok = false;
    }

    const desc = document.getElementById('add_desc').value.trim();
    if (desc.length < 10) {
        showError('add_err_desc', 'La description doit contenir au moins 10 caractères.');
        ok = false;
    }

    const cat = document.getElementById('add_cat').value;
    if (!cat) {
        showError('add_err_cat', 'Veuillez choisir une catégorie.');
        ok = false;
    }

    const prio = document.getElementById('add_prio').value;
    if (!prio) {
        showError('add_err_prio', 'Veuillez choisir une priorité.');
        ok = false;
    }

    const uid = document.getElementById('add_uid').value.trim();
    if (!/^\d+$/.test(uid) || parseInt(uid) <= 0) {
        showError('add_err_uid', 'L\'ID utilisateur doit être un entier positif.');
        ok = false;
    }

    return ok;
}

function validateEditForm() {
    clearErrors();
    let ok = true;

    const titre = document.getElementById('edit_titre').value.trim();
    if (titre.length < 3) {
        showError('edit_err_titre', 'Le titre doit contenir au moins 3 caractères.');
        ok = false;
    }

    const desc = document.getElementById('edit_desc').value.trim();
    if (desc.length < 10) {
        showError('edit_err_desc', 'La description doit contenir au moins 10 caractères.');
        ok = false;
    }

    return ok;
}

function validateReponseForm() {
    clearErrors();
    const rep = document.getElementById('detail_reponse').value.trim();
    if (rep.length < 5) {
        showError('detail_err_rep', 'La réponse doit contenir au moins 5 caractères.');
        return false;
    }
    return true;
}
</script>
</body>
</html>
