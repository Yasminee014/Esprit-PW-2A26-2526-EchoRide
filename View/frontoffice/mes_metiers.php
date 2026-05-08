<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mes Métiers | EcoRide</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Segoe UI',sans-serif;background:#0A1628;color:#fff;min-height:100vh;}
.container{max-width:1200px;margin:0 auto;padding:2rem;}
.hero-small{background:linear-gradient(135deg,#1976D2,#0F3B6E);border-radius:20px;padding:1.5rem 2rem;margin-bottom:2rem;display:flex;justify-content:space-between;align-items:center;gap:1rem;}
.hero-small h2{font-size:1.7rem;}
.hero-small p{color:rgba(255,255,255,.8);margin-top:.4rem;}
.hero-small-icon{font-size:3rem;opacity:.35;}
.page-header{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;}
.page-header h1{font-size:1.8rem;display:flex;align-items:center;gap:.8rem;}
.card-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:1rem;}
.card{background:rgba(255,255,255,.06);border:1px solid rgba(97,179,250,.18);border-radius:18px;padding:1.4rem;transition:transform .3s;}
.card:hover{transform:translateY(-3px);border-color:#61B3FA;}
.card-title{font-size:1.05rem;font-weight:700;margin-bottom:.75rem;color:#fff;}
.card-text{color:#cbd6e7;font-size:.92rem;line-height:1.6;}
.btn-primary{background:linear-gradient(135deg,#1976D2,#61B3FA);color:#fff;padding:.8rem 1.4rem;border:none;border-radius:30px;cursor:pointer;display:inline-flex;align-items:center;gap:.55rem;text-decoration:none;}
.btn-primary:hover{transform:translateY(-2px);}
.empty-state{padding:2rem;border-radius:20px;background:rgba(255,255,255,.05);text-align:center;color:#cfd6e4;}
@media(max-width:768px){.hero-small{flex-direction:column;align-items:flex-start;}.page-header{flex-direction:column;align-items:flex-start;}}
</style>
</head>
<body>
<?php require_once __DIR__ . '/includes/navbar_moderne.php'; ?>
<main class="container">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-briefcase"></i> Mes Métiers</h1>
            <p style="color:rgba(255,255,255,.75);margin-top:.5rem;">Gérez vos métiers et présentez vos services sur EcoRide.</p>
        </div>
        <a href="#new-job" class="btn-primary"><i class="fas fa-plus"></i> Ajouter un métier</a>
    </div>

    <div class="hero-small">
        <div>
            <h2>Vos métiers actifs</h2>
            <p>Affichez les métiers disponibles et suivez leur statut depuis une page propre et moderne.</p>
        </div>
        <div class="hero-small-icon"><i class="fas fa-briefcase"></i></div>
    </div>

    <div class="card-grid">
        <div class="card">
            <div class="card-title">Chauffeur Particulier</div>
            <div class="card-text">Offrez des trajets personnalisés pour vos passagers et gérez facilement vos disponibilités.</div>
        </div>
        <div class="card">
            <div class="card-title">Livraison Urgente</div>
            <div class="card-text">Proposez un service de livraison express pour les petits colis et commandes locales.</div>
        </div>
        <div class="card">
            <div class="card-title">Accompagnement événementiel</div>
            <div class="card-text">Assurez le transport des participants vers des événements et rendez-vous professionnels.</div>
        </div>
    </div>

    <section id="new-job" style="margin-top:2rem;">
        <div class="card" style="background:rgba(255,255,255,.08);border-color:rgba(97,179,250,.25);">
            <h2 style="margin-bottom:1rem;">Ajouter un métier</h2>
            <p style="color:#cbd6e7;">Cette page est prête pour intégrer le formulaire de création métier. Pour l’instant, la navigation et le style sont prêts.</p>
        </div>
    </section>
</main>
</body>
</html>
