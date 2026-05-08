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

/* ── Stats ── */
.stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:1rem;margin:1.5rem 0;}
.stat{background:rgba(255,255,255,.08);border-radius:12px;padding:1rem;text-align:center;}
.stat .num{font-size:1.8rem;font-weight:bold;color:#61B3FA;}

/* ── Toolbar ── */
.toolbar{display:flex;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:.5rem;}
.search-box{position:relative;}
.search-box input{padding:.6rem 1rem .6rem 2.3rem;border-radius:25px;border:1px solid #61B3FA;background:rgba(255,255,255,.1);color:#fff;width:220px;}
.search-box i{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#61B3FA;}
select{padding:.5rem;border-radius:20px;background:rgba(255,255,255,.1);border:1px solid #61B3FA;color:#fff;}
.btn-add{background:linear-gradient(135deg,#1976D2,#61B3FA);border:none;border-radius:25px;padding:.6rem 1.2rem;color:#fff;cursor:pointer;}
.btn-switch{background:#f39c12;border:none;border-radius:25px;padding:.6rem 1.2rem;color:#fff;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:8px;}
.btn-switch:hover{background:#e67e22;}

/* ── Table ── */
table{width:100%;border-collapse:collapse;background:rgba(255,255,255,.05);border-radius:12px;overflow:hidden;}
th,td{padding:.8rem;text-align:left;border-bottom:1px solid rgba(255,255,255,.1);}
th{background:rgba(25,118,210,.3);}
.badge{padding:.2rem .6rem;border-radius:20px;font-size:.7rem;}
.actions button{background:none;border:none;color:#fff;cursor:pointer;padding:5px;}
.st-sel{background:rgba(255,255,255,.1);border:1px solid #61B3FA;color:#fff;padding:.2rem .5rem;border-radius:12px;}

/* ── Alerts ── */
.alert{padding:.8rem;border-radius:8px;margin-bottom:1rem;}
.alert-success{background:rgba(39,174,96,.2);border:1px solid #27ae60;}
.alert-error{background:rgba(231,76,60,.2);border:1px solid #e74c3c;}

/* ── Header ── */
.header-flex{display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;}

footer{margin-top:2rem;text-align:center;color:#aaa;}

/* ════════════════════════════════════════════
   MODALS
════════════════════════════════════════════ */
.modal{
    display:none;
    position:fixed;
    inset:0;
    background:rgba(5,10,25,.85);
    backdrop-filter:blur(6px);
    justify-content:center;
    align-items:center;
    z-index:1000;
}
.modal-content{
    background:linear-gradient(145deg,#0d1f35,#112540);
    border:1px solid rgba(97,179,250,.25);
    border-radius:20px;
    padding:2rem 2rem 1.6rem;
    width:90%;
    max-width:520px;
    box-shadow:0 24px 60px rgba(0,0,0,.6),0 0 0 1px rgba(97,179,250,.08);
    animation:slideUp .3s cubic-bezier(.34,1.3,.64,1);
    position:relative;
    overflow:hidden;
}
.modal-content::before{
    content:'';
    position:absolute;
    top:0;left:0;right:0;
    height:3px;
    background:linear-gradient(90deg,#1976D2,#61B3FA,#1976D2);
    background-size:200% 100%;
    animation:shimmer 3s linear infinite;
}
@keyframes slideUp{
    from{opacity:0;transform:translateY(24px) scale(.97);}
    to{opacity:1;transform:translateY(0) scale(1);}
}
@keyframes shimmer{
    0%{background-position:0% 0%;}
    100%{background-position:200% 0%;}
}

/* Titres modals */
.modal-content h2,
.modal-content h3{
    font-size:1.1rem;
    font-weight:700;
    color:#fff;
    margin-bottom:1.4rem;
    display:flex;
    align-items:center;
    gap:.6rem;
    letter-spacing:.02em;
}
.modal-content h2 i,
.modal-content h3 i{color:#61B3FA;font-size:1rem;}

/* Formulaire générique dans modal */
.form-group{margin-bottom:1rem;}
.form-group label{display:block;margin-bottom:.3rem;color:#8A9AB8;font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;}
.form-group input,
.form-group select,
.form-group textarea{
    width:100%;padding:.6rem;border-radius:8px;
    border:1px solid rgba(97,179,250,.3);
    background:rgba(255,255,255,.06);
    color:#e8edf5;
    font-family:inherit;
    transition:border-color .2s,box-shadow .2s;
}
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus{
    outline:none;
    border-color:#61B3FA;
    box-shadow:0 0 0 3px rgba(97,179,250,.15);
}

/* Boutons modals */
.modal-buttons{display:flex;justify-content:flex-end;gap:.6rem;margin-top:1.3rem;padding-top:1rem;border-top:1px solid rgba(255,255,255,.07);}
.btn-save{
    background:linear-gradient(135deg,#1565C0,#61B3FA);
    border:none;color:#fff;border-radius:10px;
    padding:.55rem 1.4rem;font-size:.82rem;font-weight:700;
    cursor:pointer;transition:all .2s;font-family:inherit;
    display:flex;align-items:center;gap:.4rem;
    box-shadow:0 4px 16px rgba(97,179,250,.3);
}
.btn-save:hover:not(:disabled){transform:translateY(-1px);box-shadow:0 6px 20px rgba(97,179,250,.45);}
.btn-save:disabled{opacity:.4;cursor:not-allowed;box-shadow:none;}
.btn-cancel{
    background:rgba(231,76,60,.15);
    border:1px solid rgba(231,76,60,.35);
    color:#e74c3c;border-radius:10px;
    padding:.55rem 1.2rem;font-size:.82rem;
    cursor:pointer;transition:all .2s;font-family:inherit;
    display:flex;align-items:center;gap:.4rem;
}
.btn-cancel:hover{background:rgba(231,76,60,.3);border-color:#e74c3c;}

/* ── Modal réponse : éléments spécifiques ── */
.modal-label{
    font-size:.72rem;color:#8A9AB8;display:block;
    margin-bottom:.35rem;text-transform:uppercase;
    letter-spacing:.06em;font-weight:600;
}
.reply-recap-box{
    background:rgba(97,179,250,.07);
    border:1px solid rgba(97,179,250,.2);
    border-radius:10px;padding:.55rem .85rem;
    font-size:.78rem;color:#61B3FA;
    margin-bottom:1.1rem;min-height:2rem;line-height:1.5;
}
.reply-textarea{
    width:100%;resize:vertical;min-height:110px;max-height:260px;
    background:rgba(255,255,255,.05);
    border:1px solid rgba(97,179,250,.3);
    border-radius:12px;color:#e8edf5;
    font-size:.88rem;font-family:inherit;
    padding:.75rem 1rem;line-height:1.6;
    transition:border-color .2s,box-shadow .2s;outline:none;
}
.reply-textarea:focus{border-color:#61B3FA;box-shadow:0 0 0 3px rgba(97,179,250,.15);}
.reply-textarea::placeholder{color:#4a5a72;}
.progress-track{height:3px;background:rgba(255,255,255,.08);border-radius:3px;overflow:hidden;margin-top:.45rem;}
.progress-fill{height:100%;width:0%;background:#e74c3c;border-radius:3px;transition:width .3s,background .3s;}
.progress-meta{display:flex;justify-content:space-between;align-items:center;margin-top:.3rem;}
.reply-error{font-size:.67rem;color:#e74c3c;display:none;align-items:center;gap:.25rem;}
.reply-ok{font-size:.67rem;color:#27ae60;display:none;align-items:center;gap:.25rem;}
.char-counter{font-size:.67rem;color:#566480;margin-left:auto;}
.status-row{margin:1.1rem 0 1.3rem;}
.status-choices{display:flex;gap:.4rem;flex-wrap:wrap;margin-top:.35rem;}
.status-choice{
    background:rgba(255,255,255,.06);
    border:1px solid rgba(255,255,255,.12);
    color:#c5d1e0;font-size:.72rem;
    padding:.35rem .8rem;border-radius:20px;
    cursor:pointer;transition:all .2s;font-family:inherit;
}
.status-choice:hover{background:rgba(97,179,250,.15);border-color:rgba(97,179,250,.4);color:#fff;}
.status-choice.active{background:rgba(97,179,250,.2);border-color:#61B3FA;color:#61B3FA;font-weight:600;}

/* Admin chat + historique */
.fab-actions{position:fixed;right:1.25rem;bottom:1.25rem;display:flex;flex-direction:column;gap:.75rem;z-index:900;}
.fab-action{display:inline-flex;align-items:center;gap:.6rem;padding:.9rem 1.1rem;border:none;border-radius:999px;font-size:.93rem;font-weight:700;color:#fff;cursor:pointer;box-shadow:0 18px 40px rgba(0,0,0,.25);transition:transform .2s,filter .2s;}
.fab-action:hover{transform:translateY(-1px);filter:brightness(1.05);}
.fab-action.chat{background:linear-gradient(135deg,#0d6efd,#61b3fa);}
.fab-action.hist{background:linear-gradient(135deg,#28a745,#20c997);}
.side-panel{position:fixed;right:1.25rem;bottom:5.7rem;width:min(420px,calc(100vw - 2rem));max-height:82vh;display:none;flex-direction:column;overflow:hidden;border-radius:24px;background:rgba(8,16,35,.96);border:1px solid rgba(97,179,250,.18);box-shadow:0 32px 80px rgba(0,0,0,.35);z-index:900;}
.side-panel.open{display:flex;}
.panel-header{display:flex;justify-content:space-between;align-items:center;padding:1rem 1.1rem;border-bottom:1px solid rgba(255,255,255,.07);}
.panel-title{font-size:1rem;font-weight:700;color:#fff;}
.panel-subtitle{font-size:.78rem;color:#8ca8d9;margin-top:.25rem;}
.panel-close{background:none;border:none;color:#fff;font-size:1.3rem;cursor:pointer;line-height:1;}
.panel-body{padding:1rem;overflow:auto;flex:1;}
.hist-summary{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.6rem;margin-bottom:1rem;}
.hist-chip{background:rgba(255,255,255,.05);border:1px solid rgba(97,179,250,.15);padding:.7rem .85rem;border-radius:14px;font-size:.8rem;color:#e8eef7;}
.hist-chip span{display:block;font-size:1.1rem;font-weight:700;color:#61B3FA;margin-top:.25rem;}
.panel-table{width:100%;border-collapse:collapse;font-size:.82rem;}
.panel-table th,.panel-table td{padding:.55rem .65rem;border-bottom:1px solid rgba(255,255,255,.08);color:#e5eaf4;}
.panel-table th{color:#81c3ff;text-align:left;}
.chat-messages{display:flex;flex-direction:column;gap:.75rem;min-height:240px;}
.chat-message{padding:.95rem 1rem;border-radius:18px;max-width:100%;white-space:pre-wrap;line-height:1.5;}
.chat-message.bot{background:rgba(97,179,250,.12);border:1px solid rgba(97,179,250,.22);color:#eaf4ff;}
.chat-message.user{align-self:flex-end;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.14);color:#fff;}
.chat-input-row{display:flex;gap:.55rem;margin-top:1rem;}
.chat-input{flex:1;padding:.75rem 1rem;border-radius:999px;border:1px solid rgba(97,179,250,.3);background:rgba(255,255,255,.08);color:#fff;outline:none;font-size:.92rem;}
.chat-send{border:none;padding:.82rem 1.2rem;border-radius:999px;background:#61b3fa;color:#0a1628;font-weight:700;cursor:pointer;transition:filter .2s;}
.chat-send:hover{filter:brightness(1.05);}
.chat-send:disabled{opacity:.45;cursor:not-allowed;}
.panel-empty{padding:2rem 1rem;text-align:center;color:rgba(255,255,255,.6);}
</style>
</head>
<body>

<div class="header-flex">
    <h1><i class="fas fa-shield-alt"></i> Administration - Réclamations</h1>
    <a href="/ecoride/index.php?switch=1" class="btn-switch">
        <i class="fas fa-user"></i> Mode Utilisateur
    </a>
</div>

<?php if(!empty($msg)): ?>
<div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>
<?php if(!empty($err)): ?>
<div class="alert alert-error"><?= htmlspecialchars($err) ?></div>
<?php endif; ?>

<!-- Stats -->
<div class="stats">
    <div class="stat"><div class="num"><?= $stats['total'] ?></div><div>Total</div></div>
    <div class="stat"><div class="num"><?= $stats['en_attente'] ?></div><div>En attente</div></div>
    <div class="stat"><div class="num"><?= $stats['en_cours'] ?></div><div>En cours</div></div>
    <div class="stat"><div class="num"><?= $stats['resolue'] ?></div><div>Résolues</div></div>
    <div class="stat"><div class="num"><?= $stats['rejetee'] ?></div><div>Rejetées</div></div>
</div>

<!-- Toolbar -->
<div class="toolbar">
    <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" id="searchInput" placeholder="Rechercher..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
    </div>
    <div>
        <select id="filterStatut" onchange="applyFilters()">
            <option value="">Tous statuts</option>
            <option value="en_attente"  <?= ($_GET['statut']??'')==='en_attente' ?'selected':'' ?>>En attente</option>
            <option value="en_cours"    <?= ($_GET['statut']??'')==='en_cours'   ?'selected':'' ?>>En cours</option>
            <option value="resolue"     <?= ($_GET['statut']??'')==='resolue'    ?'selected':'' ?>>Résolue</option>
            <option value="rejetee"     <?= ($_GET['statut']??'')==='rejetee'    ?'selected':'' ?>>Rejetée</option>
        </select>
        <select id="filterPriorite" onchange="applyFilters()">
            <option value="">Toutes priorités</option>
            <option value="faible"  <?= ($_GET['priorite']??'')==='faible' ?'selected':'' ?>>Faible</option>
            <option value="moyenne" <?= ($_GET['priorite']??'')==='moyenne'?'selected':'' ?>>Moyenne</option>
            <option value="elevee"  <?= ($_GET['priorite']??'')==='elevee' ?'selected':'' ?>>Élevée</option>
        </select>
        <select id="filterCategorie" onchange="applyFilters()">
            <option value="">Toutes catégories</option>
            <option value="technique" <?= ($_GET['categorie']??'')==='technique'?'selected':'' ?>>Technique</option>
            <option value="paiement"  <?= ($_GET['categorie']??'')==='paiement' ?'selected':'' ?>>Paiement</option>
            <option value="securite"  <?= ($_GET['categorie']??'')==='securite' ?'selected':'' ?>>Sécurité</option>
            <option value="autre"     <?= ($_GET['categorie']??'')==='autre'    ?'selected':'' ?>>Autre</option>
        </select>
    </div>
    <button class="btn-add" onclick="openModal('addModal')"><i class="fas fa-plus"></i> Nouvelle</button>
</div>

<!-- Tableau -->
<table>
    <thead>
        <tr>
            <th>ID</th><th>Titre</th><th>Utilisateur</th><th>Catégorie</th>
            <th>Priorité</th><th>Statut</th><th>Date</th><th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if(empty($reclamations)): ?>
        <tr><td colspan="8" style="text-align:center;padding:2rem;">Aucune réclamation</td></tr>
        <?php else: ?>
        <?php foreach($reclamations as $r): ?>
        <tr>
            <td>#<?= $r['id'] ?></td>
            <td><?= htmlspecialchars($r['titre']) ?></td>
            <td><?= htmlspecialchars($r['utilisateur_nom'] ?? 'ID:'.$r['utilisateur_id']) ?></td>
            <td><?= $r['categorie'] ?></td>
            <td><span class="badge"><?= $r['priorite'] ?></span></td>
            <td>
                <form method="POST" action="../index.php" style="margin:0">
                    <input type="hidden" name="action" value="reclamation_statut">
                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                    <select name="statut" class="st-sel" onchange="this.form.submit()">
                        <option value="en_attente" <?= $r['statut']==='en_attente'?'selected':'' ?>>En attente</option>
                        <option value="en_cours"   <?= $r['statut']==='en_cours'  ?'selected':'' ?>>En cours</option>
                        <option value="resolue"    <?= $r['statut']==='resolue'   ?'selected':'' ?>>Résolue</option>
                        <option value="rejetee"    <?= $r['statut']==='rejetee'   ?'selected':'' ?>>Rejetée</option>
                    </select>
                </form>
            </td>
            <td><?= date('d/m/Y', strtotime($r['date_creation'])) ?></td>
            <td class="actions">
                <!-- Répondre -->
                <button title="Répondre"
                    onclick="openReplyModal(<?= $r['id'] ?>, '<?= addslashes(htmlspecialchars($r['titre'])) ?>')">
                    <i class="fas fa-reply" style="color:#61B3FA;"></i>
                </button>
                <!-- Modifier -->
                <button title="Modifier" onclick='editReclamation(<?= json_encode($r) ?>)'>
                    <i class="fas fa-edit" style="color:#f39c12;"></i>
                </button>
                <!-- Supprimer -->
                <form method="POST" action="../index.php" style="display:inline;margin:0"
                      onsubmit="return confirm('Supprimer cette réclamation ?')">
                    <input type="hidden" name="action" value="reclamation_delete">
                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                    <button type="submit" title="Supprimer">
                        <i class="fas fa-trash" style="color:#e74c3c;"></i>
                    </button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<footer>EcoRide - Mode Administrateur</footer>

<div class="fab-actions">
    <button class="fab-action hist" type="button" onclick="togglePanel('historyPanel')">
        <i class="fas fa-history"></i> Historique
    </button>
    <button class="fab-action chat" type="button" onclick="togglePanel('chatPanel')">
        <i class="fas fa-robot"></i> Chatbot
    </button>
</div>

<div id="historyPanel" class="side-panel" aria-hidden="true">
    <div class="panel-header">
        <div>
            <div class="panel-title"><i class="fas fa-clock"></i> Historique des réclamations</div>
            <div class="panel-subtitle">Les dernières réclamations affichées</div>
        </div>
        <button class="panel-close" type="button" onclick="closePanel('historyPanel')">&times;</button>
    </div>
    <div class="panel-body">
        <div class="hist-summary">
            <div class="hist-chip">
                Statut total <span><?= intval($stats['total']) ?></span>
            </div>
            <div class="hist-chip">
                En attente <span><?= intval($stats['en_attente']) ?></span>
            </div>
            <div class="hist-chip">
                Résolue <span><?= intval($stats['resolue']) ?></span>
            </div>
        </div>
        <?php if(empty($reclamations)): ?>
            <div class="panel-empty">Aucune réclamation trouvée.</div>
        <?php else: ?>
            <table class="panel-table">
                <thead>
                    <tr><th>ID</th><th>Titre</th><th>Statut</th><th>Date</th></tr>
                </thead>
                <tbody>
                    <?php foreach(array_slice($reclamations, 0, 10) as $item): ?>
                        <tr>
                            <td>#<?= $item['id'] ?></td>
                            <td><?= htmlspecialchars($item['titre']) ?></td>
                            <td><?= htmlspecialchars($item['statut']) ?></td>
                            <td><?= date('d/m H:i', strtotime($item['date_creation'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<div id="chatPanel" class="side-panel" aria-hidden="true">
    <div class="panel-header">
        <div>
            <div class="panel-title"><i class="fas fa-robot"></i> Chatbot admin</div>
            <div class="panel-subtitle">Demandez des statistiques ou un résumé rapide</div>
        </div>
        <button class="panel-close" type="button" onclick="closePanel('chatPanel')">&times;</button>
    </div>
    <div class="panel-body">
        <div id="chatMessages" class="chat-messages">
            <div class="chat-message bot">🤖 Bonjour admin ! Tapez 'aide' pour démarrer le chatbot.</div>
        </div>
        <div class="chat-input-row">
            <input id="chatInput" class="chat-input" type="text" placeholder="Envoyer un message au chatbot..." />
            <button id="chatSendBtn" class="chat-send" type="button" onclick="sendChatMessage()">Envoyer</button>
        </div>
    </div>
</div>


<!-- ══════════════════════════════════════════
     MODAL : RÉPONDRE À LA RÉCLAMATION
══════════════════════════════════════════ -->
<div id="replyModal" class="modal">
    <div class="modal-content">
        <h3><i class="fas fa-comment-dots"></i> Répondre à la réclamation</h3>

        <form method="POST" action="../index.php" onsubmit="return validateReply()">
            <input type="hidden" name="action" value="repondre">
            <input type="hidden" name="id" id="reply_id">

            <div>
                <span class="modal-label">Réclamation concernée</span>
                <div id="reply_recap" class="reply-recap-box"></div>
            </div>

            <div style="margin-bottom:.5rem;">
                <label class="modal-label" for="reply_text">
                    Votre réponse <span style="color:#e74c3c;">*</span>
                </label>
                <textarea
                    name="reponse_admin"
                    id="reply_text"
                    class="reply-textarea"
                    placeholder="Rédigez votre réponse ici (minimum 10 caractères)..."
                    oninput="updateReplyCounter(this)"
                ></textarea>

                <div class="progress-track">
                    <div id="replyProgressBar" class="progress-fill"></div>
                </div>
                <div class="progress-meta">
                    <span id="replyError" class="reply-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span id="replyErrorMsg"></span>
                    </span>
                    <span id="replyOk" class="reply-ok">
                        <i class="fas fa-check-circle"></i> Réponse valide
                    </span>
                    <span class="char-counter">
                        <span id="replyCount">0</span>/500 caractères
                    </span>
                </div>
            </div>

            <div class="status-row">
                <span class="modal-label">Changer le statut après réponse</span>
                <div class="status-choices" id="statusButtons">
                    <button type="button" class="status-choice active" data-val=""
                            onclick="chooseStatus(this,'')">🔄 Garder actuel</button>
                    <button type="button" class="status-choice" data-val="en_cours"
                            onclick="chooseStatus(this,'en_cours')">🔵 En cours</button>
                    <button type="button" class="status-choice" data-val="resolue"
                            onclick="chooseStatus(this,'resolue')">✅ Résolue</button>
                    <button type="button" class="status-choice" data-val="rejetee"
                            onclick="chooseStatus(this,'rejetee')">❌ Rejetée</button>
                </div>
                <input type="hidden" name="new_statut" id="reply_new_statut" value="">
            </div>

            <div class="modal-buttons">
                <button type="button" class="btn-cancel" onclick="closeModal('replyModal')">
                    <i class="fas fa-times"></i> Annuler
                </button>
                <button type="submit" class="btn-save" id="replySaveBtn" disabled>
                    <i class="fas fa-paper-plane"></i> Envoyer
                </button>
            </div>
        </form>
    </div>
</div>


<!-- ══════════════════════════════════════════
     MODAL : AJOUTER UNE RÉCLAMATION
══════════════════════════════════════════ -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <h2><i class="fas fa-plus-circle"></i> Ajouter une réclamation</h2>
        <form method="POST" action="../index.php">
            <input type="hidden" name="action" value="create_reclamation">
            <div class="form-group"><label>Titre *</label><input type="text" name="titre"></div>
            <div class="form-group"><label>Description *</label><textarea name="description" rows="3"></textarea></div>
            <div class="form-group"><label>ID Utilisateur *</label><input type="number" name="utilisateur_id"></div>
            <div class="form-group"><label>Catégorie</label>
                <select name="categorie">
                    <option value="technique">Technique</option>
                    <option value="paiement">Paiement</option>
                    <option value="securite">Sécurité</option>
                    <option value="autre">Autre</option>
                </select>
            </div>
            <div class="form-group"><label>Priorité</label>
                <select name="priorite">
                    <option value="faible">Faible</option>
                    <option value="moyenne">Moyenne</option>
                    <option value="elevee">Élevée</option>
                </select>
            </div>
            <div class="form-group"><label>Statut</label>
                <select name="statut">
                    <option value="en_attente">En attente</option>
                    <option value="en_cours">En cours</option>
                    <option value="resolue">Résolue</option>
                    <option value="rejetee">Rejetée</option>
                </select>
            </div>
            <div class="modal-buttons">
                <button type="button" class="btn-cancel" onclick="closeModal('addModal')">
                    <i class="fas fa-times"></i> Annuler
                </button>
                <button type="submit" class="btn-save">
                    <i class="fas fa-save"></i> Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>


<!-- ══════════════════════════════════════════
     MODAL : MODIFIER UNE RÉCLAMATION
══════════════════════════════════════════ -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <h2><i class="fas fa-edit"></i> Modifier la réclamation</h2>
        <form method="POST" action="../index.php">
            <input type="hidden" name="action" value="reclamation_update">
            <input type="hidden" name="id" id="edit_id">
            <div class="form-group"><label>Titre *</label><input type="text" name="titre" id="edit_titre"></div>
            <div class="form-group"><label>Description *</label><textarea name="description" id="edit_desc" rows="3"></textarea></div>
            <div class="form-group"><label>Catégorie</label>
                <select name="categorie" id="edit_cat">
                    <option value="technique">Technique</option>
                    <option value="paiement">Paiement</option>
                    <option value="securite">Sécurité</option>
                    <option value="autre">Autre</option>
                </select>
            </div>
            <div class="form-group"><label>Priorité</label>
                <select name="priorite" id="edit_prio">
                    <option value="faible">Faible</option>
                    <option value="moyenne">Moyenne</option>
                    <option value="elevee">Élevée</option>
                </select>
            </div>
            <div class="form-group"><label>Statut</label>
                <select name="statut" id="edit_statut">
                    <option value="en_attente">En attente</option>
                    <option value="en_cours">En cours</option>
                    <option value="resolue">Résolue</option>
                    <option value="rejetee">Rejetée</option>
                </select>
            </div>
            <div class="modal-buttons">
                <button type="button" class="btn-cancel" onclick="closeModal('editModal')">
                    <i class="fas fa-times"></i> Annuler
                </button>
                <button type="submit" class="btn-save">
                    <i class="fas fa-save"></i> Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>


<!-- ══════════════════════════════════════════
     SCRIPTS
══════════════════════════════════════════ -->
<script>
/* ── Filtres ── */
function applyFilters() {
    let url = '../index.php?';
    const search    = document.getElementById('searchInput').value;
    const statut    = document.getElementById('filterStatut').value;
    const priorite  = document.getElementById('filterPriorite').value;
    const categorie = document.getElementById('filterCategorie').value;
    if (search)    url += 'search='    + encodeURIComponent(search) + '&';
    if (statut)    url += 'statut='    + statut    + '&';
    if (priorite)  url += 'priorite='  + priorite  + '&';
    if (categorie) url += 'categorie=' + categorie;
    window.location.href = url;
}
document.getElementById('searchInput').addEventListener('keyup', e => { if(e.key==='Enter') applyFilters(); });

/* ── Modals génériques ── */
function openModal(id)  { document.getElementById(id).style.display = 'flex'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; }
window.addEventListener('click', e => {
    if (e.target.classList.contains('modal')) e.target.style.display = 'none';
});

/* ── Modal Modifier ── */
function editReclamation(r) {
    document.getElementById('edit_id').value    = r.id;
    document.getElementById('edit_titre').value = r.titre;
    document.getElementById('edit_desc').value  = r.description;
    document.getElementById('edit_cat').value   = r.categorie;
    document.getElementById('edit_prio').value  = r.priorite;
    document.getElementById('edit_statut').value= r.statut;
    openModal('editModal');
}

/* ── Modal Répondre ── */
function openReplyModal(id, titre) {
    document.getElementById('reply_id').value           = id;
    document.getElementById('reply_recap').textContent  = '#' + id + ' — ' + titre;
    document.getElementById('reply_text').value         = '';
    document.getElementById('replyCount').textContent   = '0';
    document.getElementById('replyProgressBar').style.width      = '0%';
    document.getElementById('replyProgressBar').style.background = '#e74c3c';
    document.getElementById('replyError').style.display = 'none';
    document.getElementById('replyOk').style.display    = 'none';
    document.getElementById('replySaveBtn').disabled    = true;
    document.querySelectorAll('.status-choice').forEach(b => b.classList.remove('active'));
    document.querySelector('.status-choice[data-val=""]').classList.add('active');
    document.getElementById('reply_new_statut').value   = '';
    openModal('replyModal');
    setTimeout(() => document.getElementById('reply_text').focus(), 200);
}

/* ── Compteur + barre progression ── */
function updateReplyCounter(el) {
    const len = el.value.length;
    const max = 500;
    const bar = document.getElementById('replyProgressBar');
    document.getElementById('replyCount').textContent = len;
    bar.style.width = Math.min(len / max * 100, 100) + '%';

    const err = document.getElementById('replyError');
    const ok  = document.getElementById('replyOk');
    const btn = document.getElementById('replySaveBtn');
    const msg = document.getElementById('replyErrorMsg');

    if (len === 0) {
        bar.style.background = '#e74c3c';
        err.style.display = 'none'; ok.style.display = 'none'; btn.disabled = true;
    } else if (len < 10) {
        bar.style.background = '#e67e22';
        msg.textContent = 'Minimum 10 caractères (' + (10 - len) + ' restants)';
        err.style.display = 'flex'; ok.style.display = 'none'; btn.disabled = true;
    } else if (len > max) {
        bar.style.background = '#e74c3c';
        msg.textContent = 'Maximum ' + max + ' caractères dépassé';
        err.style.display = 'flex'; ok.style.display = 'none'; btn.disabled = true;
    } else {
        bar.style.background = len < 50 ? '#f39c12' : '#27ae60';
        err.style.display = 'none'; ok.style.display = 'flex'; btn.disabled = false;
    }
}

/* ── Sélection statut ── */
function chooseStatus(btn, val) {
    document.querySelectorAll('.status-choice').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('reply_new_statut').value = val;
}

/* ── Validation avant envoi ── */
function validateReply() {
    const len = document.getElementById('reply_text').value.trim().length;
    if (len < 10 || len > 500) {
        updateReplyCounter(document.getElementById('reply_text'));
        return false;
    }
    return true;
}

/* ── Auto-hide alerts ── */
document.querySelectorAll('.alert').forEach(a => {
    setTimeout(() => { a.style.opacity='0'; a.style.transition='opacity .5s'; }, 4000);
    setTimeout(() => a.remove(), 4500);
});

const baseMatch = window.location.pathname.match(/^(\/.*?\/ecoride)(?:\/|$)/i);
const appBase = baseMatch ? baseMatch[1] : '';
const chatbotEndpoint = window.location.origin + appBase + '/Controller/ChatbotController.php';

function togglePanel(panelId) {
    const panel = document.getElementById(panelId);
    const otherPanel = panelId === 'chatPanel' ? document.getElementById('historyPanel') : document.getElementById('chatPanel');
    if (otherPanel) otherPanel.classList.remove('open');
    panel.classList.toggle('open');
}

function closePanel(panelId) {
    document.getElementById(panelId).classList.remove('open');
}

window.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        closePanel('chatPanel');
        closePanel('historyPanel');
    }
});

function appendChatMessage(text, role = 'bot') {
    const container = document.getElementById('chatMessages');
    const message = document.createElement('div');
    message.className = 'chat-message ' + role;
    message.textContent = text;
    container.appendChild(message);
    container.scrollTop = container.scrollHeight;
}

async function sendChatMessage() {
    const input = document.getElementById('chatInput');
    const text = input.value.trim();
    if (!text) return;
    appendChatMessage(text, 'user');
    input.value = '';
    const button = document.getElementById('chatSendBtn');
    button.disabled = true;
    try {
        const response = await fetch(chatbotEndpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: text })
        });
        const data = await response.json();
        appendChatMessage(data.reply || data.error || 'Erreur de communication.', 'bot');
    } catch (error) {
        appendChatMessage('Erreur réseau. Veuillez réessayer.', 'bot');
    } finally {
        button.disabled = false;
    }
}

document.getElementById('chatInput').addEventListener('keydown', e => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendChatMessage();
    }
});
</script>
</body>
</html>