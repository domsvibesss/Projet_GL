{{--
    La barre du haut (logo, liens, profil).
    Reçoit :  liens       = [ 'chemin-de-la-page' => 'Texte du lien' ]
              utilisateur = [ 'nom' => ..., 'role' => ..., 'photo' => ... ]
--}}
@props(['liens', 'utilisateur'])

<header class="flex items-center justify-between gap-4 px-6 py-4">

    {{-- Logo + nom (non cliquable) --}}
    <div class="flex shrink-0 items-center gap-3">
        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-14">
        <span class="text-3xl font-bold">Mon miam miam</span>
    </div>

    {{-- Liens de navigation : le lien de la page active est en doré --}}
    <nav class="hidden items-center gap-6 text-lg lg:flex xl:gap-10 xl:text-xl">
        @foreach ($liens as $chemin => $texte)
            {{-- is($chemin, $chemin.'/*') : le lien reste doré aussi sur les sous-pages (ex : /admin/menus/ajouter) --}}
            <a href="/{{ $chemin }}"
               class="hover:text-primary {{ request()->is($chemin, $chemin . '/*') ? 'text-primary' : 'text-white' }}">
                {{ $texte }}
            </a>
        @endforeach
    </nav>

    {{-- Profil de la personne connectée : un clic ouvre le menu (profil + déconnexion) --}}
    {{-- <details> est une balise HTML qui s'ouvre et se ferme toute seule, sans JavaScript --}}
    <details data-menu-profil class="relative shrink-0">

        {{-- <summary> = la partie cliquable (nom + photo). list-none cache la petite flèche par défaut --}}
        <summary class="flex cursor-pointer list-none items-center gap-3 text-right text-sm [&::-webkit-details-marker]:hidden">
            <div>
                <p>{{ $utilisateur['nom'] }}</p>
                <p>{{ $utilisateur['role'] }}</p>
            </div>
            <img src="{{ asset($utilisateur['photo']) }}" alt="Profil" class="h-14 w-14 rounded-full object-cover">
        </summary>

        {{-- Le petit menu qui s'affiche sous la photo : profil + déconnexion --}}
        <div class="absolute right-0 z-50 mt-3 w-64 rounded-xl bg-white p-5 text-center text-black shadow-lg">

            <img src="{{ asset($utilisateur['photo']) }}" alt="Profil"
                 class="mx-auto h-24 w-24 rounded-full object-cover">

            <p class="mt-3 text-lg font-semibold">{{ $utilisateur['nom'] }}</p>
            <p class="text-gray-600">{{ $utilisateur['role'] }}</p>

            {{-- La déconnexion est un formulaire POST (obligatoire dans Laravel) --}}
            <form method="POST" action="/deconnexion" class="mt-4">
                @csrf
                <button type="submit"
                        class="w-full rounded-full bg-black py-2 font-semibold text-white transition hover:opacity-80">
                    Déconnexion
                </button>
            </form>
        </div>
    </details>

</header>

{{-- Ferme le menu quand on clique ailleurs sur la page --}}
<script>
    document.addEventListener('click', (e) => {
        document.querySelectorAll('details[data-menu-profil]').forEach((menu) => {
            if (!menu.contains(e.target)) menu.removeAttribute('open');
        });
    });
</script>