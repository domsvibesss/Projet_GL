@extends('layouts.employe')

@section('titre', 'Plat du jour')
@section('police', 'font-serif')

@section('contenu')
  <main class="max-w-6xl mx-auto px-6 py-6">
    <section class="flex items-center justify-between bg-miam-beige rounded-lg px-4 py-3 mb-4">
      <div class="flex items-center gap-3">
        <div id="photo-plat-du-jour" class="relative h-14 w-14 rounded-md bg-black/10 grid place-items-center text-3xl overflow-hidden"></div>
        <div>
          <p class="text-xs text-black/80">plat du jour</p>
          <p id="nom-plat-du-jour" class="text-xl text-black">{{ $platDuJour }}</p>
        </div>
      </div>
      <button onclick="ouvrirPanneau('PLAT_DU_JOUR')"
              class="bg-black text-miam-beige text-sm px-8 py-2 rounded-lg hover:bg-neutral-800">
        changer le plat du jour
      </button>
    </section>

    <section class="border border-miam-beige/60 rounded-md px-4 py-3 mb-3 text-miam-beige">
      <h2 class="text-lg">Menu de la semaine</h2>
      <p class="text-xs">Semaine du 12 octobre au 18 octobre 2026</p>
      <p class="text-xs">Le plat du jour est deja défini : <span id="rappel-plat">{{ $platDuJour }}</span>.</p>
    </section>

    <section id="grille-jours" class="grid grid-cols-7 gap-2"></section>

    <p class="text-[11px] text-miam-beige mt-3">Le planning se réinitialise automatiquement chaque lundi à 00:00.</p>
  </main>

  <div id="overlay" onclick="fermerPanneau()" class="fixed inset-0 bg-black/60 hidden z-40"></div>

  <aside id="panneau"
         class="fixed top-4 right-4 bottom-4 w-[400px] bg-[#0b0b0b] border border-white/10 rounded-lg
                flex flex-col translate-x-[110%] transition-transform duration-300 z-50">
    <div class="px-4 pt-4">
      <h3 id="titre-panneau" class="text-white text-xl">Ajouter des plats pour jeudi</h3>
      <p id="sous-titre-panneau" class="text-miam-beige text-sm mt-1">Cochez les plats a servir ce jour la puis validez</p>
      <input id="recherche" type="text" placeholder="Rechercher un plat" oninput="afficherListePlats()"
             class="mt-4 w-full bg-transparent border border-white/40 rounded-md px-3 py-2.5 text-white
                    placeholder-white focus:outline-none focus:border-miam-beige">
    </div>

    <ul id="liste-plats" class="flex-1 overflow-y-auto px-4 py-4 space-y-4"></ul>

    <div class="flex items-center justify-between border-t border-white/20 px-4 py-3">
      <span id="compteur-selection" class="text-gray-300 text-sm">0 plat selectionne</span>
      <div class="flex gap-3">
        <button onclick="fermerPanneau()" class="bg-miam-taupe text-miam-beige px-4 py-1 rounded">Annuler</button>
        <button onclick="validerSelection()" class="bg-miam-beige text-black/70 px-4 py-1 rounded">Valider</button>
      </div>
    </div>
  </aside>
@endsection

