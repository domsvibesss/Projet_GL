<?php

namespace App\Models;

use App\Models\Concerns\AuthenticatesWithMdp;
use App\Models\Concerns\HasDateEnregistrement;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Gerant extends Authenticatable
{
    use AuthenticatesWithMdp, HasDateEnregistrement, Notifiable;

    protected $table = 'gerants';

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
