<?php
 
namespace App\Http\Controllers\Admin;
 
use App\Http\Controllers\Controller;
use App\Models\Administrateur;
use App\Models\Employe;
use App\Models\Etudiant;
use App\Models\Gerant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
 
/**
 * Gestion des comptes employés par l'administrateur.
 * (Les gérants ont leur propre table : ils ne sont pas gérés ici.)
 */
class EmployeeController extends Controller
{
    // Doit correspondre à AuthController::STATUS_ACTIVE
    private const STATUS_ACTIVE   = 'actif';
    private const STATUS_INACTIVE = 'inactif';
    private const STATUSES        = [self::STATUS_ACTIVE, self::STATUS_INACTIVE];
 
    private const PASSWORD_RULE = 'regex:/^(?=.*[A-Z])(?=.*\d).+$/';
    private const PASSWORD_MSG  = 'Le mot de passe doit contenir au moins une majuscule et un chiffre.';
 
    public function index(Request $request)
    {
        $request->validate(['status' => ['nullable', Rule::in(self::STATUSES)]]);
 
        $employees = Employe::query()
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->search, fn ($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('nom', 'like', "%{$s}%")
                  ->orWhere('prenom', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('tel', 'like', "%{$s}%")
                  ->orWhere('poste', 'like', "%{$s}%");
            }))
            ->latest()                       // trie sur date_enregistrement
            ->paginate(15)->withQueryString();
 
        return view('admin.employees.index', [
            'employees' => $employees,
            'statuses'  => self::STATUSES,
        ]);
    }
 
    public function create()
    {
        return view('admin.employees.create');
    }
 
    public function store(Request $request)
    {
        $data = $request->validate([
            'poste'          => ['required', 'string', 'max:100'],
            'nom'            => ['required', 'string', 'max:100'],
            'prenom'         => ['required', 'string', 'max:100'],
            'tel'            => ['required', 'string', 'max:20', Rule::unique(Employe::class, 'tel')],
            'email'          => ['required', 'email', 'max:255', $this->emailRule()],
            'date_naissance' => ['required', 'date', 'before:today'],
            // au moins une majuscule et un chiffre (cahier des charges)
            'password'       => ['required', 'string', 'min:8', self::PASSWORD_RULE],
        ], ['password.regex' => self::PASSWORD_MSG]);
 
        // 'mdp' est casté en 'hashed' dans le modèle : on passe le mot de passe en clair
        $data['mdp'] = $data['password'];
        unset($data['password']);
 
        $data['status'] = self::STATUS_ACTIVE;
 
        Employe::create($data);
 
        return redirect()->route('admin.employees.index')
            ->with('success', 'Compte employé créé.');
    }
 
    public function edit(Employe $employee)
    {
        return view('admin.employees.edit', [
            'employee' => $employee,
            'statuses' => self::STATUSES,
        ]);
    }
 
    public function update(Request $request, Employe $employee)
    {
        $data = $request->validate([
            'poste'          => ['required', 'string', 'max:100'],
            'nom'            => ['required', 'string', 'max:100'],
            'prenom'         => ['required', 'string', 'max:100'],
            'tel'            => ['required', 'string', 'max:20',
                                 Rule::unique(Employe::class, 'tel')->ignore($employee->id)],
            'email'          => ['required', 'email', 'max:255', $this->emailRule($employee)],
            'date_naissance' => ['required', 'date', 'before:today'],
            'password'       => ['nullable', 'string', 'min:8', self::PASSWORD_RULE],
            'status'         => ['required', Rule::in(self::STATUSES)],
        ], ['password.regex' => self::PASSWORD_MSG]);
 
        if (empty($data['password'])) {
            unset($data['password']);                 // on garde l'ancien mot de passe
        } else {
            $data['mdp'] = $data['password'];         // haché par le cast
            unset($data['password']);
        }
 
        $employee->update($data);
 
        return redirect()->route('admin.employees.index')
            ->with('success', 'Compte employé mis à jour.');
    }
 
    /** Activer / désactiver rapidement un compte (sans ouvrir le formulaire). */
    public function toggleStatus(Employe $employee)
    {
        $employee->update([
            'status' => $employee->status === self::STATUS_ACTIVE
                ? self::STATUS_INACTIVE
                : self::STATUS_ACTIVE,
        ]);
 
        return back()->with('success', $employee->status === self::STATUS_ACTIVE
            ? 'Compte activé.'
            : 'Compte désactivé.');
    }
 
    public function destroy(Employe $employee)
    {
        $employee->delete();
 
        return redirect()->route('admin.employees.index')
            ->with('success', 'Compte employé supprimé.');
    }
 
    /**
     * L'email doit être unique dans les 4 tables de comptes
     * (la connexion cherche l'email dans chacune d'elles).
     */
    private function emailRule(?Employe $current = null): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) use ($current) {
            $taken = Administrateur::where('email', $value)->exists()
                || Gerant::where('email', $value)->exists()
                || Etudiant::where('email', $value)->exists()
                || Employe::where('email', $value)
                    ->when($current, fn ($q) => $q->where('id', '!=', $current->id))
                    ->exists();
 
            if ($taken) {
                $fail('Cette adresse email est déjà utilisée.');
            }
        };
    }
}