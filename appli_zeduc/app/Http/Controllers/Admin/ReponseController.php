<?php
 
namespace App\Http\Controllers\Admin;
 
use App\Http\Controllers\Controller;
use App\Models\Reclamation;
use App\Models\Reponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
 
/**
 * Réponses aux réclamations.
 * Une réponse est écrite depuis la fiche d'une réclamation (store), puis
 * consultée / corrigée / supprimée depuis la liste des réponses.
 *
 * Suppose que la table Reponse a une colonne `reclamation_id`
 * (voir add_reclamation_id_to_reponse_table.php).
 */
class ReponseController extends Controller
{
    private const ACTEUR_ADMIN = 'administrateur';
 
    // Seule 'envoyee' est certaine (défaut du modèle) : adapte la suite à ta base
    private const STATUSES = ['envoyee', 'lue'];
 
    public function index(Request $request)
    {
        $request->validate([
            'status' => ['nullable', Rule::in(self::STATUSES)],
            'acteur' => ['nullable', 'string', 'max:50'],
        ]);
 
        $reponses = Reponse::with('reclamation:id,description,status')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->acteur, fn ($q, $a) => $q->where('acteur', $a))
            ->when($request->reclamation_id, fn ($q, $id) => $q->where('reclamation_id', $id))
            ->when($request->search, fn ($q, $s) => $q->where('contenu', 'like', "%{$s}%"))
            ->latest('id')
            ->paginate(15)->withQueryString();
 
        return view('admin.reponses.index', [
            'reponses' => $reponses,
            'statuses' => self::STATUSES,
            'acteurs'  => Reponse::select('acteur')->distinct()->orderBy('acteur')->pluck('acteur'),
        ]);
    }
 
    /** Répondre à une réclamation (formulaire sur la fiche de la réclamation). */
    public function store(Request $request, Reclamation $reclamation)
    {
        $data = $request->validate([
            'contenu' => ['required', 'string', 'max:2000'],
        ]);
 
        DB::transaction(function () use ($data, $reclamation) {
            Reponse::create([
                'reclamation_id' => $reclamation->id,
                'acteur'         => self::ACTEUR_ADMIN,
                'contenu'        => $data['contenu'],
                // status : 'envoyee' par défaut (modèle)
            ]);
 
            // `ans` garde la dernière réponse ; la réclamation n'est plus "en_attente"
            $reclamation->update([
                'ans'    => $data['contenu'],
                'status' => $reclamation->status === 'en_attente' ? 'repondue' : $reclamation->status,
            ]);
        });
 
        return redirect()->route('admin.reclamations.show', $reclamation)
            ->with('success', 'Réponse envoyée.');
    }
 
    public function show(Reponse $reponse)
    {
        $reponse->load('reclamation');
 
        return view('admin.reponses.show', compact('reponse'));
    }
 
    public function edit(Reponse $reponse)
    {
        return view('admin.reponses.edit', [
            'reponse'  => $reponse,
            'statuses' => self::STATUSES,
        ]);
    }
 
    public function update(Request $request, Reponse $reponse)
    {
        $data = $request->validate([
            'contenu' => ['required', 'string', 'max:2000'],
            'status'  => ['required', Rule::in(self::STATUSES)],
        ]);
 
        $reponse->update($data);
 
        return redirect()->route('admin.reponses.show', $reponse)
            ->with('success', 'Réponse mise à jour.');
    }
 
    public function destroy(Reponse $reponse)
    {
        $reclamationId = $reponse->reclamation_id;
 
        $reponse->delete();
 
        return $reclamationId
            ? redirect()->route('admin.reclamations.show', $reclamationId)->with('success', 'Réponse supprimée.')
            : redirect()->route('admin.reponses.index')->with('success', 'Réponse supprimée.');
    }
}
 