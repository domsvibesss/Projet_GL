<?php

namespace App\Models;

use App\Models\Concerns\HasNumero;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Commande extends Model
{
    use HasNumero;

    public const PREFIXE_NUMERO = 'CMD';   // numéro affiché : CMD-0101

    // Valeurs de la colonne `etat` : les étapes d'une commande, dans l'ordre
    // (ce sont aussi les clés de la page Supervision du gérant)
    public const ETAT_EN_ATTENTE     = 'en_attente';       // reçue, pas encore commencée
    public const ETAT_EN_PREPARATION = 'en_preparation';   // en cuisine
    public const ETAT_PRETE          = 'prete';            // prête à servir ou à livrer
    public const ETAT_LIVREE         = 'livree';           // servie / livrée : la vente est faite
    public const ETATS = [self::ETAT_EN_ATTENTE, self::ETAT_EN_PREPARATION, self::ETAT_PRETE, self::ETAT_LIVREE];

    protected $table = 'commandes';

    protected $fillable = [
        'montant_local',
        'points_accumules',
        'date_commande',
        'etat',
        'destination',
        'employe_id',
        'etudiant_id',
    ];

    protected $casts = [
        'montant_local'    => 'decimal:2',
        'points_accumules' => 'integer',
        'date_commande'    => 'datetime',
    ];

    protected $attributes = [
        'points_accumules' => 0,
        'etat'             => self::ETAT_EN_ATTENTE,
    ];

    public function etudiant(): BelongsTo
    {
        return $this->belongsTo(Etudiant::class);
    }

    // Table commande_plat : les plats de la commande, avec leur quantité et leur prix
    public function plats(): BelongsToMany
    {
        return $this->belongsToMany(Plat::class)->withPivot('quantite', 'prix_unitaire')->withTimestamps();
    }

    // Ex. "2 × Poulet DG, 1 × Jus de gingembre" (colonne « Articles » de la supervision)
    public function getArticlesAttribute(): string
    {
        return $this->plats->map(fn (Plat $p) => $p->pivot->quantite.' × '.$p->nom)->join(', ');
    }
}
