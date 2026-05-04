<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Ldap\User as LdapUser; // Ton modèle LDAP
use Illuminate\Support\Facades\Gate;

class AnnuaireController extends Controller
{
    // READ : Afficher la liste des employés
    public function index()
    {
        // On récupère tous les utilisateurs de l'AD
        $employes = LdapUser::get();
        return view('annuaire', compact('employes'));
    }

    // CREATE : Afficher le formulaire d'ajout
    public function create()
    {
        Gate::authorize('gerer-annuaire');
        return view('annuaire_create'); // Vue à créer
    }

    // CREATE : Sauvegarder dans l'AD
    public function store(Request $request)
    {
        Gate::authorize('gerer-annuaire');

        $user = new LdapUser();
        $user->cn = $request->input('prenom') . ' ' . $request->input('nom');
        $user->sn = $request->input('nom');
        $user->givenname = $request->input('prenom');
        $user->samaccountname = strtolower(substr($request->input('prenom'), 0, 1) . $request->input('nom'));
        // Ajoute ici les autres attributs LDAP nécessaires (mail, telephoneNumber, etc.)
        
        $user->save();

        return redirect()->route('annuaire.index')->with('success', 'Employé ajouté à l\'AD.');
    }

    // UPDATE : Afficher le formulaire de modification
    public function edit($samaccountname)
    {
        Gate::authorize('gerer-annuaire');
        
        $employe = LdapUser::where('samaccountname', $samaccountname)->firstOrFail();
        return view('annuaire_edit', compact('employe')); // Vue à créer
    }

    // UPDATE : Mettre à jour dans l'AD
    public function update(Request $request, $samaccountname)
    {
        Gate::authorize('gerer-annuaire');

        $user = LdapUser::where('samaccountname', $samaccountname)->firstOrFail();
        $user->sn = $request->input('nom');
        $user->givenname = $request->input('prenom');
        $user->telephonenumber = $request->input('telephone');
        
        $user->save();

        return redirect()->route('annuaire.index')->with('success', 'Employé mis à jour.');
    }

    // DELETE : Supprimer de l'AD
    public function destroy($samaccountname)
    {
        Gate::authorize('gerer-annuaire');

        $user = LdapUser::where('samaccountname', $samaccountname)->firstOrFail();
        $user->delete();

        return redirect()->route('annuaire.index')->with('success', 'Employé supprimé de l\'AD.');
    }
}