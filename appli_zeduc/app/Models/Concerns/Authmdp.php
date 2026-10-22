<?php
 
namespace App\Models\Concerns;
 
/**
 * Permet à un modèle dont la colonne mot de passe s'appelle `mdp`
 * (et sans colonne remember_token) de fonctionner avec les guards Laravel.
 * À utiliser dans Administrateur, Gerant, Employe et Etudiant.
 */
trait AuthenticatesWithMdp
{
    public function getAuthPassword()
    {
        return $this->mdp;
    }
 
    public function getAuthPasswordName()
    {
        return 'mdp';
    }
 
    // Pas de colonne remember_token dans tes tables : on désactive la fonction
    public function getRememberTokenName()
    {
        return '';
    }
}