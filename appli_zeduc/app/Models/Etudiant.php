<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Etudiant extends Model
{
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
