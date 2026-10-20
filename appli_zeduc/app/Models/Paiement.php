<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    protected $table = 'Paiements';

    // Use date_paiement as the creation timestamp; no updated_at column
    const CREATED_AT = 'date_paiement';
    const UPDATED_AT = null;

    protected $fillable = [
        'montant_depense',
        'points_depense',
    ];

    protected $casts = [
        'montant_depense' => 'decimal:2',
        'points_depense'  => 'integer',
        'date_paiement'   => 'datetime',
    ];

    protected $attributes = [
        'points_depense' => 0,
    ];
}