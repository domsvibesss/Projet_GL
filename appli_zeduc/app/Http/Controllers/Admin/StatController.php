<?php
 
namespace App\Http\Controllers\Admin;
 
use App\Http\Controllers\Controller;
use App\Services\StatsService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

 
class StatsController extends Controller
{
    public function index(Request $request, StatsService $stats)
    {
        $periode = $request->validate([
            'periode' => ['nullable', Rule::in(['jour', 'semaine', 'mois'])],
        ])['periode'] ?? 'semaine';
 
        $from = StatsService::periodStart($periode);
 
        return view('admin.stats', [
            'periode'           => $periode,
            'commandes'         => $stats->commandes($from),
            'commandesParJour'  => $stats->commandesParJour($from),
            'paiements'         => $stats->paiements($from),
            'etudiants'         => $stats->etudiants($from),
            'points'            => $stats->points($from),
            'parrainages'       => $stats->parrainages($from),
            'reclamations'      => $stats->reclamations(),
            'plats'             => $stats->plats(),
            'promotionsActives' => $stats->promotionsActives(),
            'jeuxActifs'        => $stats->jeuxActifs(),
            'employes'          => $stats->employes(),
            'meilleursClients'  => $stats->meilleursClients($from, 10),
            'meilleursParrains' => $stats->meilleursParrains(5),
            'platsFavoris'      => $stats->platsFavoris(5),
        ]);
    }
 
    // Interrogé par le tableau de bord toutes les 10 à 30 s ("temps réel")
    public function live(StatsService $stats)
    {
        $aujourdhui = StatsService::periodStart('jour');
 
        return response()->json([
            'commandes'    => $stats->commandes($aujourdhui),
            'paiements'    => $stats->paiements($aujourdhui),
            'reclamations' => ['en_attente' => $stats->reclamations()['en_attente']],
        ]);
    }
}