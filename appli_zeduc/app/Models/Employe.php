<?php

namespace App\Models;

use App\Models\Concerns\AuthenticatesWithMdp;
use App\Models\Concerns\HasDateEnregistrement;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Employe extends Authenticatable
{
    use AuthenticatesWithMdp, HasDateEnregistrement, Notifiable;

    // Valeurs de la colonne `statut` (voir UtilisateursSeeder)
    public const STATUT_ACTIF   = 'actif';
    public const STATUT_INACTIF = 'inactif';
    public const STATUT_PARTI   = 'parti';
    public const STATUTS        = [self::STATUT_ACTIF, self::STATUT_INACTIF, self::STATUT_PARTI];

    // Les rôles du formulaire admin (+ Caissier, utilisé par le gérant et le seeder)
    public const POSTES = ['Cuisinier', 'Assistant Cuisinier', 'Livreur', 'Manager', 'Caissier'];

    // Image affichée quand l'employé n'a pas de photo (images de la maquette, par rôle)
    private const PHOTOS_PAR_POSTE = [
        'cuisinier'           => 'images/admin/cuisinier.png',
        'assistant cuisinier' => 'images/admin/assistant-cuisinier.png',
        'livreur'             => 'images/admin/livreur.png',
    ];
    private const PHOTO_PAR_DEFAUT = 'images/admin/manager.png';

    protected $table = 'employes';

    protected $fillable = [
        'poste',
        'nom',
        'prenom',
        'tel',
        'email',
        'mdp',
        'date_naissance',
        'statut',
        'photo',
        'gerant_id',
    ];

    protected $hidden = [
        'mdp',
    ];

    protected $casts = [
        'mdp' => 'hashed',              // auto-hash (Laravel 10+)
        'date_naissance' => 'date',
        'date_enregistrement' => 'datetime',
    ];

    protected $attributes = [
        'statut' => self::STATUT_ACTIF,
    ];

    public function gerant(): BelongsTo
    {
        return $this->belongsTo(Gerant::class);
    }

    // Chemin à passer à asset() : la photo envoyée, sinon l'image du rôle
    public function getCheminPhotoAttribute(): string
    {
        if ($this->photo) {
            return 'storage/'.$this->photo;
        }

        return self::PHOTOS_PAR_POSTE[mb_strtolower((string) $this->poste)] ?? self::PHOTO_PAR_DEFAUT;
    }
}
