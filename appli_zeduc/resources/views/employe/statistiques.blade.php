@extends('layouts.employe')

@section('title', 'Statistiques')

@section('content')

<h1 class="font-serif text-3xl">Statistiques de la semaine</h1>
<p id="periode" class="mt-1 text-sm text-white/60"></p>

{{--
    ÉCART MAQUETTE / CAHIER DES CHARGES
    La maquette montrait « Plat le moins vendu ». Le cahier parle des « ventes de la
    semaine », et le contrôleur ne sait pas trouver un plat jamais vendu (il ne
    compte que les plats commandés). Cette carte est remplacée par le chiffre
    d'affaires, que le contrôleur calcule déjà (YAGNI + données fiables).
--}}
<div class="mt-6 grid gap-4 sm:grid-cols-3">
    <x-employe.stat-card cle="total" libelle="Commandes" />
    <x-employe.stat-card cle="top"   libelle="Plat le plus vendu" />
    <x-employe.stat-card cle="ca"    libelle="Chiffre d'affaires" />
</div>

{{-- ===== Graphique : commandes par jour ===== --}}
<section class="mt-8 rounded-2xl bg-neutral-200 p-6 text-black">
    <h2 class="text-xl font-semibold">Commandes par jour</h2>
    <p class="text-sm text-black/60">Cliquez sur un jour pour voir son détail. Le jour le plus chargé est en noir.</p>

    {{--
        KISS : des barres en simple HTML/CSS (hauteur en %) au lieu d'une bibliothèque
        de graphiques comme Chart.js : 7 barres ne justifient pas une dépendance.
    --}}
    <div id="graphique" class="mt-6 flex h-64 items-stretch gap-2 sm:gap-4"></div>
</section>

{{--
    ===== Détail d'un jour (maquette « Statistique journalier ») =====
    Placé sur la même page au lieu d'une page à part : il s'ouvre au clic sur une
    barre (KISS : pas de route ni de vue supplémentaire).
--}}
<section id="detail-jour" class="mt-8 hidden">
    <h2 class="mx-auto w-fit rounded-xl bg-primary/60 px-6 py-2 font-serif text-2xl">Statistiques journalières</h2>

    <div class="mx-auto mt-4 max-w-2xl rounded-xl bg-neutral-300 p-6 text-black">
        <div class="flex items-baseline justify-between gap-4">
            <p id="jour-nom" class="text-lg capitalize"></p>
            <p id="jour-total" class="text-2xl"></p>
        </div>
        <p class="mt-4 text-sm text-black/60">Plats les plus vendus (commandes livrées)</p>
        <ul id="jour-plats" class="mt-2 space-y-1 text-lg"></ul>
    </div>
</section>

<p id="message" class="mt-4 min-h-6 text-center"></p>

@endsection

@push('scripts')
<script>
const graphique   = document.getElementById('graphique');
const zoneMessage = document.getElementById('message');

/** 1 -> « 1 commande », 12 -> « 12 commandes » */
const pluriel = (n, mot) => `${n} ${mot}${n > 1 ? 's' : ''}`;

/** « 2026-10-19 » -> « lun. » ou « lundi 19 octobre » */
function nomJour(date, format = 'short') {
    const d = new Date(`${date}T00:00:00`); // heure locale, pour ne pas glisser au jour précédent
    return format === 'short'
        ? d.toLocaleDateString('fr-FR', { weekday: 'short' })
        : d.toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long' });
}

/** Une barre du graphique : sa hauteur est proportionnelle au jour le plus chargé. */
function barre(jour, max) {
    const hauteur = max ? Math.max(4, Math.round((jour.commandes / max) * 100)) : 4;
    const record  = max > 0 && jour.commandes === max;

    return `
        <button type="button" data-date="${jour.date}" class="group flex flex-1 flex-col items-center gap-2">
            <span class="flex w-full flex-1 items-end">
                <span style="height: ${hauteur}%"
                      class="flex w-full items-start justify-center rounded-t-xl pt-2 text-lg font-semibold transition group-hover:opacity-80
                             ${record ? 'bg-black text-white' : 'bg-primary text-black'}">
                    ${jour.commandes}
                </span>
            </span>
            <span class="font-serif text-sm capitalize">${nomJour(jour.date)}</span>
        </button>`;
}

/** GET /api/employe/statistiques/hebdomadaire -> statistiquesHebdomadaires() */
async function chargerSemaine() {
    try {
        const s = await api('GET', '/statistiques/hebdomadaire');

        document.getElementById('periode').textContent =
            `Du ${new Date(s.periode.debut + 'T00:00:00').toLocaleDateString('fr-FR')} au ${new Date(s.periode.fin + 'T00:00:00').toLocaleDateString('fr-FR')}`;

        // Remplit les 3 cartes grâce à leur attribut data-compteur
        const valeurs = {
            total: s.resume.total_commandes,
            top:   s.plats_populaires[0]?.nom ?? '—',   // la liste est triée du plus vendu au moins vendu
            ca:    formatPrix(s.resume.chiffre_affaires),
        };
        document.querySelectorAll('[data-compteur]').forEach(el => {
            el.textContent = valeurs[el.dataset.compteur];
        });

        const max = Math.max(...s.par_jour.map(j => j.commandes));
        graphique.innerHTML = s.par_jour.map(j => barre(j, max)).join('');
    } catch (erreur) {
        afficherMessage(zoneMessage, erreur.message, false);
    }
}

/** GET /api/employe/statistiques/journaliere?date=... -> statistiquesJournalieres() */
async function chargerJour(date) {
    try {
        const s = await api('GET', `/statistiques/journaliere?date=${date}`);

        document.getElementById('jour-nom').textContent   = nomJour(date, 'long');
        document.getElementById('jour-total').textContent = pluriel(s.resume.total_commandes, 'commande');
        document.getElementById('jour-plats').innerHTML = s.plats_populaires.length
            ? s.plats_populaires.map(p => `
                <li class="flex justify-between gap-4">
                    <span>• ${echapper(p.nom)}</span>
                    <span>${pluriel(Number(p.quantite_vendue), 'vendu')}</span>
                </li>`).join('')
            : '<li class="text-black/50">Aucun plat livré ce jour-là.</li>';

        const detail = document.getElementById('detail-jour');
        detail.classList.remove('hidden');
        detail.scrollIntoView({ behavior: 'smooth' });
    } catch (erreur) {
        afficherMessage(zoneMessage, erreur.message, false);
    }
}

// Clic sur une barre -> détail du jour
graphique.addEventListener('click', (e) => {
    const bouton = e.target.closest('button[data-date]');
    if (bouton) chargerJour(bouton.dataset.date);
});

chargerSemaine();
</script>
@endpush
