<?php
/**
 * ══════════════════════════════════════════════════════════════
 *  BACKOFFICE PARTIALS — fichier unique
 * ══════════════════════════════════════════════════════════════
 *
 *  Ce fichier centralise :
 *    1. Tous les CSS communs navbar / sidebar → render_nav_css()
 *    2. Les fonctions de rendu HTML pour chaque variante
 *
 *  Utilisation dans chaque page backoffice :
 *    <?php require __DIR__ . '/partials/partials.php'; ?>
 *    Puis dans le <head>  : <?php render_nav_css(); ?>
 *    Puis dans le <body>  : <?php sidebar_xxx(...); ?>
 *                           <?php navbar_xxx(...); ?>
 *
 *  Fonctions disponibles :
 *
 *  render_nav_css()
 *      → Injecte le bloc <style> commun navbar + sidebar.
 *        À appeler UNE FOIS dans le <head> de chaque page.
 *
 *  sidebar_spa(string $activePage = 'dashboard')
 *      → Sidebar SPA (navigation JS via data-page)
 *        Pages : admin_dashboard.php
 *
 *  sidebar_dashboard(string $activePage = '')
 *      → Sidebar avec liens href vers le dashboard
 *        Pages : admin_profile.php, edit_passager.php, passager_details.php
 *        Valeurs $activePage : 'dashboard' | 'passagers' | 'trajets' |
 *                              'destinations' | 'evenements' | 'reclamations' |
 *                              'vehicules' | 'lost_found'
 *
 *  sidebar_compact(string $activeItem = '')
 *      → Sidebar style compact (SVG logo + info admin)
 *        Pages : add_user.php, edit_user.php, users_list.php
 *        Valeurs $activeItem : 'dashboard' | 'users' | 'add_user' | 'site'
 *
 *  navbar_dashboard(string $extraHtml = '')
 *      → Top-bar (bouton Profil + Admin + thème)
 *        Pages : admin_dashboard.php, admin_profile.php,
 *                edit_passager.php, passager_details.php
 *
 *  navbar_compact(string $extraHtml = '')
 *      → Top-bar compacte (bouton Admin + thème)
 *        Pages : add_user.php, edit_user.php, users_list.php
 *        $extraHtml : HTML injecté à droite (ex: bouton Retour, Ajouter…)
 *
 * ══════════════════════════════════════════════════════════════
 */


/* ──────────────────────────────────────────────────────────────
 *  CSS COMMUN — navbar + sidebar (toutes variantes)
 
* ────────────────────────────────────────────────────────────── */
require_once __DIR__ . '/../../../config.php';

