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

    {{-- Profil de la personne connectée --}}
    <div class="flex shrink-0 items-center gap-3 text-right text-sm">
        <div>
            <p>{{ $utilisateur['nom'] }}</p>
            <p>{{ $utilisateur['role'] }}</p>
        </div>
        <img src="{{ asset($utilisateur['photo']) }}" alt="Profil" class="h-14 w-14 rounded-full object-cover">
    </div>

</header>