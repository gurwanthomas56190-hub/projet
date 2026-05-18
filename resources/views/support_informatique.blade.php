@extends('layouts.app')

@section('title', 'Support Informatique')

@section('content')
<div class="container">
    <main style="width: 100%;">
        <div class="card">
            <h2>🛠️ Page du Support Informatique</h2>
            <p>Bienvenue sur l'espace réservé aux administrateurs.</p>
            
            <hr>
            
            <h3>Outils disponibles</h3>
            {{-- Utilisation de votre classe existante "quick-links" --}}
            <ul class="quick-links">
                <li>
                    <a href="http://192.168.2.103/zabbix" target="_blank" rel="noopener noreferrer">
                        {{-- Correction de la balise image avec la fonction asset() --}}
                        <img src="{{ asset('images.png') }}" alt="Zabbix" style="height: 1.2em; vertical-align: middle; margin-right: 5px;"> 
                        Zabbix
                    </a>
                </li>
                <li>
                    <a href="https://192.168.2.100:8006/" target="_blank" rel="noopener noreferrer">
                        <img src="https://cdn.simpleicons.org/proxmox/E57000" alt="Proxmox" style="height: 1.2em; vertical-align: middle; margin-right: 5px;"> 
                        Proxmox
                    </a>
                </li>
                <li>
                    <a href="http://192.168.2.103:8000" target="_blank" rel="noopener noreferrer">
                        🦖 Ankyloscan
                    </a>
                </li>
            </ul>

            <br>
            <hr>

            <h3>⚠️ Données brutes LDAP (Test)</h3>
            <div>
                @dump( App\Ldap\User::whereHas('mail')->first()->getAttributes() )
            </div>
            
        </div>
    </main>
</div>
@endsection