<?php
/* ──────────────────────────────────────────────────────────────
 *  SIDEBAR SPA  (admin_dashboard)
 * ────────────────────────────────────────────────────────────── */
function sidebar_spa(string $activePage = 'dashboard'): void
{
    $a = fn(string $page) => $activePage === $page ? 'class="active"' : '';
    ?>
    <!-- ══ SIDEBAR SPA DASHBOARD ══ -->
    <div class="sidebar">
        <div>
            <div class="logo">
                <img src="<?= BASE_URL ?>uploads/photos/photo.png" onerror="this.onerror=null;this.src=this.dataset.fallback" data-fallback="<?= BASE_URL ?>serve_image.php?type=logo" alt="EcoRide Logo"
                     style="width:60px;height:60px;object-fit:contain;background:transparent;vertical-align:middle;">
                <h2>EcoRide</h2>
                <div style="font-size:0.62rem;letter-spacing:0.2em;color:rgba(255,255,255,.6);text-transform:uppercase;margin-top:4px;">Administration</div>
            </div>
            <div style="height:1px;background:rgba(255,255,255,.15);margin-bottom:1.2rem;"></div>
            <div style="font-size:0.62rem;font-weight:700;letter-spacing:0.18em;color:rgba(255,255,255,.55);text-transform:uppercase;padding:0 1rem 0.5rem;">Gestion</div>
            <nav>
                <ul>
                    <li><a href="<?= BASE_URL ?>views/backoffice/admin_dashboard.php" <?= $activePage === 'dashboard' ? 'class="active"' : '' ?>><i class="fas fa-gauge-high"></i> Dashboard</a></li>
                    <li><a href="#" <?= $a('passagers') ?> data-page="passagers"><i class="fas fa-users"></i> Passagers</a></li>
                    <li><a href="#" <?= $a('trajets') ?>><i class="fas fa-route"></i> Trajets</a></li>
                    <li><a href="#" <?= $a('destinations') ?>><i class="fas fa-map-pin"></i> Destinations</a></li>
                    <li><a href="<?= BASE_URL ?>views/backoffice/dashboard_event.php" <?= $activePage === 'evenements' ? 'class="active"' : '' ?>><i class="fas fa-calendar-alt"></i> Événements</a></li>
                    <li><a href="#" <?= $a('reclamations') ?>><i class="fas fa-triangle-exclamation"></i> Réclamations</a></li>
                    <li><a href="#" <?= $a('vehicules') ?>><i class="fas fa-car"></i> Véhicules</a></li>
                    <li><a href="#" <?= $a('lost_found') ?>><i class="fas fa-magnifying-glass"></i> Objets perdus</a></li>
                </ul>
            </nav>
        </div>
        <div class="sidebar-footer">
            <a href="<?= BASE_URL ?>controllers/AdminController.php?action=logout" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </div>
    </div>
    <?php
}

/* ──────────────────────────────────────────────────────────────
 *  SIDEBAR DASHBOARD  (admin_profile, edit_passager, passager_details)
 * ────────────────────────────────────────────────────────────── */
function sidebar_dashboard(string $activePage = ''): void
{
    $a   = fn(string $page) => $activePage === $page ? 'class="active"' : '';
    $url = BASE_URL . 'controllers/AdminController.php?action=dashboard';
    ?>
    <!-- ══ SIDEBAR DASHBOARD ══ -->
    <div class="sidebar" id="sidebar">
        <div>
            <div class="logo">
                <img src="<?= BASE_URL ?>uploads/photos/photo.png" onerror="this.onerror=null;this.src=this.dataset.fallback" data-fallback="<?= BASE_URL ?>serve_image.php?type=logo" alt="EcoRide Logo"
                     style="width:60px;height:60px;object-fit:contain;background:transparent;vertical-align:middle;">
                <h2>EcoRide</h2>
                <div style="font-size:0.62rem;letter-spacing:0.2em;color:rgba(255,255,255,.6);text-transform:uppercase;margin-top:4px;">Administration</div>
            </div>
            <div style="height:1px;background:rgba(255,255,255,.15);margin-bottom:1.2rem;"></div>
            <div style="font-size:0.62rem;font-weight:700;letter-spacing:0.18em;color:rgba(255,255,255,.55);text-transform:uppercase;padding:0 1rem 0.5rem;">Gestion</div>
            <nav>
                <ul>
                    <li><a href="<?= $url ?>"               <?= $a('dashboard')    ?>><i class="fas fa-gauge-high"></i> Dashboard</a></li>
                    <li><a href="<?= $url ?>#passagers"     <?= $a('passagers')    ?>><i class="fas fa-users"></i> Passagers</a></li>
                    <li><a href="#"       <?= $a('trajets')      ?>><i class="fas fa-route"></i> Trajets</a></li>
                    <li><a href="#"               <?= $a('destinations') ?>><i class="fas fa-map-pin"></i> Destinations</a></li>
                    <li><a href="<?= BASE_URL ?>views/backoffice/dashboard_event.php" <?= $activePage === 'evenements' ? 'class="active"' : '' ?>><i class="fas fa-calendar-alt"></i> Événements</a></li>
                    <li><a href="#"     <?= $a('vehicules')    ?>><i class="fas fa-car"></i> Véhicules</a></li>
                    <li><a href="#" <?= $a('lost_found')   ?>><i class="fas fa-magnifying-glass"></i> Objets perdus</a></li>
                </ul>
            </nav>
        </div>
        <div class="sidebar-footer">
            <a href="<?= BASE_URL ?>controllers/AdminController.php?action=logout" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </div>
    </div>
    <?php
}

