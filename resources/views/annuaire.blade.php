<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Annuaire - Portail Entreprise</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        /* Un petit style en plus juste pour le tableau de l'annuaire */
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { padding: 0.8rem; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f8f9fa; color: #0056b3; }
    </style>
</head>
<body>

    <header>
        <h1>MonEntreprise - Intranet</h1>
        <div>Bienvenue, Collaborateur</div>
    </header>

    <nav>
        <a href="{{ url('/') }}">Accueil</a>
        <a href="#">Actualités</a>
        <a href="{{ url('/annuaire') }}">Annuaire</a>
        <a href="#">Documents RH</a>
        <a href="#">Support Informatique</a>
    </nav>

    <div class="container" style="grid-template-columns: 1fr;"> <main>
            <div class="card">
                <h2>📞 Annuaire des collaborateurs</h2>
                <p>Retrouvez facilement les coordonnées de vos collègues.</p>
                
                <table>
                    <thead>
                        <tr>
                            <th>Nom & Prénom</th>
                            <th>Service</th>
                            <th>Poste interne</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Alice Dupont</strong></td>
                            <td>Ressources Humaines</td>
                            <td>205</td>
                            <td>alice.d@monentreprise.com</td>
                        </tr>
                        <tr>
                            <td><strong>Bob Martin</strong></td>
                            <td>Support Informatique</td>
                            <td>404</td>
                            <td>bob.m@monentreprise.com</td>
                        </tr>
                        <tr>
                            <td><strong>Charlie Leblanc</strong></td>
                            <td>Comptabilité</td>
                            <td>301</td>
                            <td>charlie.l@monentreprise.com</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <footer>
        <p>&copy; 2026 - MonEntreprise - Portail Interne Confidentiel</p>
    </footer>

</body>
</html>