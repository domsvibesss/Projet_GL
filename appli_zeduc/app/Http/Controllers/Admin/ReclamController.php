<?php
 
namespace App\Http\Controllers\Admin;
 
use App\Http\Controllers\Controller;
use App\Models\Reclamation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
 
class ReclamationController extends Controller
{
    // Seule la valeur 'en_attente' est certaine (défaut du modèle) : adapte les autres à ta base
    private const STATUS_EN_ATTENTE = 'en_attente';
    private const STATUSES = ['en_attente', 'repondue', 'approuvee', 'rejetee'];
 
    public function index(Request $request)
    {
        $request->validate(['status' => ['nullable', Rule::in(self::STATUSES)]]);
 
        $reclamations = Reclamation::query()
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->search, fn ($q, $s) => $q->where('description', 'like', "%{$s}%"))
            ->latest('id')
            ->paginate(15)->withQueryString();
 
        return view('admin.reclamations.index', [
            'reclamations' => $reclamations,
            'statuses'     => self::STATUSES,
            'enAttente'    => Reclamation::enAttente()->count(),
        ]);
    }
 
    public function show(Reclamation $reclamation)
    {
        return view('admin.reclamations.show', [
            'reclamation' => $reclamation,
            'statuses'    => self::STATUSES,
        ]);
    }
 
    // L'admin a le dernier mot : répondre, approuver ou rejeter
    public function update(Request $request, Reclamation $reclamation)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(self::STATUSES)],
            // `ans` = la réponse : obligatoire dès que la réclamation n'est plus "en_attente"
            'ans'    => ['required_unless:status,'.self::STATUS_EN_ATTENTE, 'nullable', 'string', 'max:2000'],
        ]);
 
        $reclamation->update($data);
 
        return redirect()->route('admin.reclamations.show', $reclamation)
            ->with('success', 'Réclamation mise à jour.');
    }
 
    public function destroy(Reclamation $reclamation)
    {
        $reclamation->delete();
 
        return redirect()->route('admin.reclamations.index')
            ->with('success', 'Réclamation supprimée.');
    }
}