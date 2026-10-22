<?php
 
namespace App\Http\Controllers\Auth;
 
use App\Http\Controllers\Controller;
use App\Models\Administrateur;
use App\Models\Employe;
use App\Models\Etudiant;
use App\Models\Gerant;
use App\Models\Parrainage;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
 
/**
 * Authentification pour 4 types de comptes stockés dans 4 tables :
 * Administrateur, Gerant, Employe, Etudiant (un guard par modèle).
 *
 *  - connexion : un seul formulaire, on cherche l'email dans chaque table
 *  - inscription : étudiants uniquement (les autres comptes sont créés par l'admin / le gérant)
 *  - mot de passe oublié : étudiants uniquement
 */
class AuthController extends Controller
{
    /** guard => modèle (ordre = ordre de recherche à la connexion) */
    private const GUARDS = [
        'admin'    => Administrateur::class,
        'gerant'   => Gerant::class,
        'employe'  => Employe::class,
        'etudiant' => Etudiant::class,
    ];
 
    private const STATUS_ACTIVE = 'actif';   // valeur de la colonne `status` d'un compte actif
 
    private const PASSWORD_RULE = 'regex:/^(?=.*[A-Z])(?=.*\d).+$/';
    private const PASSWORD_MSG  = 'Le mot de passe doit contenir au moins une majuscule et un chiffre.';
 
    /* ------------------------------------------------------------------ */
    /*  Inscription (étudiants)                                            */
    /* ------------------------------------------------------------------ */
 
    public function showRegister(Request $request)
    {
        // Lien de parrainage : /register?ref=ABC12345
        return view('auth.register', ['ref' => $request->query('ref')]);
    }
 
    public function register(Request $request)
    {
        $data = $request->validate([
            'nom'              => ['required', 'string', 'max:100'],
            'prenom'           => ['required', 'string', 'max:100'],
            'email'            => ['required', 'email', 'max:255', function ($attr, $value, $fail) {
                if ($this->emailTaken($value)) {
                    $fail('Cette adresse email est déjà utilisée.');
                }
            }],
            'password'         => ['required', 'string', 'min:8', 'confirmed', self::PASSWORD_RULE],
            'code_parrainage'  => ['nullable', 'string', Rule::exists(Etudiant::class, 'code_parain')],
        ], [
            'password.regex'           => self::PASSWORD_MSG,
            'code_parrainage.exists'   => "Ce code de parrainage n'existe pas.",
        ]);
 
        $etudiant = DB::transaction(function () use ($data) {
            $parrain = ! empty($data['code_parrainage'])
                ? Etudiant::where('code_parain', $data['code_parrainage'])->first()
                : null;
 
            // 'mdp' est casté en 'hashed' dans le modèle : on passe le mot de passe en clair
            $etudiant = Etudiant::create([
                'nom'         => $data['nom'],
                'prenom'      => $data['prenom'],
                'email'       => $data['email'],
                'mdp'         => $data['password'],
                'code_parain' => $this->uniqueReferralCode(),
                'status'      => self::STATUS_ACTIVE,
            ]);
 
            // Le parrain sera récompensé à la 1re commande réussie du filleul
            if ($parrain) {
                Parrainage::create([
                    'parrain_id' => $parrain->id,
                    'filleul_id' => $etudiant->id,
                ]);
            }
 
            return $etudiant;
        });
 
        $this->loginAs('etudiant', $etudiant, $request);
 
        return redirect()->route('home')->with('success', 'Bienvenue chez Mon Miam Miam !');
    }
 
    /* ------------------------------------------------------------------ */
    /*  Connexion / déconnexion                                            */
    /* ------------------------------------------------------------------ */
 
    public function showLogin()
    {
        return view('auth.login');
    }
 
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);
 
        $throttleKey = Str::lower($credentials['email']).'|'.$request->ip();
 
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
 
            throw ValidationException::withMessages([
                'email' => "Trop de tentatives. Réessayez dans {$seconds} secondes.",
            ]);
        }
 
        foreach (self::GUARDS as $guard => $model) {
            $user = $model::where('email', $credentials['email'])->first();
 
            if (! $user || ! Hash::check($credentials['password'], $user->mdp)) {
                continue;
            }
 
            // Compte désactivé (Administrateur et Gerant n'ont pas de colonne status)
            if (($user->status ?? self::STATUS_ACTIVE) !== self::STATUS_ACTIVE) {
                throw ValidationException::withMessages([
                    'email' => 'Ce compte est désactivé. Contactez le restaurant.',
                ]);
            }
 
            RateLimiter::clear($throttleKey);
            $this->loginAs($guard, $user, $request);
 
            return redirect()->intended($this->homeFor($guard));
        }
 
        RateLimiter::hit($throttleKey, 60);
 
        throw ValidationException::withMessages([
            'email' => 'Email ou mot de passe incorrect.',
        ]);
    }
 
    public function logout(Request $request)
    {
        foreach (array_keys(self::GUARDS) as $guard) {
            Auth::guard($guard)->logout();
        }
 
        $request->session()->invalidate();
        $request->session()->regenerateToken();
 
        return redirect()->route('home');
    }
 
    /* ------------------------------------------------------------------ */
    /*  Mot de passe oublié (étudiants)                                    */
    /* ------------------------------------------------------------------ */
 
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }
 
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);
 
        Password::broker('etudiants')->sendResetLink($request->only('email'));
 
        // Même message dans tous les cas : on ne révèle pas si l'email existe
        return back()->with('success', 'Si ce compte existe, un lien de récupération vient de lui être envoyé.');
    }
 
    public function showResetPassword(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }
 
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed', self::PASSWORD_RULE],
        ], ['password.regex' => self::PASSWORD_MSG]);
 
        $status = Password::broker('etudiants')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (Etudiant $etudiant, string $password) {
                $etudiant->forceFill(['mdp' => $password])->save();   // haché par le cast
 
                event(new PasswordReset($etudiant));
            }
        );
 
        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', 'Mot de passe modifié. Connectez-vous.')
            : back()->withErrors(['email' => 'Lien invalide ou expiré.']);
    }
 
    /* ------------------------------------------------------------------ */
    /*  Utilitaires                                                        */
    /* ------------------------------------------------------------------ */
 
    /** Ouvre une session pour ce guard et ferme toutes les autres. */
    private function loginAs(string $guard, $user, Request $request): void
    {
        foreach (array_keys(self::GUARDS) as $other) {
            if (Auth::guard($other)->check()) {
                Auth::guard($other)->logout();
            }
        }
 
        Auth::guard($guard)->login($user);
        $request->session()->regenerate();
    }
 
    /** Page d'arrivée selon le type de compte (adapte les noms de routes). */
    private function homeFor(string $guard): string
    {
        return match ($guard) {
            'admin'   => route('admin.stats'),
            'gerant'  => route('gerant.dashboard'),
            'employe' => route('employe.commandes.index'),
            default   => route('home'),
        };
    }
 
    /** L'email doit être unique dans les 4 tables (sinon la connexion serait ambiguë). */
    private function emailTaken(string $email): bool
    {
        foreach (self::GUARDS as $model) {
            if ($model::where('email', $email)->exists()) {
                return true;
            }
        }
 
        return false;
    }
 
    private function uniqueReferralCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (Etudiant::where('code_parain', $code)->exists());
 
        return $code;
    }
}