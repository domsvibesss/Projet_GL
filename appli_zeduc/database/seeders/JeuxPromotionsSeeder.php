<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JeuxPromotionsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $ts = ['created_at' => $now, 'updated_at' => $now];

        $admin2 = DB::table('administrateurs')->where('email', 'admin2@zeduc.test')->value('id');

        // ---- Jeux ------------------------------------------------------------
        DB::table('jeux')->insert([
            ['nom' => 'Roue de la chance', 'description' => 'Tournez la roue et gagnez des points de fidélité.',
             'image' => null, 'date_debut' => now()->subDays(10), 'date_fin' => now()->addDays(20)] + $ts,
            ['nom' => 'Quiz ZeDuc',        'description' => 'Répondez au quiz sur la cuisine camerounaise.',
             'image' => null, 'date_debut' => now()->addDays(10), 'date_fin' => now()->addDays(40)] + $ts,
        ]);

        // ---- Promotions : les affiches de la page admin/promotions ----
        // prix_promo < prix_initial ; égaux = événement sans réduction (pas de « -X% » sur l'affiche)
        $promos = [
            ['Vendredrink !!!', "Profitez d'une réduction sur toutes les bières !!",
             'images/admin/promo-boissons.png', 1000, 500, now()->subDays(2), now()->addDays(12)],
            ['Soirée Champions League', "Tous vos matchs en DIRECT avec l'ambiance du Zeduc !",
             'images/admin/promo-champions.png', 2000, 2000, now()->subDays(1), now()->addDays(30)],
            ['Soirée Karaoké', "Profitez d'une réduction sur tous les plats à partir de 2 plats !",
             'images/admin/promo-karaoke.png', 4000, 3000, now()->addDays(5), now()->addDays(15)],
            ['Full Poulet', "Profitez d'une réduction de 20% sur tous les plats contenant du poulet !",
             'images/admin/promo-poulet.png', 3500, 2800, now()->subDays(2), now()->addDays(12)],
        ];
        foreach ($promos as [$titre, $description, $image, $initial, $promo, $debut, $fin]) {
            $promoId = DB::table('promotions')->insertGetId([
                'titre'             => $titre,
                'description'       => $description,
                'image'             => $image,
                'prix_initial'      => $initial,
                'prix_promo'        => $promo,
                'date_debut'        => $debut,
                'date_fin'          => $fin,
                'administrateur_id' => $admin2,
            ] + $ts);

            // Liaison "ORGANISER"
            DB::table('administrateur_promotion')->insert([
                'administrateur_id' => $admin2,
                'promotion_id'      => $promoId,
            ] + $ts);
        }

        // ---- Favoris ------------------------------------------------------------
        for ($i = 0; $i < 3; $i++) {
            DB::table('favoris')->insert(['date_enregistrement' => now()->subDays($i)] + $ts);
        }
    }
}
