@extends('layouts.employe')

{{-- Pour écrire Commande::EN_COURS au lieu de App\Models\Commande::EN_COURS --}}
@use('App\Models\Commande')

@section('title', 'Commandes')

@section('content')

{{--
    Compteurs du jour.
    :cle reçoit les constantes du modèle Commande : le nom des états n'est écrit
    qu'à un seul endroit du projet (DRY, source unique de vérité). Si quelqu'un
    renomme un état dans le modèle, cette page suit automatiquement.
--}}
<div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
    <x-employe.stat-card :cle="Commande::EN_ATTENTE"   libelle="En attente" />
    <x-employe.stat-card :cle="Commande::EN_COURS"     libelle="En préparation" />
    <x-employe.stat-card :cle="Commande::PRETE"        libelle="Prêtes" />
    <x-employe.stat-card :cle="Commande::EFFECTUE"     libelle="Livrées" />
</div>

<h1 class="mt-10 font-serif text-3xl">Commandes du jour</h1>

{{-- overflow-x-auto : sur téléphone, le tableau défile au lieu de déborder de l'écran --}}
<div class="mt-4 overflow-x-auto rounded-xl bg-white text-black">
    <table class="w-full min-w-[700px] text-left text-sm">
        <thead class="bg-primary/30 text-base">
            <tr>
                <th class="px-4 py-3">N°</th>
                <th class="px-4 py-3">Client</th>
                <th class="px-4 py-3">Type</th>
                <th class="px-4 py-3">Articles</th>
                <th class="px-4 py-3">Heure</th>
                <th class="px-4 py-3">Statut</th>
                <th class="px-4 py-3">Action</th>
            </tr>
        </thead>
        {{-- Rempli par le JavaScript ci-dessous --}}
        <tbody id="corps-commandes">
            <tr><td colspan="7" class="px-4 py-6 text-center text-black/50">Chargement…</td></tr>
        </tbody>
    </table>
</div>

<p id="message" class="mt-4 min-h-6 text-center"></p>

@endsection

@push('scripts')
<script>
/*
 * Un état = son libellé, ses couleurs et l'action possible (route PATCH de l'API).
 *
 * SOLID (O, ouvert/fermé) : pour gérer un nouvel état, on ajoute une ligne ici ;
 * la fonction ligne() plus bas n'a pas besoin d'être modifiée.
 * KISS : un simple objet au lieu d'une série de if / else if.
 * Les clés viennent des constantes du modèle Commande, écrites en texte JS par Blade.
 */
const ETATS = {
    [@json(Commande::EN_ATTENTE)]: { libelle: 'En attente',     classes: 'border-red-500 text-red-600',      action: { texte: 'Préparer', route: 'en-preparation' } },
    [@json(Commande::EN_COURS)]:  { libelle: 'En préparation', classes: 'border-yellow-600 text-yellow-700', action: { texte: 'Valider',  route: 'prete' } },
    [@json(Commande::PRETE)]:     { libelle: 'Prête',          classes: 'border-blue-500 text-blue-600',    action: { texte: 'Livrer',   route: 'livree' } },
    [@json(Commande::EFFECTUE)]:  { libelle: 'Livrée',         classes: 'border-green-600 text-green-700',  action: null },
    [@json(Commande::REJETE)]:    { libelle: 'Rejetée',        classes: 'border-gray-400 text-gray-500',    action: null },
};

const corps = document.getElementById('corps-commandes');
const zoneMessage = document.getElementById('message');

/** Construit une ligne <tr> du tableau à partir d'une commande renvoyée par l'API. */
function ligne(c) {
    // Valeur de secours si l'API renvoie un état inconnu : la page ne plante pas
    const etat = ETATS[c.etat] ?? { libelle: c.etat, classes: 'border-gray-400', action: null };

    // « Marie Kamga » -> « Marie K. » (comme sur la maquette)
    const client = `${c.etudiant_prenom ?? ''} ${(c.etudiant_nom ?? '').charAt(0)}.`;

    const action = etat.action
        ? `<button type="button" data-id="${c.id}" data-route="${etat.action.route}"
                   class="rounded-full bg-primary px-4 py-1 font-semibold transition hover:opacity-80 disabled:opacity-50">
               ${etat.action.texte}
           </button>`
        : '—';

    return `
        <tr class="border-t border-black/10">
            <td class="px-4 py-3 font-bold">#${c.id}</td>
            <td class="px-4 py-3">${echapper(client)}</td>
            <td class="px-4 py-3 capitalize">${echapper((c.destination ?? '—').replace('_', ' '))}</td>
            <td class="px-4 py-3">${echapper(c.articles || '—')}</td>
            <td class="px-4 py-3">${formatHeure(c.date_commande)}</td>
            <td class="px-4 py-3">${badge(etat.libelle, etat.classes)}</td>
            <td class="px-4 py-3">${action}</td>
        </tr>`;
}

/** Charge les compteurs puis le tableau des commandes du jour. */
async function charger() {
    try {
        // Les compteurs viennent des statistiques du jour (resume.par_etat) :
        // 1 seul appel au lieu de 4, et on réutilise un calcul qui existe déjà (DRY).
        const stats = await api('GET', '/statistiques/journaliere');

        document.querySelectorAll('[data-compteur]').forEach(el => {
            el.textContent = stats.resume.par_etat[el.dataset.compteur] ?? 0;
        });

        // stats.periode.debut = la date du jour SELON LE SERVEUR : le tableau et
        // les compteurs portent donc toujours sur la même journée.
        // ordre=asc : la plus ancienne commande en premier, celle à traiter d'abord.
        const liste = await api('GET', `/commandes?date=${stats.periode.debut}&ordre=asc&par_page=100`);

        corps.innerHTML = liste.data.length
            ? liste.data.map(ligne).join('')
            : `<tr><td colspan="7" class="px-4 py-6 text-center text-black/50">Aucune commande aujourd'hui.</td></tr>`;
    } catch (erreur) {
        afficherMessage(zoneMessage, erreur.message, false);
    }
}

// Un seul écouteur pour tous les boutons du tableau (délégation d'événement).
// KISS : pas besoin de rebrancher les boutons à chaque rechargement du tableau.
corps.addEventListener('click', async (e) => {
    const bouton = e.target.closest('button[data-route]');
    if (!bouton) return;

    bouton.disabled = true; // évite le double clic pendant la requête
    try {
        // ex. PATCH /api/employe/commandes/12/prete  ->  EmployeController::prete()
        const reponse = await api('PATCH', `/commandes/${bouton.dataset.id}/${bouton.dataset.route}`);
        afficherMessage(zoneMessage, reponse.message, true);
        await charger(); // met à jour le tableau ET les compteurs
    } catch (erreur) {
        afficherMessage(zoneMessage, erreur.message, false);
        bouton.disabled = false;
    }
});

charger();
</script>
@endpush
