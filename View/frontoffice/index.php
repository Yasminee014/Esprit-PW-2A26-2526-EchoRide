<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /ecoride/View/frontoffice/login.php?show=showLogin');
    exit;
}
try {
    $pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=ecoride;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) { die("Erreur BDD : " . $e->getMessage()); }

$user_id = (int)$_SESSION['user_id'];
$message = $erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'ajouter') {
        $titre = trim($_POST['titre']); $description = trim($_POST['description']);
        $categorie = $_POST['categorie']; $priorite = $_POST['priorite'];
        if (strlen($titre)>=3 && strlen($description)>=10) {
            $stmt = $pdo->prepare("INSERT INTO reclamations (utilisateur_id,titre,description,categorie,priorite) VALUES (?,?,?,?,?)");
            if ($stmt->execute([$user_id,$titre,$description,$categorie,$priorite])) $message="Réclamation ajoutée avec succès !";
            else $erreur="Erreur lors de l'ajout";
        } else $erreur="Titre (min 3) et description (min 10) requis";
    } elseif ($action==='supprimer') {
        $stmt=$pdo->prepare("DELETE FROM reclamations WHERE id=? AND utilisateur_id=?");
        if ($stmt->execute([$_POST['id'],$user_id])) { header('Location: index.php'); exit; }
    } elseif ($action==='modifier') {
        $stmt=$pdo->prepare("UPDATE reclamations SET titre=?,description=? WHERE id=? AND utilisateur_id=?");
        if ($stmt->execute([trim($_POST['titre']),trim($_POST['description']),$_POST['id'],$user_id])) { header('Location: index.php'); exit; }
    }
}
$stmt=$pdo->prepare("SELECT * FROM reclamations WHERE utilisateur_id=? ORDER BY date_creation DESC");
$stmt->execute([$user_id]);
$reclamations=$stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Mes Réclamations - EcoRide</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

:root {
    --blue: #1976D2;
    --blue-light: #61B3FA;
    --dark: #0A1628;
    --dark2: #0D1F3A;
    --dark3: #0F3B6E;
    --white: #F4F5F7;
    --grey: #A7A9AC;
    --green: #27ae60;
    --red: #e74c3c;
    --yellow: #f1c40f;
    --orange: #e67e22;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #0A1628;
    color: #fff;
    min-height: 100vh;
    transition: background .3s, color .3s;
}

body.light-mode {
    background: #f5f5f5;
    color: #333;
}

