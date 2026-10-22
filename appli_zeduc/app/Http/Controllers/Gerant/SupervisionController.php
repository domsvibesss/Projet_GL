<?php

namespace App\Http\Controllers\Gerant;

use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Page « Supervision des commandes » du gérant.
 *
 * Rôle du contrôleur (le C de MVC) : aller chercher les données dans la base,
 * les mettre dans la forme attendue par la vue resources/views/supervision.blade.php,
 * puis envoyer le tout à cette vue.
 */
class SupervisionController extends Controller
{
    /**
     * Correspondance entre l'état enregistré en base (commandes.etat)
     * et le statut utilisé par la vue (les 4 cartes du tableau de bord).
     * On accepte les deux vocabulaires : celui du MPD (en_cours, effectué)
     * et celui de la vue (en_preparation, prete, livree).
     * Un état absent de cette liste (ex. « rejeté ») n'est pas affiché.
     */
    private const ETAT_VERS_STATUT = [
        'en_attente'     => 'en_attente',
        'en_preparation' => 'en_preparation',
        'en_cours'       => 'en_preparation',
        'prete'          => 'prete',
        'livree'         => 'livree',
        'effectué'       => 'livree',
    ];

    public function index()
    {
        // 1. Les 4 compteurs, tous à zéro au départ
        $compteurs = [
            'en_attente'     => 0,
            'en_preparation' => 0,
            'prete'          => 0,
            'livree'         => 0,
        ];

        // 2. Les commandes avec le nom du client (jointure commandes <-> etudiants)
        $lignes = DB::table('commandes')
            ->join('etudiants', 'etudiants.id', '=', 'commandes.etudiant_id')
            ->select(
                'commandes.id',
                'commandes.etat',
                'commandes.date_commande',
                'etudiants.nom',
                'etudiants.prenom'
            )
            ->orderByDesc('commandes.date_commande')
            ->limit(100)
            ->get();

        // 3. Les articles de chaque commande (table de liaison commande_plat),
        //    seulement si cette table existe avec les colonnes attendues
        $articlesParCommande = collect();
        if (Schema::hasTable('commande_plat')
            && Schema::hasColumns('commande_plat', ['commande_id', 'plat_id', 'quantite'])) {

            $articlesParCommande = DB::table('commande_plat')
                ->join('plats', 'plats.id', '=', 'commande_plat.plat_id')
                ->whereIn('commande_plat.commande_id', $lignes->pluck('id'))
                ->select('commande_plat.commande_id', 'commande_plat.quantite', 'plats.nom')
                ->get()
                ->groupBy('commande_id');
        }

        // 4. Mise en forme : un tableau par commande, comme l'attend la vue
        $commandes = [];
        foreach ($lignes as $ligne) {
            $statut = self::ETAT_VERS_STATUT[$ligne->etat] ?? null;
            if ($statut === null) {
                continue;   // état non affiché (rejeté, annulé...)
            }
            $compteurs[$statut]++;

            // « 2 × Poulet DG, 1 × Jus de gingembre »
            $articles = $articlesParCommande->get($ligne->id, collect())
                ->map(fn ($a) => $a->quantite . ' × ' . $a->nom)
                ->implode(', ');

            $commandes[] = [
                'numero'   => $ligne->id,
                // « Alice M. » : prénom + initiale du nom (on évite d'afficher le nom complet)
                'client'   => $ligne->prenom . ' ' . mb_substr($ligne->nom, 0, 1) . '.',
                'articles' => $articles !== '' ? $articles : '—',
                'heure'    => Carbon::parse($ligne->date_commande)->format('H\hi'),
                'statut'   => $statut,
            ];
        }

        // 5. Envoi à la vue : $compteurs et $commandes remplacent les valeurs de secours
        return view('supervision', [
            'compteurs' => $compteurs,
            'commandes' => $commandes,
        ]);
    }
}