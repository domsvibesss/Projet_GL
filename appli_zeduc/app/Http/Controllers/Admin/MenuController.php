<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PlatRequest;
use App\Models\Plat;
use App\Services\ImageStorage;

/**
 * Pages admin/menus et admin/menus-ajout (table plats).
 * Les vues reçoivent des tableaux au même format que leurs données de démo.
 */
class MenuController extends Controller
{
    private const DOSSIER_IMAGES = 'plats';

    public function __construct(private ImageStorage $images)
    {
    }

    public function index()
    {
        return view('admin.menus', [
            'menus' => Plat::orderBy('nom')->get()->map(fn (Plat $p) => $this->pourLaVue($p))->all(),
        ]);
    }

    public function create()
    {
        return view('admin.menus-ajout');
    }

    public function store(PlatRequest $request)
    {
        $data = $request->donnees();
        $data['image'] = $this->images->remplacer(null, $request->file('photo'), self::DOSSIER_IMAGES);

        Plat::create($data);

        // Comme en démo : on reste sur le formulaire, vidé, avec le message de confirmation
        return redirect()->route('admin.menus.create')
            ->with('success', 'Plat ajouté avec succès.');
    }

    public function edit(Plat $plat)
    {
        return view('admin.menus-ajout', ['menu' => $this->pourLaVue($plat)]);
    }

    public function update(PlatRequest $request, Plat $plat)
    {
        $data = $request->donnees();
        $data['image'] = $this->images->remplacer($plat->image, $request->file('photo'), self::DOSSIER_IMAGES);

        $plat->update($data);

        return redirect()->route('admin.menus.edit', $plat)
            ->with('success', 'Menu modifié avec succès.');
    }

    public function destroy(Plat $plat)
    {
        $this->images->supprimer($plat->image);
        $plat->delete();

        return redirect()->route('admin.menus.index');
    }

    /** Un plat au format attendu par les vues. */
    private function pourLaVue(Plat $p): array
    {
        return [
            'id'          => $p->id,
            'nom'         => $p->nom,
            'prix'        => (int) $p->prix,
            'description' => $p->description,
            'photo'       => $p->chemin_image,
        ];
    }
}
