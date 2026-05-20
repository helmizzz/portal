<?php
require_once 'includes/init.php';
require_once 'includes/theme_handler.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_permissions = getUserPermissions($conn, $_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Calendar</title>
    <link rel="icon" type="image/png" href="uploads/EIP.png">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <style>
        .fc-event {
            cursor: pointer;
        }
        body.dark-mode .fc-toolbar-title {
            color: #e0e0e0;
        }
        body.dark-mode .fc-daygrid-day-number, body.dark-mode .fc-col-header-cell-cushion {
            color: #c5c5c5;
        }
        body.dark-mode .fc-day-today {
            background-color: rgba(255, 255, 255, 0.1) !important;
        }
    </style>
</head>
<body class="<?= $theme_class ?>">
    
    <?php include 'main_nav.php'; ?>

    <main class="container mt-4">
        <div class="card shadow-sm">
            <div class="card-header">
                <h4 class="card-title mb-0">Event Calendar</h4>
            </div>
            <div class="card-body">
                <div id="calendar"></div>
            </div>
        </div>
    </main>

    <div class="modal fade" id="eventDetailModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventModalTitle"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Date:</strong> <span id="eventModalDate"></span></p>
                    <p id="eventModalDescription"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var eventDetailModal = new bootstrap.Modal(document.getElementById('eventDetailModal'));

            var calendar = new FullCalendar.Calendar(calendarEl, {
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
                },
                initialView: 'dayGridMonth',
                events: 'api/get_events.php', // URL ke API untuk mengambil data acara
                editable: false, // Pengguna tidak bisa mengedit acara dari sini
                selectable: false,
                eventClick: function(info) {
                    // Tampilkan modal saat acara diklik
                    document.getElementById('eventModalTitle').innerText = info.event.title;
                    
                    let startDate = info.event.start;
                    let endDate = info.event.end;
                    let dateString = startDate.toLocaleString('id-ID', { dateStyle: 'full', timeStyle: 'short' });
                    if (endDate) {
                        dateString += ' - ' + endDate.toLocaleString('id-ID', { dateStyle: 'full', timeStyle: 'short' });
                    }
                    document.getElementById('eventModalDate').innerText = dateString;
                    
                    document.getElementById('eventModalDescription').innerHTML = info.event.extendedProps.description || 'Tidak ada deskripsi.';
                    
                    eventDetailModal.show();
                }
            });

            calendar.render();
        });
    </script>
</body>
</html>