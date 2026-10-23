<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promotion;

/**
 * Page admin/promotions : promotions et événements présentés sous forme d'affiches.
 */
class PromotionController extends Controller
{
    // Couleurs des affiches de la maquette, utilisées à tour de rôle
    private const FONDS = ['bg-green-300', 'bg-blue-300', 'bg-red-300', 'bg-purple-100'];

    public function index()
    {
        $promotions = Promotion::latest('date_debut')->get()->values();

        return view('admin.promotions', [
            'promotions' => $promotions->map(fn (Promotion $p, int $i) => $this->pourLaVue($p, $i))->all(),
        ]);
    }

    /** Une promotion au format attendu par la vue. */
    private function pourLaVue(Promotion $p, int $rang): array
    {
        $prix = fn ($montant) => number_format((float) $montant, 0, ',', ' ').' F';

        return [
            'titre'     => $p->titre ?? 'Promotion',
            'fond'      => self::FONDS[$rang % count(self::FONDS)],
            'reduction' => $p->reduction_pourcentage > 0 ? '-'.round($p->reduction_pourcentage).'%' : null,
            'image'     => $p->chemin_image,
            'texte'     => $p->description
                ?? "{$prix($p->prix_promo)} au lieu de {$prix($p->prix_initial)}, du {$p->date_debut->format('d/m')} au {$p->date_fin->format('d/m')} !",
        ];
    }
}
