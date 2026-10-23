<?php

namespace App\Http\Controllers\Employe;

use App\Http\Controllers\Controller;
use App\Models\Commande;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Espace employé (cahier des charges, §3) :
 *  - gestion des commandes (consulter, préparer, valider, rejeter)
 *  - mise à jour temporaire du menu (plat épuisé, stock, plat du jour)
 *  - réclamations (consulter, proposer une réponse que le gérant validera)
 *  - statistiques de vente (hebdomadaires / journalières)
 *
 * Les tables sont lues avec le Query Builder car les migrations (users + tables filles
 * employes / etudiants / gerants, clé user_id) ont évolué plus vite que certains modèles
 * (Employe, Reclamation, Reponse). Le modèle Commande est utilisé pour sa machine à états.
 */
class EmployeController extends Controller
{
    /** Règles de fidélité du cahier des charges : 1 000 F dépensés = 1 point. */
    private const PAS_POINT = 1000;

    /** Validité des points : 12 mois (cahier des charges §2.4.2). */
    private const VALIDITE_POINTS_MOIS = 12;

    /** Points offerts au parrain à la 1re commande livrée de son filleul (valeur du seeder, à ajuster). */
    private const POINTS_PARRAINAGE = 5;

    /** Statuts d'une réclamation considérés comme clos. */
    private const RECLAMATION_CLOSE = ['accepté', 'rejeté'];

    // =====================================================================
    //  COMMANDES
    // =====================================================================

    /**
     * GET /employe/commandes
     * Filtres (query string) : etat, destination, date (Y-m-d), recherche (nom/prénom étudiant),
     * ordre (asc|desc), par_page. Pensé pour le rafraîchissement AJAX des tableaux.
     */
    public function commandes(Request $request): JsonResponse
    {
        $this->employeConnecte($request);

        $data = $request->validate([
            'etat'        => 'nullable|in:' . implode(',', array_keys(Commande::TRANSITIONS)),
            'destination' => 'nullable|string|max:50',
            'date'        => 'nullable|date_format:Y-m-d',
            'recherche'   => 'nullable|string|max:100',
            'ordre'       => 'nullable|in:asc,desc',
            'par_page'    => 'nullable|integer|min:1|max:100',
        ]);

        $commandes = DB::table('commandes as c')
            ->join('users as u', 'u.id', '=', 'c.etudiant_id')
            ->when($data['etat'] ?? null, fn ($q, $v) => $q->where('c.etat', $v))
            ->when($data['destination'] ?? null, fn ($q, $v) => $q->where('c.destination', $v))
            ->when($data['date'] ?? null, fn ($q, $v) => $q->whereDate('c.date_commande', $v))
            ->when($data['recherche'] ?? null, function ($q, $v) {
                $q->where(fn ($w) => $w->where('u.name', 'like', "%{$v}%")->orWhere('u.prenom', 'like', "%{$v}%"));
            })
            ->orderBy('c.date_commande', $data['ordre'] ?? 'desc')
            ->select([
                'c.id', 'c.montant_local', 'c.points_accumules', 'c.date_commande', 'c.etat',
                'c.destination', 'c.employe_id', 'c.etudiant_id',
                'u.name as etudiant_nom', 'u.prenom as etudiant_prenom', 'u.telephone as etudiant_telephone',
            ])
            ->paginate($data['par_page'] ?? 20);

        // Colonne « Articles » de la page Commandes : on récupère les plats de TOUTES
        // les commandes de la page en UNE seule requête (et non une par commande).
        $lignes = DB::table('commande_plat as cp')
            ->join('plats as p', 'p.id', '=', 'cp.plat_id')
            ->whereIn('cp.commande_id', $commandes->pluck('id'))
            ->get(['cp.commande_id', 'p.nom', 'cp.quantite'])
            ->groupBy('commande_id');

        // Ajoute à chaque commande un texte du type « 2 × Poulet DG, 1 × Ndolé »
        $commandes->getCollection()->transform(function ($c) use ($lignes) {
            $c->articles = ($lignes[$c->id] ?? collect())
                ->map(fn ($l) => "{$l->quantite} × {$l->nom}")
                ->implode(', ');
            return $c;
        });

        return response()->json($commandes);
    }