/* ========== NAVBAR (identique à user.php) ========== */
.navbar-modern {
    background: linear-gradient(135deg, #1976D2 0%, #0F3B6E 100%);
    padding: 1.2rem 5%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 1000;
    flex-wrap: wrap;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.navbar-modern .logo {
    display: flex;
    flex-direction: row !important;
    align-items: center;
    gap: 10px;
    text-decoration: none;
}

.navbar-modern .logo-img {
    width: 56px;
    height: 56px;
    object-fit: contain;
    filter: drop-shadow(0 2px 8px rgba(0,0,0,0.5));
    transition: transform 0.3s ease;
}

.navbar-modern .logo:hover .logo-img {
    transform: scale(1.08) rotate(-3deg);
}

.navbar-modern .logo-text {
    font-size: 1.6rem;
    font-weight: 700;
    letter-spacing: 1px;
    color: white;
    line-height: 1.3;
}

.navbar-modern .logo-tagline {
    font-size: 0.65rem;
    color: rgba(255,255,255,0.7);
    letter-spacing: 0.5px;
}

.menu-toggle {
    background: rgba(255,255,255,0.15);
    border: none;
    color: white;
    font-size: 1.2rem;
    padding: 0.5rem 1rem;
    border-radius: 25px;
    cursor: pointer;
    display: none;
    transition: all 0.3s;
}

.menu-toggle:hover {
    background: rgba(255,255,255,0.25);
}

.nav-links {
    display: flex;
    gap: 0.8rem;
    list-style: none;
    margin: 0;
    padding: 0;
    align-items: center;
    flex-wrap: wrap;
}

.nav-links li a {
    text-decoration: none;
    padding: 0.5rem 1.2rem;
    border-radius: 30px;
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: transparent;
    color: white;
    border: none;
    cursor: pointer;
}

.nav-links li a:hover {
    background: rgba(255,255,255,0.2);
    transform: translateY(-2px);
}

.nav-links li a.active {
    background: #0A1628;
    color: white;
    box-shadow: 0 2px 8px rgba(10,22,40,0.3);
}

.nav-links .admin-btn {
    background: rgba(231,76,60,0.2);
    border: 1px solid rgba(231,76,60,0.4);
    color: #e74c3c;
    padding: 0.5rem 1.2rem;
    border-radius: 30px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.3s;
}

.nav-links .admin-btn:hover {
    background: rgba(231,76,60,0.35);
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(231,76,60,0.2);
}

.profile-btn {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #2F6FA5;
    border: none;
    padding: 0.5rem 1.2rem;
    border-radius: 30px;
    cursor: pointer;
    transition: all 0.3s;
    color: #FFFFFF;
    font-size: 0.9rem;
    font-weight: 500;
}

.profile-btn:hover {
    background: #3C82C4;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(60,130,196,0.3);
}

.profile-avatar {
    width: 28px;
    height: 28px;
    background: #5FA8E0;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.profile-avatar i {
    font-size: 0.8rem;
    color: #FFFFFF;
}

.theme-btn {
    background: rgba(255,255,255,0.15);
    border: none;
    color: white;
    width: 38px;
    height: 38px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 1.1rem;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.theme-btn:hover {
    background: rgba(255,255,255,0.3);
    transform: rotate(15deg);
}

.profile-dropdown {
    position: relative;
}

.dropdown-menu {
    display: none;
    position: absolute;
    top: 100%;
    right: 0;
    width: 280px;
    background: #0F2A44;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    z-index: 1000;
    overflow: hidden;
    margin-top: 10px;
    backdrop-filter: blur(10px);
}

.dropdown-menu.show {
    display: block;
    animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.dropdown-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 1rem;
    background: #163A5C;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}

.dropdown-header .avatar {
    width: 45px;
    height: 45px;
    background: #5FA8E0;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.dropdown-header .avatar i {
    font-size: 1.2rem;
    color: white;
}

.dropdown-header .user-info {
    display: flex;
    flex-direction: column;
}

.dropdown-header .user-name {
    font-size: 0.95rem;
    font-weight: 600;
    color: #CFE6FF;
}

.dropdown-header .user-role {
    font-size: 0.65rem;
    color: rgba(207,230,255,0.7);
}

.dropdown-links {
    padding: 0.5rem 0;
}

.dropdown-links a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 0.7rem 1rem;
    margin: 0 0.5rem;
    border-radius: 10px;
    color: #CFE6FF;
    background: transparent;
    text-decoration: none;
    font-size: 0.85rem;
    transition: all 0.2s;
}

.dropdown-links a i {
    width: 22px;
    color: #5FA8E0;
    font-size: 1rem;
}

.dropdown-links a:hover {
    background: rgba(255,255,255,0.05);
}

.dropdown-links a.active {
    background: #1E4F7A;
    position: relative;
}

.dropdown-links a.active::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 3px;
    background: #5FA8E0;
    border-radius: 0 3px 3px 0;
}

.dropdown-divider {
    height: 1px;
    background: rgba(255,255,255,0.08);
    margin: 0.5rem 0;
}

.dropdown-actions a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 0.7rem 1rem;
    margin: 0 0.5rem 0.5rem 0.5rem;
    border-radius: 10px;
    color: #FF5C5C;
    background: transparent;
    text-decoration: none;
    font-size: 0.85rem;
    transition: all 0.2s;
}

.dropdown-actions a i {
    width: 22px;
    color: #FF5C5C;
    font-size: 1rem;
}

.dropdown-actions a:hover {
    background: rgba(255,92,92,0.15);
}

body.light-mode .navbar-modern {
    background: linear-gradient(135deg, #1565C0, #0D47A1);
}

@media (max-width: 768px) {
    .navbar-modern {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
        padding: 1rem;
    }

    .menu-toggle {
        display: block;
        position: absolute;
        top: 1rem;
        right: 1rem;
    }

    .nav-links {
        display: none;
        width: 100%;
        flex-direction: column;
        margin-top: 1rem;
        gap: 0.8rem;
    }

    .nav-links.show {
        display: flex;
    }

    .profile-dropdown {
        width: 100%;
    }

    .dropdown-menu {
        position: static;
        width: 100%;
        margin-top: 8px;
    }
}

/* ========== HERO ========== */
.hero-wrapper {
    padding: 1.8rem 5% 0;
}

.hero {
    background: linear-gradient(120deg, #1256b4 0%, #1976D2 55%, #0d47a1 100%);
    padding: 2rem 2.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    min-height: 120px;
    overflow: hidden;
    position: relative;
    border-radius: 20px;
}

.hero-icon-bg {
    font-size: 5.5rem;
    color: rgba(255,255,255,.13);
    flex-shrink: 0;
    line-height: 1;
}

.hero h1 {
    font-size: 1.7rem;
    font-weight: 800;
    margin-bottom: .3rem;
    display: flex;
    align-items: center;
    gap: .6rem;
}

.hero h1 i {
    font-size: 1.5rem;
    color: #fff;
}

.hero h1 span {
    color: #90CAF9;
}

.hero p {
    font-size: .85rem;
    color: rgba(255,255,255,.6);
}

/* ========== BODY ========== */
.page-body {
    padding: 2rem 5%;
}

.section-label {
    display: flex;
    align-items: center;
    gap: .7rem;
    margin-bottom: 1.1rem;
}

.section-label .icon-box {
    width: 32px;
    height: 32px;
    background: rgba(25,118,210,.22);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #61B3FA;
}

.sl-text h2 {
    font-size: .95rem;
    font-weight: 700;
}

.sl-text p {
    font-size: .72rem;
    color: rgba(255,255,255,.38);
}

/* TWO COL */
.two-col {
    display: flex;
    gap: 1rem;
    align-items: flex-start;
    flex-wrap: wrap;
}

/* FORM PANEL */
.form-panel {
    width: 270px;
    flex-shrink: 0;
    background: #111f38;
    border-radius: 16px;
    padding: 1.2rem;
}

.form-panel-title {
    display: flex;
    align-items: center;
    gap: .5rem;
    font-size: .86rem;
    font-weight: 700;
    padding-bottom: .75rem;
    margin-bottom: .9rem;
    border-bottom: 1px solid rgba(255,255,255,.07);
}

.form-panel-title i {
    color: #61B3FA;
}

.tab-buttons {
    display: flex;
    gap: .4rem;
    margin-bottom: 1rem;
}

.tab-btn {
    flex: 1;
    padding: .45rem;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    font-size: .78rem;
    font-weight: 600;
    transition: .2s;
}

.tab-btn.primary {
    background: #1976D2;
    color: #fff;
}

.tab-btn.ghost {
    background: rgba(255,255,255,.07);
    color: rgba(255,255,255,.6);
}

.tab-btn.ghost:hover {
    background: rgba(255,255,255,.12);
}

.form-group {
    margin-bottom: .78rem;
}

.form-group label {
    display: flex;
    align-items: center;
    gap: .3rem;
    font-size: .7rem;
    color: rgba(255,255,255,.42);
    margin-bottom: .26rem;
}

.form-group label i {
    font-size: .66rem;
    color: #61B3FA;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: .55rem .72rem;
    border-radius: 9px;
    border: 1px solid rgba(97,179,250,.12);
    background: rgba(255,255,255,.05);
    color: #fff;
    font-size: .81rem;
    transition: .2s;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: rgba(97,179,250,.4);
    background: rgba(97,179,250,.06);
}

.form-group input::placeholder,
.form-group textarea::placeholder {
    color: rgba(255,255,255,.2);
}

.form-group select option {
    background: #111f38;
}

.form-group textarea {
    resize: vertical;
    min-height: 75px;
}

.btn-submit {
    width: 100%;
    padding: .65rem;
    border: none;
    border-radius: 10px;
    background: linear-gradient(135deg, #1256b4, #1976D2);
    color: #fff;
    font-size: .85rem;
    font-weight: 700;
    cursor: pointer;
    transition: .2s;
}

.btn-submit:hover {
    opacity: .87;
}

/* TABLE PANEL */
.table-panel {
    flex: 1;
    min-width: 0;
}

.table-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: .85rem;
    flex-wrap: wrap;
    gap: .5rem;
}

.table-header h3 {
    font-size: .88rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: .4rem;
}

.table-header h3 i {
    color: #61B3FA;
}

.table-tools {
    display: flex;
    gap: .4rem;
    align-items: center;
}

.search-box {
    display: flex;
    align-items: center;
    gap: .32rem;
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.09);
    border-radius: 8px;
    padding: .36rem .65rem;
}

.search-box input {
    background: none;
    border: none;
    color: #fff;
    font-size: .78rem;
    outline: none;
    width: 115px;
}

.search-box input::placeholder {
    color: rgba(255,255,255,.25);
}

.search-box i {
    color: rgba(255,255,255,.25);
    font-size: .75rem;
}

.sort-select {
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.09);
    color: #fff;
    font-size: .78rem;
    border-radius: 8px;
    padding: .36rem .6rem;
    cursor: pointer;
}

.sort-select option {
    background: #111f38;
}

.act {
    display: flex;
    gap: .25rem;
    align-items: center;
}

.bv, .bh, .be, .bd {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: .75rem;
    transition: all .2s;
    flex-shrink: 0;
}

.bv {
    background: rgba(59,130,246,.18);
    color: #60a5fa;
    border: 1px solid rgba(59,130,246,.25);
}

.bv:hover {
    background: rgba(59,130,246,.32);
    transform: translateY(-2px);
}

.bh {
    background: rgba(168,85,247,.18);
    color: #c084fc;
    border: 1px solid rgba(168,85,247,.25);
}

.bh:hover {
    background: rgba(168,85,247,.32);
    transform: translateY(-2px);
}

.be {
    background: rgba(59,130,246,.18);
    color: #60a5fa;
    border: 1px solid rgba(59,130,246,.28);
}

.be:hover {
    background: rgba(59,130,246,.32);
    transform: translateY(-2px);
}

.bd {
    background: rgba(239,68,68,.12);
    color: #f87171;
    border: 1px solid rgba(239,68,68,.2);
}

.bd:hover {
    background: rgba(239,68,68,.28);
    transform: translateY(-2px);
}

td {
    padding: .45rem .6rem;
    font-size: .76rem;
    border-bottom: 1px solid rgba(255,255,255,.04);
    vertical-align: middle;
    word-break: break-word;
    max-width: 0;
}

.table-wrap {
    background: #111f38;
    border-radius: 14px;
    overflow: hidden;
    border: 1px solid rgba(97,179,250,.08);
}

table {
    width: 100%;
    border-collapse: collapse;
    min-width: 0;
}

thead tr {
    background: rgba(25,118,210,.12);
}

th {
    padding: .5rem .6rem;
    text-align: left;
    font-size: .73rem;
    font-weight: 700;
    color: #61B3FA;
    border-bottom: 1px solid rgba(97,179,250,.15);
    white-space: normal;
    cursor: pointer;
}

th i {
    margin-left: .3rem;
    font-size: .7rem;
    opacity: 0.6;
}

tbody tr {
    border-bottom: 1px solid rgba(255,255,255,.05);
    transition: .15s;
}

tbody tr:last-child {
    border-bottom: none;
}

tbody tr:hover {
    background: rgba(97,179,250,.05);
}

td {
    padding: .8rem 1rem;
    font-size: .85rem;
    vertical-align: middle;
}

.bid {
    background: rgba(25,118,210,.2);
    color: #61B3FA;
    border-radius: 6px;
    padding: .2rem .5rem;
    font-size: .85rem;
    font-weight: 600;
    display: inline-block;
}

.bst {
    padding: .2rem .6rem;
    border-radius: 20px;
    font-size: .75rem;
    white-space: nowrap;
    display: inline-block;
}

.bst.en_attente {
    background: rgba(241,196,15,.15);
    color: #f1c40f;
}

.bst.en_cours {
    background: rgba(52,152,219,.15);
    color: #3498db;
}

.bst.resolue {
    background: rgba(39,174,96,.15);
    color: #27ae60;
}

.bst.rejetee {
    background: rgba(231,76,60,.15);
    color: #e74c3c;
}

.rep-box {
    background: rgba(39,174,96,.1);
    padding: .3rem .5rem;
    border-radius: 6px;
    font-size: .75rem;
    max-width: 150px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    cursor: help;
}

.act {
    display: flex;
    gap: .5rem;
    align-items: center;
}

.empty {
    text-align: center;
    padding: 2.5rem;
    color: rgba(255,255,255,.25);
    font-size: .85rem;
}

.empty i {
    font-size: 2rem;
    margin-bottom: .5rem;
    display: block;
}

/* ALERTS */
.alert {
    padding: .8rem 1rem;
    border-radius: 10px;
    margin: 1rem 5% 0;
    font-size: .85rem;
    display: flex;
    align-items: center;
    gap: .5rem;
}

.alert-success {
    background: rgba(39,174,96,.15);
    border: 1px solid #27ae60;
    color: #27ae60;
}

.alert-error {
    background: rgba(231,76,60,.15);
    border: 1px solid #e74c3c;
    color: #e74c3c;
}

/* MODAL */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.8);
    justify-content: center;
    align-items: center;
    z-index: 2000;
}

