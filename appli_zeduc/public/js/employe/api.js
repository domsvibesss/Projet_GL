/*
|--------------------------------------------------------------------------
| Outils partagés par les 4 pages de l'espace employé
|--------------------------------------------------------------------------
| DRY : chaque page a besoin d'appeler l'API, d'afficher un message,
| de formater un prix ou une heure. Ces fonctions sont écrites UNE fois ici
| au lieu d'être recopiées dans commandes, menu, réclamations et statistiques.
|
| SOLID (S, responsabilité unique) : ce fichier ne fait QUE parler à l'API
| et formater des valeurs. Il ne sait rien de l'affichage d'une page précise.
|
| KISS : un simple fichier JS dans public/, chargé par une balise <script>.
| Pas de framework, pas d'étape de compilation supplémentaire.
*/

/** Préfixe commun de toutes les routes de routes/api.php (groupe "employe"). */
const API_EMPLOYE = '/api/employe';

/** Jeton CSRF écrit par le layout dans <meta name="csrf-token">. */
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

/**
 * Appelle l'API de l'espace employé et renvoie le JSON.
 * En cas d'erreur (403, 404, 422…), lève une Error avec le message du contrôleur,
 * par exemple « Transition impossible : la commande est … ».
 *
 * @param {string} methode  'GET' | 'POST' | 'PATCH' | 'DELETE'
 * @param {string} chemin   partie après /api/employe, ex. '/commandes/12/prete'
 * @param {object|null} corps  données envoyées en JSON (POST / PATCH)
 */
async function api(methode, chemin, corps = null) {
    const reponse = await fetch(API_EMPLOYE + chemin, {
        method: methode,
        headers: {
            'Accept': 'application/json',        // Laravel renvoie alors ses erreurs en JSON
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,                // obligatoire pour POST / PATCH / DELETE
        },
        body: corps ? JSON.stringify(corps) : null,
        credentials: 'same-origin',              // envoie le cookie de session (auth:sanctum)
    });

    // .catch : une page d'erreur HTML (500) ne doit pas faire planter le JSON.parse
    const donnees = await reponse.json().catch(() => ({}));

    if (!reponse.ok) {
        throw new Error(donnees.message ?? `Erreur ${reponse.status}`);
    }
    return donnees;
}

/**
 * Neutralise le HTML d'un texte avant de l'insérer avec innerHTML.
 * Sécurité (faille XSS) : la description d'une réclamation est écrite par un
 * étudiant ; sans ça, un "<script>" dans son texte s'exécuterait chez l'employé.
 */
function echapper(texte) {
    const div = document.createElement('div');
    div.textContent = texte ?? '';
    return div.innerHTML;
}

/** Affiche un message de succès (vert) ou d'erreur (rouge) dans l'élément donné. */
function afficherMessage(element, texte, succes = true) {
    element.textContent = texte;
    element.className = `mt-4 min-h-6 text-center ${succes ? 'text-green-400' : 'text-red-400'}`;
}

/** 3000 -> « 3 000 F » */
function formatPrix(montant) {
    return `${new Intl.NumberFormat('fr-FR').format(Number(montant) || 0)} F`;
}

/**
 * Transforme « 2026-10-23 12:30:00 » (format MySQL) en objet Date.
 * Le remplacement de l'espace par « T » est nécessaire pour Safari,
 * qui ne lit pas l'autre format (le cahier des charges exige Safari).
 */
function versDate(valeur) {
    return new Date(String(valeur).replace(' ', 'T'));
}

/** « 2026-10-23 12:30:00 » -> « 12h30 » */
function formatHeure(valeur) {
    return versDate(valeur)
        .toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })
        .replace(':', 'h');
}

/** « 2026-10-23 12:30:00 » -> « 23/10/2026, 12h30 » */
function formatDateHeure(valeur) {
    return `${versDate(valeur).toLocaleDateString('fr-FR')}, ${formatHeure(valeur)}`;
}

/**
 * Pastille de statut (« En attente », « Livrée »…).
 * Chaque page fournit son propre libellé et ses couleurs : la fonction ne
 * dépend d'aucun statut précis (SOLID, O : on ajoute un statut sans la modifier).
 */
function badge(libelle, classes) {
    return `<span class="inline-block rounded-full border px-3 py-1 text-xs font-semibold ${classes}">${echapper(libelle)}</span>`;
}
