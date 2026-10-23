<?php

namespace App\Models;

use App\Models\Concerns\HasNumero;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Reclamation extends Model
{
    use HasNumero;

    public const PREFIXE_NUMERO = 'REC';   // numéro affiché : REC-0001

    // Valeurs de la colonne `statut` (voir ReclamationsSeeder)
    public const STATUT_EN_COURS = 'en_cours';
    public const STATUT_ACCEPTE  = 'accepté';
    public const STATUT_REJETE   = 'rejeté';
    public const STATUTS         = [self::STATUT_EN_COURS, self::STATUT_ACCEPTE, self::STATUT_REJETE];

    // Valeurs de la colonne `type_reclamation`
    public const TYPE_PLAINTE = 'Plainte';
    public const TYPE_AVIS    = 'Avis';

    // Valeurs de la colonne `priorite`
    public const PRIORITES = ['urgente', 'normale', 'faible'];

    protected $table = 'reclamations';

    protected $fillable = [
        'type_reclamation',
        'description',
        'statut',
        'priorite',
        'etudiant_id',
        'gerant_id',
    ];

    protected $attributes = [
        'statut' => self::STATUT_EN_COURS,
    ];

    public function etudiant(): BelongsTo
    {
        return $this->belongsTo(Etudiant::class);
    }

    public function reponses(): HasMany
    {
        return $this->hasMany(Reponse::class);
    }

    public function derniereReponse(): HasOne
    {
        return $this->hasOne(Reponse::class)->latestOfMany();
    }

    // Claims still waiting for a decision
    public function scopeEnCours($query)
    {
        return $query->where('statut', self::STATUT_EN_COURS);
    }

    /**
     * Statut affiché par les pages admin et gérant : nouvelle / en_cours / resolue.
     * (En base : en_cours tant qu'aucune décision n'est prise, puis accepté ou rejeté.)
     */
    public function getStatutAfficheAttribute(): string
    {
        return match (true) {
            $this->statut !== self::STATUT_EN_COURS => 'resolue',
            $this->derniereReponse !== null         => 'en_cours',   // quelqu'un a déjà répondu
            default                                 => 'nouvelle',
        };
    }

    // Priorité affichée (normale si la valeur en base est inconnue)
    public function getPrioriteAfficheeAttribute(): string
    {
        return in_array($this->priorite, self::PRIORITES, true) ? $this->priorite : 'normale';
    }
}
