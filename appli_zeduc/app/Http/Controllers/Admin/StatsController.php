<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\StatsService;

/**
 * Page admin/statistiques : la vue statistiques.blade.php avec le layout admin.
 * Les valeurs sont envoyées déjà formatées, comme les chiffres de démo de la vue.
 */
class StatsController extends Controller
{
    private const PERIODE_JOURS = 30;   // la vue affiche « 30 jours »

    public function __invoke(StatsService $stats)
    {
        $cartes     = $stats->cartes(self::PERIODE_JOURS);
        $points     = $stats->mouvementPoints(self::PERIODE_JOURS);
        $parrainage = $stats->parrainage();

        // 185000 -> "185 000"
        $nombre = fn ($n) => number_format($n, 0, ',', ' ');

        return view('statistiques', [
            'layout' => 'layouts.admin',
            'stats'  => [
                'commandes_jour'     => $nombre($cartes['commandes_jour']),
                'commandes_en_cours' => $nombre($cartes['commandes_en_cours']),
                'chiffre_affaires'   => $nombre($cartes['chiffre_affaires']).' F',
                'clients_actifs'     => $nombre($cartes['clients_actifs']),
                'points_emis'        => $nombre($points['emis']),
                'points_utilises'    => $nombre($points['utilises']),
                'points_taux'        => number_format($points['taux'], 1, ',', ' ').' %',
                // Les invitations envoyées ne sont pas enregistrées en base : pas de chiffre réel
                'invitations'        => '—',
                'converties'         => $nombre($parrainage['conversions']),
                'conversion'         => '—',
            ],
            'parrains' => $stats->meilleursParrains(4)->all(),
        ]);
    }
}
