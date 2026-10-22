// ============================================================
// Pages admin : suppression, photo, envoi démo, recherche de liste
// ============================================================

// ----- Suppression d'une ligne ou d'une carte (bouton avec data-supprimer) -----
function activerSuppressionCartes() {
    document.addEventListener('click', (e) => {
        const bouton = e.target.closest('[data-supprimer]');
        if (!bouton) return;

        const carte  = bouton.closest('[data-carte]');         // la ligne ou la carte concernée
        const groupe = bouton.closest('[data-item-id]');    // existe seulement sur les pages Employes et Menus
        const titre  = carte?.dataset.nom ?? carte?.querySelector('h2')?.textContent.trim() ?? 'cet élément';

        if (!confirm(`Supprimer « ${titre} » ?`)) return;

        if (groupe) {
            // Pages Employes et Menus : on retire l element dans les deux vues (carte ET ligne du tableau)
            document.querySelectorAll(`[data-item-id="${groupe.dataset.itemId}"]`)
                    .forEach((element) => element.remove());
        } else {
            // Autres pages (Menus...) : comportement d'origine
            carte.remove();
        }
    });
}

// ----- Aperçu de la photo choisie (limite 2 Mo) -----
function activerApercuPhoto() {
    const $ = (id) => document.getElementById(id);
    const champ  = $('champ-photo');
    const apercu = $('apercu-photo');
    if (!champ || !apercu) return;   // pas sur une page avec photo

    // Affiche l'image (url) ou revient à la zone vide (url = '')
    function afficher(url) {
        if (url) apercu.src = url; else apercu.removeAttribute('src');
        apercu.classList.toggle('hidden', !url);
        $('photo-vide')?.classList.toggle('hidden', !!url);
    }

    $('btn-ajouter-photo').addEventListener('click', () => champ.click());

    champ.addEventListener('change', () => {
        const fichier = champ.files[0];
        if (!fichier) return;
        if (fichier.size > 2 * 1024 * 1024) {
            alert('Image trop lourde (2 Mo maximum).');
            champ.value = '';
            return;
        }
        afficher(URL.createObjectURL(fichier));
    });

    $('btn-supprimer-photo').addEventListener('click', () => {
        champ.value = '';
        afficher('');
    });
}

// ----- Envoi en MODE DÉMO : on affiche un message au lieu d'envoyer -----
// À supprimer quand le backend sera branché.
function activerEnvoiDemo() {
    const form    = document.getElementById('form-admin');
    const message = document.getElementById('confirmation-admin');
    if (!form || !message) return;

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        message.textContent = form.dataset.messageSucces ?? 'Enregistré (démo).';

        // En ajout, on vide le formulaire ; en modification, on garde les valeurs
        if (!form.dataset.modification) {
            form.reset();
            document.getElementById('btn-supprimer-photo')?.click();
        }
    });
}

// ----- Recherche dans une liste : <input data-recherche-liste="#id-du-tbody"> -----
function activerRechercheListe() {
    const champ = document.querySelector('[data-recherche-liste]');
    if (!champ) return;

    const sansAccent = (t) => t.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();

    champ.addEventListener('input', () => {
        const recherche = sansAccent(champ.value);
        document.querySelectorAll(`${champ.dataset.rechercheListe} tr`).forEach((ligne) => {
            ligne.classList.toggle('hidden', !sansAccent(ligne.textContent).includes(recherche));
        });
    });
}


// ============================================================
// Pages Employes et Menus : bascule Cartes / Tableau + recherche
// ============================================================
function activerVuesListe() {
    const champ       = document.getElementById('champ-recherche');
    const vueCartes   = document.getElementById('vue-cartes');
    const vueTableau  = document.getElementById('vue-tableau');
    const messageVide = document.getElementById('message-vide');
    const boutons     = document.querySelectorAll('[data-vue]');

    if (!champ || !vueCartes || !vueTableau) return;   // pas sur une page avec cartes + tableau

    const sansAccent = (t) => t.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();

    // Affiche « cartes » ou « tableau » et colore le bon bouton
    function afficherVue(nom) {
        vueCartes.classList.toggle('hidden', nom !== 'cartes');
        vueTableau.classList.toggle('hidden', nom !== 'tableau');

        boutons.forEach((b) => {
            const actif = b.dataset.vue === nom;
            b.classList.toggle('bg-primary', actif);
            b.classList.toggle('text-black', actif);
            b.classList.toggle('text-primary', !actif);
        });
    }

    boutons.forEach((b) => b.addEventListener('click', () => afficherVue(b.dataset.vue)));

    // Recherche : filtre les cartes ET les lignes du tableau en même temps
    champ.addEventListener('input', () => {
        const recherche = sansAccent(champ.value);
        let visibles = 0;

        vueTableau.querySelectorAll('[data-item-id]').forEach((ligne) => {
            const ok = sansAccent(ligne.dataset.recherche).includes(recherche);
            ligne.classList.toggle('hidden', !ok);
            if (ok) visibles++;
        });

        vueCartes.querySelectorAll('[data-item-id]').forEach((carte) => {
            carte.classList.toggle('hidden', !sansAccent(carte.dataset.recherche).includes(recherche));
        });

        messageVide?.classList.toggle('hidden', visibles > 0);
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
activerRechercheListe();
activerVuesListe();
activerReclamations();