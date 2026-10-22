<?php
 
namespace App\Models;
 
use App\Models\Concerns\AuthenticatesWithMdp;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
 
class Etudiant extends Authenticatable
{
    // Notifiable : nécessaire pour l'email de réinitialisation du mot de passe
    use AuthenticatesWithMdp, Notifiable;
 
    protected $table = 'Etudiant';
 
    // Use date_inscription as the creation timestamp; no updated_at column
    const CREATED_AT = 'date_inscription';
    const UPDATED_AT = null;
 
    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'mdp',
        'points_fidelite',
        'code_parain',
        'status',
    ];
 
    protected $hidden = [
        'mdp',
    ];
 
    protected $casts = [
        'mdp' => 'hashed',          // auto-hash (Laravel 10+)
        'points_fidelite' => 'integer',
        'date_inscription' => 'datetime',
    ];
 
    protected $attributes = [
        'points_fidelite' => 0,
    ];
}

