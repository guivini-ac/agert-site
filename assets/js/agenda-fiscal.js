// Calendar for Agenda Fiscal on home page
// Requires FullCalendar

document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.getElementById('agenda-fiscal-calendar');
    var dateFilter = document.getElementById('agenda-fiscal-date-filter');
    var goBtn = document.getElementById('agenda-fiscal-go-btn');

    if (!calendarEl || typeof FullCalendar === 'undefined' || typeof agertAgendaFiscal === 'undefined') {
        return;
    }

    var allEvents = agertAgendaFiscal.events || [];

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'pt-br',
        events: allEvents,
        eventClick: function(info) {
            if (info.event.url) {
                window.location.href = info.event.url;
                info.jsEvent.preventDefault();
            }
        }
    });

    calendar.render();

    if (dateFilter && goBtn) {
        goBtn.addEventListener('click', function () {
            var selected = dateFilter.value;
            calendar.removeAllEvents();
            if (selected) {
                var parts = selected.split('-');
                var year = parseInt(parts[0], 10);
                var month = parseInt(parts[1], 10) - 1; // zero-based
                var startOfMonth = new Date(year, month, 1);
                var endOfMonth = new Date(year, month + 1, 0);
                var filtered = allEvents.filter(function (ev) {
                    var start = new Date(ev.start);
                    var end = ev.end ? new Date(ev.end) : start;
                    return start <= endOfMonth && end >= startOfMonth;
                });
                calendar.addEventSource(filtered);
                calendar.gotoDate(startOfMonth);
            } else {
                calendar.addEventSource(allEvents);
            }
        });
    }
});