.modal-box {
    background: #111f38;
    padding: 1.5rem;
    border-radius: 16px;
    width: 90%;
    max-width: 450px;
    border: 1px solid rgba(97,179,250,.15);
}

.modal-box h3 {
    font-size: 1rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: .5rem;
    color: #61B3FA;
}

.modal-btns {
    display: flex;
    justify-content: flex-end;
    gap: .5rem;
    margin-top: 1rem;
}

.btn-cancel {
    background: rgba(255,255,255,.1);
    border: none;
    color: #fff;
    padding: .5rem 1rem;
    border-radius: 8px;
    cursor: pointer;
}

.btn-save {
    background: #1976D2;
    border: none;
    color: #fff;
    padding: .5rem 1rem;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
}

.btn-save:hover {
    background: #1565C0;
}

/* HISTORIQUE PANEL */
.hist-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,.6);
    z-index: 500;
}

.hist-overlay.open {
    display: block;
}

.hist-panel {
    position: fixed;
    right: 0;
    top: 0;
    height: 100%;
    width: 400px;
    background: #0f1e36;
    border-left: 1px solid rgba(97,179,250,.18);
    z-index: 501;
    display: flex;
    flex-direction: column;
    transform: translateX(100%);
    transition: transform .3s ease;
}

.hist-panel.open {
    transform: translateX(0);
}

