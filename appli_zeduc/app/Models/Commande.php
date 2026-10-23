<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{
    // Valeurs de la colonne `etat` (voir CommandesSeeder)
    public const ETAT_EN_ATTENTE = 'en_attente';
    public const ETAT_EN_COURS   = 'en_cours';
    public const ETAT_EFFECTUE   = 'effectué';
    public const ETAT_REJETE     = 'rejeté';

    protected $table = 'commandes';

    protected $fillable = [
        'montant_local',
        'points_accumules',
        'date_commande',
        'etat',
        'destination',
        'employe_id',
        'etudiant_id',
    ];

    protected $casts = [
        'montant_local'    => 'decimal:2',
        'points_accumules' => 'integer',
        'date_commande'    => 'datetime',
    ];

    protected $attributes = [
        'points_accumules' => 0,
        'etat'             => self::ETAT_EN_ATTENTE,
    ];
}
