<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plat extends Model
{
    protected $table = 'Plat';

    protected $fillable = [
        'name',
        'description',
        'prix',
        'quantite',
    ];

    protected $casts = [
        'prix'     => 'decimal:2',
        'quantite' => 'integer',
    ];

    protected $attributes = [
        'quantite' => 0,
    ];

    // Dishes currently in stock
    public function scopeDisponibles($query)
    {
        return $query->where('quantite', '>', 0);
    }

    public function estDisponible(): bool
    {
        return $this->quantite > 0;
    }
}