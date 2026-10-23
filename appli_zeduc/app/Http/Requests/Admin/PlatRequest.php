<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/** Formulaire « Ajout / Modification d'un menu » (admin/menus-ajout.blade.php). */
class PlatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;   // la connexion est gérée par une autre partie du projet
    }

    public function rules(): array
    {
        return [
            'nom'         => ['required', 'string', 'max:255'],
            'prix'        => ['required', 'numeric', 'min:0'],     // en F CFA
            'description' => ['required', 'string', 'max:1000'],
            'photo'       => ['nullable', 'image', 'max:2048'],
        ];
    }

    /** Données prêtes pour Plat::create() / update() (sans la photo, gérée à part). */
    public function donnees(): array
    {
        return $this->safe()->only(['nom', 'prix', 'description']);
    }
}
