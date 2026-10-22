@extends('layouts.employe')

@section('titre', 'Commandes')

@section('contenu')
  <main class="relative min-h-[calc(100vh-4rem)] bg-cover bg-center"
        style="background-image: url('{{ asset('images/fond-commandes.jpg') }}'), url('{{ asset('images/fond-commandes.png') }}');">
    <div class="absolute inset-0 bg-black/40"></div>

    <div class="relative max-w-6xl mx-auto px-6 pt-12 pb-16">
      <section class="flex justify-between px-10 mb-16">
        @foreach ($compteurs as $compteur)
          <div class="w-44 py-2 rounded-xl bg-miam-sable/70 border border-white/20 text-center text-white font-serif">
            <span data-compteur="{{ $compteur['statut'] }}" class="block text-4xl">{{ $compteur['valeur'] }}</span>
            <span class="text-lg">{{ $compteur['label'] }}</span>
          </div>
        @endforeach
      </section>

      <section class="mx-8 bg-white">
        <table class="w-full text-left border-collapse">
          <thead class="bg-miam-creme">
            <tr class="text-[15px] font-semibold text-black">
              <th class="px-4 py-3 border border-gray-300 w-[12%]">N°</th>
              <th class="px-4 py-3 border border-gray-300">Client</th>
              <th class="px-4 py-3 border border-gray-300">Type</th>
              <th class="px-4 py-3 border border-gray-300">Articles</th>
              <th class="px-4 py-3 border border-gray-300">Heure</th>
              <th class="px-4 py-3 border border-gray-300">Statut</th>
              <th class="px-4 py-3 border border-gray-300">Action</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($commandes as $index => $commande)
              <tr>
                <td class="px-4 py-3.5 border border-gray-300 bg-[#EFEBE4] font-semibold text-[15px]">{{ $commande['id'] }}</td>
                <td class="px-4 py-3.5 border border-gray-300 text-xs text-black">{{ $commande['client'] }}</td>
                <td class="px-4 py-3.5 border border-gray-300 text-xs text-black">{{ $commande['type'] }}</td>
                <td class="px-4 py-3.5 border border-gray-300 text-xs text-black">{{ $commande['articles'] }}</td>
                <td class="px-4 py-3.5 border border-gray-300 text-xs text-black">{{ $commande['heure'] }}</td>
                <td id="statut-{{ $index }}" class="px-4 py-3.5 border border-gray-300 text-xs text-black">{{ $commande['statut'] }}</td>
                <td id="action-{{ $index }}" class="px-4 py-3.5 border border-gray-300 text-xs text-black"></td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </section>
    </div>
  </main>
@endsection

@push('scripts')
  <script>
    const ACTIONS = {
      "En attente": "Préparer",
      "En préparation": "Valider",
      "Prête": "Livrer",
      "Livrée": "—",
    };

    const STATUT_SUIVANT = {
      "En attente": "En préparation",
      "En préparation": "Prête",
      "Prête": "Livrée",
    };

    const commandes = @json($commandes);
    const compteurs = Object.fromEntries(@json($compteurs).map((c) => [c.statut, c.valeur]));

    function afficherAction(index) {
      const statut = commandes[index].statut;
      document.getElementById(`statut-${index}`).textContent = statut;
      document.getElementById(`action-${index}`).innerHTML = statut === "Livrée"
        ? ACTIONS[statut]
        : `<button onclick="avancerCommande(${index})" class="hover:underline hover:text-miam-sable">${ACTIONS[statut]}</button>`;
    }

    function afficherCompteurs() {
      document.querySelectorAll("[data-compteur]").forEach((span) => {
        span.textContent = compteurs[span.dataset.compteur];
      });
    }

    function avancerCommande(index) {
      const commande = commandes[index];
      const nouveau = STATUT_SUIVANT[commande.statut];
      if (!nouveau) return;

      compteurs[commande.statut]--;
      compteurs[nouveau]++;
      commande.statut = nouveau;

      afficherAction(index);
      afficherCompteurs();
    }

    commandes.forEach((_, index) => afficherAction(index));
  </script>
@endpush
