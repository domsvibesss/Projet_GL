<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reclamation;
use App\Models\Reponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Page admin/reclamations.
 * La vue parle de « nouvelle / en_cours / resolue » ; la base (gérant, seeder) de
 * « en_cours / accepté / rejeté » : la traduction se fait ici, dans les deux sens.
 */
class ReclamationController extends Controller
{
    // Statut choisi dans la fenêtre « Traiter » => valeur enregistrée en base
    private const STATUT_EN_BASE = [
        'nouvelle' => Reclamation::STATUT_EN_COURS,
        'en_cours' => Reclamation::STATUT_EN_COURS,
        'resolue'  => Reclamation::STATUT_ACCEPTE,
    ];

    public function index()
    {
        $reclamations = Reclamation::with(['etudiant', 'derniereReponse'])->latest()->get();

        return view('admin.reclamations', [
            'reclamations' => $reclamations->map(fn (Reclamation $r) => $this->pourLaVue($r))->all(),
        ]);
    }

    // L'admin a le dernier mot : il change le statut et peut répondre à l'étudiant
    public function update(Request $request, Reclamation $reclamation)
    {
        $data = $request->validate([
            'statut'  => ['required', Rule::in(array_keys(self::STATUT_EN_BASE))],
            'reponse' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($data, $reclamation) {
            $reclamation->update(['statut' => self::STATUT_EN_BASE[$data['statut']]]);

            if (! empty($data['reponse'])) {
                $reclamation->reponses()->create([
                    'auteur'  => 'Administrateur',
                    'contenu' => $data['reponse'],
                    'statut'  => Reponse::STATUT_VALIDEE,   // pas de validation du gérant pour l'admin
                ]);
            }
        });

        return redirect()->route('admin.reclamations.index');
    }

    /** Une réclamation au format attendu par la vue (et par la fenêtre « Traiter » d'admin.js). */
    private function pourLaVue(Reclamation $r): array
    {
        $reponse = $r->derniereReponse;

        return [
            'id'         => $r->numero,   // REC-0001
            'client'     => $r->etudiant?->nom_complet ?? 'Étudiant supprimé',
            'objet'      => Str::limit($r->description, 40),
            'date'       => $r->created_at?->format('d/m/Y'),
            'type'       => Str::contains(Str::lower($r->type_reclamation), 'point') ? 'points' : 'client',
            'priorite'   => $r->priorite_affichee,
            'statut'     => $r->statut_affiche,
            'message'    => $r->description,
            'reponse'    => $reponse?->contenu,
            'validation' => match ($reponse?->statut) {
                null                    => null,
                Reponse::STATUT_VALIDEE => 'validee',
                Reponse::STATUT_REJETEE => 'rejetee',
                default                 => 'attente',
            },
            // URL du formulaire de la fenêtre « Traiter »
            'url'        => route('admin.reclamations.update', $r),
        ];
    }
}
