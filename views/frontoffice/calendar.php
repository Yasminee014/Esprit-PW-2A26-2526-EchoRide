<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../models/Event.php';

use Model\Event;

$eventModel = new Event();
$events = $eventModel->getAll();

$calendarEvents = [];
foreach($events as $event) {
    $calendarEvents[] = [
        'id' => $event['id'],
        'title' => $event['titre'],
        'start' => $event['date_evenement'],
        'url' => 'events-detail.php?id=' . $event['id'],
        'color' => getEventColor($event['type']),
        'ville' => $event['ville'],
        'type' => $event['type']
    ];
}

function getEventColor($type) {
    switch($type) {
        case 'concert': return '#e74c3c';
        case 'match': return '#27ae60';
        case 'festival': return '#f39c12';
        case 'sortie': return '#3498db';
        default: return '#1976D2';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Eco Ride - Calendrier des événements</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/fr.js"></script>
<link rel="stylesheet" href="<?= BASE_URL ?>views/frontoffice/navbar-front.css">
<?php define('ECORIDE_NAVBAR_CSS_LINKED', true); ?>
<link rel="stylesheet" href="../../style.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">

<style>
:root {
    --primary-blue: #61B3FA;
    --primary-dark: #1976D2;
    --accent-red: #e74c3c;
    --accent-green: #27ae60;
    --accent-orange: #f39c12;
    --accent-cyan: #3498db;
    --bg-dark: #0f172e;
    --bg-lighter: rgba(255,255,255,0.03);
    --border-color: rgba(97,179,250,0.15);
    --text-light: #ffffff;
    --text-muted: #A7A9AC;
}

html, body {
    background: linear-gradient(135deg, #0f172e 0%, #1a2447 100%);
    color: var(--text-light);
    font-family: 'Poppins', sans-serif;
}

.calendar-page {
    max-width: 1600px;
    margin: 0 auto;
    padding: 3rem 2rem;
    min-height: 100vh;
}

/* Header Section */
.page-header {
    text-align: center;
    margin-bottom: 3rem;
    position: relative;
    z-index: 1;
}

.page-header h1 {
    font-family: 'Playfair Display', serif;
    font-size: 3.2rem;
    font-weight: 700;
    background: linear-gradient(135deg, var(--primary-blue) 0%, #60D9FF 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 0.5rem;
    letter-spacing: -1px;
}

.page-header p {
    color: var(--text-muted);
    font-size: 1.1rem;
    font-weight: 300;
    margin-top: 0.5rem;
}

.header-icon {
    display: inline-flex;
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, rgba(97,179,250,0.2) 0%, rgba(97,179,250,0.05) 100%);
    border-radius: 20px;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.5rem;
    border: 1px solid var(--border-color);
    font-size: 2rem;
    color: var(--primary-blue);
}

/* Legend Section */
.legend {
    display: flex;
    justify-content: center;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 2.5rem;
    padding: 1.2rem 1.8rem;
    background: linear-gradient(135deg, rgba(97,179,250,0.08) 0%, rgba(97,179,250,0.02) 100%);
    border-radius: 60px;
    border: 1px solid var(--border-color);
    backdrop-filter: blur(10px);
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 0.5rem 1rem;
    background: rgba(255,255,255,0.03);
    border-radius: 40px;
    border: 1px solid rgba(97,179,250,0.1);
    transition: all 0.3s ease;
}

.legend-item:hover {
    background: rgba(97,179,250,0.15);
    transform: translateY(-2px);
}

.legend-color {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
}

.legend-color.concert { background: linear-gradient(135deg, #e74c3c, #c0392b); }
.legend-color.match { background: linear-gradient(135deg, #27ae60, #229954); }
.legend-color.festival { background: linear-gradient(135deg, #f39c12, #d68910); }
.legend-color.sortie { background: linear-gradient(135deg, #3498db, #2980b9); }
.legend-color.autre { background: linear-gradient(135deg, #1976D2, #1565c0); }

.legend-text {
    font-size: 0.85rem;
    font-weight: 500;
    color: var(--text-light);
}

/* Calendar Container */
.calendar-container {
    background: rgba(20, 30, 55, 0.6);
    border-radius: 24px;
    padding: 1.8rem;
    border: 1px solid var(--border-color);
    backdrop-filter: blur(8px);
    box-shadow: 0 20px 50px rgba(0,0,0,0.3);
}

/* FullCalendar Customization */
.fc {
    color: var(--text-light);
}

.fc-toolbar-title {
    color: var(--primary-blue) !important;
    font-family: 'Playfair Display', serif;
    font-size: 1.6rem !important;
    font-weight: 700 !important;
}

.fc-toolbar {
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.8rem;
}

.fc-button-primary {
    background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-blue) 100%) !important;
    border: none !important;
    border-radius: 40px !important;
    padding: 0.5rem 1.2rem !important;
    font-weight: 600 !important;
    transition: all 0.3s ease !important;
    text-transform: capitalize;
}

.fc-button-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(97, 179, 250, 0.4) !important;
}

.fc-button-primary.fc-button-active {
    background: linear-gradient(135deg, #60D9FF 0%, var(--primary-blue) 100%) !important;
}

.fc-daygrid-day {
    border-color: var(--border-color) !important;
    background: rgba(255,255,255,0.02);
    transition: all 0.2s ease;
}

.fc-daygrid-day:hover {
    background: rgba(97,179,250,0.05);
}

.fc-day-today {
    background: linear-gradient(135deg, rgba(97,179,250,0.15) 0%, rgba(97,179,250,0.05) 100%) !important;
    border: 1px solid var(--primary-blue) !important;
}

.fc-daygrid-day-number {
    color: var(--text-light) !important;
    padding: 0.5rem !important;
    font-weight: 500;
}

.fc-col-header-cell {
    background: rgba(97,179,250,0.08) !important;
    padding: 0.8rem 0.5rem !important;
}

.fc-col-header-cell-cushion {
    color: var(--primary-blue) !important;
    font-weight: 600 !important;
}

/* Event Styling */
.fc-event {
    cursor: pointer;
    transition: all 0.3s ease;
    border-radius: 20px !important;
    border: none !important;
    margin: 2px 4px !important;
    padding: 2px 6px !important;
}

.fc-event-title {
    padding: 2px 4px !important;
    font-weight: 500;
    font-size: 0.8rem;
}

.fc-event:hover {
    transform: scale(1.02) translateY(-2px) !important;
    filter: brightness(1.1);
    box-shadow: 0 6px 16px rgba(0,0,0,0.3) !important;
}

/* List View */
.fc-list-day-cushion {
    background: rgba(97,179,250,0.15) !important;
}

.fc-list-event:hover td {
    background: rgba(97,179,250,0.1) !important;
}

/* Responsive */
@media (max-width: 1024px) {
    .calendar-page {
        padding: 2rem 1.5rem;
    }
    .page-header h1 {
        font-size: 2.5rem;
    }
    .calendar-container {
        padding: 1.2rem;
    }
}

@media (max-width: 768px) {
    .calendar-page {
        padding: 1.5rem 1rem;
    }
    .page-header h1 {
        font-size: 1.8rem;
    }
    .page-header p {
        font-size: 0.9rem;
    }
    .header-icon {
        width: 50px;
        height: 50px;
        font-size: 1.5rem;
    }
    .legend {
        border-radius: 20px;
        padding: 1rem;
        gap: 0.5rem;
    }
    .legend-item {
        padding: 0.3rem 0.8rem;
    }
    .legend-text {
        font-size: 0.75rem;
    }
    .fc-toolbar {
        flex-direction: column;
        align-items: stretch;
    }
    .fc-toolbar-title {
        font-size: 1.2rem !important;
        text-align: center;
    }
    .fc-button-group {
        display: flex;
        justify-content: center;
    }
    .calendar-container {
        padding: 0.8rem;
        border-radius: 16px;
    }
}

@media (max-width: 480px) {
    .page-header h1 {
        font-size: 1.4rem;
    }
    .legend {
        flex-wrap: wrap;
        justify-content: center;
    }
    .fc-event-title {
        font-size: 0.7rem;
    }
}

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.page-header {
    animation: fadeInUp 0.6s ease-out;
}

.legend {
    animation: fadeInUp 0.6s ease-out 0.1s both;
}

.calendar-container {
    animation: fadeInUp 0.6s ease-out 0.2s both;
}
</style>
</head>
<body>

<?php include_once __DIR__ . '/navbar.php'; ?>

<div class="calendar-page">
    <div class="page-header">
        <div class="header-icon">
            <i class="fas fa-calendar-alt"></i>
        </div>
        <h1>Calendrier des Événements</h1>
        <p>Découvrez tous nos événements sur une carte interactive - Concerts, Matchs, Festivals et Sorties</p>
    </div>
    
    <div class="legend">
        <div class="legend-item">
            <div class="legend-color concert"></div>
            <span class="legend-text">Concert</span>
        </div>
        <div class="legend-item">
            <div class="legend-color match"></div>
            <span class="legend-text">Match</span>
        </div>
        <div class="legend-item">
            <div class="legend-color festival"></div>
            <span class="legend-text">Festival</span>
        </div>
        <div class="legend-item">
            <div class="legend-color sortie"></div>
            <span class="legend-text">Sortie</span>
        </div>
        <div class="legend-item">
            <div class="legend-color autre"></div>
            <span class="legend-text">Autre</span>
        </div>
    </div>
    
    <div class="calendar-container">
        <div id="calendar"></div>
    </div>
</div>

<footer>
    <p><i class="fas fa-leaf"></i> Eco Ride © 2025 - Covoiturage Intelligent</p>
</footer>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'fr',
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,dayGridWeek,listMonth'
        },
        events: <?= json_encode($calendarEvents) ?>,
        eventClick: function(info) {
            if(info.event.url) {
                window.location.href = info.event.url;
            }
        },
        height: 'auto',
        buttonText: {
            today: 'Aujourd\'hui',
            month: 'Mois',
            week: 'Semaine',
            list: 'Liste'
        },
        eventDisplay: 'block',
        displayEventTime: false,
        dayMaxEvents: 3,
        moreLinkText: '+ voir',
        firstDay: 1
    });
    calendar.render();
});
</script>
<?php require_once __DIR__ . '/chatbot_widget.php'; ?>
</body>
</html>