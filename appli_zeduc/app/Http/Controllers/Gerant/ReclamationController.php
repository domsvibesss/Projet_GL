<?php

namespace App\Http\Controllers\Gerant;

use App\Http\Controllers\Controller;
use App\Models\Reclamation;
use App\Models\Reponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Page « Réclamations » du gérant : voir les réclamations (R) et y répondre (U).
 * La vue reçoit $reclamations et $total au même format que ses données de démo.
 */
class ReclamationController extends Controller
{
    public function index()
    {
        $reclamations = Reclamation::with(['etudiant', 'derniereReponse'])->latest()->get();

        return view('reclamations', [
            'total'        => $reclamations->count(),
            'reclamations' => $reclamations->map(fn (Reclamation $r) => [
                'id'       => $r->numero,                                        // REC-0001
                'client'   => $r->etudiant?->nom_complet ?? 'Étudiant supprimé',
                'sujet'    => Str::limit($r->description, 30),
                'date'     => $this->date($r),
                'priorite' => $r->priorite_affichee,
                'statut'   => $r->statut_affiche,
                'message'  => $r->description,
                'url'      => route('gerant.reclamations.repondre', $r),        // utilisé par reclamations.js
            ])->all(),
        ]);
    }

    // « Envoyer la réponse » : la réponse du gérant est enregistrée dans la table reponses
    public function repondre(Request $request, Reclamation $reclamation)
    {
        $data = $request->validate([
            'contenu' => ['required', 'string', 'max:2000'],
        ], [
            'contenu.required' => "Écris une réponse avant de l'envoyer.",
        ]);

        $reclamation->reponses()->create([
            'auteur'  => 'Gérant',
            'contenu' => $data['contenu'],
            'statut'  => Reponse::STATUT_VALIDEE,   // la réponse du gérant n'a pas besoin d'être validée
        ]);

        return redirect()->route('gerant.reclamations.index')
            ->with('success', 'Réponse envoyée à '.($reclamation->etudiant?->nom_complet ?? "l'étudiant").'.');
    }

    // Ex. « Aujourd'hui, 11:42 », « Hier, 19:07 », « 18/10/2026, 12:31 »
    private function date(Reclamation $r): string
    {
        $date = $r->created_at;

        return match (true) {
            $date === null       => '',
            $date->isToday()     => "Aujourd'hui, ".$date->format('H:i'),
            $date->isYesterday() => 'Hier, '.$date->format('H:i'),
            default              => $date->format('d/m/Y, H:i'),
        };
    }
}
