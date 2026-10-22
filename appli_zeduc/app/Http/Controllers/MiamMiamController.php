<?php

namespace App\Http\Controllers;

class MiamMiamController extends Controller
{
    public function accueil()
    {
        return view('miammiam.accueil');
    }

    public function menu()
    {
        // TODO BDD : remplacer par Plat::all() quand la table "plats" existera
        $plats = [
            ['id' => 1,  'nom' => 'Boulettes',             'image' => 'Boulettes.jpeg',            'prix' => 1000],
            ['id' => 2,  'nom' => 'Eru',                   'image' => 'Eru.jpeg',                  'prix' => 1000],
            ['id' => 3,  'nom' => 'Ndole',                 'image' => 'Ndole.jpeg',                'prix' => 1000],
            ['id' => 4,  'nom' => 'Okok',                  'image' => 'Okok.jpeg',                 'prix' => 1000],
            ['id' => 5,  'nom' => 'Poisson Braise',        'image' => 'PoissonBraise.jpeg',        'prix' => 2000],
            ['id' => 6,  'nom' => 'Porc Roti',             'image' => 'PorcRoti.jpeg',             'prix' => 2000],
            ['id' => 7,  'nom' => 'Poulet Pane',           'image' => 'PouletPane.jpeg',           'prix' => 2000],
            ['id' => 8,  'nom' => 'Poulet Braise',         'image' => 'PouletBraise.jpeg',         'prix' => 2000],
            ['id' => 9,  'nom' => 'Poulet DG',             'image' => 'PouletDG.jpeg',             'prix' => 2000],
            ['id' => 10, 'nom' => 'Poulet Mayo',           'image' => 'PouletMayo.jpeg',           'prix' => 2500],
            ['id' => 11, 'nom' => 'Poulet Roti',           'image' => 'PouletRoti.jpeg',           'prix' => 1500],
            ['id' => 12, 'nom' => 'Spaghetti Bolognaise',  'image' => 'SpaghettiBolognaise.jpeg',  'prix' => 1000],
        ];

        return view('miammiam.menu', compact('plats'));
    }

    public function platDuJour()
    {
        // TODO BDD : remplacer par Plat::all() (même source que le menu)
        $plats = [
            ['nom' => 'PORC ROTI',            'description' => 'Description provisoire du porc roti.', 'prix' => 2000, 'image' => 'PorcRoti.jpeg'],
            ['nom' => 'BOULETTES',            'description' => 'Description provisoire.',               'prix' => 1000, 'image' => 'Boulettes.jpeg'],
            ['nom' => 'ERU',                  'description' => 'Description provisoire.',               'prix' => 1000, 'image' => 'Eru.jpeg'],
            ['nom' => 'NDOLE',                'description' => 'Description provisoire.',               'prix' => 1000, 'image' => 'Ndole.jpeg'],
            ['nom' => 'OKOK',                 'description' => 'Description provisoire.',               'prix' => 1500, 'image' => 'Okok.jpeg'],
            ['nom' => 'POISSON BRAISE',       'description' => 'Description provisoire.',               'prix' => 2000, 'image' => 'PoissonBraise.jpeg'],
            ['nom' => 'POULET BRAISE',        'description' => 'Description provisoire.',               'prix' => 2000, 'image' => 'PouletBraise.jpeg'],
            ['nom' => 'POULET DG',            'description' => 'Description provisoire.',               'prix' => 2000, 'image' => 'PouletDG.jpeg'],
            ['nom' => 'POULET MAYO',          'description' => 'Description provisoire.',               'prix' => 2500, 'image' => 'PouletMayo.jpeg'],
            ['nom' => 'POULET PANE',          'description' => 'Description provisoire.',               'prix' => 2000, 'image' => 'PouletPane.jpeg'],
            ['nom' => 'POULET ROTI',          'description' => 'Description provisoire.',               'prix' => 1500, 'image' => 'PouletRoti.jpeg'],
            ['nom' => 'SPAGHETTI BOLOGNAISE', 'description' => 'Description provisoire.',               'prix' => 1000, 'image' => 'SpaghettiBolognaise.jpeg'],
        ];

        return view('miammiam.plat-du-jour', compact('plats'));
    }

    public function panier()
    {
        // TODO BDD : le panier reste côté JS/localStorage pour l'instant.
        // Plus tard : Commande::where('user_id', auth()->id())->where('statut', 'panier')->...
        return view('miammiam.panier');
    }

    public function finalisation()
    {
        // TODO BDD : injecter ici les quartiers depuis la table "quartiers"
        // au lieu de resources/js/quartiers.js
        return view('miammiam.finalisation');
    }

    public function confirmation()
    {
        return view('miammiam.confirmation');
    }

    public function historique()
    {
        // TODO BDD : remplacer par auth()->user()->commandes()->latest()->get()
        return view('miammiam.historique');
    }

    public function fidelite()
    {
        // TODO BDD : remplacer par les points/badges réels de l'utilisateur connecté
        return view('miammiam.fidelite');
    }

    public function reclamations()
    {
        // TODO BDD : remplacer par auth()->user()->reclamations()->latest()->get()
        return view('miammiam.reclamations');
    }

    public function connexion()
    {
        return view('miammiam.connexion');
    }

    public function inscription()
    {
        return view('miammiam.inscription');
    }

    public function motDePasseOublie()
    {
        return view('miammiam.mot-de-passe-oublie');
    }
}