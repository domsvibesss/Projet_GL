<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    protected $table = 'promotions';

    protected $fillable = [
        'titre',
        'description',
        'image',
        'prix_initial',
        'prix_promo',
        'date_debut',
        'date_fin',
        'administrateur_id',
    ];

    protected $casts = [
        'prix_initial' => 'decimal:2',
        'prix_promo'   => 'decimal:2',
        'date_debut'   => 'datetime',
        'date_fin'     => 'datetime',
    ];

    // Promotions running right now
    public function scopeActives($query)
    {
        return $query->where('date_debut', '<=', now())
                     ->where('date_fin', '>=', now());
    }

    public function estActive(): bool
    {
        return $this->date_debut->isPast() && $this->date_fin->isFuture();
    }

    // Discount as a percentage, e.g. 20.0
    public function getReductionPourcentageAttribute(): float
    {
        if ((float) $this->prix_initial <= 0) {
            return 0;
        }

        return round((1 - $this->prix_promo / $this->prix_initial) * 100, 1);
    }

    // Chemin à passer à asset() :
    //  - "images/..." : affiche commune, rangée dans public/ (donnée par JeuxPromotionsSeeder)
    //  - sinon        : affiche envoyée (dans storage)
    //  - pas d'image  : une image de la maquette
    public function getCheminImageAttribute(): string
    {
        if (! $this->image) {
            return 'images/admin/promo-poulet.png';
        }

        return str_starts_with($this->image, 'images/') ? $this->image : 'storage/'.$this->image;
    }
}
