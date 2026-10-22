<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Promotions, événements et jeux (présentés sous forme d'affiches).
 */
class PromotionController extends Controller
{
    public function index(Request $request)
    {
        $promotions = Promotion::query()
            ->latest('starts_at')
            ->paginate(12)
            ->withQueryString();

        return view('admin.promotions.index', [
            'promotions' => $promotions,
        ]);
    }

    public function create()
    {
        return view('admin.promotions.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, posterRequired: true);

        $data['poster'] = $request->file('poster')->store('promotions', 'public');

        Promotion::create($data);

        return redirect()->route('admin.promotions.index')
            ->with('success', 'Affiche publiée.');
    }

    public function edit(Promotion $promotion)
    {
        return view('admin.promotions.edit', [
            'promotion' => $promotion,
        ]);
    }

    public function update(Request $request, Promotion $promotion)
    {
        $data = $this->validated($request, posterRequired: false);

        if ($request->hasFile('poster')) {
            Storage::disk('public')->delete($promotion->poster);
            $data['poster'] = $request->file('poster')->store('promotions', 'public');
        }

        $promotion->update($data);

        return redirect()->route('admin.promotions.index')
            ->with('success', 'Affiche mise à jour.');
    }

    public function destroy(Promotion $promotion)
    {
        Storage::disk('public')->delete($promotion->poster);
        $promotion->delete();

        return redirect()->route('admin.promotions.index')
            ->with('success', 'Affiche supprimée.');
    }

    public function toggle(Promotion $promotion)
    {
        $promotion->update(['is_active' => ! $promotion->is_active]);

        return back()->with('success', $promotion->is_active ? 'Affiche activée.' : 'Affiche désactivée.');
    }

    private function validated(Request $request, bool $posterRequired): array
    {
        $data = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'poster'      => [$posterRequired ? 'required' : 'nullable', 'image', 'max:4096'],
            'starts_at'   => ['required', 'date'],
            'ends_at'     => ['required', 'date', 'after_or_equal:starts_at'],
            'is_active'   => ['sometimes', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        unset($data['poster']); // géré séparément (fichier)

        return $data;
    }
}