<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reponse extends Model
{
    protected $table = 'Reponse';

    protected $fillable = [
        'acteur',
        'contenu',
        'status',
    ];

    protected $attributes = [
        'status' => 'envoyee',
    ];
}