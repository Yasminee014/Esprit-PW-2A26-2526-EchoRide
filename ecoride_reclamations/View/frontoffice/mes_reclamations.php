<?php
// View/frontoffice/mes_reclamations.php
// Variables : $reclamations (array), $msg, $err, $formData (array), $formErrors (array)
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mes Réclamations | EcoRide</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
:root{--bleu-fonce:#1976D2;--bleu-clair:#61B3FA;--blanc:#F4F5F7;--gris:#A7A9AC;--dark-bg:#0A1628;--green:#27ae60;--red:#e74c3c;--yellow:#f1c40f;}
body{font-family:'Poppins','Segoe UI',sans-serif;background:linear-gradient(135deg,var(--dark-bg) 0%,#0D1F3A 100%);color:#fff;min-height:100vh;}

/* ── NAVBAR ── */
.navbar{background:linear-gradient(90deg,var(--bleu-fonce),#0F3B6E);padding:1rem 2rem;display:flex;justify-content:space-between;align-items:center;box-shadow:0 4px 20px rgba(0,0,0,.3);position:sticky;top:0;z-index:100;}
.navbar .logo{display:flex;align-items:center;gap:10px;font-size:1.3rem;font-weight:700;color:#fff;text-decoration:none;}
.navbar .logo i{color:var(--bleu-clair);}
.navbar nav a{color:#fff;text-decoration:none;padding:.45rem 1rem;border-radius:22px;font-size:.86rem;font-weight:500;transition:all .3s;border:1px solid rgba(97,179,250,.3);background:rgba(255,255,255,.07);display:inline-flex;align-items:center;gap:7px;margin:0 2px;}
.navbar nav a:hover,.navbar nav a.active{background:rgba(25,118,210,.3);border-color:#61B3FA;}
.navbar nav a.admin-nav:hover{color:#61B3FA;}

/* ── CONTAINER ── */
.container{max-width:1200px;margin:0 auto;padding:2rem;}

/* ── PAGE HEADER ── */
.page-header{margin-bottom:2rem;}
.page-header h1{font-size:1.8rem;display:flex;align-items:center;gap:10px;margin-bottom:.5rem;}
.page-header h1 i{color:var(--bleu-clair);}
.page-header p{color:var(--gris);}

/* ── ALERTS ── */
.alert{padding:1rem 1.5rem;border-radius:14px;margin-bottom:1.5rem;display:flex;align-items:center;gap:10px;font-size:.9rem;}
.alert-success{background:rgba(39,174,96,.14);border:1px solid rgba(39,174,96,.35);color:var(--green);}
.alert-error  {background:rgba(231,76,60,.14);border:1px solid rgba(231,76,60,.35);color:var(--red);}

/* ── LAYOUT 2 COLS ── */
.layout{display:grid;grid-template-columns:1fr 380px;gap:2rem;align-items:start;}
@media(max-width:900px){.layout{grid-template-columns:1fr;}}

/* ── RÉCLAMATIONS LISTE ── */
.recs-list{display:flex;flex-direction:column;gap:1rem;}
.rec-card{background:rgba(255,255,255,.07);border:1px solid rgba(97,179,250,.18);border-radius:16px;padding:1.3rem 1.5rem;transition:all .3s;}
.rec-card:hover{transform:translateY(-3px);border-color:var(--bleu-clair);box-shadow:0 8px 24px rgba(25,118,210,.18);}
.rec-header{display:flex;justify-content:space-between;align-items:flex-start;gap:.8rem;margin-bottom:.7rem;}
.rec-title{font-weight:600;font-size:.96rem;}
.rec-meta{display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:.6rem;}
.rec-desc{color:var(--gris);font-size:.85rem;line-height:1.55;}
.rec-footer{margin-top:.8rem;padding-top:.7rem;border-top:1px solid rgba(255,255,255,.06);font-size:.8rem;color:var(--gris);display:flex;justify-content:space-between;align-items:center;}
.rec-reponse{margin-top:.8rem;padding:.7rem 1rem;background:rgba(97,179,250,.07);border-left:3px solid var(--bleu-clair);border-radius:0 8px 8px 0;font-size:.85rem;color:#d8efff;}
.rec-reponse strong{display:block;color:var(--bleu-clair);margin-bottom:.3rem;font-size:.78rem;text-transform:uppercase;letter-spacing:.5px;}

/* ── BADGES ── */
.badge{display:inline-flex;align-items:center;gap:4px;padding:.18rem .65rem;border-radius:11px;font-size:.72rem;font-weight:600;}
.b-attente{background:rgba(241,196,15,.15);color:var(--yellow);border:1px solid rgba(241,196,15,.3);}
.b-cours  {background:rgba(97,179,250,.15);color:var(--bleu-clair);border:1px solid rgba(97,179,250,.3);}
.b-resolue{background:rgba(39,174,96,.15);color:var(--green);border:1px solid rgba(39,174,96,.3);}
.b-rejetee{background:rgba(231,76,60,.15);color:var(--red);border:1px solid rgba(231,76,60,.3);}
.b-faible {background:rgba(39,174,96,.12);color:var(--green);border:1px solid rgba(39,174,96,.25);}
.b-moyenne{background:rgba(241,196,15,.12);color:var(--yellow);border:1px solid rgba(241,196,15,.25);}
.b-elevee {background:rgba(231,76,60,.12);color:var(--red);border:1px solid rgba(231,76,60,.25);}
.b-cat    {background:rgba(97,179,250,.12);color:var(--bleu-clair);border:1px solid rgba(97,179,250,.25);}

/* ── FORMULAIRE STICKY ── */
.form-card{background:rgba(255,255,255,.06);border:1px solid rgba(97,179,250,.18);border-radius:20px;padding:1.8rem;position:sticky;top:90px;}
.form-card h2{font-size:1.1rem;margin-bottom:1.4rem;display:flex;align-items:center;gap:9px;color:var(--blanc);}
.form-card h2 i{color:var(--bleu-clair);}

.fg{margin-bottom:1rem;}
.fg label{display:block;font-size:.77rem;color:var(--gris);margin-bottom:.35rem;text-transform:uppercase;letter-spacing:.5px;}
.fg label .req{color:var(--red);}
.fg input,.fg select,.fg textarea{width:100%;background:rgba(255,255,255,.07);border:1px solid rgba(97,179,250,.22);color:#fff;padding:.6rem .9rem;border-radius:10px;font-size:.88rem;font-family:inherit;outline:none;transition:all .25s;resize:vertical;}
.fg input:focus,.fg select:focus,.fg textarea:focus{border-color:var(--bleu-clair);background:rgba(97,179,250,.07);}
.fg input::placeholder,.fg textarea::placeholder{color:var(--gris);}
.fg select option{background:#0D1F3A;}
.fg.has-error input,.fg.has-error select,.fg.has-error textarea{border-color:var(--red);}
.ferr{color:var(--red);font-size:.76rem;margin-top:.25rem;display:none;}
.ferr.show{display:block;}

/* Compteur caractères */
.char-count{font-size:.74rem;color:var(--gris);text-align:right;margin-top:.2rem;}
.char-count.warn{color:var(--yellow);}
.char-count.over{color:var(--red);}

/* Bouton soumettre stylé */
.btn-submit{width:100%;background:linear-gradient(135deg,var(--bleu-fonce),var(--bleu-clair));color:#fff;border:none;border-radius:14px;padding:.85rem;font-size:.95rem;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:all .3s;font-family:inherit;margin-top:.5rem;}
.btn-submit:hover{transform:translateY(-2px);box-shadow:0 8px 22px rgba(25,118,210,.4);}
.btn-submit:active{transform:translateY(0);}

/* ── EMPTY STATE ── */
.empty-state{text-align:center;padding:3rem 2rem;background:rgba(255,255,255,.04);border-radius:16px;border:1px dashed rgba(97,179,250,.2);}
.empty-state i{font-size:3.5rem;color:rgba(97,179,250,.25);margin-bottom:1rem;display:block;}
.empty-state p{color:var(--gris);}

/* ── FOOTER ── */
footer{margin-top:2rem;padding:1.5rem 0;text-align:center;color:var(--gris);border-top:1px solid rgba(97,179,250,.15);font-size:.82rem;}
footer i{color:var(--bleu-clair);}

/* Scrollbar */
::-webkit-scrollbar{width:5px;}
::-webkit-scrollbar-track{background:rgba(255,255,255,.04);}
::-webkit-scrollbar-thumb{background:var(--bleu-fonce);border-radius:10px;}
</style>
</head>
<body>

<!-- ══ NAVBAR ══ -->
<nav class="navbar">
  <a href="../index.php" class="logo"><i class="fas fa-leaf"></i> EcoRide</a>
  <nav>
    <a href="vehicules_disponibles.php"><i class="fas fa-car"></i> Covoiturages</a>
    <a href="mes_reservations.php"><i class="fas fa-calendar-check"></i> Réservations</a>
    <a href="mes_vehicules.php"><i class="fas fa-key"></i> Mes véhicules</a>
    <a href="mes_reclamations.php" class="active"><i class="fas fa-exclamation-circle"></i> Réclamations</a>
    <a href="mon_historique.php"><i class="fas fa-history"></i> Historique</a>
    <?php if (!empty($_SESSION['is_admin'])): ?>
      <a href="../backoffice/admin_reclamations.php" class="admin-nav"><i class="fas fa-shield-alt"></i> Admin</a>
    <?php endif; ?>
    <a href="logout.php"><i class="fas fa-sign-out-alt"></i></a>
  </nav>
</nav>

<div class="container">

  <!-- Header -->
  <div class="page-header">
    <h1><i class="fas fa-exclamation-circle"></i> Mes Réclamations</h1>
    <p><?= count($reclamations) ?> réclamation<?= count($reclamations) > 1 ? 's' : '' ?> soumise<?= count($reclamations) > 1 ? 's' : '' ?></p>
  </div>

  <!-- Alertes -->
  <?php if ($msg): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg) ?></div>
  <?php endif; ?>
  <?php if ($err): ?>
    <div class="alert alert-error"><i class="fas fa-times-circle"></i> <?= htmlspecialchars($err) ?></div>
  <?php endif; ?>

  <div class="layout">

    <!-- ── Liste réclamations ── -->
    <div class="recs-list">
      <?php if (empty($reclamations)): ?>
        <div class="empty-state">
          <i class="fas fa-folder-open"></i>
          <p>Vous n'avez encore soumis aucune réclamation.</p>
        </div>
      <?php else: ?>
        <?php
          $statutBadge = [
            'en_attente' => ['b-attente', '⏳ En attente'],
            'en_cours'   => ['b-cours',   '🔄 En cours'],
            'resolue'    => ['b-resolue', '✅ Résolue'],
            'rejetee'    => ['b-rejetee', '❌ Rejetée'],
          ];
          $prioBadge = [
            'faible'  => ['b-faible',  'Faible'],
            'moyenne' => ['b-moyenne', 'Moyenne'],
            'elevee'  => ['b-elevee',  'Élevée'],
          ];
        ?>
        <?php foreach ($reclamations as $r): ?>
          <?php
            [$sc, $sl] = $statutBadge[$r['statut']] ?? ['b-cat', $r['statut']];
            [$pc, $pl] = $prioBadge[$r['priorite']] ?? ['b-cat', $r['priorite']];
          ?>
          <div class="rec-card">
            <div class="rec-header">
              <div class="rec-title"><?= htmlspecialchars($r['titre']) ?></div>
              <span class="badge <?= $sc ?>"><?= $sl ?></span>
            </div>
            <div class="rec-meta">
              <span class="badge b-cat"><i class="fas fa-tag"></i> <?= htmlspecialchars(ucfirst($r['categorie'])) ?></span>
              <span class="badge <?= $pc ?>"><i class="fas fa-flag"></i> <?= $pl ?></span>
            </div>
            <div class="rec-desc"><?= nl2br(htmlspecialchars($r['description'])) ?></div>
            <?php if (!empty($r['reponse_admin'])): ?>
              <div class="rec-reponse">
                <strong><i class="fas fa-reply"></i> Réponse de l'équipe EcoRide</strong>
                <?= nl2br(htmlspecialchars($r['reponse_admin'])) ?>
              </div>
            <?php endif; ?>
            <div class="rec-footer">
              <span><i class="fas fa-calendar" style="color:var(--bleu-clair);margin-right:4px;"></i>
                <?= date('d/m/Y à H:i', strtotime($r['date_creation'])) ?>
              </span>
              <span>#<?= $r['id'] ?></span>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- ── Formulaire Nouvelle Réclamation ── -->
    <div class="form-card">
      <h2><i class="fas fa-plus-circle"></i> Nouvelle réclamation</h2>

      <form method="POST" id="reclamationForm" novalidate>
        <input type="hidden" name="action" value="create_reclamation">

        <!-- Titre -->
        <div class="fg <?= isset($formErrors['titre']) ? 'has-error' : '' ?>">
          <label>Titre <span class="req">*</span></label>
          <input type="text" name="titre" id="f_titre"
                 value="<?= htmlspecialchars($formData['titre'] ?? '') ?>"
                 placeholder="Résumez votre problème en quelques mots">
          <span class="ferr <?= isset($formErrors['titre']) ? 'show' : '' ?>" id="err_titre">
            <?= htmlspecialchars($formErrors['titre'] ?? '') ?>
          </span>
        </div>

        <!-- Description -->
        <div class="fg <?= isset($formErrors['description']) ? 'has-error' : '' ?>">
          <label>Description <span class="req">*</span></label>
          <textarea name="description" id="f_desc" rows="5"
                    placeholder="Décrivez votre problème en détail…"><?= htmlspecialchars($formData['description'] ?? '') ?></textarea>
          <div class="char-count" id="descCount">0 / 2000 caractères</div>
          <span class="ferr <?= isset($formErrors['description']) ? 'show' : '' ?>" id="err_desc">
            <?= htmlspecialchars($formErrors['description'] ?? '') ?>
          </span>
        </div>

        <!-- Catégorie -->
        <div class="fg <?= isset($formErrors['categorie']) ? 'has-error' : '' ?>">
          <label>Catégorie <span class="req">*</span></label>
          <select name="categorie" id="f_cat">
            <option value="">— Choisir —</option>
            <?php foreach (['technique'=>'Technique','paiement'=>'Paiement','securite'=>'Sécurité','autre'=>'Autre'] as $v=>$l): ?>
              <option value="<?= $v ?>" <?= ($formData['categorie'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
            <?php endforeach; ?>
          </select>
          <span class="ferr <?= isset($formErrors['categorie']) ? 'show' : '' ?>" id="err_cat">
            <?= htmlspecialchars($formErrors['categorie'] ?? '') ?>
          </span>
        </div>

        <!-- Priorité -->
        <div class="fg <?= isset($formErrors['priorite']) ? 'has-error' : '' ?>">
          <label>Priorité <span class="req">*</span></label>
          <select name="priorite" id="f_prio">
            <option value="">— Choisir —</option>
            <?php foreach (['faible'=>'Faible','moyenne'=>'Moyenne','elevee'=>'Élevée'] as $v=>$l): ?>
              <option value="<?= $v ?>" <?= ($formData['priorite'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
            <?php endforeach; ?>
          </select>
          <span class="ferr <?= isset($formErrors['priorite']) ? 'show' : '' ?>" id="err_prio">
            <?= htmlspecialchars($formErrors['priorite'] ?? '') ?>
          </span>
        </div>

        <button type="submit" class="btn-submit" onclick="return validateForm()">
          <i class="fas fa-paper-plane"></i> Soumettre la réclamation
        </button>
      </form>
    </div>

  </div><!-- /.layout -->

  <footer>
    <p><i class="fas fa-leaf"></i> EcoRide — Covoiturage intelligent et écologique</p>
  </footer>

</div><!-- /.container -->

<script>
/* ══ Compteur de caractères (description) ══ */
const descField = document.getElementById('f_desc');
const descCount = document.getElementById('descCount');
function updateCount() {
    const len = descField.value.length;
    descCount.textContent = len + ' / 2000 caractères';
    descCount.className = 'char-count' + (len > 1900 ? ' warn' : '') + (len > 2000 ? ' over' : '');
}
if (descField) { descField.addEventListener('input', updateCount); updateCount(); }

/* ══ Auto-dismiss alerts ══ */
document.querySelectorAll('.alert').forEach(a => {
    setTimeout(() => { a.style.transition = 'opacity .5s'; a.style.opacity = '0'; }, 5000);
    setTimeout(() => a.remove(), 5600);
});

/* ══ VALIDATION JS — sans HTML5 ══ */
function showError(id, msg) {
    const el = document.getElementById(id);
    if (el) { el.textContent = msg; el.classList.add('show'); }
}
function clearErrors() {
    document.querySelectorAll('.ferr').forEach(e => {
        e.textContent = '';
        e.classList.remove('show');
    });
    document.querySelectorAll('.has-error').forEach(e => e.classList.remove('has-error'));
}

function validateForm() {
    clearErrors();
    let ok = true;

    // Titre
    const titre = document.getElementById('f_titre').value.trim();
    if (titre.length < 3) {
        showError('err_titre', 'Le titre doit contenir au moins 3 caractères.');
        document.getElementById('f_titre').closest('.fg').classList.add('has-error');
        ok = false;
    } else if (titre.length > 150) {
        showError('err_titre', 'Le titre ne peut pas dépasser 150 caractères.');
        document.getElementById('f_titre').closest('.fg').classList.add('has-error');
        ok = false;
    }

    // Description
    const desc = document.getElementById('f_desc').value.trim();
    if (desc.length < 10) {
        showError('err_desc', 'La description doit contenir au moins 10 caractères.');
        document.getElementById('f_desc').closest('.fg').classList.add('has-error');
        ok = false;
    } else if (desc.length > 2000) {
        showError('err_desc', 'La description ne peut pas dépasser 2000 caractères.');
        document.getElementById('f_desc').closest('.fg').classList.add('has-error');
        ok = false;
    }

    // Catégorie
    const cat = document.getElementById('f_cat').value;
    const validCat = ['technique', 'paiement', 'securite', 'autre'];
    if (!cat || !validCat.includes(cat)) {
        showError('err_cat', 'Veuillez choisir une catégorie.');
        document.getElementById('f_cat').closest('.fg').classList.add('has-error');
        ok = false;
    }

    // Priorité
    const prio = document.getElementById('f_prio').value;
    const validPrio = ['faible', 'moyenne', 'elevee'];
    if (!prio || !validPrio.includes(prio)) {
        showError('err_prio', 'Veuillez choisir une priorité.');
        document.getElementById('f_prio').closest('.fg').classList.add('has-error');
        ok = false;
    }

    // Scroll vers la première erreur
    if (!ok) {
        const first = document.querySelector('.has-error');
        if (first) first.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    return ok;
}
</script>
</body>
</html>
