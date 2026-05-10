<?php
// dashboard_event.php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../models/Event.php';
require_once __DIR__ . '/../../models/Sponsor.php';
require_once __DIR__ . '/partials/partials.php';

use Model\Event;
use Model\Sponsor;

// Vérifier si l'admin est connecté
if (!isset($_SESSION['admin_id'])) {
    header('Location: ' . BASE_URL . 'controllers/AdminController.php?action=showLogin');
    exit();
}

$eventModel = new Event();
$sponsorModel = new Sponsor();

$totalEvents = $eventModel->countAllEvents();
$upcomingEvents = $eventModel->countUpcomingEvents();
$totalSponsors = $sponsorModel->countAll();
$totalSponsoring = $sponsorModel->getTotalMontant();
$recentEvents = $eventModel->getUpcoming(5);
$topSponsors = $sponsorModel->getTopSponsors(5);
$monthlyStats = $eventModel->getStatsByMonth();

if($recentEvents === null) $recentEvents = [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eco Ride - Gestion des Événements</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <?php render_nav_css(); ?>
    <style>
        /* Reset et base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #0A1628 0%, #0D1F3A 100%);
            min-height: 100vh;
            color: #F4F5F7;
        }

        /* Layout principal */
        .app-wrapper {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        /* Sidebar fixe à gauche */
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, #1976D2 0%, #1565C0 40%, #0F3B6E 100%);
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            padding: 2rem 1rem;
            overflow-y: auto;
            z-index: 100;
            display: flex;
            flex-direction: column;
        }

        /* Contenu principal à droite */
        .main-content {
            margin-left: 260px;
            width: calc(100% - 260px);
            min-height: 100vh;
            padding: 1.5rem 2rem;
        }

        /* Top bar */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            background: linear-gradient(90deg, #0D2350 0%, #0F3166 50%, #0D2350 100%);
            border-radius: 16px;
            padding: 0.75rem 1.5rem;
            border: 1px solid rgba(97,179,250,0.18);
            box-shadow: 0 4px 24px rgba(0,0,0,0.25);
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .navbar-logo strong {
            font-size: 1rem;
            font-weight: 800;
            color: #61B3FA;
            letter-spacing: 0.05em;
        }

        .navbar-logo span {
            font-size: 0.62rem;
            color: rgba(255,255,255,0.75);
            letter-spacing: 0.08em;
        }

        .top-bar-right {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .btn-top, .btn-admin-profile, .btn-admin-plain, .btn-theme-toggle {
            background: transparent;
            color: white;
            padding: 0.4rem 1rem;
            border-radius: 25px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            font-size: 0.85rem;
            border: 1px solid rgba(255,255,255,0.18);
            cursor: pointer;
        }

        .btn-top:hover, .btn-admin-profile:hover, .btn-admin-plain:hover {
            background: rgba(255,255,255,0.12);
        }

        .btn-admin-plain {
            border-color: rgba(231,76,60,0.45);
            color: #E74C3C;
        }

        .btn-theme-toggle {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            justify-content: center;
        }

        /* Stats cards */
        .stats-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: rgba(13, 31, 45, 0.9);
            border-radius: 20px;
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid rgba(25,118,210, 0.3);
            flex: 1;
            min-width: 180px;
            transition: all 0.3s;
        }

        .stat-card:hover {
            border-color: #1976D2;
            transform: translateY(-5px);
        }

        .stat-card h3 {
            font-size: 2rem;
            color: #1976D2;
            margin: 0;
        }

        .stat-card p {
            color: #61B3FA;
            margin: 0;
            font-size: 0.85rem;
        }

        .stat-icon i {
            font-size: 2rem;
            color: #1976D2;
        }

        /* Cards */
        .card {
            background: rgba(13, 31, 45, 0.9);
            border-radius: 20px;
            padding: 1.5rem;
            border: 1px solid rgba(25,118,210, 0.3);
            margin-bottom: 1.5rem;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .card-header h2 {
            color: #61B3FA;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Boutons actions */
        .dashboard-actions {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .btn-action {
            background: linear-gradient(135deg, #1976D2, #1565C0);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
            font-weight: 500;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            background: linear-gradient(135deg, #1e88e5, #1976D2);
        }

        .btn-primary {
            background: linear-gradient(135deg, #1976D2, #1565C0);
            color: white;
            padding: 0.5rem 1.2rem;
            border-radius: 25px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-edit {
            background: rgba(25,118,210,0.2);
            color: #1976D2;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            text-decoration: none;
            font-size: 0.8rem;
            margin-right: 0.5rem;
        }

        .btn-delete {
            background: rgba(255,68,68,0.2);
            color: #ff4444;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            text-decoration: none;
            font-size: 0.8rem;
        }

        /* Badges */
        .badge-ouvert {
            background: rgba(0,255,136,0.15);
            color: #00ff88;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: 0.7rem;
        }

        .badge-complet {
            background: rgba(255,165,0,0.15);
            color: #ffa500;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: 0.7rem;
        }

        .badge-annule {
            background: rgba(255,68,68,0.15);
            color: #ff6666;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: 0.7rem;
        }

        /* Tableau */
        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        th {
            color: #1976D2;
            font-weight: 600;
        }

        tr:hover {
            background: rgba(25,118,210,0.05);
        }

        /* Layout 2 colonnes pour les graphiques */
        .charts-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        /* Footer */
        footer {
            text-align: center;
            padding: 2rem;
            border-top: 1px solid rgba(25,118,210, 0.2);
            color: #A7A9AC;
            margin-top: 2rem;
        }

        /* Light mode */
        body.light-mode {
            background: linear-gradient(135deg, #EDF2F7 0%, #DBEAFE 100%) !important;
            color: #1A2844 !important;
        }

        body.light-mode .card,
        body.light-mode .stat-card {
            background: rgba(255,255,255,0.95);
        }

        body.light-mode td {
            color: #1A2844;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
            }
            .sidebar.open {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 1rem;
            }
            .stats-grid {
                flex-direction: column;
            }
            .charts-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="app-wrapper">
        <!-- Sidebar gauche -->
        <?php sidebar_spa('evenements'); ?>

        <!-- Contenu principal droite -->
        <div class="main-content">
            <!-- Top bar -->
            <div class="top-bar">
                <div class="navbar-logo">
                    <strong>ECO RIDE</strong>
                    <span>Covoiturage Intelligent</span>
                </div>
                <div class="top-bar-right">
                    <a href="<?= BASE_URL ?>controllers/UserController.php?action=showLoginForm#hero" class="btn-top">Voir site</a>
                    <a href="<?= BASE_URL ?>controllers/AdminController.php?action=showProfile" class="btn-admin-profile">
                        <div class="admin-avatar-btn">
                            <?php if (!empty($_SESSION['admin_photo'])): ?>
                                <img src="<?= BASE_URL ?>uploads/photos/<?= htmlspecialchars($_SESSION['admin_photo']) ?>" alt="" style="width:24px;height:24px;border-radius:50%;object-fit:cover;">
                            <?php else: ?>
                                <i class="fas fa-user-shield"></i>
                            <?php endif; ?>
                        </div>
                        Profil
                    </a>
                    <a href="<?= BASE_URL ?>controllers/AdminController.php?action=dashboard" class="btn-admin-plain">Admin</a>
                    <button class="btn-theme-toggle" onclick="toggleTheme()">
                        <i class="fas fa-moon themeIcon"></i>
                    </button>
                </div>
            </div>

            <!-- Contenu spécifique événements -->
            <div class="dashboard-actions">
                <a href="events/list.php" class="btn-action">
                    <i class="fas fa-calendar-alt"></i> Gérer les événements
                </a>
                <a href="sponsors/list.php" class="btn-action">
                    <i class="fas fa-handshake"></i> Gérer les sponsors
                </a>
            </div>

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div>
                        <h3><?= $totalEvents ?></h3>
                        <p>Total événements</p>
                    </div>
                    <div class="stat-icon"><i class="fas fa-calendar-alt"></i></div>
                </div>
                <div class="stat-card">
                    <div>
                        <h3><?= $upcomingEvents ?></h3>
                        <p>Événements à venir</p>
                    </div>
                    <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
                </div>
                <div class="stat-card">
                    <div>
                        <h3><?= $totalSponsors ?></h3>
                        <p>Sponsors actifs</p>
                    </div>
                    <div class="stat-icon"><i class="fas fa-handshake"></i></div>
                </div>
                <div class="stat-card">
                    <div>
                        <h3><?= number_format($totalSponsoring, 0, ',', ' ') ?> DT</h3>
                        <p>Sponsoring total</p>
                    </div>
                    <div class="stat-icon"><i class="fas fa-coins"></i></div>
                </div>
            </div>

            <!-- Graphiques -->
            <div class="charts-row">
                <div class="card">
                    <h3 style="color:#61B3FA; margin-bottom:1rem;"><i class="fas fa-chart-bar"></i> Top 5 Sponsors</h3>
                    <canvas id="sponsorsChart" height="250"></canvas>
                </div>
                <div class="card">
                    <h3 style="color:#61B3FA; margin-bottom:1rem;"><i class="fas fa-chart-line"></i> Événements par mois</h3>
                    <canvas id="eventsChart" height="250"></canvas>
                </div>
            </div>

            <!-- Événements récents -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-calendar-alt"></i> Événements récents</h2>
                    <a href="events/form.php" class="btn-primary"><i class="fas fa-plus"></i> Ajouter</a>
                </div>
                <?php if(empty($recentEvents)): ?>
                    <p style="text-align:center;padding:2rem; color:#A7A9AC;">Aucun événement</p>
                <?php else: ?>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr><th>Titre</th><th>Ville</th><th>Date</th><th>Places</th><th>Statut</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach($recentEvents as $e): ?>
                        <tr>
                            <td><?= htmlspecialchars($e['titre']) ?></td>
                            <td><?= htmlspecialchars($e['ville']) ?></td>
                            <td><?= date('d/m/Y', strtotime($e['date_evenement'])) ?></td>
                            <td><?= $e['nb_places'] ?></td>
                            <td><span class="badge-<?= $e['statut'] == 'ouvert' ? 'ouvert' : ($e['statut'] == 'complet' ? 'complet' : 'annule') ?>"><?= $e['statut'] ?></span></td>
                            <td>
                                <a href="events/form.php?id=<?= $e['id'] ?>" class="btn-edit">Modifier</a>
                                <a href="events/list.php?delete=<?= $e['id'] ?>" class="btn-delete" onclick="return confirmDelete()">Supprimer</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>

            <footer>
                <p>
                    <svg width="16" height="16" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align:middle">
                        <path d="M22 4C22 4 8 10 8 24C8 31.732 14.268 38 22 38C29.732 38 36 31.732 36 24C36 14 28 8 22 4Z" fill="#61B3FA" opacity="0.9"/>
                    </svg> 
                    Eco Ride by Echo Group © 2025 - Gestion des Événements
                </p>
            </footer>
        </div>
    </div>

    <script>
        function confirmDelete() { return confirm('Supprimer définitivement cet événement ?'); }

        // Graphique Sponsors
        const sponsorsCtx = document.getElementById('sponsorsChart');
        if (sponsorsCtx && <?= json_encode(array_column($topSponsors, 'nom_entreprise')) ?> !== null) {
            new Chart(sponsorsCtx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode(array_column($topSponsors, 'nom_entreprise')) ?>,
                    datasets: [{ 
                        label: 'Montant sponsoring (DT)', 
                        data: <?= json_encode(array_column($topSponsors, 'montant_sponsoring')) ?>, 
                        backgroundColor: 'rgba(97,179,250,0.7)', 
                        borderColor: '#61B3FA', 
                        borderRadius: 8 
                    }]
                },
                options: { 
                    responsive: true, 
                    maintainAspectRatio: true,
                    scales: { 
                        y: { ticks: { color: '#A7A9AC' }, grid: { color: 'rgba(255,255,255,0.05)' } }, 
                        x: { ticks: { color: '#A7A9AC' } } 
                    }, 
                    plugins: { legend: { labels: { color: '#A7A9AC' } } } 
                }
            });
        }

        // Graphique Événements par mois
        const months = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
        const eventsData = Array(12).fill(0);
        <?php foreach($monthlyStats as $stat): ?> eventsData[<?= $stat['mois'] - 1 ?>] = <?= $stat['total'] ?>; <?php endforeach; ?>
        
        const eventsCtx = document.getElementById('eventsChart');
        if (eventsCtx) {
            new Chart(eventsCtx, {
                type: 'line',
                data: { 
                    labels: months, 
                    datasets: [{ 
                        label: "Nombre d'événements", 
                        data: eventsData, 
                        backgroundColor: 'rgba(97,179,250,0.1)', 
                        borderColor: '#61B3FA', 
                        borderWidth: 3, 
                        fill: true, 
                        tension: 0.3, 
                        pointBackgroundColor: '#61B3FA', 
                        pointBorderColor: '#61B3FA' 
                    }] 
                },
                options: { 
                    responsive: true, 
                    maintainAspectRatio: true,
                    scales: { 
                        y: { ticks: { color: '#A7A9AC', stepSize: 1 }, grid: { color: 'rgba(255,255,255,0.05)' } }, 
                        x: { ticks: { color: '#A7A9AC' } } 
                    }, 
                    plugins: { legend: { labels: { color: '#A7A9AC' } } } 
                }
            });
        }

        // Thème clair/sombre
        function toggleTheme() {
            document.body.classList.toggle('light-mode');
            const isLight = document.body.classList.contains('light-mode');
            document.querySelectorAll('.themeIcon').forEach(i => {
                i.className = isLight ? 'fas fa-sun themeIcon' : 'fas fa-moon themeIcon';
            });
            localStorage.setItem('ecoride_theme', isLight ? 'light' : 'dark');
        }
        
        (function() {
            if (localStorage.getItem('ecoride_theme') === 'light') {
                document.body.classList.add('light-mode');
                document.querySelectorAll('.themeIcon').forEach(i => { i.className = 'fas fa-sun themeIcon'; });
            }
        })();
    </script>
<?php require_once __DIR__ . '/ai_helper_widget.php'; ?>
</body>
</html>