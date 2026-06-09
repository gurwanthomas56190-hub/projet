<!--
Fichier généré par l'agent — objectif : rendre un nouvel agent AI immédiatement productif.
Conserver ce document court, factuel et directement lié au code source.
-->
# Instructions pour les agents Copilot / AI

Ce dépôt est une application Laravel (v12) intégrée à Active Directory via `directorytree/ldaprecord-laravel`, exposée dans Docker avec un proxy SSO Kerberos.

- **Points d'entrée clés** :
  - Routes : `routes/web.php` (SSO, routes protégées `auth`, préfixe `fichiers` pour le gestionnaire de fichiers).
  - Contrôleurs : `app/Http/Controllers/` (ex : `LoginController`, `AnnuaireController`, `FileManagerController`).
  - Modèles LDAP : `app/Ldap/*` (ex : `app/Ldap/User.php` utilise LdapRecord pour AD).
  - Middleware SSO : `app/Http/Middleware/KerberosSSO.php` (lit `X-Remote-User`).
  - Provider global : `app/Providers/AppServiceProvider.php` (définit le Gate `gerer-annuaire` et force HTTPS).
  - Config LDAP : `config/ldap.php` (hôtes, base_dn, options LDAPS). Voir variables d'environnement (`LDAP_HOST`, `LDAP_BASE_DN`, ...).

- **Architecture & flux important** :
  - Le SSO Kerberos est géré via un proxy Apache (`docker/apache`) qui injecte `X-Remote-User`. Le middleware `KerberosSSO` accepte ce header et connecte l'utilisateur AD via `App\\Ldap\\User`.
  - Il existe aussi une route secours `/sso-login` qui effectue la même jonction côté application (utile pour debug et migration progressive).
  - L'annuaire (AnnuaireController) lit/écrit directement dans l'AD via `LdapRecord` ; les autorisations d'admin sont contrôlées par Gate `gerer-annuaire`.

- **Conventions spécifiques au projet** :
  - LDAP vs utilisateur Laravel : utilisez `App\\Ldap\\User` pour toutes les opérations AD (lecture/CRUD). Ne pas confondre avec `App\\Models\\User` (auth local).
  - Les méthodes utilitaires LDAP (ex : `getService()`, `getSite()`) sont définies dans `app/Ldap/User.php` et sont utilisées par les vues.
  - Routes protégées : tout ce qui est sensible est placé dans `Route::middleware('auth')->group(...)` dans `routes/web.php`.
  - Gate `gerer-annuaire` est la source d'autorité pour les opérations CRUD AD.

- **Dépendances / intégrations externes** :
  - PHP 8.2+, Laravel 12, package LDAP `directorytree/ldaprecord-laravel` (voir `composer.json`).
  - Docker compose configure 3 services : `app`, `webserver` (nginx) et `sso_proxy` (Apache + Kerberos). Voir `docker-compose.yml` et `docker/apache`.
  - Le build Apache attend un fichier `intranet.keytab` à la racine et un `/etc/krb5.conf` monté sur l'hôte.

- **Commandes dev & CI utiles** :
  - Installation + build initial : `composer setup` (défini dans `composer.json`), ou manuellement :
    - `composer install`
    - `cp .env.example .env` (ou laisser `composer setup` faire la copie)
    - `php artisan key:generate`
    - `php artisan migrate`
    - `npm install && npm run build`
  - Mode dev local (multi-process) : `composer dev` (utilise `npx concurrently` pour lancer `php artisan serve`, files, vite, logs).
  - Docker : `docker-compose up -d` (monte `intranet.keytab` et `/etc/krb5.conf` pour SSO). Pour ouvrir un shell dans le conteneur : `docker exec -it laravel_app bash`.
  - Tests : `composer test` (exécute `@php artisan test`). Fichiers de config PHPUnit : `phpunit.xml`.

- **Ce qu'il faut vérifier avant tout changement touchant l'auth/SSO/LDAP** :
  - Variables d'environnement LDAP dans `.env` (host, base_dn, username/password si utilisé).
  - Présence et permissions de `intranet.keytab` (SANS clé, le SSO Apache échoue).
  - Que `app/Providers/AppServiceProvider.php` contient `URL::forceScheme('https')` si l'application est derrière proxy SSL.
  - Tests d'intégration AD : les contrôleurs font souvent `->first()` ou `->firstOrFail()` — manipulations AD peuvent lancer des exceptions réseau/LDAP (gestion présente dans contrôleurs).

- **Exemples rapides pour l'AI** :
  - Ajouter une nouvelle route protégée : mettre la route dans le `auth` group de `routes/web.php`.
  - Lire un attribut AD : `App\\Ldap\\User::where('samaccountname', $x)->first()` puis `$user->getFirstAttribute('mail')`.
  - Autorisation admin : utiliser `Gate::authorize('gerer-annuaire')` (ex. dans contrôleur).

Fin du document
