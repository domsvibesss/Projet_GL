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

        // ---- Promotions (prix_promo toujours strictement inférieur à prix_initial) ----
        $promos = [
            [3500, 2800, now()->subDays(2), now()->addDays(12)],   // promotion en cours
            [1500, 1000, now()->addDays(5), now()->addDays(15)],   // promotion à venir
        ];
        foreach ($promos as [$initial, $promo, $debut, $fin]) {
            $promoId = DB::table('promotions')->insertGetId([
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