.hist-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.2rem 1.4rem;
    border-bottom: 1px solid rgba(255,255,255,.07);
}

.hist-header h3 {
    font-size: .95rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: .5rem;
}

.hist-header h3 i {
    color: #61B3FA;
}

.hist-close {
    background: none;
    border: none;
    color: rgba(255,255,255,.5);
    font-size: 1.1rem;
    cursor: pointer;
}

.hist-close:hover {
    color: #fff;
}

.hist-body {
    flex: 1;
    overflow-y: auto;
    padding: 1rem 1.2rem;
}

.hist-item {
    display: flex;
    align-items: flex-start;
    gap: .8rem;
    padding: .8rem 0;
    border-bottom: 1px solid rgba(255,255,255,.05);
}

.hist-item:last-child {
    border-bottom: none;
}

.hist-dot {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .75rem;
}

.hist-dot.added {
    background: rgba(39,174,96,.2);
    color: #27ae60;
}

.hist-dot.modified {
    background: rgba(241,196,15,.2);
    color: #f1c40f;
}

.hist-dot.deleted {
    background: rgba(231,76,60,.2);
    color: #e74c3c;
}

.hist-dot.status {
    background: rgba(52,152,219,.2);
    color: #3498db;
}

.hist-info .hi-title {
    font-size: .83rem;
    font-weight: 600;
    margin-bottom: .2rem;
}

