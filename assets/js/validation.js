/**
 * EcoRide - Contrôle de saisie JavaScript
 * Aucune validation HTML5 n'est utilisée
 * Version centralisée - Réutilisable sur toutes les pages
 */

// ═══════════════════════════════════════════════════════════
//  CONSTANTES
// ═══════════════════════════════════════════════════════════

const VALIDATION_CONFIG = {
    immatRegex: /^[A-Z]{2}-\d{3}-[A-Z]{2}$/,
    validStatuts: ['disponible', 'indisponible', 'en_maintenance'],
    maxLengthMarque: 50,
    maxLengthModele: 50,
    maxLengthCouleur: 30,
    minCapacite: 1,
    maxCapacite: 9
};

// ═══════════════════════════════════════════════════════════
//  VALIDATIONS VÉHICULE
// ═══════════════════════════════════════════════════════════

function validateMarque(value) {
    const val = value?.trim() || '';
    if (!val) return 'La marque est obligatoire.';
    if (val.length < 2) return 'La marque doit contenir au moins 2 caractères.';
    if (val.length > VALIDATION_CONFIG.maxLengthMarque) return 'La marque ne doit pas dépasser ' + VALIDATION_CONFIG.maxLengthMarque + ' caractères.';
    if (!/^[a-zA-ZÀ-ÿ0-9\s\-]+$/.test(val)) return 'La marque ne doit contenir que des lettres, chiffres, espaces et tirets.';
    return null;
}

function validateModele(value) {
    const val = value?.trim() || '';
    if (!val) return 'Le modèle est obligatoire.';
    if (val.length < 2) return 'Le modèle doit contenir au moins 2 caractères.';
    if (val.length > VALIDATION_CONFIG.maxLengthModele) return 'Le modèle ne doit pas dépasser ' + VALIDATION_CONFIG.maxLengthModele + ' caractères.';
    if (!/^[a-zA-ZÀ-ÿ0-9\s\-]+$/.test(val)) return 'Le modèle ne doit contenir que des lettres, chiffres, espaces et tirets.';
    return null;
}

function validateImmatriculation(value) {
    const val = value?.trim().toUpperCase() || '';
    if (!val) return "L'immatriculation est obligatoire.";
    if (!VALIDATION_CONFIG.immatRegex.test(val)) return "Format d'immatriculation invalide. Exemple: AB-123-CD";
    return null;
}

function validateCouleur(value) {
    const val = value?.trim() || '';
    if (val === '') return null;
    if (val.length > VALIDATION_CONFIG.maxLengthCouleur) return 'La couleur ne doit pas dépasser ' + VALIDATION_CONFIG.maxLengthCouleur + ' caractères.';
    if (!/^[a-zA-ZÀ-ÿ\s\-]+$/.test(val)) return 'La couleur ne doit contenir que des lettres.';
    return null;
}

function validateCapacite(value) {
    const val = parseInt(value);
    if (isNaN(val)) return 'La capacité doit être un nombre.';
    if (val < VALIDATION_CONFIG.minCapacite) return 'La capacité doit être au moins ' + VALIDATION_CONFIG.minCapacite + ' place.';
    if (val > VALIDATION_CONFIG.maxCapacite) return 'La capacité ne peut pas dépasser ' + VALIDATION_CONFIG.maxCapacite + ' places.';
    return null;
}

function validateStatut(value) {
    if (!VALIDATION_CONFIG.validStatuts.includes(value)) return 'Statut invalide.';
    return null;
}

// ═══════════════════════════════════════════════════════════
//  VALIDATIONS RÉSERVATION
// ═══════════════════════════════════════════════════════════

