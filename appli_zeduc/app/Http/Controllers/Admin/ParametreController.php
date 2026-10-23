<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ParametresRequest;
use App\Models\Setting;

/**
 * Page admin/parametres. La vue reçoit $horaires, $tauxPoint, $validiteMois et $politiques,
 * au même format que ses données de démo.
 */
class ParametreController extends Controller
{
    public function edit()
    {
        $p = Setting::tous();

        return view('admin.parametres', [
            'horaires'     => collect(Setting::JOURS)
                ->map(fn ($jour) => ['cle' => $jour, 'jour' => ucfirst($jour)] + $p['horaires'][$jour])
                ->all(),
            'tauxPoint'    => $p['taux_point'],
            'validiteMois' => $p['validite_mois'],
            'politiques'   => [
                'fidelite'      => $p['politique_fidelite'],
                'parrainage'    => $p['politique_parrainage'],
                'remboursement' => $p['politique_remboursement'],
            ],
        ]);
    }

    public function update(ParametresRequest $request)
    {
        Setting::enregistrer($request->donnees());

        return redirect()->route('admin.parametres.edit');
    }
}
