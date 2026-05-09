<?php
session_start();
try {
    $pdo = new PDO("mysql:host=localhost;dbname=ecoride", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) { die("Erreur BDD : " . $e->getMessage()); }

$user_id = 1;
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

<nav class="navbar-modern">
    <a href="index.php" class="logo">
        <img src="data:image/png;base64,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" alt="EcoRide Logo" class="logo-img">
        <div>
            <div class="logo-text">ECO RIDE</div>
            <div class="logo-tagline">Covoiturage Intelligent</div>
        </div>
    </a>

    <button type="button" class="menu-toggle" onclick="toggleMenu()">
        <i class="fas fa-bars"></i>
    </button>

    <ul class="nav-links" id="navLinks">
        <li><a href="index.php" class="active">Accueil</a></li>
        <li><a href="/ecoride/View/frontoffice/index.php">Événements</a></li>
        <li><a href="/ecoride/View/frontoffice/index.php">Sponsors</a></li>
        <li><a href="/ecoride/View/frontoffice/tous_les_trajets.php">Covoiturage</a></li>
        <li><a href="/ecoride/View/frontoffice/lostfound_front.php">Objets perdus</a></li>

        <li class="profile-dropdown">
            <button type="button" class="profile-btn" onclick="toggleProfileDropdown(event)">
                <div class="profile-avatar"><i class="fas fa-user"></i></div>
                <span>Profil</span>
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="dropdown-menu" id="profileDropdown">
                <div class="dropdown-header">
                    <div class="avatar"><i class="fas fa-user"></i></div>
                    <div class="user-info">
                        <div class="user-name">Utilisateur</div>
                        <div class="user-role">Membre EcoRide</div>
                    </div>
                </div>
                <div class="dropdown-links">
                    <a href="/ecoride/View/frontoffice/user.php"><i class="fas fa-user-circle"></i> Mon profil</a>
                    <a href="/ecoride/View/frontoffice/tous_les_trajets.php"><i class="fas fa-car"></i> Covoiturages</a>
                    <a href="/ecoride/View/frontoffice/user.php?tab=mes-trajets"><i class="fas fa-map-marker-alt"></i> Mes trajets</a>
                    <a href="/ecoride/View/frontoffice/mes_vehicules.php"><i class="fas fa-key"></i> Mes véhicules</a>
                    <a href="/ecoride/View/frontoffice/mon_historique.php"><i class="fas fa-history"></i> Mon historique</a>
                    <a href="/ecoride/View/frontoffice/user.php?tab=favoris"><i class="fas fa-heart"></i> Mes favoris</a>
                    <a href="/ecoride/View/frontoffice/index.php"><i class="fas fa-exclamation-triangle"></i> Réclamations</a>
                    <a href="/ecoride/View/frontoffice/mes_objets_perdus.php"><i class="fas fa-search"></i> Mes objets perdus</a>
                </div>
                <div class="dropdown-divider"></div>
                <div class="dropdown-actions">
                    <a href="/ecoride/View/frontoffice/logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
                </div>
            </div>
        </li>

        <li><a href="/ecoride/View/backoffice/admin_reclamations.php" class="admin-btn">Admin</a></li>

        <li>
            <button type="button" class="theme-btn" onclick="toggleTheme()" id="themeBtn">
                <i class="fas fa-moon"></i>
            </button>
        </li>
    </ul>
</nav>

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