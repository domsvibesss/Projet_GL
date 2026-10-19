<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReclamationsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $ts = ['created_at' => $now, 'updated_at' => $now];

        $idEtudiant = fn (string $email) => DB::table('etudiants')->where('email', $email)->value('id');
        $gerant     = DB::table('gerants')->where('email', 'gerant1@zeduc.test')->value('id');
        $caissier   = DB::table('employes')->where('email', 'employe1@zeduc.test')->value('id');
        $pdg        = DB::table('administrateurs')->where('email', 'admin1@zeduc.test')->value('id');

        // type_reclamation : Plainte | Avis      statut : accepté | rejeté | en_cours
        $r1 = DB::table('reclamations')->insertGetId([
            'type_reclamation' => 'Plainte',
            'description'      => 'Mon plat est arrivé froid.',
            'statut'           => 'accepté',
            'etudiant_id'      => $idEtudiant('etudiant1@zeduc.test'),
            'gerant_id'        => $gerant,
        ] + $ts);

        $r2 = DB::table('reclamations')->insertGetId([
            'type_reclamation' => 'Avis',
            'description'      => 'Très bon service, merci à toute l\'équipe !',
            'statut'           => 'en_cours',
            'etudiant_id'      => $idEtudiant('etudiant2@zeduc.test'),
            'gerant_id'        => null,
        ] + $ts);

        $r3 = DB::table('reclamations')->insertGetId([
            'type_reclamation' => 'Plainte',
            'description'      => 'Il y a une erreur sur le montant de ma commande.',
            'statut'           => 'en_cours',
            'etudiant_id'      => $idEtudiant('etudiant3@zeduc.test'),
            'gerant_id'        => $gerant,
        ] + $ts);

        // reponses.statut : Validée | Rejetée  (l'employé propose, le gérant valide ou rejette)
        $rep1 = DB::table('reponses')->insertGetId([
            'auteur'         => 'Ngono Rose (Caissier)',
            'contenu'        => 'Nous nous excusons pour ce désagrément. Un jus offert vous attend à votre prochaine commande.',
            'statut'         => 'Validée',
            'reclamation_id' => $r1,
            'gerant_id'      => $gerant,
        ] + $ts);

        $rep2 = DB::table('reponses')->insertGetId([
            'auteur'         => 'Ngono Rose (Caissier)',
            'contenu'        => 'Nous vérifions votre ticket de caisse.',
            'statut'         => 'Rejetée',
            'reclamation_id' => $r3,
            'gerant_id'      => $gerant,
        ] + $ts);

        // Liaisons
        DB::table('employe_reclamation')->insert([
            ['employe_id' => $caissier, 'reclamation_id' => $r1] + $ts,    // REPONDRE
            ['employe_id' => $caissier, 'reclamation_id' => $r3] + $ts,
        ]);
        DB::table('administrateur_reclamation')->insert([
            ['administrateur_id' => $pdg, 'reclamation_id' => $r1] + $ts,  // CONSULTER
            ['administrateur_id' => $pdg, 'reclamation_id' => $r2] + $ts,
        ]);
        DB::table('administrateur_reponse')->insert([
            ['administrateur_id' => $pdg, 'reponse_id' => $rep1] + $ts,    // PRODUIRE
            ['administrateur_id' => $pdg, 'reponse_id' => $rep2] + $ts,
        ]);
    }
}
