<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Intranet - Portail Entreprise</title>
    
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>

    <header>
        <h1>MonEntreprise - Intranet</h1>
        <div>Bienvenue, Collaborateur</div>
    </header>

    <nav>
        <a href="#">Accueil</a>
        <a href="#">Actualités</a>
        <a href="#">Annuaire</a>
        <a href="#">Documents RH</a>
        <a href="#">Support Informatique</a>
    </nav>

    <div class="container">
        <main>
            <div class="card">
                <h2>📢 Dernières Annonces</h2>
                <h3>Mise à jour du système informatique</h3>
                <p>Le service informatique procédera à une maintenance des serveurs ce vendredi à partir de 20h. Pensez à bien sauvegarder votre travail.</p>
                <hr style="border: 0; border-top: 1px solid #eee; margin: 1rem 0;">
                <h3>Nouvelle recrue au service Comptabilité</h3>
                <p>Souhaitons la bienvenue à Marie qui rejoint notre équipe comptable à partir d'aujourd'hui. Son bureau se trouve au 2ème étage.</p>
            </div>
        </main>

        <aside>
            <div class="card">
                <h2>🛠️ Liens Rapides</h2>
                <ul class="quick-links">
                    <li><a href="#">📩 Webmail</a></li>
                    <li><a href="#">🗓️ Planning des congés</a></li>
                    <li><a href="#">📁 Serveur de fichiers</a></li>
                    <li><a href="#">🆘 Créer un ticket IT</a></li>
                </ul>
            </div>
            
            <div class="card">
                <h2>📞 Contacts Utiles</h2>
                <p><strong>Accueil :</strong> Poste 101</p>
                <p><strong>Support IT :</strong> Poste 404</p>
                <p><strong>RH :</strong> Poste 205</p>
            </div>
        </aside>
    </div>

    <footer>
        <p>&copy; 2026 - MonEntreprise - Portail Interne Confidentiel</p>
    </footer>

</body>
</html>