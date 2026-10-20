<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parrainage extends Model
{
    protected $table = 'parrainages';

    // Use date_parrainage as the creation timestamp; no updated_at column
    const CREATED_AT = 'date_parrainage';
    const UPDATED_AT = null;

    protected $fillable = [
        'parrain_id',
        'filleul_id',
        'etat',
    ];

    protected $casts = [
        'date_parrainage' => 'datetime',
    ];

    protected $attributes = [
        'etat' => 'en_attente',
    ];

    // The student who refers
    public function parrain()
    {
        return $this->belongsTo(Etudiant::class, 'parrain_id');
    }

    // The student who was referred
    public function filleul()
    {
        return $this->belongsTo(Etudiant::class, 'filleul_id');
    }
}
