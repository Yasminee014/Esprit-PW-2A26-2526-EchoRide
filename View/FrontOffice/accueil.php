<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Eco Ride - Accueil</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Poppins',sans-serif;background:linear-gradient(135deg,#0A2F44,#0D1F3A);color:white;}
.navbar{background:rgba(10,47,68,0.95);padding:1rem 2rem;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid rgba(0,180,216,0.3);}
.logo{display:flex;align-items:center;gap:10px;}
.logo i{color:#00B4D8;font-size:28px;}
.nav-links{display:flex;gap:20px;list-style:none;}
.nav-links a{color:white;text-decoration:none;}
.nav-links a:hover,.nav-links a.active{color:#00B4D8;}
.hero{text-align:center;padding:3rem;}
.hero h1{font-size:2.3rem;}
.highlight{color:#00B4D8;}
.form-section{padding:2rem;}
.form-container{max-width:1100px;margin:auto;display:grid;grid-template-columns:1fr 1fr;gap:20px;}
.form-card{background:rgba(13,31,45,0.9);padding:2rem;border-radius:20px;border:1px solid rgba(0,180,216,0.3);}
.form-card h2{color:#00B4D8;margin-bottom:15px;}
.event-item,.sponsor-item{background:rgba(10,47,68,0.5);padding:1rem;margin-bottom:0.5rem;border-radius:10px;display:flex;justify-content:space-between;align-items:center;}
.btn-small{background:#00B4D8;padding:0.3rem 0.8rem;border-radius:15px;text-decoration:none;color:white;font-size:0.8rem;}
.features{padding:3rem;}
.features-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;}
.feature-card{background:rgba(255,255,255,0.05);padding:20px;border-radius:15px;text-align:center;}
.feature-card i{font-size:2rem;color:#00B4D8;margin-bottom:10px;}
footer{text-align:center;padding:20px;color:#6B6B6B;}
</style>
</head>
<body>

<nav class="navbar">
<div class="logo"><i class="fas fa-leaf"></i><h2>ECO RIDE</h2></div>
<ul class="nav-links">
<li><a href="index.php?action=accueil" class="active">Accueil</a></li>
<li><a href="index.php?action=events">Événements</a></li>
<li><a href="index.php?action=dashboard">Admin</a></li>
</ul>
</nav>

<div class="hero">
<h1>Gérez vos <span class="highlight">événements facilement</span></h1>
<p>Découvrez et participez aux événements Eco Ride</p>
</div>

<div class="form-section">
<div class="form-container">
<div class="form-card">
<h2><i class="fas fa-calendar-alt"></i> Événements à venir</h2>
<?php if(!empty($upcomingEvents)): ?>
    <?php foreach($upcomingEvents as $event): ?>
    <div class="event-item">
        <div>
            <strong><?= htmlspecialchars($event['titre']) ?></strong><br>
            <small><?= htmlspecialchars($event['ville']) ?> - <?= date('d/m/Y H:i', strtotime($event['date_evenement'])) ?></small>
        </div>
        <a href="index.php?action=event-detail&id=<?= $event['id'] ?>" class="btn-small">Voir</a>
    </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>Aucun événement à venir</p>
<?php endif; ?>
</div>

<div class="form-card">
<h2><i class="fas fa-handshake"></i> Nos Sponsors</h2>
<?php if(!empty($sponsors)): ?>
    <?php foreach($sponsors as $sponsor): ?>
    <div class="sponsor-item">
        <strong><?= htmlspecialchars($sponsor['nom_entreprise']) ?></strong>
        <span><?= number_format($sponsor['montant_sponsoring'], 0, ',', ' ') ?> €</span>
    </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>Aucun sponsor</p>
<?php endif; ?>
</div>
</div>
</div>

<div class="features">
<h2 style="text-align:center;margin-bottom:20px;">Notre Solution</h2>
<div class="features-grid">
<div class="feature-card"><i class="fas fa-calendar"></i><h3>Gestion événements</h3><p>Créez et gérez</p></div>
<div class="feature-card"><i class="fas fa-handshake"></i><h3>Sponsors</h3><p>Gérez vos partenaires</p></div>
<div class="feature-card"><i class="fas fa-chart-line"></i><h3>Statistiques</h3><p>Tableau de bord</p></div>
</div>
</div>

<footer><p>Eco Ride © 2025</p></footer>

<script src="validation.js"></script>
</body>
</html>