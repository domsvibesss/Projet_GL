<?php

namespace App\Models\Concerns;

/**
 * Numéro affiché à l'écran, fabriqué à partir de l'id : PRÉFIXE-0007.
 * Le modèle choisit son préfixe avec : public const PREFIXE_NUMERO = 'EMP';
 * Utilisation : $employe->numero  →  "EMP-0007"
 */
trait HasNumero
{
    public function getNumeroAttribute(): string
    {
        return sprintf('%s-%04d', static::PREFIXE_NUMERO, $this->id);
    }
}
