<?php

namespace App\Models;

use App\Models\Concerns\AuthenticatesWithMdp;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Gerant extends Authenticatable
{
    use AuthenticatesWithMdp, Notifiable;
    protected $table = 'gerants';

    // Use date_enregistrement as the creation timestamp; no updated_at column
    const CREATED_AT = 'date_enregistrement';
    const UPDATED_AT = null;

    protected $fillable = [
        'nom',
        'prenom',
        'tel',
        'email',
        'mdp',
        'date_naissance',
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