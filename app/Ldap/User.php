<?php

namespace App\Ldap;

use LdapRecord\Models\ActiveDirectory\User as LdapUser;

class User extends LdapUser
{
    public function getService()
    {
        $dn = $this->getFirstAttribute('distinguishedname');
        
        // On cherche tous les textes qui suivent "OU=" jusqu'à la prochaine virgule
        if (preg_match_all('/OU=([^,]+)/i', $dn, $matches)) {
            return $matches[1][0] ?? 'Non renseigné'; // Retourne le 1er résultat
        }
        
        return 'Non renseigné';
    }

    /**
     * Récupère le site/localisation (Le 2ème "OU" du distinguishedname, ex: Arzal)
     */
    public function getSite()
    {
        $dn = $this->getFirstAttribute('distinguishedname');
        
        // On cherche à nouveau tous les "OU="
        if (preg_match_all('/OU=([^,]+)/i', $dn, $matches)) {
            return $matches[1][1] ?? 'Non renseigné'; // Retourne le 2ème résultat
        }
        
        return 'Non renseigné';
    }
}