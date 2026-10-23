<?php

namespace App\Services;

use App\Models\Commande;
use App\Models\Etudiant;
use App\Models\Paiement;
use App\Models\Point;
use Illuminate\Support\Collection;

/**
 * Chiffres de la page Statistiques (gérant et administrateur).
 */
class StatsService
{
    /** @return array{commandes_jour:int, commandes_en_cours:int, chiffre_affaires:int, clients_actifs:int} */
    public function cartes(int $jours): array
    {
        return [
            'commandes_jour'     => Commande::where('date_commande', '>=', now()->startOfDay())->count(),
            // en cours = pas encore livrées
            'commandes_en_cours' => Commande::where('etat', '!=', Commande::ETAT_LIVREE)->count(),
            'chiffre_affaires'   => (int) round(Commande::where('etat', Commande::ETAT_LIVREE)
                ->where('date_commande', '>=', now()->subDays($jours))
                ->sum('montant_local')),
            'clients_actifs'     => Etudiant::where('statut', Etudiant::STATUT_ACTIF)->count(),
        ];
    }

    /**
     * Points émis (table points) et utilisés (table paiements) sur la période.
     *
     * @return array{emis:int, utilises:int, taux:float}
     */
    public function mouvementPoints(int $jours): array
    {
        $depuis = now()->subDays($jours);

        $emis     = (int) Point::where('date_reception', '>=', $depuis)->sum('quantite');
        $utilises = (int) Paiement::where('date_paiement', '>=', $depuis)->sum('points_depense');

        return [
            'emis'     => $emis,
            'utilises' => $utilises,
            'taux'     => $emis > 0 ? round($utilises / $emis * 100, 1) : 0.0,
        ];
    }

    /**
     * Un parrainage est « converti » quand le parrain reçoit ses points,
     * c'est-à-dire à la 1re commande du filleul (voir CommandesSeeder).
     *
     * @return array{parrains:int, conversions:int, points:int}
     */
    public function parrainage(): array
    {
        $points = Point::where('origine', Point::ORIGINE_PARRAINAGE);

        return [
            'parrains'    => (clone $points)->distinct()->count('etudiant_id'),
            'conversions' => (clone $points)->count(),
            'points'      => (int) (clone $points)->sum('quantite'),
        ];
    }

    /** Parrains : [{nom, initiales, filleuls}, ...] */
    public function meilleursParrains(int $limit): Collection
    {
        $rows = Point::where('origine', Point::ORIGINE_PARRAINAGE)
            ->selectRaw('etudiant_id, COUNT(*) as filleuls')
            ->groupBy('etudiant_id')
            ->orderByDesc('filleuls')->limit($limit)->get();

        $etudiants = Etudiant::whereIn('id', $rows->pluck('etudiant_id'))->get()->keyBy('id');

        return $rows->map(function ($r) use ($etudiants) {
            $etudiant = $etudiants->get($r->etudiant_id);

            return [
                'nom'       => $etudiant?->nom_complet ?? '—',
                'initiales' => $etudiant?->initiales ?? '?',
                'filleuls'  => (int) $r->filleuls,
            ];
        });
    }
}
