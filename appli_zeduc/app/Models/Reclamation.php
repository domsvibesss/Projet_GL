<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reclamation extends Model
{
    protected $table = 'Reclamations';

    protected $fillable = [
        'description',
        'ans',
        'status',
    ];

    protected $attributes = [
        'status' => 'en_attente',
    ];

    // Claims still waiting for an answer
    public function scopeEnAttente($query)
    {
        return $query->where('status', 'en_attente');
    }
}