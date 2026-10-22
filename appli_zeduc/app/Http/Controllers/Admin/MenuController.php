<?php
 
namespace App\Http\Controllers\Admin;
 
use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
 
/**
 * CRUD du menu (plats, boissons, ...).
 * Menu utilise SoftDeletes : "supprimer" masque l'article mais garde
 * l'historique des commandes ; on peut le restaurer ou le supprimer pour de bon.
 */
class MenuController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['categorie' => ['nullable', Rule::in(Menu::CATEGORIES)]]);
 
        $items = Menu::query()
            // ?corbeille=1 : afficher uniquement les articles supprimés
            ->when($request->boolean('corbeille'), fn ($q) => $q->onlyTrashed())
            ->dansCategorie($request->categorie)                   // scope du modèle
            ->when($request->recherche, fn ($q, $s) => $q->where('nom', 'like', "%{$s}%"))
            ->orderBy('categorie')->orderBy('nom')
            ->paginate(15)->withQueryString();
 
        return view('admin.menu.index', [
            'items'      => $items,
            'categories' => Menu::CATEGORIES,
            'corbeille'  => $request->boolean('corbeille'),
        ]);
    }
 
    public function create()
    {
        return view('admin.menu.create', ['categories' => Menu::CATEGORIES]);
    }
 
    public function store(Request $request)
    {
        $data = $this->validated($request);
 
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('menu', 'public');
        }
 
        Menu::create($data);
 
        return redirect()->route('admin.menu.index')
            ->with('success', 'Article ajouté au menu.');
    }
 
    public function edit(Menu $menu)
    {
        return view('admin.menu.edit', [
            'item'       => $menu,
            'categories' => Menu::CATEGORIES,
        ]);
    }
 
    public function update(Request $request, Menu $menu)
    {
        $data = $this->validated($request);
 
        if ($request->hasFile('image')) {
            if ($menu->image) {
                Storage::disk('public')->delete($menu->image);
            }
            $data['image'] = $request->file('image')->store('menu', 'public');
        }
 
        $menu->update($data);
 
        return redirect()->route('admin.menu.index')
            ->with('success', 'Article mis à jour.');
    }
 
    /**
     * Suppression "douce" : l'article disparaît du menu mais reste en base.
     * On garde l'image pour pouvoir restaurer l'article.
     */
    public function destroy(Menu $menu)
    {
        $menu->delete();
 
        return redirect()->route('admin.menu.index')
            ->with('success', 'Article supprimé (restaurable depuis la corbeille).');
    }
 
    public function restore(Menu $menu)
    {
        $menu->restore();
 
        return redirect()->route('admin.menu.index')
            ->with('success', 'Article restauré.');
    }
 
    /** Suppression définitive (depuis la corbeille) : efface aussi l'image. */
    public function forceDelete(Menu $menu)
    {
        if ($menu->image) {
            Storage::disk('public')->delete($menu->image);
        }
 
        $menu->forceDelete();
 
        return redirect()->route('admin.menu.index', ['corbeille' => 1])
            ->with('success', 'Article supprimé définitivement.');
    }
 
    /**
     * Raccourci : plat épuisé / de nouveau disponible (sans ouvrir le formulaire).
     */
    public function toggleAvailability(Menu $menu)
    {
        $menu->update(['disponible' => ! $menu->disponible]);
 
        return back()->with('success', $menu->disponible
            ? 'Article de nouveau disponible.'
            : 'Article marqué comme épuisé.');
    }
 
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'nom'          => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string', 'max:1000'],
            'prix'         => ['required', 'integer', 'min:0'],            // en F CFA
            'categorie'    => ['required', Rule::in(Menu::CATEGORIES)],
            'image'        => ['nullable', 'image', 'max:2048'],
            'disponible'   => ['sometimes', 'boolean'],
            'plat_du_jour' => ['sometimes', 'boolean'],
        ]);
 
        // Une case décochée n'est pas envoyée par le navigateur
        $data['disponible']   = $request->boolean('disponible');
        $data['plat_du_jour'] = $request->boolean('plat_du_jour');
 
        // 'image' est un fichier : il est enregistré séparément dans store()/update()
        unset($data['image']);
 
        return $data;
    }
}
 