<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Reponse extends Model
{
    // Valeurs de la colonne `statut` : l'employé propose, le gérant valide ou rejette
    public const STATUT_VALIDEE = 'Validée';
    public const STATUT_REJETEE = 'Rejetée';

    protected $table = 'reponses';

    protected $fillable = [
        'auteur',
        'contenu',
        'statut',
        'reclamation_id',
        'gerant_id',
    ];

    public function reclamation(): BelongsTo
    {
        return $this->belongsTo(Reclamation::class);
    }

    // Table administrateur_reponse
    public function administrateurs(): BelongsToMany
    {
        return $this->belongsToMany(Administrateur::class)->withTimestamps();
    }
}
