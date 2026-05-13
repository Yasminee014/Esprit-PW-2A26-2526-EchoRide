/**
 * Validation JS - Backoffice : Modifier un utilisateur
 * Fichier : views/backoffice/js/edit_user.validation.js
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
        if (!value || value.trim() === '') return { valid: false, message: "L'email est obligatoire." };
        if (!value.includes('@')) return { valid: false, message: 'Adresse email invalide : "@" manquant.' };
        const parts = value.split('@');
        if (parts.length !== 2 || parts[0].length === 0) return { valid: false, message: "Format d'email invalide." };
        if (!parts[1].includes('.') || parts[1].endsWith('.')) return { valid: false, message: 'Le domaine doit contenir un point valide (ex: gmail.com).' };
        return { valid: true, message: '' };
    }

    function validatePassword(value) {
        if (!value || value.length < 8) return { valid: false, message: 'Le mot de passe doit contenir au moins 8 caractères.' };
        if (!/[A-Z]/.test(value))        return { valid: false, message: 'Le mot de passe doit contenir au moins une majuscule.' };
        if (!/[0-9]/.test(value))        return { valid: false, message: 'Le mot de passe doit contenir au moins un chiffre.' };
        return { valid: true, message: '' };
    }

    // ── Formulaire édition utilisateur ───────────────────────────────────────

    const form = document.querySelector('form[action*="action=editUser"]');
    if (!form) return;

    const prenomInput  = form.querySelector('input[name="prenom"]');
    const nomInput     = form.querySelector('input[name="nom"]');
    const emailInput   = form.querySelector('input[name="email"]');
    const telInput     = form.querySelector('input[name="telephone"]');
    const pwdInput     = form.querySelector('input[name="password"]');
    const confirmInput = form.querySelector('input[name="confirm_password"]');

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
    form.addEventListener('submit', function (e) {
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

        // Téléphone (8 à 15 chiffres)
        const phoneRegex = /^[0-9]{8,15}$/;
        if (!telInput || !phoneRegex.test(telInput.value.trim())) {
            showError(telInput, 'Le numéro de téléphone doit contenir entre 8 et 15 chiffres.');
            valid = false;
        }

        // Mot de passe (optionnel en édition : valider seulement si rempli)
        if (pwdInput && pwdInput.value.trim() !== '') {
            const pwdResult = validatePassword(pwdInput.value);
            if (!pwdResult.valid) {
                showError(pwdInput, pwdResult.message);
                valid = false;
            }
            if (!confirmInput || confirmInput.value !== pwdInput.value) {
                showError(confirmInput, 'Les mots de passe ne correspondent pas.');
                valid = false;
            }
        }

        if (!valid) {
            e.preventDefault();
        }
    });
});
