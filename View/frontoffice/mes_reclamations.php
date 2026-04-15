<?php
$reclamations = $reclamations ?? [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Mes Réclamations | EcoRide</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Segoe UI',sans-serif;background:#0A1628;color:#fff;padding:2rem;}
.container{max-width:1200px;margin:0 auto;}
h1{color:#61B3FA;}
.alert{padding:1rem;border-radius:8px;margin-bottom:1rem;}
.alert-success{background:rgba(39,174,96,.2);border:1px solid #27ae60;color:#27ae60;}
.alert-error{background:rgba(231,76,60,.2);border:1px solid #e74c3c;color:#e74c3c;}
.layout{display:grid;grid-template-columns:1fr 380px;gap:2rem;}
@media(max-width:900px){.layout{grid-template-columns:1fr;}}
.rec-card{background:rgba(255,255,255,.08);border-radius:12px;padding:1rem;margin-bottom:1rem;}
.rec-title{font-weight:bold;font-size:1rem;}
.rec-meta{margin:.5rem 0;display:flex;gap:.5rem;flex-wrap:wrap;}
.rec-desc{color:#ccc;margin:.5rem 0;font-size:.85rem;}
.rec-reponse{margin-top:.5rem;padding:.5rem;background:rgba(97,179,250,.1);border-left:3px solid #61B3FA;}
.rec-footer{margin-top:.5rem;font-size:.7rem;color:#aaa;}
.badge{padding:.2rem .6rem;border-radius:20px;font-size:.7rem;}
.form-card{background:rgba(255,255,255,.08);border-radius:16px;padding:1.5rem;position:sticky;top:20px;}
.form-card h2{margin-bottom:1rem;font-size:1.1rem;}
.fg{margin-bottom:1rem;}
.fg label{display:block;margin-bottom:.3rem;color:#aaa;font-size:.8rem;}
.fg input,.fg select,.fg textarea{width:100%;padding:.6rem;border-radius:8px;border:1px solid #61B3FA;background:rgba(255,255,255,.1);color:#fff;}
.btn-submit{width:100%;background:linear-gradient(135deg,#1976D2,#61B3FA);border:none;border-radius:10px;padding:.8rem;color:#fff;font-weight:bold;cursor:pointer;margin-top:.5rem;}
.btn-submit:hover{transform:translateY(-2px);}
.btn-delete{background:#e74c3c;border:none;color:#fff;padding:5px 10px;border-radius:5px;cursor:pointer;}
.btn-switch{background:#1976D2;border:none;border-radius:25px;padding:.6rem 1.2rem;color:#fff;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:8px;}
.btn-switch:hover{background:#1565C0;}
.ferr{color:#e74c3c;font-size:.7rem;display:none;margin-top:.2rem;}
.ferr.show{display:block;}
footer{margin-top:2rem;text-align:center;color:#aaa;padding-top:1rem;border-top:1px solid rgba(255,255,255,.1);}
.header-flex{display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;}
</style>
</head>
<body>
<div class="container">

<div class="header-flex">
    <h1><i class="fas fa-exclamation-circle"></i> Mes Réclamations</h1>
    <a href="../index.php?switch=1" class="btn-switch">
        <i class="fas fa-shield-alt"></i> Mode Admin
    </a>
</div>

<?php if(!empty($msg)): ?>
<div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>
<?php if(!empty($err)): ?>
<div class="alert alert-error"><?= htmlspecialchars($err) ?></div>
<?php endif; ?>

<div class="layout">
    <div>
        <p style="margin-bottom:1rem"><?= count($reclamations) ?> réclamation(s) soumise(s)</p>
        <?php if(empty($reclamations)): ?>
            <div style="text-align:center;padding:2rem;background:rgba(255,255,255,.05);border-radius:12px">
                Aucune réclamation pour le moment.
            </div>
        <?php else: ?>
            <?php foreach($reclamations as $r): ?>
            <div class="rec-card">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                    <div class="rec-title"><?= htmlspecialchars($r['titre']) ?></div>
                    <form method="POST" onsubmit="return confirm('Supprimer cette réclamation ?')" style="margin:0">
                        <input type="hidden" name="action" value="delete_reclamation">
                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
                        <button type="submit" class="btn-delete">
                            <i class="fas fa-trash"></i> Supprimer
                        </button>
                    </form>
                </div>
                <div class="rec-meta">
                    <span class="badge" style="background:<?= $r['statut']=='en_attente'?'#f1c40f':($r['statut']=='en_cours'?'#3498db':($r['statut']=='resolue'?'#27ae60':'#e74c3c')) ?>;color:#fff">
                        <?= str_replace('_',' ',$r['statut']) ?>
                    </span>
                    <span class="badge"><?= $r['categorie'] ?></span>
                    <span class="badge"><?= $r['priorite'] ?></span>
                </div>
                <div class="rec-desc"><?= nl2br(htmlspecialchars($r['description'])) ?></div>
                <?php if(!empty($r['reponse_admin'])): ?>
                <div class="rec-reponse"><strong>Réponse :</strong> <?= nl2br(htmlspecialchars($r['reponse_admin'])) ?></div>
                <?php endif; ?>
                <div class="rec-footer"><?= date('d/m/Y H:i', strtotime($r['date_creation'])) ?></div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <div class="form-card">
        <h2><i class="fas fa-plus-circle"></i> Nouvelle réclamation</h2>
        <form method="POST" id="reclamationForm">
            <input type="hidden" name="action" value="create_reclamation">
            
            <div class="fg">
                <label>Titre *</label>
                <input type="text" name="titre" id="titre" value="<?= htmlspecialchars($formData['titre']??'') ?>">
                <div class="ferr" id="err_titre"></div>
            </div>
            
            <div class="fg">
                <label>Description *</label>
                <textarea name="description" id="description" rows="4"><?= htmlspecialchars($formData['description']??'') ?></textarea>
                <div class="ferr" id="err_description"></div>
            </div>
            
            <div class="fg">
                <label>Catégorie *</label>
                <select name="categorie" id="categorie">
                    <option value="">-- Choisir --</option>
                    <option value="technique" <?= ($formData['categorie']??'')=='technique'?'selected':'' ?>>Technique</option>
                    <option value="paiement" <?= ($formData['categorie']??'')=='paiement'?'selected':'' ?>>Paiement</option>
                    <option value="securite" <?= ($formData['categorie']??'')=='securite'?'selected':'' ?>>Sécurité</option>
                    <option value="autre" <?= ($formData['categorie']??'')=='autre'?'selected':'' ?>>Autre</option>
                </select>
                <div class="ferr" id="err_categorie"></div>
            </div>
            
            <div class="fg">
                <label>Priorité *</label>
                <select name="priorite" id="priorite">
                    <option value="">-- Choisir --</option>
                    <option value="faible" <?= ($formData['priorite']??'')=='faible'?'selected':'' ?>>Faible</option>
                    <option value="moyenne" <?= ($formData['priorite']??'')=='moyenne'?'selected':'' ?>>Moyenne</option>
                    <option value="elevee" <?= ($formData['priorite']??'')=='elevee'?'selected':'' ?>>Élevée</option>
                </select>
                <div class="ferr" id="err_priorite"></div>
            </div>
            
            <button type="submit" class="btn-submit" onclick="return validateForm()">
                <i class="fas fa-paper-plane"></i> Soumettre
            </button>
        </form>
    </div>
</div>

<footer>EcoRide - Covoiturage intelligent et écologique</footer>
</div>

<script>
function validateForm() {
    let ok = true;
    document.querySelectorAll('.ferr').forEach(e => { 
        e.textContent = ''; 
        e.classList.remove('show'); 
    });
    
    let titre = document.getElementById('titre').value.trim();
    if(titre.length < 3) {
        document.getElementById('err_titre').textContent = 'Le titre doit contenir au moins 3 caractères.';
        document.getElementById('err_titre').classList.add('show');
        ok = false;
    }
    
    let description = document.getElementById('description').value.trim();
    if(description.length < 10) {
        document.getElementById('err_description').textContent = 'La description doit contenir au moins 10 caractères.';
        document.getElementById('err_description').classList.add('show');
        ok = false;
    }
    
    let categorie = document.getElementById('categorie').value;
    if(!categorie) {
        document.getElementById('err_categorie').textContent = 'Veuillez choisir une catégorie.';
        document.getElementById('err_categorie').classList.add('show');
        ok = false;
    }
    
    let priorite = document.getElementById('priorite').value;
    if(!priorite) {
        document.getElementById('err_priorite').textContent = 'Veuillez choisir une priorité.';
        document.getElementById('err_priorite').classList.add('show');
        ok = false;
    }
    
    if(!ok) {
        let first = document.querySelector('.ferr.show');
        if(first) first.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    return ok;
}

document.querySelectorAll('.alert').forEach(a => {
    setTimeout(() => { a.style.opacity = '0'; }, 4000);
    setTimeout(() => a.remove(), 4500);
});
</script>
</body>
</html>