.hist-info .hi-date {
    font-size: .7rem;
    color: rgba(255,255,255,.4);
}

.hist-empty {
    text-align: center;
    padding: 3rem 1rem;
    color: rgba(255,255,255,.3);
}

.hist-empty i {
    font-size: 2rem;
    margin-bottom: .5rem;
    display: block;
}
</style>
</head>
<body>
<?php include_once __DIR__ . '/partials/navbar.php'; ?>

<div class="hero-wrapper">
<div class="hero">
    <div>
        <h1><i class="fas fa-exclamation-triangle"></i> Gérez vos <span>réclamations</span></h1>
        <p>Publiez, suivez et gérez vos réclamations en quelques secondes</p>
    </div>
    <div class="hero-icon-bg"><i class="fas fa-exclamation-triangle"></i></div>
</div>
</div>

<?php if($message): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($message) ?></div>
<?php endif; ?>
<?php if($erreur): ?>
<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($erreur) ?></div>
<?php endif; ?>

<div class="page-body">
    <div class="section-label">
        <div class="icon-box"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="sl-text"><h2>Mes réclamations</h2><p>Gérez vos réclamations publiées</p></div>
    </div>

    <div class="two-col">

        <!-- FORMULAIRE -->
        <div class="form-panel">
            <div class="form-panel-title"><i class="fas fa-exclamation-circle"></i> Gestion des réclamations</div>
            <div class="tab-buttons">
                <button class="tab-btn primary" id="tabAjouter" onclick="showTab('ajouter')"><i class="fas fa-plus"></i> Ajouter</button>
                <button class="tab-btn ghost" id="tabRechercher" onclick="showTab('rechercher')"><i class="fas fa-search"></i> Rechercher</button>
            </div>
            <form method="POST" id="form-ajouter">
                <input type="hidden" name="action" value="ajouter">
                <div class="form-group">
                    <label><i class="fas fa-heading"></i> Titre * (min 3 caractères)</label>
                    <input type="text" name="titre" placeholder="Ex: Problème de paiement" required>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-align-left"></i> Description * (min 10 caractères)</label>
                    <textarea name="description" placeholder="Décrivez votre problème..." rows="3" required></textarea>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-tag"></i> Catégorie</label>
                    <select name="categorie">
                        <option value="technique">🔧 Technique</option>
                        <option value="paiement">💰 Paiement</option>
                        <option value="securite">🔒 Sécurité</option>
                        <option value="autre">📝 Autre</option>
                    </select>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-flag"></i> Priorité</label>
                    <select name="priorite">
                        <option value="faible">🟢 Faible</option>
                        <option value="moyenne" selected>🟡 Moyenne</option>
                        <option value="elevee">🔴 Élevée</option>
                    </select>
                </div>
                <button type="submit" class="btn-submit"><i class="fas fa-paper-plane"></i> Publier la réclamation</button>
            </form>
            <div id="form-rechercher" style="display:none">
                <div class="form-group">
                    <label><i class="fas fa-search"></i> Rechercher par titre</label>
                    <input type="text" id="searchSide" placeholder="Ex: paiement..." oninput="filterTable()">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-filter"></i> Filtrer par statut</label>
                    <select id="filterStatut" onchange="filterTable()">
                        <option value="">Tous les statuts</option>
                        <option value="en_attente">⏳ En attente</option>
                        <option value="en_cours">🔄 En cours</option>
                        <option value="resolue">✅ Résolue</option>
                        <option value="rejetee">❌ Rejetée</option>
                    </select>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-flag"></i> Filtrer par priorité</label>
                    <select id="filterPriorite" onchange="filterTable()">
                        <option value="">Toutes les priorités</option>
                        <option value="faible">🟢 Faible</option>
                        <option value="moyenne">🟡 Moyenne</option>
                        <option value="elevee">🔴 Élevée</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- TABLEAU -->
        <div class="table-panel">
            <div class="table-header">
                <h3><i class="fas fa-list"></i> Mes réclamations publiées</h3>
                <div class="table-tools">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchTop" placeholder="Filtrer..." oninput="filterTable()">
                    </div>
                    <select class="sort-select" id="sortSelect" onchange="sortTable()">
                        <option value="">Trier par...</option>
                        <option value="date_desc">Date ↓</option>
                        <option value="date_asc">Date ↑</option>
                        <option value="priorite">Priorité</option>
                        <option value="statut">Statut</option>
                    </select>
                </div>
            </div>
            <div class="table-wrap">
                <table id="reclamationsTable">
                    <colgroup>
                        <col style="width:38px">
                        <col style="width:150px">
                        <col style="width:70px">
                        <col style="width:68px">
                        <col style="width:85px">
                        <col style="width:68px">
                        <col style="width:100px">
                        <col style="width:128px">
                    </colgroup>
                    <thead>
                        <tr>
                            <th onclick="sortByCol(0)">ID <i class="fas fa-sort"></i></th>
                            <th onclick="sortByCol(1)">Titre <i class="fas fa-sort"></i></th>
                            <th onclick="sortByCol(2)">Catégorie <i class="fas fa-sort"></i></th>
                            <th onclick="sortByCol(3)">Priorité <i class="fas fa-sort"></i></th>
                            <th onclick="sortByCol(4)">Statut <i class="fas fa-sort"></i></th>
                            <th onclick="sortByCol(5)">Date <i class="fas fa-sort"></i></th>
                            <th>Réponse admin</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <?php if(empty($reclamations)): ?>
                        <tr><td colspan="8"><div class="empty"><i class="fas fa-inbox"></i>Aucune réclamation trouvée</div></td></tr>
                        <?php else: ?>
                        <?php foreach($reclamations as $r): ?>
                        <tr data-titre="<?= strtolower(htmlspecialchars($r['titre'])) ?>"
                            data-statut="<?= $r['statut'] ?>"
                            data-priorite="<?= $r['priorite'] ?>"
                            data-date="<?= $r['date_creation'] ?>"
                            data-id="<?= $r['id'] ?>">
                            <td><span class="bid">#<?= $r['id'] ?></span></td>
                            <td>
                                <strong><?= htmlspecialchars($r['titre']) ?></strong><br>
                                <small style="color:rgba(255,255,255,.4);"><?= htmlspecialchars(substr($r['description'],0,35)) ?>...</small>
                            </td>
                            <td><?= htmlspecialchars($r['categorie']) ?></td>
                            <td>
                                <?php if($r['priorite']=='faible'): ?>
                                <span style="color:#27ae60;">🟢 Faible</span>
                                <?php elseif($r['priorite']=='moyenne'): ?>
                                <span style="color:#f1c40f;">🟡 Moyenne</span>
                                <?php else: ?>
                                <span style="color:#e74c3c;">🔴 Élevée</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="bst <?= $r['statut'] ?>">
                                <?php if($r['statut']=='en_attente'): ?>⏳ En attente
                                <?php elseif($r['statut']=='en_cours'): ?>🔄 En cours
                                <?php elseif($r['statut']=='resolue'): ?>✅ Résolue
                                <?php else: ?>❌ Rejetée<?php endif; ?>
                            </span></td>
                            <td><?= date('d/m/Y', strtotime($r['date_creation'])) ?></td>
                            <td>
                                <?php if(!empty($r['reponse_admin'])): ?>
                                <div class="rep-box" title="<?= htmlspecialchars($r['reponse_admin']) ?>">
                                    <i class="fas fa-reply" style="color:#27ae60;"></i> <?= htmlspecialchars(substr($r['reponse_admin'],0,20)) ?>...
                                </div>
                                <?php else: ?>
                                <span style="color:rgba(255,255,255,.2);">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="act">
                                    <button class="bv" title="Détails" onclick='openDetail(<?= json_encode($r) ?>)'>
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="bh" title="Historique" onclick="openHist()">
                                        <i class="fas fa-history"></i>
                                    </button>
                                    <button class="be" title="Modifier" onclick='openEdit(<?= json_encode($r) ?>)'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" style="display:inline" onsubmit="return confirm('Supprimer cette réclamation ?')">
                                        <input type="hidden" name="action" value="supprimer">
                                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                        <button type="submit" class="bd" title="Supprimer"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL DÉTAILS -->
