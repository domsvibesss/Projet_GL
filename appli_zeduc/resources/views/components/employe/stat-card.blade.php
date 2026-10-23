{{--
    Carte de chiffre clé (beige) : « 4 En attente », « 112 Commandes »…
    Utilisée 4 fois sur la page Commandes et 3 fois sur la page Statistiques.

    DRY : un seul composant au lieu de 7 blocs HTML identiques.
    SOLID (S) : le composant ne fait qu'AFFICHER une valeur ; c'est le script
    de la page qui la remplit, grâce à l'attribut data-compteur.

    Reçoit :
      - libelle : texte sous le chiffre
      - cle     : identifiant que le JavaScript utilise pour retrouver la carte
      - valeur  : valeur de départ (« — » tant que l'API n'a pas répondu)
--}}
@props(['libelle', 'cle', 'valeur' => '—'])

<div class="rounded-xl bg-primary/80 px-6 py-4 text-center text-black">
    <p data-compteur="{{ $cle }}" class="break-words font-serif text-3xl sm:text-4xl">{{ $valeur }}</p>
    <p class="mt-1 font-serif text-base">{{ $libelle }}</p>
</div>
