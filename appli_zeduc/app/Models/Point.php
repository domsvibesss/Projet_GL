<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Point extends Model
{
    // Valeurs de la colonne `origine` (voir CommandesSeeder)
    public const ORIGINE_ACHAT      = 'achat';
    public const ORIGINE_JEUX       = 'jeux';
    public const ORIGINE_PARRAINAGE = 'parrainage';

    protected $table = 'points';

    protected $fillable = [
        'quantite',
        'origine',
        'date_reception',
        'date_expiration',
        'commande_id',
        'etudiant_id',
        'jeu_id',
    ];

    protected $casts = [
        'quantite'        => 'integer',
        'date_expiration' => 'datetime',
        'date_reception'  => 'datetime',
    ];

    // Points that have not expired yet
    public function scopeValides($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('date_expiration')
              ->orWhere('date_expiration', '>', now());
        });
    }

    public function estExpire(): bool
    {
        return $this->date_expiration !== null && $this->date_expiration->isPast();
    }
}
