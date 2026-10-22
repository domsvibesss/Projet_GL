<?php
 
namespace App\Http\Controllers\Admin;
 
use App\Http\Controllers\Controller;
use App\Models\Jeu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
 
/**
 * Gestion des jeux (concours, tombolas...) affichés sous forme d'affiches.
 * Un jeu est "actif" quand now() est entre date_debut et date_fin (date_fin peut être vide).
 */
class JeuController extends Controller
{
    private const STATUTS = ['actif', 'a_venir', 'termine'];
 
    public function index(Request $request)
    {
        $request->validate(['statut' => ['nullable', Rule::in(self::STATUTS)]]);
 
        $jeux = Jeu::query()
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->when($request->statut, function ($q, $statut) {
                match ($statut) {
                    'actif'   => $q->actifs(),                                   // scope du modèle
                    'a_venir' => $q->where('date_debut', '>', now()),
                    'termine' => $q->whereNotNull('date_fin')->where('date_fin', '<', now()),
                };
            })
            ->latest('date_debut')
            ->paginate(12)->withQueryString();
 
        return view('admin.jeux.index', [
            'jeux'    => $jeux,
            'statuts' => self::STATUTS,
        ]);
    }
 
    public function create()
    {
        return view('admin.jeux.create');
    }
 
    public function store(Request $request)
    {
        $data = $this->validated($request, imageObligatoire: true);
 
        $data['image'] = $request->file('image')->store('jeux', 'public');
 
        Jeu::create($data);
 
        return redirect()->route('admin.jeux.index')
            ->with('success', 'Jeu créé.');
    }
 
    public function show(Jeu $jeu)
    {
        return view('admin.jeux.show', compact('jeu'));
    }
 
    public function edit(Jeu $jeu)
    {
        return view('admin.jeux.edit', compact('jeu'));
    }
 
    public function update(Request $request, Jeu $jeu)
    {
        $data = $this->validated($request, imageObligatoire: false);
 
        if ($request->hasFile('image')) {
            if ($jeu->image) {
                Storage::disk('public')->delete($jeu->image);
            }
            $data['image'] = $request->file('image')->store('jeux', 'public');
        }
 
        $jeu->update($data);
 
        return redirect()->route('admin.jeux.index')
            ->with('success', 'Jeu mis à jour.');
    }
 
    /** Arrêter un jeu tout de suite : date_fin = maintenant. */
    public function terminer(Jeu $jeu)
    {
        if (! $jeu->estActif()) {
            return back()->with('error', "Ce jeu n'est pas en cours.");
        }
 
        $jeu->update(['date_fin' => now()]);
 
        return back()->with('success', 'Jeu terminé.');
    }
 
    public function destroy(Jeu $jeu)
    {
        if ($jeu->image) {
            Storage::disk('public')->delete($jeu->image);
        }
 
        $jeu->delete();
 
        return redirect()->route('admin.jeux.index')
            ->with('success', 'Jeu supprimé.');
    }
 
    private function validated(Request $request, bool $imageObligatoire): array
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'image'       => [$imageObligatoire ? 'required' : 'nullable', 'image', 'max:4096'],
            'date_debut'  => ['required', 'date'],
            'date_fin'    => ['nullable', 'date', 'after_or_equal:date_debut'],   // vide = sans fin
        ]);
 
        // 'image' est un fichier : il est enregistré séparément dans store()/update()
        unset($data['image']);
 
        return $data;
    }
}