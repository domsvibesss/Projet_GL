<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Mon Miam Miam')</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,400i,600,700,700i&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-secondary text-white min-h-screen">

    @php
        // Valeurs par défaut = espace du GÉRANT
        $liens = $liens ?? [
            'supervision'  => 'Supervision des commandes',
            'employes'     => 'Compte employés',
            'statistiques' => 'Statistiques',
            'reclamations' => 'Réclamations',
        ];

        $utilisateur = $utilisateur ?? $gerant ?? [
            'nom'   => 'Junior BOGOGNE',
            'role'  => 'Gérant',
            'photo' => 'images/gerant.png',
        ];
    @endphp

    {{-- Le header est maintenant un composant --}}
    <x-navbar :liens="$liens" :utilisateur="$utilisateur" />

    <main class="@yield('main-class', 'px-6 pb-10')">
        @yield('content')
    </main>

    {{-- Emplacement du JavaScript propre à chaque page (si tu as déjà cette ligne, garde-la) --}}
    @stack('scripts')

</body>
</html>