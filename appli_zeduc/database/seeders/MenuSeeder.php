<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $ts = ['created_at' => $now, 'updated_at' => $now];

        $admin1 = DB::table('administrateurs')->where('email', 'admin1@zeduc.test')->value('id');
        $admin2 = DB::table('administrateurs')->where('email', 'admin2@zeduc.test')->value('id');

        // nom, description, prix (FCFA), stock, administrateur qui gère le plat
        $plats = [
            ['Poulet DG',                  'Poulet sauté aux plantains et légumes',          3500, 30, $admin1],
            ['Ndolé et plantain',          'Ndolé aux crevettes avec plantains mûrs',        2500, 25, $admin1],
            ['Riz sauce tomate',           'Riz blanc et sauce tomate maison',               1500, 50, $admin1],
            ['Poisson braisé',             'Poisson braisé, bâton de manioc et piment',      3000, 20, $admin1],
            ['Koki et plantain',           'Koki de haricots à l\'huile de palme',           1000, 40, $admin1],
            ['Eru et water-fufu',          'Eru et waterleaf accompagnés de water-fufu',     2000, 20, $admin2],
            ['Brochettes de boeuf',        'Brochettes grillées (portion de 3)',             1500, 60, $admin2],
            ['Beignets haricots bouillie', 'Petit-déjeuner traditionnel',                     500, 80, $admin2],
            ['Jus de gingembre',           'Jus de gingembre frais, 50 cl',                   500, 70, $admin2],
            ['Spaghetti sauté',            'Spaghetti sautés aux légumes et à l\'oeuf',      1200, 45, $admin2],
        ];

        // plats.commande_id est une erreur de conception signalée à Rigeau (un plat peut être dans
        // plusieurs commandes). On ne la remplit que si la colonne existe encore.
        $aCommandeId = Schema::hasColumn('plats', 'commande_id');

        foreach ($plats as [$nom, $description, $prix, $stock, $adminId]) {
            $ligne = [
                'nom'               => $nom,
                'description'       => $description,
                'prix'              => $prix,
                'quantite'          => $stock,
                'administrateur_id' => $adminId,
            ] + $ts;
            if ($aCommandeId) {
                $ligne['commande_id'] = null;
            }
            $platId = DB::table('plats')->insertGetId($ligne);

            // Liaison "GERER" : quel administrateur gère quel plat
            DB::table('administrateur_plat')->insert([
                'administrateur_id' => $adminId,
                'plat_id'           => $platId,
            ] + $ts);
        }
    }
}
