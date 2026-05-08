<?php
session_start();
$_SESSION['is_admin'] = true;

try {
    $pdo = new PDO("mysql:host=localhost;dbname=ecoride;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES utf8");
} catch (PDOException $e) {
    die("Erreur BDD : " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'update_statut') {
        $stmt = $pdo->prepare("UPDATE reclamations SET statut = ? WHERE id = ?");
        $stmt->execute([$_POST['statut'], $_POST['id']]);
    } elseif ($action === 'repondre') {
        $stmt = $pdo->prepare("INSERT INTO reponse (reclamation_id, auteur_admin, contenu)
            VALUES (?, 'Admin', ?)
            ON DUPLICATE KEY UPDATE contenu = VALUES(contenu), date_reponse = NOW()");
        $stmt->execute([$_POST['id'], $_POST['reponse_admin']]);

        if (!empty($_POST['new_statut'])) {
            $statusStmt = $pdo->prepare("UPDATE reclamations SET statut = ? WHERE id = ?");
            $statusStmt->execute([$_POST['new_statut'], $_POST['id']]);
        }
    } elseif ($action === 'update') {
        $stmt = $pdo->prepare("UPDATE reclamations SET titre = ?, description = ?, categorie = ?, priorite = ?, statut = ? WHERE id = ?");
        $stmt->execute([$_POST['titre'], $_POST['description'], $_POST['categorie'], $_POST['priorite'], $_POST['statut'], $_POST['id']]);
    } elseif ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM reclamations WHERE id = ?");
        $stmt->execute([$_POST['id']]);
    }

    $redirect = 'admin_reclamations.php';
    if (!empty($_SERVER['QUERY_STRING'])) {
        $redirect .= '?' . $_SERVER['QUERY_STRING'];
    }
    header('Location: ' . $redirect);
    exit;
}

