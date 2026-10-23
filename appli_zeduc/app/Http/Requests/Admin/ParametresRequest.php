<?php

namespace App\Http\Requests\Admin;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;

/** Formulaire de la page Paramètres (admin/parametres.blade.php). */
class ParametresRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;   // la connexion est gérée par une autre partie du projet
    }

    public function rules(): array
    {
        $regles = [
            'taux_point'              => ['required', 'integer', 'min:1'],
            'validite_mois'           => ['required', 'integer', 'min:1', 'max:120'],
            'politique_fidelite'      => ['nullable', 'string', 'max:2000'],
            'politique_parrainage'    => ['nullable', 'string', 'max:2000'],
            'politique_remboursement' => ['nullable', 'string', 'max:2000'],
        ];

        foreach (Setting::JOURS as $jour) {
            $regles["horaires.$jour.ouverture"] = ['nullable', 'date_format:H:i'];
            $regles["horaires.$jour.fermeture"] = ['nullable', 'date_format:H:i'];
            $regles["horaires.$jour.ouvert"]    = ['required', 'boolean'];
        }

        return $regles;
    }

    /** Valeurs prêtes pour Setting::enregistrer(). */
    public function donnees(): array
    {
        $data = $this->validated();

        foreach (Setting::JOURS as $jour) {
            $data['horaires'][$jour] = [
                'ouverture' => $data['horaires'][$jour]['ouverture'] ?? '',
                'fermeture' => $data['horaires'][$jour]['fermeture'] ?? '',
                'ouvert'    => (bool) $data['horaires'][$jour]['ouvert'],
            ];
        }

        $data['taux_point']    = (int) $data['taux_point'];
        $data['validite_mois'] = (int) $data['validite_mois'];

        return $data;
    }
}