/* ──────────────────────────────────────────────────────────────
 *  SIDEBAR COMPACT  (add_user, edit_user, users_list)
 * ────────────────────────────────────────────────────────────── */
function sidebar_compact(string $activeItem = ''): void
{
    $a = fn(string $item) => $activeItem === $item ? 'active' : '';
    ?>
    <!-- ══ SIDEBAR COMPACT ══ -->
    <aside class="sidebar" style="width:260px;background:#0D1F3A;border-right:1px solid #1976D2;">
        <div class="sidebar-logo">
            <svg width="32" height="32" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg"
                 style="filter:drop-shadow(0 0 8px rgba(97,179,250,.45))">
                <path d="M22 4C22 4 8 10 8 24C8 31.732 14.268 38 22 38C29.732 38 36 31.732 36 24C36 14 28 8 22 4Z"
                      fill="url(#lg_s)" opacity="0.95"/>
                <path d="M22 38L22 12" stroke="rgba(255,255,255,0.3)" stroke-width="1.2" stroke-linecap="round"/>
                <defs>
                    <linearGradient id="lg_s" x1="12" y1="4" x2="36" y2="38" gradientUnits="userSpaceOnUse">
                        <stop offset="0%" stop-color="#61B3FA"/>
                        <stop offset="100%" stop-color="#1976D2"/>
                    </linearGradient>
                </defs>
            </svg>
            <h2>Eco<span>Ride</span></h2>
        </div>
        <div class="admin-info">
            <i class="fas fa-user-shield"></i>
            <strong><?= htmlspecialchars($_SESSION['admin_nom'] ?? 'Admin') ?></strong>
            <small><?= htmlspecialchars($_SESSION['admin_email'] ?? '') ?></small>
        </div>
        <span class="nav-section">Navigation</span>
        <a href="<?= BASE_URL ?>controllers/AdminController.php?action=dashboard"
           class="nav-item <?= $a('dashboard') ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a href="<?= BASE_URL ?>controllers/AdminController.php?action=listUsers"
           class="nav-item <?= $a('users') ?>">
            <i class="fas fa-users"></i> Utilisateurs
        </a>
        <a href="<?= BASE_URL ?>controllers/AdminController.php?action=showAddUser"
           class="nav-item <?= $a('add_user') ?>">
            <i class="fas fa-user-plus"></i> Ajouter utilisateur
        </a>
        <a href="<?= BASE_URL ?>controllers/AIController.php?action=showAssistant"
           class="nav-item <?= $a('ai_helper') ?>" style="<?= $a('ai_helper') ? '' : 'color:#61B3FA;' ?>">
            <i class="fas fa-robot" style="color:#1976D2;"></i> AI Helper
        </a>
        <span class="nav-section">Site</span>
        <a href="<?= BASE_URL ?>controllers/UserController.php?action=index"
           class="nav-item <?= $a('site') ?>">
            <i class="fas fa-globe"></i> Voir le site
        </a>
        <div class="sidebar-footer">
            <a href="<?= BASE_URL ?>controllers/AdminController.php?action=logout" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </div>
    </aside>
    <?php
}
