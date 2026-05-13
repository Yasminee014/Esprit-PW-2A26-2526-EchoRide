/**
 * Validation JS - Frontoffice : Profil utilisateur
 * Fichier : views/frontoffice/js/profile.validation.js
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

    function clearErrors() {
        document.querySelectorAll('.error-msg-js').forEach(el => el.remove());
        document.querySelectorAll('.error-field').forEach(el => el.classList.remove('error-field'));
    }

    // ── Validation : Mise à jour du profil ───────────────────────────────────

    const profileForm = document.querySelector('form[action*="updateProfile"]');
    if (profileForm) {
        profileForm.addEventListener('submit', function (e) {
            clearErrors();
            let valid = true;

            const prenom    = this.querySelector('input[name="prenom"]');
            const nom       = this.querySelector('input[name="nom"]');
            const email     = this.querySelector('input[name="email"]');
            const telephone = this.querySelector('input[name="telephone"]');

            // Prénom
            if (!prenom || prenom.value.trim().length < 2) {
                showError(prenom, 'Prénom invalide (min 2 caractères)');
                valid = false;
            }

            // Nom
            if (!nom || nom.value.trim().length < 2) {
                showError(nom, 'Nom invalide (min 2 caractères)');
                valid = false;
            }

            // Email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!email || !emailRegex.test(email.value)) {
                showError(email, 'Email invalide');
                valid = false;
            }

            // Téléphone (8 à 15 chiffres)
            const phoneRegex = /^[0-9]{8,15}$/;
            if (!telephone || !phoneRegex.test(telephone.value.trim())) {
                showError(telephone, 'Téléphone invalide (8-15 chiffres)');
                valid = false;
            }

            if (!valid) e.preventDefault();
        });
    }

    // ── Validation : Changement de mot de passe ───────────────────────────────

    const passwordForm = document.querySelector('form[action*="changePassword"]');
    if (passwordForm) {
        passwordForm.addEventListener('submit', function (e) {
            clearErrors();
            let valid = true;

            const current = this.querySelector('input[name="current_password"]');
            const newPass = this.querySelector('input[name="new_password"]');
            const confirm = this.querySelector('input[name="confirm_password"]');

            // Mot de passe actuel requis
            if (!current || current.value.trim() === '') {
                showError(current, 'Mot de passe actuel requis');
                valid = false;
            }

            // Nouveau mot de passe : min 8 car., 1 maj., 1 chiffre
            const passwordRegex = /^(?=.*[A-Z])(?=.*\d).{8,}$/;
            if (!newPass || !passwordRegex.test(newPass.value)) {
                showError(newPass, 'Min 8 caractères, 1 majuscule, 1 chiffre');
                valid = false;
            }

            // Confirmation
            if (!confirm || newPass.value !== confirm.value) {
                showError(confirm, 'Les mots de passe ne correspondent pas');
                valid = false;
            }

            if (!valid) e.preventDefault();
        });
    }
});
