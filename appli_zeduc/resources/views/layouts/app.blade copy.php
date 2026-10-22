<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('titre') – Mon miam miam</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Lilita+One&family=Lora:wght@400;500;600&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="{{ asset('js/tailwind.config.js') }}"></script>
  @stack('styles')
</head>
<body class="bg-black min-h-screen @yield('police', 'font-sans')">
  @yield('entete')
  @yield('contenu')
  @stack('scripts')
</body>
</html>
