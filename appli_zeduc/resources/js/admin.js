// ============================================================
// Espace administrateur
//   1. Supprimer une carte (employé / menu) avec confirmation (MODE DÉMO)
//   2. Aperçu de la photo dans les formulaires d'ajout
// ============================================================


// ----- 1. SUPPRIMER UNE CARTE -----
function activerSuppressionCartes() {
    // S'il n'y a pas de carte sur cette page, on ne fait rien
    // (ce fichier est chargé sur toutes les pages)
    if (!document.querySelector('[data-carte]')) return;

    // Un seul écouteur pour toute la page : on regarde si le clic
    // est tombé sur un bouton « Supprimer »
    document.addEventListener('click', (evenement) => {
        const bouton = evenement.target.closest('[data-supprimer]');
        if (!bouton) return;

        // On remonte du bouton jusqu'à sa carte, et on lit son titre
        const carte = bouton.closest('[data-carte]');
        const titre = carte.querySelector('h2')?.textContent.trim() ?? 'cet élément';

        // confirm() ouvre une fenêtre « OK / Annuler »
        if (confirm('Supprimer « ' + titre + ' » ?')) {
            carte.remove(); // À remplacer par un appel au backend quand il sera prêt
        }
    });
}


// ----- 2. APERÇU DE LA PHOTO -----
function activerApercuPhoto() {
    const champ = document.getElementById('champ-photo');
    if (!champ) return; // pas de formulaire photo sur cette page

    const apercu          = document.getElementById('apercu-photo');
    const boutonAjouter   = document.getElementById('btn-ajouter-photo');
    const boutonSupprimer = document.getElementById('btn-supprimer-photo');
    const TAILLE_MAX      = 2 * 1024 * 1024; // 2 Mo

    // Notre joli bouton déclenche le champ fichier (qui est caché)
    boutonAjouter.addEventListener('click', () => champ.click());

    // Quand un fichier est choisi : on vérifie sa taille, puis on l'affiche
    champ.addEventListener('change', () => {
        const fichier = champ.files[0];
        if (!fichier) return;

        if (fichier.size > TAILLE_MAX) {
            alert('La photo ne doit pas dépasser 2 Mo.');
            champ.value = '';
            return;
        }

        apercu.src = URL.createObjectURL(fichier); // adresse temporaire de l'image choisie
        apercu.classList.remove('hidden');         // on montre l'image
    });

    // Supprimer la photo : on vide le champ et on cache l'aperçu
    boutonSupprimer.addEventListener('click', () => {
        champ.value = '';
        apercu.removeAttribute('src');
        apercu.classList.add('hidden');
    });
}
// ----- 3. ENVOI DU FORMULAIRE (MODE DÉMO) -----
// À supprimer quand le backend est prêt, puis mettre sa vraie adresse dans action="..."
function activerEnvoiDemo() {
    const formulaire = document.getElementById('form-admin');
    if (!formulaire) return; // pas de formulaire sur cette page

    const message = document.getElementById('confirmation-admin');

    // « submit » se déclenche quand on clique sur le bouton d'envoi,
    // seulement si le navigateur a déjà validé tous les champs (required, pattern...)
    formulaire.addEventListener('submit', (evenement) => {
        evenement.preventDefault(); // on bloque l'envoi réel

        // On affiche le message prévu par la page (ou un message par défaut)
        message.textContent = formulaire.dataset.messageSucces ?? 'Enregistré (démo).';

        formulaire.reset();                                       // on vide les champs
        document.getElementById('btn-supprimer-photo').click();   // on vide aussi l'aperçu de la photo
    });
}

// ----- DÉMARRAGE -----
activerSuppressionCartes();
activerApercuPhoto();
activerEnvoiDemo();