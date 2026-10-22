<?php
 
namespace App\Services;
 
use App\Models\Commande;
use App\Models\Employe;
use App\Models\Etudiant;
use App\Models\Favori;
use App\Models\Jeu;
use App\Models\Paiement;
use App\Models\Parrainage;
use App\Models\Plat;
use App\Models\Point;
use App\Models\Promotion;
use App\Models\Reclamation;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
 
class StatsService
{
    // Valeurs des colonnes (à adapter si ta base utilise d'autres mots)
    private const ETAT_ANNULEE    = 'annulee';      // Commande.etat
    private const ETAT_EN_ATTENTE = 'en_attente';   // Commande.etat / Parrainage.etat / Reclamation.status
    private const STATUS_ACTIF    = 'actif';        // Etudiant.status / Employe.status
 
    public static function periodStart(string $periode): CarbonInterface
    {
        return match ($periode) {
            'jour'  => now()->startOfDay(),
            'mois'  => now()->startOfMonth(),
            default => now()->startOfWeek(),
        };
    }
 
    /* ------------------------------------------------------------------ */
    /*  Table Commandes                                                    */
    /* ------------------------------------------------------------------ */
 
    /**
     * @return array{nombre:int, amount:int, amount_moyen:int, points_accumules:int,
     *               en_attente:int, annulees:int, par_etat:array<string,int>}
     */
    public function commandes(CarbonInterface $from): array
    {
        // Commandes non annulées : chiffre d'affaires
        $row = Commande::where('etat', '!=', self::ETAT_ANNULEE)
            ->where('date_commande', '>=', $from)
            ->selectRaw('COUNT(*) as nombre,
                         COALESCE(SUM(amount), 0)           as amount,
                         COALESCE(SUM(points_accumules), 0) as points_accumules')
            ->first();
 
        // Toutes les commandes : répartition par état
        $parEtat = Commande::where('date_commande', '>=', $from)
            ->selectRaw('etat, COUNT(*) as nombre')
            ->groupBy('etat')->pluck('nombre', 'etat')
            ->map(fn ($n) => (int) $n);
 
        $nombre = (int) $row->nombre;
        $amount = (int) round($row->amount);
 
        return [
            'nombre'           => $nombre,
            'amount'           => $amount,
            'amount_moyen'     => $nombre > 0 ? (int) round($amount / $nombre) : 0,
            'points_accumules' => (int) $row->points_accumules,
            'en_attente'       => (int) ($parEtat[self::ETAT_EN_ATTENTE] ?? 0),
            'annulees'         => (int) ($parEtat[self::ETAT_ANNULEE] ?? 0),
            'par_etat'         => $parEtat->all(),
        ];
    }
 
    /** Graphique : [{jour, commandes, amount}, ...] */
    public function commandesParJour(CarbonInterface $from): array
    {
        return Commande::where('etat', '!=', self::ETAT_ANNULEE)
            ->where('date_commande', '>=', $from)
            ->selectRaw('DATE(date_commande) as jour, COUNT(*) as commandes, SUM(amount) as amount')
            ->groupBy('jour')->orderBy('jour')
            ->get()->toArray();
    }
 
    /* ------------------------------------------------------------------ */
    /*  Table Paiements                                                    */
    /* ------------------------------------------------------------------ */
 
    /** @return array{nombre:int, montant_depense:int, points_depense:int, nombre_avec_points:int} */
    public function paiements(CarbonInterface $from): array
    {
        $row = Paiement::where('date_paiement', '>=', $from)
            ->selectRaw('COUNT(*) as nombre,
                         COALESCE(SUM(montant_depense), 0) as montant_depense,
                         COALESCE(SUM(points_depense), 0)  as points_depense,
                         COALESCE(SUM(CASE WHEN points_depense > 0 THEN 1 ELSE 0 END), 0) as nombre_avec_points')
            ->first();
 
        return [
            'nombre'             => (int) $row->nombre,
            'montant_depense'    => (int) round($row->montant_depense),
            'points_depense'     => (int) $row->points_depense,
            'nombre_avec_points' => (int) $row->nombre_avec_points,
        ];
    }
 
    /* ------------------------------------------------------------------ */
    /*  Table Etudiant                                                     */
    /* ------------------------------------------------------------------ */
 
    /** @return array{total:int, actifs:int, nouveaux:int, avec_points_fidelite:int, points_fidelite:int} */
    public function etudiants(CarbonInterface $from): array
    {
        $row = Etudiant::selectRaw(
            "COUNT(*) as total,
             COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0)            as actifs,
             COALESCE(SUM(CASE WHEN date_inscription >= ? THEN 1 ELSE 0 END), 0) as nouveaux,
             COALESCE(SUM(CASE WHEN points_fidelite > 0 THEN 1 ELSE 0 END), 0)   as avec_points_fidelite,
             COALESCE(SUM(points_fidelite), 0)                                   as points_fidelite",
            [self::STATUS_ACTIF, $from]
        )->first();
 
        return [
            'total'                => (int) $row->total,
            'actifs'               => (int) $row->actifs,
            'nouveaux'             => (int) $row->nouveaux,              // date_inscription sur la période
            'avec_points_fidelite' => (int) $row->avec_points_fidelite,
            'points_fidelite'      => (int) $row->points_fidelite,       // points en circulation
        ];
    }
 
    /* ------------------------------------------------------------------ */
    /*  Table Points                                                       */
    /* ------------------------------------------------------------------ */
 
    /** @return array{quantite:int, par_origine:array<string,int>, quantite_expirant_30j:int} */
    public function points(CarbonInterface $from): array
    {
        $parOrigine = Point::where('date_reception', '>=', $from)
            ->selectRaw('origine, COALESCE(SUM(quantite), 0) as quantite')
            ->groupBy('origine')->pluck('quantite', 'origine')
            ->map(fn ($q) => (int) $q);
 
        return [
            'quantite'              => (int) $parOrigine->sum(),   // points reçus sur la période
            'par_origine'           => $parOrigine->all(),
            'quantite_expirant_30j' => (int) Point::valides()
                ->whereNotNull('date_expiration')
                ->where('date_expiration', '<=', now()->addDays(30))
                ->sum('quantite'),
        ];
    }
 
    /* ------------------------------------------------------------------ */
    /*  Table parrainages                                                  */
    /* ------------------------------------------------------------------ */
 
    /** @return array{total:int, periode:int, en_attente:int, traites:int, par_etat:array<string,int>} */
    public function parrainages(CarbonInterface $from): array
    {
        $parEtat = Parrainage::selectRaw('etat, COUNT(*) as nombre')
            ->groupBy('etat')->pluck('nombre', 'etat')
            ->map(fn ($n) => (int) $n);
 
        $total     = (int) $parEtat->sum();
        $enAttente = (int) ($parEtat[self::ETAT_EN_ATTENTE] ?? 0);
 
        return [
            'total'      => $total,
            'periode'    => Parrainage::where('date_parrainage', '>=', $from)->count(),
            'en_attente' => $enAttente,
            'traites'    => $total - $enAttente,     // tout ce qui n'est plus "en_attente"
            'par_etat'   => $parEtat->all(),
        ];
    }
 
    /* ------------------------------------------------------------------ */
    /*  Table Reclamations                                                 */
    /* ------------------------------------------------------------------ */
 
    /** @return array{total:int, en_attente:int, par_status:array<string,int>} */
    public function reclamations(): array
    {
        $parStatus = Reclamation::selectRaw('status, COUNT(*) as nombre')
            ->groupBy('status')->pluck('nombre', 'status')
            ->map(fn ($n) => (int) $n);
 
        return [
            'total'      => (int) $parStatus->sum(),
            'en_attente' => (int) ($parStatus[self::ETAT_EN_ATTENTE] ?? 0),
            'par_status' => $parStatus->all(),
        ];
    }
 
    /* ------------------------------------------------------------------ */
    /*  Plat, promotions, jeux, employés                                   */
    /* ------------------------------------------------------------------ */
 
    /** @return array{total:int, disponibles:int, epuises:int} (Plat.quantite) */
    public function plats(): array
    {
        $total       = Plat::count();
        $disponibles = Plat::disponibles()->count();
 
        return [
            'total'       => $total,
            'disponibles' => $disponibles,
            'epuises'     => $total - $disponibles,   // quantite = 0
        ];
    }
 
    public function promotionsActives(): int
    {
        return Promotion::actives()->count();
    }
 
    public function jeuxActifs(): int
    {
        return Jeu::actifs()->count();
    }
 
    /** @return array{total:int, actifs:int} */
    public function employes(): array
    {
        return [
            'total'  => Employe::count(),
            'actifs' => Employe::where('status', self::STATUS_ACTIF)->count(),
        ];
    }
 
    /* ------------------------------------------------------------------ */
    /*  Classements                                                        */
    /* ------------------------------------------------------------------ */
 
    /**
     * Meilleurs clients : [{etudiant_id, nom, prenom, commandes, amount}, ...]
     * Suppose que la table Commandes a une colonne `etudiant_id`.
     */
    public function meilleursClients(CarbonInterface $from, int $limit = 10): Collection
    {
        $rows = Commande::where('etat', '!=', self::ETAT_ANNULEE)
            ->where('date_commande', '>=', $from)
            ->selectRaw('etudiant_id, COUNT(*) as commandes, COALESCE(SUM(amount), 0) as amount')
            ->groupBy('etudiant_id')
            ->orderByDesc('commandes')->orderByDesc('amount')
            ->limit($limit)->get();
 
        $etudiants = $this->etudiantsParId($rows->pluck('etudiant_id'));
 
        return $rows->map(fn ($r) => [
            'etudiant_id' => $r->etudiant_id,
            'nom'         => $etudiants[$r->etudiant_id]->nom ?? null,
            'prenom'      => $etudiants[$r->etudiant_id]->prenom ?? null,
            'commandes'   => (int) $r->commandes,
            'amount'      => (int) round($r->amount),
        ]);
    }
 
    /** Parrains : [{parrain_id, nom, prenom, filleuls}, ...] */
    public function meilleursParrains(int $limit = 5): Collection
    {
        $rows = Parrainage::selectRaw('parrain_id, COUNT(*) as filleuls')
            ->groupBy('parrain_id')
            ->orderByDesc('filleuls')->limit($limit)->get();
 
        $etudiants = $this->etudiantsParId($rows->pluck('parrain_id'));
 
        return $rows->map(fn ($r) => [
            'parrain_id' => $r->parrain_id,
            'nom'        => $etudiants[$r->parrain_id]->nom ?? null,
            'prenom'     => $etudiants[$r->parrain_id]->prenom ?? null,
            'filleuls'   => (int) $r->filleuls,
        ]);
    }
 
    /** Plats favoris : [{plat_id, name, favoris}, ...] */
    public function platsFavoris(int $limit = 5): Collection
    {
        $rows = Favori::selectRaw('plat_id, COUNT(*) as favoris')
            ->groupBy('plat_id')
            ->orderByDesc('favoris')->limit($limit)->get();
 
        $noms = Plat::whereIn('id', $rows->pluck('plat_id'))->pluck('name', 'id');
 
        return $rows->map(fn ($r) => [
            'plat_id' => $r->plat_id,
            'name'    => $noms[$r->plat_id] ?? null,
            'favoris' => (int) $r->favoris,
        ]);
    }
 
    private function etudiantsParId(Collection $ids): Collection
    {
        return Etudiant::whereIn('id', $ids->filter()->unique())
            ->get(['id', 'nom', 'prenom'])
            ->keyBy('id');
    }
}