<div id="detailModal" class="modal">
    <div class="modal-box" style="max-width:520px;">
        <h3><i class="fas fa-eye" style="color:#27ae60;"></i> Détails de la réclamation</h3>
        <div id="detailContent" style="margin-top:1rem;"></div>
        <div class="modal-btns" style="margin-top:1.2rem;">
            <button type="button" class="btn-cancel" onclick="closeDetailModal()">Fermer</button>
        </div>
    </div>
</div>

<!-- MODAL MODIFIER -->
<div id="editModal" class="modal">
    <div class="modal-box">
        <h3><i class="fas fa-edit"></i> Modifier la réclamation</h3>
        <form method="POST">
            <input type="hidden" name="action" value="modifier">
            <input type="hidden" name="id" id="edit_id">
            <div class="form-group"><label>Titre</label><input type="text" name="titre" id="edit_titre" required></div>
            <div class="form-group"><label>Description</label><textarea name="description" id="edit_description" rows="3" required></textarea></div>
            <div class="modal-btns">
                <button type="button" class="btn-cancel" onclick="closeModal()">Annuler</button>
                <button type="submit" class="btn-save"><i class="fas fa-save"></i> Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<!-- HISTORIQUE PANEL -->
<div class="hist-overlay" id="histOverlay" onclick="closeHist()"></div>
<div class="hist-panel" id="histPanel">
    <div class="hist-header">
        <h3><i class="fas fa-history"></i> Mon historique</h3>
        <button class="hist-close" onclick="closeHist()"><i class="fas fa-times"></i></button>
    </div>
    <div class="hist-body" id="histBody">
        <?php if(empty($reclamations)): ?>
        <div class="hist-empty"><i class="fas fa-clock"></i>Aucun historique disponible</div>
        <?php else: ?>
        <?php foreach($reclamations as $r): ?>
        <div class="hist-item">
            <div class="hist-dot <?= $r['statut']=='resolue'?'added':($r['statut']=='en_cours'?'status':($r['statut']=='rejetee'?'deleted':'modified')) ?>">
                <i class="fas <?= $r['statut']=='resolue'?'fa-check':($r['statut']=='en_cours'?'fa-sync':($r['statut']=='rejetee'?'fa-times':'fa-clock')) ?>"></i>
            </div>
            <div class="hist-info">
                <div class="hi-title"><?= htmlspecialchars($r['titre']) ?></div>
                <div class="hi-date">
                    <?= date('d/m/Y H:i', strtotime($r['date_creation'])) ?>
                    <span class="bst <?= $r['statut'] ?>" style="margin-left:8px; padding:2px 8px; font-size:11px;">
                        <?= ucfirst(str_replace('_',' ',$r['statut'])) ?>
                    </span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleMenu() {
    document.getElementById('navLinks').classList.toggle('show');
}

