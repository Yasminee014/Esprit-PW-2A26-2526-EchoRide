// validation.js - Contrôle de saisie en temps réel

// ========== VALIDATION EN TEMPS RÉEL ==========

// Validation du titre
function validateTitre() {
    const titre = document.getElementById('titre');
    const titreError = document.getElementById('titreError');
    
    if(!titre.value || titre.value.trim().length < 3) {
        titreError.innerHTML = '❌ Le titre doit contenir au moins 3 caractères';
        titreError.style.color = '#dc3545';
        titre.style.border = '1px solid #dc3545';
        return false;
    } else {
        titreError.innerHTML = '✓ Titre valide';
        titreError.style.color = '#28a745';
        titre.style.border = '1px solid #28a745';
        return true;
    }
}

// Validation du type
function validateType() {
    const type = document.getElementById('type');
    const typeError = document.getElementById('typeError');
    
    if(!type.value) {
        typeError.innerHTML = '❌ Le type est obligatoire';
        typeError.style.color = '#dc3545';
        type.style.border = '1px solid #dc3545';
        return false;
    } else {
        typeError.innerHTML = '✓ Type valide';
        typeError.style.color = '#28a745';
        type.style.border = '1px solid #28a745';
        return true;
    }
}

// Validation de la ville
function validateVille() {
    const ville = document.getElementById('ville');
    const villeError = document.getElementById('villeError');
    
    if(!ville.value || ville.value.trim() === '') {
        villeError.innerHTML = '❌ La ville est obligatoire';
        villeError.style.color = '#dc3545';
        ville.style.border = '1px solid #dc3545';
        return false;
    } else {
        villeError.innerHTML = '✓ Ville valide';
        villeError.style.color = '#28a745';
        ville.style.border = '1px solid #28a745';
        return true;
    }
}

// Validation de la date
function validateDate() {
    const date = document.getElementById('date_evenement');
    const dateError = document.getElementById('dateError');
    const now = new Date().toISOString().slice(0, 16);
    
    if(!date.value) {
        dateError.innerHTML = '❌ La date est obligatoire';
        dateError.style.color = '#dc3545';
        date.style.border = '1px solid #dc3545';
        return false;
    } else if(date.value < now) {
        dateError.innerHTML = '❌ La date ne peut pas être dans le passé';
        dateError.style.color = '#dc3545';
        date.style.border = '1px solid #dc3545';
        return false;
    } else {
        dateError.innerHTML = '✓ Date valide';
        dateError.style.color = '#28a745';
        date.style.border = '1px solid #28a745';
        return true;
    }
}

// Validation du nombre de places
function validatePlaces() {
    const places = document.getElementById('nb_places');
    const placesError = document.getElementById('placesError');
    
    if(!places.value || places.value <= 0) {
        placesError.innerHTML = '❌ Le nombre de places doit être > 0';
        placesError.style.color = '#dc3545';
        places.style.border = '1px solid #dc3545';
        return false;
    } else {
        placesError.innerHTML = '✓ Nombre de places valide';
        placesError.style.color = '#28a745';
        places.style.border = '1px solid #28a745';
        return true;
    }
}

// Validation du nom entreprise (sponsor)
function validateNomEntreprise() {
    const nom = document.getElementById('nom_entreprise');
    const nomError = document.getElementById('nomError');
    
    if(!nom.value || nom.value.trim().length < 2) {
        nomError.innerHTML = '❌ Le nom doit contenir au moins 2 caractères';
        nomError.style.color = '#dc3545';
        nom.style.border = '1px solid #dc3545';
        return false;
    } else {
        nomError.innerHTML = '✓ Nom valide';
        nomError.style.color = '#28a745';
        nom.style.border = '1px solid #28a745';
        return true;
    }
}

// Validation du montant (sponsor)
function validateMontant() {
    const montant = document.getElementById('montant_sponsoring');
    const montantError = document.getElementById('montantError');
    
    if(!montant.value || montant.value <= 0) {
        montantError.innerHTML = '❌ Le montant doit être > 0';
        montantError.style.color = '#dc3545';
        montant.style.border = '1px solid #dc3545';
        return false;
    } else {
        montantError.innerHTML = '✓ Montant valide';
        montantError.style.color = '#28a745';
        montant.style.border = '1px solid #28a745';
        return true;
    }
}

