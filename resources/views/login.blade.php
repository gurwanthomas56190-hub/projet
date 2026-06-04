<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - Intranet</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body style="display:flex; align-items:center; justify-content:center; height:100vh;">
    <div class="card" style="width: 400px;">
        <h2>Connexion Intranet</h2>
        <a href="{{ route('sso.login') }}" style="display:block; background-color:#28a745; color:white; padding:10px; text-align:center; margin-bottom:20px; text-decoration:none; border-radius:5px;">
    🔑 Connexion Automatique (Réseau Silvadec)
        </a>
        <form action="{{ url('/login') }}" method="POST">
            @csrf
            <div style="margin-bottom: 1rem;">
                <label>Identifiant Windows</label>
                <input type="text" name="samaccountname" required style="width:100%; padding:8px;">
            </div>
            <div style="margin-bottom: 1rem;">
                <label>Mot de passe</label>
                <input type="password" name="password" required style="width:100%; padding:8px;">
            </div>
            <button type="submit" style="background:#0056b3; color:white; border:none; padding:10px 20px; cursor:pointer; width:100%;">Se connecter</button>
            @if ($errors->any())
                <p style="color:red; margin-top:10px;">{{ $errors->first() }}</p>
            @endif
        </form>
    </div>
</body>
</html>