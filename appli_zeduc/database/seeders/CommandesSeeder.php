<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CommandesSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $ts = ['created_at' => $now, 'updated_at' => $now];

        $idEtudiant = fn (string $email) => DB::table('etudiants')->where('email', $email)->value('id');
        $caissier   = DB::table('employes')->where('email', 'employe1@zeduc.test')->value('id');
        $roue       = DB::table('jeux')->where('nom', 'Roue de la chance')->value('id');
        $prix       = DB::table('plats')->pluck('prix', 'nom');   // nom => prix
        $idPlat     = DB::table('plats')->pluck('id', 'nom');     // nom => id

        // Règles du cahier des charges : 1 000 F dépensés = 1 point ; 15 points = 1 000 F de réduction
        $PAS_POINT = 1000;
        $SEUIL_REDUCTION = 15;
        $VALEUR_REDUCTION = 1000;

        // Valeurs de commandes.etat : effectué | en_cours | rejeté | en_attente
        $commandes = [
            ['etudiant1@zeduc.test', 'effectué',  12, 0,  ['Poulet DG' => 1, 'Jus de gingembre' => 1]],
            ['etudiant1@zeduc.test', 'effectué',   6, 0,  ['Riz sauce tomate' => 2, 'Beignets haricots bouillie' => 2]],
            ['etudiant2@zeduc.test', 'effectué',   9, 0,  ['Poisson braisé' => 1, 'Jus de gingembre' => 1]],   // 1re commande du filleul (index 2)
            ['etudiant3@zeduc.test', 'effectué',   4, 0,  ['Ndolé et plantain' => 1, 'Koki et plantain' => 1]],
            ['etudiant1@zeduc.test', 'effectué',   2, 15, ['Poulet DG' => 1]],                                  // paiement avec 15 points
            ['etudiant7@zeduc.test', 'effectué',   2, 0,  ['Poulet DG' => 2]],
            ['etudiant2@zeduc.test', 'effectué',   1, 0,  ['Riz sauce tomate' => 1, 'Koki et plantain' => 1]],
            ['etudiant4@zeduc.test', 'en_cours',   1, 0,  ['Brochettes de boeuf' => 3]],
            ['etudiant5@zeduc.test', 'en_attente', 0, 0,  ['Spaghetti sauté' => 2]],
            ['etudiant6@zeduc.test', 'rejeté',     3, 0,  ['Eru et water-fufu' => 1]],
        ];

        $solde = [];        // points restants par étudiant
        $commandeIds = [];  // pour retrouver une commande par son rang

        foreach ($commandes as $index => [$email, $etat, $jours, $pointsUtilises, $lignes]) {
            $etudiantId = $idEtudiant($email);
            $date = now()->subDays($jours)->setTime(12, 30);

            // Montant = somme des lignes (le prix actuel du plat)
            $montant = 0;
            foreach ($lignes as $nomPlat => $quantite) {
                $montant += $prix[$nomPlat] * $quantite;
            }
            $pointsGagnes = $etat === 'effectué' ? intdiv((int) $montant, $PAS_POINT) : 0;

            $commandeId = DB::table('commandes')->insertGetId([
                'montant_local'    => $montant,
                'points_accumules' => $pointsGagnes,
                'date_commande'    => $date,
                'etat'             => $etat,
                'destination'      => 'Sur place',   // colonne obligatoire (migration commandes)
                'employe_id'       => $etat === 'en_attente' ? null : $caissier,
                'etudiant_id'      => $etudiantId,
            ] + $ts);
            $commandeIds[$index] = $commandeId;

            // Lignes de commande : seulement si Rigeau a créé la table de liaison commande_plat
            if (Schema::hasTable('commande_plat')) {
                $avecDates = Schema::hasColumn('commande_plat', 'created_at');
                foreach ($lignes as $nomPlat => $quantite) {
                    DB::table('commande_plat')->insert([
                        'commande_id' => $commandeId,
                        'plat_id'     => $idPlat[$nomPlat],
                        'quantite'    => $quantite,
                    ] + ($avecDates ? $ts : []));
                }
            }

            if ($etat === 'effectué') {
                // Paiement : on déduit la réduction obtenue avec les points
                $reduction = intdiv($pointsUtilises, $SEUIL_REDUCTION) * $VALEUR_REDUCTION;
                DB::table('paiements')->insert([
                    'montant_depense' => $montant - $reduction,
                    'points_depense'  => $pointsUtilises,
                    'date_paiement'   => $date,
                    'employe_id'      => $caissier,
                    'commande_id'     => $commandeId,
                ] + $ts);

                // Points gagnés par achat (expirent après 12 mois)
                if ($pointsGagnes > 0) {
                    DB::table('points')->insert([
                        'quantite'        => $pointsGagnes,
                        'origine'         => 'achat',
                        'date_reception'  => $date,
                        'date_expiration' => $date->copy()->addMonths(12),
                        'commande_id'     => $commandeId,
                        'etudiant_id'     => $etudiantId,
                        'jeu_id'          => null,
                    ] + $ts);
                    $solde[$etudiantId] = ($solde[$etudiantId] ?? 0) + $pointsGagnes;
                }
                $solde[$etudiantId] = ($solde[$etudiantId] ?? 0) - $pointsUtilises;
            }
        }

        // Points gagnés avec le jeu « Roue de la chance » (Alice)
        $alice = $idEtudiant('etudiant1@zeduc.test');
        $dateJeu = now()->subDays(8);
        DB::table('points')->insert([
            'quantite'        => 12,
            'origine'         => 'jeux',
            'date_reception'  => $dateJeu,
            'date_expiration' => $dateJeu->copy()->addMonths(12),
            'commande_id'     => null,
            'etudiant_id'     => $alice,
            'jeu_id'          => $roue,
        ] + $ts);
        $solde[$alice] += 12;

        // Parrainage : Alice parraine Bruno, récompense à la 1re commande de Bruno (commande n°3)
        $datePar = now()->subDays(9);
        DB::table('points')->insert([
            'quantite'        => 5,
            'origine'         => 'parrainage',
            'date_reception'  => $datePar,
            'date_expiration' => $datePar->copy()->addMonths(12),
            'commande_id'     => $commandeIds[2],
            'etudiant_id'     => $alice,
            'jeu_id'          => null,
        ] + $ts);
        $solde[$alice] += 5;

        // Solde de points = points gagnés - points dépensés
        foreach ($solde as $etudiantId => $points) {
            DB::table('etudiants')->where('id', $etudiantId)->update(['points_fidelite' => max(0, $points)]);
        }
    }
}
