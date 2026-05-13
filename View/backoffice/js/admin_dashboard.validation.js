/**
 * Validation JS - Backoffice : Dashboard Admin (formulaire modal passager)
 * Fichier : views/backoffice/js/admin_dashboard.validation.js
 */

document.addEventListener('DOMContentLoaded', function () {

    // ── Utilitaires ──────────────────────────────────────────────────────────

    function showError(input, message) {
        input.classList.add('error-field');
        let span = input.parentNode.querySelector('.error-msg-js');
        if (!span) {
            span = document.createElement('span');
            span.className = 'error-msg error-msg-js';
            span.style.cssText = 'color:#ff6b6b;font-size:0.8rem;margin-top:4px;display:block;';
            input.parentNode.appendChild(span);
        }
        span.innerHTML = '<i class="fas fa-times-circle"></i> ' + message;
    }

    function clearErrors(form) {
        form.querySelectorAll('.error-msg-js').forEach(el => el.remove());
        form.querySelectorAll('.error-field').forEach(el => el.classList.remove('error-field'));
    }

    function validateEmail(value) {
        if (!value || value.trim() === '') return { valid: false, message: "L'email est obligatoire." };
        if (!value.includes('@'))             return { valid: false, message: 'Adresse email invalide : "@" manquant.' };
        const parts = value.split('@');
        if (parts.length !== 2 || parts[0].length === 0) return { valid: false, message: "Format d'email invalide." };
        if (!parts[1].includes('.') || parts[1].endsWith('.')) return { valid: false, message: 'Le domaine doit contenir un point valide (ex: gmail.com).' };
        return { valid: true, message: '' };
    }

    // ── Formulaire modal passager ─────────────────────────────────────────────

    const passagerForm = document.getElementById('passagerForm');
    if (!passagerForm) return;

    const emailInput = document.getElementById('email');
    const prenomInput = document.getElementById('prenom');
    const nomInput    = document.getElementById('nom');
    const telInput    = document.getElementById('telephone');

    // Validation en temps réel sur l'email
    if (emailInput) {
        emailInput.addEventListener('input', function () {
            const result = validateEmail(this.value);
            if (!result.valid && this.value.length > 0) {
                showError(this, result.message);
            } else {
                const span = this.parentNode.querySelector('.error-msg-js');
                if (span) span.remove();
                this.classList.remove('error-field');
            }
        });
    }

    // Validation à la soumission
    passagerForm.addEventListener('submit', function (e) {
        clearErrors(this);
        let valid = true;

        // Prénom
        if (!prenomInput || prenomInput.value.trim().length < 2) {
            showError(prenomInput, 'Le prénom doit contenir au moins 2 caractères.');
            valid = false;
        }

        // Nom
        if (!nomInput || nomInput.value.trim().length < 2) {
            showError(nomInput, 'Le nom doit contenir au moins 2 caractères.');
            valid = false;
        }

        // Email
        const emailResult = validateEmail(emailInput ? emailInput.value : '');
        if (!emailResult.valid) {
            showError(emailInput, emailResult.message);
            valid = false;
        }

        // Téléphone (optionnel, mais si rempli : 8–15 chiffres)
        if (telInput && telInput.value.trim() !== '') {
            const phoneRegex = /^[0-9]{8,15}$/;
            if (!phoneRegex.test(telInput.value.trim())) {
                showError(telInput, 'Le numéro de téléphone doit contenir entre 8 et 15 chiffres.');
                valid = false;
            }
        }

        if (!valid) e.preventDefault();
    });
});
