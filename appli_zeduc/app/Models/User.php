<?php

namespace App\Models;

// Authenticatable (et non Model) : c'est ce qui permet de se connecter avec ce modèle
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;   // nécessaire pour auth:sanctum dans routes/api.php

/**
 * Table commune à tous les profils : étudiant, employé, gérant, administrateur.
 * Aligné sur la migration create_users_table.
 */
class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',            // = nom
        'prenom',
        'email',
        'telephone',
        'password',
        'date_naissance',
        'role',            // etudiant | employe | gerant | administrateur
    ];

    // Ne jamais renvoyer le mot de passe dans un JSON
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password'         => 'hashed',   // chiffré automatiquement à l'enregistrement
            'date_naissance'   => 'date',
            'date_inscription' => 'datetime',
        ];
    }

    /**
     * Profil employé (poste, statut) quand role = 'employe'.
     * Utilisé par le layout employé pour afficher « Assistant cuisinier » sous le nom.
     */
    public function employe(): HasOne
    {
        return $this->hasOne(Employe::class, 'user_id');
    }
}
