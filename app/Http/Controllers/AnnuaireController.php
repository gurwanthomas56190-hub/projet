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
        
        $isAdmin = Auth::user() && (
            strtolower(Auth::user()->getFirstAttribute('samaccountname')) === 'administrateur' || 
            Gate::allows('gerer-annuaire')
        );

        if (!$isAdmin) {
            $users = $tousLesUtilisateurs->reject(function ($user) {
                $samaccountname = strtolower($user->getFirstAttribute('samaccountname'));
                
                $comptesExclus = [
                    'krbtgt', 'guest', 'invité', 'defaultaccount', 'wdagutilityaccount', 'srv_intranet'
                ];
                
                return in_array($samaccountname, $comptesExclus);
            });
        } else {
            $users = $tousLesUtilisateurs;
        }
        
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
        
        // 1. Récupération des informations utilisateur
        $nom = trim($request->input('nom'));
        $prenom = trim($request->input('prenom'));
        $ville = $request->input('ville');
        $service = $request->input('service');
        $adresse = $request->input('adresse');
        $telephone = $request->input('telephone');

        // 2. Construction des variables (Copie exacte du script PowerShell)
        $login = $nom . '.' . $prenom;
        $email = $nom . '.' . $prenom . '@silvadec.com';
        $domaine = 'silvadec.local';
        $profilPath = "\\\\192.168.2.101\\Profils$\\%" . $login . "%";
        $ouPath = "OU={$service},OU={$ville},OU=Silvadec,DC=silvadec,DC=local";
        $groupeName = "GRP_" . $service;

        // 3. Sécurité : Vérification d'existence
        $existingUser = LdapUser::where('samaccountname', $login)->first();
        if ($existingUser) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['samaccountname' => "Le compte {$login} existe déjà dans l'Active Directory."]);
        }
        
        // 4. Création de l'utilisateur (Attributs de base)
        $user = new LdapUser();
        $user->cn = $nom . ' ' . $prenom;       // -Name "$Nom $Prenom"
        $user->givenname = $prenom;             // -GivenName $Prenom
        $user->sn = $nom;                       // -Surname $Nom
        $user->samaccountname = $login;         // -SamAccountName $Login
        $user->userprincipalname = $login . '@' . $domaine; // -UserPrincipalName
        $user->mail = $email;                   // -EmailAddress $Email
        $user->profilepath = $profilPath;       // -ProfilePath $ProfilPath
        
        if (!empty($adresse)) {
            $user->streetaddress = $adresse;
        }
        if (!empty($telephone)) {
            $user->telephonenumber = $telephone;
        }

        // On indique le chemin de destination (OU)
        $user->inside($ouPath);
        
        try {
            // ==============================================================
            // DÉPLACÉ ICI : Configuration du mot de passe et du compte.
            // Si LDAPS n'est pas encore actif, l'erreur sera capturée proprement !
            // ==============================================================
            $user->unicodepwd = 'Temp1234!';
            $user->pwdlastset = 0;                  // -ChangePasswordAtLogon $true
            $user->useraccountcontrol = 512;        // -Enabled $true

            // Sauvegarde dans l'AD
            $user->save();

            // 5. Ajout au groupe (Équivalent de Add-ADGroupMember)
            $groupe = LdapGroup::where('cn', $groupeName)->first();
            if ($groupe) {
                $groupe->members()->attach($user);
            }
            
            return redirect()->route('annuaire.index')->with('success', "Utilisateur créé avec succès ! Créé dans $ouPath et ajouté au groupe $groupeName.");

        } catch (\Exception $e) {
            // En cas de refus du serveur LDAP (ex: erreur SSL pour le mot de passe)
            return redirect()->back()
                ->withInput()
                ->withErrors(['erreur_ldap' => 'Création refusée par Active Directory. ERREUR : ' . $e->getMessage()]);
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
            // Renommage LDAP si le nom complet change
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