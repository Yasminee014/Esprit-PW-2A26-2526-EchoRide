<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Réservations | EcoRide</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        :root { --bleu-fonce:#1976D2; --bleu-clair:#61B3FA; --gris:#A7A9AC; --dark-bg:#0A1628; }
        body { font-family:'Poppins','Segoe UI',sans-serif; background:linear-gradient(135deg,var(--dark-bg) 0%,#0D1F3A 100%); color:#fff; min-height:100vh; }
        .navbar { background:linear-gradient(90deg,var(--bleu-fonce),#0F3B6E); padding:1rem 2rem; display:flex; justify-content:space-between; align-items:center; box-shadow:0 4px 20px rgba(0,0,0,.3); position:sticky; top:0; z-index:100; }
        .navbar .logo { display:flex; align-items:center; gap:10px; font-size:1.3rem; font-weight:700; color:#fff; text-decoration:none; }
        .navbar .logo i { color:var(--bleu-clair); }
        .navbar nav a { color:#fff !important; text-decoration:none !important; padding:.5rem 1.2rem; border-radius:25px; font-size:.88rem; font-weight:500; transition:all .3s; border:1px solid rgba(97,179,250,.35); background:rgba(255,255,255,.08); display:inline-flex; align-items:center; gap:8px; margin:0 2px; }
        .navbar nav a:hover { background:rgba(25,118,210,.3) !important; border-color:#61B3FA !important; }
        .navbar nav a.active { background:rgba(255,255,255,.08) !important; border-color:rgba(97,179,250,.35) !important; color:#fff !important; }
        .navbar nav a.admin-nav { background:rgba(255,255,255,.08) !important; border-color:rgba(97,179,250,.35) !important; color:#fff !important; }
        .navbar nav a.admin-nav:hover { background:rgba(25,118,210,.3) !important; border-color:#61B3FA !important; color:#61B3FA !important; }
        .container { max-width:1000px; margin:0 auto; padding:2rem; }
        .page-header { margin-bottom:1.5rem; }
        .page-header h1 { font-size:1.8rem; display:flex; align-items:center; gap:10px; }
        .page-header h1 i { color:var(--bleu-clair); }
        .filter-bar { display:flex; gap:.6rem; flex-wrap:wrap; margin-bottom:1.8rem; background:rgba(255,255,255,.05); border:1px solid rgba(97,179,250,.15); border-radius:16px; padding:.8rem 1rem; }
        .filter-btn { display:inline-flex; align-items:center; gap:6px; padding:.45rem 1.1rem; border-radius:20px; font-size:.83rem; font-weight:600; cursor:pointer; border:1px solid transparent; transition:all .25s; background:rgba(255,255,255,.07); color:var(--gris); }
        .filter-btn:hover { background:rgba(97,179,250,.15); color:#fff; border-color:rgba(97,179,250,.4); }
        .filter-btn.active { background:rgba(25,118,210,.35); color:#fff; border-color:var(--bleu-clair); }
        .filter-btn .count { background:rgba(255,255,255,.15); border-radius:10px; padding:1px 7px; font-size:.75rem; }
        .filter-btn.f-attente.active   { background:rgba(241,196,15,.2);  border-color:#f1c40f; color:#f1c40f; }
        .filter-btn.f-confirmee.active { background:rgba(39,174,96,.2);   border-color:#27ae60; color:#27ae60; }
        .filter-btn.f-annulee.active   { background:rgba(231,76,60,.2);   border-color:#e74c3c; color:#e74c3c; }
        .section-title { display:flex; align-items:center; gap:10px; font-size:1rem; font-weight:600; color:var(--bleu-clair); margin:1.8rem 0 .8rem; padding-bottom:.5rem; border-bottom:1px solid rgba(97,179,250,.2); }
        .section-title .pill { background:rgba(97,179,250,.15); color:var(--bleu-clair); border-radius:12px; padding:1px 8px; font-size:.78rem; }
        .resa-list { display:flex; flex-direction:column; gap:1rem; }
        .resa-card { background:rgba(255,255,255,.07); border-radius:16px; border:1px solid rgba(97,179,250,.2); padding:1.2rem 1.5rem; transition:all .3s; }
        .resa-card:hover { border-color:rgba(97,179,250,.4); background:rgba(255,255,255,.09); }
        .resa-card.historique { opacity:.75; border-style:dashed; }
        .resa-card.historique:hover { opacity:1; }
        .resa-top { display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap; }
        .resa-info { display:flex; align-items:center; gap:1.2rem; }
        .resa-icon { width:48px; height:48px; border-radius:14px; background:rgba(25,118,210,.25); display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .resa-icon i { font-size:1.3rem; color:var(--bleu-clair); }
        .resa-icon.hist { background:rgba(167,169,172,.15); }
        .resa-icon.hist i { color:var(--gris); }
        .resa-details h3 { font-size:.98rem; margin-bottom:.3rem; }
        .resa-details p { font-size:.82rem; color:var(--gris); display:flex; align-items:center; gap:6px; margin-top:.2rem; }
        .resa-details p i { color:var(--bleu-clair); width:14px; }
        code { color:var(--bleu-clair); font-family:monospace; }
        .resa-right { display:flex; flex-direction:column; align-items:flex-end; gap:.6rem; }
        .badge { display:inline-block; padding:.25rem .8rem; border-radius:20px; font-size:.78rem; font-weight:600; }
        .badge-attente   { background:rgba(241,196,15,.2);  color:#f1c40f; border:1px solid rgba(241,196,15,.4); }
        .badge-confirmee { background:rgba(39,174,96,.2);   color:#27ae60; border:1px solid rgba(39,174,96,.4); }
        .badge-annulee   { background:rgba(231,76,60,.2);   color:#e74c3c; border:1px solid rgba(231,76,60,.4); }
        .crud-btns { display:flex; gap:.5rem; flex-wrap:wrap; justify-content:flex-end; margin-top:.6rem; }
        .btn-crud { display:inline-flex; align-items:center; gap:5px; padding:.4rem .9rem; border-radius:10px; font-size:.8rem; font-weight:600; cursor:pointer; border:1px solid transparent; transition:all .25s; }
        .btn-voir      { background:rgba(97,179,250,.15); border-color:rgba(97,179,250,.4); color:var(--bleu-clair); }
        .btn-voir:hover { background:rgba(97,179,250,.3); }
        .btn-modifier  { background:rgba(241,196,15,.15); border-color:rgba(241,196,15,.4); color:#f1c40f; }
        .btn-modifier:hover { background:rgba(241,196,15,.3); }
        .btn-annuler   { background:rgba(231,76,60,.15); border-color:rgba(231,76,60,.4); color:#e74c3c; }
        .btn-annuler:hover { background:rgba(231,76,60,.3); }
        .btn-supprimer { background:rgba(231,76,60,.15); border-color:rgba(231,76,60,.4); color:#e74c3c; }
        .btn-supprimer:hover { background:rgba(231,76,60,.3); }
        .empty-state { text-align:center; padding:4rem 2rem; background:rgba(255,255,255,.05); border-radius:20px; border:1px dashed rgba(97,179,250,.3); }
        .empty-state i { font-size:4rem; color:rgba(97,179,250,.3); margin-bottom:1rem; display:block; }
        .empty-state p { color:var(--gris); margin-bottom:1.5rem; }
        .btn-link { background:linear-gradient(135deg,var(--bleu-fonce),var(--bleu-clair)); color:#fff; border:none; padding:.8rem 1.5rem; border-radius:25px; font-size:.95rem; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:8px; transition:all .3s; }
        .btn-link:hover { transform:translateY(-2px); }
        .alert { padding:1rem 1.5rem; border-radius:14px; margin-bottom:1.5rem; display:flex; align-items:center; gap:10px; }
        .alert-success { background:rgba(39,174,96,.15); border:1px solid rgba(39,174,96,.4); color:#27ae60; }
        .alert-error   { background:rgba(231,76,60,.15);  border:1px solid rgba(231,76,60,.4);  color:#e74c3c; }
        .hidden { display:none !important; }
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.75); z-index:1000; align-items:center; justify-content:center; }
        .modal-overlay.open { display:flex; }
        .modal { background:linear-gradient(135deg,#0D1F3A,#1a2f50); border:1px solid rgba(97,179,250,.3); border-radius:24px; padding:2rem; width:480px; max-width:95vw; }
        .modal-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; padding-bottom:1rem; border-bottom:1px solid rgba(97,179,250,.2); }
        .modal-header h3 { font-size:1.1rem; color:var(--bleu-clair); display:flex; align-items:center; gap:8px; }
        .modal-close { background:none; border:none; color:var(--gris); font-size:1.3rem; cursor:pointer; }
        .modal-close:hover { color:#e74c3c; }
        .detail-row { display:flex; align-items:center; gap:10px; padding:.6rem 0; border-bottom:1px solid rgba(255,255,255,.06); font-size:.9rem; }
        .detail-row:last-child { border-bottom:none; }
        .detail-row i { color:var(--bleu-clair); width:18px; }
        .detail-row .label { color:var(--gris); min-width:130px; }
        .detail-row .val { font-weight:600; }
        .form-group { display:flex; flex-direction:column; gap:.5rem; margin-bottom:1rem; }
        .form-group label { font-size:.85rem; color:var(--bleu-clair); display:flex; align-items:center; gap:6px; }
        .form-group input { background:rgba(255,255,255,.08); border:1px solid rgba(97,179,250,.3); color:#fff; padding:.7rem 1rem; border-radius:12px; font-size:.9rem; outline:none; transition:all .3s; }
        .form-group input:focus { border-color:var(--bleu-clair); }
        .modal-buttons { display:flex; gap:1rem; margin-top:1.5rem; justify-content:flex-end; }
        .btn-save   { background:linear-gradient(135deg,var(--bleu-fonce),var(--bleu-clair)); color:#fff; border:none; padding:.8rem 2rem; border-radius:12px; font-size:.9rem; cursor:pointer; display:flex; align-items:center; gap:8px; }
        .btn-save:hover { transform:translateY(-2px); }
        .btn-cancel { background:rgba(255,255,255,.1); color:var(--gris); border:1px solid rgba(255,255,255,.2); padding:.8rem 1.5rem; border-radius:12px; font-size:.9rem; cursor:pointer; display:flex; align-items:center; gap:8px; }
        .btn-cancel:hover { background:rgba(231,76,60,.2); color:#e74c3c; }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="../index.php" class="logo"><i class="fas fa-leaf"></i> EcoRide</a>
    <nav>
        <a href="vehicules_disponibles.php"><i class="fas fa-car"></i> Covoiturages</a>
        <a href="mes_reservations.php" class="active"><i class="fas fa-calendar-check"></i> Réservations</a>
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
        <h1><i class="fas fa-history"></i> Mes Réservations & Historique</h1>
    </div>

    <?php if (empty($reservations)): ?>
        <div class="empty-state">
            <i class="fas fa-calendar-times"></i>
            <p>Vous n'avez aucune réservation pour le moment.</p>
            <a href="vehicules_disponibles.php" class="btn-link"><i class="fas fa-search"></i> Trouver un véhicule</a>
        </div>
    <?php else: ?>

    <?php
        $today = date('Y-m-d');
        $actives = $historique = [];
        $nb_attente = $nb_confirmee = $nb_annulee = 0;
        foreach ($reservations as $r) {
            if ($r['statut'] === 'en_attente') $nb_attente++;
            if ($r['statut'] === 'confirmee')  $nb_confirmee++;
            if ($r['statut'] === 'annulee')    $nb_annulee++;
            if ($r['statut'] === 'annulee' || $r['date_reservation'] < $today)
                $historique[] = $r;
            else
                $actives[] = $r;
        }
    ?>

    <div class="filter-bar">
        <button class="filter-btn active" onclick="filtrer('all',this)">
            <i class="fas fa-list"></i> Toutes <span class="count"><?= count($reservations) ?></span>
        </button>
        <button class="filter-btn f-attente" onclick="filtrer('en_attente',this)">
            <i class="fas fa-clock"></i> En attente <span class="count"><?= $nb_attente ?></span>
        </button>
        <button class="filter-btn f-confirmee" onclick="filtrer('confirmee',this)">
            <i class="fas fa-check-circle"></i> Confirmées <span class="count"><?= $nb_confirmee ?></span>
        </button>
        <button class="filter-btn f-annulee" onclick="filtrer('annulee',this)">
            <i class="fas fa-times-circle"></i> Annulées <span class="count"><?= $nb_annulee ?></span>
        </button>
    </div>

    <?php if (!empty($actives)): ?>
    <div class="section-title" id="section-actives">
        <i class="fas fa-calendar-check"></i> Réservations actives
        <span class="pill"><?= count($actives) ?></span>
    </div>
    <div class="resa-list" id="liste-actives">
    <?php foreach ($actives as $r):
        $statut      = $r['statut'];
        $badgeClass  = $statut === 'confirmee' ? 'badge-confirmee' : 'badge-attente';
        $statutLabel = $statut === 'confirmee' ? '✅ Confirmée' : '⏳ En attente';
        $peutModifier = ($statut === 'en_attente');
    ?>
    <div class="resa-card" data-statut="<?= $statut ?>">
        <div class="resa-top">
            <div class="resa-info">
                <div class="resa-icon"><i class="fas fa-car"></i></div>
                <div class="resa-details">
                    <h3><?= htmlspecialchars($r['marque'] . ' ' . $r['modele']) ?></h3>
                    <p><i class="fas fa-id-card"></i> <code><?= htmlspecialchars($r['immatriculation']) ?></code></p>
                    <p><i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($r['date_reservation'])) ?></p>
                </div>
            </div>
            <div class="resa-right">
                <span class="badge <?= $badgeClass ?>"><?= $statutLabel ?></span>
            </div>
        </div>
        <div class="crud-btns">
            <button class="btn-crud btn-voir" onclick="ouvrirVoir('<?= addslashes($r['marque'].' '.$r['modele']) ?>','<?= addslashes($r['immatriculation']) ?>','<?= date('d/m/Y', strtotime($r['date_reservation'])) ?>','<?= $statutLabel ?>',<?= $r['id'] ?>)">
                <i class="fas fa-eye"></i> Voir
            </button>
            <?php if ($peutModifier): ?>
            <button class="btn-crud btn-modifier" onclick="ouvrirModifier(<?= $r['id'] ?>,'<?= $r['date_reservation'] ?>','<?= addslashes($r['marque'].' '.$r['modele']) ?>')">
                <i class="fas fa-edit"></i> Modifier
            </button>
            <form method="POST" style="margin:0" onsubmit="return confirm('Annuler cette réservation ?')">
                <input type="hidden" name="action" value="annuler">
                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                <button type="submit" class="btn-crud btn-annuler"><i class="fas fa-ban"></i> Annuler</button>
            </form>
    
            <form method="POST" style="margin:0" onsubmit="return confirm('Supprimer définitivement ?')">
                <input type="hidden" name="action" value="supprimer">
                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                <button type="submit" class="btn-crud btn-supprimer"><i class="fas fa-trash"></i> Supprimer</button>
            </form>
        </div>
    </div>
    <?php endif; ?>
    <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($historique)): ?>
    <div class="section-title" id="section-historique">
        <i class="fas fa-history"></i> Historique des véhicules utilisés
        <span class="pill"><?= count($historique) ?></span>
    </div>
    <div class="resa-list" id="liste-historique">
    <?php foreach ($historique as $r):
        $statut      = $r['statut'];
        $badgeClass  = match($statut) { 'confirmee'=>'badge-confirmee','annulee'=>'badge-annulee',default=>'badge-attente' };
        $statutLabel = match($statut) { 'confirmee'=>'✅ Effectuée','annulee'=>'❌ Annulée',default=>'⏳ En attente' };
    ?>
    <div class="resa-card historique" data-statut="<?= $statut ?>">
        <div class="resa-top">
            <div class="resa-info">
                <div class="resa-icon hist"><i class="fas fa-car-side"></i></div>
                <div class="resa-details">
                    <h3><?= htmlspecialchars($r['marque'] . ' ' . $r['modele']) ?></h3>
                    <p><i class="fas fa-id-card"></i> <code><?= htmlspecialchars($r['immatriculation']) ?></code></p>
                    <p><i class="fas fa-calendar-alt"></i> <?= date('d/m/Y', strtotime($r['date_reservation'])) ?></p>
                    <p style="font-size:.75rem;color:rgba(167,169,172,.5)"><i class="fas fa-archive"></i> Archivé</p>
                </div>
            </div>
            <div class="resa-right">
                <span class="badge <?= $badgeClass ?>"><?= $statutLabel ?></span>
            </div>
        </div>
        <div class="crud-btns">
            <button class="btn-crud btn-voir" onclick="ouvrirVoir('<?= addslashes($r['marque'].' '.$r['modele']) ?>','<?= addslashes($r['immatriculation']) ?>','<?= date('d/m/Y', strtotime($r['date_reservation'])) ?>','<?= $statutLabel ?>',<?= $r['id'] ?>)">
                <i class="fas fa-eye"></i> Voir détails
            </button>
            <form method="POST" style="margin:0" onsubmit="return confirm('Supprimer de l\'historique ?')">
                <input type="hidden" name="action" value="supprimer">
                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                <button type="submit" class="btn-crud btn-supprimer"><i class="fas fa-trash"></i> Supprimer</button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div id="empty-filtre" class="empty-state hidden" style="margin-top:1rem;">
        <i class="fas fa-filter"></i>
        <p>Aucune réservation pour ce filtre.</p>
    </div>

    <?php endif; ?>
</div>

<!-- MODAL VOIR -->
<div class="modal-overlay" id="modalVoir">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fas fa-eye"></i> Détails de la réservation</h3>
            <button class="modal-close" onclick="fermerModal('modalVoir')"><i class="fas fa-times"></i></button>
        </div>
        <div id="voirContenu"></div>
        <div class="modal-buttons">
            <button class="btn-cancel" onclick="fermerModal('modalVoir')"><i class="fas fa-times"></i> Fermer</button>
        </div>
    </div>
</div>

<!-- MODAL MODIFIER -->
<div class="modal-overlay" id="modalModifier">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fas fa-edit"></i> Modifier la réservation</h3>
            <button class="modal-close" onclick="fermerModal('modalModifier')"><i class="fas fa-times"></i></button>
        </div>
        <p style="color:var(--gris);font-size:.88rem;margin-bottom:1.2rem" id="modifierVehicule"></p>
        <form method="POST">
            <input type="hidden" name="action" value="modifier">
            <input type="hidden" name="id" id="modifierIdInput">
            <div class="form-group">
                <label><i class="fas fa-calendar-alt"></i> Nouvelle date de réservation</label>
                <input type="date" name="date_reservation" id="modifierDateInput" min="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="modal-buttons">
                <button type="button" class="btn-cancel" onclick="fermerModal('modalModifier')"><i class="fas fa-times"></i> Annuler</button>
                <button type="submit" class="btn-save"><i class="fas fa-check"></i> Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<script>
    function filtrer(statut, btn) {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        let visible = 0;
        document.querySelectorAll('.resa-card').forEach(c => {
            const show = statut === 'all' || c.dataset.statut === statut;
            c.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        ['actives','historique'].forEach(id => {
            const liste = document.getElementById('liste-' + id);
            const titre = document.getElementById('section-' + id);
            if (!liste) return;
            const any = [...liste.querySelectorAll('.resa-card')].some(c => c.style.display !== 'none');
            if (titre) titre.style.display = any ? '' : 'none';
            liste.style.display = any ? '' : 'none';
        });
        document.getElementById('empty-filtre').classList.toggle('hidden', visible > 0);
    }

    function fermerModal(id) { document.getElementById(id).classList.remove('open'); }
    window.addEventListener('click', e => {
        ['modalVoir','modalModifier'].forEach(id => {
            if (e.target === document.getElementById(id)) fermerModal(id);
        });
    });

    function ouvrirVoir(vehicule, immat, date, statut, id) {
        document.getElementById('voirContenu').innerHTML = `
            <div class="detail-row"><i class="fas fa-car"></i><span class="label">Véhicule</span><span class="val">${vehicule}</span></div>
            <div class="detail-row"><i class="fas fa-id-card"></i><span class="label">Immatriculation</span><span class="val"><code style="color:var(--bleu-clair)">${immat}</code></span></div>
            <div class="detail-row"><i class="fas fa-calendar"></i><span class="label">Date réservation</span><span class="val">${date}</span></div>
            <div class="detail-row"><i class="fas fa-info-circle"></i><span class="label">Statut</span><span class="val">${statut}</span></div>
            <div class="detail-row"><i class="fas fa-hashtag"></i><span class="label">N° réservation</span><span class="val">#${id}</span></div>
        `;
        document.getElementById('modalVoir').classList.add('open');
    }

    function ouvrirModifier(id, date, vehicule) {
        document.getElementById('modifierIdInput').value = id;
        document.getElementById('modifierDateInput').value = date;
        document.getElementById('modifierVehicule').innerHTML = '<i class="fas fa-car"></i> ' + vehicule;
        document.getElementById('modalModifier').classList.add('open');
    }

    document.querySelectorAll('.alert').forEach(a => {
        setTimeout(() => a.style.opacity = '0', 4000);
        setTimeout(() => a.remove(), 4500);
    });
</script>
</body>
</html>