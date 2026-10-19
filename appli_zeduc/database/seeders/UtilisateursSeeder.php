<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class UtilisateursSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $ts = ['created_at' => $now, 'updated_at' => $now];

        // Mot de passe commun à tous les comptes de test (1 majuscule + 1 chiffre, comme le cahier des charges)
        $mdp = Hash::make('Password1');

        // ---- Gérants -------------------------------------------------------
        $gerants = [
            ['nom' => 'Fouda',    'prenom' => 'Marie', 'email' => 'gerant1@zeduc.test', 'tel' => '+237690000101', 'date_naissance' => '1985-01-12'],
            ['nom' => 'Atangana', 'prenom' => 'Joel',  'email' => 'gerant2@zeduc.test', 'tel' => '+237690000102', 'date_naissance' => '1988-06-03'],
        ];
        foreach ($gerants as $g) {
            DB::table('gerants')->insert($g + ['mdp' => $mdp, 'date_enregistrement' => $now] + $ts);
        }

        // ---- Administrateurs -------------------------------------------------
        $admins = [
            ['poste' => 'PDG',                 'nom' => 'Tchamba', 'prenom' => 'Eric', 'email' => 'admin1@zeduc.test', 'tel' => '+237690000201', 'date_naissance' => '1975-03-20'],
            ['poste' => 'Directeur marketing', 'nom' => 'Nkoa',    'prenom' => 'Paul', 'email' => 'admin2@zeduc.test', 'tel' => '+237690000202', 'date_naissance' => '1980-05-10'],
        ];
        foreach ($admins as $a) {
            DB::table('administrateurs')->insert($a + ['mdp' => $mdp, 'date_enregistrement' => $now] + $ts);
        }

        // ---- Employés (le Livreur est un employé, poste = Livreur) -------------
        $employes = [
            ['poste' => 'Caissier',  'nom' => 'Ngono',  'prenom' => 'Rose',  'email' => 'employe1@zeduc.test', 'tel' => '+237690000301', 'date_naissance' => '1995-04-04', 'statut' => 'actif', 'gerant' => 'gerant1@zeduc.test'],
            ['poste' => 'Cuisinier', 'nom' => 'Biloa',  'prenom' => 'Marc',  'email' => 'employe2@zeduc.test', 'tel' => '+237690000302', 'date_naissance' => '1990-09-15', 'statut' => 'actif', 'gerant' => 'gerant1@zeduc.test'],
            ['poste' => 'Livreur',   'nom' => 'Kamga',  'prenom' => 'Paul',  'email' => 'employe3@zeduc.test', 'tel' => '+237690000303', 'date_naissance' => '1998-07-07', 'statut' => 'actif', 'gerant' => 'gerant1@zeduc.test'],
            ['poste' => 'Caissier',  'nom' => 'Essama', 'prenom' => 'Julie', 'email' => 'employe4@zeduc.test', 'tel' => '+237690000304', 'date_naissance' => '1996-11-30', 'statut' => 'parti', 'gerant' => 'gerant2@zeduc.test'],
        ];
        foreach ($employes as $e) {
            $gerantId = DB::table('gerants')->where('email', $e['gerant'])->value('id');
            unset($e['gerant']);
            DB::table('employes')->insert($e + ['mdp' => $mdp, 'date_enregistrement' => $now, 'gerant_id' => $gerantId] + $ts);
        }

        // ---- Étudiants -----------------------------------------------------------
        // Fonctionne avec le nom actuel de la colonne (codae_parrain) ET avec le nom corrigé (code_parrain)
        $colParrain = Schema::hasColumn('etudiants', 'code_parrain') ? 'code_parrain' : 'codae_parrain';

        $etudiants = [
            ['Mbarga',  'Alice',     'etudiant1@zeduc.test', 'PARR0001', 'actif',   60],
            ['Onana',   'Bruno',     'etudiant2@zeduc.test', 'PARR0002', 'actif',   45],
            ['Essomba', 'Jean',      'etudiant3@zeduc.test', 'PARR0003', 'actif',   40],
            ['Nguema',  'Clarisse',  'etudiant4@zeduc.test', 'PARR0004', 'actif',   30],
            ['Tchoumi', 'Patrick',   'etudiant5@zeduc.test', 'PARR0005', 'actif',   20],
            ['Fotso',   'Laure',     'etudiant6@zeduc.test', 'PARR0006', 'actif',   15],
            ['Mvondo',  'Yannick',   'etudiant7@zeduc.test', 'PARR0007', 'actif',   10],
            ['Abena',   'Sandrine',  'etudiant8@zeduc.test', 'PARR0008', 'inactif', 90],
        ];
        foreach ($etudiants as $i => [$nom, $prenom, $email, $code, $statut, $jours]) {
            DB::table('etudiants')->insert([
                'nom'              => $nom,
                'prenom'           => $prenom,
                'tel'              => '+2376900004' . str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT),
                'email'            => $email,
                'mdp'              => $mdp,
                'points_fidelite'  => 0,                       // recalculé dans CommandesSeeder
                $colParrain        => $code,
                'date_inscription' => now()->subDays($jours),
                'statut'           => $statut,
            ] + $ts);
        }
    }
}
