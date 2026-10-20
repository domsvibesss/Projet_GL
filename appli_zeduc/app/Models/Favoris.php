<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favori extends Model
{
    protected $table = 'favoris';

    // Use date_enregistrement as the creation timestamp; no updated_at column
    const CREATED_AT = 'date_enregistrement';
    const UPDATED_AT = null;

    protected $fillable = [
        'etudiant_id',
        'plat_id',
    ];

    protected $casts = [
        'date_enregistrement' => 'datetime',
    ];

    public function etudiant()
    {
        return $this->belongsTo(Etudiant::class);
    }

    public function plat()
    {
        return $this->belongsTo(Plat::class);
    }
}