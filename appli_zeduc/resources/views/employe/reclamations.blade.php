@extends('layouts.employe')

@section('title', 'Réclamations')

@section('content')

<h1 class="font-serif text-3xl">Réclamations</h1>
<p class="mt-1 text-sm text-white/70">Cliquez sur une réclamation pour la lire et proposer une réponse.</p>

{{--
    ÉCART MAQUETTE / DONNÉES
    La maquette affichait « Plat » et « Prix ». Une réclamation de la base contient
    un type, une description et un étudiant, mais ni plat ni prix : le tableau
    affiche donc ce qui existe vraiment.
--}}
<div class="mt-6 overflow-x-auto rounded-xl bg-white text-black">
    <table class="w-full min-w-[600px] text-left text-sm">
        <thead class="bg-neutral-200 text-base">
            <tr>
                <th class="px-4 py-3">N°</th>
                <th class="px-4 py-3">Étudiant</th>
                <th class="px-4 py-3">Type</th>
                <th class="px-4 py-3">Date</th>
                <th class="px-4 py-3">Statut</th>
            </tr>
        </thead>
        <tbody id="corps-reclamations">
            <tr><td colspan="5" class="px-4 py-6 text-center text-black/50">Chargement…</td></tr>
        </tbody>
    </table>
</div>

<p id="message" class="mt-4 min-h-6 text-center"></p>


{{-- ===== Détail de la réclamation sélectionnée (caché au départ) ===== --}}
<section id="detail" class="mt-6 hidden">
    <div class="grid gap-6 rounded-2xl bg-neutral-200 p-6 text-black lg:grid-cols-3">

        <div class="lg:col-span-2">
            <h2 class="font-serif text-2xl">
                Réclamation n°<span id="d-id"></span> — <span id="d-type"></span>
            </h2>
            <p class="text-sm text-black/60">De <span id="d-etudiant"></span>, le <span id="d-date"></span></p>

            <blockquote id="d-description" class="mt-4 rounded-xl bg-primary/50 px-4 py-3 font-serif italic"></blockquote>

            <h3 class="mt-6 font-semibold">Réponses déjà proposées</h3>
            <ul id="d-reponses" class="mt-2 space-y-2 text-sm"></ul>
        </div>

        <form id="form-reponse" class="flex flex-col">
            <label for="contenu" class="font-serif text-xl">Votre réponse</label>
            {{-- minlength / maxlength : mêmes limites que la validation du contrôleur (min:5, max:2000) --}}
            <textarea id="contenu" name="contenu" rows="5" minlength="5" maxlength="2000" required
                      placeholder="Écrire la réponse au client…"
                      class="mt-2 w-full resize-none rounded-lg border border-primary bg-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary"></textarea>

            {{--
                ÉCART MAQUETTE / CAHIER DES CHARGES
                Le cahier précise que le GÉRANT valide ou rejette les réponses proposées
                par les employés. La réponse ne part donc pas directement à l'étudiant.
            --}}
            <p class="mt-2 text-xs text-black/60">Votre réponse sera transmise au gérant, qui la validera avant de l'envoyer à l'étudiant.</p>

            <button type="submit" id="btn-envoyer"
                    class="mt-4 rounded-full bg-primary py-3 font-serif text-xl transition hover:opacity-80 disabled:opacity-40">
                Envoyer la réponse
            </button>
            <p id="message-reponse" class="mt-2 min-h-6 text-center text-sm"></p>
        </form>
    </div>
</section>

@endsection

@push('scripts')
<script>
/*
 * Libellés et couleurs des statuts de réclamation.
 * Les valeurs 'accepté' et 'rejeté' sont celles de RECLAMATION_CLOSE dans EmployeController.
 * KISS : un objet de correspondance au lieu de if / else if.
 */
const STATUTS = {
    'en_attente': { libelle: 'En attente', classes: 'border-red-500 text-red-600' },
    'en_cours':   { libelle: 'En cours',   classes: 'border-yellow-600 text-yellow-700' },
    'accepté':    { libelle: 'Acceptée',   classes: 'border-green-600 text-green-700' },
    'rejeté':     { libelle: 'Rejetée',    classes: 'border-gray-400 text-gray-500' },
};
const CLOTUREES = ['accepté', 'rejeté'];

const corps       = document.getElementById('corps-reclamations');
const zoneMessage = document.getElementById('message');
const formulaire  = document.getElementById('form-reponse');
const msgReponse  = document.getElementById('message-reponse');
let idSelectionne = null;

