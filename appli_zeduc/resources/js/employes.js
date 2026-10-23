// ============================================================
// Page « Compte employés »
//   1. Recherche en direct dans le tableau
//   (La création d'un employé est un vrai formulaire : il part au serveur.)
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
}

init();