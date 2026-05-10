<?php
/* ──────────────────────────────────────────────────────────────
 *  NAVBAR DASHBOARD  (admin_dashboard, admin_profile, edit_passager, passager_details)
 * ────────────────────────────────────────────────────────────── */
function navbar_dashboard(string $extraHtml = ''): void
{
    ?>
    <!-- ══ NAVBAR DASHBOARD ══ -->
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
                Profil
            </a>
            <a href="<?= BASE_URL ?>controllers/AdminController.php?action=dashboard" class="btn-admin-plain">Admin</a>
            <button class="btn-theme-toggle" onclick="toggleTheme()" title="Mode sombre / clair">
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
