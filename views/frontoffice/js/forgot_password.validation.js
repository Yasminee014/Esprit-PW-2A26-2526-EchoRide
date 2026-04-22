/**
 * Validation JS - Frontoffice : Mot de passe oublié (logique multi-étapes + validation)
 * Fichier : views/frontoffice/js/forgot_password.validation.js
 * VERSION CORRIGÉE : sendResetCode retourne du JSON, le code est vérifié côté serveur
 */

(function () {

    let currentStep = 1;
    let userEmail   = '';
    let userCode    = '';

    // ── Éléments DOM ─────────────────────────────────────────────────────────

    const step1    = document.getElementById('step1');
    const step2    = document.getElementById('step2');
    const step3    = document.getElementById('step3');
    const step1Dot = document.getElementById('step1Dot');
    const step2Dot = document.getElementById('step2Dot');
    const step3Dot = document.getElementById('step3Dot');

    // ── Utilitaires ───────────────────────────────────────────────────────────

    function showAlert(message, type) {
        const container = document.getElementById('alertContainer');
        if (!container) return;
        // Effacer les alertes précédentes du même type
        container.querySelectorAll('.alert-' + type).forEach(el => el.remove());
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-' + type;
        alertDiv.innerHTML = '<i class="fas ' + (type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle') + '"></i> ' + message;
        container.appendChild(alertDiv);
        setTimeout(() => alertDiv.remove(), 6000);
    }

    function clearAlerts() {
        const container = document.getElementById('alertContainer');
        if (container) container.innerHTML = '';
    }

    function goToStep(step) {
        [step1, step2, step3].forEach(s => s && s.classList.add('hidden'));
        [step1Dot, step2Dot, step3Dot].forEach(d => d && d.classList.remove('active', 'completed'));

        if (step === 1 && step1) step1.classList.remove('hidden');
        if (step === 2 && step2) step2.classList.remove('hidden');
        if (step === 3 && step3) step3.classList.remove('hidden');

        if (step >= 1 && step1Dot) step1Dot.classList.add('completed');
        if (step >= 2 && step2Dot) step2Dot.classList.add('completed');
        if (step >= 3 && step3Dot) step3Dot.classList.add('completed');

        if (step === 1 && step1Dot) step1Dot.classList.add('active');
        if (step === 2 && step2Dot) step2Dot.classList.add('active');
        if (step === 3 && step3Dot) step3Dot.classList.add('active');

        currentStep = step;
        clearAlerts();
    }

    function getSendResetUrl() {
        return (window.FP_CONFIG && window.FP_CONFIG.sendResetUrl) || '';
    }

    function getVerifyUrl() {
        return (window.FP_CONFIG && window.FP_CONFIG.verifyCodeUrl) || '';
    }

    // ── Étape 1 : Envoi du code ───────────────────────────────────────────────

    const sendCodeBtn = document.getElementById('sendCodeBtn');
    if (sendCodeBtn) {
        sendCodeBtn.addEventListener('click', function () {
            const email = document.getElementById('resetEmail').value.trim();

            if (!email) {
                showAlert('Veuillez entrer votre email', 'error');
                return;
            }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                showAlert('Format d\'email invalide', 'error');
                return;
            }

            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi en cours...';

            fetch(getSendResetUrl(), {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'email=' + encodeURIComponent(email)
            })
            .then(response => response.json())
            .then(data => {
                sendCodeBtn.disabled = false;
                sendCodeBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Envoyer le code';

                if (data.success) {
                    userEmail = email;
                    goToStep(2);
                    showAlert('Code envoyé ! Vérifiez votre boîte email.', 'success');
                    // Mode dev : afficher le code directement
                    if (data.dev_code) {
                        showAlert('🔧 Code de dev : ' + data.dev_code, 'info');
                    }
                } else {
                    showAlert(data.message || "Erreur lors de l'envoi", 'error');
                }
            })
            .catch(() => {
                showAlert("Erreur de connexion. Réessayez.", 'error');
                sendCodeBtn.disabled = false;
                sendCodeBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Envoyer le code';
            });
        });
    }

    // ── Étape 2 : Vérification du code (côté client, le vrai check est côté serveur) ──

    const verifyCodeBtn = document.getElementById('verifyCodeBtn');
    if (verifyCodeBtn) {
        verifyCodeBtn.addEventListener('click', function () {
            const code = document.getElementById('verificationCode').value.trim();

            if (!code || code.length < 6) {
                showAlert('Veuillez entrer un code valide à 6 chiffres', 'error');
                return;
            }

            userCode = code;

            // Remplir les champs hidden AVANT de passer à l'étape 3
            const finalEmail = document.getElementById('finalEmail');
            const finalCode  = document.getElementById('finalCode');
            if (finalEmail) finalEmail.value = userEmail;
            if (finalCode)  finalCode.value  = userCode;

            goToStep(3);
            showAlert('Code accepté. Entrez votre nouveau mot de passe.', 'success');
        });
    }

    // ── Renvoi du code ────────────────────────────────────────────────────────

    const resendCodeLink = document.getElementById('resendCodeLink');
    if (resendCodeLink) {
        resendCodeLink.addEventListener('click', function (e) {
            e.preventDefault();
            if (!userEmail) {
                showAlert('Email introuvable. Recommencez depuis le début.', 'error');
                return;
            }
            fetch(getSendResetUrl(), {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'email=' + encodeURIComponent(userEmail)
            })
            .then(r => r.json())
            .then(data => {
                showAlert('Un nouveau code a été envoyé !', 'success');
                if (data.dev_code) showAlert('🔧 Code de dev : ' + data.dev_code, 'info');
            })
            .catch(() => showAlert('Erreur lors du renvoi', 'error'));
        });
    }

    // ── Étape 3 : Validation du nouveau mot de passe ──────────────────────────

    const passwordForm = document.getElementById('passwordForm');
    if (passwordForm) {
        passwordForm.addEventListener('submit', function (e) {
            const newPassword     = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            // Vérifier que les champs hidden sont bien remplis
            const finalEmail = document.getElementById('finalEmail');
            const finalCode  = document.getElementById('finalCode');
            if (!finalEmail || !finalEmail.value) {
                e.preventDefault();
                showAlert('Session perdue. Veuillez recommencer depuis le début.', 'error');
                goToStep(1);
                return;
            }

            if (newPassword.length < 8) {
                e.preventDefault();
                showAlert('Le mot de passe doit contenir au moins 8 caractères', 'error');
                return;
            }
            if (!/[A-Z]/.test(newPassword)) {
                e.preventDefault();
                showAlert('Le mot de passe doit contenir au moins une majuscule', 'error');
                return;
            }
            if (!/[0-9]/.test(newPassword)) {
                e.preventDefault();
                showAlert('Le mot de passe doit contenir au moins un chiffre', 'error');
                return;
            }
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                showAlert('Les mots de passe ne correspondent pas', 'error');
                return;
            }
            // Tout est OK → laisser le formulaire se soumettre normalement
        });
    }

    // ── Init depuis session PHP (via FP_CONFIG) ───────────────────────────────

    if (window.FP_CONFIG) {
        if (window.FP_CONFIG.resetEmail) {
            userEmail = window.FP_CONFIG.resetEmail;
            goToStep(2);
        }
        if (window.FP_CONFIG.devCode) {
            showAlert('🔧 Code de développement : ' + window.FP_CONFIG.devCode, 'info');
        }
    }

})();
