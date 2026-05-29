<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Ldap\User as LdapUser;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use LdapRecord\Models\ActiveDirectory\Group as LdapGroup;

class AnnuaireController extends Controller
{
    public function index()
    {
        $tousLesUtilisateurs = LdapUser::get();
        
        // 1. Liste des comptes systèmes et admins à CACHER de l'annuaire
        $comptesExclus = [
            'krbtgt', 
            'guest', 
            'invité', 
            'defaultaccount', 
            'wdagutilityaccount', 
            'srv_intranet',
            'administrateur' // <-- L'administrateur est maintenant caché de la liste !
        ];

        // 2. On filtre la liste pour n'avoir QUE les vrais employés
        $users = $tousLesUtilisateurs->reject(function ($user) use ($comptesExclus) {
            $samaccountname = strtolower($user->getFirstAttribute('samaccountname') ?? '');
            return in_array($samaccountname, $comptesExclus);
        });
        
        return view('annuaire', compact('users'));
    }

    public function create()
    {
        Gate::authorize('gerer-annuaire');
        return view('annuaire_create');
    }

    public function store(Request $request)
    {
        Gate::authorize('gerer-annuaire');
        
        $nom = trim($request->input('nom'));
        $prenom = trim($request->input('prenom'));
        $ville = $request->input('ville');
        $service = $request->input('service');
        $adresse = $request->input('adresse');
        $telephone = $request->input('telephone');

        $login = $nom . '.' . $prenom;
        $email = $nom . '.' . $prenom . '@silvadec.com';
        $domaine = 'silvadec.local';
        $profilPath = "\\\\192.168.2.101\\Profils$\\%" . $login . "%";
        $ouPath = "OU={$service},OU={$ville},OU=Silvadec,DC=silvadec,DC=local";
        $groupeName = "GRP_" . $service;

        $existingUser = LdapUser::where('samaccountname', $login)->first();
        if ($existingUser) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['samaccountname' => "Le compte {$login} existe déjà dans l'Active Directory."]);
        }
        
        $user = new LdapUser();
        $user->cn = $nom . ' ' . $prenom;
        $user->givenname = $prenom;
        $user->sn = $nom;
        $user->samaccountname = $login;
        $user->userprincipalname = $login . '@' . $domaine;
        $user->mail = $email;
        $user->profilepath = $profilPath;
        
        if (!empty($adresse)) {
            $user->streetaddress = $adresse;
        }
        if (!empty($telephone)) {
            $user->telephonenumber = $telephone;
        }

        $user->inside($ouPath);
        
        try {
            // LDAPS est ACTIF : On configure le mot de passe !
            $user->unicodepwd = 'Temp1234!';
            $user->pwdlastset = 0;                  // Forcer le changement à la 1ere connexion
            $user->useraccountcontrol = 512;        // Compte Activé

            $user->save();

            $groupe = LdapGroup::where('cn', $groupeName)->first();
            if ($groupe) {
                $groupe->members()->attach($user);
            }
            
            return redirect()->route('annuaire.index')->with('success', "Utilisateur créé avec succès ! Créé dans $ouPath et ajouté au groupe $groupeName avec le mot de passe Temp1234!");

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['erreur_ldap' => 'Création refusée par Active Directory (Vérifiez que le LDAPS fonctionne côté Windows). ERREUR : ' . $e->getMessage()]);
        }
    }

    public function edit($samaccountname)
    {
        Gate::authorize('gerer-annuaire');
        $employe = LdapUser::where('samaccountname', $samaccountname)->firstOrFail();
        return view('annuaire_edit', compact('employe'));
    }

    public function update(Request $request, $samaccountname)
    {
        Gate::authorize('gerer-annuaire');
        
        $user = LdapUser::where('samaccountname', $samaccountname)->firstOrFail();
        
        $nouveauPrenom = $request->input('prenom');
        $nouveauNom = $request->input('nom');
        $nouveauCn = $nouveauNom . ' ' . $nouveauPrenom;

        try {
            if ($user->getFirstAttribute('cn') !== $nouveauCn) {
                $user->rename($nouveauCn);
            }

            $user->sn = $nouveauNom;
            $user->givenname = $nouveauPrenom;
            
            if($request->filled('telephone')) {
                $user->telephonenumber = $request->input('telephone');
            } else {
                $user->removeAttribute('telephonenumber');
            }
            
            if($request->filled('email')) {
                $user->mail = $request->input('email');
            } else {
                $user->removeAttribute('mail');
            }
            
            $user->save();
            return redirect()->route('annuaire.index')->with('success', 'Employé mis à jour avec succès dans l\'Active Directory.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['erreur_ldap' => 'Mise à jour refusée par Active Directory. ERREUR : ' . $e->getMessage()]);
        }
    }

    public function destroy($samaccountname)
    {
        Gate::authorize('gerer-annuaire');
        
        try {
            $user = LdapUser::where('samaccountname', $samaccountname)->firstOrFail();
            $user->delete();
            return redirect()->route('annuaire.index')->with('success', 'Employé supprimé de l\'Active Directory.');
        } catch (\Exception $e) {
            return redirect()->route('annuaire.index')->withErrors(['erreur_ldap' => 'Impossible de supprimer l\'utilisateur : ' . $e->getMessage()]);
        }
    }
}