function toggleProfileDropdown(event) {
    event.stopPropagation();
    document.getElementById('profileDropdown').classList.toggle('show');
}

function toggleTheme() {
    document.body.classList.toggle('light-mode');
    const isLight = document.body.classList.contains('light-mode');
    localStorage.setItem('theme', isLight ? 'light' : 'dark');
    const btn = document.getElementById('themeBtn');
    const icon = btn.querySelector('i');
    icon.className = isLight ? 'fas fa-sun' : 'fas fa-moon';
}

if (localStorage.getItem('theme') === 'light') {
    document.body.classList.add('light-mode');
    const btn = document.getElementById('themeBtn');
    if (btn) btn.querySelector('i').className = 'fas fa-sun';
}

window.onclick = function(event) {
    if (!event.target.closest('.profile-dropdown')) {
        var dd = document.getElementById('profileDropdown');
        if (dd && dd.classList.contains('show')) dd.classList.remove('show');
    }
}

function openHist() {
    document.getElementById('histPanel').classList.add('open');
    document.getElementById('histOverlay').classList.add('open');
}
function closeHist() {
    document.getElementById('histPanel').classList.remove('open');
    document.getElementById('histOverlay').classList.remove('open');
}

function showTab(tab) {
    const formAjouter = document.getElementById('form-ajouter');
    const formRechercher = document.getElementById('form-rechercher');
    const btnAjouter = document.getElementById('tabAjouter');
    const btnRechercher = document.getElementById('tabRechercher');
    
    if (tab === 'ajouter') {
        formAjouter.style.display = 'block';
        formRechercher.style.display = 'none';
        btnAjouter.className = 'tab-btn primary';
        btnRechercher.className = 'tab-btn ghost';
    } else {
        formAjouter.style.display = 'none';
        formRechercher.style.display = 'block';
        btnAjouter.className = 'tab-btn ghost';
        btnRechercher.className = 'tab-btn primary';
    }
}

function filterTable() {
    const search = (document.getElementById('searchTop').value || document.getElementById('searchSide').value || '').toLowerCase();
    const statut = document.getElementById('filterStatut')?.value || '';
    const priorite = document.getElementById('filterPriorite')?.value || '';
    const rows = document.querySelectorAll('#tableBody tr');
    
    rows.forEach(row => {
        if (!row.dataset.titre) return;
        const okSearch = !search || row.dataset.titre.includes(search);
        const okStatut = !statut || row.dataset.statut === statut;
        const okPrio = !priorite || row.dataset.priorite === priorite;
        row.style.display = (okSearch && okStatut && okPrio) ? '' : 'none';
    });
}

let sortDir = {};
function sortByCol(col) {
    const tbody = document.getElementById('tableBody');
    const rows = [...tbody.querySelectorAll('tr')].filter(r => r.dataset.titre);
    const dir = sortDir[col] === 'asc' ? 'desc' : 'asc';
    sortDir[col] = dir;
    rows.sort((a, b) => {
        const at = a.cells[col]?.innerText.trim() || '';
        const bt = b.cells[col]?.innerText.trim() || '';
        return dir === 'asc' ? at.localeCompare(bt) : bt.localeCompare(at);
    });
    rows.forEach(r => tbody.appendChild(r));
}

