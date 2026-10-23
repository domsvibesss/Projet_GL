<?php

namespace App\Models;

use App\Models\Concerns\AuthenticatesWithMdp;
use App\Models\Concerns\HasDateEnregistrement;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Administrateur extends Authenticatable
{
    use AuthenticatesWithMdp, HasDateEnregistrement;

    protected $table = 'administrateurs';

    protected $fillable = [
        'poste',
        'nom',
        'prenom',
        'tel',
        'email',
        'mdp',
        'date_naissance',
    ];

    protected $hidden = [
        'mdp',
    ];

    protected $casts = [
        'mdp' => 'hashed',              // auto-hash (Laravel 10+)
        'date_naissance' => 'date',
        'date_enregistrement' => 'datetime',
    ];

    // Table administrateur_employe
    public function employes(): BelongsToMany
    {
        return $this->belongsToMany(Employe::class)->withTimestamps();
    }

    // Table administrateur_plat
    public function plats(): BelongsToMany
    {
        return $this->belongsToMany(Plat::class)->withTimestamps();
    }

    // Table administrateur_reclamation (réclamations consultées)
    public function reclamations(): BelongsToMany
    {
        return $this->belongsToMany(Reclamation::class)->withTimestamps();
    }

    // Table administrateur_reponse (réponses produites)
    public function reponses(): BelongsToMany
    {
        return $this->belongsToMany(Reponse::class)->withTimestamps();
    }
}
