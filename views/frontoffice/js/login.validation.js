/**
 * Validation JS - Frontoffice : Connexion & Inscription
 * Fichier : views/frontoffice/js/login.validation.js
 */

// ── Smooth scroll ─────────────────────────────────────────────────────────────
document.querySelectorAll('a[href="#features"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector('#features');
        if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
});

// ── Utilitaires ───────────────────────────────────────────────────────────────

function validateEmail(email) {
    if (!email || email.trim() === '') {
        return { valid: false, message: "L'email est obligatoire." };
    }
    if (!email.includes('@')) {
        return { valid: false, message: 'Veuillez saisir une adresse email valide contenant "@".' };
    }
    const parts = email.split('@');
    if (parts.length !== 2) {
        return { valid: false, message: "Format d'email invalide." };
    }
    const localPart  = parts[0];
    const domainPart = parts[1];
    if (localPart.length === 0) {
        return { valid: false, message: 'La partie avant "@" ne peut pas être vide.' };
    }
    if (domainPart.length === 0) {
        return { valid: false, message: 'Veuillez saisir la partie manquante après le symbole "@". L\'adresse "' + email + '" est incomplète.' };
    }
    if (!domainPart.includes('.')) {
        return { valid: false, message: 'Le domaine doit contenir un point (ex: gmail.com).' };
    }
    if (domainPart.endsWith('.')) {
        return { valid: false, message: 'Le domaine ne peut pas se terminer par un point.' };
    }
    return { valid: true, message: '' };
}

function setupEmailValidation(emailInput) {
    if (!emailInput) return null;

    let errorSpan = emailInput.parentNode.querySelector('.email-error-msg');
    if (!errorSpan) {
        errorSpan = document.createElement('span');
        errorSpan.className = 'error-msg email-error-msg';
        errorSpan.style.display = 'none';
        errorSpan.style.marginTop = '4px';
        emailInput.parentNode.appendChild(errorSpan);
    }

    emailInput.addEventListener('input', function () {
        const result = validateEmail(this.value);
        if (!result.valid && this.value.length > 0) {
            errorSpan.innerHTML = '<i class="fas fa-times-circle"></i> ' + result.message;
            errorSpan.style.display = 'block';
            this.classList.add('error-field');
        } else {
            errorSpan.style.display = 'none';
            this.classList.remove('error-field');
        }
    });

    emailInput.addEventListener('blur', function () {
        const result = validateEmail(this.value);
        if (!result.valid && this.value.length > 0) {
            errorSpan.innerHTML = '<i class="fas fa-times-circle"></i> ' + result.message;
            errorSpan.style.display = 'block';
            this.classList.add('error-field');
        }
    });

    return errorSpan;
}

// ── Initialisation ────────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', function () {

    // Formulaire d'inscription
    const registerEmail = document.getElementById('registerEmail');
    let registerErrorSpan = null;

    if (registerEmail) {
        registerErrorSpan = setupEmailValidation(registerEmail);

        const registerForm = document.getElementById('registerFormElement');
        if (registerForm) {
            registerForm.addEventListener('submit', function (e) {
                const result = validateEmail(registerEmail.value);
                if (!result.valid) {
                    e.preventDefault();
                    if (registerErrorSpan) {
                        registerErrorSpan.innerHTML = '<i class="fas fa-times-circle"></i> ' + result.message;
                        registerErrorSpan.style.display = 'block';
                    }
                    registerEmail.classList.add('error-field');
                    registerEmail.focus();
                }
            });
        }
    }

    // Formulaire de connexion
    const loginEmail = document.getElementById('loginEmail');
    let loginErrorSpan = null;

    if (loginEmail) {
        loginErrorSpan = setupEmailValidation(loginEmail);

        const loginForm = document.getElementById('loginFormElement');
        if (loginForm) {
            loginForm.addEventListener('submit', function (e) {
                const result = validateEmail(loginEmail.value);
                if (!result.valid) {
                    e.preventDefault();
                    if (loginErrorSpan) {
                        loginErrorSpan.innerHTML = '<i class="fas fa-times-circle"></i> ' + result.message;
                        loginErrorSpan.style.display = 'block';
                    }
                    loginEmail.classList.add('error-field');
                    loginEmail.focus();
                }
            });
        }
    }
});
