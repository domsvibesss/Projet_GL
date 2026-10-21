{{--
    Layout de l'ADMINISTRATEUR.
    Il réutilise layouts/app.blade.php : on change seulement les liens et le profil.
--}}
@php
    // chemin de la page => texte du lien
    $liensAdmin = [
        'admin/employes'     => 'Employés',
        'admin/menus'        => 'Menus',
        'admin/promotions'   => 'Promotions',
        'admin/statistiques' => 'Statistiques',
        'admin/reclamations' => 'Réclamations',
        'admin/parametres'   => 'Paramètres',
    ];

    // Valeur de secours : le backend enverra l'administrateur connecté
    $admin = $admin ?? [
        'nom'   => 'Humphrey Mbeng',
        'role'  => 'Administrateur',
        'photo' => 'images/humphrey.png',
    ];
@endphp

@extends('layouts.app', ['liens' => $liensAdmin, 'utilisateur' => $admin])