@push('scripts')
  <script>
    const DOSSIER_PLATS = @json(asset('images/plats'));
    const PLATS = @json($plats);
    const JOURS = @json($jours);

    const menuSemaine = Object.fromEntries(JOURS.map((j) => [j, []]));

    let platDuJour = @json($platDuJour);
    let jourEnCours = null;
    let selection = new Set();

    const pluriel = (n, mot) => `${n} ${mot}${n > 1 ? "s" : ""}`;

    function nomFichier(nom) {
      return nom
        .toLowerCase()
        .normalize("NFD").replace(/[̀-ͯ]/g, "")
        .replace(/[^a-z0-9]+/g, "-")
        .replace(/^-|-$/g, "");
    }

    function balisePhoto(nom) {
      return `<img src="${DOSSIER_PLATS}/${nomFichier(nom)}.jpg" alt=""
                   class="absolute inset-0 h-full w-full object-cover" onerror="essayerPng(this)">`;
    }

    function essayerPng(img) {
      if (img.src.endsWith(".jpg")) {
        img.src = img.src.replace(/\.jpg$/, ".png");
      } else {
        img.remove();
      }
    }

    function afficherPhotoPlatDuJour() {
      const plat = PLATS.find((p) => p.nom.toLowerCase() === platDuJour.toLowerCase());
      const emoji = plat ? plat.emoji : "🍽️";
      document.getElementById("photo-plat-du-jour").innerHTML = `${emoji}${balisePhoto(platDuJour)}`;
    }

    function afficherJours() {
      document.getElementById("grille-jours").innerHTML = JOURS.map((jour) => {
        const plats = menuSemaine[jour];
        const estDimanche = jour === "Dimanche";

        const carte = estDimanche
          ? "bg-black border border-miam-beige text-miam-beige"
          : "bg-miam-beige text-black";
        const bouton = estDimanche
          ? "bg-miam-beige text-black/70"
          : "bg-black text-miam-beige";

        const contenu = plats.length === 0
          ? `<p class="text-[11px] mt-4 opacity-80">Aucun plat prévu</p>`
          : `<ul class="text-[11px] mt-4 space-y-1">${plats.map((p) => `<li>• ${p}</li>`).join("")}</ul>`;

        return `
          <div class="${carte} rounded-md p-2 min-h-[330px] flex flex-col">
            <div class="flex justify-between items-baseline">
              <span class="text-sm">${jour}</span>
              <span class="text-[9px]">${pluriel(plats.length, "plat")}</span>
            </div>
            ${contenu}
            <button onclick="ouvrirPanneau('${jour}')"
                    class="${bouton} mt-auto text-[10px] leading-tight rounded-md px-2 py-1.5 hover:opacity-80">
              + Ajouter un ou<br>plusieurs plats
            </button>
          </div>`;
      }).join("");
    }

    function ouvrirPanneau(jour) {
      jourEnCours = jour;

      if (jour === "PLAT_DU_JOUR") {
        document.getElementById("titre-panneau").textContent = "Changer le plat du jour";
        document.getElementById("sous-titre-panneau").textContent = "Cochez un seul plat puis validez";
        selection = new Set();
      } else {
        document.getElementById("titre-panneau").textContent = `Ajouter des plats pour ${jour.toLowerCase()}`;
        document.getElementById("sous-titre-panneau").textContent = "Cochez les plats a servir ce jour la puis validez";
        selection = new Set(menuSemaine[jour]);
      }

      document.getElementById("recherche").value = "";
      afficherListePlats();

      document.getElementById("panneau").classList.remove("translate-x-[110%]");
      document.getElementById("overlay").classList.remove("hidden");
    }

    function fermerPanneau() {
      document.getElementById("panneau").classList.add("translate-x-[110%]");
      document.getElementById("overlay").classList.add("hidden");
    }

    function afficherListePlats() {
      const filtre = document.getElementById("recherche").value.toLowerCase();
      const visibles = PLATS.filter((p) => p.nom.toLowerCase().includes(filtre));

      document.getElementById("liste-plats").innerHTML = visibles.map((plat) => `
        <li>
          <label class="flex items-center gap-4 cursor-pointer text-white text-sm">
            <input type="checkbox" ${selection.has(plat.nom) ? "checked" : ""}
                   onchange="basculerPlat('${plat.nom}', this.checked)"
                   class="appearance-none h-5 w-5 rounded-sm bg-gray-400 checked:bg-miam-beige
                          checked:ring-2 checked:ring-white cursor-pointer">
            <span class="relative h-8 w-8 rounded-full bg-white/10 grid place-items-center text-sm overflow-hidden">
              ${plat.emoji}
              ${balisePhoto(plat.nom)}
            </span>
            <span>${plat.nom}</span>
          </label>
        </li>`).join("");

      majCompteurSelection();
    }

    function basculerPlat(nom, coche) {
      if (jourEnCours === "PLAT_DU_JOUR") {
        selection = coche ? new Set([nom]) : new Set();
        afficherListePlats();
        return;
      }
      coche ? selection.add(nom) : selection.delete(nom);
      majCompteurSelection();
    }

    function majCompteurSelection() {
      const n = selection.size;
      document.getElementById("compteur-selection").textContent =
        `${pluriel(n, "plat")} selectionne${n > 1 ? "s" : ""}`;
    }

    function validerSelection() {
      if (jourEnCours === "PLAT_DU_JOUR") {
        if (selection.size === 1) {
          platDuJour = [...selection][0];
          document.getElementById("nom-plat-du-jour").textContent = platDuJour;
          document.getElementById("rappel-plat").textContent = platDuJour;
          afficherPhotoPlatDuJour();
        }
      } else {
        menuSemaine[jourEnCours] = [...selection];
        afficherJours();
      }
      fermerPanneau();
    }

    document.addEventListener("keydown", (e) => { if (e.key === "Escape") fermerPanneau(); });

    afficherPhotoPlatDuJour();
    afficherJours();
  </script>
@endpush
