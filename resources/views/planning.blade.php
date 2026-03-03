<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planning - Portail Entreprise</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
    
    <style>
        /* On donne juste une taille à notre calendrier */
        #calendar {
            max-width: 900px;
            margin: 0 auto;
            font-family: Arial, Helvetica Neue, Helvetica, sans-serif;
            font-size: 14px;
        }
    </style>
</head>
<body>

    <header>
        <h1>Silvadec</h1>
        <div>Bienvenue, Collaborateur</div>
    </header>

    <nav>
        <a href="{{ url('/') }}">Accueil</a>
        <a href="#">Actualités</a>
        <a href="{{ url('/annuaire') }}">Annuaire</a>
        <a href="#">Support Informatique</a>
    </nav>

    <div class="container" style="grid-template-columns: 1fr;">
        <main>
            <div class="card">
                <h2>🗓️ Planning des congés et événements</h2>
                
                <div id="calendar"></div>

            </div>
        </main>
    </div>

    <footer>
        <p>&copy; 2026 - MonEntreprise - Portail Interne Confidentiel</p>
    </footer>

    <script>
      document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');

        var calendar = new FullCalendar.Calendar(calendarEl, {
          initialView: 'dayGridMonth', // Vue par mois
          locale: 'fr', // On le met en français !
          firstDay: 1, // La semaine commence le lundi
          headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
          },
          buttonText: {
            today: "Aujourd'hui",
            month: 'Mois',
            week: 'Semaine',
            day: 'Jour'
          },
          // Voici quelques faux événements pour tester
          events: [
            {
              title: "Réunion d'équipe",
              start: new Date().toISOString().split('T')[0], // Met l'événement à la date d'aujourd'hui
              color: '#0056b3'
            },
            {
              title: 'Congés Alice',
              start: '2026-03-10', // Format YYYY-MM-DD
              end: '2026-03-15',
              color: '#ffc107', // Jaune
              textColor: '#333'
            },
            {
              title: 'Formation Sécurité',
              start: '2026-03-20T14:00:00', // Avec une heure précise
              color: '#28a745' // Vert
            }
          ]
        });

        calendar.render();
      });
    </script>
</body>
</html>