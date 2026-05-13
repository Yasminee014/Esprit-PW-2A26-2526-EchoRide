/**
 * Validation JS - Frontoffice : Inscription (formulaire simple)
 * Fichier : views/frontoffice/js/register.validation.js
 */

document.addEventListener('DOMContentLoaded', function () {

    // ── Utilitaires ──────────────────────────────────────────────────────────

    function showError(input, message) {
        input.classList.add('error-field');
        let span = input.parentNode.querySelector('.error-msg-js');
        if (!span) {
            span = document.createElement('span');
            span.className = 'error-msg-js';
            span.style.cssText = 'color:#ff6b6b;font-size:0.85rem;display:block;margin-top:4px;';
            input.parentNode.appendChild(span);
        }
        span.textContent = message;
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

    // ── Formulaire inscription ────────────────────────────────────────────────

    const registerForm = document.getElementById('registerForm');
    if (!registerForm) return;

    const firstnameInput = registerForm.querySelector('[name="firstname"]');
    const lastnameInput  = registerForm.querySelector('[name="lastname"]');
    const emailInput     = registerForm.querySelector('[name="email"]');
    const passwordInput  = registerForm.querySelector('[name="password"]');
    const phoneInput     = registerForm.querySelector('[name="phone"]');

    registerForm.addEventListener('submit', function (e) {
        clearErrors(this);
        let valid = true;

        // Prénom
        if (!firstnameInput || firstnameInput.value.trim().length < 2) {
            showError(firstnameInput, 'Le prénom doit contenir au moins 2 caractères.');
            valid = false;
        }

        // Nom
        if (!lastnameInput || lastnameInput.value.trim().length < 2) {
            showError(lastnameInput, 'Le nom doit contenir au moins 2 caractères.');
            valid = false;
        }

        // Email
        const emailResult = validateEmail(emailInput ? emailInput.value : '');
        if (!emailResult.valid) {
            showError(emailInput, emailResult.message);
            valid = false;
        }

        // Mot de passe : min 8 car., 1 maj., 1 chiffre
        if (!passwordInput || passwordInput.value.length < 8) {
            showError(passwordInput, 'Le mot de passe doit contenir au moins 8 caractères.');
            valid = false;
        } else if (!/[A-Z]/.test(passwordInput.value)) {
            showError(passwordInput, 'Le mot de passe doit contenir au moins une majuscule.');
            valid = false;
        } else if (!/[0-9]/.test(passwordInput.value)) {
            showError(passwordInput, 'Le mot de passe doit contenir au moins un chiffre.');
            valid = false;
        }

        // Téléphone (si rempli : 8–15 chiffres)
        if (phoneInput && phoneInput.value.trim() !== '') {
            const phoneRegex = /^[0-9]{8,15}$/;
            if (!phoneRegex.test(phoneInput.value.trim())) {
                showError(phoneInput, 'Le numéro de téléphone doit contenir entre 8 et 15 chiffres.');
                valid = false;
            }
        }

        if (!valid) e.preventDefault();
    });
});
