// ============================================================
// Page « Réclamations »
//   1. Recherche en direct dans le tableau
//   2. Clic sur une ligne : la réclamation s'affiche à droite
//   3. Envoi de la réponse au serveur (formulaire #form-reponse)
// ============================================================

function init() {
    // On retrouve les éléments de la page par leur id
    const liste          = document.getElementById('liste-reclamations');

    // Ce fichier est chargé sur toutes les pages : si on n'est pas
    // sur la page « réclamations », on ne fait rien.
    if (!liste || !document.getElementById('btn-envoyer')) return;

    const champRecherche = document.getElementById('recherche-reclamation');
    const titreDetail    = document.getElementById('detail-titre');
    const messageDetail  = document.getElementById('detail-message');
    const champReponse   = document.getElementById('reponse');
    const formulaire     = document.getElementById('form-reponse');
    const confirmation   = document.getElementById('confirmation');

    // Met en minuscules et enlève les accents : « Hervé » = « herve »
    const simplifier = (texte) =>
        texte.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');


    // ===== 1. RECHERCHE EN DIRECT =====
    champRecherche.addEventListener('input', () => {
        const recherche = simplifier(champRecherche.value.trim());

        liste.querySelectorAll('tr').forEach((ligne) => {
            const visible = simplifier(ligne.textContent).includes(recherche);
            ligne.classList.toggle('hidden', !visible);
        });
    });


    // ===== 2. SÉLECTION D'UNE RÉCLAMATION =====
    let ligneActive = null; // la ligne sur laquelle on a cliqué en dernier

    // Affiche une message dans la zone de confirmation (vert si ok, rouge si erreur)
    function afficherMessage(texte, estErreur) {
        confirmation.textContent = texte;
        confirmation.classList.toggle('text-red-600', estErreur);
        confirmation.classList.toggle('text-green-700', !estErreur);
    }

    function selectionner(ligne) {
        // On enlève le surlignage de l'ancienne ligne, on le met sur la nouvelle
        if (ligneActive) ligneActive.classList.remove('bg-primary/25');
        ligneActive = ligne;
        ligne.classList.add('bg-primary/25');

        // On remplit le panneau de droite avec les données de la ligne (data-id, data-message)
        titreDetail.textContent   = 'Réclamation - ' + ligne.dataset.id;
        messageDetail.textContent = '“' + ligne.dataset.message + '”';

        // Le formulaire enverra la réponse à l'adresse de CETTE réclamation (data-url)
        formulaire.action = ligne.dataset.url;
    }

    // UN seul écouteur sur tout le tableau (« délégation d'événement ») :
    // on regarde sur quelle ligne le clic est tombé.
    liste.addEventListener('click', (evenement) => {
        const ligne = evenement.target.closest('tr');
        if (!ligne) return;

        selectionner(ligne);

        // Nouvelle réclamation choisie : on repart d'une réponse vide
        champReponse.value = '';
        afficherMessage('', false);
    });

    // Au chargement, la première réclamation est déjà sélectionnée
    // (on garde le message « Réponse envoyée » affiché par le serveur après un envoi)
    const premiere = liste.querySelector('tr');
    if (premiere) selectionner(premiere);


    // ===== 3. ENVOI DE LA RÉPONSE =====
    // Le formulaire part au serveur, qui enregistre la réponse puis recharge la page.
    // On vérifie seulement avant l'envoi que la réponse n'est pas vide.
    formulaire.addEventListener('submit', (evenement) => {
        if (!ligneActive || champReponse.value.trim() === '') {
            evenement.preventDefault();
            afficherMessage('Écris une réponse avant de l\'envoyer.', true);
        }
    });
}

init();