    /** GET /employe/commandes/{id} : détail d'une commande avec ses lignes. */
    public function commande(Request $request, int $id): JsonResponse
    {
        $this->employeConnecte($request);

        $commande = DB::table('commandes as c')
            ->join('users as u', 'u.id', '=', 'c.etudiant_id')
            ->leftJoin('users as e', 'e.id', '=', 'c.employe_id')
            ->where('c.id', $id)
            ->first([
                'c.*',
                'u.name as etudiant_nom', 'u.prenom as etudiant_prenom', 'u.telephone as etudiant_telephone',
                'e.name as employe_nom', 'e.prenom as employe_prenom',
            ]);

        if (! $commande) {
            $this->erreur('Commande introuvable.', 404);
        }

        $lignes = DB::table('commande_plat as cp')
            ->join('plats as p', 'p.id', '=', 'cp.plat_id')
            ->where('cp.commande_id', $id)
            ->get(['p.id as plat_id', 'p.nom', 'p.prix', 'cp.quantite'])
            ->map(function ($l) {
                $l->sous_total = round($l->prix * $l->quantite, 2);
                return $l;
            });

        return response()->json([
            'commande'                => $commande,
            'lignes'                  => $lignes,
            'transitions_possibles'   => Commande::TRANSITIONS[$commande->etat] ?? [],
        ]);
    }

    /** PATCH /employe/commandes/{id}/en-preparation : en_attente -> en_cours */
    public function enPreparation(Request $request, int $id): JsonResponse
    {
        return $this->changerEtat($request, $id, Commande::EN_COURS);
    }

    /** PATCH /employe/commandes/{id}/prete : en_cours -> prete */
    public function prete(Request $request, int $id): JsonResponse
    {
        return $this->changerEtat($request, $id, Commande::PRETE);
    }

    /** PATCH /employe/commandes/{id}/livree : prete -> effectué (attribue les points de fidélité). */
    public function livree(Request $request, int $id): JsonResponse
    {
        return $this->changerEtat($request, $id, Commande::EFFECTUE);
    }

    /** PATCH /employe/commandes/{id}/rejeter : en_attente | en_cours -> rejeté */
    public function rejeter(Request $request, int $id): JsonResponse
    {
        return $this->changerEtat($request, $id, Commande::REJETE);
    }

