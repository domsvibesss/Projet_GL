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

// ============================================================
// Réclamations (administrateur) : fenêtre « Traiter », recherche, filtres
// ============================================================

// Pour chaque statut : [texte affiché, couleurs de la pastille, couleur du point]
const STATUTS = {
    nouvelle: ['Nouvelle', 'bg-primary text-black',         'bg-black'],
    en_cours: ['En cours', 'bg-yellow-100 text-yellow-900', 'bg-yellow-500'],
    resolue:  ['Résolue',  'bg-green-100 text-green-900',   'bg-green-600'],
};

// Texte de la décision du gérant (la clé vient du backend)
const VALIDATIONS = {
    attente: 'En attente de validation par le gérant',
    validee: 'Réponse validée par le gérant',
    rejetee: 'Réponse rejetée par le gérant',
};

function activerReclamations() {
    // Raccourci : $('mon-id') remplace document.getElementById('mon-id')
    const $ = (id) => document.getElementById(id);

    const corps   = $('liste-reclamations');
    const fenetre = $('detail-reclamation');

    // Pas sur la page réclamations de l'admin : on ne fait rien
    if (!corps || !fenetre) return;

    let statutActif  = '';     // filtre en cours ('' = toutes)
    let ligneOuverte = null;   // ligne sur laquelle on a cliqué « Traiter »

    // Enlève les accents et met en minuscules : « Résolue » devient « resolue »
    const sansAccent = (t) => t.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();


    // ----- Recherche + filtre : une ligne reste visible si elle passe les DEUX tests -----
    function filtrer() {
        const recherche = sansAccent($('recherche-reclamations').value);

        corps.querySelectorAll('tr[data-statut]').forEach((ligne) => {
            const ok = sansAccent(ligne.textContent).includes(recherche)
                    && (statutActif === '' || ligne.dataset.statut === statutActif);
            ligne.classList.toggle('hidden', !ok);
        });
    }

    $('recherche-reclamations').addEventListener('input', filtrer);

    // Boutons de filtre : le bouton actif devient doré
    document.querySelectorAll('[data-statut-filtre]').forEach((bouton, _, tous) => {
        bouton.addEventListener('click', () => {
            statutActif = bouton.dataset.statutFiltre;
            tous.forEach((b) => {
                b.classList.toggle('bg-primary', b === bouton);
                b.classList.toggle('text-black', b === bouton);
                b.classList.toggle('text-primary', b !== bouton);
            });
            filtrer();
        });
    });


    // ----- Clic sur « Traiter » : on remplit la fenêtre et on l'ouvre -----
    corps.addEventListener('click', (e) => {
        const bouton = e.target.closest('[data-reclamation]');
        if (!bouton) return;

        const r = JSON.parse(bouton.dataset.reclamation);   // texte JSON -> objet
        ligneOuverte = bouton.closest('tr');

        $('d-titre').textContent = `${r.id} : ${r.objet}`;
        $('d-meta').textContent  = `${r.client} · ${r.date} · Réclamation ${r.type === 'points' ? 'de points' : 'client'}`;
        $('d-message').textContent    = r.message ?? '—';
        $('d-reponse').textContent    = r.reponse ?? "Aucune réponse de l'employé pour l'instant.";
        $('d-validation').textContent = VALIDATIONS[r.validation] ?? 'Aucune réponse à valider';
        $('d-statut').value = ligneOuverte.dataset.statut;
        $('d-reponse-admin').value = '';

        fenetre.showModal();
    });

    $('d-fermer').addEventListener('click', () => fenetre.close());


    // ----- Enregistrer (MODE DÉMO : la ligne change à l'écran) -----
    $('d-enregistrer').addEventListener('click', () => {
        const statut = $('d-statut').value;
        const [texte, couleurs, point] = STATUTS[statut];

        ligneOuverte.dataset.statut = statut;
        ligneOuverte.querySelector('[data-cellule-statut]').innerHTML =
            `<span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-semibold ${couleurs}">
                <span class="h-2 w-2 rounded-full ${point}"></span>${texte}</span>`;

        if ($('d-reponse-admin').value.trim() !== '') {
            alert("Réponse envoyée à l'étudiant (démo).");
        }

        fenetre.close();
        filtrer();
    });
}

// ----- DÉMARRAGE -----
activerSuppressionCartes();
activerApercuPhoto();
activerEnvoiDemo();
activerReclamations();