function render_nav_css(): void
{
    ?>
    <style>
        /* ════════════════════════════════════════════════════
         *  SIDEBAR — variante Dashboard (spa / dashboard)
         * ════════════════════════════════════════════════════ */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg,#2F76BC 0%,#1E5EA5 50%,#174C8A 100%);
            padding: 1.5rem 0;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            overflow-y: auto;
            z-index: 100;
            box-shadow: 4px 0 20px rgba(0,0,0,.2);
            display: flex;
            flex-direction: column;
        }
        .sidebar-header, .sidebar .logo {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,.15);
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .sidebar .logo i            { font-size: 48px; color: #61B3FA; }
        .sidebar .logo h2           { color: #61B3FA; margin-top: 10px; }
        .sidebar .logo .admin-label {
            font-size: 0.62rem; letter-spacing: 0.2em;
            color: rgba(255,255,255,.6); text-transform: uppercase; margin-top: 4px;
        }
        .sidebar nav ul             { list-style: none; }
        .sidebar nav ul li          { margin-bottom: 0.5rem; }
        .sidebar nav ul li a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: .75rem 1.5rem;
            color: #EAF4FF;
            text-decoration: none;
            transition: all .3s;
            font-size: .9rem;
            margin: 0 0.5rem;
            border-radius: 10px;
            font-weight: 500;
        }
        .sidebar nav ul li a:hover {
            background: rgba(111,168,220,.3);
            color: #fff;
            transform: translateX(4px);
        }
        .sidebar nav ul li a.active {
            background: linear-gradient(135deg,#6FA8DC,#8FC1F5);
            color: #FFFFFF;
            box-shadow: 0 4px 12px rgba(111,168,220,.3);
        }

        .sidebar-footer {
            margin-top: auto;
            border-top: 1px solid rgba(255,255,255,.15);
            padding-top: 1rem;
        }

        /* ════════════════════════════════════════════════════
         *  SIDEBAR — variante Compact
         * ════════════════════════════════════════════════════ */
        .sidebar-logo {
            display: flex; align-items: center; gap: 10px;
            margin-bottom: 1.5rem; padding-bottom: 1rem;
            border-bottom: 2px solid #1976D2;
        }
        .sidebar-logo i   { font-size: 28px; color: #1976D2; }
        .sidebar-logo h2  { font-size: 1.3rem; }
        .sidebar-logo span { color: #1976D2; }

        .admin-info {
            background: rgba(25,118,210,0.1); border-radius: 12px;
            padding: 0.8rem; margin-bottom: 1.5rem; text-align: center;
            border: 1px solid rgba(25,118,210,0.2);
        }
        .admin-info i     { font-size: 1.5rem; color: #1976D2; display: block; margin-bottom: 0.3rem; }
        .admin-info small { color: #A7A9AC; font-size: 0.75rem; display: block; }

        .nav-section {
            color: #A7A9AC; font-size: 0.7rem; text-transform: uppercase;
            letter-spacing: 1px; margin: 1rem 0 0.5rem 0.5rem;
        }
        .nav-item {
            display: flex; align-items: center; gap: 12px;
            padding: 0.75rem 1rem; color: #fff; text-decoration: none;
            border-radius: 12px; margin-bottom: 0.3rem; transition: all 0.3s; font-size: 0.9rem;
        }
        .nav-item i                  { width: 20px; color: #A7A9AC; transition: color 0.3s; }
        .nav-item:hover,
        .nav-item.active             { background: rgba(25,118,210,0.2); color: #1976D2; }
        .nav-item:hover i,
        .nav-item.active i           { color: #1976D2; }

        /* ════════════════════════════════════════════════════
         *  LOGOUT — commun
         * ════════════════════════════════════════════════════ */
        .logout-btn {
            display: flex; align-items: center; gap: 10px;
            padding: 0.75rem 1rem; color: #ff6b6b; text-decoration: none;
            border-radius: 12px; transition: all 0.3s; font-size: 0.9rem;
        }
        .logout-btn:hover { background: rgba(255,68,68,0.2); }

        /* ════════════════════════════════════════════════════
         *  TOP-BAR / NAVBAR — commune à toutes les pages
         * ════════════════════════════════════════════════════ */
        /* ── Navbar (identique à admin.php) ── */
        .admin-header { background:linear-gradient(90deg,#071C2F,#0A2A47,#0D355B); padding:1rem 2rem; display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem; border-radius:12px; border-bottom:1px solid rgba(255,255,255,.08); flex-wrap:wrap; gap:1rem; }
        .admin-logo { display:flex; flex-direction:column; }
        .admin-logo .logo-eco { font-size:1.5rem; font-weight:700; letter-spacing:1px; font-family:'Poppins',sans-serif; }
        .admin-logo .logo-eco span:first-child { color:#4EA3FF; }
        .admin-logo .logo-eco span:last-child { color:#6BB8FF; }
        .admin-logo .logo-tagline { font-size:.65rem; color:#A8C1D9; margin-top:2px; }
        .admin-nav { display:flex; gap:.5rem; align-items:center; flex-wrap:wrap; }
        .admin-nav a { text-decoration:none; padding:.5rem 1.2rem; border-radius:30px; font-size:.9rem; font-weight:500; transition:all .3s; background:transparent; color:#CFE6FF; font-family:'Poppins',sans-serif; }
        .admin-nav a:hover { background:rgba(255,255,255,.1); color:#fff; }
        .admin-nav .profile-btn { background:#003050; color:#fff; display:flex; align-items:center; gap:10px; padding:.5rem 1.2rem; }
        .admin-nav .profile-btn:hover { background:#002050; transform:translateY(-2px); box-shadow:0 4px 12px rgba(0,48,80,.4); }
        .profile-avatar { width:36px; height:36px; background:#5FA8FF; border-radius:50%; display:flex; align-items:center; justify-content:center; overflow:hidden; flex-shrink:0; border:2px solid rgba(255,255,255,.3); }
        .profile-avatar img { width:100%; height:100%; object-fit:cover; border-radius:50%; }
        .profile-avatar i { font-size:.9rem; color:#fff; }
        .admin-nav .admin-btn { background:rgba(231,76,60,.2); border:1px solid rgba(231,76,60,.4); color:#e74c3c; }
        .admin-nav .admin-btn:hover { background:rgba(231,76,60,.35); }
        .theme-btn { background:rgba(255,255,255,.1); border:none; width:38px; height:38px; border-radius:50%; cursor:pointer; font-size:1.1rem; transition:all .3s; display:flex; align-items:center; justify-content:center; color:#fff; }
        .theme-btn:hover { background:rgba(255,255,255,.2); transform:rotate(15deg); }

        .top-bar {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 2rem;
            background: linear-gradient(90deg, #0D2350 0%, #0F3166 50%, #0D2350 100%);
            border-radius: 16px; padding: 0.75rem 1.5rem;
            border: 1px solid rgba(97,179,250,0.18);
            box-shadow: 0 4px 24px rgba(0,0,0,0.25);
            position: sticky; top: 1rem; z-index: 600;
            flex-wrap: nowrap; width: 100%; box-sizing: border-box;
        }
        .navbar-logo              { display: flex; flex-direction: column; line-height: 1.2; }
        .navbar-logo strong       { font-size: 1rem; font-weight: 800; color: #61B3FA; letter-spacing: 0.05em; }
        .navbar-logo span         { font-size: 0.62rem; color: rgba(255,255,255,0.75); letter-spacing: 0.08em; }
        .top-bar-right            { display: flex; align-items: center; gap: 0.5rem; flex-wrap: nowrap; flex-shrink: 0; }

        /* ── Bouton "Voir site" ── */
        .btn-top {
            background: transparent; color: #fff; padding: 0.4rem 1rem;
            border-radius: 20px; text-decoration: none;
            display: inline-flex; align-items: center; gap: 8px;
            transition: background 0.2s; font-size: 0.9rem;
            font-weight: 500; border: none; white-space: nowrap; cursor: pointer;
        }
        .btn-top:hover { background: rgba(255,255,255,0.12); }

        /* ── Bouton Mon Profil ── */
        .btn-admin-profile,
        .btn-admin-active {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(255,255,255,0.1); color: #fff;
            border: 1px solid rgba(255,255,255,0.18);
            padding: 0.3rem 1rem 0.3rem 0.4rem; border-radius: 25px;
            font-size: 0.88rem; cursor: pointer; font-family: inherit;
            font-weight: 500; transition: all 0.2s; text-decoration: none;
        }
        .btn-admin-profile:hover,
        .btn-admin-active:hover  { background: rgba(255,255,255,0.22); }

        .btn-admin-profile .admin-avatar-btn,
        .btn-admin-active  .admin-avatar-btn {
            width: 30px; height: 30px; border-radius: 50%; overflow: hidden;
            display: flex; align-items: center; justify-content: center;
            background: rgba(25,118,210,0.35); border: 2px solid rgba(97,179,250,0.5); flex-shrink: 0;
        }
        .btn-admin-profile .admin-avatar-btn img,
        .btn-admin-active  .admin-avatar-btn img { width: 100%; height: 100%; object-fit: cover; }
        .btn-admin-profile .admin-avatar-btn i,
        .btn-admin-active  .admin-avatar-btn i   { font-size: 0.85rem; color: #61B3FA; }

        /* ── Bouton Admin (lien dashboard) ── */
        .btn-admin-plain {
            display: inline-flex; align-items: center; gap: 6px;
            background: transparent; color: #E74C3C;
            border: 1px solid rgba(231,76,60,0.45);
            padding: 0.4rem 1.1rem; border-radius: 25px;
            font-size: 0.9rem; font-weight: 700; text-decoration: none;
            transition: all 0.2s; letter-spacing: 0.02em; cursor: pointer;
        }
        .btn-admin-plain:hover { background: rgba(231,76,60,0.12); border-color: #E74C3C; color: #FF6B6B; }

        /* ── Bouton Retour ── */
        .back-btn {
            background: rgba(25,118,210,0.2); color: #1976D2;
            padding: 0.5rem 1.2rem; border-radius: 20px;
            text-decoration: none; font-size: 0.85rem;
            border: 1px solid rgba(25,118,210,0.3);
            display: flex; align-items: center; gap: 8px; transition: all 0.3s;
        }

        /* ── Bouton Ajouter ── */
        .btn-add {
            background: linear-gradient(135deg, #1976D2, #1565C0);
            color: white; padding: 0.5rem 1.2rem; border-radius: 20px;
            text-decoration: none; font-size: 0.85rem; transition: all 0.3s;
            display: flex; align-items: center; gap: 8px;
        }

        /* ── Toggle thème ── */
        .btn-theme-toggle {
            width: 34px; height: 34px; border-radius: 50%;
            background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.22);
            color: #fff; font-size: 0.92rem;
            display: inline-flex; align-items: center; justify-content: center;
            cursor: pointer; transition: all 0.25s; flex-shrink: 0;
        }
        .btn-theme-toggle:hover { background: rgba(255,255,255,0.18); }

        /* ════════════════════════════════════════════════════
         *  LIGHT MODE — commun
         * ════════════════════════════════════════════════════ */
        body.light-mode {
            background: linear-gradient(135deg, #EDF2F7 0%, #DBEAFE 100%) !important;
            color: #1A2844 !important;
        }

        /* ════════════════════════════════════════════════════
         *  RESPONSIVE — commun
         * ════════════════════════════════════════════════════ */
        @media (max-width: 768px) {
            .sidebar      { display: none; }
            .top-bar      { flex-direction: column; gap: 1rem; align-items: flex-start; padding: 0.8rem 1rem; }
            .main-content { margin-left: 0 !important; width: 100% !important; }
            .page-content { padding: 1rem; }
        }
    </style>
    <?php
}

require_once __DIR__ . '/navbar.php';
require_once __DIR__ . '/sidebar.php';
