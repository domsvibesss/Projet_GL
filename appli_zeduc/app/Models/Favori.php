<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Favori extends Model
{
    protected $table = 'favoris';

    protected $fillable = [
        'date_enregistrement',
    ];

    protected $casts = [
        'date_enregistrement' => 'datetime',
    ];

    // Table etudiant_favori
    public function etudiants(): BelongsToMany
    {
        return $this->belongsToMany(Etudiant::class)->withTimestamps();
    }
}