$search = trim($_GET['search'] ?? '');
$statut_filter = $_GET['statut'] ?? '';
$priorite_filter = $_GET['priorite'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 6;
$offset = ($page - 1) * $limit;

$count_sql = "SELECT COUNT(*) as total FROM reclamations WHERE 1=1";
$count_params = [];
if ($statut_filter) { $count_sql .= " AND statut = ?"; $count_params[] = $statut_filter; }
if ($priorite_filter) { $count_sql .= " AND priorite = ?"; $count_params[] = $priorite_filter; }
if ($search) { $count_sql .= " AND (titre LIKE ? OR description LIKE ? OR categorie LIKE ? OR priorite LIKE ? OR statut LIKE ? )"; $count_params[] = "%$search%"; $count_params[] = "%$search%"; $count_params[] = "%$search%"; $count_params[] = "%$search%"; $count_params[] = "%$search%"; }
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($count_params);
$total_rows = intval($count_stmt->fetchColumn());
$total_pages = max(1, ceil($total_rows / $limit));

$sql = "SELECT r.*, CONCAT('User #', r.utilisateur_id) AS utilisateur_nom, rp.contenu AS reponse_admin
        FROM reclamations r
        LEFT JOIN reponse rp ON rp.reclamation_id = r.id
        WHERE 1=1";
$params = [];
if ($statut_filter) { $sql .= " AND r.statut = ?"; $params[] = $statut_filter; }
if ($priorite_filter) { $sql .= " AND r.priorite = ?"; $params[] = $priorite_filter; }
if ($search) { $sql .= " AND (r.titre LIKE ? OR r.description LIKE ? OR r.categorie LIKE ? OR r.priorite LIKE ? OR r.statut LIKE ? )"; $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%"; }
$sql .= " ORDER BY r.date_creation DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reclamations = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stats_stmt = $pdo->query("SELECT COUNT(*) AS total,
    SUM(statut='en_attente') AS en_attente,
    SUM(statut='en_cours') AS en_cours,
    SUM(statut='resolue') AS resolue,
    SUM(statut='rejetee') AS rejetee
    FROM reclamations");
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

$selected_language = $_GET['lang'] ?? ($_SESSION['lang'] ?? 'fr');
$selected_language = in_array($selected_language, ['fr', 'en']) ? $selected_language : 'fr';
$_SESSION['lang'] = $selected_language;
?>
<!DOCTYPE html>
<html lang="<?= $selected_language === 'en' ? 'en' : 'fr' ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin - Réclamations | EcoRide</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
:root{--blue:#1976D2;--blue-light:#61B3FA;--dark:#0A1628;--dark2:#0D1F3A;--dark3:#0F3B6E;--grey:#A7A9AC;--green:#27ae60;--red:#e74c3c;--yellow:#f1c40f;--bg:#071b2c;}
body{font-family:'Poppins',sans-serif;background:linear-gradient(135deg,var(--dark),var(--dark2));color:#fff;min-height:100vh;overflow-x:hidden;transition:background .3s,color .3s;}
body::before{content:'';position:fixed;inset:0;background:radial-gradient(circle at 20% 15%, rgba(97,179,250,.16), transparent 36%),radial-gradient(circle at 78% 78%, rgba(25,118,210,.14), transparent 40%);opacity:1;z-index:-2;pointer-events:none;}
body::after{content:'';position:fixed;inset:0;background:linear-gradient(130deg,rgba(8,20,38,.88) 0%,rgba(12,31,58,.84) 45%,rgba(8,20,38,.9) 100%);z-index:-1;pointer-events:none;}
.wrap{display:flex;min-height:100vh;}
.sidebar{width:280px;background:linear-gradient(180deg,#2F76BC 0%,#1E5EA5 50%,#174C8A 100%);padding:1.5rem 0;position:fixed;top:0;left:0;height:100vh;overflow-y:auto;z-index:100;box-shadow:4px 0 20px rgba(0,0,0,.2);display:flex;flex-direction:column;}
.sidebar-header{padding:1.5rem;border-bottom:1px solid rgba(255,255,255,.15);margin-bottom:1.5rem;text-align:center;}
.sidebar-header .logo{display:flex;flex-direction:column;align-items:center;gap:6px;text-decoration:none;}
.sidebar-header .logo-img{width:72px;height:72px;border-radius:18px;background:rgba(255,255,255,.12);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.5rem;box-shadow:0 8px 20px rgba(0,0,0,.15);}
.sidebar-header .logo-text{font-size:1.3rem;font-weight:700;color:#A9D6FF;letter-spacing:1px;}
.sidebar-header .logo-tagline{font-size:0.75rem;color:#BFD8F1;margin-top:2px;letter-spacing:1px;opacity:.85;}
.nav-section{color:#CFE6FF;font-size:0.7rem;text-transform:uppercase;letter-spacing:2px;padding:.75rem 1.5rem;margin-top:0.5rem;opacity:.8;font-weight:600;}
.sidebar nav ul{list-style:none;}
.sidebar nav ul li{margin-bottom:.35rem;}
.sidebar nav ul li a{display:flex;align-items:center;gap:12px;padding:.75rem 1.5rem;color:#EAF4FF;text-decoration:none;transition:all .3s;font-size:.9rem;margin:0 0.5rem;border-radius:10px;font-weight:500;}
.sidebar nav ul li a i{width:22px;color:#EAF4FF;font-size:1rem;}
.sidebar nav ul li a:hover{background:rgba(111,168,220,.3);color:#fff;transform:translateX(4px);}
.sidebar nav ul li a.active{background:linear-gradient(135deg,#6FA8DC,#8FC1F5);color:#FFFFFF;box-shadow:0 4px 12px rgba(111,168,220,.3);}
.sidebar-footer{margin-top:auto;padding:1rem 1.5rem;border-top:1px solid rgba(255,255,255,.1);}
.sidebar-footer a{display:flex;align-items:center;gap:12px;color:#FFCDD2;text-decoration:none;font-size:0.85rem;padding:0.5rem 0;border-radius:10px;transition:all .3s;}
.sidebar-footer a:hover{color:#FF8A80;transform:translateX(5px);}
.main{flex:1;margin-left:280px;padding:1.2rem;position:relative;z-index:1;}
.admin-header{background:linear-gradient(90deg,#071C2F,#0A2A47,#0D355B);padding:0.7rem 1.5rem;display:flex;justify-content:space-between;align-items:center;margin-bottom:0.8rem;border-radius:12px;border-bottom:1px solid rgba(255,255,255,.08);flex-wrap:wrap;gap:0.8rem;}
.admin-logo{display:flex;flex-direction:column;}
.admin-logo .logo-eco{font-size:1.3rem;font-weight:700;letter-spacing:1px;}
.admin-logo .logo-eco span:first-child{color:#4EA3FF;}
.admin-logo .logo-eco span:last-child{color:#6BB8FF;}
.admin-logo .logo-tagline{font-size:0.7rem;color:#A8C1D9;margin-top:2px;}
.admin-nav{display:flex;gap:0.4rem;align-items:center;flex-wrap:wrap;}
.admin-nav a{text-decoration:none;padding:0.4rem 1rem;border-radius:30px;font-size:0.85rem;font-weight:500;transition:all 0.3s;background:transparent;color:#CFE6FF;font-family:'Poppins',sans-serif;}
.admin-nav a:hover{background:rgba(255,255,255,0.1);color:#FFFFFF;}
.admin-nav .lang-form{display:flex;align-items:center;}
.admin-nav .lang-form select{min-width:85px;padding:.4rem .7rem;border-radius:20px;border:1px solid rgba(255,255,255,.18);background:rgba(255,255,255,.08);color:#fff;font-size:.85rem;cursor:pointer;outline:none;transition:all .2s;}
.admin-nav .lang-form select:hover{background:rgba(255,255,255,.12);}
.admin-nav .lang-form select option{background:#0D1F3A;color:#fff;}
.sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0;}
/* ========== BOUTON PROFIL ========== */
.admin-nav .profile-btn{background:#003050;color:#FFFFFF;display:flex;align-items:center;gap:8px;padding:0.4rem 1rem;}
.admin-nav .profile-btn:hover{background:#002050;transform:translateY(-2px);box-shadow:0 4px 12px rgba(0,48,80,0.4);}
.profile-avatar{width:24px;height:24px;background:#5FA8FF;border-radius:50%;display:flex;align-items:center;justify-content:center;}
.profile-avatar i{font-size:0.7rem;color:#FFFFFF;}
/* ========== BOUTON ADMIN - STYLE ROUGE ========== */
.admin-nav .admin-btn{background:rgba(231,76,60,0.2);border:1px solid rgba(231,76,60,0.4);color:#e74c3c;}
.admin-nav .admin-btn:hover{background:rgba(231,76,60,0.35);}
.theme-btn{background:rgba(255,255,255,0.1);border:none;width:32px;height:32px;border-radius:50%;cursor:pointer;font-size:1rem;transition:all 0.3s;display:flex;align-items:center;justify-content:center;color:white;}
.stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:0.8rem;margin:1rem 0;}
.stat{background:rgba(255,255,255,.08);border:1px solid rgba(97,179,250,.18);border-radius:14px;padding:0.8rem 0.7rem;transition:all .3s;}
.stat:hover{transform:translateY(-2px);border-color:var(--blue-light);}
.stat .icon{width:32px;height:32px;border-radius:10px;display:flex;align-items:center;justify-content:center;background:rgba(97,179,250,.15);color:#61B3FA;margin-bottom:.5rem;}
.stat .num{font-size:1.6rem;font-weight:700;color:#fff;}
.stat .label{color:var(--grey);font-size:.7rem;margin-top:.1rem;}
.filters{display:flex;flex-wrap:wrap;gap:.6rem;margin-bottom:1rem;align-items:flex-end;}
.filter-group{display:flex;flex-direction:column;gap:.25rem;min-width:150px;}
.filter-group label{font-size:.65rem;color:var(--grey);}
.filter-group input,.filter-group select{padding:.5rem .7rem;border-radius:10px;background:rgba(255,255,255,.08);border:1px solid rgba(97,179,250,.22);color:#fff;font-size:.8rem;outline:none;transition:border .2s;background-clip:padding-box;}
.filter-group input::placeholder{color:rgba(255,255,255,.55);}
.filter-group button{padding:.5rem 0.9rem;border-radius:10px;border:none;background:linear-gradient(135deg,#1976D2,#61B3FA);color:#fff;font-weight:600;cursor:pointer;font-size:.75rem;}
.filter-group a{font-size:.75rem;color:#61B3FA;text-decoration:none;}
.table-wrapper{background:rgba(16,39,70,.52);border-radius:16px;overflow-x:auto;border:1px solid rgba(97,179,250,.16);box-shadow:0 8px 20px rgba(0,0,0,.1);margin:0.5rem auto;width:100%;}
table{width:100%;border-collapse:collapse;table-layout:auto;font-size:.75rem;background:rgba(255,255,255,.04);}
th,td{padding:.4rem .5rem;text-align:left;border-bottom:1px solid rgba(255,255,255,.06);word-break:break-word;}
th{background:rgba(25,118,210,.22);color:var(--blue-light);font-weight:600;font-size:.7rem;text-transform:uppercase;letter-spacing:.03em;}
tr:hover td{background:rgba(255,255,255,.05);}
td{vertical-align:middle;color:#e8eef8;font-size:.75rem;white-space:normal;overflow:hidden;text-overflow:ellipsis;}
.empty{text-align:center;color:rgba(255,255,255,.65);padding:1rem;}
.status-select{min-width:100px;background:rgba(255,255,255,.08);border:1px solid rgba(97,179,250,.2);color:#fff;border-radius:8px;padding:.3rem .5rem;font-size:.7rem;}
.actions{display:flex;gap:.3rem;flex-wrap:nowrap;align-items:center;}
.btn-action{width:28px;height:28px;border-radius:50%;border:none;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;font-size:.75rem;transition:all .2s;flex-shrink:0;}
.btn-action:hover{transform:scale(1.05);}
@media(max-width:1200px){table{min-width:650px;}}
@media(max-width:900px){.btn-action{width:26px;height:26px;font-size:.7rem;}th,td{padding:.35rem .4rem;font-size:.7rem;}}
.reponse-text{background:rgba(39,174,96,.12);padding:.2rem .4rem;border-radius:8px;font-size:.7rem;display:inline-flex;align-items:center;gap:.3rem;color:#d5f5e3;}
.btn-action.reply{background:rgba(39,174,96,.17);color:#27ae60;}
.btn-action.edit{background:rgba(241,196,15,.18);color:#f1c40f;}
.btn-action.delete{background:rgba(231,76,60,.95);color:#fff;}
.btn-action.history{background:rgba(52,152,219,.18);color:#3498db;border:1px solid rgba(52,152,219,.25);}
.btn-action.history:hover{background:rgba(52,152,219,.32);transform:translateY(-2px);}
.pagination{display:flex;justify-content:center;gap:.4rem;margin-top:1rem;flex-wrap:wrap;}
.pagination a,.pagination span{display:inline-block;padding:.3rem .7rem;border-radius:999px;background:rgba(255,255,255,.08);color:#fff;text-decoration:none;font-size:.7rem;}
.pagination a:hover{background:#1976D2;}
.pagination .active{background:#1976D2;cursor:default;color:#fff;}
.footer{text-align:center;margin-top:1rem;padding:0.8rem;color:#A7A9AC;font-size:.7rem;border-top:1px solid rgba(97,179,250,.1);}
.modal{display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,.75);justify-content:center;align-items:center;overflow:auto;padding:1rem;}
.modal.active{display:flex;}
.modal-content{background:#0D1F3A;margin:auto;padding:1.2rem;border-radius:18px;width:100%;max-width:500px;box-shadow:0 20px 60px rgba(0,0,0,.35);}
.modal-content h3{color:#61B3FA;margin-bottom:0.8rem;font-size:1rem;}
.modal-content label{display:block;margin-bottom:.25rem;color:rgba(255,255,255,.72);font-size:.75rem;font-weight:600;}
.modal-content input,.modal-content textarea,.modal-content select{width:100%;padding:.6rem .8rem;margin-bottom:0.8rem;border-radius:10px;border:1px solid rgba(97,179,250,.25);background:rgba(255,255,255,.06);color:#fff;font-size:.85rem;outline:none;}
.modal-content textarea{resize:vertical;min-height:100px;}
.modal-buttons{display:flex;justify-content:flex-end;gap:.5rem;flex-wrap:wrap;}
.btn-save{background:#27ae60;border:none;padding:.5rem 1rem;border-radius:999px;color:#fff;cursor:pointer;transition:all .2s;font-size:.75rem;}
.btn-save:hover{transform:translateY(-1px);}
.btn-cancel{background:#e74c3c;border:none;padding:.5rem 1rem;border-radius:999px;color:#fff;cursor:pointer;font-size:.75rem;}
.modal .stat-options{display:flex;flex-wrap:wrap;gap:.4rem;margin-bottom:0.8rem;}
.stat-option{cursor:pointer;padding:.4rem .7rem;border-radius:999px;border:1px solid rgba(255,255,255,.15);color:#fff;transition:all .2s;background:rgba(255,255,255,.06);font-size:.7rem;}
.stat-option.active{background:rgba(97,179,250,.25);border-color:rgba(97,179,250,.45);}
body.light-mode{background:#f5f5f5;color:#263238;}
body.light-mode .sidebar{background:#fff;border-right:1px solid #dfe6f2;}
body.light-mode .sidebar-header .logo-text{color:#1f2937;}
body.light-mode .sidebar nav ul li a{color:#1f2937;}
body.light-mode .sidebar nav ul li a.active{background:rgba(25,118,210,.08);color:#1976D2;}
body.light-mode .admin-header{background:#fff;border:1px solid #dbe4f0;color:#1f2937;}
body.light-mode .admin-nav a{color:#1f2937;}
body.light-mode .filters .filter-group input,.body.light-mode .filters .filter-group select{background:#fff;color:#333;border-color:#ddd;}
body.light-mode .table-wrapper{background:#fff;border-color:#e5efff;}
body.light-mode th{background:#e3f2fd;color:#1976D2;}
body.light-mode td{border-bottom-color:#eee;color:#1f2937;}
body.light-mode .modal-content{background:#fff;color:#1f2937;}
body.light-mode .modal-content label{color:#404b5a;}
body.light-mode .hist-panel{background:#fff;border-left:1px solid #e0e0e0;}
body.light-mode .hist-header{border-bottom-color:#e0e0e0;}
body.light-mode .hist-item{border-bottom-color:#eee;}
body.light-mode .hi-date{color:#666;}

/* STATS FLOATING BUTTON */
.fab-stats{position:fixed;bottom:80px;right:20px;z-index:9997;}
.fab-stats-btn{width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,#27ae60,#1a8a4a);border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:1rem;color:#fff;text-decoration:none;position:relative;}
.fab-stats-btn .fab-tooltip{position:absolute;right:54px;top:50%;transform:translateY(-50%);background:#1a2744;color:#fff;font-size:.65rem;padding:.2rem .5rem;border-radius:6px;white-space:nowrap;opacity:0;transition:opacity .2s;}
.fab-stats-btn:hover .fab-tooltip{opacity:1;}

/* CHATBOT */
.chat-fab{position:fixed;bottom:20px;right:20px;z-index:9999;width:50px;height:50px;border-radius:50%;background:linear-gradient(135deg,#1976D2,#0D47A1);border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:#fff;}
.chat-fab .chat-badge{position:absolute;top:-4px;right:-4px;width:16px;height:16px;background:#e74c3c;border-radius:50%;font-size:.5rem;display:flex;align-items:center;justify-content:center;}
.chatbot-window{position:fixed;bottom:80px;right:20px;z-index:9998;width:320px;height:480px;background:#0D1B35;border:1px solid rgba(97,179,250,.2);border-radius:18px;display:flex;flex-direction:column;transform:scale(0);transform-origin:bottom right;transition:transform .25s;opacity:0;pointer-events:none;}
.chatbot-window.open{transform:scale(1);opacity:1;pointer-events:all;}
.cbot-header{background:linear-gradient(135deg,#1976D2,#0D47A1);border-radius:18px 18px 0 0;padding:.6rem 0.8rem;display:flex;align-items:center;gap:.5rem;}
.cbot-avatar{width:30px;height:30px;background:rgba(255,255,255,.2);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.8rem;}
.cbot-title{flex:1;}
.cbot-title strong{font-size:.8rem;display:block;}
.cbot-title span{font-size:.6rem;color:rgba(255,255,255,.6);}
.cbot-messages{flex:1;overflow-y:auto;padding:.7rem;display:flex;flex-direction:column;gap:.5rem;}
.cbot-msg{max-width:85%;padding:.4rem .6rem;border-radius:12px;font-size:.7rem;}
.cbot-msg.bot{background:rgba(25,118,210,.15);border-radius:4px 12px 12px 12px;align-self:flex-start;}
.cbot-msg.user{background:#1976D2;border-radius:12px 4px 12px 12px;align-self:flex-end;}
.cbot-typing{display:flex;gap:4px;padding:.4rem .6rem;background:rgba(25,118,210,.1);border-radius:4px 12px 12px 12px;align-self:flex-start;}
.cbot-typing span{width:6px;height:6px;background:#61B3FA;border-radius:50%;animation:cbot-bounce .9s infinite;}
.cbot-typing span:nth-child(2){animation-delay:.2s;}
.cbot-typing span:nth-child(3){animation-delay:.4s;}
@keyframes cbot-bounce{0%,60%,100%{transform:translateY(0);}30%{transform:translateY(-5px);}}
.cbot-quick{padding:.3rem .6rem;display:flex;gap:.25rem;flex-wrap:wrap;border-top:1px solid rgba(255,255,255,.06);}
.cbot-chip{background:rgba(25,118,210,.18);border:1px solid rgba(97,179,250,.25);color:#61B3FA;font-size:.6rem;padding:.15rem .4rem;border-radius:16px;cursor:pointer;}
.cbot-input-row{padding:.5rem .6rem;border-top:1px solid rgba(255,255,255,.07);display:flex;gap:.4rem;}
.cbot-input{flex:1;background:rgba(255,255,255,.07);border:1px solid rgba(97,179,250,.2);border-radius:18px;padding:.35rem .7rem;color:#fff;font-size:.7rem;outline:none;}
.cbot-input:focus{border-color:rgba(97,179,250,.5);background:rgba(255,255,255,.1);}
.cbot-input::placeholder{color:rgba(255,255,255,.3);}
.cbot-send{width:30px;height:30px;border-radius:50%;background:#1976D2;border:none;color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:.7rem;flex-shrink:0;}
.cbot-send:disabled{background:rgba(255,255,255,.1);cursor:default;}

/* PANEL HISTORIQUE - Version latérale droite */
.hist-overlay{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.6);z-index:9995;}
.hist-overlay.open{display:block;}
.hist-panel{position:fixed;top:0;right:0;height:100%;width:420px;background:#0f1e36;box-shadow:-4px 0 20px rgba(0,0,0,.3);z-index:9996;display:flex;flex-direction:column;transform:translateX(100%);transition:transform .3s ease-out;}
.hist-panel.open{transform:translateX(0);}
.hist-header{display:flex;justify-content:space-between;align-items:center;padding:1rem 1.2rem;border-bottom:1px solid rgba(255,255,255,.1);}
.hist-header h3{font-size:1rem;font-weight:700;display:flex;align-items:center;gap:.5rem;}
.hist-header h3 i{color:#61B3FA;}
.hist-close{background:none;border:none;color:rgba(255,255,255,.6);font-size:1.1rem;cursor:pointer;transition:color .2s;}
.hist-close:hover{color:#fff;}
.hist-body{flex:1;overflow-y:auto;padding:1rem;}
.hist-item{display:flex;gap:.8rem;padding:.8rem 0;border-bottom:1px solid rgba(255,255,255,.05);}
.hist-dot{width:30px;height:30px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:.7rem;}
.hist-dot.en_attente{background:rgba(241,196,15,.2);color:#f1c40f;}
.hist-dot.en_cours{background:rgba(52,152,219,.2);color:#3498db;}
.hist-dot.resolue{background:rgba(39,174,96,.2);color:#27ae60;}
.hist-dot.rejetee{background:rgba(231,76,60,.2);color:#e74c3c;}
.hist-info{flex:1;}
.hi-title{font-size:.8rem;font-weight:600;margin-bottom:.2rem;}
.hi-date{font-size:.65rem;color:rgba(255,255,255,.5);}
.hist-empty{text-align:center;padding:2rem;color:rgba(255,255,255,.4);}
.hist-empty i{font-size:2rem;margin-bottom:.5rem;display:block;}
</style>
</head>
<body>
<div class="wrap">
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="/ecoride/View/backoffice/admin.php" class="logo">
                <img src="assets/images/photo.png" alt="EcoRide Logo" class="logo-img">
                <div class="logo-text">EcoRide</div>
                <div class="logo-tagline">ADMINISTRATION</div>
            </a>
        </div>
        
        <div class="nav-section">GESTION</div>
        <nav>
            <ul>
                <li><a href="/ecoride/View/backoffice/admin.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="/ecoride/View/backoffice/admin_trajet.php?page=passagers"><i class="fas fa-users"></i> Passagers</a></li>
                <li><a href="/ecoride/View/backoffice/admin_trajet.php?page=trajets"><i class="fas fa-route"></i> Trajets</a></li>
                <li><a href="/ecoride/View/backoffice/admin_trajet.php?page=destinations"><i class="fas fa-map-pin"></i> Destinations</a></li>
                <li><a href="/ecoride/View/backoffice/admin_trajet.php?page=evenements"><i class="fas fa-calendar-alt"></i> Événements</a></li>
                <li><a href="/ecoride/admin_reclamations.php" class="active"><i class="fas fa-exclamation-triangle"></i> Réclamations</a></li>
                <li><a href="/ecoride/View/backoffice/admin.php"><i class="fas fa-car"></i> Véhicules</a></li>
                <li><a href="/ecoride/View/backoffice/lostfound_admin.php"><i class="fas fa-search-location"></i> Lost &amp; Found</a></li>
            </ul>
        </nav>
        
        <div class="sidebar-footer">
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </div>
    </aside>

    <main class="main">
        <div class="admin-header">
            <div class="admin-logo">
                <div class="logo-eco"><span>ECO</span> <span>RIDE</span></div>
                <div class="logo-tagline">Covoiturage Intelligent</div>
            </div>
            <div class="admin-nav">
                <a href="/ecoride/View/frontoffice/tous_les_trajets.php">Voir site</a>
                <a href="profil.php" class="profile-btn">
                    <div class="profile-avatar"><i class="fas fa-user"></i></div>
                    <span>Profil</span>
                </a>
                <a href="/ecoride/View/backoffice/admin.php" class="admin-btn">Admin</a>
                <button class="theme-btn" onclick="toggleTheme()" id="themeToggle">
                    <i class="fas fa-moon"></i>
                </button>
            </div>
        </div>

        <div class="stats">
            <div class="stat"><div class="icon"><i class="fas fa-database"></i></div><div class="num"><?= intval($stats['total']) ?></div><div class="label">Total</div></div>
            <div class="stat"><div class="icon"><i class="fas fa-clock"></i></div><div class="num"><?= intval($stats['en_attente']) ?></div><div class="label">En attente</div></div>
            <div class="stat"><div class="icon"><i class="fas fa-sync-alt"></i></div><div class="num"><?= intval($stats['en_cours']) ?></div><div class="label">En cours</div></div>
            <div class="stat"><div class="icon"><i class="fas fa-check-circle"></i></div><div class="num"><?= intval($stats['resolue']) ?></div><div class="label">Résolues</div></div>
            <div class="stat"><div class="icon"><i class="fas fa-times-circle"></i></div><div class="num"><?= intval($stats['rejetee']) ?></div><div class="label">Rejetées</div></div>
        </div>

        <form method="GET" class="filters">
            <div class="filter-group"><label>Recherche</label><input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Titre, description..."></div>
            <div class="filter-group"><label>Statut</label><select name="statut"><option value="">Tous</option><option value="en_attente" <?= $statut_filter === 'en_attente' ? 'selected' : '' ?>>En attente</option><option value="en_cours" <?= $statut_filter === 'en_cours' ? 'selected' : '' ?>>En cours</option><option value="resolue" <?= $statut_filter === 'resolue' ? 'selected' : '' ?>>Résolue</option><option value="rejetee" <?= $statut_filter === 'rejetee' ? 'selected' : '' ?>>Rejetée</option></select></div>
            <div class="filter-group"><label>Priorité</label><select name="priorite"><option value="">Toutes</option><option value="faible" <?= $priorite_filter === 'faible' ? 'selected' : '' ?>>Faible</option><option value="moyenne" <?= $priorite_filter === 'moyenne' ? 'selected' : '' ?>>Moyenne</option><option value="elevee" <?= $priorite_filter === 'elevee' ? 'selected' : '' ?>>Élevée</option></select></div>
            <div class="filter-group"><button type="submit"><i class="fas fa-filter"></i> Filtrer</button></div>
            <?php if ($search || $statut_filter || $priorite_filter): ?><div class="filter-group"><a href="admin_reclamations.php">Réinitialiser</a></div><?php endif; ?>
        </form>

        <div class="table-wrapper">
            <table cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Utilisateur</th>
                        <th>Réclamation</th>
                        <th>Cat.</th>
                        <th>Prio</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th>Réponse</th>
                        <th colspan="2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reclamations)): ?>
                        <tr><td colspan="10" class="empty">Aucune réclamation trouvée</td></tr>
                    <?php else: foreach ($reclamations as $r): ?>
                        <tr>
                            <td>#<?= intval($r['id']) ?></td>
                            <td><?= htmlspecialchars($r['utilisateur_nom'] ?? 'User #' . intval($r['utilisateur_id'])) ?></td>
                            <td><strong><?= htmlspecialchars($r['titre']) ?></strong><br><small style="color:var(--grey);font-size:.7rem;"><?= htmlspecialchars(mb_strimwidth($r['description'], 0, 50, '...')) ?></small></td>
                            <td><?= htmlspecialchars($r['categorie']) ?></td>
                            <td><span class="prio-<?= htmlspecialchars($r['priorite']) ?>"><?= $r['priorite'] === 'faible' ? 'Faible' : ($r['priorite'] === 'moyenne' ? 'Moyenne' : 'Élevée') ?></span></td>
                            <td>
                                <form method="POST" style="margin:0">
                                    <input type="hidden" name="action" value="update_statut">
                                    <input type="hidden" name="id" value="<?= intval($r['id']) ?>">
                                    <select name="statut" class="status-select" onchange="this.form.submit()">
                                        <option value="en_attente" <?= $r['statut'] === 'en_attente' ? 'selected' : '' ?>>En attente</option>
                                        <option value="en_cours" <?= $r['statut'] === 'en_cours' ? 'selected' : '' ?>>En cours</option>
                                        <option value="resolue" <?= $r['statut'] === 'resolue' ? 'selected' : '' ?>>Résolue</option>
                                        <option value="rejetee" <?= $r['statut'] === 'rejetee' ? 'selected' : '' ?>>Rejetée</option>
                                    </select>
                                </form>
                            </td>
                            <td><?= date('d/m/Y', strtotime($r['date_creation'])) ?></td>
                            <td><?= !empty($r['reponse_admin']) ? '<div class="reponse-text"><i class="fas fa-reply"></i> ' . htmlspecialchars(mb_strimwidth($r['reponse_admin'], 0, 30, '...')) . '</div>' : '—' ?></td>
                            <td class="actions">
                                <a href="#" class="btn-action reply" title="Répondre" onclick='openReplyModal(<?= intval($r['id']) ?>, <?= json_encode($r['titre'], JSON_HEX_APOS|JSON_HEX_QUOT) ?>); return false;'><i class="fas fa-comment-dots"></i></a>
                                <button type="button" class="btn-action edit" title="Modifier" onclick='openEditModal(<?= json_encode($r, JSON_HEX_APOS|JSON_HEX_QUOT) ?>)'><i class="fas fa-edit"></i></button>
                                <form method="POST" style="display:inline;margin:0" onsubmit="return confirm('Supprimer cette réclamation ?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= intval($r['id']) ?>">
                                    <button type="submit" class="btn-action delete" title="Supprimer"><i class="fas fa-trash-alt"></i></button>
                                </form>
                            </td>
                            <td class="actions">
                                <button type="button" class="btn-action history" title="Voir historique" onclick="openHistForClaim(<?= intval($r['id']) ?>)">
                                    <i class="fas fa-history"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">« Précédent</a>
                <?php else: ?>
                    <span class="disabled">« Précédent</span>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i === $page): ?>
                        <span class="active"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                <?php if ($page < $total_pages): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Suivant »</a>
                <?php else: ?>
                    <span class="disabled">Suivant »</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="footer">EcoRide - Panneau d'administration © <?= date('Y') ?></div>
    </main>
</div>

<!-- MODAL RÉPONSE -->
<div id="replyModal" class="modal" aria-hidden="true">
    <div class="modal-content">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.8rem;">
            <h3><i class="fas fa-comment-dots"></i> Répondre</h3>
            <button type="button" onclick="closeModal('replyModal')" style="background:rgba(255,255,255,.08);border:none;color:#fff;width:28px;height:28px;border-radius:50%;cursor:pointer;"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="repondre">
            <input type="hidden" name="id" id="reply_id">
            <div id="reply_reclamation_info" style="background:rgba(97,179,250,.08);border:1px solid rgba(97,179,250,.18);border-radius:10px;padding:0.7rem;margin-bottom:0.8rem;color:#DDEEFF;font-size:.8rem;"></div>
            <label>Votre réponse *</label>
            <textarea id="reply_text" name="reponse_admin" rows="4" placeholder="Rédigez votre réponse..." oninput="updateCharCount(this)"></textarea>
            <div id="charCount" style="text-align:right;font-size:.65rem;color:rgba(255,255,255,.65);margin-bottom:0.8rem;">0/500 caractères</div>
            <label>Changer le statut après réponse</label>
            <div class="stat-options">
                <label class="stat-option"><input type="radio" name="new_statut" value="" checked style="display:none;"> Garder</label>
                <label class="stat-option"><input type="radio" name="new_statut" value="en_cours" style="display:none;"> En cours</label>
                <label class="stat-option"><input type="radio" name="new_statut" value="resolue" style="display:none;"> Résolue</label>
                <label class="stat-option"><input type="radio" name="new_statut" value="rejetee" style="display:none;"> Rejetée</label>
            </div>
            <div class="modal-buttons">
                <button type="button" class="btn-cancel" onclick="closeModal('replyModal')">Annuler</button>
                <button type="submit" id="reply_submit_btn" class="btn-save" disabled>Envoyer</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL MODIFIER -->
<div id="editModal" class="modal" aria-hidden="true">
    <div class="modal-content">
        <h3><i class="fas fa-edit"></i> Modifier</h3>
        <form method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" id="edit_id">
            <label>Titre</label>
            <input type="text" id="edit_titre" name="titre" required>
            <label>Description</label>
            <textarea id="edit_description" name="description" rows="3" required></textarea>
            <label>Catégorie</label>
            <select id="edit_categorie" name="categorie" required>
                <option value="technique">Technique</option>
                <option value="paiement">Paiement</option>
                <option value="securite">Sécurité</option>
                <option value="autre">Autre</option>
            </select>
            <label>Priorité</label>
            <select id="edit_priorite" name="priorite" required>
                <option value="faible">Faible</option>
                <option value="moyenne">Moyenne</option>
                <option value="elevee">Élevée</option>
            </select>
            <label>Statut</label>
            <select id="edit_statut" name="statut" required>
                <option value="en_attente">En attente</option>
                <option value="en_cours">En cours</option>
                <option value="resolue">Résolue</option>
                <option value="rejetee">Rejetée</option>
            </select>
            <div class="modal-buttons">
                <button type="button" class="btn-cancel" onclick="closeModal('editModal')">Annuler</button>
                <button type="submit" class="btn-save">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<!-- BOUTON STATISTIQUES -->
<div class="fab-stats">
    <a href="statistiques.php" class="fab-stats-btn" title="Statistiques">
        <i class="fas fa-chart-pie"></i>
        <span class="fab-tooltip">📊 Statistiques</span>
    </a>
</div>

<!-- BOUTON CHATBOT -->
<button class="chat-fab" id="chatFab" onclick="toggleChat()" title="Assistant IA">
    <i class="fas fa-robot"></i>
    <span class="chat-badge">IA</span>
</button>

<!-- FENÊTRE CHATBOT -->
<div class="chatbot-window" id="chatbotWindow">
    <div class="cbot-header">
        <div class="cbot-avatar"><i class="fas fa-robot"></i></div>
        <div class="cbot-title">
            <strong>Assistant EcoRide</strong>
            <span>Gestion des réclamations</span>
        </div>
        <button class="cbot-close" onclick="toggleChat()"><i class="fas fa-times"></i></button>
    </div>
    <div class="cbot-messages" id="cbotMessages">
        <div class="cbot-msg bot">👋 Bonjour ! Tapez <strong>aide</strong> pour voir les commandes disponibles.</div>
    </div>
    <div class="cbot-quick">
        <button class="cbot-chip" onclick="sendQuick('statistiques')">📊 Stats</button>
        <button class="cbot-chip" onclick="sendQuick('urgentes')">🔴 Urgentes</button>
        <button class="cbot-chip" onclick="sendQuick('en attente')">⏳ Attente</button>
        <button class="cbot-chip" onclick="sendQuick('dernières réclamations')">📋 Récentes</button>
        <button class="cbot-chip" onclick="sendQuick('aide')">❓ Aide</button>
    </div>
    <div class="cbot-input-row">
        <input class="cbot-input" id="cbotInput" type="text" placeholder="Votre message..." autocomplete="off" onkeydown="if(event.key==='Enter') sendMsg()">
        <button class="cbot-send" id="cbotSendBtn" onclick="sendMsg()"><i class="fas fa-paper-plane"></i></button>
    </div>
</div>

<!-- PANEL HISTORIQUE - Version latérale droite -->
<div id="histOverlay" onclick="closeHist()"></div>
<div id="histPanel">
    <div class="hist-header">
        <h3><i class="fas fa-history"></i> Historique</h3>
        <div style="display:flex; gap:8px;">
            <button onclick="openHistAll()" style="background:rgba(97,179,250,.2); border:none; color:#61B3FA; font-size:10px; padding:3px 8px; border-radius:16px; cursor:pointer;">Tout voir</button>
            <button class="hist-close" onclick="closeHist()"><i class="fas fa-times"></i></button>
        </div>
    </div>
    <div class="hist-body" id="histBody">
        <?php if(empty($reclamations)): ?>
        <div class="hist-empty"><i class="fas fa-clock"></i>Aucune réclamation</div>
        <?php else: foreach($reclamations as $r): ?>
        <div class="hist-item" data-claim-id="<?= $r['id'] ?>">
            <div class="hist-dot <?= $r['statut'] ?>">
                <i class="fas <?= $r['statut']=='resolue'?'fa-check':($r['statut']=='en_cours'?'fa-sync-alt':($r['statut']=='rejetee'?'fa-times':'fa-clock')) ?>"></i>
            </div>
            <div class="hist-info">
                <div class="hi-title">#<?= $r['id'] ?> - <?= htmlspecialchars($r['titre']) ?></div>
                <div class="hi-date"><?= date('d/m/Y H:i', strtotime($r['date_creation'])) ?></div>
                <?php if(!empty($r['reponse_admin'])): ?>
                <div class="hi-date" style="color:#27ae60; margin-top:2px;"><i class="fas fa-reply"></i> <?= htmlspecialchars(substr($r['reponse_admin'],0,40)) ?>...</div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; endif; ?>
    </div>
</div>

<script>
// Fonctions existantes
function openReplyModal(id, titre) {
    document.getElementById('reply_id').value = id;
    document.getElementById('reply_reclamation_info').innerHTML = '<strong>Réclamation #' + id + '</strong>' + (titre ? ' à ' + titre : '');
    document.getElementById('reply_text').value = '';
    updateCharCount(document.getElementById('reply_text'));
    document.getElementById('replyModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}
function openEditModal(reclamation) {
    document.getElementById('edit_id').value = reclamation.id;
    document.getElementById('edit_titre').value = reclamation.titre;
    document.getElementById('edit_description').value = reclamation.description;
    document.getElementById('edit_categorie').value = reclamation.categorie;
    document.getElementById('edit_priorite').value = reclamation.priorite;
    document.getElementById('edit_statut').value = reclamation.statut;
    document.getElementById('editModal').classList.add('active');
}
function closeModal(id) {
    document.getElementById(id).classList.remove('active');
    document.body.style.overflow = '';
}
function updateCharCount(el) {
    var len = el.value.trim().length;
    var countEl = document.getElementById('charCount');
    if (countEl) countEl.textContent = len + '/500 caractères';
    var btn = document.getElementById('reply_submit_btn');
    if (btn) {
        if (len >= 10) { btn.disabled = false; btn.style.opacity = '1'; btn.style.cursor = 'pointer'; }
        else { btn.disabled = true; btn.style.opacity = '.5'; btn.style.cursor = 'not-allowed'; }
    }
}
function changeLanguage(select) {
    var url = new URL(window.location.href);
    url.searchParams.set('lang', select.value);
    window.location.href = url.toString();
}
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal')) {
        e.target.classList.remove('active');
    }
});
function toggleTheme() {
    var themeBtn = document.getElementById('themeToggle');
    document.body.classList.toggle('light-mode');
    var isLight = document.body.classList.contains('light-mode');
    localStorage.setItem('theme', isLight ? 'light' : 'dark');
    if (themeBtn) themeBtn.innerHTML = isLight ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
}
var themeBtn = document.getElementById('themeToggle');
if (themeBtn && localStorage.getItem('theme') === 'light') {
    document.body.classList.add('light-mode');
    themeBtn.innerHTML = '<i class="fas fa-sun"></i>';
}
document.querySelectorAll('.stat-option').forEach(function(option) {
    option.addEventListener('click', function() {
        document.querySelectorAll('.stat-option').forEach(function(el) { el.classList.remove('active'); });
        this.classList.add('active');
        var input = this.querySelector('input');
        if (input) input.checked = true;
    });
});

// CHATBOT
var chatOpen = false;
function toggleChat() {
    chatOpen = !chatOpen;
    var w = document.getElementById('chatbotWindow');
    if (chatOpen) { w.classList.add('open'); document.getElementById('cbotInput').focus(); }
    else { w.classList.remove('open'); }
}
function addMsg(text, role) {
    var box = document.getElementById('cbotMessages');
    var div = document.createElement('div');
    div.className = 'cbot-msg ' + role;
    div.textContent = text;
    box.appendChild(div);
    box.scrollTop = box.scrollHeight;
}
function showTyping() {
    var box = document.getElementById('cbotMessages');
    var t = document.createElement('div');
    t.className = 'cbot-typing';
    t.id = 'cbotTyping';
    t.innerHTML = '<span></span><span></span><span></span>';
    box.appendChild(t);
    box.scrollTop = box.scrollHeight;
}
function removeTyping() {
    var t = document.getElementById('cbotTyping');
    if (t) t.remove();
}
function sendQuick(txt) {
    document.getElementById('cbotInput').value = txt;
    sendMsg();
}
function sendMsg() {
    var input = document.getElementById('cbotInput');
    var msg = input.value.trim();
    if (!msg) return;
    input.value = '';
    document.getElementById('cbotSendBtn').disabled = true;
    addMsg(msg, 'user');
    showTyping();
    fetch('ChatbotController.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: msg })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        removeTyping();
        addMsg(data.reply || '❌ Réponse invalide.', 'bot');
    })
    .catch(function() {
        removeTyping();
        addMsg('❌ Erreur de connexion au serveur.', 'bot');
    })
    .finally(function() {
        document.getElementById('cbotSendBtn').disabled = false;
        document.getElementById('cbotInput').focus();
    });
}

// HISTORIQUE
function openHist() {
    document.getElementById('histPanel').classList.add('open');
    document.getElementById('histOverlay').classList.add('open');
}
function closeHist() {
    document.getElementById('histPanel').classList.remove('open');
    document.getElementById('histOverlay').classList.remove('open');
}
// Ouvrir l'historique pour une réclamation spécifique
function openHistForClaim(claimId) {
    var items = document.querySelectorAll('.hist-item');
    var found = false;
    items.forEach(function(item) {
        var titleDiv = item.querySelector('.hi-title');
        if (titleDiv && titleDiv.textContent.includes('#' + claimId)) {
            item.style.display = 'flex';
            found = true;
        } else {
            item.style.display = 'none';
        }
    });
    var histBody = document.getElementById('histBody');
    var emptyMsg = histBody.querySelector('.hist-empty');
    if (!found) {
        if (emptyMsg) {
            emptyMsg.style.display = 'block';
            emptyMsg.innerHTML = '<i class="fas fa-clock"></i>Aucun historique pour cette réclamation.';
        } else {
            histBody.innerHTML = '<div class="hist-empty"><i class="fas fa-clock"></i>Aucun historique pour cette réclamation.</div>';
        }
    } else if (emptyMsg) {
        emptyMsg.style.display = 'none';
    }
    openHist();
}
// Afficher tout l'historique
function openHistAll() {
    var items = document.querySelectorAll('.hist-item');
    items.forEach(function(item) {
        item.style.display = 'flex';
    });
    var histBody = document.getElementById('histBody');
    var emptyMsg = histBody.querySelector('.hist-empty');
    if (emptyMsg) emptyMsg.style.display = 'none';
    openHist();
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeHist();
});
</script>
</body>
</html>