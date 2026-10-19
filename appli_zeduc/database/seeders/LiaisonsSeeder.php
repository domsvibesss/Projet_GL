<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LiaisonsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $ts = ['created_at' => $now, 'updated_at' => $now];

        $idEtudiant = fn (string $email) => DB::table('etudiants')->where('email', $email)->value('id');
        $favoris    = DB::table('favoris')->orderBy('id')->pluck('id')->all();
        $roue       = DB::table('jeux')->where('nom', 'Roue de la chance')->value('id');
        $promoEnCours = DB::table('promotions')->orderBy('id')->value('id');
        $pdg        = DB::table('administrateurs')->where('email', 'admin1@zeduc.test')->value('id');

        // SELECTIONNER : étudiants <-> favoris
        DB::table('etudiant_favori')->insert([
            ['etudiant_id' => $idEtudiant('etudiant1@zeduc.test'), 'favori_id' => $favoris[0]] + $ts,
            ['etudiant_id' => $idEtudiant('etudiant2@zeduc.test'), 'favori_id' => $favoris[1]] + $ts,
            ['etudiant_id' => $idEtudiant('etudiant6@zeduc.test'), 'favori_id' => $favoris[2]] + $ts,
        ]);

        // JOUER : étudiants <-> jeux
        DB::table('etudiant_jeu')->insert([
            ['etudiant_id' => $idEtudiant('etudiant1@zeduc.test'), 'jeu_id' => $roue] + $ts,
            ['etudiant_id' => $idEtudiant('etudiant2@zeduc.test'), 'jeu_id' => $roue] + $ts,
            ['etudiant_id' => $idEtudiant('etudiant3@zeduc.test'), 'jeu_id' => $roue] + $ts,
        ]);

        // BENEFICIER : étudiants <-> promotions
        DB::table('etudiant_promotion')->insert([
            ['etudiant_id' => $idEtudiant('etudiant1@zeduc.test'), 'promotion_id' => $promoEnCours] + $ts,
            ['etudiant_id' => $idEtudiant('etudiant5@zeduc.test'), 'promotion_id' => $promoEnCours] + $ts,
        ]);

        // ADMINISTRER : l'administrateur supervise les employés
        foreach (DB::table('employes')->pluck('id') as $employeId) {
            DB::table('administrateur_employe')->insert([
                'administrateur_id' => $pdg,
                'employe_id'        => $employeId,
            ] + $ts);
        }
    }
}
