<?php
session_start();
require 'resources/database/db.php';

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$pageTitle = "Events | SportConnect";
ob_start();

try {
    $pdo = new PDO("mysql:host=localhost;dbname=sportconnect", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    $stmt = $pdo->query("SELECT id, title, event_date, location, sport_type FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC");
    $events = $stmt->fetchAll();

} catch (PDOException $e) {
    echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
}
?>

<h1 class="text-purple">📅 Upcoming Sports Events</h1>
<p class="subtext">Explore sports events on the calendar!</p>

<?php if (isset($_SESSION['email'])): ?>
    <a href="create_event.php" class="btn-purple">➕ Create Event</a>
<?php endif; ?>
<br>
<a href="invitations.php" class="btn-outline-purple">📩 Invitations</a>

<div id="calendar">

</div>

<!-- FullCalendar CSS/JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const calendarEl = document.getElementById('calendar');

        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            height: 700,
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: ''
            },
            events: <?= json_encode(array_map(function ($event) {
                return [
                    'title' => $event['title'],
                    'start' => $event['event_date'],
                    'url' => 'event_details.php?id=' . $event['id'],
                    'title' => $event['title'] . ' (' . $event['sport_type'] . ')',
                    'display' => 'block'
                ];
            }, $events)) ?>,
            eventClick: function(info) {
                info.jsEvent.preventDefault(); // open in same tab
                window.location.href = info.event.url;
            }
        });

        calendar.render();
    });
</script>

<?php
$master_content = ob_get_clean();
include 'resources/view/layouts/sportconnnect/master.php';
?>


