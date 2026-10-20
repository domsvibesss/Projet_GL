<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jeu extends Model
{
    protected $table = 'jeux';

    protected $fillable = [
        'name',
        'description',
        'image',
        'date_debut',
        'date_fin',
    ];

    protected $casts = [
        'date_debut' => 'datetime',
        'date_fin'   => 'datetime',
    ];

    // Games running right now
    public function scopeActifs($query)
    {
        return $query->where('date_debut', '<=', now())
                     ->where(function ($q) {
                         $q->whereNull('date_fin')
                           ->orWhere('date_fin', '>=', now());
                     });
    }

    public function estActif(): bool
    {
        return $this->date_debut->isPast()
            && ($this->date_fin === null || $this->date_fin->isFuture());
    }
}