<?php
if (!isset($_SESSION['admin_id'])) {
    header('Location: ' . BASE_URL . 'controllers/AdminController.php?action=showLogin');
    exit();
}
// $passager injecté par le contrôleur via showEditPassager()
?>
<?php require_once __DIR__ . '/partials/partials.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eco Ride — Modifier le passager</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }

        body {
            font-family: 'Poppins','Segoe UI',sans-serif;
            background: linear-gradient(135deg, #0A1628 0%, #0D1F3A 100%);
            min-height: 100vh;
            color: #F4F5F7;
        }

        /* ══ MAIN ══ */
        .main-content {
            margin-left: 280px;
            width: calc(100% - 280px);
            min-height: 100vh;
            padding: 0;
        }
        .page-content { padding: 2rem 2.5rem; }

        /* ══ PANEL FORM ══ */
        .details-panel {
            background: linear-gradient(160deg, #0D1F3A 0%, #091525 100%);
            border: 1px solid rgba(25,118,210,0.5);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.7);
            overflow: hidden;
            max-width: 860px;
            margin: 0 auto;
        }

        .panel-header {
            display:flex; align-items:center; justify-content:space-between;
            padding: 1rem 1.4rem;
            border-bottom: 1px solid rgba(25,118,210,0.3);
            background: rgba(25,118,210,0.12);
        }
        .panel-header h2 {
            color: #61B3FA;
            font-size: 1rem; font-weight:700;
            display:flex; align-items:center; gap:10px;
            margin:0;
        }
        .btn-back-panel {
            display:inline-flex; align-items:center; gap:6px;
            background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.2);
            color:#F4F5F7; padding:0.35rem 0.9rem; border-radius:20px;
            font-size:0.82rem; font-weight:600; text-decoration:none;
            transition:background 0.2s; cursor:pointer;
        }
        .btn-back-panel:hover { background:rgba(255,255,255,.14); }

        .panel-body { padding: 1.8rem 1.4rem; }

        /* ══ ALERTS ══ */
        .alert {
            padding: 0.8rem 1.2rem;
            border-radius: 10px;
            margin-bottom: 1.2rem;
            font-size: 0.88rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-success { background: rgba(0,204,106,0.12); border: 1px solid rgba(0,204,106,0.35); color: #00cc6a; }
        .alert-danger  { background: rgba(255,68,68,0.12);  border: 1px solid rgba(255,68,68,0.35);  color: #ff6666; }

        /* ══ FORM ══ */
        .form-group { margin-bottom: 1.2rem; }

        .form-group label {
            display: block;
            font-size: 0.88rem;
            font-weight: 600;
            color: #61B3FA;
            margin-bottom: 0.4rem;
        }

        .form-group label .required { color: #ff6666; margin-left: 2px; }

        .form-control {
            width: 100%;
            background: rgba(10,47,68,0.7);
            border: 1px solid rgba(25,118,210,0.35);
            border-radius: 10px;
            padding: 0.7rem 1rem;
            color: #F4F5F7;
            font-family: 'Poppins', sans-serif;
            font-size: 0.9rem;
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
        }
        .form-control:focus {
            border-color: #1976D2;
            box-shadow: 0 0 0 3px rgba(25,118,210,0.18);
        }
        .form-control.is-invalid {
            border-color: #ff4444;
            box-shadow: 0 0 0 3px rgba(255,68,68,0.15);
        }
        .form-control option { background: #0D1F3A; }

        .invalid-feedback {
            font-size: 0.78rem;
            color: #ff6666;
            margin-top: 0.3rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        /* ══ SUBMIT ACTIONS ══ */
        .form-actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-top: 1.8rem;
            padding-top: 1.2rem;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        .btn-submit {
            display:inline-flex; align-items:center; gap:8px;
            padding:0.6rem 1.6rem; border-radius:25px; font-size:0.9rem;
            font-weight:600; cursor:pointer; border:none; text-decoration:none;
            transition:all 0.2s; font-family: 'Poppins', sans-serif;
        }
        .btn-save {
            background: linear-gradient(135deg, #1976D2, #1565C0);
            color: #fff;
            box-shadow: 0 4px 15px rgba(25,118,210,0.35);
        }
        .btn-save:hover { background: linear-gradient(135deg, #2196F3, #1976D2); transform: translateY(-1px); }
        .btn-cancel {
            background: rgba(255,255,255,0.07);
            color: #F4F5F7;
            border: 1px solid rgba(255,255,255,0.2);
        }
        .btn-cancel:hover { background: rgba(255,255,255,0.14); }
        .btn-details {
            background: rgba(25,118,210,0.12);
            color: #61B3FA;
            border: 1px solid rgba(25,118,210,0.4);
        }
        .btn-details:hover { background: rgba(25,118,210,0.25); }

        /* ══ LIGHT MODE ══ */
        body.light-mode { background:linear-gradient(135deg,#EDF2F7 0%,#DBEAFE 100%) !important; color:#1A2844 !important; }
        body.light-mode .details-panel { background:rgba(255,255,255,.95) !important; }
        body.light-mode .form-control { background:rgba(240,248,255,0.9) !important; color:#1A2844 !important; border-color:rgba(25,118,210,0.3) !important; }

        @media (max-width:768px) {
            .main-content { margin-left:0; width:100%; }
            .page-content { padding:1rem; }
            .form-row { grid-template-columns: 1fr; }
        }
    </style>
<?php render_nav_css(); ?>
</head>
<body>

<?php require_once __DIR__ . '/partials/partials.php'; ?>
<?php sidebar_dashboard('passagers'); ?>

<!-- ══ MAIN ══ -->
<div class="main-content">
    <div class="page-content">

        <?php navbar_dashboard(); ?>

        <!-- PANEL MODIFIER PASSAGER -->
        <div class="details-panel">

            <!-- En-tête du panel -->
            <div class="panel-header">
                <h2>
                    <i class="fas fa-user-edit"></i>
                    Modifier le passager
                </h2>
                <a href="<?= BASE_URL ?>controllers/AdminController.php?action=dashboard&tab=passagers" class="btn-back-panel">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
            </div>

            <!-- Corps du panel -->
            <div class="panel-body">

                <!-- Alertes session -->
                <?php if (!empty($_SESSION['admin_success'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?= htmlspecialchars($_SESSION['admin_success']) ?>
                    </div>
                    <?php unset($_SESSION['admin_success']); ?>
                <?php endif; ?>

                <?php if (!empty($_SESSION['admin_error'])): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($_SESSION['admin_error']) ?>
                    </div>
                    <?php unset($_SESSION['admin_error']); ?>
                <?php endif; ?>

                <!-- Formulaire de modification -->
                <form method="POST" action="<?= BASE_URL ?>controllers/AdminController.php?action=editPassager" id="editPassagerForm" novalidate>
                    <input type="hidden" name="id" value="<?= (int)$passager['id'] ?>">
                    <!-- Redirection retour vers la page de détails après sauvegarde -->
                    <input type="hidden" name="redirect_to" value="passager_details">

                    <div class="form-row">
                        <!-- Prénom -->
                        <div class="form-group">
                            <label for="prenom">Prénom <span class="required">*</span></label>
                            <input
                                type="text"
                                id="prenom"
                                name="prenom"
                                class="form-control"
                                value="<?= htmlspecialchars($passager['prenom'] ?? '') ?>"
                                placeholder="Prénom du passager"
                                required
                                minlength="2"
                            >
                            <div class="invalid-feedback" id="prenom-error"></div>
                        </div>

                        <!-- Nom -->
                        <div class="form-group">
                            <label for="nom">Nom <span class="required">*</span></label>
                            <input
                                type="text"
                                id="nom"
                                name="nom"
                                class="form-control"
                                value="<?= htmlspecialchars($passager['nom'] ?? '') ?>"
                                placeholder="Nom du passager"
                                required
                                minlength="2"
                            >
                            <div class="invalid-feedback" id="nom-error"></div>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                        <label for="email">Email <span class="required">*</span></label>
                        <input
                            type="text"
                            id="email"
                            name="email"
                            class="form-control"
                            value="<?= htmlspecialchars($passager['email'] ?? '') ?>"
                            placeholder="adresse@email.com"
                            autocomplete="email"
                        >
                        <div class="invalid-feedback" id="email-error"></div>
                    </div>

                    <!-- Téléphone -->
                    <div class="form-group">
                        <label for="telephone">Téléphone</label>
                        <input
                            type="text"
                            id="telephone"
                            name="telephone"
                            class="form-control"
                            value="<?= htmlspecialchars($passager['telephone'] ?? '') ?>"
                            placeholder="+216 XX XXX XXX"
                            maxlength="20"
                        >
                        <div class="invalid-feedback" id="telephone-error"></div>
                        <div style="font-size:0.75rem;color:#A7A9AC;margin-top:0.3rem;">
                            Formats acceptés : +216XXXXXXXX, 0XXXXXXXXX, ou laisser vide
                        </div>
                    </div>

                    <!-- Statut -->
                    <div class="form-group">
                        <label for="statut">Statut</label>
                        <select id="statut" name="statut" class="form-control">
                            <option value="actif"  <?= ($passager['statut'] ?? '') === 'actif'  ? 'selected' : '' ?>>Actif</option>
                            <option value="banni"  <?= ($passager['statut'] ?? '') === 'banni'  ? 'selected' : '' ?>>Banni</option>
                        </select>
                    </div>

                    <!-- Boutons d'action -->
                    <div class="form-actions">
                        <button type="submit" class="btn-submit btn-save">
                            <i class="fas fa-save"></i> Enregistrer
                        </button>
<a href="<?= BASE_URL ?>controllers/AdminController.php?action=dashboard&tab=passagers"
                           class="btn-submit btn-cancel">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                    </div>

                </form>

            </div><!-- /panel-body -->
        </div><!-- /details-panel -->

    </div><!-- /page-content -->
</div><!-- /main-content -->

<script>
    /* ── Validation front-end ── */
    const form = document.getElementById('editPassagerForm');

    form.addEventListener('submit', function(e) {
        let valid = true;

        const prenom    = document.getElementById('prenom');
        const nom       = document.getElementById('nom');
        const email     = document.getElementById('email');
        const telephone = document.getElementById('telephone');

        clearError(prenom,    'prenom-error');
        clearError(nom,       'nom-error');
        clearError(email,     'email-error');
        clearError(telephone, 'telephone-error');

        // Prénom
        if (prenom.value.trim().length < 2) {
            showError(prenom, 'prenom-error', 'Le prénom doit contenir au moins 2 caractères.');
            valid = false;
        }

        // Nom
        if (nom.value.trim().length < 2) {
            showError(nom, 'nom-error', 'Le nom doit contenir au moins 2 caractères.');
            valid = false;
        }

        // Email — validation JS complète (pas de type="email" HTML)
        const emailVal   = email.value.trim();
        const emailRegex = /^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/;
        if (emailVal === '') {
            showError(email, 'email-error', 'L\'adresse email est obligatoire.');
            valid = false;
        } else if (!emailRegex.test(emailVal)) {
            showError(email, 'email-error', 'Veuillez saisir une adresse email valide (ex : nom@domaine.com).');
            valid = false;
        } else if (emailVal.indexOf('..') !== -1) {
            showError(email, 'email-error', 'L\'adresse email ne peut pas contenir deux points consécutifs.');
            valid = false;
        }

        // Téléphone — validation JS (champ optionnel)
        const telVal   = telephone.value.trim();
        // Accepte : vide, +216XXXXXXXX, 00216XXXXXXXX, 0XXXXXXXXX, ou numéro international +XX…
        const telRegex = /^(\+?[0-9]{1,4}[\s\-]?)?(\(?\d{1,4}\)?[\s\-]?)?[\d\s\-]{6,15}$/;
        if (telVal !== '' && !telRegex.test(telVal)) {
            showError(telephone, 'telephone-error', 'Numéro de téléphone invalide. Exemples : +21698765432, 0698765432.');
            valid = false;
        } else if (telVal !== '' && telVal.replace(/\D/g, '').length < 8) {
            showError(telephone, 'telephone-error', 'Le numéro de téléphone doit contenir au moins 8 chiffres.');
            valid = false;
        }

        if (!valid) e.preventDefault();
    });

    // Validation en temps réel sur l'email
    document.getElementById('email').addEventListener('input', function() {
        const emailRegex = /^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/;
        const val = this.value.trim();
        if (val === '' || emailRegex.test(val)) {
            clearError(this, 'email-error');
        }
    });

    // Validation en temps réel sur le téléphone (n'accepte que chiffres, +, -, espaces)
    document.getElementById('telephone').addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9+\-\s()]/g, '');
        clearError(this, 'telephone-error');
    });

    function showError(input, errorId, message) {
        input.classList.add('is-invalid');
        const el = document.getElementById(errorId);
        if (el) el.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + message;
    }
    function clearError(input, errorId) {
        input.classList.remove('is-invalid');
        const el = document.getElementById(errorId);
        if (el) el.textContent = '';
    }

    /* ── Thème ── */
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
</body>
</html>
