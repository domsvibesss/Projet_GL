<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Plat extends Model
{
    protected $table = 'plats';

    protected $fillable = [
        'nom',
        'description',
        'prix',
        'quantite',
        'image',
        'administrateur_id',
    ];

    protected $casts = [
        'prix'     => 'decimal:2',
        'quantite' => 'integer',
    ];

    protected $attributes = [
        'quantite' => 0,
    ];

    // Administrateur qui a ajouté le plat
    public function administrateur(): BelongsTo
    {
        return $this->belongsTo(Administrateur::class);
    }

    // Dishes currently in stock
    public function scopeDisponibles($query)
    {
        return $query->where('quantite', '>', 0);
    }

    public function estDisponible(): bool
    {
        return $this->quantite > 0;
    }

    // Chemin à passer à asset() :
    //  - "images/plats/..." : photo commune, rangée dans public/images/plats (donnée par MenuSeeder)
    //  - sinon              : photo envoyée par la page Modifier (dans storage)
    //  - pas de photo       : le logo
    public function getCheminImageAttribute(): string
    {
        if (! $this->image) {
            return 'images/logo.png';
        }

        return str_starts_with($this->image, 'images/') ? $this->image : 'storage/'.$this->image;
    }

    // Ex. 2500 -> "2 500 F"
    public function getPrixFormateAttribute(): string
    {
        return number_format((float) $this->prix, 0, ',', ' ').' F';
    }
}