function validateDateReservation(value) {
    const val = value?.trim() || '';
    if (!val) return 'La date de réservation est obligatoire.';
    
    const dateRegex = /^(\d{2})\/(\d{2})\/(\d{4})$/;
    const match = val.match(dateRegex);
    
    if (!match) return 'Format invalide. Utilisez JJ/MM/AAAA';
    
    const day = parseInt(match[1], 10);
    const month = parseInt(match[2], 10);
    const year = parseInt(match[3], 10);
    
    if (month < 1 || month > 12) return 'Mois invalide (01-12)';
    
    const daysInMonth = new Date(year, month, 0).getDate();
    if (day < 1 || day > daysInMonth) return 'Jour invalide pour ce mois';
    
    const selectedDate = new Date(year, month - 1, day);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    if (selectedDate < today) return 'La date ne peut pas être dans le passé';
    
    return null;
}

function validateNote(value) {
    const val = value?.trim() || '';
    if (val.length > 500) return 'La note ne doit pas dépasser 500 caractères.';
    return null;
}

// ═══════════════════════════════════════════════════════════
//  VALIDATIONS GLOBALES
// ═══════════════════════════════════════════════════════════

function validateVehiculeForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;
    
    let errorMessages = [];
    
    const marque = document.querySelector('#marque')?.value || document.querySelector('input[name="marque"]')?.value || '';
    const modele = document.querySelector('#modele')?.value || document.querySelector('input[name="modele"]')?.value || '';
    const immat = document.querySelector('#immatriculation')?.value || document.querySelector('input[name="immatriculation"]')?.value || '';
    const couleur = document.querySelector('#couleur')?.value || document.querySelector('input[name="couleur"]')?.value || '';
    const capacite = document.querySelector('#capacite')?.value || document.querySelector('input[name="capacite"]')?.value || '4';
    const statut = document.querySelector('#statut')?.value || document.querySelector('select[name="statut"]')?.value || 'disponible';
    
    const errMarque = validateMarque(marque);
    if (errMarque) errorMessages.push(errMarque);
    
    const errModele = validateModele(modele);
    if (errModele) errorMessages.push(errModele);
    
    const errImmat = validateImmatriculation(immat);
    if (errImmat) errorMessages.push(errImmat);
    
    const errCouleur = validateCouleur(couleur);
    if (errCouleur) errorMessages.push(errCouleur);
    
    const errCapacite = validateCapacite(capacite);
    if (errCapacite) errorMessages.push(errCapacite);
    
    const errStatut = validateStatut(statut);
    if (errStatut) errorMessages.push(errStatut);
    
    if (errorMessages.length > 0) {
        alert('❌ Veuillez corriger les erreurs :\n- ' + errorMessages.join('\n- '));
        return false;
    }
    
    return true;
}

function validateReservationForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;
    
    let errorMessages = [];
    
    const date = document.querySelector('#dateReservation')?.value || '';
    const note = document.querySelector('#note')?.value || '';
    
    const errDate = validateDateReservation(date);
    if (errDate) errorMessages.push(errDate);
    
    const errNote = validateNote(note);
    if (errNote) errorMessages.push(errNote);
    
    if (errorMessages.length > 0) {
        alert('❌ Veuillez corriger les erreurs :\n- ' + errorMessages.join('\n- '));
        return false;
    }
    
    // Convertir la date pour l'envoi
    if (date && date.includes('/')) {
        const parts = date.split('/');
        let hiddenDate = document.querySelector('input[name="date_reservation_hidden"]');
        if (!hiddenDate) {
            hiddenDate = document.createElement('input');
            hiddenDate.type = 'hidden';
            hiddenDate.name = 'date_reservation';
            form.appendChild(hiddenDate);
        }
        hiddenDate.value = parts[2] + '-' + parts[1] + '-' + parts[0];
    }
    
    return true;
}

// ═══════════════════════════════════════════════════════════
//  FORMATAGE EN TEMPS RÉEL
// ═══════════════════════════════════════════════════════════

