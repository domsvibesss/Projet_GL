<?php

namespace App\Models;

use App\Models\Concerns\AuthenticatesWithMdp;
use App\Models\Concerns\HasDateEnregistrement;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Etudiant extends Authenticatable
{
    // Notifiable : nécessaire pour l'email de réinitialisation du mot de passe
    use AuthenticatesWithMdp, HasDateEnregistrement, Notifiable;

    public const STATUT_ACTIF = 'actif';

    protected $table = 'etudiants';

    protected $fillable = [
        'nom',
        'prenom',
        'tel',
        'email',
        'mdp',
        'points_fidelite',
        'code_parrain',
        'statut',
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
        'statut'          => self::STATUT_ACTIF,
    ];

    public function colonneDateEnregistrement(): string
    {
        return 'date_inscription';
    }

    public function getNomCompletAttribute(): string
    {
        return "{$this->prenom} {$this->nom}";
    }

    // Ex. "Alice Mbarga" -> "AM"
    public function getInitialesAttribute(): string
    {
        return mb_strtoupper(mb_substr($this->prenom, 0, 1).mb_substr($this->nom, 0, 1));
    }
}
