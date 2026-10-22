<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
 
class Menu extends Model
{
    use HasFactory, SoftDeletes;   // SoftDeletes : on garde l'historique des commandes
 
    public const CATEGORIES = ['plat', 'boisson', 'dessert', 'accompagnement'];
 
    protected $fillable = [
        'nom', 'description', 'prix', 'categorie',
        'image', 'disponible', 'plat_du_jour',
    ];
 
    protected $casts = [
        'prix'         => 'integer',
        'disponible'   => 'boolean',
        'plat_du_jour' => 'boolean',
    ];
 
    protected $appends = ['url_image', 'prix_formate'];
 
    /* ----------------------------- Relations ----------------------------- */
 
    // Suppose une colonne `menu_item_id` dans la table Commandes
    public function commandes(): HasMany
    {
        return $this->hasMany(Commande::class);
    }
 
    /* ------------------------------ Scopes ------------------------------- */
 
    // Visibles par les étudiants (non épuisés)
    public function scopeDisponibles(Builder $q): Builder
    {
        return $q->where('disponible', true);
    }
 
    // Plat du jour
    public function scopeDuJour(Builder $q): Builder
    {
        return $q->where('plat_du_jour', true);
    }
 
    public function scopeDansCategorie(Builder $q, ?string $categorie): Builder
    {
        return $categorie ? $q->where('categorie', $categorie) : $q;
    }
 
    /* ----------------------------- Accessors ----------------------------- */
 
    // URL publique de l'image (null si aucune)
    protected function urlImage(): Attribute
    {
        return Attribute::get(fn () => $this->image
            ? Storage::disk('public')->url($this->image)
            : null);
    }
 
    // Ex. 2500 -> "2 500 F"
    protected function prixFormate(): Attribute
    {
        return Attribute::get(fn () => number_format($this->prix, 0, ',', ' ').' F');
    }
}
 