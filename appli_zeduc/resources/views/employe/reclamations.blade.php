@extends('layouts.employe')

@section('titre', 'Reclamations')

@section('contenu')
  <main class="max-w-6xl mx-auto px-10 pt-14 pb-16">
    <section class="bg-white rounded-lg overflow-hidden mb-6">
      <table class="w-full text-center">
        <thead class="bg-[#E4E4E4]">
          <tr class="text-xs font-semibold text-black">
            <th class="py-3">Id Commande</th>
            <th class="py-3">Plat</th>
            <th class="py-3">Date et Heure Achat</th>
            <th class="py-3">Prix</th>
            <th class="py-3"></th>
          </tr>
        </thead>
        <tbody id="corps-reclamations"></tbody>
      </table>
    </section>

    <section class="bg-miam-gris rounded-lg p-4 grid grid-cols-[1fr_38%] gap-6 font-serif">
      <div>
        <h2 class="text-xl text-black">
          Reclamation - commande <span id="detail-id"></span>
        </h2>
        <p id="detail-message" class="inline-block mt-2 bg-miam-beige/70 rounded-full px-4 py-1.5 text-sm text-black"></p>
      </div>
      <div>
        <label for="reponse" class="block text-lg text-black">Votre reponse</label>
        <textarea id="reponse" rows="2" placeholder="Ecrire la reponse aux clients..."
                  class="w-full mt-1 bg-transparent border border-gray-400/60 rounded px-2 py-1 text-xs
                         resize-none focus:outline-none focus:border-miam-sable"></textarea>
        <button onclick="envoyerReponse()"
                class="w-full mt-3 bg-miam-beige rounded-full py-2 text-xl text-black/80 hover:brightness-95">
          Envoyer la reponse
        </button>
        <p id="confirmation" class="text-sm text-green-700 mt-1 h-5"></p>
      </div>
    </section>
  </main>
@endsection

@push('scripts')
  <script>
    const reclamations = @json($reclamations);

    const STYLE_STATUT = {
      "En cours": "bg-miam-beige border-[#E9D98C] text-[#F5E77C]",
      "Traitée": "bg-[#9FD893] border-[#7CC46D] text-green-600",
      "En attente": "bg-[#F2B9A9] border-[#E66A5A] text-red-600",
    };

    let indexSelection = reclamations.length - 1;

    function afficherReclamations() {
      document.getElementById("corps-reclamations").innerHTML = reclamations.map((r, i) => `
        <tr onclick="selectionner(${i})"
            class="cursor-pointer text-[11px] text-black ${i === indexSelection ? "bg-miam-creme" : "hover:bg-gray-50"}">
          <td class="py-4">${r.id}</td>
          <td class="py-4">${r.plat}</td>
          <td class="py-4">${r.date}</td>
          <td class="py-4">${r.prix}</td>
          <td class="py-4">
            <span class="inline-block w-24 py-1 rounded-md border-2 text-[10px] font-semibold ${STYLE_STATUT[r.statut]}">
              ${r.statut}
            </span>
          </td>
        </tr>`).join("");
    }

    function selectionner(i) {
      indexSelection = i;
      const r = reclamations[i];
      document.getElementById("detail-id").textContent = r.id;
      document.getElementById("detail-message").textContent = r.message;
      document.getElementById("reponse").value = "";
      document.getElementById("confirmation").textContent = "";
      afficherReclamations();
    }

    function envoyerReponse() {
      const champ = document.getElementById("reponse");
      if (champ.value.trim() === "") {
        champ.classList.add("border-red-500");
        setTimeout(() => champ.classList.remove("border-red-500"), 1500);
        return;
      }
      reclamations[indexSelection].statut = "Traitée";
      champ.value = "";
      document.getElementById("confirmation").textContent = "Réponse envoyée ✓";
      afficherReclamations();
    }

    selectionner(indexSelection);
  </script>
@endpush