function formatImmatriculation(input) {
    if (!input) return;
    
    input.addEventListener('input', function() {
        this.value = this.value.toUpperCase();
        const isValid = VALIDATION_CONFIG.immatRegex.test(this.value);
        if (isValid) {
            this.style.borderColor = '#27ae60';
            this.style.backgroundColor = 'rgba(39,174,96,.1)';
        } else if (this.value.length > 0) {
            this.style.borderColor = '#e74c3c';
            this.style.backgroundColor = 'rgba(231,76,60,.1)';
        } else {
            this.style.borderColor = '';
            this.style.backgroundColor = '';
        }
    });
    
    input.addEventListener('blur', function() {
        let val = this.value.toUpperCase();
        val = val.replace(/[^A-Z0-9]/g, '');
        if (val.length === 7) {
            val = val.substring(0,2) + '-' + val.substring(2,5) + '-' + val.substring(5,7);
            this.value = val;
        }
    });
}

function formatDateReservation(input) {
    if (!input) return;
    
    input.addEventListener('input', function(e) {
        let cursorPos = this.selectionStart;
        let oldLength = this.value.length;
        let cleaned = this.value.replace(/\D/g, '');
        
        let formatted = '';
        if (cleaned.length >= 2 && cleaned.length < 4) {
            formatted = cleaned.substring(0, 2) + '/' + cleaned.substring(2);
        } else if (cleaned.length >= 4 && cleaned.length < 6) {
            formatted = cleaned.substring(0, 2) + '/' + cleaned.substring(2, 4) + '/' + cleaned.substring(4);
        } else if (cleaned.length >= 6) {
            formatted = cleaned.substring(0, 2) + '/' + cleaned.substring(2, 4) + '/' + cleaned.substring(4, 8);
        } else {
            formatted = cleaned;
        }
        
        this.value = formatted;
        
        let newLength = formatted.length;
        let newPos = cursorPos + (newLength - oldLength);
        this.setSelectionRange(newPos, newPos);
    });
}

// ═══════════════════════════════════════════════════════════
//  INITIALISATION
// ═══════════════════════════════════════════════════════════

document.addEventListener('DOMContentLoaded', function() {
    
    // Formulaire ajout/modification véhicule
    const formVehicule = document.getElementById('vehiculeForm');
    if (formVehicule) {
        formVehicule.addEventListener('submit', function(e) {
            if (!validateVehiculeForm('vehiculeForm')) {
                e.preventDefault();
            }
        });
    }
    
    // Formulaire réservation
    const formResa = document.getElementById('reservationForm');
    if (formResa) {
        formResa.addEventListener('submit', function(e) {
            if (!validateReservationForm('reservationForm')) {
                e.preventDefault();
            }
        });
    }
    
    // Formatage immatriculation
    const immatInputs = document.querySelectorAll('#immatriculation, input[name="immatriculation"]');
    immatInputs.forEach(input => formatImmatriculation(input));
    
    // Formatage date
    const dateInputs = document.querySelectorAll('#dateReservation');
    dateInputs.forEach(input => formatDateReservation(input));
    
    // Auto-dismiss alertes
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
        }, 4000);
        setTimeout(() => alert.remove(), 4500);
    });
});

// ═══════════════════════════════════════════════════════════
//  FONCTION POUR MODALE (mes_vehicules)
// ═══════════════════════════════════════════════════════════

function validateEditModal() {
    let errors = [];
    
    const marque = document.getElementById('edit_marque')?.value?.trim() || '';
    const modele = document.getElementById('edit_modele')?.value?.trim() || '';
    const immat = document.getElementById('edit_immatriculation')?.value?.trim() || '';
    const capacite = document.getElementById('edit_capacite')?.value || '4';
    
    const errMarque = validateMarque(marque);
    if (errMarque) errors.push(errMarque);
    
    const errModele = validateModele(modele);
    if (errModele) errors.push(errModele);
    
    const errImmat = validateImmatriculation(immat);
    if (errImmat) errors.push(errImmat);
    
    const errCapacite = validateCapacite(capacite);
    if (errCapacite) errors.push(errCapacite);
    
    if (errors.length > 0) {
        alert('❌ Veuillez corriger :\n- ' + errors.join('\n- '));
        return false;
    }
    
    return true;
}