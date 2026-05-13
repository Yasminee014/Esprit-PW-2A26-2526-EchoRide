<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/includes/auth_guard.php';

require_once __DIR__ . '/../../Model/VehiculeModel.php';

$vehiculeId = (int)($_GET['id'] ?? 0);
if ($vehiculeId <= 0) {
    header('Location: tous_les_trajets.php');
    exit;
}

$vehiculeModel = new VehiculeModel();
$vehicule = $vehiculeModel->getById($vehiculeId);
if (!$vehicule) {
    header('Location: tous_les_trajets.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails voiture | EcoRide</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #0A1628; color:#fff; margin:0; }
        .wrap { max-width: 900px; margin: 2rem auto; padding: 0 1rem; }
        .card { background: rgba(255,255,255,.07); border:1px solid rgba(97,179,250,.25); border-radius:18px; overflow:hidden; }
        .head { padding:1rem 1.2rem; background:linear-gradient(135deg,#1976D2,#0F3B6E); display:flex; justify-content:space-between; align-items:center; gap:1rem; }
        .body { padding:1.2rem; display:grid; grid-template-columns: 320px 1fr; gap:1.2rem; }
        .image { border-radius:12px; overflow:hidden; height:220px; background:#132745; display:flex; align-items:center; justify-content:center; }
        .image img { width:100%; height:100%; object-fit:cover; }
        .info { display:grid; grid-template-columns: 1fr 1fr; gap:.7rem; }
        .it { background: rgba(255,255,255,.05); border-radius:10px; padding:.7rem; }
        .lb { font-size:.72rem; color:#A7A9AC; margin-bottom:.2rem; }
        .vl { font-size:.92rem; font-weight:600; }
        .actions { margin-top:1rem; display:flex; gap:.6rem; flex-wrap:wrap; }
        .btn { border:none; text-decoration:none; border-radius:26px; padding:.6rem 1rem; display:inline-flex; align-items:center; gap:8px; cursor:pointer; }
        .btn-primary { background:linear-gradient(135deg,#1976D2,#61B3FA); color:#fff; }
        .btn-ghost { background:rgba(255,255,255,.1); color:#fff; }
        @media (max-width: 860px) {
            .body { grid-template-columns: 1fr; }
            .info { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<?php include_once __DIR__ . '/partials/navbar.php'; ?>

<div class="wrap">
    <div class="card">
        <div class="head">
            <h2 style="margin:0;"><i class="fas fa-car-side"></i> <?= htmlspecialchars(($vehicule['marque'] ?? '') . ' ' . ($vehicule['modele'] ?? '')) ?></h2>
            <span><?= !empty($vehicule['statut']) ? htmlspecialchars($vehicule['statut']) : 'disponible' ?></span>
        </div>
        <div class="body">
            <div class="image">
                <?php
                $photo = $vehicule['photo'] ?? '';
                $photoPath = '/ecoride/assets/uploads/vehicules/' . $photo;
                $fullServerPath = $_SERVER['DOCUMENT_ROOT'] . $photoPath;
                if (!empty($photo) && file_exists($fullServerPath)):
                ?>
                    <img src="<?= $photoPath ?>" alt="Photo véhicule">
                <?php else: ?>
                    <i class="fas fa-car" style="font-size:68px;opacity:.35;"></i>
                <?php endif; ?>
            </div>

            <div>
                <div class="info">
                    <div class="it"><div class="lb">Conducteur</div><div class="vl"><?= htmlspecialchars(trim(($vehicule['prenom'] ?? '') . ' ' . ($vehicule['nom'] ?? ''))) ?></div></div>
                    <div class="it"><div class="lb">Immatriculation</div><div class="vl"><?= htmlspecialchars($vehicule['immatriculation'] ?? '—') ?></div></div>
                    <div class="it"><div class="lb">Couleur</div><div class="vl"><?= htmlspecialchars($vehicule['couleur'] ?? '—') ?></div></div>
                    <div class="it"><div class="lb">Capacité</div><div class="vl"><?= (int)($vehicule['capacite'] ?? 0) ?> place(s)</div></div>
                    <div class="it"><div class="lb">Climatisation</div><div class="vl"><?= !empty($vehicule['climatisation']) ? 'Oui' : 'Non' ?></div></div>
                    <div class="it"><div class="lb">Trajet associé</div><div class="vl"><?= htmlspecialchars(($vehicule['trajet_depart'] ?? '') && ($vehicule['trajet_arrive'] ?? '') ? $vehicule['trajet_depart'] . ' -> ' . $vehicule['trajet_arrive'] : 'Non défini') ?></div></div>
                </div>

                <div class="actions">
                    <a class="btn btn-ghost" href="tous_les_trajets.php">
                        <i class="fas fa-arrow-left"></i> Retour trajets
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
