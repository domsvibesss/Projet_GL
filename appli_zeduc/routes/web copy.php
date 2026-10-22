<?php

use Illuminate\Support\Facades\Route;

$commandes = [
    ['id' => '#101', 'client' => 'Marie K.', 'type' => 'Sur place', 'articles' => '2 × Poulet DG', 'heure' => '12h30', 'statut' => 'En attente'],
    ['id' => '#102', 'client' => 'Paul T.', 'type' => 'Livraison', 'articles' => '1 × Ndolé', 'heure' => '12h35', 'statut' => 'En préparation'],
    ['id' => '#103', 'client' => 'Awa N.', 'type' => 'Sur place', 'articles' => '1 × Riz sauté', 'heure' => '12h40', 'statut' => 'Prête'],
    ['id' => '#104', 'client' => 'Luc B.', 'type' => 'Livraison', 'articles' => '3 × Brochettes', 'heure' => '12h45', 'statut' => 'Livrée'],
];

$compteurs = [
    ['statut' => 'En attente', 'label' => 'En attente', 'valeur' => 4],
    ['statut' => 'En préparation', 'label' => 'En preparation', 'valeur' => 3],
    ['statut' => 'Prête', 'label' => 'Prêtes', 'valeur' => 8],
    ['statut' => 'Livrée', 'label' => 'Livrées', 'valeur' => 12],
];

$statsSemaine = [
    ['code' => 'lun', 'label' => 'lun', 'nom' => 'Lundi', 'valeur' => 12],
    ['code' => 'mar', 'label' => 'Mar', 'nom' => 'Mardi', 'valeur' => 15],
    ['code' => 'mer', 'label' => 'Mer', 'nom' => 'Mercredi', 'valeur' => 10],
    ['code' => 'jeu', 'label' => 'Jeu', 'nom' => 'Jeudi', 'valeur' => 18],
    ['code' => 'ven', 'label' => 'Ven', 'nom' => 'Vendredi', 'valeur' => 24],
    ['code' => 'sam', 'label' => 'Sam', 'nom' => 'Samedi', 'valeur' => 21],
    ['code' => 'dim', 'label' => 'Dim', 'nom' => 'Dimanche', 'valeur' => 12],
];

Route::redirect('/', '/employe/commandes');

Route::prefix('employe')->name('employe.')->group(function () use ($commandes, $compteurs, $statsSemaine) {

    Route::get('/commandes', fn () => view('employe.commandes', [
        'commandes' => $commandes,
        'compteurs' => $compteurs,
    ]))->name('commandes');

    Route::get('/plat-du-jour', fn () => view('employe.plat-du-jour', [
        'platDuJour' => 'poulet DG',
        'plats' => [
            ['nom' => 'Poulet DG', 'emoji' => '🍗'],
            ['nom' => 'Porc braise', 'emoji' => '🥩'],
            ['nom' => 'Poisson braise', 'emoji' => '🐟'],
            ['nom' => 'Water fufu + eru', 'emoji' => '🥬'],
            ['nom' => 'Okok sucre + baton de manioc', 'emoji' => '🥗'],
            ['nom' => 'Ndole', 'emoji' => '🥘'],
        ],
        'jours' => ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'],
    ]))->name('plat-du-jour');

    Route::get('/reclamations', fn () => view('employe.reclamations', [
        'reclamations' => [
            ['id' => '#ND0987', 'plat' => 'Ndole (Plantains)', 'date' => '2026-10-10, 14:39', 'prix' => 1500, 'statut' => 'En cours', 'message' => '“Le plat est arrivé froid”'],
            ['id' => '#KK2341', 'plat' => 'Koki (Plantains)', 'date' => '2026-10-09, 20:40', 'prix' => 1000, 'statut' => 'Traitée', 'message' => '“Il manquait une portion de plantain”'],
            ['id' => '#KK6749', 'plat' => 'Koki (Plantains)', 'date' => '2026-10-07, 18:29', 'prix' => 1000, 'statut' => 'En attente', 'message' => '“Il manquait la mayonnaise dans mon poulet pane”'],
        ],
    ]))->name('reclamations');

    Route::get('/statistiques', fn () => view('employe.statistiques', [
        'stats' => $statsSemaine,
        'resume' => [
            ['valeur' => '112', 'label' => 'Commandes', 'grand' => true],
            ['valeur' => 'POULET DG', 'label' => 'Plat le plus vendu', 'grand' => false],
            ['valeur' => 'ROTI DE POULET', 'label' => 'Plat le moins vendu', 'grand' => false],
        ],
    ]))->name('statistiques');

    Route::get('/statistiques/{jour}', function (string $jour) use ($statsSemaine) {
        $details = [
            'lun' => [
                ['nom' => 'poulet pane', 'nb' => 5],
                ['nom' => 'Eru + Waterfufu', 'nb' => 4],
                ['nom' => 'Roti de poulet', 'nb' => 2],
                ['nom' => 'Poulet DG', 'nb' => 1],
            ],
        ];
        $infos = collect($statsSemaine)->firstWhere('code', $jour) ?? $statsSemaine[0];

        return view('employe.statistique-journalier', [
            'nomJour' => $infos['nom'],
            'total' => $infos['valeur'],
            'plats' => $details[$infos['code']] ?? [],
        ]);
    })->name('statistique-journalier');
});

