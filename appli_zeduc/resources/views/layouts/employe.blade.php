<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Chaque page remplit ce titre avec @section('title', '...') --}}
    <title>@yield('title') — Mon Miam Miam</title>

    {{-- Jeton CSRF : lu par public/js/employe/api.js pour les requêtes PATCH / POST --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Tailwind + les couleurs de la charte (primary #cfbd97, secondary #000000) définies dans app.css --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

{{-- bg-secondary = noir de la charte graphique --}}
<body class="min-h-screen bg-secondary text-white">

@php
    // DRY : les 4 liens du menu sont décrits UNE fois ici, puis affichés par une boucle.
    // Ajouter une page = ajouter une ligne (au lieu de recopier un bloc <a> complet).
    $liens = [
        'employe.commandes'    => 'Commandes',
        'employe.menu'         => 'Plat du jour',
        'employe.reclamations' => 'Réclamations',
        'employe.statistiques' => 'Statistiques',
    ];

    $utilisateur = auth()->user();
    // Le poste (ex. « Assistant cuisinier ») est dans la table employes, d'où la relation employe()
    $poste    = $utilisateur?->employe?->poste ?? 'Employé';
    $nomAffiche = trim(($utilisateur->prenom ?? '') . ' ' . ($utilisateur->name ?? ''));
    // Initiales pour l'avatar (KISS : pas de gestion de photo de profil tant qu'on n'en a pas besoin)
    $initiales = mb_strtoupper(mb_substr($utilisateur->prenom ?? '', 0, 1) . mb_substr($utilisateur->name ?? '', 0, 1));
@endphp

<header class="border-b border-primary/30">
    <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-4 py-4">

        {{-- Logo --}}
        <a href="{{ route('employe.commandes') }}" class="text-2xl font-bold text-primary">Mon Miam Miam</a>

        {{-- Menu : défile horizontalement sur téléphone au lieu de casser la mise en page --}}
        <nav class="order-last flex w-full gap-6 overflow-x-auto font-serif text-lg lg:order-none lg:w-auto">
            @foreach ($liens as $route => $texte)
                {{-- request()->routeIs() : met en beige le lien de la page affichée --}}
                <a href="{{ route($route) }}"
                   class="whitespace-nowrap transition hover:text-primary {{ request()->routeIs($route) ? 'text-primary' : '' }}">
                    {{ $texte }}
                </a>
            @endforeach
        </nav>

        {{-- Employé connecté --}}
        <div class="flex items-center gap-3 text-right">
            <div class="leading-tight">
                <p class="font-serif text-sm">{{ $nomAffiche }}</p>
                <p class="text-xs text-white/60">{{ $poste }}</p>
            </div>
            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-primary font-semibold text-black">
                {{ $initiales }}
            </span>
        </div>
    </div>
</header>

<main class="mx-auto max-w-7xl px-4 py-8">
    @yield('content')
</main>

{{-- Outils partagés (appel API, formats, messages), chargés AVANT le script de la page --}}
<script src="{{ asset('js/employe/api.js') }}"></script>

{{-- Ici s'insère le <script> que chaque page met dans @push('scripts') --}}
@stack('scripts')
</body>
</html>
