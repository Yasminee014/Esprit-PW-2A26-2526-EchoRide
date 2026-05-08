<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($isEditMode) && $isEditMode ? 'Modifier' : 'Ajouter' ?> un véhicule | EcoRide</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap');

        :root {
            --bg: #060D1A;
            --surface: rgba(255,255,255,0.04);
            --border: rgba(97,179,250,0.18);
            --border-focus: #61B3FA;
            --accent: #61B3FA;
            --accent2: #1976D2;
            --text: #E8EDF5;
            --muted: #7A8899;
            --red: #e74c3c;
            --green: #27ae60;
            --radius: 14px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }
        body::before {
            content: '';
            position: fixed; inset: 0;
            background:
                radial-gradient(ellipse 60% 40% at 80% 10%, rgba(25,118,210,0.12) 0%, transparent 60%),
                radial-gradient(ellipse 40% 50% at 10% 80%, rgba(97,179,250,0.07) 0%, transparent 60%);
            pointer-events: none; z-index: 0;
        }

        .container { position: relative; z-index: 1; max-width: 640px; margin: 0 auto; padding: 2rem 1.5rem 4rem; }

        .hero {
            background: linear-gradient(135deg, #0F3B6E, #1976D2);
            border-radius: 20px; padding: 1.6rem 2rem; margin-bottom: 1.5rem;
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 8px 32px rgba(25,118,210,0.3);
        }
        .hero h2 { font-family: 'Syne', sans-serif; font-size: 1.4rem; font-weight: 700; margin-bottom: 0.25rem; }
        .hero p { color: rgba(255,255,255,0.75); font-size: 0.85rem; }
        .hero-icon { font-size: 2.8rem; opacity: 0.3; }

        .alert { border-radius: 12px; padding: 0.85rem 1.1rem; margin-bottom: 1.2rem; font-size: 0.88rem; }
        .alert-error  { background: rgba(231,76,60,.1);  border: 1px solid rgba(231,76,60,.3);  color: #ffd6d1; }
        .alert-success{ background: rgba(39,174,96,.1);  border: 1px solid rgba(39,174,96,.3);  color: #d4ffe5; }
        .alert ul { margin: 0.4rem 0 0 1.2rem; }

        .form-card {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: 20px; padding: 2rem; backdrop-filter: blur(12px);
        }

        .section-title {
            font-family: 'Syne', sans-serif; font-size: 0.72rem; font-weight: 700;
            letter-spacing: 0.12em; text-transform: uppercase; color: var(--accent);
            margin: 1.75rem 0 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid var(--border);
        }
        .section-title:first-child { margin-top: 0; }

        .field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        @media (max-width: 480px) { .field-row { grid-template-columns: 1fr; } }

        .form-group { margin-bottom: 1.1rem; }
        .form-group label { display: block; font-size: 0.8rem; font-weight: 500; color: var(--muted); margin-bottom: 0.45rem; }

        .input-wrap { position: relative; display: flex; align-items: center; }
        .input-wrap input,
        .input-wrap select {
            width: 100%; padding: 0.75rem 3rem 0.75rem 0.95rem;
            border-radius: 10px; border: 1px solid var(--border);
            background: rgba(0,0,0,0.25); color: var(--text);
            font-family: 'DM Sans', sans-serif; font-size: 0.9rem;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s; outline: none;
        }
        .input-wrap input:focus, .input-wrap select:focus {
            border-color: var(--border-focus); box-shadow: 0 0 0 3px rgba(97,179,250,0.15); background: rgba(0,0,0,0.35);
        }
        .input-wrap input.v-ok {
            border-color: rgba(39,174,96,0.7); background: rgba(39,174,96,0.07);
            animation: flashGreen 0.5s ease;
        }
        .input-wrap input.v-error {
            border-color: var(--red); box-shadow: 0 0 0 3px rgba(231,76,60,0.1);
        }
        @keyframes flashGreen { 0%,100%{} 50%{background:rgba(39,174,96,0.22);} }
        .input-wrap input::placeholder { color: rgba(122,136,153,0.55); }

        .field-mic {
            position: absolute; right: 0.55rem;
            background: none; border: none; cursor: pointer;
            width: 28px; height: 28px; border-radius: 7px;
            display: flex; align-items: center; justify-content: center;
            color: var(--muted); font-size: 0.82rem;
            transition: color 0.2s, background 0.2s;
        }
        .field-mic:hover { color: var(--accent); background: rgba(97,179,250,0.1); }
        .field-mic.active { color: var(--red); background: rgba(231,76,60,0.12); animation: blink 0.8s ease-in-out infinite; }
        @keyframes blink { 50%{opacity:0.4;} }

        .checkbox-wrap {
            display: flex; align-items: center; gap: 0.6rem;
            padding: 0.65rem 1rem; border: 1px solid var(--border); border-radius: 10px;
            background: rgba(0,0,0,0.2); cursor: pointer; transition: border-color 0.2s;
            user-select: none;
        }
        .checkbox-wrap:hover { border-color: var(--border-focus); }
        .checkbox-wrap input[type="checkbox"] { display: none; }
        .chk-box {
            width: 18px; height: 18px; border: 2px solid var(--muted); border-radius: 5px;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
            transition: background 0.2s, border-color 0.2s; font-size: 0.65rem; color: #fff;
        }
        .checkbox-wrap:has(input:checked) .chk-box { background: var(--accent2); border-color: var(--accent2); }

        .error-msg {
            display: none;
            font-size: 0.7rem;
            color: var(--red);
            margin-top: 0.35rem;
            padding-left: 0.5rem;
        }
        .error-msg.show { display: block; }

        .form-actions { display: flex; gap: 0.9rem; margin-top: 1.75rem; flex-wrap: wrap; }
        .btn-submit {
            background: linear-gradient(135deg, var(--accent2), var(--accent)); color: #fff;
            padding: 0.75rem 1.8rem; border-radius: 30px; border: none; cursor: pointer;
            font-family: 'Syne', sans-serif; font-weight: 600; font-size: 0.9rem;
            display: inline-flex; align-items: center; gap: 8px;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 4px 16px rgba(25,118,210,0.35); text-decoration: none;
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(25,118,210,0.45); }
        .btn-cancel {
            background: var(--surface); border: 1px solid var(--border); color: var(--muted);
            padding: 0.75rem 1.4rem; border-radius: 30px; text-decoration: none;
            font-size: 0.88rem; display: inline-flex; align-items: center; gap: 8px;
            transition: background 0.2s, color 0.2s;
        }
        .btn-cancel:hover { background: rgba(255,255,255,0.08); color: var(--text); }

        /* ═══ WIZARD VOCAL ═══ */
        .voice-wizard {
            background: rgba(25,118,210,0.06);
            border: 1px solid rgba(97,179,250,0.22);
            border-radius: 18px; padding: 1.5rem;
            margin-top: 1.5rem;
        }
        .wizard-top { margin-bottom: 1.2rem; }
        .wizard-title { font-family: 'Syne', sans-serif; font-weight: 700; font-size: 1rem; margin-bottom: 0.2rem; }
        .wizard-sub { font-size: 0.78rem; color: var(--muted); }

        .wizard-progress { margin-bottom: 1.2rem; }
        .pb-bg { height: 4px; background: rgba(255,255,255,0.08); border-radius: 99px; overflow: hidden; }
        .pb-fill { height: 100%; background: linear-gradient(90deg, var(--accent2), var(--accent)); border-radius: 99px; transition: width 0.4s ease; width: 0%; }
        .pb-label { font-size: 0.73rem; color: var(--muted); margin-top: 0.35rem; }

        .wiz-step-tag { font-size: 0.72rem; color: var(--accent); font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 0.3rem; }
        .wiz-question { font-family: 'Syne', sans-serif; font-size: 1.05rem; font-weight: 700; margin-bottom: 0.35rem; }
        .wiz-hint { font-size: 0.79rem; color: var(--muted); margin-bottom: 1.1rem; }

        .wiz-mic-wrap { display: flex; flex-direction: column; align-items: center; gap: 0.55rem; margin-bottom: 1.1rem; }
        .wiz-mic-btn {
            width: 72px; height: 72px; border-radius: 50%; border: none; cursor: pointer;
            background: var(--accent2); color: #fff; font-size: 1.5rem;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 20px rgba(25,118,210,0.4);
            transition: background 0.25s, transform 0.2s;
            position: relative;
        }
        .wiz-mic-btn::after {
            content:''; position:absolute; inset:-5px; border-radius:50%;
            border:2px solid rgba(97,179,250,0.2); pointer-events:none;
        }
        .wiz-mic-btn:hover { transform: scale(1.06); }
        .wiz-mic-btn.listening {
            background: var(--red);
            animation: wpulse 1s ease-in-out infinite;
        }
        .wiz-mic-btn.listening::after { animation: wring 1s ease-out infinite; }
        @keyframes wpulse { 50%{ box-shadow:0 0 0 14px rgba(231,76,60,0.1),0 4px 20px rgba(231,76,60,0.35);} }
        @keyframes wring  { 0%{inset:-5px;opacity:1} 100%{inset:-20px;opacity:0} }
        .wiz-mic-btn:disabled { opacity:0.4; cursor:default; transform:none; animation:none; }
        .wiz-mic-btn:disabled::after { display:none; }

        .wiz-mic-label { font-size: 0.8rem; color: var(--muted); text-align:center; }
        .wiz-mic-label.active { color: var(--red); font-weight:500; }

        .wiz-transcript {
            display:none; border:1px solid rgba(97,179,250,0.2); border-radius:10px;
            padding:0.65rem 0.9rem; font-size:0.84rem; color:var(--accent);
            background:rgba(97,179,250,0.05); line-height:1.5; margin-bottom:0.9rem;
        }
        .wiz-transcript.show { display:block; }
        .wiz-transcript.interim { color:var(--muted); font-style:italic; }

        .wiz-btns { display:flex; gap:0.6rem; flex-wrap:wrap; }
        .wbtn {
            padding:0.52rem 1.05rem; border-radius:20px; border:none; cursor:pointer;
            font-family:'DM Sans',sans-serif; font-size:0.82rem; font-weight:500;
            display:inline-flex; align-items:center; gap:6px;
            transition:transform 0.15s, opacity 0.15s;
        }
        .wbtn:hover{transform:translateY(-1px);}
        .wbtn-ok   { background:var(--green); color:#fff; }
        .wbtn-redo { background:rgba(255,255,255,0.08); color:var(--text); }
        .wbtn-skip { background:transparent; border:1px solid var(--border); color:var(--muted); }

        .wiz-done { display:none; text-align:center; padding:0.75rem 0; }
        .wiz-done .done-ico { font-size:2.2rem; margin-bottom:0.4rem; }
        .wiz-done h3 { font-family:'Syne',sans-serif; font-size:1rem; margin-bottom:0.3rem; }
        .wiz-done p  { font-size:0.8rem; color:var(--muted); }

        .nospeech { display:none; background:rgba(255,193,7,0.08); border:1px solid rgba(255,193,7,0.3); color:#ffe082; border-radius:10px; padding:0.75rem 1rem; font-size:0.82rem; margin-bottom:1rem; }
        .nospeech.show { display:block; }

        @media(max-width:768px){ .container{padding:1rem 1rem 3rem;} .form-card{padding:1.5rem;} .form-actions{flex-direction:column;} .btn-submit,.btn-cancel{justify-content:center;} }
    </style>
</head>
<body>

<?php require_once __DIR__ . '/includes/navbar_moderne.php'; ?>

<main class="container">

    <?php if (!empty($_SESSION['errors']) && is_array($_SESSION['errors'])): ?>
        <div class="alert alert-error">
            <strong><i class="fas fa-triangle-exclamation"></i> Erreur</strong>
            <ul><?php foreach ($_SESSION['errors'] as $err): ?><li><?= htmlspecialchars((string)$err) ?></li><?php endforeach; ?></ul>
        </div>
        <?php unset($_SESSION['errors']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <strong><i class="fas fa-check-circle"></i> <?= htmlspecialchars((string)$_SESSION['success']) ?></strong>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <div class="hero">
        <div>
            <h2><i class="fas fa-<?= isset($isEditMode) && $isEditMode ? 'edit' : 'plus' ?>"></i>
                <?= isset($isEditMode) && $isEditMode ? 'Modifier' : 'Ajouter' ?> un véhicule</h2>
            <p>Saisie manuelle ou guidée par la voix 🎙️</p>
        </div>
        <div class="hero-icon"><i class="fas fa-car"></i></div>
    </div>

    <div class="form-card">
        <form method="POST" action="mes_vehicules.php" enctype="multipart/form-data" id="vehicleForm">
            <input type="hidden" name="action" value="<?= isset($isEditMode) && $isEditMode ? 'update' : 'create' ?>">
            <?php if (isset($isEditMode) && $isEditMode && isset($vehicule)): ?>
                <input type="hidden" name="id" value="<?= $vehicule['id'] ?>">
            <?php endif; ?>

            <p class="section-title"><i class="fas fa-id-card"></i> Identification</p>
            <div class="field-row">
                <div class="form-group">
                    <label>Marque *</label>
                    <div class="input-wrap">
                        <input type="text" id="marque" name="marque"
                            value="<?= htmlspecialchars($vehicule['marque'] ?? ($_SESSION['old']['marque'] ?? '')) ?>"
                            placeholder="ex: Renault" required>
                        <button type="button" class="field-mic" data-field="marque"><i class="fas fa-microphone"></i></button>
                    </div>
                    <div class="error-msg" id="error-marque">La marque est obligatoire (2-50 caractères)</div>
                </div>
                <div class="form-group">
                    <label>Modèle *</label>
                    <div class="input-wrap">
                        <input type="text" id="modele" name="modele"
                            value="<?= htmlspecialchars($vehicule['modele'] ?? ($_SESSION['old']['modele'] ?? '')) ?>"
                            placeholder="ex: Clio" required>
                        <button type="button" class="field-mic" data-field="modele"><i class="fas fa-microphone"></i></button>
                    </div>
                    <div class="error-msg" id="error-modele">Le modèle est obligatoire (2-50 caractères)</div>
                </div>
            </div>
            <div class="field-row">
                <div class="form-group">
                    <label>Immatriculation *</label>
                    <div class="input-wrap">
                        <input type="text" id="immatriculation" name="immatriculation"
                            value="<?= htmlspecialchars($vehicule['immatriculation'] ?? ($_SESSION['old']['immatriculation'] ?? '')) ?>"
                            placeholder="ex: AB-123-CD" required>
                        <button type="button" class="field-mic" data-field="immatriculation"><i class="fas fa-microphone"></i></button>
                    </div>
                    <div class="error-msg" id="error-immatriculation">Format invalide (ex: AB-123-CD)</div>
                </div>
                <div class="form-group">
                    <label>Couleur</label>
                    <div class="input-wrap">
                        <input type="text" id="couleur" name="couleur"
                            value="<?= htmlspecialchars($vehicule['couleur'] ?? ($_SESSION['old']['couleur'] ?? '')) ?>"
                            placeholder="ex: Rouge">
                        <button type="button" class="field-mic" data-field="couleur"><i class="fas fa-microphone"></i></button>
                    </div>
                    <div class="error-msg" id="error-couleur"></div>
                </div>
            </div>

            <p class="section-title"><i class="fas fa-sliders"></i> Détails</p>
            <div class="field-row">
                <div class="form-group">
                    <label>Nombre de places (1–9) *</label>
                    <div class="input-wrap">
                        <input type="number" id="capacite" name="capacite" min="1" max="9"
                            value="<?= htmlspecialchars((string)($vehicule['capacite'] ?? ($_SESSION['old']['capacite'] ?? '4'))) ?>"
                            required>
                        <button type="button" class="field-mic" data-field="capacite"><i class="fas fa-microphone"></i></button>
                    </div>
                    <div class="error-msg" id="error-capacite">Le nombre de places doit être entre 1 et 9</div>
                </div>
                <div class="form-group">
                    <label>Statut</label>
                    <div class="input-wrap">
                        <select id="statut" name="statut">
                            <option value="disponible"     <?= (($vehicule['statut'] ?? $_SESSION['old']['statut'] ?? '') == 'disponible')     ? 'selected':'' ?>>✅ Disponible</option>
                            <option value="indisponible"   <?= (($vehicule['statut'] ?? $_SESSION['old']['statut'] ?? '') == 'indisponible')   ? 'selected':'' ?>>⛔ Indisponible</option>
                            <option value="en_maintenance" <?= (($vehicule['statut'] ?? $_SESSION['old']['statut'] ?? '') == 'en_maintenance') ? 'selected':'' ?>>🔧 En maintenance</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Trajet associé</label>
                <div class="input-wrap">
                    <select id="trajet_id" name="trajet_id">
                        <option value="">-- Aucun trajet associé --</option>
                        <?php foreach (($mesTrajets ?? []) as $trajet): ?>
                            <option value="<?= (int)$trajet['id_T'] ?>"
                                <?= ((int)($vehicule['trajet_id'] ?? ($_SESSION['old']['trajet_id'] ?? 0)) === (int)$trajet['id_T']) ? 'selected':'' ?>>
                                <?= htmlspecialchars(($trajet['point_depart'] ?? '?') . ' → ' . ($trajet['point_arrive'] ?? '?')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="checkbox-wrap" for="climatisation">
                    <input type="checkbox" id="climatisation" name="climatisation" value="1"
                        <?= ((isset($vehicule['climatisation']) && $vehicule['climatisation']) || !empty($_SESSION['old']['climatisation'])) ? 'checked':'' ?>>
                    <span class="chk-box"><i class="fas fa-check"></i></span>
                    <span><i class="fas fa-snowflake" style="color:var(--accent);margin-right:6px"></i> Climatisation incluse</span>
                </label>
            </div>

            <p class="section-title"><i class="fas fa-camera"></i> Photo</p>
            <div class="form-group">
                <label>Fichier image (jpg, jpeg, png)</label>
                <div class="input-wrap">
                    <input type="file" id="photo" name="photo" accept="image/*" style="padding:0.6rem 0.9rem;">
                </div>
                <?php if (!empty($vehicule['photo'])): ?>
                    <small style="color:var(--muted);display:block;margin-top:0.4rem">
                        <i class="fas fa-image"></i> Photo actuelle : <?= htmlspecialchars($vehicule['photo']) ?>
                    </small>
                <?php endif; ?>
            </div>

            <!-- ═══ WIZARD VOCAL ═══ -->
            <div class="voice-wizard" id="voiceWizard">
                <div class="wizard-top">
                    <div class="wizard-title">🎙️ Assistant vocal — remplissage guidé</div>
                    <div class="wizard-sub">L'assistant vous pose une question à la fois. Parlez, confirmez, passez au suivant.</div>
                </div>

                <div class="wizard-progress">
                    <div class="pb-bg"><div class="pb-fill" id="pbFill"></div></div>
                    <div class="pb-label" id="pbLabel">Prêt — cliquez sur le micro pour commencer</div>
                </div>

                <div class="wiz-step-tag" id="wizTag">—</div>
                <div class="wiz-question" id="wizQ">Cliquez sur le microphone pour démarrer</div>
                <div class="wiz-hint" id="wizH"></div>

                <div class="wiz-mic-wrap">
                    <button type="button" class="wiz-mic-btn" id="wizMic">
                        <i class="fas fa-microphone" id="wizMicIco"></i>
                    </button>
                    <div class="wiz-mic-label" id="wizMicLbl">Appuyer pour démarrer</div>
                </div>

                <div class="wiz-transcript" id="wizTr"></div>

                <div class="wiz-btns" id="wizBtns" style="display:none">
                    <button type="button" class="wbtn wbtn-ok" id="wizOk"><i class="fas fa-check"></i> Confirmer</button>
                    <button type="button" class="wbtn wbtn-redo" id="wizRedo"><i class="fas fa-rotate-left"></i> Réessayer</button>
                    <button type="button" class="wbtn wbtn-skip" id="wizSkip"><i class="fas fa-forward"></i> Passer ce champ</button>
                </div>

                <div class="wiz-done" id="wizDone">
                    <div class="done-ico">✅</div>
                    <h3>Formulaire rempli par la voix !</h3>
                    <p>Vérifiez les champs ci-dessus, corrigez si besoin, puis cliquez sur Enregistrer.</p>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit" id="submitBtn">
                    <i class="fas fa-save"></i>
                    <?= isset($isEditMode) && $isEditMode ? 'Mettre à jour' : 'Enregistrer' ?>
                </button>
                <a href="mes_vehicules.php" class="btn-cancel">
                    <i class="fas fa-times"></i> Annuler
                </a>
            </div>
        </form>
    </div>
</main>

<script>
(function() {
    'use strict';

    // ═══════════════════════════════════════════════════════════
    // PARTIE 1 — VALIDATION EN TEMPS RÉEL
    // ═══════════════════════════════════════════════════════════

    function validateMarque(value) {
        if (!value || value.trim().length < 2 || value.trim().length > 50) {
            return { valid: false, message: 'La marque est obligatoire (2-50 caractères)' };
        }
        return { valid: true, message: '' };
    }

    function validateModele(value) {
        if (!value || value.trim().length < 2 || value.trim().length > 50) {
            return { valid: false, message: 'Le modèle est obligatoire (2-50 caractères)' };
        }
        return { valid: true, message: '' };
    }

    function validateImmatriculation(value) {
        var pattern = /^[A-Z]{2}-\d{3}-[A-Z]{2}$/;
        var upperVal = value.toUpperCase().trim();
        if (!pattern.test(upperVal)) {
            return { valid: false, message: 'Format invalide (ex: AB-123-CD)' };
        }
        return { valid: true, message: '' };
    }

    function validateCouleur(value) {
        if (value && value.trim().length > 30) {
            return { valid: false, message: 'La couleur ne doit pas dépasser 30 caractères' };
        }
        return { valid: true, message: '' };
    }

    function validateCapacite(value) {
        var num = parseInt(value, 10);
        if (isNaN(num) || num < 1 || num > 9) {
            return { valid: false, message: 'Le nombre de places doit être entre 1 et 9' };
        }
        return { valid: true, message: '' };
    }

    var validators = {
        marque: validateMarque,
        modele: validateModele,
        immatriculation: validateImmatriculation,
        couleur: validateCouleur,
        capacite: validateCapacite
    };

    var fields = {
        marque: document.getElementById('marque'),
        modele: document.getElementById('modele'),
        immatriculation: document.getElementById('immatriculation'),
        couleur: document.getElementById('couleur'),
        capacite: document.getElementById('capacite')
    };

    var errorMsgs = {
        marque: document.getElementById('error-marque'),
        modele: document.getElementById('error-modele'),
        immatriculation: document.getElementById('error-immatriculation'),
        couleur: document.getElementById('error-couleur'),
        capacite: document.getElementById('error-capacite')
    };

    var submitBtn = document.getElementById('submitBtn');

    function validateField(fieldName) {
        var input = fields[fieldName];
        var validator = validators[fieldName];
        var errorDiv = errorMsgs[fieldName];
        
        if (!input || !validator || !errorDiv) return true;
        
        var result = validator(input.value);
        
        if (!result.valid) {
            input.classList.add('v-error');
            input.classList.remove('v-ok');
            errorDiv.textContent = result.message;
            errorDiv.classList.add('show');
            return false;
        } else {
            input.classList.remove('v-error');
            input.classList.add('v-ok');
            errorDiv.classList.remove('show');
            return true;
        }
    }

    function formatImmatriculation(input) {
        var value = input.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        var formatted = '';
        
        if (value.length > 0) {
            formatted += value.substring(0, 2);
        }
        if (value.length > 2) {
            formatted += '-' + value.substring(2, 5);
        }
        if (value.length > 5) {
            formatted += '-' + value.substring(5, 7);
        }
        
        if (formatted !== input.value.toUpperCase()) {
            input.value = formatted;
        }
    }

    function initValidation() {
        for (var fieldName in fields) {
            var input = fields[fieldName];
            if (input) {
                input.addEventListener('input', (function(fn) {
                    return function() { validateField(fn); };
                })(fieldName));
                
                input.addEventListener('blur', (function(fn) {
                    return function() { validateField(fn); };
                })(fieldName));
            }
        }
        
        if (fields.immatriculation) {
            fields.immatriculation.addEventListener('input', function() {
                formatImmatriculation(this);
            });
        }
        
        var form = document.getElementById('vehicleForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                var isValid = true;
                for (var fieldName in validators) {
                    if (!validateField(fieldName)) {
                        isValid = false;
                    }
                }
                
                if (!isValid) {
                    e.preventDefault();
                    var firstError = document.querySelector('.v-error');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        firstError.focus();
                    }
                    showToast('Veuillez corriger les erreurs dans le formulaire', false);
                }
            });
        }
        
        // Valider tous les champs au chargement
        for (var fieldName in validators) {
            validateField(fieldName);
        }
        
        // Bouton toujours actif
        if (submitBtn) {
            submitBtn.disabled = false;
        }
    }

    function showToast(msg, success) {
        var toast = document.createElement('div');
        toast.className = 'toast ' + (success ? 'success' : 'error');
        toast.innerHTML = '<i class="fas fa-' + (success ? 'check-circle' : 'exclamation-circle') + '"></i> ' + msg;
        toast.style.cssText = 'position:fixed;bottom:20px;right:20px;padding:12px 20px;border-radius:10px;z-index:9999;background:' + (success ? '#27ae60' : '#e74c3c') + ';color:#fff;';
        document.body.appendChild(toast);
        setTimeout(function() { toast.remove(); }, 3000);
    }

    window.validateField = validateField;

    // ═══════════════════════════════════════════════════════════
    // PARTIE 2 — ASSISTANT VOCAL
    // ═══════════════════════════════════════════════════════════

    var SR = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (!SR) {
        document.getElementById('voiceW