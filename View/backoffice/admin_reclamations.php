<?php
$reclamations = $reclamations ?? [];
$stats = $stats ?? ['total' => 0, 'en_attente' => 0, 'en_cours' => 0, 'resolue' => 0, 'rejetee' => 0];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Admin - Réclamations | EcoRide</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Segoe UI',sans-serif;background:#0A1628;color:#fff;padding:2rem;}
h1{color:#61B3FA;}
.stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:1rem;margin:1.5rem 0;}
.stat{background:rgba(255,255,255,.08);border-radius:12px;padding:1rem;text-align:center;}
.stat .num{font-size:1.8rem;font-weight:bold;color:#61B3FA;}
.toolbar{display:flex;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:.5rem;}
.search-box{position:relative;}
.search-box input{padding:.6rem 1rem .6rem 2.3rem;border-radius:25px;border:1px solid #61B3FA;background:rgba(255,255,255,.1);color:#fff;width:220px;}
.search-box i{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#61B3FA;}
select{padding:.5rem;border-radius:20px;background:rgba(255,255,255,.1);border:1px solid #61B3FA;color:#fff;}
.btn-add{background:linear-gradient(135deg,#1976D2,#61B3FA);border:none;border-radius:25px;padding:.6rem 1.2rem;color:#fff;cursor:pointer;}
.btn-switch{background:#f39c12;border:none;border-radius:25px;padding:.6rem 1.2rem;color:#fff;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:8px;}
.btn-switch:hover{background:#e67e22;}
table{width:100%;border-collapse:collapse;background:rgba(255,255,255,.05);border-radius:12px;overflow:hidden;}
th,td{padding:.8rem;text-align:left;border-bottom:1px solid rgba(255,255,255,.1);}
th{background:rgba(25,118,210,.3);}
.badge{padding:.2rem .6rem;border-radius:20px;font-size:.7rem;}
.actions button{background:none;border:none;color:#fff;cursor:pointer;padding:5px;}
.st-sel{background:rgba(255,255,255,.1);border:1px solid #61B3FA;color:#fff;padding:.2rem .5rem;border-radius:12px;}
.alert{padding:.8rem;border-radius:8px;margin-bottom:1rem;}
.alert-success{background:rgba(39,174,96,.2);border:1px solid #27ae60;}
.alert-error{background:rgba(231,76,60,.2);border:1px solid #e74c3c;}
.modal{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.8);justify-content:center;align-items:center;}
.modal-content{background:#1a2a3a;padding:1.5rem;border-radius:16px;width:90%;max-width:500px;}
.form-group{margin-bottom:1rem;}
.form-group label{display:block;margin-bottom:.3rem;}
.form-group input,.form-group select,.form-group textarea{width:100%;padding:.6rem;border-radius:8px;border:1px solid #61B3FA;background:rgba(255,255,255,.1);color:#fff;}
.modal-buttons{display:flex;justify-content:flex-end;gap:.5rem;margin-top:1rem;}
.btn-save{background:#27ae60;border:none;padding:.5rem 1rem;border-radius:8px;color:#fff;}
.btn-cancel{background:#e74c3c;border:none;padding:.5rem 1rem;border-radius:8px;color:#fff;}
footer{margin-top:2rem;text-align:center;color:#aaa;}
.header-flex{display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;}
</style>
</head>
<body>

<div class="header-flex">
    <h1><i class="fas fa-shield-alt"></i> Administration - Réclamations</h1>
    <a href="../index.php?switch=1" class="btn-switch">
        <i class="fas fa-user"></i> Mode Utilisateur
    </a>
</div>

<?php if(!empty($msg)): ?>
<div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>
<?php if(!empty($err)): ?>
<div class="alert alert-error"><?= htmlspecialchars($err) ?></div>
<?php endif; ?>

<div class="stats">
    <div class="stat"><div class="num"><?= $stats['total'] ?></div><div>Total</div></div>
    <div class="stat"><div class="num"><?= $stats['en_attente'] ?></div><div>En attente</div></div>
    <div class="stat"><div class="num"><?= $stats['en_cours'] ?></div><div>En cours</div></div>
    <div class="stat"><div class="num"><?= $stats['resolue'] ?></div><div>Résolues</div></div>
    <div class="stat"><div class="num"><?= $stats['rejetee'] ?></div><div>Rejetées</div></div>
</div>

<div class="toolbar">
    <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" id="searchInput" placeholder="Rechercher..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
    </div>
    <div>
        <select id="filterStatut" onchange="applyFilters()">
            <option value="">Tous statuts</option>
            <option value="en_attente" <?= ($_GET['statut']??'')=='en_attente'?'selected':'' ?>>En attente</option>
            <option value="en_cours" <?= ($_GET['statut']??'')=='en_cours'?'selected':'' ?>>En cours</option>
            <option value="resolue" <?= ($_GET['statut']??'')=='resolue'?'selected':'' ?>>Résolue</option>
            <option value="rejetee" <?= ($_GET['statut']??'')=='rejetee'?'selected':'' ?>>Rejetée</option>
        </select>
        <select id="filterPriorite" onchange="applyFilters()">
            <option value="">Toutes priorités</option>
            <option value="faible" <?= ($_GET['priorite']??'')=='faible'?'selected':'' ?>>Faible</option>
            <option value="moyenne" <?= ($_GET['priorite']??'')=='moyenne'?'selected':'' ?>>Moyenne</option>
            <option value="elevee" <?= ($_GET['priorite']??'')=='elevee'?'selected':'' ?>>Élevée</option>
        </select>
        <select id="filterCategorie" onchange="applyFilters()">
            <option value="">Toutes catégories</option>
            <option value="technique" <?= ($_GET['categorie']??'')=='technique'?'selected':'' ?>>Technique</option>
            <option value="paiement" <?= ($_GET['categorie']??'')=='paiement'?'selected':'' ?>>Paiement</option>
            <option value="securite" <?= ($_GET['categorie']??'')=='securite'?'selected':'' ?>>Sécurité</option>
            <option value="autre" <?= ($_GET['categorie']??'')=='autre'?'selected':'' ?>>Autre</option>
        </select>
    </div>
    <button class="btn-add" onclick="openAddModal()"><i class="fas fa-plus"></i> Nouvelle</button>
</div>

<table>
    <thead>
        <tr><th>ID</th><th>Titre</th><th>Utilisateur</th><th>Catégorie</th><th>Priorité</th><th>Statut</th><th>Date</th><th>Actions</th></tr>
    </thead>
    <tbody>
        <?php if(empty($reclamations)): ?>
        <tr><td colspan="8" style="text-align:center">Aucune réclamation</td></tr>
        <?php else: ?>
        <?php foreach($reclamations as $r): ?>
        <tr>
            <td>#<?= $r['id'] ?></td>
            <td><?= htmlspecialchars($r['titre']) ?></td>
            <td><?= htmlspecialchars($r['utilisateur_nom'] ?? 'ID:'.$r['utilisateur_id']) ?></td>
            <td><?= $r['categorie'] ?></td>
            <td><span class="badge"><?= $r['priorite'] ?></span></td>
            <td>
                <form method="POST" style="margin:0">
                    <input type="hidden" name="action" value="reclamation_statut">
                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                    <select name="statut" class="st-sel" onchange="this.form.submit()">
                        <option value="en_attente" <?= $r['statut']=='en_attente'?'selected':'' ?>>En attente</option>
                        <option value="en_cours" <?= $r['statut']=='en_cours'?'selected':'' ?>>En cours</option>
                        <option value="resolue" <?= $r['statut']=='resolue'?'selected':'' ?>>Résolue</option>
                        <option value="rejetee" <?= $r['statut']=='rejetee'?'selected':'' ?>>Rejetée</option>
                    </select>
                </form>
            </td>
            <td><?= date('d/m/Y', strtotime($r['date_creation'])) ?></td>
            <td class="actions">
                <button onclick='editReclamation(<?= json_encode($r) ?>)'><i class="fas fa-edit"></i></button>
                <form method="POST" style="margin:0" onsubmit="return confirm('Supprimer ?')">
                    <input type="hidden" name="action" value="reclamation_delete">
                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                    <button type="submit"><i class="fas fa-trash"></i></button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<footer>EcoRide - Mode Administrateur</footer>

<div id="addModal" class="modal">
    <div class="modal-content">
        <h2>Ajouter une réclamation</h2>
        <form method="POST">
            <input type="hidden" name="action" value="create_reclamation">
            <div class="form-group"><label>Titre *</label><input type="text" name="titre" id="add_titre"></div>
            <div class="form-group"><label>Description *</label><textarea name="description" id="add_desc" rows="3"></textarea></div>
            <div class="form-group"><label>ID Utilisateur *</label><input type="number" name="utilisateur_id" id="add_uid"></div>
            <div class="form-group"><label>Catégorie</label><select name="categorie"><option value="technique">Technique</option><option value="paiement">Paiement</option><option value="securite">Sécurité</option><option value="autre">Autre</option></select></div>
            <div class="form-group"><label>Priorité</label><select name="priorite"><option value="faible">Faible</option><option value="moyenne">Moyenne</option><option value="elevee">Élevée</option></select></div>
            <div class="form-group"><label>Statut</label><select name="statut"><option value="en_attente">En attente</option><option value="en_cours">En cours</option><option value="resolue">Résolue</option><option value="rejetee">Rejetée</option></select></div>
            <div class="modal-buttons"><button type="button" class="btn-cancel" onclick="closeAddModal()">Annuler</button><button type="submit" class="btn-save">Enregistrer</button></div>
        </form>
    </div>
</div>

<div id="editModal" class="modal">
    <div class="modal-content">
        <h2>Modifier réclamation</h2>
        <form method="POST">
            <input type="hidden" name="action" value="reclamation_update">
            <input type="hidden" name="id" id="edit_id">
            <div class="form-group"><label>Titre *</label><input type="text" name="titre" id="edit_titre"></div>
            <div class="form-group"><label>Description *</label><textarea name="description" id="edit_desc" rows="3"></textarea></div>
            <div class="form-group"><label>Catégorie</label><select name="categorie" id="edit_cat"><option value="technique">Technique</option><option value="paiement">Paiement</option><option value="securite">Sécurité</option><option value="autre">Autre</option></select></div>
            <div class="form-group"><label>Priorité</label><select name="priorite" id="edit_prio"><option value="faible">Faible</option><option value="moyenne">Moyenne</option><option value="elevee">Élevée</option></select></div>
            <div class="form-group"><label>Statut</label><select name="statut" id="edit_statut"><option value="en_attente">En attente</option><option value="en_cours">En cours</option><option value="resolue">Résolue</option><option value="rejetee">Rejetée</option></select></div>
            <div class="modal-buttons"><button type="button" class="btn-cancel" onclick="closeEditModal()">Annuler</button><button type="submit" class="btn-save">Enregistrer</button></div>
        </form>
    </div>
</div>

<script>
function applyFilters() {
    let url = '../index.php?';
    let search = document.getElementById('searchInput').value;
    let statut = document.getElementById('filterStatut').value;
    let priorite = document.getElementById('filterPriorite').value;
    let categorie = document.getElementById('filterCategorie').value;
    if(search) url += 'search=' + encodeURIComponent(search) + '&';
    if(statut) url += 'statut=' + statut + '&';
    if(priorite) url += 'priorite=' + priorite + '&';
    if(categorie) url += 'categorie=' + categorie;
    window.location.href = url;
}
document.getElementById('searchInput').addEventListener('keyup', function(e){ if(e.key=='Enter') applyFilters(); });

function openAddModal() { document.getElementById('addModal').style.display = 'flex'; }
function closeAddModal() { document.getElementById('addModal').style.display = 'none'; }
function openEditModal() { document.getElementById('editModal').style.display = 'flex'; }
function closeEditModal() { document.getElementById('editModal').style.display = 'none'; }

function editReclamation(r) {
    document.getElementById('edit_id').value = r.id;
    document.getElementById('edit_titre').value = r.titre;
    document.getElementById('edit_desc').value = r.description;
    document.getElementById('edit_cat').value = r.categorie;
    document.getElementById('edit_prio').value = r.priorite;
    document.getElementById('edit_statut').value = r.statut;
    openEditModal();
}

window.onclick = function(e) {
    if(e.target.classList.contains('modal')) e.target.style.display = 'none';
}
</script>
</body>
</html>