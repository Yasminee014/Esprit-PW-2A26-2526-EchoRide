<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../Config/Database.php';
require_once __DIR__ . '/MatchingIA.php';

$db = Database::getInstance();
$trajetId = (int)($_GET['trajet_id'] ?? 0);

if (!$trajetId) {
    header('Location: tous_les_trajets.php');
    exit;
}

// Charger le trajet
$trajet = $db->prepare("
    SELECT t.*,
           u.nom AS conducteur_nom, u.prenom AS conducteur_prenom
    FROM trajet t
    LEFT JOIN users u ON u.id = t.id_u
    WHERE t.id_T = ?
")->execute([$trajetId]) ? null : null;

$stmt = $db->prepare("
    SELECT t.*,
           u.nom AS conducteur_nom, u.prenom AS conducteur_prenom
    FROM trajet t
    LEFT JOIN users u ON u.id = t.id_u
    WHERE t.id_T = ?
");
$stmt->execute([$trajetId]);
$trajet = $stmt->fetch();

if (!$trajet) {
    header('Location: tous_les_trajets.php');
    exit;
}

// Charger tous les véhicules disponibles avec scoring IA
$stmt2 = $db->prepare("
    SELECT v.*,
           u.nom AS proprietaire_nom, u.prenom AS proprietaire_prenom,
           COUNT(r.id) AS nb_reservations
    FROM vehicules v
    LEFT JOIN users u ON v.user_id = u.id
    LEFT JOIN reservations r ON v.id = r.vehicule_id
    WHERE v.statut = 'disponible'
    GROUP BY v.id
    ORDER BY nb_reservations DESC, v.capacite DESC
");
$stmt2->execute();
$vehicules = $stmt2->fetchAll();

// Scoring IA pour chaque véhicule
function scoreVehicule(array $v, array $trajet): array {
    $score = 0;
    $raisons = [];

    // Disponibilité (poids majeur)
    if (($v['statut'] ?? '') === 'disponible') {
        $score += 50;
        $raisons[] = ['type' => 'success', 'icon' => 'fa-check-circle', 'texte' => 'Véhicule disponible immédiatement'];
    }

    // Capacité
    $capacite = (int)($v['capacite'] ?? 4);
    if ($capacite >= 4) {
        $score += 25;
        $raisons[] = ['type' => 'success', 'icon' => 'fa-users', 'texte' => "Grande capacité ({$capacite} places)"];
    } elseif ($capacite >= 2) {
        $score += 15;
        $raisons[] = ['type' => 'info', 'icon' => 'fa-users', 'texte' => "Capacité standard ({$capacite} places)"];
    } else {
        $score += 5;
        $raisons[] = ['type' => 'warning', 'icon' => 'fa-users', 'texte' => "Capacité limitée ({$capacite} places)"];
    }

    // Historique réservations = popularité
    $nbResa = (int)($v['nb_reservations'] ?? 0);
    if ($nbResa >= 5) {
        $score += 20;
        $raisons[] = ['type' => 'success', 'icon' => 'fa-star', 'texte' => "Très populaire ({$nbResa} réservations)"];
    } elseif ($nbResa >= 2) {
        $score += 12;
        $raisons[] = ['type' => 'info', 'icon' => 'fa-star-half-alt', 'texte' => "Populaire ({$nbResa} réservations)"];
    } elseif ($nbResa >= 1) {
        $score += 6;
        $raisons[] = ['type' => 'info', 'icon' => 'fa-star-half-alt', 'texte' => "{$nbResa} réservation(s) antérieure(s)"];
    } else {
        $raisons[] = ['type' => 'muted', 'icon' => 'fa-info-circle', 'texte' => 'Nouveau véhicule'];
    }

    // Climatisation = confort
    if (!empty($v['climatisation']) && $v['climatisation'] !== '0') {
        $score += 15;
        $raisons[] = ['type' => 'success', 'icon' => 'fa-snowflake', 'texte' => 'Climatisation incluse'];
    }

    // Photo disponible = confiance
    if (!empty($v['photo'])) {
        $score += 5;
        $raisons[] = ['type' => 'info', 'icon' => 'fa-camera', 'texte' => 'Photo disponible'];
    }

    $pertinence = min(100, (int)round($score / 115 * 100));
    return ['score' => $score, 'pertinence' => $pertinence, 'raisons' => $raisons];
}

// Scorer et trier les véhicules
foreach ($vehicules as &$v) {
    $sc = scoreVehicule($v, $trajet);
    $v['score_ia']   = $sc['score'];
    $v['pertinence'] = $sc['pertinence'];
    $v['raisons']    = $sc['raisons'];
}
unset($v);

usort($vehicules, fn($a, $b) => $b['score_ia'] <=> $a['score_ia']);
$best = $vehicules[0] ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Véhicule Optimal Suggéré | EcoRide</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{
  --bg:#0A1628;--bg2:#0d1f3a;--bg3:#0f2340;
  --card:#0d1f3a;--card2:#122a4a;
  --border:rgba(56,139,253,0.15);--border2:rgba(56,139,253,0.25);
  --blue:#3b82f6;--blue-bright:#60a5fa;--blue-dim:rgba(59,130,246,0.12);
  --green:#22c55e;--green-dim:rgba(34,197,94,0.12);
  --amber:#f59e0b;--amber-dim:rgba(245,158,11,0.12);
  --purple:#a855f7;--purple-dim:rgba(168,85,247,0.12);
  --red:#ef4444;
  --text:#e2e8f0;--text2:#cbd5e1;--muted:#64748b;--muted2:#475569;
}
body{font-family:'Outfit',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;overflow-x:hidden}

/* TOPBAR */
.topbar{padding:.75rem 1.5rem;display:flex;align-items:center;gap:1rem;border-bottom:1px solid var(--border);background:var(--bg2)}
.topbar a{color:var(--muted);font-size:.85rem;text-decoration:none;display:flex;align-items:center;gap:6px;transition:color .2s}
.topbar a:hover{color:var(--blue-bright)}
.topbar .logo{font-size:1.1rem;font-weight:700;background:linear-gradient(135deg,var(--blue-bright),var(--purple));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;margin-left:auto}

/* HERO BANNER */
.hero{
  background:linear-gradient(135deg,rgba(59,130,246,0.12),rgba(168,85,247,0.08));
  border-bottom:1px solid var(--border);
  padding:2rem 1.5rem 1.5rem;
  text-align:center;position:relative;overflow:hidden;
}
.hero::before{content:'';position:absolute;top:-60px;left:50%;transform:translateX(-50%);width:400px;height:200px;background:radial-gradient(ellipse,rgba(59,130,246,0.15),transparent 70%);pointer-events:none}
.hero-badge{
  display:inline-flex;align-items:center;gap:8px;
  background:var(--amber-dim);border:1px solid rgba(245,158,11,0.3);
  color:var(--amber);font-size:.75rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;
  padding:.35rem 1rem;border-radius:99px;margin-bottom:1rem;
}
.hero h1{font-size:1.8rem;font-weight:700;margin-bottom:.5rem}
.hero h1 span{background:linear-gradient(135deg,var(--blue-bright),var(--purple));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.hero p{font-size:.88rem;color:var(--muted);max-width:500px;margin:0 auto}

/* TRAJET STRIP */
.trajet-strip{
  max-width:1100px;margin:1.5rem auto 0;padding:0 1.5rem;
}
.trajet-card{
  background:var(--card2);border:1px solid var(--border2);border-radius:14px;
  padding:1rem 1.5rem;display:flex;align-items:center;gap:1.5rem;flex-wrap:wrap;
}
.trajet-route{display:flex;align-items:center;gap:.75rem;font-size:1.05rem;font-weight:600}
.trajet-route .arrow{color:var(--muted);font-size:.85rem}
.trajet-meta{display:flex;gap:1rem;font-size:.78rem;color:var(--muted);flex-wrap:wrap}
.trajet-meta span{display:flex;align-items:center;gap:5px}
.trajet-meta i{font-size:.7rem}

/* MAIN LAYOUT */
.main{max-width:1100px;margin:1.5rem auto;padding:0 1.5rem;display:grid;grid-template-columns:1fr 300px;gap:1.5rem}
@media(max-width:900px){.main{grid-template-columns:1fr}}

/* BEST VEHICLE BANNER */
.best-banner{
  background:linear-gradient(135deg,rgba(34,197,94,0.08),rgba(59,130,246,0.05));
  border:1.5px solid rgba(34,197,94,0.3);border-radius:18px;
  padding:1.5rem;margin-bottom:1.5rem;position:relative;overflow:hidden;
}
.best-banner::before{
  content:'';position:absolute;right:-30px;top:-30px;
  width:160px;height:160px;border-radius:50%;
  background:radial-gradient(circle,rgba(34,197,94,0.1),transparent 70%);
}
.best-label{
  display:inline-flex;align-items:center;gap:6px;
  background:var(--green);color:#000;font-size:.7rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase;
  padding:.3rem .9rem;border-radius:99px;margin-bottom:1rem;
}
.best-body{display:grid;grid-template-columns:auto 1fr auto;gap:1.25rem;align-items:center}
@media(max-width:600px){.best-body{grid-template-columns:1fr;text-align:center}}
.best-img{
  width:110px;height:80px;border-radius:12px;overflow:hidden;border:1px solid rgba(34,197,94,0.2);flex-shrink:0;
}
.best-img img{width:100%;height:100%;object-fit:cover}
.best-img .no-img{
  width:100%;height:100%;display:flex;align-items:center;justify-content:center;
  background:rgba(34,197,94,0.06);font-size:2rem;
}
.best-info h2{font-size:1.25rem;font-weight:700;margin-bottom:.3rem}
.best-info .tags{display:flex;gap:.5rem;flex-wrap:wrap;margin-top:.5rem}
.tag{
  display:inline-flex;align-items:center;gap:5px;
  padding:.28rem .75rem;border-radius:99px;font-size:.72rem;font-weight:600;
}
.tag.green{background:var(--green-dim);color:var(--green);border:1px solid rgba(34,197,94,0.25)}
.tag.blue{background:var(--blue-dim);color:var(--blue-bright);border:1px solid rgba(59,130,246,0.25)}
.tag.amber{background:var(--amber-dim);color:var(--amber);border:1px solid rgba(245,158,11,0.25)}
.tag.muted{background:rgba(255,255,255,0.04);color:var(--muted);border:1px solid var(--border)}

.best-right{display:flex;flex-direction:column;align-items:flex-end;gap:.75rem}
.score-ring{position:relative;width:78px;height:78px;flex-shrink:0}
.score-ring svg{transform:rotate(-90deg)}
.score-num{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;font-size:1.1rem;font-weight:700;line-height:1}
.score-num small{font-size:.55rem;font-weight:400;color:var(--muted)}
.score-label{font-size:.68rem;font-weight:700;color:var(--green);text-align:center;margin-top:-2px}

.btn-reserve{
  padding:.65rem 1.5rem;
  background:linear-gradient(135deg,#16a34a,#22c55e);
  border:none;border-radius:10px;
  color:#fff;font-family:inherit;font-size:.9rem;font-weight:700;
  cursor:pointer;display:inline-flex;align-items:center;gap:8px;text-decoration:none;
  box-shadow:0 4px 20px rgba(34,197,94,0.3);
  transition:opacity .2s,transform .1s;white-space:nowrap;
}
.btn-reserve:hover{opacity:.9;transform:translateY(-1px)}
.btn-reserve:active{transform:scale(.97)}

/* RAISONS LIST */
.raisons-grid{display:flex;flex-direction:column;gap:.45rem;margin-top:.85rem}
.raison-item{
  display:flex;align-items:center;gap:.6rem;
  font-size:.78rem;padding:.4rem .6rem;border-radius:8px;
}
.raison-item.success{background:rgba(34,197,94,0.07);color:var(--green)}
.raison-item.info{background:rgba(59,130,246,0.07);color:var(--blue-bright)}
.raison-item.warning{background:var(--amber-dim);color:var(--amber)}
.raison-item.error{background:rgba(239,68,68,0.07);color:var(--red)}
.raison-item.muted{background:rgba(255,255,255,0.03);color:var(--muted)}
.raison-item i{font-size:.72rem;flex-shrink:0}

/* SECTION HEADER */
.sec-hdr{display:flex;align-items:center;gap:8px;margin:1.5rem 0 .85rem}
.sec-hdr h3{font-size:.95rem;font-weight:600;color:var(--text2)}
.sec-hdr .cnt{
  font-size:.7rem;font-weight:700;background:var(--blue-dim);color:var(--blue-bright);
  border:1px solid rgba(59,130,246,0.25);padding:2px 8px;border-radius:99px;
}

/* VEHICLE CARD */
.vcard{
  background:var(--card2);border:1px solid var(--border);border-radius:14px;
  padding:1.1rem 1.25rem;margin-bottom:.75rem;
  display:grid;grid-template-columns:80px 1fr auto;gap:1rem;align-items:center;
  position:relative;overflow:hidden;
  transition:border-color .2s,box-shadow .2s;
  cursor:pointer;
}
.vcard:hover{border-color:rgba(59,130,246,0.3);box-shadow:0 0 20px rgba(59,130,246,0.06)}
.vcard.selected{border-color:rgba(34,197,94,0.5);background:rgba(34,197,94,0.04)}
.vcard::before{content:'';position:absolute;left:0;top:0;bottom:0;width:3px;background:var(--border2);border-radius:3px 0 0 3px;transition:background .2s}
.vcard.selected::before{background:var(--green)}
.vcard:hover::before{background:var(--blue)}

.vcard-img{width:80px;height:60px;border-radius:10px;overflow:hidden;flex-shrink:0}
.vcard-img img{width:100%;height:100%;object-fit:cover}
.vcard-img .no-img{width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:rgba(59,130,246,0.06);font-size:1.5rem;border-radius:10px}

.vcard-info h4{font-size:.92rem;font-weight:600;margin-bottom:.3rem}
.vcard-info .vtags{display:flex;gap:.35rem;flex-wrap:wrap}

.vcard-right{display:flex;flex-direction:column;align-items:flex-end;gap:.5rem}
.mini-ring{position:relative;width:50px;height:50px}
.mini-ring svg{transform:rotate(-90deg)}
.mini-num{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:.78rem;font-weight:700}
.btn-choisir{
  padding:.38rem .85rem;
  background:rgba(59,130,246,0.12);border:1px solid rgba(59,130,246,0.3);
  border-radius:8px;color:var(--blue-bright);
  font-family:inherit;font-size:.75rem;font-weight:600;cursor:pointer;
  transition:all .2s;text-decoration:none;white-space:nowrap;
  display:inline-flex;align-items:center;gap:5px;
}
.btn-choisir:hover{background:rgba(59,130,246,0.2);border-color:rgba(59,130,246,0.5)}
.btn-choisir.selected{background:var(--green-dim);border-color:rgba(34,197,94,0.4);color:var(--green)}

/* SIDEBAR */
.sidebar{}
.sc-card{background:var(--card2);border:1px solid var(--border);border-radius:14px;padding:1.2rem;margin-bottom:1rem}
.sc-card h5{font-size:.88rem;font-weight:600;display:flex;align-items:center;gap:8px;margin-bottom:.85rem}

/* CTA STICKY */
.sticky-cta{
  position:sticky;bottom:1.5rem;
  background:linear-gradient(135deg,rgba(22,163,74,0.95),rgba(34,197,94,0.95));
  backdrop-filter:blur(10px);
  border-radius:14px;padding:1rem 1.25rem;
  border:1px solid rgba(34,197,94,0.4);
  display:none;
  box-shadow:0 8px 32px rgba(34,197,94,0.25);
}
.sticky-cta.show{display:flex;align-items:center;justify-content:space-between;gap:.75rem}
.sticky-cta .vname{font-size:.88rem;font-weight:600;color:#fff}
.sticky-cta .vname small{display:block;font-size:.7rem;opacity:.8;font-weight:400}
.btn-reserve-cta{
  padding:.6rem 1.3rem;background:#fff;border:none;border-radius:10px;
  color:#16a34a;font-family:inherit;font-size:.85rem;font-weight:700;
  cursor:pointer;display:inline-flex;align-items:center;gap:6px;text-decoration:none;
  transition:opacity .2s;white-space:nowrap;flex-shrink:0;
}
.btn-reserve-cta:hover{opacity:.9}

/* EMPTY */
.empty-state{text-align:center;padding:3rem 1rem;color:var(--muted)}
.empty-state .ei{font-size:3rem;margin-bottom:1rem;opacity:.3}

@keyframes fadein{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none}}
.vcard{animation:fadein .35s ease both}
.vcard:nth-child(2){animation-delay:.05s}
.vcard:nth-child(3){animation-delay:.1s}
</style>
</head>
<body>
<?php require_once __DIR__ . '/includes/navbar_moderne.php'; ?>

<!-- Bouton Retour design -->
<div style="max-width: 1100px; margin: 1rem auto 0; padding: 0 1.5rem;">
    <a href="javascript:history.back()" style="
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255,255,255,0.08);
        backdrop-filter: blur(10px);
        padding: 8px 18px;
        border-radius: 40px;
        text-decoration: none;
        color: #61B3FA;
        font-size: 0.85rem;
        font-weight: 500;
        transition: all 0.3s ease;
        border: 1px solid rgba(97,179,250,0.3);
    " onmouseover="this.style.background='rgba(97,179,250,0.15)'; this.style.transform='translateX(-3px)'; this.style.borderColor='rgba(97,179,250,0.6)';"
       onmouseout="this.style.background='rgba(255,255,255,0.08)'; this.style.transform='translateX(0)'; this.style.borderColor='rgba(97,179,250,0.3)';">
        <i class="fas fa-arrow-left" style="font-size: 0.75rem;"></i>
        <span>Retour</span>
    </a>
</div>


<!-- TRAJET STRIP -->
<div class="trajet-strip">
  <div class="trajet-card">
    <div class="trajet-route">
      <span><?= htmlspecialchars($trajet['point_depart'] ?? '—') ?></span>
      <span class="arrow"><i class="fas fa-arrow-right"></i></span>
      <span><?= htmlspecialchars($trajet['point_arrive'] ?? '—') ?></span>
    </div>
    <div class="trajet-meta">
      <?php if (!empty($trajet['heure_depart'])): ?>
        <span><i class="fas fa-clock"></i> <?= htmlspecialchars($trajet['heure_depart']) ?></span>
      <?php endif; ?>
      <?php if (!empty($trajet['prix_total'])): ?>
        <span><i class="fas fa-tag"></i> <?= number_format((float)$trajet['prix_total'], 2) ?> TND</span>
      <?php endif; ?>
      <?php if (!empty($trajet['conducteur_nom'])): ?>
        <span><i class="fas fa-user"></i> <?= htmlspecialchars($trajet['conducteur_prenom'] . ' ' . $trajet['conducteur_nom']) ?></span>
      <?php endif; ?>
    </div>
    <a href="reserver_vehicule.php?trajet_id=<?= $trajetId ?><?= $best ? '&vehicule_id=' . (int)$best['id'] : '' ?>" style="margin-left:auto;padding:.5rem 1.2rem;background:linear-gradient(135deg,#1565C0,#1976D2);border:none;border-radius:10px;color:#fff;text-decoration:none;font-size:.82rem;font-weight:600;white-space:nowrap;display:flex;align-items:center;gap:6px;box-shadow:0 4px 14px rgba(25,118,210,0.35);">
      <i class="fas fa-calendar-check"></i> Réserver
    </a>
  </div>
</div>

<!-- MAIN -->
<div class="main">

  <!-- LEFT COLUMN -->
  <div>

    <?php if ($best): ?>
    <!-- BEST VEHICLE BANNER -->
    <div class="best-banner">
      <div class="best-label"><i class="fas fa-crown"></i> Meilleure recommandation IA</div>
      <div class="best-body">
        <!-- Image -->
        <div class="best-img">
          <?php
            $photoPath = '/ecoride/assets/uploads/vehicules/' . ($best['photo'] ?? '');
            if (!empty($best['photo']) && file_exists($_SERVER['DOCUMENT_ROOT'] . $photoPath)):
          ?>
            <img src="<?= htmlspecialchars($photoPath) ?>" alt="Véhicule">
          <?php else: ?>
            <div class="no-img">🚗</div>
          <?php endif; ?>
        </div>
        <!-- Info -->
        <div class="best-info">
          <h2><?= htmlspecialchars(($best['marque'] ?? '—') . ' ' . ($best['modele'] ?? '')) ?></h2>
          <?php if (!empty($best['couleur'])): ?>
            <div style="font-size:.78rem;color:var(--muted);margin-bottom:.4rem">
              <i class="fas fa-palette"></i> <?= htmlspecialchars($best['couleur']) ?>
            </div>
          <?php endif; ?>
          <div class="tags">
            <?php if (($best['statut'] ?? '') === 'disponible'): ?>
              <span class="tag green"><i class="fas fa-circle" style="font-size:.45rem"></i> Disponible</span>
            <?php endif; ?>
            <span class="tag blue"><i class="fas fa-users"></i> <?= (int)($best['capacite'] ?? 4) ?> places</span>
            <?php if (!empty($best['climatisation']) && $best['climatisation'] !== '0'): ?>
              <span class="tag blue"><i class="fas fa-snowflake"></i> Clim</span>
            <?php endif; ?>
            <?php if ((int)($best['nb_reservations'] ?? 0) > 0): ?>
              <span class="tag amber"><i class="fas fa-star"></i> <?= (int)$best['nb_reservations'] ?> rés.</span>
            <?php endif; ?>
          </div>
          <!-- Raisons -->
          <div class="raisons-grid">
            <?php foreach (array_slice($best['raisons'], 0, 4) as $r): ?>
              <div class="raison-item <?= htmlspecialchars($r['type']) ?>">
                <i class="fas <?= htmlspecialchars($r['icon'] ?? 'fa-check') ?>"></i>
                <?= htmlspecialchars($r['texte']) ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <!-- Score + CTA -->
        <div class="best-right">
          <?php $pct = (int)($best['pertinence'] ?? 0); $R=30; $C=2*M_PI*$R; $dash=$C*($pct/100); ?>
          <div class="score-ring">
            <svg width="78" height="78" viewBox="0 0 78 78">
              <circle cx="39" cy="39" r="<?= $R ?>" fill="none" stroke="rgba(255,255,255,0.06)" stroke-width="6"/>
              <circle cx="39" cy="39" r="<?= $R ?>" fill="none" stroke="#22c55e"
                stroke-width="6" stroke-linecap="round"
                stroke-dasharray="<?= number_format($dash,1) ?> <?= number_format($C,1) ?>"/>
            </svg>
            <div class="score-num"><?= $pct ?><small>%</small></div>
          </div>
          <div class="score-label">Score IA</div>
          <a href="reserver_vehicule.php?vehicule_id=<?= (int)$best['id'] ?>&trajet_id=<?= $trajetId ?>" class="btn-reserve">
            <i class="fas fa-calendar-check"></i> Réserver ce véhicule
          </a>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- ALL VEHICLES LIST -->
    <div class="sec-hdr">
      <i class="fas fa-list" style="color:var(--blue-bright);font-size:.8rem"></i>
      <h3>Tous les véhicules disponibles</h3>
      <span class="cnt"><?= count($vehicules) ?></span>
    </div>

    <?php if (empty($vehicules)): ?>
      <div class="empty-state">
        <div class="ei">🚗</div>
        <h3>Aucun véhicule disponible</h3>
        <p>Aucun véhicule n'est disponible pour le moment.</p>
      </div>
    <?php else: ?>
      <?php foreach ($vehicules as $i => $v):
        $pct2 = (int)($v['pertinence'] ?? 0);
        $color2 = $pct2 >= 70 ? '#22c55e' : ($pct2 >= 45 ? '#f59e0b' : '#64748b');
        $R2=19; $C2=2*M_PI*$R2; $dash2=$C2*($pct2/100);
        $isBest = $best && $v['id'] === $best['id'];
        $photoPath2 = '/ecoride/assets/uploads/vehicules/' . ($v['photo'] ?? '');
        $hasPhoto2 = !empty($v['photo']) && file_exists($_SERVER['DOCUMENT_ROOT'] . $photoPath2);
      ?>
      <div class="vcard <?= $isBest ? 'selected' : '' ?>" onclick="selectVehicule(<?= (int)$v['id'] ?>, '<?= htmlspecialchars(addslashes($v['marque'] . ' ' . $v['modele'])) ?>')">
        <div class="vcard-img">
          <?php if ($hasPhoto2): ?>
            <img src="<?= htmlspecialchars($photoPath2) ?>" alt="Véhicule">
          <?php else: ?>
            <div class="no-img">🚗</div>
          <?php endif; ?>
        </div>
        <div class="vcard-info">
          <h4>
            <?= htmlspecialchars(($v['marque'] ?? '—') . ' ' . ($v['modele'] ?? '')) ?>
            <?php if ($isBest): ?>
              <span style="font-size:.65rem;background:var(--green);color:#000;padding:2px 8px;border-radius:99px;font-weight:800;margin-left:6px;vertical-align:middle;">★ Optimal</span>
            <?php endif; ?>
          </h4>
          <div class="vtags">
            <?php if (($v['statut'] ?? '') === 'disponible'): ?>
              <span class="tag green" style="font-size:.65rem;padding:.2rem .55rem"><i class="fas fa-circle" style="font-size:.4rem"></i> Dispo</span>
            <?php endif; ?>
            <span class="tag blue" style="font-size:.65rem;padding:.2rem .55rem"><i class="fas fa-users"></i> <?= (int)($v['capacite'] ?? 4) ?></span>
            <?php if (!empty($v['climatisation']) && $v['climatisation'] !== '0'): ?>
              <span class="tag blue" style="font-size:.65rem;padding:.2rem .55rem"><i class="fas fa-snowflake"></i></span>
            <?php endif; ?>
            <?php if ((int)($v['nb_reservations'] ?? 0) > 0): ?>
              <span class="tag amber" style="font-size:.65rem;padding:.2rem .55rem"><i class="fas fa-star"></i> <?= (int)$v['nb_reservations'] ?></span>
            <?php endif; ?>
          </div>
          <div style="margin-top:.4rem">
            <?php foreach (array_slice($v['raisons'], 0, 2) as $r): ?>
              <div class="raison-item <?= htmlspecialchars($r['type']) ?>" style="padding:.25rem .5rem;font-size:.7rem;border-radius:6px;margin-bottom:2px">
                <i class="fas <?= htmlspecialchars($r['icon'] ?? 'fa-check') ?>"></i>
                <?= htmlspecialchars($r['texte']) ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="vcard-right">
          <div class="mini-ring">
            <svg width="50" height="50" viewBox="0 0 50 50">
              <circle cx="25" cy="25" r="<?= $R2 ?>" fill="none" stroke="rgba(255,255,255,0.06)" stroke-width="4.5"/>
              <circle cx="25" cy="25" r="<?= $R2 ?>" fill="none" stroke="<?= $color2 ?>"
                stroke-width="4.5" stroke-linecap="round"
                stroke-dasharray="<?= number_format($dash2,1) ?> <?= number_format($C2,1) ?>"/>
            </svg>
            <div class="mini-num" style="color:<?= $color2 ?>"><?= $pct2 ?>%</div>
          </div>
          <a href="reserver_vehicule.php?vehicule_id=<?= (int)$v['id'] ?>&trajet_id=<?= $trajetId ?>"
             class="btn-choisir <?= $isBest ? 'selected' : '' ?>"
             onclick="event.stopPropagation()">
            <i class="fas fa-<?= $isBest ? 'check' : 'arrow-right' ?>"></i>
            <?= $isBest ? 'Sélectionné' : 'Choisir' ?>
          </a>
        </div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>

  </div>

  <!-- SIDEBAR -->
  <div class="sidebar">

    <div class="sc-card">
      <h5><i class="fas fa-brain" style="color:var(--purple)"></i> Critères IA</h5>
      <div style="display:flex;flex-direction:column;gap:.65rem">
        <?php
          $criteria = [
            ['icon'=>'fa-check-circle','color'=>'var(--green)','label'=>'Disponibilité','desc'=>'Véhicule libre et prêt','weight'=>50],
            ['icon'=>'fa-users','color'=>'var(--blue-bright)','label'=>'Capacité','desc'=>'Nombre de places disponibles','weight'=>25],
            ['icon'=>'fa-star','color'=>'var(--amber)','label'=>'Popularité','desc'=>'Historique des réservations','weight'=>20],
            ['icon'=>'fa-snowflake','color'=>'var(--blue-bright)','label'=>'Confort','desc'=>'Climatisation incluse','weight'=>15],
            ['icon'=>'fa-camera','color'=>'var(--muted)','label'=>'Confiance','desc'=>'Photo disponible','weight'=>5],
          ];
          foreach ($criteria as $c):
        ?>
          <div style="display:flex;align-items:center;gap:.75rem">
            <div style="width:32px;height:32px;border-radius:8px;background:rgba(255,255,255,0.04);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;flex-shrink:0">
              <i class="fas <?= $c['icon'] ?>" style="font-size:.75rem;color:<?= $c['color'] ?>"></i>
            </div>
            <div style="flex:1">
              <div style="font-size:.78rem;font-weight:600;display:flex;justify-content:space-between">
                <span><?= $c['label'] ?></span>
                <span style="color:var(--muted);font-weight:400"><?= $c['weight'] ?>pts</span>
              </div>
              <div style="font-size:.68rem;color:var(--muted)"><?= $c['desc'] ?></div>
              <div style="height:3px;background:rgba(255,255,255,0.05);border-radius:99px;margin-top:4px">
                <div style="height:100%;width:<?= round($c['weight']/115*100) ?>%;background:<?= $c['color'] ?>;border-radius:99px"></div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="sc-card">
      <h5><i class="fas fa-lightbulb" style="color:var(--amber)"></i> Conseils</h5>
      <div style="display:flex;flex-direction:column;gap:.5rem;font-size:.78rem;color:var(--muted)">
        <div style="display:flex;gap:7px"><span style="color:var(--blue-bright);flex-shrink:0">•</span>Le score IA reflète la meilleure adéquation globale</div>
        <div style="display:flex;gap:7px"><span style="color:var(--blue-bright);flex-shrink:0">•</span>La disponibilité est le critère le plus important</div>
        <div style="display:flex;gap:7px"><span style="color:var(--blue-bright);flex-shrink:0">•</span>Plus un véhicule a de réservations, plus il est fiable</div>
        <div style="display:flex;gap:7px"><span style="color:var(--blue-bright);flex-shrink:0">•</span>Vérifiez la capacité selon votre groupe</div>
      </div>
    </div>

    <?php if ($best): ?>
    <div class="sc-card" style="border-color:rgba(34,197,94,0.3);background:rgba(34,197,94,0.04)">
      <h5><i class="fas fa-bolt" style="color:var(--green)"></i> Réservation rapide</h5>
      <p style="font-size:.78rem;color:var(--muted);margin-bottom:.85rem">Réservez directement le véhicule optimal sélectionné par l'IA.</p>
      <a href="reserver_vehicule.php?vehicule_id=<?= (int)$best['id'] ?>&trajet_id=<?= $trajetId ?>" class="btn-reserve" style="width:100%;justify-content:center">
        <i class="fas fa-calendar-check"></i> Réserver maintenant
      </a>
    </div>
    <?php endif; ?>

  </div>
</div>

<!-- STICKY CTA (appears when scrolled past best banner) -->
<?php if ($best): ?>
<div class="sticky-cta" id="stickyCta" style="max-width:1100px;margin:0 auto 1.5rem;">
  <div class="vname">
    <i class="fas fa-crown" style="color:var(--amber)"></i>
    <?= htmlspecialchars(($best['marque'] ?? '') . ' ' . ($best['modele'] ?? '')) ?>
    <small>Véhicule optimal IA — Score <?= (int)($best['pertinence'] ?? 0) ?>%</small>
  </div>
  <a href="reserver_vehicule.php?vehicule_id=<?= (int)$best['id'] ?>&trajet_id=<?= $trajetId ?>" class="btn-reserve-cta">
    <i class="fas fa-calendar-check"></i> Réserver ce véhicule
  </a>
</div>
<?php endif; ?>

<script>
function selectVehicule(id, name) {
  document.querySelectorAll('.vcard').forEach(c => c.classList.remove('selected'));
  document.querySelectorAll('.btn-choisir').forEach(b => {
    b.classList.remove('selected');
    b.innerHTML = '<i class="fas fa-arrow-right"></i> Choisir';
  });
  const card = event.currentTarget;
  card.classList.add('selected');
  const btn = card.querySelector('.btn-choisir');
  if (btn) {
    btn.classList.add('selected');
    btn.innerHTML = '<i class="fas fa-check"></i> Sélectionné';
  }
}

// Sticky CTA observer
<?php if ($best): ?>
const observer = new IntersectionObserver(entries => {
  const cta = document.getElementById('stickyCta');
  if (cta) cta.classList.toggle('show', !entries[0].isIntersecting);
}, { threshold: 0 });
const bestBanner = document.querySelector('.best-banner');
if (bestBanner) observer.observe(bestBanner);
<?php endif; ?>
</script>
</body>
</html>