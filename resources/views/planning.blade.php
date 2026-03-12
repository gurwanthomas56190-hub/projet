@extends('layouts.app')

@section('title', 'Planning')

@push('scripts')
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
    <style>
        #calendar {
            max-width: 1000px;
            margin: 0 auto;
        }
    </style>
@endpush

@section('content')
<div class="container full-width">
    <main>
        <div class="card">
            <h2>⏱️ Planning de travail de l'équipe</h2>
            <p>Consultez les horaires de présence des collaborateurs cette semaine.</p>
            
            <div id="calendar"></div>
        </div>
    </main>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'timeGridWeek', 
        locale: 'fr',
        firstDay: 1, 
        slotMinTime: '07:00:00',
        slotMaxTime: '20:00:00',
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
        events: [
        {
            title: 'Mon planning',
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
@endsection