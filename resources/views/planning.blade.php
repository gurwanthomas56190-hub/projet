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
              title: 'Alice (Développement)',
              daysOfWeek: [1, 2, 3, 4, 5], // 1=Lundi ... 5=Vendredi
              startTime: '09:00', // Heure de début
              endTime: '17:30',   // Heure de fin
              color: '#28a745'    // Vert
            },
            {
              title: 'Bob (Support IT)',
              daysOfWeek: [1, 2, 3, 4, 5],
              startTime: '08:00',
              endTime: '16:00',
              color: '#0056b3'    // Bleu
            },
            {
              title: 'Charlie (Accueil)',
              daysOfWeek: [1, 3, 5], // Ne travaille que Lundi, Mercredi, Vendredi
              startTime: '08:30',
              endTime: '12:30',
              color: '#17a2b8'    // Bleu clair
            },
            {
              title: 'Réunion d\'équipe',
              daysOfWeek: [1], // Seulement le Lundi
              startTime: '10:00',
              endTime: '11:00',
              color: '#dc3545'    // Rouge
            }
          ]
        });

        calendar.render();
      });
    </script>
</body>
</html>