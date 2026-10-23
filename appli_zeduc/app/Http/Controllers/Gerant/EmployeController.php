<?php

namespace App\Http\Controllers\Gerant;

use App\Http\Controllers\Controller;
use App\Models\Employe;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Page « Compte employés » du gérant : voir la liste (R) et créer un compte (C).
 * Modifier et supprimer restent réservés à l'administrateur.
 */
class EmployeController extends Controller
{
    public function index()
    {
        $employes = Employe::latest('id')->get();

        return view('employes', [
            // Numéro que recevra le prochain employé (affiché dans le champ « Identifiant »)
            'prochainId' => sprintf('%s-%04d', Employe::PREFIXE_NUMERO, (Employe::max('id') ?? 0) + 1),
            'postes'     => Employe::POSTES,
            'employes'   => $employes->map(fn (Employe $e) => [
                'id'     => $e->numero,   // EMP-0007
                'poste'  => $e->poste,
                'nom'    => $e->nom,
                'prenom' => $e->prenom,
                'tel'    => $e->tel,
                'email'  => $e->email,
            ])->all(),
        ]);
    }

    public function store(Request $request)
    {
        // Mêmes règles que les champs du formulaire (pattern, required...)
        $data = $request->validate([
            'nom'            => ['required', 'string', 'max:100'],
            'prenom'         => ['required', 'string', 'max:100'],
            'telephone'      => ['required', 'regex:/^(\+237\s?)?6(\s?[0-9]){8}$/', Rule::unique(Employe::class, 'tel')],
            'email'          => ['required', 'email', 'max:255', Rule::unique(Employe::class, 'email')],
            'poste'          => ['required', Rule::in(Employe::POSTES)],
            'date_naissance' => ['required', 'date', 'before:today'],
            // Cahier des charges : au moins une majuscule et au moins un chiffre
            'mot_de_passe'   => ['required', 'string', 'min:8', 'regex:/^(?=.*[A-Z])(?=.*\d).+$/'],
        ], [
            'telephone.regex'    => 'Numéro camerounais attendu : 9 chiffres commençant par 6.',
            'telephone.unique'   => 'Ce numéro est déjà utilisé par un autre employé.',
            'email.unique'       => 'Cet e-mail est déjà utilisé par un autre employé.',
            'mot_de_passe.regex' => 'Le mot de passe doit contenir au moins une majuscule et un chiffre.',
        ]);

        Employe::create([
            'nom'            => $data['nom'],
            'prenom'         => $data['prenom'],
            'tel'            => $data['telephone'],
            'email'          => $data['email'],
            'poste'          => $data['poste'],
            'date_naissance' => $data['date_naissance'],
            'mdp'            => $data['mot_de_passe'],   // haché automatiquement (cast du modèle)
        ]);

        // La page se recharge : le nouvel employé apparaît en haut de la liste
        return redirect()->route('gerant.employes.index');
    }
}
