<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    protected $table = 'paiements';

    protected $fillable = [
        'montant_depense',
        'points_depense',
        'date_paiement',
        'employe_id',
        'commande_id',
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
