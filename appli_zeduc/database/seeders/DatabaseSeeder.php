<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Ordre imposé par les clés étrangères : les tables "parents" d'abord.
     */
    public function run(): void
    {
        $this->call([
            UtilisateursSeeder::class,      // gerants, administrateurs, employes, etudiants
            MenuSeeder::class,              // plats + administrateur_plat
            JeuxPromotionsSeeder::class,    // jeux, promotions, favoris + administrateur_promotion
            CommandesSeeder::class,         // commandes, (commande_plat), paiements, points
            ReclamationsSeeder::class,      // reclamations, reponses + liaisons
            LiaisonsSeeder::class,          // etudiant_favori, etudiant_jeu, etudiant_promotion, administrateur_employe
        ]);
    }
}
