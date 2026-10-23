<?php

namespace App\Http\Requests\Admin;

use App\Models\Employe;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Formulaire « Ajouter / Modifier un employé » (admin/employes-ajout.blade.php). */
class EmployeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;   // la connexion est gérée par une autre partie du projet
    }

    public function rules(): array
    {
        return [
            'nom'     => ['required', 'string', 'max:100'],
            'prenom'  => ['required', 'string', 'max:100'],
            'poste'   => ['required', Rule::in(Employe::POSTES)],
            // même format que le pattern du champ : +237 6 90 11 22 33
            'contact' => ['required', 'string', 'max:20', 'regex:/^(\+237\s?)?6(\s?[0-9]){8}$/',
                          Rule::unique(Employe::class, 'tel')->ignore($this->route('employe'))],
            'photo'   => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'contact.regex'  => 'Numéro camerounais attendu, ex : +237 6 90 11 22 33.',
            'contact.unique' => 'Ce numéro est déjà utilisé par un autre employé.',
        ];
    }

    /** Données prêtes pour Employe::create() / update() (le champ « contact » est la colonne tel). */
    public function donnees(): array
    {
        return [
            'nom'    => $this->input('nom'),
            'prenom' => $this->input('prenom'),
            'poste'  => $this->input('poste'),
            'tel'    => $this->input('contact'),
        ];
    }
}
