<?php

namespace App\Models\Concerns;

/**
 * Remplit la date d'enregistrement d'un compte à sa création.
 * Les tables ont aussi created_at / updated_at : on garde ces timestamps
 * et on remplit en plus la colonne métier.
 * Un modèle change de colonne en redéfinissant colonneDateEnregistrement().
 */
trait HasDateEnregistrement
{
    protected static function bootHasDateEnregistrement(): void
    {
        static::creating(function ($model) {
            $model->{$model->colonneDateEnregistrement()} ??= now();
        });
    }

    public function colonneDateEnregistrement(): string
    {
        return 'date_enregistrement';
    }
}
