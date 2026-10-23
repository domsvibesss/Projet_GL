<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EmployeRequest;
use App\Models\Employe;
use App\Services\ImageStorage;

/**
 * Pages admin/employes et admin/employes-ajout.
 * Les vues reçoivent des tableaux au même format que leurs données de démo.
 */
class EmployeController extends Controller
{
    private const DOSSIER_PHOTOS = 'employes';

    public function __construct(private ImageStorage $images)
    {
    }

    public function index()
    {
        $employes = Employe::orderBy('nom')->orderBy('prenom')->get();

        return view('admin.employes', [
            'employes' => $employes->map(fn (Employe $e) => $this->pourLaVue($e))->all(),
        ]);
    }

    public function create()
    {
        return view('admin.employes-ajout', ['postes' => Employe::POSTES]);
    }

    public function store(EmployeRequest $request)
    {
        $data = $request->donnees();
        $data['photo'] = $this->images->remplacer(null, $request->file('photo'), self::DOSSIER_PHOTOS);

        Employe::create($data);

        // Comme en démo : on reste sur le formulaire, vidé, avec le message de confirmation
        return redirect()->route('admin.employes.create')
            ->with('success', 'Employé ajouté avec succès.');
    }

    public function edit(Employe $employe)
    {
        return view('admin.employes-ajout', [
            'employe' => $this->pourLaVue($employe),
            'postes'  => Employe::POSTES,
        ]);
    }

    public function update(EmployeRequest $request, Employe $employe)
    {
        $data = $request->donnees();
        $data['photo'] = $this->images->remplacer($employe->photo, $request->file('photo'), self::DOSSIER_PHOTOS);

        $employe->update($data);

        return redirect()->route('admin.employes.edit', $employe)
            ->with('success', 'Employé modifié avec succès.');
    }

    public function destroy(Employe $employe)
    {
        $this->images->supprimer($employe->photo);
        $employe->delete();

        return redirect()->route('admin.employes.index');
    }

    /** Un employé au format attendu par les vues. */
    private function pourLaVue(Employe $e): array
    {
        return [
            'id'      => $e->id,
            'nom'     => $e->nom,
            'prenom'  => $e->prenom,
            'poste'   => $e->poste,
            'contact' => $e->tel,
            'photo'   => $e->chemin_photo,
        ];
    }
}
