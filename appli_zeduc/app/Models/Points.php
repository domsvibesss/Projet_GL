<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Point extends Model
{
    protected $table = 'Points';

    const CREATED_AT = 'date_reception';
    const UPDATED_AT = null;

    protected $fillable = [
        'quantite',
        'origine',
        'date_expiration',
    ];

    protected $casts = [
        'quantite'        => 'integer',
        'date_expiration' => 'datetime',
        'date_reception'  => 'datetime',
    ];

    // Points that have not expired yet
    public function scopeValides($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('date_expiration')
              ->orWhere('date_expiration', '>', now());
        });
    }

    public function estExpire(): bool
    {
        return $this->date_expiration !== null && $this->date_expiration->isPast();
    }
}