// ========== VALIDATION FINALE POUR SOUMISSION ==========

// Validation formulaire événement
function validateEventForm() {
    const isTitreValid = validateTitre();
    const isTypeValid = validateType();
    const isVilleValid = validateVille();
    const isDateValid = validateDate();
    const isPlacesValid = validatePlaces();
    
    return isTitreValid && isTypeValid && isVilleValid && isDateValid && isPlacesValid;
}

// Validation formulaire sponsor
function validateSponsorForm() {
    const isNomValid = validateNomEntreprise();
    const isMontantValid = validateMontant();
    
    return isNomValid && isMontantValid;
}

// Confirmation suppression
function confirmDelete() {
    return confirm('Êtes-vous sûr de vouloir supprimer cet élément ?');
}

// ========== ATTACHER LES ÉVÉNEMENTS AU CHARGEMENT ==========
document.addEventListener('DOMContentLoaded', function() {
    
    // Événements pour le formulaire événement
    const titre = document.getElementById('titre');
    if(titre) {
        titre.addEventListener('input', validateTitre);
        titre.addEventListener('blur', validateTitre);
    }
    
    const type = document.getElementById('type');
    if(type) {
        type.addEventListener('change', validateType);
        type.addEventListener('blur', validateType);
    }
    
    const ville = document.getElementById('ville');
    if(ville) {
        ville.addEventListener('input', validateVille);
        ville.addEventListener('blur', validateVille);
    }
    
    const date = document.getElementById('date_evenement');
    if(date) {
        date.addEventListener('change', validateDate);
        date.addEventListener('blur', validateDate);
    }
    
    const places = document.getElementById('nb_places');
    if(places) {
        places.addEventListener('input', validatePlaces);
        places.addEventListener('blur', validatePlaces);
    }
    
    // Événements pour le formulaire sponsor
    const nom = document.getElementById('nom_entreprise');
    if(nom) {
        nom.addEventListener('input', validateNomEntreprise);
        nom.addEventListener('blur', validateNomEntreprise);
    }
    
    const montant = document.getElementById('montant_sponsoring');
    if(montant) {
        montant.addEventListener('input', validateMontant);
        montant.addEventListener('blur', validateMontant);
    }

// Génération de description avec IA
document.getElementById('btnGenerateIA').addEventListener('click', async function() {
    const titre = document.getElementById('titre').value;
    const ville = document.getElementById('ville').value;
    const type = document.getElementById('type').value;
    
    if(!titre) {
        alert('Veuillez d\'abord saisir le titre de l\'événement');
        document.getElementById('titre').focus();
        return;
    }
    
    if(!ville) {
        alert('Veuillez d\'abord saisir la ville');
        document.getElementById('ville').focus();
        return;
    }
    
    if(!type) {
        alert('Veuillez d\'abord sélectionner le type');
        document.getElementById('type').focus();
        return;
    }
    
    const btn = document.getElementById('btnGenerateIA');
    const loader = document.getElementById('iaLoading');
    btn.disabled = true;
    loader.style.display = 'block';
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Génération en cours...';
    
    try {
        const response = await fetch('../../../Controller/api_generate_description.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                titre: titre, 
                ville: ville, 
                type: type 
            })
        });
        
        const data = await response.json();
        
        if(data.description) {
            document.getElementById('description').value = data.description;
            if(data.fallback) {
                console.log('Mode fallback utilisé (API indisponible)');
            }
        } else {
            alert('Erreur lors de la génération');
        }
    } catch(error) {
        console.error('Erreur:', error);
        alert('Erreur de connexion à l\'API');
    } finally {
        btn.disabled = false;
        loader.style.display = 'none';
        btn.innerHTML = '<i class="fas fa-robot"></i> ✨ Générer description avec IA';
    }
});
});