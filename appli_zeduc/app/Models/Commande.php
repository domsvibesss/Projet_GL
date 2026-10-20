<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{
    protected $table = 'Commandes';

    // Use date_commande as the creation timestamp; no updated_at column
    const CREATED_AT = 'date_commande';
    const UPDATED_AT = null;

    protected $fillable = [
        'amount',
        'points_accumules',
        'etat',
    ];

    protected $casts = [
        'amount'           => 'decimal:2',
        'points_accumules' => 'integer',
        'date_commande'    => 'datetime',
    ];

    protected $attributes = [
        'points_accumules' => 0,
        'etat'             => 'en_attente',
    ];
}