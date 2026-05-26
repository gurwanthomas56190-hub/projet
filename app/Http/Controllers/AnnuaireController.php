<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Ldap\User as LdapUser;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use LdapRecord\Models\ActiveDirectory\OrganizationalUnit;

class AnnuaireController extends Controller
{
    /**
     * Affiche la liste de tous les utilisateurs AD.
     * Accessible à tous les utilisateurs authentifiés.
     */
    public function index()
    {
        // 1. On récupère TOUS les utilisateurs depuis l'AD
        $tousLesEmployes = LdapUser::get();
        
        // 2. On filtre la liste en PHP pour cacher les comptes systèmes de Windows
        // ATTENTION ICI : On nomme la variable $users pour que ça corresponde à ton fichier Blade !
        $users = $tousLesEmployes->filter(function ($user) {
            $username = strtolower($user->getFirstAttribute('samaccountname'));
            return !in_array($username, ['krbtgt', 'guest', 'invité', 'defaultaccount', 'wdagutilityaccount', 'srv intranet']);
        });
        
        // 3. On envoie la bonne variable 'users' à la vue
        return view('annuaire', compact('users'));
    }

    /**
     * Affiche le formulaire de création d'un nouvel utilisateur AD.
     * Réservé aux admins.
     */
    public function create()
    {
        Gate::authorize('gerer-annuaire');

        // Récupère les OUs disponibles pour proposer un choix
        $ous = OrganizationalUnit::get();

        return view('annuaire_create', compact('ous'));
    }

    /**
     * Crée un nouvel utilisateur dans l'Active Directory.
     * Réservé aux admins.
     */
    public function store(Request $request)
    {
        Gate::authorize('gerer-annuaire');

        $request->validate([
            'prenom'    => 'required|string|max:100',
            'nom'       => 'required|string|max:100',
            'email'     => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:30',
            'password'  => 'required|string|min:8',
            'ou_dn'     => 'required|string',
        ]);

        $prenom = $request->input('prenom');
        $nom    = $request->input('nom');
        $cn     = $prenom . ' ' . $nom;
        $sam    = strtolower(substr($prenom, 0, 1) . $nom);
        $ouDn   = $request->input('ou_dn');

        // Vérifie que le samaccountname n'existe pas déjà
        $existing = LdapUser::where('samaccountname', $sam)->first();
        if ($existing) {
            return back()->withInput()->withErrors([
                'nom' => "Le compte '$sam' existe déjà dans l'Active Directory."
            ]);
        }

        try {
            $user = (new LdapUser)->inside($ouDn);
            $user->cn                  = $cn;
            $user->sn                  = $nom;
            $user->givenname           = $prenom;
            $user->samaccountname      = $sam;
            $user->userprincipalname   = $sam . '@' . $this->getDomainFromBaseDn();
            $user->displayname         = $cn;
            $user->unicodepwd          = $this->encodePassword($request->input('password'));

            if ($request->filled('email')) {
                $user->mail = $request->input('email');
            }
            if ($request->filled('telephone')) {
                $user->telephonenumber = $request->input('telephone');
            }

            $user->save();

            // Active le compte (supprime le flag ACCOUNTDISABLE = 2)
            $user->userAccountControl = 512; // NORMAL_ACCOUNT
            $user->save();

            return redirect()->route('annuaire.index')->with('success', "L'employé $cn a été ajouté avec succès à l'Active Directory.");

        } catch (\Exception $e) {
            Log::error('Erreur création utilisateur AD: ' . $e->getMessage());
            return back()->withInput()->withErrors([
                'general' => "Erreur lors de la création du compte : " . $e->getMessage()
            ]);
        }
    }

    /**
     * Affiche le formulaire de modification d'un utilisateur AD.
     * Réservé aux admins.
     */
    public function edit($samaccountname)
    {
        Gate::authorize('gerer-annuaire');

        $employe = LdapUser::where('samaccountname', $samaccountname)->firstOrFail();
        return view('annuaire_edit', compact('employe'));
    }

    /**
     * Met à jour un utilisateur dans l'Active Directory.
     * Réservé aux admins.
     */
    public function update(Request $request, $samaccountname)
    {
        Gate::authorize('gerer-annuaire');

        $request->validate([
            'prenom'    => 'required|string|max:100',
            'nom'       => 'required|string|max:100',
            'email'     => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:30',
            'password'  => 'nullable|string|min:8',
        ]);

        $user = LdapUser::where('samaccountname', $samaccountname)->firstOrFail();

        try {
            $user->sn          = $request->input('nom');
            $user->givenname   = $request->input('prenom');
            $user->cn          = $request->input('prenom') . ' ' . $request->input('nom');
            $user->displayname = $request->input('prenom') . ' ' . $request->input('nom');

            if ($request->filled('email')) {
                $user->mail = $request->input('email');
            }
            if ($request->filled('telephone')) {
                $user->telephonenumber = $request->input('telephone');
            }

            // Changement de mot de passe optionnel
            if ($request->filled('password')) {
                $user->unicodepwd = $this->encodePassword($request->input('password'));
            }

            $user->save();

            return redirect()->route('annuaire.index')->with('success', "L'employé a été mis à jour dans l'Active Directory.");

        } catch (\Exception $e) {
            Log::error('Erreur mise à jour utilisateur AD: ' . $e->getMessage());
            return back()->withInput()->withErrors([
                'general' => "Erreur lors de la mise à jour : " . $e->getMessage()
            ]);
        }
    }

    /**
     * Supprime un utilisateur de l'Active Directory.
     * Réservé aux admins.
     */
    public function destroy($samaccountname)
    {
        Gate::authorize('gerer-annuaire');

        try {
            $user = LdapUser::where('samaccountname', $samaccountname)->firstOrFail();
            $cn = $user->getFirstAttribute('cn');
            $user->delete();

            return redirect()->route('annuaire.index')->with('success', "L'employé $cn a été supprimé de l'Active Directory.");

        } catch (\Exception $e) {
            Log::error('Erreur suppression utilisateur AD: ' . $e->getMessage());
            return redirect()->route('annuaire.index')->withErrors([
                'general' => "Erreur lors de la suppression : " . $e->getMessage()
            ]);
        }
    }

    /**
     * Encode un mot de passe au format Unicode pour Active Directory.
     */
    private function encodePassword(string $password): string
    {
        return iconv('UTF-8', 'UTF-16LE', '"' . $password . '"');
    }

    /**
     * Extrait le nom de domaine depuis le LDAP_BASE_DN.
     * Ex: "dc=silvadec,dc=local" → "silvadec.local"
     */
    private function getDomainFromBaseDn(): string
    {
        $baseDn = config('ldap.connections.default.base_dn');
        preg_match_all('/dc=([^,]+)/i', $baseDn, $matches);
        return implode('.', $matches[1] ?? ['local']);
    }
}