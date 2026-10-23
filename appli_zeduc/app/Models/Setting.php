<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Paramètres du restaurant (page Paramètres de l'administrateur).
 * Une ligne par clé ; la valeur est stockée en JSON.
 */
class Setting extends Model
{
    public const JOURS = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];

    protected $fillable = ['key', 'value'];

    protected $casts = ['value' => 'json'];

    /** Valeurs utilisées tant que l'administrateur n'a rien enregistré (celles de la maquette). */
    public static function defauts(): array
    {
        $semaine = ['ouverture' => '16:45', 'fermeture' => '22:00', 'ouvert' => true];

        return [
            'horaires' => array_fill_keys(['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi'], $semaine) + [
                'samedi'   => ['ouverture' => '10:00', 'fermeture' => '23:00', 'ouvert' => true],
                'dimanche' => ['ouverture' => '',      'fermeture' => '23:00', 'ouvert' => true],
            ],
            'taux_point'              => 1000,   // 1 point = 1000 FCFA
            'validite_mois'           => 12,     // les points expirent après 12 mois
            'politique_fidelite'      => 'Les points sont crédités après chaque achat. 15 points te donnent droit à une remise de 1000 FCFA.',
            'politique_parrainage'    => 'Le parrain et son filleul reçoivent 3 points après le premier achat de son filleul.',
            'politique_remboursement' => 'Toute demande est étudiée par notre équipe sous 48 heures. Un avoir peut être proposé selon la situation.',
        ];
    }

    /** Tous les paramètres : ceux enregistrés en base, complétés par les valeurs par défaut. */
    public static function tous(): array
    {
        return array_replace(self::defauts(), static::pluck('value', 'key')->all());
    }

    public static function enregistrer(array $valeurs): void
    {
        foreach ($valeurs as $cle => $valeur) {
            static::updateOrCreate(['key' => $cle], ['value' => $valeur]);
        }
    }
}
