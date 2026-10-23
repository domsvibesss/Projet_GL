@extends('layouts.employe')

@section('title', 'Plat du jour')

@section('content')

{{-- ===== Bandeau : plat du jour actuel ===== --}}
<section class="flex flex-col gap-4 rounded-xl bg-primary p-5 text-black sm:flex-row sm:items-center sm:justify-between">
    <div>
        <p class="text-sm">Plat du jour</p>
        <p id="pdj-nom" class="font-serif text-2xl">Chargement…</p>
    </div>
    <button type="button" id="btn-changer-pdj"
            class="rounded-lg bg-black px-6 py-3 font-serif text-primary transition hover:opacity-80">
        Changer le plat du jour
    </button>
</section>

{{--
    ÉCART MAQUETTE / CAHIER DES CHARGES
    La maquette proposait un « Menu de la semaine » (planning lundi → dimanche).
    Le cahier des charges demande seulement des mises à jour TEMPORAIRES :
    « plat épuisé, changement du plat du jour ». Le planning n'existe ni dans le
    cahier ni dans le contrôleur : il est remplacé par la disponibilité des plats
    (YAGNI : on ne code pas une fonctionnalité que personne n'a demandée).
--}}
<section class="mt-6 rounded-xl border border-primary/40 p-5">
    <h1 class="font-serif text-2xl text-primary">Disponibilité des plats</h1>
    <p class="mt-1 text-sm text-white/70">
        Marquez un plat épuisé, ou corrigez son stock après un réapprovisionnement.
    </p>
</section>

{{-- Cartes beiges des plats, remplies par le JavaScript --}}
<div id="grille-plats" class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4"></div>

<p id="message" class="mt-4 min-h-6 text-center"></p>


{{--
    ===== Fenêtre : choisir le plat du jour =====
    <dialog> est une balise HTML native : ouverture, fermeture avec Échap et fond
    assombri sont gérés par le navigateur (KISS : pas de bibliothèque de modales).

    ÉCART MAQUETTE / CAHIER DES CHARGES
    La maquette utilisait des cases à cocher (plusieurs plats). Il n'y a qu'UN plat
    du jour par jour (le contrôleur le garantit) : on utilise donc des boutons radio.
--}}
<dialog id="fenetre-pdj" class="w-full max-w-md rounded-2xl border border-primary/40 bg-neutral-900 p-0 text-white backdrop:bg-black/70">
    <div class="p-6">
        <h2 class="font-serif text-2xl">Choisir le plat du jour</h2>
        <p class="mt-1 text-sm text-primary">Sélectionnez le plat à servir aujourd'hui, puis validez.</p>

        <input type="search" id="recherche-plat" placeholder="Rechercher un plat"
               class="mt-4 w-full rounded-lg border border-primary/60 bg-transparent px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary">

        {{-- Seuls les plats en stock sont proposés : un plat épuisé ne peut pas être plat du jour --}}
        <ul id="liste-choix" class="mt-4 max-h-72 space-y-1 overflow-y-auto"></ul>
    </div>

    <div class="flex items-center justify-between gap-3 border-t border-white/10 px-6 py-4">
        <span id="choix-libelle" class="text-sm text-white/60">Aucun plat sélectionné</span>
        <div class="flex gap-2">
            <button type="button" id="btn-annuler"
                    class="rounded-lg bg-white/20 px-4 py-2 font-serif transition hover:opacity-80">Annuler</button>
            <button type="button" id="btn-valider" disabled
                    class="rounded-lg bg-primary px-4 py-2 font-serif text-black transition hover:opacity-80 disabled:opacity-40">Valider</button>
        </div>
    </div>
</dialog>

@endsection

@push('scripts')
<script>
const grille      = document.getElementById('grille-plats');
const zoneMessage = document.getElementById('message');
const fenetre     = document.getElementById('fenetre-pdj');
const listeChoix  = document.getElementById('liste-choix');
const btnValider  = document.getElementById('btn-valider');

let plats = []; // gardés en mémoire pour la fenêtre de choix et la recherche

/** Une carte de plat : nom, prix, stock, et les deux actions de mise à jour. */
function carte(p) {
    return `
        <article class="flex flex-col rounded-xl bg-primary p-4 text-black">
            <div class="flex items-start justify-between gap-2">
                <h3 class="font-serif text-lg">${echapper(p.nom)}</h3>
                ${p.plat_du_jour ? '<span class="rounded-full bg-black px-2 py-0.5 text-xs text-primary">Plat du jour</span>' : ''}
            </div>
            <p class="text-sm">${formatPrix(p.prix)}</p>
            <p class="mt-2 text-sm">
                ${p.disponible ? `En stock : <strong>${p.quantite}</strong>` : '<strong class="text-red-700">Épuisé</strong>'}
            </p>

            <div class="mt-auto pt-4">
                <label class="text-xs" for="stock-${p.id}">Nouveau stock</label>
                <div class="mt-1 flex gap-2">
                    <input id="stock-${p.id}" type="number" min="0" value="${p.quantite}"
                           class="w-full rounded-lg bg-white px-3 py-2">
                    <button type="button" data-action="stock" data-id="${p.id}"
                            class="rounded-lg bg-black px-3 py-2 text-primary transition hover:opacity-80">OK</button>
                </div>
                <button type="button" data-action="epuise" data-id="${p.id}" ${p.disponible ? '' : 'disabled'}
                        class="mt-2 w-full rounded-lg bg-black py-2 text-primary transition hover:opacity-80 disabled:opacity-40">
                    Marquer épuisé
                </button>
            </div>
        </article>`;
}

/** Charge le plat du jour et la liste des plats. */
async function charger() {
    try {
        // Les deux requêtes sont indépendantes : on les lance en même temps
        const [pdj, menu] = await Promise.all([
            api('GET', '/plat-du-jour'),
            api('GET', '/plats'),
        ]);

        document.getElementById('pdj-nom').textContent = pdj.data ? pdj.data.nom : 'Aucun plat du jour défini';
        plats = menu.data;
        grille.innerHTML = plats.length ? plats.map(carte).join('') : '<p>Aucun plat dans le menu.</p>';
    } catch (erreur) {
        afficherMessage(zoneMessage, erreur.message, false);
    }
}

/** Exécute une action, affiche le message du contrôleur et recharge la page. (DRY) */
async function executer(promesse) {
    try {
        const reponse = await promesse;
        afficherMessage(zoneMessage, reponse.message, true);
        await charger();
    } catch (erreur) {
        afficherMessage(zoneMessage, erreur.message, false);
    }
}

// Délégation : un seul écouteur pour les boutons de toutes les cartes
grille.addEventListener('click', (e) => {
    const bouton = e.target.closest('button[data-action]');
    if (!bouton) return;
    const id = bouton.dataset.id;

    if (bouton.dataset.action === 'epuise') {
        // PATCH /api/employe/plats/{id}/epuise  ->  marquerEpuise()
        executer(api('PATCH', `/plats/${id}/epuise`));
    } else {
        // PATCH /api/employe/plats/{id}/quantite  ->  mettreAJourStock()
        const quantite = Number(document.getElementById(`stock-${id}`).value);
        executer(api('PATCH', `/plats/${id}/quantite`, { quantite }));
    }
});


// ===== Fenêtre de choix du plat du jour =====

/** Affiche les plats en stock dont le nom contient le texte recherché. */
function afficherChoix(filtre = '') {
    const proposes = plats.filter(p => p.disponible && p.nom.toLowerCase().includes(filtre.toLowerCase()));

    listeChoix.innerHTML = proposes.length
        ? proposes.map(p => `
            <li>
                <label class="flex cursor-pointer items-center gap-3 rounded-lg px-3 py-2 hover:bg-white/10">
                    <input type="radio" name="plat-choisi" value="${p.id}" data-nom="${echapper(p.nom)}" class="h-5 w-5 accent-primary">
                    <span class="font-serif">${echapper(p.nom)}</span>
                </label>
            </li>`).join('')
        : '<li class="px-3 py-2 text-white/60">Aucun plat disponible.</li>';
}

document.getElementById('btn-changer-pdj').addEventListener('click', () => {
    document.getElementById('recherche-plat').value = '';
    document.getElementById('choix-libelle').textContent = 'Aucun plat sélectionné';
    btnValider.disabled = true;
    afficherChoix();
    fenetre.showModal();
});

document.getElementById('recherche-plat').addEventListener('input', (e) => afficherChoix(e.target.value));

listeChoix.addEventListener('change', (e) => {
    document.getElementById('choix-libelle').textContent = `Choix : ${e.target.dataset.nom}`;
    btnValider.disabled = false;
});

document.getElementById('btn-annuler').addEventListener('click', () => fenetre.close());

btnValider.addEventListener('click', () => {
    const choix = listeChoix.querySelector('input:checked');
    if (!choix) return;
    fenetre.close();
    // PATCH /api/employe/plats/{id}/plat-du-jour  ->  definirPlatDuJour()
    executer(api('PATCH', `/plats/${choix.value}/plat-du-jour`));
});

charger();
</script>
@endpush
