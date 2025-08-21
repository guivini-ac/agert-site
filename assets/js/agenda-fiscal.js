// Calendar for Agenda Fiscal on home page
// Requires FullCalendar

document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.getElementById('agenda-fiscal-calendar');

    var dateFilter = document.getElementById('agenda-fiscal-date-filter');

    if (!calendarEl || typeof FullCalendar === 'undefined' || typeof agertAgendaFiscal === 'undefined') {
        return;
    }

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'pt-br',
        events: agertAgendaFiscal.events || [],
        eventClick: function(info) {
            if (info.event.url) {
                window.location.href = info.event.url;
                info.jsEvent.preventDefault();
            }
        }
    });

    calendar.render();

    if (dateFilter) {
        dateFilter.addEventListener('change', function () {
            var selected = dateFilter.value;
            calendar.removeAllEvents();
            if (selected) {
                var d = new Date(selected);
                d.setHours(0, 0, 0, 0);
                var filtered = allEvents.filter(function (ev) {
                    var start = new Date(ev.start);
                    var end = ev.end ? new Date(ev.end) : start;
                    start.setHours(0, 0, 0, 0);
                    end.setHours(0, 0, 0, 0);
                    return start <= d && end >= d;
                });
                calendar.addEventSource(filtered);
                calendar.gotoDate(selected);
            } else {
                calendar.addEventSource(allEvents);
            }
        });
    }

});
