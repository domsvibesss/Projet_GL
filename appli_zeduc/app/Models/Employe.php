<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\Concerns\AuthenticatesWithMdp;
use Illuminate\Notifications\Notifiable;


class Employe extends Authenticatable
{
    use AuthenticatesWithMdp, Notifiable;
    protected $table = 'Employe';

    // Use date_enregistrement as the creation timestamp; no updated_at column
    const CREATED_AT = 'date_enregistrement';
    const UPDATED_AT = null;

    protected $fillable = [
        'poste',
        'nom',
        'prenom',
        'tel',
        'email',
        'mdp',
        'date_naissance',
        'status',
    ];

    protected $hidden = [
        'mdp',
    ];

    protected $casts = [
        'mdp' => 'hashed',              // auto-hash (Laravel 10+)
        'date_naissance' => 'date',
        'date_enregistrement' => 'datetime',
    ];
}
