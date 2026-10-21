// ============================================================
// Page « Compte employés »
//   1. Recherche en direct dans le tableau
//   2. Simulation de création d'un employé (MODE DÉMO)
// ============================================================

function init() {
    // On retrouve les éléments de la page par leur id
    const champ      = document.getElementById('recherche');
    const liste      = document.getElementById('liste-employes');
    const formulaire = document.getElementById('form-employe');

    // Ce fichier est chargé sur toutes les pages : si on n'est pas
    // sur la page « employés », on ne fait rien.
    if (!formulaire) return;

    // Met en minuscules et enlève les accents : « Hervé » = « herve »
    const simplifier = (texte) =>
        texte.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');


    // ===== 1. RECHERCHE EN DIRECT =====
    // À chaque lettre tapée, on cache les lignes qui ne contiennent pas la recherche
    champ.addEventListener('input', () => {
        const recherche = simplifier(champ.value.trim());

        // On relit les lignes à chaque fois, pour inclure celles qu'on vient d'ajouter
        liste.querySelectorAll('tr').forEach((ligne) => {
            const visible = simplifier(ligne.textContent).includes(recherche);
            ligne.classList.toggle('hidden', !visible);
        });
    });


    // ===== 2. SIMULATION DE CRÉATION (MODE DÉMO) =====
    // À supprimer quand le backend est prêt, puis mettre sa vraie adresse dans action="..."
    // Ce code ne s'exécute que si le navigateur a déjà validé tous les champs.
    formulaire.addEventListener('submit', (evenement) => {
        evenement.preventDefault();               // on bloque l'envoi réel
        const donnees = new FormData(formulaire); // on lit ce qui a été tapé

        // Les 6 valeurs, dans l'ordre des colonnes du tableau
        const valeurs = [
            donnees.get('identifiant'), donnees.get('poste'),
            donnees.get('nom'),         donnees.get('prenom'),
            donnees.get('telephone'),   donnees.get('email'),
        ];

        // On construit la nouvelle ligne du tableau
        const ligne = document.createElement('tr');
        ligne.className = 'border-b border-stone-200 italic';

        valeurs.forEach((valeur, i) => {
            const cellule = document.createElement('td');
            cellule.className = 'px-6 py-5' + (i === 0 ? ' text-xl font-bold' : '');
            cellule.textContent = valeur;         // le texte n'est jamais exécuté comme du code
            ligne.appendChild(cellule);
        });

        liste.prepend(ligne);                     // nouvelle ligne en haut du tableau

        // Identifiant suivant : zds-0007 devient zds-0008
        const numero = parseInt(valeurs[0].split('-')[1], 10) + 1;
        formulaire.reset();                       // on vide le formulaire
        document.getElementById('identifiant').value = 'zds-' + String(numero).padStart(4, '0');
    });
}

init();