/**
 * Validation JS - Backoffice : Connexion Admin
 * Fichier : views/backoffice/js/admin_login.validation.js
 */

document.addEventListener('DOMContentLoaded', function () {

    // ── Utilitaires ──────────────────────────────────────────────────────────

    function showError(input, message) {
        input.classList.add('error-field');
        let span = input.parentNode.querySelector('.error-msg-js');
        if (!span) {
            span = document.createElement('span');
            span.className = 'error-msg error-msg-js';
            input.parentNode.appendChild(span);
        }
        span.innerHTML = '<i class="fas fa-times-circle"></i> ' + message;
        span.style.display = 'block';
    }

    function clearErrors(form) {
        form.querySelectorAll('.error-msg-js').forEach(el => el.remove());
        form.querySelectorAll('.error-field').forEach(el => el.classList.remove('error-field'));
    }

    function validateEmail(value) {
        if (!value || value.trim() === '') {
            return { valid: false, message: "L'email est obligatoire." };
        }
        if (!value.includes('@')) {
            return { valid: false, message: 'Veuillez saisir une adresse email valide contenant "@".' };
        }
        const parts = value.split('@');
        if (parts.length !== 2 || parts[0].length === 0) {
            return { valid: false, message: "Format d'email invalide." };
        }
        if (parts[1].length === 0) {
            return { valid: false, message: 'La partie après "@" est manquante.' };
        }
        if (!parts[1].includes('.') || parts[1].endsWith('.')) {
            return { valid: false, message: 'Le domaine doit contenir un point valide (ex: gmail.com).' };
        }
        return { valid: true, message: '' };
    }

    // ── Formulaire connexion admin ────────────────────────────────────────────

    const form = document.querySelector('form[action*="action=login"]');
    if (!form) return;

    const emailInput    = form.querySelector('input[name="email"]');
    const passwordInput = form.querySelector('input[name="password"]');

    // Validation en temps réel
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
    form.addEventListener('submit', function (e) {
        clearErrors(this);
        let valid = true;

        // Email
        const emailResult = validateEmail(emailInput ? emailInput.value : '');
        if (!emailResult.valid) {
            showError(emailInput, emailResult.message);
            valid = false;
        }

        // Mot de passe
        if (!passwordInput || passwordInput.value.trim() === '') {
            showError(passwordInput, 'Le mot de passe est obligatoire.');
            valid = false;
        }

        if (!valid) {
            e.preventDefault();
        }
    });
});