Route::prefix('gerant')->name('gerant.')->group(function () use ($commandes, $compteurs) {

    Route::get('/commandes', fn () => view('gerant.commandes', [
        'commandes' => $commandes,
        'compteurs' => $compteurs,
    ]))->name('commandes');

    Route::get('/employes', fn () => view('gerant.employes', [
        'postes' => ['Cuisinier', 'Assistant cuisinier', 'Livreur', 'Manager'],
        'employes' => [
            ['id' => 'auto-0006', 'poste' => 'Livreur', 'nom' => 'Abada', 'prenom' => 'Kyle', 'tel' => '6 77 12 45 18', 'email' => 'kyle.abada@example.com'],
            ['id' => 'auto-0005', 'poste' => 'Assistant cuisinier', 'nom' => 'KENMOE', 'prenom' => 'Valdes', 'tel' => '6 93 30 71 37', 'email' => 'valdes.kenmoe@example.com'],
            ['id' => 'auto-0004', 'poste' => 'Cuisinier', 'nom' => 'Fouda', 'prenom' => 'Pierre', 'tel' => '6 98 24 36 61', 'email' => 'pierre.fouda@example.com'],
            ['id' => 'auto-0003', 'poste' => 'Cuisinier', 'nom' => 'Mbarga', 'prenom' => 'Jean-Paul', 'tel' => '6 70 26 22 50', 'email' => 'jp.mbarga@example.com'],
            ['id' => 'auto-0002', 'poste' => 'Cuisinier', 'nom' => 'Atangana', 'prenom' => 'Hervé', 'tel' => '6 52 27 12 48', 'email' => 'herve.atangana@example.com'],
            ['id' => 'auto-0001', 'poste' => 'Cuisinier', 'nom' => 'Essomba', 'prenom' => 'Christine', 'tel' => '6 90 73 71 13', 'email' => 'christine.essomba@example.com'],
        ],
    ]))->name('employes');

    Route::get('/statistiques', fn () => view('gerant.statistiques', [
        'kpis' => [
            ['titre' => 'Commandes d’Aujourd’hui', 'valeur' => '—'],
            ['titre' => 'Commandes en Cours', 'valeur' => '—'],
            ['titre' => 'Chiffres d’Affaires', 'valeur' => '—'],
            ['titre' => 'Clients Actifs', 'valeur' => '—'],
        ],
        'points' => ['emis' => '48 290', 'utilises' => '16 840', 'taux' => '34,9 %'],
        'graduations' => [2000, 1500, 1000, 500, 0],
        'parrainage' => ['invitations' => '1 248', 'converties' => '438', 'conversion' => '35 %'],
        'parrains' => [
            ['nom' => 'Dominique Daphne', 'filleuls' => 17, 'couleur' => 'bg-pink-300'],
            ['nom' => 'Pethe William', 'filleuls' => 15, 'couleur' => 'bg-yellow-200'],
            ['nom' => 'Messang Louis', 'filleuls' => 10, 'couleur' => 'bg-purple-300'],
            ['nom' => 'Rigeau Dajel', 'filleuls' => 7, 'couleur' => 'bg-green-300'],
        ],
    ]))->name('statistiques');

    Route::get('/reclamations', fn () => view('gerant.reclamations', [
        'total' => 23,
        'reclamations' => [
            ['id' => '#KK1234', 'client' => 'Jennifer Bela', 'sujet' => 'Commande arrivee froide', 'date' => 'Aujourd’hui, 11:42', 'priorite' => 'Urgente', 'statut' => 'Nouvelle', 'message' => '“Il manquait la mayonnaise dans mon poulet pane”'],
            ['id' => '#KK1234', 'client' => 'Danielle Lili', 'sujet' => 'Article manquant', 'date' => 'Aujourd’hui, 10:18', 'priorite' => 'Normale', 'statut' => 'En cours', 'message' => '“Il manque une boisson dans ma commande”'],
            ['id' => '#KK1234', 'client' => 'Yely Kanoute', 'sujet' => 'Erreur sur la formule', 'date' => 'Hier, 19:07', 'priorite' => 'Faible', 'statut' => 'Resolue', 'message' => '“J’ai reçu la formule simple au lieu de la complète”'],
            ['id' => '#KK1234', 'client' => 'Dominique Daphne', 'sujet' => 'Retard de livraison', 'date' => 'Hier, 12:31', 'priorite' => 'Normale', 'statut' => 'Nouvelle', 'message' => '“Ma commande est arrivée avec 40 minutes de retard”'],
        ],
    ]))->name('reclamations');
});
