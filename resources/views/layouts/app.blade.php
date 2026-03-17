<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Intranet') - Silvadec</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    
    @stack('scripts')
</head>
<body>

    <header class="intranet-header" style="position: sticky; top: 0; height: 10vh; z-index: 1000;">
        <div class="header-top">
            <div class="logo-area">
                <h1>Silvadec - Intranet</h1>
            </div>
            <div class="user-area">
                @auth
                    <span class="welcome-text">Bienvenue, {{ Auth::user()->getFirstAttribute('cn') ?? 'Collaborateur' }}</span>
                    <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                        @csrf
                        <button type="submit" class="logout-button">Se déconnecter</button>
                    </form>
                @endauth
            </div>
        </div>

        <nav class="main-nav">
            <ul>
                <li><a href="{{ url('/') }}" class="{{ Request::is('/') ? 'active' : '' }}">Accueil</a></li>
                <li><a href="#">Actualités</a></li>
                <li><a href="{{ url('/annuaire') }}" class="{{ Request::is('annuaire') ? 'active' : '' }}">Annuaire</a></li>
                <li><a href="{{ url('/planning') }}" class="{{ Request::is('planning') ? 'active' : '' }}">Planning</a></li>
                <li><a href="{{ url('/support_informatique') }}" class="{{ Request::is('support_informatique') ? 'active' : '' }}">Support Informatique</a></li>
            </ul>
        </nav>
    </header>

    @yield('content')

    <footer>
        <p>&copy; {{ date('Y') }} - Silvadec - Portail Interne Confidentiel</p>
    </footer>

</body>
</html>