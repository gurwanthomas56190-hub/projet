<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Ldap\User as LdapUser;
use Illuminate\Support\Facades\Gate;

class AnnuaireController extends Controller
{
    public function index()
    {
        // 1. On récupère tous les utilisateurs de l'Active Directory
        $tousLesUtilisateurs = LdapUser::get();
        
        // 2. On filtre la collection Laravel pour exclure les comptes systèmes Windows
        $users = $tousLesUtilisateurs->reject(function ($user) {
            // On récupère le samaccountname en minuscules pour éviter les problèmes de casse
            $samaccountname = strtolower($user->getFirstAttribute('samaccountname'));
            
            $comptesExclus = [
                'krbtgt', 
                'guest', 
                'invité', 
                'defaultaccount', 
                'wdagutilityaccount', 
                'srv_intranet', // Correction de l'espace ici
                'administrateur' // Optionnel : à ajouter si vous ne voulez pas voir l'admin dans l'annuaire
            ];
            
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
        
        $user = new LdapUser();
        $user->cn = $request->input('prenom') . ' ' . $request->input('nom');
        $user->sn = $request->input('nom');
        $user->givenname = $request->input('prenom');
        $user->samaccountname = strtolower(substr($request->input('prenom'), 0, 1) . $request->input('nom'));
        
        if($request->filled('telephone')) {
            $user->telephonenumber = $request->input('telephone');
        }
        if($request->filled('email')) {
            $user->mail = $request->input('email');
        }
        
        $user->save();
        
        return redirect()->route('annuaire.index')->with('success', 'Employé ajouté avec succès à l\'Active Directory.');
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