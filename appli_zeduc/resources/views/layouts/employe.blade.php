@extends('layouts.app')

@section('entete')
  @include('partials.nav', [
      'accueil' => 'employe.commandes',
      'liens' => [
          'employe.commandes' => 'Commandes',
          'employe.plat-du-jour' => 'Plat du jour',
          'employe.reclamations' => 'Reclamations',
          'employe.statistiques' => 'Statistiques',
      ],
      'utilisateur' => [
          'nom' => 'Valdes KENMOE',
          'role' => 'Assistant cuisinier',
          'image' => 'images/chef.png',
          'emoji' => '👨‍🍳',
      ],
  ])
@endsection
