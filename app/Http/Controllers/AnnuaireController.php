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
        // 1. On récupère tous les utilisateurs de l'Active Directory
        $tousLesUtilisateurs = LdapUser::get();
        
        // 2. On vérifie si l'utilisateur connecté est l'admin (par son samaccountname ou via la Gate)
        $isAdmin = Auth::user() && (
            strtolower(Auth::user()->getFirstAttribute('samaccountname')) === 'administrateur' || 
            Gate::allows('gerer-annuaire')
        );

        // 3. Si ce n'est PAS un admin, on applique le filtre d'exclusion des comptes systèmes
        if (!$isAdmin) {
            $users = $tousLesUtilisateurs->reject(function ($user) {
                $samaccountname = strtolower($user->getFirstAttribute('samaccountname'));
                
                $comptesExclus = [
                    'krbtgt', 
                    'guest', 
                    'invité', 
                    'defaultaccount', 
                    'wdagutilityaccount', 
                    'srv_intranet'
                ];
                
                return in_array($samaccountname, $comptesExclus);
            });
        } else {
            // Si c'est l'admin, on lui donne la collection complète sans aucun filtre
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
        
        // 1. Récupération et nettoyage des données de base
        $nom = str_replace(' ', '', $request->input('nom'));
        $prenom = str_replace(' ', '', $request->input('prenom'));
        $ville = $request->input('ville');
        $service = $request->input('service');

        // 2. Reproduction exacte des variables du script PowerShell
        $login = strtolower($nom . '.' . $prenom);
        $email = $login . '@silvadec.com';
        $upn = $login . '@silvadec.local';
        $profilPath = "\\\\192.168.2.101\\Profils$\\" . $login;
        
        // L'arborescence dynamique (Attention à ce que ces OU existent bien dans Windows !)
        $ouPath = "OU={$service},OU={$ville},OU=Silvadec,DC=silvadec,DC=local";

        // 3. Sécurité : Vérification d'existence
        $existingUser = LdapUser::where('samaccountname', $login)->first();
        if ($existingUser) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['samaccountname' => "Le compte {$login} existe déjà."]);
        }
        
        // 4. Création de l'utilisateur
        $user = new LdapUser();
        $user->cn = $nom . ' ' . $prenom; // Le script PS met "$Nom $Prenom"
        $user->givenname = $prenom;
        $user->sn = $nom;
        $user->samaccountname = $login;
        $user->userprincipalname = $upn;
        $user->mail = $email;
        $user->profilepath = $profilPath;
        
        if ($request->filled('telephone')) {
            $user->telephonenumber = $request->input('telephone');
        }
        if ($request->filled('adresse')) {
            $user->streetaddress = $request->input('adresse');
        }

        // 5. Configuration du mot de passe et des règles du compte
        $user->unicodepwd = 'Temp1234!';
        $user->pwdlastset = 0; // "ChangePasswordAtLogon = $true"
        $user->useraccountcontrol = 512; // "Enabled = $true" (Compte Normal activé)
        
        // On indique à Laravel dans quel dossier placer ce compte
        $user->inside($ouPath);
        
        try {
            $user->save();

            // 6. Ajout automatique au groupe de sécurité (ex: GRP_Informatique)
            $groupeName = "GRP_" . $service;
            $groupe = LdapGroup::where('cn', $groupeName)->first();
            
            if ($groupe) {
                // Si le groupe existe sur Windows, on ajoute le membre
                $groupe->members()->attach($user);
            }
            
            return redirect()->route('annuaire.index')->with('success', "Employé ajouté avec succès. Il a été placé dans $ville > $service avec le mot de passe Temp1234!");

        } catch (\Exception $e) {
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
        $user->sn = $request->input('nom');
        $user->givenname = $request->input('prenom');
        $user->cn = $request->input('prenom') . ' ' . $request->input('nom');
        
        if($request->filled('telephone')) {
            $user->telephonenumber = $request->input('telephone');
        }
        if($request->filled('email')) {
            $user->mail = $request->input('email');
        }
        
        $user->save();
        
        return redirect()->route('annuaire.index')->with('success', 'Employé mis à jour dans l\'Active Directory.');
    }

    public function destroy($samaccountname)
    {
        Gate::authorize('gerer-annuaire');
        
        $user = LdapUser::where('samaccountname', $samaccountname)->firstOrFail();
        $user->delete();
        
        return redirect()->route('annuaire.index')->with('success', 'Employé supprimé de l\'Active Directory.');
    }
}