/** Pastille d'un statut, avec une valeur de secours pour un statut inconnu. */
function pastille(statut) {
    const s = STATUTS[statut] ?? { libelle: statut, classes: 'border-gray-400' };
    return badge(s.libelle, s.classes);
}

function ligne(r) {
    const choisie = r.id === idSelectionne ? 'bg-primary/20' : '';
    return `
        <tr data-id="${r.id}" class="cursor-pointer border-t border-black/10 hover:bg-primary/10 ${choisie}">
            <td class="px-4 py-3 font-bold">#${r.id}</td>
            <td class="px-4 py-3">${echapper(`${r.etudiant_prenom ?? ''} ${r.etudiant_nom ?? ''}`)}</td>
            <td class="px-4 py-3">${echapper(r.type_reclamation)}</td>
            <td class="px-4 py-3">${formatDateHeure(r.created_at)}</td>
            <td class="px-4 py-3">${pastille(r.statut)}</td>
        </tr>`;
}

/** GET /api/employe/reclamations -> reclamations() */
async function chargerListe() {
    try {
        const liste = await api('GET', '/reclamations?par_page=100');
        corps.innerHTML = liste.data.length
            ? liste.data.map(ligne).join('')
            : '<tr><td colspan="5" class="px-4 py-6 text-center text-black/50">Aucune réclamation.</td></tr>';
    } catch (erreur) {
        afficherMessage(zoneMessage, erreur.message, false);
    }
}

/** GET /api/employe/reclamations/{id} -> reclamation() : détail + historique des réponses */
async function chargerDetail(id) {
    try {
        const { reclamation: r, reponses } = await api('GET', `/reclamations/${id}`);
        idSelectionne = r.id;

        document.getElementById('d-id').textContent       = r.id;
        document.getElementById('d-type').textContent     = r.type_reclamation;
        document.getElementById('d-etudiant').textContent = `${r.etudiant_prenom ?? ''} ${r.etudiant_nom ?? ''}`;
        document.getElementById('d-date').textContent     = formatDateHeure(r.created_at);
        // textContent (et non innerHTML) : le texte de l'étudiant est affiché tel quel, sans risque
        document.getElementById('d-description').textContent = `« ${r.description} »`;

        document.getElementById('d-reponses').innerHTML = reponses.length
            ? reponses.map(rep => `
                <li class="rounded-lg bg-white px-3 py-2">
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-semibold">${echapper(rep.auteur)}</span>
                        ${pastille(rep.statut)}
                    </div>
                    <p class="mt-1">${echapper(rep.contenu)}</p>
                </li>`).join('')
            : '<li class="text-black/50">Aucune réponse pour l\'instant.</li>';

        // Mêmes règles que le contrôleur, appliquées AVANT l'envoi pour éviter un aller-retour inutile :
        // pas de réponse sur une réclamation close, ni si une réponse attend déjà le gérant.
        const close     = CLOTUREES.includes(r.statut);
        const enAttente = reponses.some(rep => rep.statut === 'en_attente');
        document.getElementById('btn-envoyer').disabled = close || enAttente;
        msgReponse.textContent = close ? 'Réclamation clôturée.'
                               : enAttente ? 'Une réponse attend déjà la validation du gérant.' : '';
        msgReponse.className = 'mt-2 min-h-6 text-center text-sm text-black/60';

        document.getElementById('detail').classList.remove('hidden');
        chargerListe(); // pour surligner la ligne choisie
    } catch (erreur) {
        afficherMessage(zoneMessage, erreur.message, false);
    }
}

// Clic sur une ligne du tableau -> affiche son détail
corps.addEventListener('click', (e) => {
    const tr = e.target.closest('tr[data-id]');
    if (tr) chargerDetail(Number(tr.dataset.id));
});

// POST /api/employe/reclamations/{id}/reponses -> repondreReclamation()
formulaire.addEventListener('submit', async (e) => {
    e.preventDefault(); // on envoie en JavaScript, sans recharger la page
    const contenu = document.getElementById('contenu').value.trim();

    try {
        const reponse = await api('POST', `/reclamations/${idSelectionne}/reponses`, { contenu });
        formulaire.reset();
        await chargerDetail(idSelectionne);
        afficherMessage(msgReponse, reponse.message, true);
        msgReponse.classList.replace('text-green-400', 'text-green-700'); // lisible sur fond clair
    } catch (erreur) {
        afficherMessage(msgReponse, erreur.message, false);
        msgReponse.classList.replace('text-red-400', 'text-red-700');
    }
});

chargerListe();
</script>
@endpush
