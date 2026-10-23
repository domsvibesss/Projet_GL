<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Photos des pages admin (plats, employés), stockées sur le disque public.
 * Les chemins renvoyés sont ceux enregistrés en base (ex. "plats/abc.png").
 */
class ImageStorage
{
    private const DISQUE = 'public';

    /** Nouveau fichier envoyé : on le stocke et on supprime l'ancien. Sinon on garde l'ancien. */
    public function remplacer(?string $ancien, ?UploadedFile $nouveau, string $dossier): ?string
    {
        if (! $nouveau) {
            return $ancien;
        }

        $this->supprimer($ancien);

        return $nouveau->store($dossier, self::DISQUE);
    }

    public function supprimer(?string $chemin): void
    {
        if ($chemin) {
            Storage::disk(self::DISQUE)->delete($chemin);
        }
    }
}
