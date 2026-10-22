<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Mon Miam Miam')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Gilda+Display&family=Handlee&family=Italianno&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css'])
    @stack('head')
</head>
<body class="@yield('bodyClass', 'bg-black min-h-screen text-white')" data-page="@yield('page')">

    @yield('background')

    @yield('content')

    @vite('resources/js/main.js')
    @stack('scripts')
</body>
</html>