function sortTable() {
    const val = document.getElementById('sortSelect').value;
    const tbody = document.getElementById('tableBody');
    const rows = [...tbody.querySelectorAll('tr')].filter(r => r.dataset.titre);
    if (!val) return;
    rows.sort((a, b) => {
        if (val === 'date_desc' || val === 'date_asc') {
            const ad = new Date(a.dataset.date), bd = new Date(b.dataset.date);
            return val === 'date_desc' ? bd - ad : ad - bd;
        }
        if (val === 'priorite') {
            const order = { faible: 0, moyenne: 1, elevee: 2 };
            return (order[b.dataset.priorite] || 0) - (order[a.dataset.priorite] || 0);
        }
        if (val === 'statut') {
            return a.dataset.statut.localeCompare(b.dataset.statut);
        }
        return 0;
    });
    rows.forEach(r => tbody.appendChild(r));
}

function openEdit(r) {
    document.getElementById('edit_id').value = r.id;
    document.getElementById('edit_titre').value = r.titre;
    document.getElementById('edit_description').value = r.description;
    document.getElementById('editModal').style.display = 'flex';
}

function openDetail(r) {
    const statMap = {en_attente: '⏳ En attente', en_cours: '🔄 En cours', resolue: '✅ Résolue', rejetee: '❌ Rejetée'};
    const prioMap = {faible: '🟢 Faible', moyenne: '🟡 Moyenne', elevee: '🔴 Élevée'};
    const reponse = r.reponse_admin ? `<div style="background:rgba(39,174,96,.08);border:1px solid rgba(39,174,96,.2);border-radius:10px;padding:.8rem;margin-top:.5rem;"><i class='fas fa-reply' style='color:#27ae60;'></i> <strong>Réponse admin :</strong><br>${r.reponse_admin}</div>` : '<span style="color:rgba(255,255,255,.3);">Aucune réponse admin.</span>';
    const date = r.date_creation ? new Date(r.date_creation).toLocaleDateString('fr-FR', {day:'2-digit',month:'2-digit',year:'numeric',hour:'2-digit',minute:'2-digit'}) : '—';
    document.getElementById('detailContent').innerHTML = `
        <table style="width:100%;border-collapse:collapse;">
            <tr><td style="padding:.45rem .6rem;color:rgba(255,255,255,.45);font-size:.78rem;width:120px;">ID</td><td style="padding:.45rem .6rem;font-size:.85rem;"><strong>#${r.id}</strong></td></tr>
            <tr style="background:rgba(255,255,255,.03);"><td style="padding:.45rem .6rem;color:rgba(255,255,255,.45);font-size:.78rem;">Titre</td><td style="padding:.45rem .6rem;font-size:.85rem;font-weight:600;">${r.titre}</td></tr>
            <tr><td style="padding:.45rem .6rem;color:rgba(255,255,255,.45);font-size:.78rem;">Catégorie</td><td style="padding:.45rem .6rem;font-size:.85rem;">${r.categorie}</td></tr>
            <tr style="background:rgba(255,255,255,.03);"><td style="padding:.45rem .6rem;color:rgba(255,255,255,.45);font-size:.78rem;">Priorité</td><td style="padding:.45rem .6rem;font-size:.85rem;">${prioMap[r.priorite] || r.priorite}</td></tr>
            <tr><td style="padding:.45rem .6rem;color:rgba(255,255,255,.45);font-size:.78rem;">Statut</td><td style="padding:.45rem .6rem;font-size:.85rem;">${statMap[r.statut] || r.statut}</td></tr>
            <tr style="background:rgba(255,255,255,.03);"><td style="padding:.45rem .6rem;color:rgba(255,255,255,.45);font-size:.78rem;">Date</td><td style="padding:.45rem .6rem;font-size:.85rem;">${date}</td></tr>
            <tr><td colspan="2" style="padding:.45rem .6rem;color:rgba(255,255,255,.45);font-size:.78rem;">Description</td></tr>
            <tr style="background:rgba(255,255,255,.03);"><td colspan="2" style="padding:.6rem;font-size:.83rem;line-height:1.5;">${r.description}</td></tr>
            <tr><td colspan="2" style="padding:.6rem;">${reponse}</td></tr>
        </table>`;
    document.getElementById('detailModal').style.display = 'flex';
}
function closeDetailModal() {
    document.getElementById('detailModal').style.display = 'none';
}
function closeModal() {
    document.getElementById('editModal').style.display = 'none';
}

window.addEventListener('click', function(e) {
    if (e.target === document.getElementById('editModal')) closeModal();
    if (e.target === document.getElementById('detailModal')) closeDetailModal();
});

// Initialize
document.getElementById('form-rechercher').style.display = 'none';
document.getElementById('form-ajouter').style.display = 'block';
</script>
</body>
</html>