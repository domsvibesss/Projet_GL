// ============================================================
// Page « Supervision des commandes »
//   Filtrer le tableau en cliquant sur une carte
// ============================================================

function init() {
    const corps = document.getElementById('corps-commandes');

    // Ce fichier est chargé sur toutes les pages : si on n'est pas
    // sur la page « supervision », on ne fait rien.
    if (!corps) return;

    const cartes = document.querySelectorAll('[data-filtre]');
    let filtreActif = null; // null = pas de filtre, on voit tout

    // Cache les lignes qui ne correspondent pas, et entoure la carte choisie
    function appliquerFiltre() {
        corps.querySelectorAll('tr[data-statut]').forEach((ligne) => {
            const cacher = filtreActif !== null && ligne.dataset.statut !== filtreActif;
            ligne.classList.toggle('hidden', cacher);
        });

        cartes.forEach((carte) => {
            carte.classList.toggle('ring-2', carte.dataset.filtre === filtreActif);
        });
    }

    // Clic sur une carte : on active son filtre, ou on l'enlève si c'était déjà le sien
    cartes.forEach((carte) => {
        carte.addEventListener('click', () => {
            const choisi = carte.dataset.filtre;
            filtreActif = (filtreActif === choisi) ? null : choisi;
            appliquerFiltre();
        });
    });

    // Bouton « Tout afficher » : on enlève le filtre
    document.getElementById('btn-tout').addEventListener('click', () => {
        filtreActif = null;
        appliquerFiltre();
    });
}

init();