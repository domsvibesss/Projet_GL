<?php

namespace App\Http\Controllers\Gerant;

use App\Http\Controllers\Controller;
use App\Models\Commande;

/**
 * Page « Supervision des commandes » du gérant (lecture seule).
 * La vue reçoit $compteurs et $commandes au même format que ses données de démo.
 */
class SupervisionController extends Controller
{
    public function index()
    {
        $commandes = Commande::with(['etudiant', 'plats'])->latest('date_commande')->get();

        // Nombre de commandes par statut : [en_attente => 4, en_preparation => 3, ...]
        $compteurs = array_fill_keys(Commande::ETATS, 0);
        foreach ($commandes as $c) {
            if (isset($compteurs[$c->etat])) {
                $compteurs[$c->etat]++;
            }
        }

        return view('supervision', [
            'compteurs' => $compteurs,
            'commandes' => $commandes->map(fn (Commande $c) => [
                'numero'   => $c->numero,                                   // CMD-0001
                'client'   => $c->etudiant?->nom_complet ?? 'Client supprimé',
                'articles' => $c->articles ?: '—',                          // 2 × Poulet DG, ...
                'heure'    => $c->date_commande->format('H\hi'),            // 12h30
                'statut'   => $c->etat,
            ])->all(),
        ]);
    }
}
