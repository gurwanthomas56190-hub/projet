<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planning - Portail Entreprise</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
    
    <style>
        #calendar {
            max-width: 1000px;
            margin: 0 auto;
            font-family: Arial, Helvetica Neue, Helvetica, sans-serif;
            font-size: 14px;
        }
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
        <a href="#">Support Informatique</a>
    </nav>

    <div class="container" style="grid-template-columns: 1fr;">
        <main>
            <div class="card">
                <h2>⏱️ Planning de travail de l'équipe</h2>
                <p>Consultez les horaires de présence des collaborateurs cette semaine.</p>
                
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
          // On change la vue par défaut pour voir les heures de la semaine
          initialView: 'timeGridWeek', 
          locale: 'fr',
          firstDay: 1, 
          
          // On restreint l'affichage pour ne pas voir la nuit (de 7h à 20h)
          slotMinTime: '07:00:00',
          slotMaxTime: '20:00:00',
          
          // On masque les week-ends si l'entreprise est fermée
          weekends: false, 

          headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'timeGridWeek,timeGridDay'
          },
          buttonText: {
            today: "Aujourd'hui",
            week: 'Semaine',
            day: 'Jour'
          },
          
          // Voici les horaires de travail avec heures de début et fin
          events: [
            {
              title: 'Mon planning ({{ Auth::user()->name }})',
              daysOfWeek: [1, 2, 3, 4, 5],
              startTime: '08:30',
              endTime: '17:00',
              color: '#28a745'
            }
          ]
        });

        calendar.render();
      });
    </script>
</body>
</html>