<?php
/* ──────────────────────────────────────────────────────────────
 *  NAVBAR DASHBOARD  (admin_dashboard, admin_profile, edit_passager, passager_details)
 * ────────────────────────────────────────────────────────────── */
function navbar_dashboard(string $extraHtml = ''): void
{
    ?>
    <!-- ══ NAVBAR DASHBOARD ══ -->
    <div class="admin-header">
        <div class="admin-logo">
            <div class="logo-eco">
                <span>ECO</span> <span>RIDE</span>
            </div>
            <div class="logo-tagline">Covoiturage Intelligent</div>
        </div>
        <div class="admin-nav">
            <a href="<?= BASE_URL ?>View/frontoffice/tous_les_trajets.php">Voir site</a>

            <a href="<?= BASE_URL ?>Controller/AdminController.php?action=showProfile" class="profile-btn">
                <div class="profile-avatar">
                    <?php if (!empty($_SESSION['admin_photo'])): ?>
                        <img src="<?= BASE_URL ?>uploads/photos/<?= htmlspecialchars($_SESSION['admin_photo']) ?>" alt="Photo admin" onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='flex';">
                        <i class="fas fa-user-shield" style="display:none"></i>
                    <?php else: ?>
                        <i class="fas fa-user-shield"></i>
                    <?php endif; ?>
                </div>
                <span>Profil</span>
            </a>

            <a href="<?= BASE_URL ?>Controller/AdminController.php?action=dashboard" class="admin-btn">Admin</a>

            <button class="theme-btn" id="themeToggle" title="Changer le thème" onclick="toggleTheme()">
                <i class="fas fa-moon themeIcon"></i>
            </button>
            <?= $extraHtml ?>
        </div>
    </div>
    <?php
}

/* ──────────────────────────────────────────────────────────────
 *  NAVBAR COMPACT  (add_user, edit_user, users_list)
 * ────────────────────────────────────────────────────────────── */
function navbar_compact(string $extraHtml = ''): void
{
    ?>
    <!-- ══ NAVBAR COMPACT ══ -->
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
                        <img src="<?= BASE_URL ?>uploads/photos/<?= htmlspecialchars($_SESSION['admin_photo']) ?>" onerror="this.onerror=null;this.src=this.dataset.fallback" data-fallback="<?= BASE_URL ?>serve_image.php?type=admin&id=<?= intval($_SESSION['admin_id']) ?>" alt="">
                    <?php else: ?>
                        <i class="fas fa-user-shield"></i>
                    <?php endif; ?>
                </div>
                Admin
            </a>
            <button class="btn-theme-toggle" onclick="toggleTheme()" title="Mode sombre / clair">
                <i class="fas fa-moon themeIcon"></i>
            </button>
            <?= $extraHtml ?>
        </div>
    </div>
    <?php
}