    /**
     * Applique une transition d'état en respectant Commande::TRANSITIONS,
     * dans une transaction avec verrou pour éviter que deux employés agissent en même temps.
     */
    private function changerEtat(Request $request, int $id, string $nouvelEtat): JsonResponse
    {
        $employe = $this->employeConnecte($request);

        $commande = DB::transaction(function () use ($id, $nouvelEtat, $employe) {
            $commande = Commande::query()->lockForUpdate()->find($id);

            if (! $commande) {
                $this->erreur('Commande introuvable.', 404);
            }

            if (! $commande->peutPasserA($nouvelEtat)) {
                $this->erreur(
                    "Transition impossible : la commande est « {$commande->etat} » et ne peut pas passer à « {$nouvelEtat} ».",
                    422
                );
            }

            $commande->etat = $nouvelEtat;
            // Le premier employé qui prend la commande en charge en devient le responsable.
            $commande->employe_id ??= $employe->user_id;
            $commande->save();

            // Table de liaison Gestion_Commande : tous les employés qui interviennent sur la commande.
            DB::table('commande_employe')->insertOrIgnore([
                'commande_id' => $commande->id,
                'employe_id'  => $employe->user_id,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            if ($nouvelEtat === Commande::EFFECTUE) {
                $this->attribuerPoints($commande);
            }

            return $commande;
        });

        return response()->json([
            'message'  => 'État de la commande mis à jour.',
            'commande' => $commande->fresh(),
        ]);
    }

    /**
     * À la livraison : points d'achat pour l'étudiant et, si c'est la 1re commande livrée
     * d'un filleul, points de parrainage pour son parrain (cahier des charges §1.2 et §2.4.3).
     */
    private function attribuerPoints(Commande $commande): void
    {
        $now = now();
        $expiration = $now->copy()->addMonths(self::VALIDITE_POINTS_MOIS);

        $points = intdiv((int) $commande->montant_local, self::PAS_POINT);
        $commande->forceFill(['points_accumules' => $points])->save();

        if ($points > 0) {
            $this->crediterPoints($commande->etudiant_id, $points, 'achat', $commande->id, $now, $expiration);
        }

        // Parrainage : récompense unique, à la première commande livrée du filleul.
        $parrainage = DB::table('parrainages')
            ->where('filleul_id', $commande->etudiant_id)
            ->where('etat', 'en_attente')
            ->lockForUpdate()
            ->first();

        $autresLivrees = Commande::where('etudiant_id', $commande->etudiant_id)
            ->where('etat', Commande::EFFECTUE)
            ->where('id', '!=', $commande->id)
            ->exists();

        if ($parrainage && ! $autresLivrees) {
            $this->crediterPoints($parrainage->parrain_id, self::POINTS_PARRAINAGE, 'parrainage', $commande->id, $now, $expiration);
            DB::table('parrainages')->where('id', $parrainage->id)->update(['etat' => 'validé', 'updated_at' => $now]);
        }
    }

    private function crediterPoints(int $etudiantId, int $quantite, string $origine, int $commandeId, Carbon $date, Carbon $expiration): void
    {
        DB::table('points')->insert([
            'quantite'        => $quantite,
            'origine'         => $origine,
            'date_reception'  => $date,
            'date_expiration' => $expiration,
            'commande_id'     => $commandeId,
            'etudiant_id'     => $etudiantId,
            'jeu_id'          => null,
            'created_at'      => $date,
            'updated_at'      => $date,
        ]);

        DB::table('etudiants')->where('user_id', $etudiantId)->increment('points_fidelite', $quantite);
    }

    // =====================================================================
    //  MENU
    // =====================================================================

    /**
     * GET /employe/plats : le menu avec disponibilité et marqueur « plat du jour ».
     * Filtre optionnel : disponibles=1 pour ne garder que les plats en stock.
     */
    public function plats(Request $request): JsonResponse
    {
        $this->employeConnecte($request);

        $platDuJourId = $this->platDuJourAujourdhui()?->plat_id;

        $plats = DB::table('plats')
            ->when($request->boolean('disponibles'), fn ($q) => $q->where('quantite', '>', 0))
            ->orderBy('nom')
            ->get(['id', 'nom', 'description', 'prix', 'quantite'])
            ->map(function ($p) use ($platDuJourId) {
                $p->disponible   = $p->quantite > 0;
                $p->plat_du_jour = $p->id === $platDuJourId;
                return $p;
            });

        return response()->json(['data' => $plats]);
    }

    /** PATCH /employe/plats/{id}/quantite : met à jour le stock (réapprovisionnement ou correction). */
    public function mettreAJourStock(Request $request, int $id): JsonResponse
    {
        $this->employeConnecte($request);

        $data = $request->validate(['quantite' => 'required|integer|min:0|max:100000']);

        $plat = $this->trouverPlat($id);
        DB::table('plats')->where('id', $id)->update(['quantite' => $data['quantite'], 'updated_at' => now()]);

        return response()->json([
            'message' => "Stock de « {$plat->nom} » mis à jour.",
            'plat'    => DB::table('plats')->where('id', $id)->first(['id', 'nom', 'prix', 'quantite']),
        ]);
    }

    /** PATCH /employe/plats/{id}/epuise : raccourci « plat épuisé » (quantité = 0). */
    public function marquerEpuise(Request $request, int $id): JsonResponse
    {
        $this->employeConnecte($request);

        $plat = $this->trouverPlat($id);
        DB::table('plats')->where('id', $id)->update(['quantite' => 0, 'updated_at' => now()]);

        return response()->json(['message' => "« {$plat->nom} » est marqué comme épuisé."]);
    }

    /** GET /employe/plat-du-jour : plat du jour d'aujourd'hui (data = null s'il n'y en a pas). */
    public function platDuJour(Request $request): JsonResponse
    {
        $this->employeConnecte($request);

        $pdj = $this->platDuJourAujourdhui();

        return response()->json([
            'date' => today()->toDateString(),
            'data' => $pdj ? DB::table('plats')->where('id', $pdj->plat_id)->first(['id', 'nom', 'description', 'prix', 'quantite']) : null,
        ]);
    }

    /** PATCH /employe/plats/{id}/plat-du-jour : définit le plat du jour (un seul par jour). */
    public function definirPlatDuJour(Request $request, int $id): JsonResponse
    {
        $employe = $this->employeConnecte($request);

        $plat = $this->trouverPlat($id);
        if ($plat->quantite <= 0) {
            $this->erreur("« {$plat->nom} » est épuisé : il ne peut pas être plat du jour.", 422);
        }

        $aujourdhui = today()->toDateString();

        DB::transaction(function () use ($id, $employe, $aujourdhui) {
            // L'éventuel plat du jour précédent perd son marqueur…
            DB::table('plat_jour')
                ->where('date_service', $aujourdhui)
                ->where('plat_id', '!=', $id)
                ->update(['est_plat_du_jour' => false, 'updated_at' => now()]);

            // … et celui-ci le reçoit (contrainte unique plat_id + date_service).
            $existe = DB::table('plat_jour')->where('plat_id', $id)->where('date_service', $aujourdhui)->exists();

            if ($existe) {
                DB::table('plat_jour')->where('plat_id', $id)->where('date_service', $aujourdhui)->update([
                    'est_plat_du_jour' => true,
                    'employe_id'       => $employe->user_id,
                    'updated_at'       => now(),
                ]);
            } else {
                DB::table('plat_jour')->insert([
                    'plat_id'          => $id,
                    'date_service'     => $aujourdhui,
                    'est_plat_du_jour' => true,
                    'employe_id'       => $employe->user_id,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }
        });

        return response()->json(['message' => "« {$plat->nom} » est le plat du jour."]);
    }

    /** DELETE /employe/plat-du-jour : retire le plat du jour d'aujourd'hui. */
    public function retirerPlatDuJour(Request $request): JsonResponse
    {
        $this->employeConnecte($request);

        $retires = DB::table('plat_jour')
            ->where('date_service', today()->toDateString())
            ->where('est_plat_du_jour', true)
            ->update(['est_plat_du_jour' => false, 'updated_at' => now()]);

        if ($retires === 0) {
            $this->erreur("Aucun plat du jour n'est défini aujourd'hui.", 404);
        }

        return response()->json(['message' => 'Le plat du jour a été retiré.']);
    }

    private function platDuJourAujourdhui(): ?object
    {
        return DB::table('plat_jour')
            ->where('date_service', today()->toDateString())
            ->where('est_plat_du_jour', true)
            ->first();
    }

    private function trouverPlat(int $id): object
    {
        $plat = DB::table('plats')->where('id', $id)->first();

        if (! $plat) {
            $this->erreur('Plat introuvable.', 404);
        }

        return $plat;
    }

    // =====================================================================
    //  RÉCLAMATIONS
    // =====================================================================

    /** GET /employe/reclamations : filtres statut, type, par_page. */
    public function reclamations(Request $request): JsonResponse
    {
        $this->employeConnecte($request);

        $data = $request->validate([
            'statut'   => 'nullable|string|max:50',
            'type'     => 'nullable|string|max:50',
            'par_page' => 'nullable|integer|min:1|max:100',
        ]);

        $reclamations = DB::table('reclamations as r')
            ->join('users as u', 'u.id', '=', 'r.etudiant_id')
            ->when($data['statut'] ?? null, fn ($q, $v) => $q->where('r.statut', $v))
            ->when($data['type'] ?? null, fn ($q, $v) => $q->where('r.type_reclamation', $v))
            ->orderByDesc('r.created_at')
            ->select([
                'r.id', 'r.type_reclamation', 'r.description', 'r.statut', 'r.created_at',
                'u.name as etudiant_nom', 'u.prenom as etudiant_prenom',
            ])
            ->paginate($data['par_page'] ?? 20);

        return response()->json($reclamations);
    }

    /** GET /employe/reclamations/{id} : réclamation et historique des réponses. */
    public function reclamation(Request $request, int $id): JsonResponse
    {
        $this->employeConnecte($request);

        $reclamation = DB::table('reclamations as r')
            ->join('users as u', 'u.id', '=', 'r.etudiant_id')
            ->where('r.id', $id)
            ->first(['r.*', 'u.name as etudiant_nom', 'u.prenom as etudiant_prenom']);

        if (! $reclamation) {
            $this->erreur('Réclamation introuvable.', 404);
        }

        $reponses = DB::table('reponses')
            ->where('reclamation_id', $id)
            ->orderBy('created_at')
            ->get(['id', 'auteur', 'contenu', 'statut', 'created_at']);

        return response()->json(['reclamation' => $reclamation, 'reponses' => $reponses]);
    }

    /**
     * POST /employe/reclamations/{id}/reponses  { contenu }
     * L'employé PROPOSE une réponse ; elle reste « en_attente » jusqu'à ce que le gérant
     * la valide ou la rejette (cahier des charges §4).
     */
    public function repondreReclamation(Request $request, int $id): JsonResponse
    {
        $employe = $this->employeConnecte($request);

        $data = $request->validate(['contenu' => 'required|string|min:5|max:2000']);

        $reponse = DB::transaction(function () use ($id, $employe, $data) {
            $reclamation = DB::table('reclamations')->where('id', $id)->lockForUpdate()->first();

            if (! $reclamation) {
                $this->erreur('Réclamation introuvable.', 404);
            }

            if (in_array($reclamation->statut, self::RECLAMATION_CLOSE, true)) {
                $this->erreur("Cette réclamation est déjà clôturée ({$reclamation->statut}).", 422);
            }

            $enAttente = DB::table('reponses')
                ->where('reclamation_id', $id)
                ->where('statut', 'en_attente')
                ->exists();

            if ($enAttente) {
                $this->erreur('Une réponse est déjà en attente de validation par le gérant.', 422);
            }

            $now = now();

            $reponseId = DB::table('reponses')->insertGetId([
                'auteur'         => trim("{$employe->prenom} {$employe->name}") . " ({$employe->poste})",
                'contenu'        => $data['contenu'],
                'statut'         => 'en_attente',
                'reclamation_id' => $id,
                'gerant_id'      => null,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);

            // Table de liaison Repondre : employé <-> réclamation.
            DB::table('employe_reclamation')->insertOrIgnore([
                'employe_id'     => $employe->user_id,
                'reclamation_id' => $id,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);

            // La réclamation est désormais prise en charge.
            if ($reclamation->statut === 'en_attente') {
                DB::table('reclamations')->where('id', $id)->update(['statut' => 'en_cours', 'updated_at' => $now]);
            }

            return DB::table('reponses')->where('id', $reponseId)->first();
        });

        return response()->json([
            'message' => 'Réponse envoyée au gérant pour validation.',
            'reponse' => $reponse,
        ], 201);
    }

    // =====================================================================
    //  STATISTIQUES
    // =====================================================================

    /**
     * GET /employe/statistiques/hebdomadaire?date=Y-m-d
     * Semaine (lundi → dimanche) contenant `date` ; semaine en cours par défaut.
     */
    public function statistiquesHebdomadaires(Request $request): JsonResponse
    {
        $this->employeConnecte($request);

        $data = $request->validate(['date' => 'nullable|date_format:Y-m-d']);
        $ref = isset($data['date']) ? Carbon::parse($data['date']) : today();

        return response()->json($this->statistiques($ref->copy()->startOfWeek(), $ref->copy()->endOfWeek()));
    }

    /** GET /employe/statistiques/journaliere?date=Y-m-d : aujourd'hui par défaut. */
    public function statistiquesJournalieres(Request $request): JsonResponse
    {
        $this->employeConnecte($request);

        $data = $request->validate(['date' => 'nullable|date_format:Y-m-d']);
        $ref = isset($data['date']) ? Carbon::parse($data['date']) : today();

        return response()->json($this->statistiques($ref->copy()->startOfDay(), $ref->copy()->endOfDay()));
    }

    /**
     * Agrège les commandes de la période. Le chiffre d'affaires ne compte que les commandes
     * livrées (état « effectué »). Le regroupement par jour est fait en PHP pour rester
     * indépendant du SGBD (MySQL, SQLite, PostgreSQL).
     */
    private function statistiques(Carbon $debut, Carbon $fin): array
    {
        $commandes = DB::table('commandes')
            ->whereBetween('date_commande', [$debut, $fin])
            ->get(['id', 'montant_local', 'etat', 'destination', 'date_commande']);

        $livrees = $commandes->where('etat', Commande::EFFECTUE);
        $chiffreAffaires = (float) $livrees->sum('montant_local');

        $parEtat = [];
        foreach (array_keys(Commande::TRANSITIONS) as $etat) {
            $parEtat[$etat] = $commandes->where('etat', $etat)->count();
        }

        // Une entrée par jour de la période, même sans commande.
        $parJour = [];
        for ($jour = $debut->copy(); $jour->lte($fin); $jour->addDay()) {
            $cle = $jour->toDateString();
            $duJour = $commandes->filter(fn ($c) => Carbon::parse($c->date_commande)->toDateString() === $cle);
            $livreesDuJour = $duJour->where('etat', Commande::EFFECTUE);

            $parJour[] = [
                'date'             => $cle,
                'commandes'        => $duJour->count(),
                'livrees'          => $livreesDuJour->count(),
                'chiffre_affaires' => (float) $livreesDuJour->sum('montant_local'),
            ];
        }

        $platsPopulaires = DB::table('commande_plat as cp')
            ->join('commandes as c', 'c.id', '=', 'cp.commande_id')
            ->join('plats as p', 'p.id', '=', 'cp.plat_id')
            ->where('c.etat', Commande::EFFECTUE)
            ->whereBetween('c.date_commande', [$debut, $fin])
            ->groupBy('p.id', 'p.nom')
            ->orderByDesc(DB::raw('SUM(cp.quantite)'))
            ->limit(5)
            ->get(['p.id', 'p.nom', DB::raw('SUM(cp.quantite) as quantite_vendue')]);

        return [
            'periode' => ['debut' => $debut->toDateString(), 'fin' => $fin->toDateString()],
            'resume'  => [
                'total_commandes'  => $commandes->count(),
                'commandes_livrees' => $livrees->count(),
                'chiffre_affaires' => $chiffreAffaires,
                'panier_moyen'     => $livrees->count() > 0 ? round($chiffreAffaires / $livrees->count(), 2) : 0,
                'par_etat'         => $parEtat,
                'par_destination'  => $commandes->groupBy('destination')->map->count(),
            ],
            'par_jour'         => $parJour,
            'plats_populaires' => $platsPopulaires,
        ];
    }

    // =====================================================================
    //  OUTILS
    // =====================================================================

    /**
     * Vérifie que l'utilisateur connecté est un employé actif et renvoie son profil
     * (colonnes de `employes` + identité issue de `users`). Fonctionne avec n'importe quel
     * guard : le middleware d'authentification de la route s'occupe de la connexion.
     */
    private function employeConnecte(Request $request): object
    {
        $user = $request->user();

        if (! $user || $user->role !== 'employe') {
            $this->erreur('Accès réservé aux employés.', 403);
        }

        $employe = DB::table('employes')
            ->join('users', 'users.id', '=', 'employes.user_id')
            ->where('employes.user_id', $user->id)
            ->first(['employes.user_id', 'employes.poste', 'employes.statut', 'users.name', 'users.prenom']);

        if (! $employe || $employe->statut !== 'actif') {
            $this->erreur('Votre compte employé est inactif.', 403);
        }

        return $employe;
    }

    /** Interrompt la requête avec une réponse JSON d'erreur (annule aussi la transaction en cours). */
    private function erreur(string $message, int $code): never
    {
        throw new HttpResponseException(response()->json(['message' => $message], $code));
    }
}
