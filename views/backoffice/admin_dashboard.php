<?php
require_once __DIR__ . '/partials/partials.php';
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../models/User.php';   // on inclut le modèle User

// Vérifier si l'admin est connecté
if (!isset($_SESSION['admin_id'])) {
    header('Location: ' . BASE_URL . 'controllers/AdminController.php?action=showLogin');
    exit();
}

// Instanciation du modèle User (connexion PDO automatique via constructeur)
$userModel = new User();

// Récupération des statistiques
$stats = [
    'total_passagers'    => $userModel->countTotalPassagers(),
    'active_passagers'   => $userModel->countActivePassagers(),
    'inactive_passagers' => $userModel->countInactivePassagers(),
    'total_admins'       => $userModel->countTotalAdmins(),
];

// Récupération de la liste des passagers
$passagers = $userModel->getAllPassagers();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eco Ride - Dashboard Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        /* ── Chatbot Styles ── */
/* ── Chatbot Styles (positionné en haut à droite) ── */
/* ── Chatbot Styles (positionné tout en bas) ── */
.chatbot-toggle {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, #1976D2, #1565C0);
    border: none;
    color: white;
    font-size: 1.6rem;
    cursor: pointer;
    box-shadow: 0 4px 20px rgba(25,118,210,0.4);
    z-index: 997;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.chatbot-toggle:hover {
    transform: scale(1.08);
    background: linear-gradient(135deg, #1e88e5, #1976D2);
}

.chatbot-container {
    position: fixed;
    bottom: 100px;
    right: 30px;
    width: 420px;
    height: 520px;
    background: linear-gradient(160deg, #0D1F3A 0%, #0A1628 100%);
    border-radius: 20px;
    border: 1px solid rgba(25,118,210,0.4);
    box-shadow: 0 10px 40px rgba(0,0,0,0.5);
    z-index: 998;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transition: all 0.3s ease;
    opacity: 0;
    visibility: hidden;
    transform: translateY(20px);
}

.chatbot-container.open {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

/* Ajustement pour petit écran */
@media (max-width: 600px) {
    .chatbot-container {
        width: calc(100vw - 40px);
        right: 20px;
        bottom: 80px;
        height: 480px;
    }
    .chatbot-toggle {
        bottom: 20px;
        right: 20px;
    }
}

        body.light-mode .chatbot-container {
            background: linear-gradient(160deg, #ffffff 0%, #f0f4fa 100%);
            border-color: #1976D2;
        }

        .chatbot-header {
            background: linear-gradient(135deg, #1976D2, #1565C0);
            padding: 1rem 1.2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
        }

        .chatbot-header h3 {
            color: white;
            margin: 0;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .chatbot-header h3 i {
            font-size: 1.1rem;
        }

        .chatbot-close {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }

        .chatbot-close:hover {
            background: rgba(255,68,68,0.6);
        }

        .chatbot-messages {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .chatbot-messages::-webkit-scrollbar {
            width: 4px;
        }

        .chatbot-messages::-webkit-scrollbar-track {
            background: transparent;
        }

        .chatbot-messages::-webkit-scrollbar-thumb {
            background: rgba(25,118,210,0.5);
            border-radius: 4px;
        }

        .message {
            max-width: 85%;
            padding: 8px 12px;
            border-radius: 16px;
            font-size: 0.85rem;
            line-height: 1.4;
            word-wrap: break-word;
        }

        .message.user {
            background: #1976D2;
            color: white;
            align-self: flex-end;
            border-bottom-right-radius: 4px;
        }

        .message.bot {
            background: rgba(25,118,210,0.15);
            color: #F4F5F7;
            align-self: flex-start;
            border-bottom-left-radius: 4px;
            border: 1px solid rgba(25,118,210,0.3);
        }

        body.light-mode .message.bot {
            background: #e3f2fd;
            color: #1a2844;
        }

        .message.bot strong {
            color: #1976D2;
        }

        .message.bot ul {
            margin: 6px 0 0 20px;
        }

        .message.bot li {
            margin: 4px 0;
        }

        /* Categories Menu */
        .categories-menu {
            background: rgba(13, 31, 58, 0.9);
            border-top: 1px solid rgba(25,118,210,0.2);
            border-bottom: 1px solid rgba(25,118,210,0.2);
            padding: 8px 12px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            flex-shrink: 0;
        }

        .category-btn {
            background: transparent;
            border: 1px solid rgba(25,118,210,0.4);
            color: #61B3FA;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.7rem;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .category-btn:hover {
            background: rgba(25,118,210,0.2);
            border-color: #1976D2;
        }

        /* Questions Panel */
        .questions-panel {
            background: rgba(13, 31, 58, 0.95);
            border-top: 1px solid rgba(25,118,210,0.3);
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            flex-shrink: 0;
        }

        .questions-panel.open {
            max-height: 280px;
            overflow-y: auto;
        }

        .questions-list {
            padding: 8px 12px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .question-item {
            background: rgba(25,118,210,0.1);
            padding: 8px 12px;
            border-radius: 12px;
            font-size: 0.75rem;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #F4F5F7;
        }

        .question-item:hover {
            background: rgba(25,118,210,0.3);
            transform: translateX(5px);
        }

        .question-item i {
            color: #61B3FA;
            width: 20px;
            font-size: 0.7rem;
        }

        body.light-mode .question-item {
            color: #1a2844;
        }

        .typing-indicator {
            display: flex;
            gap: 4px;
            padding: 8px 12px;
            background: rgba(25,118,210,0.15);
            border-radius: 16px;
            align-self: flex-start;
            width: fit-content;
        }

        .typing-indicator span {
            width: 8px;
            height: 8px;
            background: #1976D2;
            border-radius: 50%;
            animation: typing 1.4s infinite;
        }

        .typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
        .typing-indicator span:nth-child(3) { animation-delay: 0.4s; }

        @keyframes typing {
            0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
            30% { transform: translateY(-8px); opacity: 1; }
        }

        .chatbot-input-area {
            padding: 1rem;
            border-top: 1px solid rgba(25,118,210,0.2);
            display: flex;
            gap: 8px;
            flex-shrink: 0;
        }

        .chatbot-input-area input {
            flex: 1;
            padding: 10px 14px;
            border-radius: 25px;
            border: 1px solid rgba(25,118,210,0.3);
            background: rgba(10,47,68,0.6);
            color: white;
            font-size: 0.85rem;
            outline: none;
        }

        body.light-mode .chatbot-input-area input {
            background: white;
            color: #1a2844;
        }

        .chatbot-input-area input::placeholder {
            color: #A7A9AC;
        }

        .chatbot-input-area button {
            background: #1976D2;
            border: none;
            color: white;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }

        .chatbot-input-area button:hover {
            background: #1565C0;
        }

        @media (max-width: 600px) {
            .chatbot-container {
                width: calc(100vw - 40px);
                right: 20px;
                bottom: 80px;
                height: 500px;
            }
        }

        /* Rest of existing styles */
        .charts-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-top: 1.5rem;
        }
        .chart-card {
            background: linear-gradient(135deg, #0D2350 0%, #0F3166 100%);
            border-radius: 16px;
            border: 1px solid rgba(97,179,250,0.18);
            padding: 1.5rem;
            box-shadow: 0 4px 24px rgba(0,0,0,0.25);
        }
        .chart-card-header {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1rem;
            font-weight: 600;
            color: #61B3FA;
            margin-bottom: 1.2rem;
        }
        .chart-wrapper {
            position: relative;
            width: 100%;
            max-width: 260px;
            margin: 0 auto;
        }
        .donut-center-label {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            pointer-events: none;
        }
        .donut-total {
            display: block;
            font-size: 2rem;
            font-weight: 700;
            color: #F4F5F7;
            line-height: 1;
        }
        .donut-sublabel {
            display: block;
            font-size: 0.75rem;
            color: #A7A9AC;
            margin-top: 2px;
        }
        .chart-legend {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin-top: 1.2rem;
            flex-wrap: wrap;
        }
        .legend-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.82rem;
            color: #F4F5F7;
        }
        .legend-dot {
            width: 11px;
            height: 11px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .legend-item strong {
            margin-left: 4px;
            color: #61B3FA;
        }
        .btn-pdf-export {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 0.45rem 1.1rem;
            background: transparent;
            color: #E74C3C;
            border: 1px solid rgba(231,76,60,0.45);
            border-radius: 25px;
            text-decoration: none;
            font-size: 0.88rem;
            font-weight: 700;
            white-space: nowrap;
            letter-spacing: 0.02em;
            transition: all 0.2s;
            cursor: pointer;
        }
        .btn-pdf-export:hover {
            background: rgba(231,76,60,0.12);
            border-color: #E74C3C;
            color: #FF6B6B;
        }
        @media (max-width: 900px) {
            .charts-row { grid-template-columns: 1fr; }
        }
        :root {
            --bleu-fonce:  #1976D2;
            --bleu-clair:  #61B3FA;
            --gris:        #A7A9AC;
            --dark-bg:     #0A1628;
            --text:        #F4F5F7;
            --border:      rgba(97,179,250,.25);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Poppins', 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #0A1628 0%, #0D1F3A 100%);
            min-height: 100vh;
            color: #F4F5F7;
        }

        .main-content {
            margin-left: 240px;
            width: calc(100% - 240px);
            padding: 0;
            min-height: 100vh;
        }

        .page-content {
            padding: 2rem 2.5rem;
            width: 100%;
            box-sizing: border-box;
        }

        body.light-mode { background: linear-gradient(135deg,#EDF2F7 0%,#DBEAFE 100%) !important; color:#1A2844 !important; }
        body.light-mode .stat-card, body.light-mode .table-container { background: rgba(255,255,255,.95) !important; }
        body.light-mode td { color: #1A2844 !important; }

        .stats-grid {
            display: flex;
            flex-wrap: nowrap;
            gap: 1.5rem;
            margin-bottom: 2rem;
            justify-content: stretch;
            width: 100%;
        }

        .stat-card {
            background: rgba(13, 31, 45, 0.9);
            border-radius: 20px;
            padding: 1.5rem;
            text-align: center;
            border: 1px solid rgba(25,118,210, 0.3);
            transition: all 0.3s;
            min-width: 180px;
            flex: 1;
        }

        .stat-card:hover {
            border-color: #1976D2;
            transform: translateY(-5px);
        }

        .stat-card i {
            font-size: 2rem;
            color: #1976D2;
            margin-bottom: 0.5rem;
        }

        .stat-card .number {
            font-size: 2rem;
            font-weight: bold;
            color: #1976D2;
        }

        .stat-card .label {
            color: #61B3FA;
            margin-top: 0.5rem;
            font-size: 0.85rem;
        }

        .actions-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
            width: 100%;
        }

        .search-box {
            position: relative;
            flex: 1;
            max-width: 520px;
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #A7A9AC;
        }

        .search-box input {
            width: 100%;
            padding: 0.8rem 1rem 0.8rem 2.8rem;
            border-radius: 30px;
            border: 1px solid rgba(25,118,210, 0.3);
            background: rgba(13, 31, 45, 0.8);
            color: white;
        }

        .filter-select {
            padding: 0.8rem 1rem;
            border-radius: 30px;
            border: 1px solid rgba(25,118,210, 0.3);
            background: rgba(13, 31, 45, 0.8);
            color: white;
            cursor: pointer;
        }

        .table-container {
            background: rgba(13, 31, 45, 0.8);
            border-radius: 20px;
            overflow-x: auto;
            border: 1px solid rgba(25,118,210, 0.3);
            width: 100%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
        }

        th, td {
            padding: 1rem;
            text-align: left;
        }

        th {
            background: rgba(25,118,210, 0.15);
            color: #1976D2;
            font-weight: 600;
        }

        tr {
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        tr:hover {
            background: rgba(25,118,210, 0.05);
        }

        .badge {
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: 0.7rem;
            display: inline-block;
        }

        .badge-active {
            background: rgba(0, 255, 136, 0.15);
            color: #00ff88;
        }

        .badge-inactive {
            background: rgba(255, 68, 68, 0.15);
            color: #ff6666;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .btn-icon {
            background: transparent;
            border: none;
            padding: 0.35rem 0.45rem;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.25s;
            font-size: 1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            width: 32px; height: 32px;
            flex-shrink: 0;
        }
        .btn-icon span.btn-label { display: none; }

        .btn-icon.edit {
            color: #1976D2;
            border: 1px solid rgba(25,118,210,0.35);
        }
        .btn-icon.edit:hover {
            background: rgba(25,118,210,0.18);
            border-color: #1976D2;
        }

        .btn-icon.details {
            color: #1976D2;
            border: 1px solid rgba(25,118,210,0.35);
        }
        .btn-icon.details:hover {
            background: rgba(25,118,210,0.18);
            border-color: #1976D2;
        }

        .btn-icon.ban {
            color: #ff4444;
            border: none;
            font-size: 1.05rem;
        }
        .btn-icon.ban:hover {
            background: rgba(255,68,68,0.18);
            border-radius: 6px;
        }

        .btn-icon.unban {
            color: #00cc6a;
            border: none;
            font-size: 1.05rem;
        }
        .btn-icon.unban:hover {
            background: rgba(0,255,136,0.15);
            border-radius: 6px;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.85);
            backdrop-filter: blur(6px);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background: #0A1628;
            padding: 2rem;
            border-radius: 24px;
            width: 90%; max-width: 700px;
            border: 1px solid #1976D2;
            max-height: 85vh;
            overflow-y: auto;
        }
        .modal-content h2 {
            color: #1976D2;
            margin-bottom: 1.5rem;
            display: flex; align-items: center; gap: 10px;
        }

        .drawer-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 240px;
            width: calc(100% - 240px);
            height: 100%;
            background: transparent;
            z-index: 498;
            pointer-events: none;
            align-items: flex-start;
            justify-content: center;
            padding-top: 6rem;
            padding-bottom: 2rem;
            box-sizing: border-box;
        }
        .drawer-overlay.open {
            display: flex;
            pointer-events: all;
        }

        .details-drawer {
            position: relative;
            width: 90%;
            max-width: 680px;
            max-height: calc(100vh - 9rem);
            background: linear-gradient(160deg, #0D1F3A 0%, #091525 100%);
            border: 1px solid rgba(25,118,210,0.5);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.7);
            z-index: 499;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            opacity: 0;
            transform: scale(0.94) translateY(16px);
            transition: opacity 0.3s ease, transform 0.3s ease;
        }
        .drawer-overlay.open .details-drawer {
            opacity: 1;
            transform: scale(1) translateY(0);
        }

        .drawer-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.4rem;
            border-bottom: 1px solid rgba(25,118,210,0.3);
            background: rgba(25,118,210,0.12);
            flex-shrink: 0;
            border-radius: 20px 20px 0 0;
        }
        .drawer-header h2 {
            color: #61B3FA;
            font-size: 1rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0;
        }
        .drawer-close {
            width: 30px; height: 30px;
            border-radius: 50%;
            background: rgba(255,68,68,0.15);
            border: 1px solid rgba(255,68,68,0.45);
            color: #ff6b6b;
            font-size: 0.9rem;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            transition: background 0.2s;
            flex-shrink: 0;
        }
        .drawer-close:hover { background: rgba(255,68,68,0.35); }

        .drawer-body {
            flex: 1;
            overflow-y: auto;
            padding: 1.4rem;
        }
        .drawer-body::-webkit-scrollbar { width: 4px; }
        .drawer-body::-webkit-scrollbar-track { background: transparent; }
        .drawer-body::-webkit-scrollbar-thumb { background: rgba(25,118,210,0.5); border-radius: 4px; }

        .modal-section {
            margin-bottom: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 1rem;
        }

        .modal-section h3 {
            color: #1976D2;
            margin-bottom: 0.8rem;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .modal-section .empty {
            color: #A7A9AC;
            font-style: italic;
            padding: 0.5rem;
        }

        .detail-item {
            background: rgba(10, 47, 68, 0.5);
            padding: 0.5rem;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            font-size: 0.85rem;
        }

        .detail-item strong {
            color: #1976D2;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #61B3FA;
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 0.8rem;
            border-radius: 12px;
            border: 1px solid rgba(25,118,210, 0.3);
            background: rgba(10, 47, 68, 0.8);
            color: white;
        }

        .modal-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .btn-save {
            background: linear-gradient(135deg, #1976D2, #1976D2);
            color: white;
            padding: 0.7rem 1.5rem;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            flex: 1;
        }

        .btn-cancel, .close-modal {
            background: rgba(255, 68, 68, 0.2);
            border: 1px solid #ff4444;
            color: #ff4444;
            padding: 0.7rem 1.5rem;
            border-radius: 25px;
            cursor: pointer;
            flex: 1;
        }

        .detail-badge {
            display: inline-block;
            padding: 0.2rem 0.5rem;
            border-radius: 12px;
            font-size: 0.7rem;
            margin-left: 0.5rem;
        }

        .badge-vehicle { background: rgba(25,118,210,0.2); color: #1976D2; }
        .badge-trip { background: rgba(0,255,136,0.2); color: #00ff88; }
        .badge-reclamation { background: rgba(255,165,0,0.2); color: #ffa500; }
        .badge-event { background: rgba(255,68,68,0.2); color: #ff6666; }
        .badge-lost { background: rgba(255,68,68,0.2); color: #ff6666; }

        .toast {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: linear-gradient(135deg, #1976D2, #1976D2);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            z-index: 1100;
            animation: slideIn 0.3s, fadeOut 0.3s 2.7s;
        }

        .toast.error {
            background: linear-gradient(135deg, #ff4444, #cc0000);
        }

        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @keyframes fadeOut {
            to { opacity: 0; visibility: hidden; }
        }

        footer {
            text-align: center;
            padding: 2rem;
            border-top: 1px solid rgba(25,118,210, 0.2);
            color: #A7A9AC;
            margin-top: 2rem;
        }

        .pagination-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1.5rem;
            padding: 1rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .pagination-info {
            color: #A7A9AC;
            font-size: 0.85rem;
        }

        .pagination-info span {
            color: #1976D2;
            font-weight: 600;
        }

        .pagination-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .page-btn {
            background: rgba(13, 31, 58, 0.9);
            border: 1px solid rgba(25, 118, 210, 0.3);
            color: #A7A9AC;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.85rem;
            transition: all 0.2s;
            min-width: 40px;
        }

        .page-btn:hover:not(:disabled) {
            background: rgba(25, 118, 210, 0.2);
            color: #1976D2;
            border-color: #1976D2;
        }

        .page-btn.active {
            background: rgba(25, 118, 210, 0.3);
            color: #1976D2;
            border-color: #1976D2;
            font-weight: 600;
        }

        .page-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        .page-btn.nav-btn {
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                width: 100%;
            }
            .stats-grid {
                flex-wrap: wrap;
            }
            .stat-card {
                min-width: auto;
            }
            .actions-bar {
                flex-direction: column;
                align-items: stretch;
            }
            .search-box {
                max-width: 100%;
            }
            .pagination-wrapper {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
<?php render_nav_css(); ?>
</head>
<body>
    <div style="display: flex; width:100%; overflow-x:hidden;">
        <?php require_once __DIR__ . '/partials/partials.php'; sidebar_spa('dashboard'); ?>

        <div class="main-content">
            <div class="page-content">
                <?php navbar_dashboard(); ?>

                <div id="dashboardPage">
                    <div class="stats-grid">
                        <div class="stat-card"><i class="fas fa-users"></i><div class="number"><?= $stats['total_passagers'] ?? 0 ?></div><div class="label">Passagers total</div></div>
                        <div class="stat-card"><i class="fas fa-user-check"></i><div class="number"><?= $stats['active_passagers'] ?? 0 ?></div><div class="label">Passagers actifs</div></div>
                        <div class="stat-card"><i class="fas fa-user-slash"></i><div class="number"><?= $stats['inactive_passagers'] ?? 0 ?></div><div class="label">Passagers inactifs</div></div>
                        <div class="stat-card"><i class="fas fa-user-shield"></i><div class="number"><?= $stats['total_admins'] ?? 0 ?></div><div class="label">Administrateurs</div></div>
                    </div>

                    <div class="charts-row">
                        <div class="chart-card">
                            <div class="chart-card-header"><i class="fas fa-chart-pie"></i><span>Répartition des Passagers</span></div>
                            <div class="chart-wrapper"><canvas id="passagersDonutChart"></canvas><div class="donut-center-label"><span class="donut-total"><?= $stats['total_passagers'] ?? 0 ?></span><span class="donut-sublabel">Total</span></div></div>
                            <div class="chart-legend"><div class="legend-item"><span class="legend-dot" style="background:#22c55e;"></span><span>Actifs</span><strong><?= $stats['active_passagers'] ?? 0 ?></strong></div><div class="legend-item"><span class="legend-dot" style="background:#ef4444;"></span><span>Bannis</span><strong><?= $stats['inactive_passagers'] ?? 0 ?></strong></div><div class="legend-item"><span class="legend-dot" style="background:#3b82f6;"></span><span>Admins</span><strong><?= $stats['total_admins'] ?? 0 ?></strong></div></div>
                        </div>
                        <div class="chart-card"><div class="chart-card-header"><i class="fas fa-chart-bar"></i><span>Statistiques Générales</span></div><div class="chart-wrapper"><canvas id="statsBarChart"></canvas></div></div>
                    </div>
                    <script>var dashboardStats = { active: <?= (int)($stats['active_passagers'] ?? 0) ?>, inactive: <?= (int)($stats['inactive_passagers'] ?? 0) ?>, admins: <?= (int)($stats['total_admins'] ?? 0) ?> };</script>
                </div>

                <div id="passagersPage" style="display: none;">
                    <div class="actions-bar">
                        <div class="search-box"><i class="fas fa-search"></i><input type="text" id="searchInput" placeholder="Rechercher un passager..."></div>
                        <div class="filter-group"><select id="statusFilter" class="filter-select"><option value="all">Tous les statuts</option><option value="actif">Actifs</option><option value="inactif">Inactifs</option></select></div>
                        <a href="<?= BASE_URL ?>controllers/AdminController.php?action=exportPassagersPDF" class="btn-pdf-export" target="_blank"><i class="fas fa-file-pdf"></i> Exporter PDF</a>
                    </div>
                    <div class="table-container"><table id="passagersTable"><thead><tr><th>ID</th><th>Nom complet</th><th>Email</th><th>Téléphone</th><th>Statut</th><th>Date d'inscription</th><th>Actions</th></tr></thead><tbody id="passagersTableBody"><?php foreach ($passagers as $passager): ?><tr data-status="<?= $passager['statut'] ?>" data-name="<?= strtolower(htmlspecialchars($passager['prenom'] . ' ' . $passager['nom'])) ?>" data-email="<?= strtolower(htmlspecialchars($passager['email'])) ?>"><td><?= $passager['id'] ?></td><td><strong><?= htmlspecialchars($passager['prenom'] . ' ' . $passager['nom']) ?></strong></td><td><?= htmlspecialchars($passager['email']) ?></td><td><?= htmlspecialchars($passager['telephone'] ?? '-') ?></td><td><span class="badge <?= $passager['statut'] === 'actif' ? 'badge-active' : 'badge-inactive' ?>"><?= $passager['statut'] === 'actif' ? 'Actif' : 'Banni' ?></span></td><td><?= date('d/m/Y', strtotime($passager['created_at'])) ?></td><td class="action-buttons"><a href="<?= BASE_URL ?>controllers/AdminController.php?action=showPassagerDetailsPage&id=<?= $passager['id'] ?>" class="btn-icon details" title="Voir les détails"><i class="fas fa-eye"></i></a><a href="<?= BASE_URL ?>controllers/AdminController.php?action=showEditPassager&id=<?= $passager['id'] ?>" class="btn-icon edit" title="Modifier"><i class="fas fa-pen"></i></a><?php if ($passager['statut'] === 'actif'): ?><button class="btn-icon ban" onclick="banPassager(<?= $passager['id'] ?>)" title="Bannir"><i class="fas fa-ban"></i></button><?php else: ?><button class="btn-icon unban" onclick="unbanPassager(<?= $passager['id'] ?>)" title="Réactiver"><i class="fas fa-check-circle"></i></button><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div>
                    <div class="pagination-wrapper"><div class="pagination-info">Affichage <span id="pageStart">1</span> – <span id="pageEnd">0</span> sur <span id="totalPassagers"><?= count($passagers) ?></span> passager(s)</div><div class="pagination-buttons" id="paginationButtons"></div></div>
                </div>
            </div>
        </div>
    </div>

    <footer><p><svg width="16" height="16" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align:middle"><path d="M22 4C22 4 8 10 8 24C8 31.732 14.268 38 22 38C29.732 38 36 31.732 36 24C36 14 28 8 22 4Z" fill="#61B3FA" opacity="0.9"/></svg> Eco Ride by Echo Group © 2025 - Panel Administrateur</p></footer>

    <div class="drawer-overlay" id="drawerOverlay" onclick="closeDetailsModal()"><div class="details-drawer" id="detailsDrawer" onclick="event.stopPropagation()"><div class="drawer-header"><h2><i class="fas fa-user-circle"></i> Détails du passager</h2><button class="drawer-close" onclick="closeDetailsModal()"><i class="fas fa-times"></i></button></div><div class="drawer-body" id="detailsContent"></div></div></div>

    <div id="passagerModal" class="modal"><div class="modal-content"><h2><i class="fas fa-user-edit"></i> Modifier le passager</h2><form id="passagerForm" method="POST" action="<?= BASE_URL ?>controllers/AdminController.php?action=editPassager"><input type="hidden" id="passagerId" name="id"><div class="form-group"><label>Prénom *</label><input type="text" id="prenom" name="prenom" required></div><div class="form-group"><label>Nom *</label><input type="text" id="nom" name="nom" required></div><div class="form-group"><label>Email *</label><input type="email" id="email" name="email" required></div><div class="form-group"><label>Téléphone</label><input type="text" id="telephone" name="telephone"></div><div class="form-group"><label>Statut</label><select id="statut" name="statut"><option value="actif">Actif</option><option value="inactif">Banni</option></select></div><div class="modal-buttons"><button type="submit" class="btn-save">Enregistrer</button><button type="button" class="btn-cancel" onclick="closePassagerModal()">Annuler</button></div></form></div></div>

    <!-- Chatbot Button -->
    <button class="chatbot-toggle" id="chatbotToggle" title="Assistant Admin"><i class="fas fa-robot"></i></button>

    <!-- Chatbot Container -->
    <div class="chatbot-container" id="chatbotContainer">
        <div class="chatbot-header"><h3><i class="fas fa-brain"></i> Assistant Admin EcoRide</h3><button class="chatbot-close" id="chatbotClose"><i class="fas fa-times"></i></button></div>
        
        <!-- Categories Menu -->
        <div class="categories-menu">
            <button class="category-btn" data-category="passagers"><i class="fas fa-users"></i> Passagers</button>
            <button class="category-btn" data-category="recherche"><i class="fas fa-search"></i> Recherche</button>
            <button class="category-btn" data-category="analyse"><i class="fas fa-chart-line"></i> Analyse</button>
            <button class="category-btn" data-category="aide"><i class="fas fa-question-circle"></i> Aide</button>
        </div>
        
        <!-- Questions Panel -->
        <div class="questions-panel" id="questionsPanel">
            <div class="questions-list" id="questionsList"></div>
        </div>
        
        <div class="chatbot-messages" id="chatbotMessages">
            <div class="message bot">👋 Bonjour <strong>Admin <?= htmlspecialchars($_SESSION['admin_nom'] ?? '') ?></strong> !<br>Je suis votre assistant. Cliquez sur une catégorie ci-dessus pour voir les questions prédéfinies.</div>
        </div>
        <div class="chatbot-input-area"><input type="text" id="chatbotInput" placeholder="Ou écrivez votre question..." autocomplete="off"><button id="chatbotSend"><i class="fas fa-paper-plane"></i></button></div>
    </div>

    <script>
        // Dark / Light mode
        function toggleTheme() {
            document.body.classList.toggle('light-mode');
            const isLight = document.body.classList.contains('light-mode');
            document.querySelectorAll('.themeIcon').forEach(i => { i.className = isLight ? 'fas fa-sun themeIcon' : 'fas fa-moon themeIcon'; });
            localStorage.setItem('ecoride_theme', isLight ? 'light' : 'dark');
        }
        (function() { if (localStorage.getItem('ecoride_theme') === 'light') { document.body.classList.add('light-mode'); document.querySelectorAll('.themeIcon').forEach(i => { i.className = 'fas fa-sun themeIcon'; }); } })();

        // Pagination (existing code)
        const ROWS_PER_PAGE = 5;
        let currentPage = 1;
        let allPassagersRows = [];
        function getAllRows() { return Array.from(document.querySelectorAll('#passagersTableBody tr')); }
        function getFilteredRows() { const searchTerm = document.getElementById('searchInput')?.value.toLowerCase() || ''; const statusFilter = document.getElementById('statusFilter')?.value || 'all'; return allPassagersRows.filter(row => { const name = row.getAttribute('data-name') || ''; const email = row.getAttribute('data-email') || ''; const status = row.getAttribute('data-status') || ''; const matchesSearch = name.includes(searchTerm) || email.includes(searchTerm); const matchesStatus = statusFilter === 'all' || status === statusFilter; return matchesSearch && matchesStatus; }); }
        function displayPage() { const filteredRows = getFilteredRows(); const totalPages = Math.ceil(filteredRows.length / ROWS_PER_PAGE); if (currentPage > totalPages) currentPage = totalPages; if (currentPage < 1) currentPage = 1; allPassagersRows.forEach(row => row.style.display = 'none'); const start = (currentPage - 1) * ROWS_PER_PAGE; const end = start + ROWS_PER_PAGE; const rowsToShow = filteredRows.slice(start, end); rowsToShow.forEach(row => row.style.display = ''); updatePaginationInfo(filteredRows.length, start, end); renderPaginationButtons(totalPages); }
        function updatePaginationInfo(totalFiltered, start, end) { const pageStartSpan = document.getElementById('pageStart'); const pageEndSpan = document.getElementById('pageEnd'); const totalSpan = document.getElementById('totalPassagers'); if (pageStartSpan) pageStartSpan.textContent = totalFiltered > 0 ? start + 1 : 0; if (pageEndSpan) pageEndSpan.textContent = Math.min(end, totalFiltered); if (totalSpan) totalSpan.textContent = totalFiltered; }
        function renderPaginationButtons(totalPages) { const container = document.getElementById('paginationButtons'); if (!container) return; container.innerHTML = ''; if (totalPages <= 1) { const singleBtn = document.createElement('button'); singleBtn.className = 'page-btn'; singleBtn.textContent = '1'; singleBtn.disabled = true; container.appendChild(singleBtn); return; } const prevBtn = document.createElement('button'); prevBtn.className = 'page-btn nav-btn'; prevBtn.innerHTML = '<i class="fas fa-chevron-left"></i> Précédent'; prevBtn.disabled = currentPage === 1; prevBtn.onclick = () => { if (currentPage > 1) { currentPage--; displayPage(); } }; container.appendChild(prevBtn); let startPage = Math.max(1, currentPage - 2); let endPage = Math.min(totalPages, startPage + 4); if (endPage - startPage < 4) startPage = Math.max(1, endPage - 4); if (startPage > 1) { const firstBtn = document.createElement('button'); firstBtn.className = 'page-btn'; firstBtn.textContent = '1'; firstBtn.onclick = () => { currentPage = 1; displayPage(); }; container.appendChild(firstBtn); if (startPage > 2) { const dots = document.createElement('span'); dots.className = 'page-btn'; dots.textContent = '...'; dots.disabled = true; dots.style.cursor = 'default'; container.appendChild(dots); } } for (let i = startPage; i <= endPage; i++) { const pageBtn = document.createElement('button'); pageBtn.className = 'page-btn' + (i === currentPage ? ' active' : ''); pageBtn.textContent = i; pageBtn.onclick = (function(page) { return function() { currentPage = page; displayPage(); }; })(i); container.appendChild(pageBtn); } if (endPage < totalPages) { if (endPage < totalPages - 1) { const dots = document.createElement('span'); dots.className = 'page-btn'; dots.textContent = '...'; dots.disabled = true; dots.style.cursor = 'default'; container.appendChild(dots); } const lastBtn = document.createElement('button'); lastBtn.className = 'page-btn'; lastBtn.textContent = totalPages; lastBtn.onclick = () => { currentPage = totalPages; displayPage(); }; container.appendChild(lastBtn); } const nextBtn = document.createElement('button'); nextBtn.className = 'page-btn nav-btn'; nextBtn.innerHTML = 'Suivant <i class="fas fa-chevron-right"></i>'; nextBtn.disabled = currentPage === totalPages; nextBtn.onclick = () => { if (currentPage < totalPages) { currentPage++; displayPage(); } }; container.appendChild(nextBtn); }
        function resetAndDisplay() { currentPage = 1; displayPage(); }
        const allPages = ['dashboard', 'passagers', 'trajets', 'destinations', 'evenements', 'reclamations', 'vehicules', 'lost_found'];
        function showPage(page) { allPages.forEach(p => { const el = document.getElementById(p + 'Page'); if (el) el.style.display = (p === page) ? 'block' : 'none'; }); document.querySelectorAll('.sidebar nav ul li a').forEach(a => { a.classList.toggle('active', a.dataset.page === page); }); if (page === 'passagers') setTimeout(() => resetAndDisplay(), 50); }
document.querySelectorAll('.sidebar nav ul li a[data-page]').forEach(item => {
    item.addEventListener('click', function(e) {
        e.preventDefault();
        showPage(this.dataset.page);
    });
});
        const searchInput = document.getElementById('searchInput'); const statusFilter = document.getElementById('statusFilter');
        function filterAndPaginate() { currentPage = 1; displayPage(); }
        if (searchInput) searchInput.addEventListener('input', filterAndPaginate);
        if (statusFilter) statusFilter.addEventListener('change', filterAndPaginate);
        document.addEventListener('DOMContentLoaded', function() { allPassagersRows = getAllRows(); displayPage(); const urlParams = new URLSearchParams(window.location.search); const tab = urlParams.get('tab'); if (tab) showPage(tab); });

        // Passager functions
        function showPassagerDetails(userId) { fetch('<?= BASE_URL ?>controllers/AdminController.php?action=getPassagerDetails&id=' + userId).then(response => response.json()).then(data => { const user = data.user; let html = `<div class="modal-section"><h3><i class="fas fa-user"></i> Informations personnelles</h3><div class="detail-item"><strong>ID:</strong> ${user.id}</div><div class="detail-item"><strong>Nom:</strong> ${user.prenom} ${user.nom}</div><div class="detail-item"><strong>Email:</strong> ${user.email}</div><div class="detail-item"><strong>Téléphone:</strong> ${user.telephone || 'Non renseigné'}</div><div class="detail-item"><strong>Statut:</strong> ${user.statut === 'actif' ? 'Actif' : 'Banni'}</div><div class="detail-item"><strong>Date d'inscription:</strong> ${new Date(user.created_at).toLocaleDateString('fr-FR')}</div></div><div class="modal-section"><h3><i class="fas fa-car"></i> Véhicules <span class="detail-badge badge-vehicle">${data.vehicles.length}</span></h3>`; if (data.vehicles.length > 0) { data.vehicles.forEach(v => { html += `<div class="detail-item"><strong>${v.brand} ${v.model}</strong> - ${v.plate} - ${v.color} - ${v.seats} places</div>`; }); } else { html += `<div class="empty">Aucun véhicule enregistré</div>`; } html += `</div><div class="modal-section"><h3><i class="fas fa-route"></i> Trajets <span class="detail-badge badge-trip">${data.trips.length}</span></h3>`; if (data.trips.length > 0) { data.trips.forEach(t => { html += `<div class="detail-item"><strong>${t.departure} → ${t.arrival}</strong> - ${t.date} ${t.time} - ${t.price} DT - ${t.available}/${t.seats} places</div>`; }); } else { html += `<div class="empty">Aucun trajet proposé</div>`; } html += `</div><div class="modal-section"><h3><i class="fas fa-exclamation-triangle"></i> Réclamations <span class="detail-badge badge-reclamation">${data.reclamations.length}</span></h3>`; if (data.reclamations.length > 0) { data.reclamations.forEach(r => { html += `<div class="detail-item"><strong>${r.title || 'Sans titre'}</strong> - ${r.status || 'En attente'} - ${new Date(r.created_at).toLocaleDateString('fr-FR')}</div>`; }); } else { html += `<div class="empty">Aucune réclamation</div>`; } html += `</div><div class="modal-section"><h3><i class="fas fa-calendar-alt"></i> Événements <span class="detail-badge badge-event">${data.events.length}</span></h3>`; if (data.events.length > 0) { data.events.forEach(e => { html += `<div class="detail-item"><strong>${e.title || 'Sans titre'}</strong> - ${e.date || 'Date non définie'}</div>`; }); } else { html += `<div class="empty">Aucun événement</div>`; } html += `</div><div class="modal-section"><h3><i class="fas fa-search"></i> Objets perdus/trouvés <span class="detail-badge badge-lost">${data.lost_found.length}</span></h3>`; if (data.lost_found.length > 0) { data.lost_found.forEach(l => { html += `<div class="detail-item"><strong>${l.item_name || 'Objet'}</strong> - ${l.status || 'En cours'} - ${new Date(l.created_at).toLocaleDateString('fr-FR')}</div>`; }); } else { html += `<div class="empty">Aucun objet signalé</div>`; } html += `</div>`; document.getElementById('detailsContent').innerHTML = html; document.getElementById('drawerOverlay').classList.add('open'); }).catch(error => { console.error('Erreur:', error); alert('Erreur lors du chargement des détails'); }); }
        function closeDetailsModal() { document.getElementById('drawerOverlay').classList.remove('open'); }

        function banPassager(id) { if (confirm('Êtes-vous sûr de vouloir BANNIR ce passager ?')) { window.location.href = '<?= BASE_URL ?>controllers/AdminController.php?action=banPassager&id=' + id; } }
        function unbanPassager(id) { if (confirm('Êtes-vous sûr de vouloir RÉACTIVER ce passager ?')) { window.location.href = '<?= BASE_URL ?>controllers/AdminController.php?action=unbanPassager&id=' + id; } }

        function showToast(message, type = 'success') { const toast = document.createElement('div'); toast.className = 'toast'; if (type === 'error') toast.classList.add('error'); toast.innerHTML = `<i class="fas ${type === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle'}"></i> ${message}`; document.body.appendChild(toast); setTimeout(() => toast.remove(), 3000); }
        <?php if (isset($_SESSION['admin_success'])): ?> showToast('<?= $_SESSION['admin_success'] ?>'); <?php unset($_SESSION['admin_success']); endif; ?>
        <?php if (isset($_SESSION['admin_error'])): ?> showToast('<?= $_SESSION['admin_error'] ?>', 'error'); <?php unset($_SESSION['admin_error']); endif; ?>
    </script>

    <script>
        // Graphiques
        (function() { var ctx = document.getElementById('passagersDonutChart'); if (ctx && typeof Chart !== 'undefined') { new Chart(ctx, { type: 'doughnut', data: { labels: ['Actifs', 'Bannis', 'Admins'], datasets: [{ data: [dashboardStats.active, dashboardStats.inactive, dashboardStats.admins], backgroundColor: ['#22c55e', '#ef4444', '#3b82f6'], borderColor: ['#16a34a', '#dc2626', '#2563eb'], borderWidth: 2, hoverOffset: 8 }] }, options: { responsive: true, cutout: '70%', plugins: { legend: { display: false }, tooltip: { callbacks: { label: function(ctx) { var total = ctx.dataset.data.reduce(function(a,b){ return a+b; }, 0); var pct = total > 0 ? Math.round(ctx.parsed / total * 100) : 0; return ' ' + ctx.label + ': ' + ctx.parsed + ' (' + pct + '%)'; } } } } } }); } var ctxBar = document.getElementById('statsBarChart'); if (ctxBar && typeof Chart !== 'undefined') { new Chart(ctxBar, { type: 'bar', data: { labels: ['Actifs', 'Bannis', 'Admins'], datasets: [{ label: 'Utilisateurs', data: [dashboardStats.active, dashboardStats.inactive, dashboardStats.admins], backgroundColor: ['rgba(34,197,94,0.75)', 'rgba(239,68,68,0.75)', 'rgba(59,130,246,0.75)'], borderColor: ['#22c55e', '#ef4444', '#3b82f6'], borderWidth: 2, borderRadius: 8 }] }, options: { responsive: true, plugins: { legend: { display: false } }, scales: { x: { grid: { color: 'rgba(255,255,255,0.06)' }, ticks: { color: '#A7A9AC' } }, y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.06)' }, ticks: { color: '#A7A9AC', precision: 0 } } } } }); } })();
    </script>

    <script>
        // ========== CHATBOT AVEC CATÉGORIES DE QUESTIONS ==========
        (function() {
            const chatbotToggle = document.getElementById('chatbotToggle');
            const chatbotContainer = document.getElementById('chatbotContainer');
            const chatbotClose = document.getElementById('chatbotClose');
            const chatbotMessages = document.getElementById('chatbotMessages');
            const chatbotInput = document.getElementById('chatbotInput');
            const chatbotSend = document.getElementById('chatbotSend');
            const questionsPanel = document.getElementById('questionsPanel');
            const questionsList = document.getElementById('questionsList');

            if (!chatbotToggle || !chatbotContainer) return;

            // Données stats
            const botStats = {
                total: <?= (int)($stats['total_passagers'] ?? 0) ?>,
                actifs: <?= (int)($stats['active_passagers'] ?? 0) ?>,
                bannis: <?= (int)($stats['inactive_passagers'] ?? 0) ?>,
                admins: <?= (int)($stats['total_admins'] ?? 0) ?>
            };

            // Liste des passagers
            const passagersList = <?= json_encode(array_map(function($p) {
                return [
                    'id' => $p['id'],
                    'nom' => $p['prenom'] . ' ' . $p['nom'],
                    'prenom' => $p['prenom'],
                    'nom_family' => $p['nom'],
                    'email' => $p['email'],
                    'statut' => $p['statut'],
                    'created_at' => $p['created_at']
                ];
            }, $passagers ?? [])) ?>;

            // Trier par date récente
            const recentUsers = [...passagersList].sort((a,b) => new Date(b.created_at) - new Date(a.created_at)).slice(0, 5);

            // Définition des questions par catégorie
// Définition des questions par catégorie (version mise à jour)
const categoriesQuestions = {
    passagers: {
        icon: "👥",
        questions: [
            "Liste des passagers actifs",
            "Liste des passagers bannis",
            "Liste de tous les passagers",
            "Qui s'est inscrit récemment ?",
            "Quels sont les passagers inactifs ?",
            "Affiche les passagers avec email gmail"
        ]
    },
    recherche: {
        icon: "🔍",
        questions: [
            "Filtrer les passagers actifs",
            "Filtrer les passagers bannis",
            "Trier par date d'inscription"
        ]
    },
    analyse: {
        icon: "📊",
        questions: [
            "Qui sont les utilisateurs les plus récents ?",
            "Quel est le nombre de bannis aujourd'hui ?",
            "Y a-t-il des anomalies ?",
            "Donne-moi un résumé du système",
            "Quel est le taux d'activité ?"
        ]
    },
    aide: {
        icon: "❓",
        questions: [
            "Comment bannir un utilisateur ?",
            "Comment activer un passager ?",
            "Comment utiliser le dashboard ?",
            "Que signifie 'passager inactif' ?",
            "Comment rechercher un utilisateur ?"
        ]
    }
};

            // Réponses prédéfinies pour chaque question
            function getAnswerForQuestion(question) {
                const q = question.toLowerCase();
                
                // Passagers
                if (q.includes("liste des passagers actifs")) return getActivePassagersList();
                if (q.includes("liste des passagers bannis")) return getBannedPassagersList();
                if (q.includes("liste de tous les passagers")) return getAllPassagersList();
                if (q.includes("qui s'est inscrit récemment")) return getRecentUsers();
                if (q.includes("passagers inactifs")) return getInactivePassagersList();
                if (q.includes("email gmail")) return getGmailUsers();
                
                // Recherche
                if (q.includes('cherche "maram"')) return searchUser("maram");
                if (q.includes("trouve les utilisateurs avec numéro 22")) return searchUserById(22);
                if (q.includes("filtrer les passagers actifs")) return getActivePassagersList();
                if (q.includes("filtrer les passagers bannis")) return getBannedPassagersList();
                if (q.includes("trier par date d'inscription")) return getUsersByDate();
                
                // Analyse
                if (q.includes("utilisateurs les plus récents")) return getRecentUsers();
                if (q.includes("nombre de bannis aujourd'hui")) return getBannedToday();
                if (q.includes("anomalies")) return checkAnomalies();
                if (q.includes("groupe est le plus important")) return getMostImportantGroup();
                if (q.includes("résumé du système")) return getSystemSummary();
                if (q.includes("taux d'activité")) return getActivityRate();
                
                // Aide
                if (q.includes("comment bannir")) return getHelpBan();
                if (q.includes("comment activer")) return getHelpActivate();
                if (q.includes("comment utiliser le dashboard")) return getHelpDashboard();
                if (q.includes("que signifie 'passager inactif'")) return getHelpInactive();
                if (q.includes("comment rechercher")) return getHelpSearch();
                
                return traitementIntelligent(q);
            }

            function getActivePassagersList() {
                const actifs = passagersList.filter(p => p.statut === 'actif');
                if (actifs.length === 0) return "⚠️ Aucun passager actif trouvé.";
                let html = `🟢 <strong>Passagers actifs (${actifs.length}) :</strong><ul>`;
                actifs.slice(0, 20).forEach(a => { html += `<li>#${a.id} - ${a.nom} (${a.email})</li>`; });
                if (actifs.length > 20) html += `<li>... et ${actifs.length - 20} autre(s)</li>`;
                html += `</ul>`;
                return html;
            }

            function getBannedPassagersList() {
                const bannis = passagersList.filter(p => p.statut === 'inactif');
                if (bannis.length === 0) return "✅ Aucun passager banni.";
                let html = `🔴 <strong>Passagers bannis (${bannis.length}) :</strong><ul>`;
                bannis.forEach(b => { html += `<li>#${b.id} - ${b.nom} (${b.email})</li>`; });
                html += `</ul>`;
                return html;
            }

            function getAllPassagersList() {
                let html = `👥 <strong>Tous les passagers (${passagersList.length}) :</strong><ul>`;
                passagersList.slice(0, 20).forEach(p => {
                    const statusIcon = p.statut === 'actif' ? '🟢' : '🔴';
                    html += `<li>${statusIcon} #${p.id} - ${p.nom} (${p.email})</li>`;
                });
                if (passagersList.length > 20) html += `<li>... et ${passagersList.length - 20} autre(s)</li>`;
                html += `</ul>`;
                return html;
            }

            function getRecentUsers() {
                const sorted = [...passagersList].sort((a,b) => new Date(b.created_at) - new Date(a.created_at)).slice(0, 5);
                if (sorted.length === 0) return "Aucun utilisateur trouvé.";
                let html = `📅 <strong>5 derniers inscrits :</strong><ul>`;
                sorted.forEach(u => {
                    const date = new Date(u.created_at).toLocaleDateString('fr-FR');
                    html += `<li>#${u.id} - ${u.nom} (inscrit le ${date})</li>`;
                });
                html += `</ul>`;
                return html;
            }

            function getInactivePassagersList() {
                const inactifs = passagersList.filter(p => p.statut === 'inactif');
                if (inactifs.length === 0) return "✅ Aucun passager inactif.";
                let html = `⚠️ <strong>Passagers inactifs/bannis (${inactifs.length}) :</strong><ul>`;
                inactifs.forEach(i => { html += `<li>#${i.id} - ${i.nom} (${i.email})</li>`; });
                html += `</ul>`;
                return html;
            }

            function getGmailUsers() {
                const gmailUsers = passagersList.filter(p => p.email.toLowerCase().includes('@gmail.com'));
                if (gmailUsers.length === 0) return "Aucun passager avec email Gmail trouvé.";
                let html = `📧 <strong>Passagers avec email Gmail (${gmailUsers.length}) :</strong><ul>`;
                gmailUsers.forEach(u => { html += `<li>#${u.id} - ${u.nom} (${u.email})</li>`; });
                html += `</ul>`;
                return html;
            }

            function searchUser(name) {
                const found = passagersList.filter(p => p.nom.toLowerCase().includes(name.toLowerCase()) || p.prenom.toLowerCase().includes(name.toLowerCase()));
                if (found.length === 0) return `❌ Aucun passager trouvé pour "${name}".`;
                if (found.length === 1) {
                    const f = found[0];
                    const statusIcon = f.statut === 'actif' ? '🟢 Actif' : '🔴 Banni';
                    return `🔍 <strong>Passager trouvé :</strong><br>#${f.id} - ${f.nom}<br>📧 ${f.email}<br>${statusIcon}`;
                }
                let html = `🔍 <strong>${found.length} passagers pour "${name}" :</strong><ul>`;
                found.forEach(f => { html += `<li>#${f.id} - ${f.nom} (${f.email})</li>`; });
                html += `</ul>`;
                return html;
            }

            function searchUserById(id) {
                const user = passagersList.find(p => p.id === id);
                if (!user) return `❌ Aucun passager avec l'ID ${id}.`;
                const statusIcon = user.statut === 'actif' ? '🟢 Actif' : '🔴 Banni';
                return `🔍 <strong>Passager ID ${id} :</strong><br>👤 ${user.nom}<br>📧 ${user.email}<br>${statusIcon}`;
            }

            function getUsersByDate() {
                const sorted = [...passagersList].sort((a,b) => new Date(b.created_at) - new Date(a.created_at));
                let html = `📅 <strong>Passagers triés par date (du plus récent au plus ancien) :</strong><ul>`;
                sorted.slice(0, 10).forEach(u => {
                    const date = new Date(u.created_at).toLocaleDateString('fr-FR');
                    html += `<li>#${u.id} - ${u.nom} - inscrit le ${date}</li>`;
                });
                if (sorted.length > 10) html += `<li>... et ${sorted.length - 10} autre(s)</li>`;
                html += `</ul>`;
                return html;
            }

            function getBannedToday() {
                const today = new Date().toDateString();
                const bannedToday = passagersList.filter(p => p.statut === 'inactif' && new Date(p.created_at).toDateString() === today);
                return `📊 Aujourd'hui, <strong>${bannedToday.length}</strong> passager(s) ont été bannis.`;
            }

            function checkAnomalies() {
                const anomalies = [];
                const today = new Date();
                const lastWeek = new Date(today.setDate(today.getDate() - 7));
                const recentBans = passagersList.filter(p => p.statut === 'inactif' && new Date(p.created_at) > lastWeek);
                if (recentBans.length > 5) anomalies.push(`⚠️ ${recentBans.length} bannissements récents (7 jours)`);
                if (botStats.actifs < botStats.bannis) anomalies.push(`⚠️ Plus de bannis (${botStats.bannis}) que d'actifs (${botStats.actifs})`);
                if (anomalies.length === 0) return "✅ Aucune anomalie détectée. Tout est normal !";
                return `🚨 <strong>Anomalies détectées :</strong><ul>${anomalies.map(a => `<li>${a}</li>`).join('')}</ul>`;
            }

            function getMostImportantGroup() {
                if (botStats.actifs > botStats.bannis) return `📊 Le groupe <strong>Actifs (${botStats.actifs})</strong> est le plus important.`;
                if (botStats.bannis > botStats.actifs) return `📊 Le groupe <strong>Bannis (${botStats.bannis})</strong> est le plus important.`;
                return `📊 Les groupes Actifs (${botStats.actifs}) et Bannis (${botStats.bannis}) sont équivalents.`;
            }

            function getSystemSummary() {
                const activeRate = botStats.total > 0 ? Math.round((botStats.actifs / botStats.total) * 100) : 0;
                return `📊 <strong>Résumé du système EcoRide</strong><br>
                • 👥 Total passagers : ${botStats.total}<br>
                • 🟢 Actifs : ${botStats.actifs} (${activeRate}%)<br>
                • 🔴 Bannis : ${botStats.bannis}<br>
                • 👑 Admins : ${botStats.admins}<br>
                • 📈 Taux d'activité : ${activeRate}%`;
            }

            function getActivityRate() {
                const activeRate = botStats.total > 0 ? Math.round((botStats.actifs / botStats.total) * 100) : 0;
                return `📈 Le taux d'activité est de <strong>${activeRate}%</strong> (${botStats.actifs} actifs sur ${botStats.total} total).`;
            }

            function getHelpBan() {
                return `❓ <strong>Comment bannir un utilisateur ?</strong><br>
                • Via le chatbot : tapez "bannir 12" (remplacez 12 par l'ID)<br>
                • Via le tableau : cliquez sur le bouton 🚫 dans la colonne Actions<br>
                • L'utilisateur banni ne pourra plus se connecter.`;
            }

            function getHelpActivate() {
                return `❓ <strong>Comment activer/réactiver un passager ?</strong><br>
                • Via le chatbot : tapez "réactiver 12"<br>
                • Via le tableau : cliquez sur ✅ sur un passager banni<br>
                • L'utilisateur pourra à nouveau se connecter.`;
            }

            function getHelpDashboard() {
                return `❓ <strong>Comment utiliser le dashboard ?</strong><br>
                • Statistiques : voir les cartes en haut<br>
                • Graphiques : répartition visuelle des passagers<br>
                • Navigation : menu de gauche pour gérer passagers/trajets<br>
                • Recherche : utilisez la barre de recherche dans l'onglet Passagers`;
            }

            function getHelpInactive() {
                return `❓ <strong>Que signifie "passager inactif" ?</strong><br>
                Un passager inactif (ou banni) ne peut plus :<br>
                • Se connecter à l'application<br>
        • Proposer ou participer à des trajets<br>
                • Accéder à ses données<br>
                Pour le réactiver, utilisez la fonction "réactiver".`;
            }

            function getHelpSearch() {
                return `❓ <strong>Comment rechercher un utilisateur ?</strong><br>
                • Via le chatbot : tapez directement le nom "maram"<br>
                • Via le tableau : utilisez la barre de recherche "Rechercher un passager"<br>
                • Recherche avancée : filtrez par statut (Actif/Banni)`;
            }

            function traitementIntelligent(msg) {
                if (msg.includes("statistiques") || msg.includes("stats")) return getSystemSummary();
                if (msg.includes("combien de passagers actifs")) return `📈 ${botStats.actifs} passager(s) actif(s)`;
                if (msg.includes("combien de passagers bannis")) return `🚫 ${botStats.bannis} passager(s) banni(s)`;
                if (msg.includes("total")) return `👥 ${botStats.total} passager(s) au total`;
                if (msg.match(/bannir\s+\d+/i)) {
                    const id = parseInt(msg.match(/\d+/)[0]);
                    const user = passagersList.find(p => p.id === id);
                    if (user) return `⚠️ Confirmation bannir ${user.nom} ?<br><button onclick="confirmBanAction(${id})" style="background:#ef4444;color:white;border:none;padding:5px 12px;border-radius:6px;cursor:pointer;">Confirmer</button>`;
                    return `❌ ID ${id} non trouvé`;
                }
                if (msg.match(/r(é|e)activer\s+\d+/i)) {
                    const id = parseInt(msg.match(/\d+/)[0]);
                    const user = passagersList.find(p => p.id === id);
                    if (user) return `⚠️ Confirmation réactiver ${user.nom} ?<br><button onclick="confirmUnbanAction(${id})" style="background:#22c55e;color:white;border:none;padding:5px 12px;border-radius:6px;cursor:pointer;">Confirmer</button>`;
                    return `❌ ID ${id} non trouvé`;
                }
                // Recherche simple
                if (msg.length > 2 && msg.length < 30 && !msg.includes(' ')) {
                    return searchUser(msg);
                }
                return `❓ Je n'ai pas compris. Cliquez sur "Aide" pour voir les commandes disponibles.`;
            }

            // Confirmations globales
            window.confirmBanAction = function(id) {
                window.location.href = '<?= BASE_URL ?>controllers/AdminController.php?action=banPassager&id=' + id;
            };
            window.confirmUnbanAction = function(id) {
                window.location.href = '<?= BASE_URL ?>controllers/AdminController.php?action=unbanPassager&id=' + id;
            };

            // UI Chatbot
            function addMessage(text, isUser = false) {
                const messageDiv = document.createElement('div');
                messageDiv.className = `message ${isUser ? 'user' : 'bot'}`;
                messageDiv.innerHTML = text;
                chatbotMessages.appendChild(messageDiv);
                chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
            }

            function showTyping() {
                const typingDiv = document.createElement('div');
                typingDiv.className = 'typing-indicator';
                typingDiv.id = 'typingIndicator';
                typingDiv.innerHTML = '<span></span><span></span><span></span>';
                chatbotMessages.appendChild(typingDiv);
                chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
            }

            function removeTyping() {
                const typing = document.getElementById('typingIndicator');
                if (typing) typing.remove();
            }

            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            function sendMessage(messageText = null) {
                const message = messageText !== null ? messageText : chatbotInput.value.trim();
                if (!message) return;
                
                addMessage(escapeHtml(message), true);
                if (messageText === null) chatbotInput.value = '';
                showTyping();
                
                setTimeout(() => {
                    const response = getAnswerForQuestion(message);
                    removeTyping();
                    addMessage(response, false);
                }, 400);
            }

            // Gestion des catégories
            let currentOpenCategory = null;
            
            function toggleCategory(category) {
                if (currentOpenCategory === category) {
                    questionsPanel.classList.remove('open');
                    currentOpenCategory = null;
                    return;
                }
                
                currentOpenCategory = category;
                const questions = categoriesQuestions[category]?.questions || [];
                const icon = categoriesQuestions[category]?.icon || "📋";
                
                questionsList.innerHTML = questions.map(q => `
                    <div class="question-item" onclick="sendQuestion('${escapeHtml(q).replace(/'/g, "\\'")}')">
                        <i class="fas fa-comment"></i> ${escapeHtml(q)}
                    </div>
                `).join('');
                
                questionsPanel.classList.add('open');
            }

            window.sendQuestion = function(question) {
                questionsPanel.classList.remove('open');
                currentOpenCategory = null;
                sendMessage(question);
            };

            // Événements
            document.querySelectorAll('.category-btn').forEach(btn => {
                btn.addEventListener('click', () => toggleCategory(btn.dataset.category));
            });

            chatbotToggle.addEventListener('click', () => chatbotContainer.classList.toggle('open'));
            chatbotClose.addEventListener('click', () => chatbotContainer.classList.remove('open'));
            chatbotSend.addEventListener('click', () => sendMessage());
            chatbotInput.addEventListener('keypress', (e) => { if (e.key === 'Enter') sendMessage(); });
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && chatbotContainer.classList.contains('open')) chatbotContainer.classList.remove('open'); });

            // Message d'accueil amélioré
            setTimeout(() => {
                addMessage("💡 <strong>Conseil :</strong> Cliquez sur les boutons ci-dessus pour voir les questions prédéfinies par catégorie !", false);
            }, 1000);
        })();
    </script>
<?php require_once __DIR__ . '/ai_helper_widget.php'; ?>
</body>
</html>