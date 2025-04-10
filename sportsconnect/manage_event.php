<?php
session_start();
require 'resources/database/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id = $_GET['id'] ?? null;
$error = '';
$success = '';
$event = null;

try {
    $pdo = new PDO("mysql:host=localhost;dbname=sportconnect", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    if (!$id) {
        throw new Exception("Event ID is missing.");
    }

    // Fetch event details for this user
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $_SESSION['user_id']]);
    $event = $stmt->fetch();

    if (!$event) {
        throw new Exception("Event not found or you don't have permission.");
    }

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $event_date = $_POST['event_date'];
        $location = trim($_POST['location']);

        if (empty($title) || empty($event_date) || empty($location)) {
            $error = "Title, date, and location are required.";
        } else {
            $stmt = $pdo->prepare("UPDATE events SET title = ?, description = ?, event_date = ?, location = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$title, $description, $event_date, $location, $id, $_SESSION['user_id']]);
            $success = "Event updated successfully.";
            // Refresh the event data
            $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $_SESSION['user_id']]);
            $event = $stmt->fetch();
        }
    }

} catch (Exception $e) {
    $error = $e->getMessage();
}

$pageTitle = "Manage Event | SportConnect";
ob_start();
?>
<div class="profile-container">
<h2>Manage Event</h2>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php if ($event): ?>
    <form method="POST">
        <div>
            <label for="title">Event Title</label>
            <input type="text" name="title" id="title" class="input-field" value="<?= htmlspecialchars($event['title']) ?>" required>
        </div>

        <div>
            <label for="description">Description</label>
            <textarea name="description" id="description" class="input-field" rows="4"><?= htmlspecialchars($event['description']) ?></textarea>
        </div>

        <div>
            <label for="event_date">Date</label>
            <input type="date" name="event_date" id="event_date" class="input-field" value="<?= htmlspecialchars($event['event_date']) ?>" required>
        </div>

        <div>
            <label for="location">Location</label>
            <input type="text" name="location" id="location" class="input-field" value="<?= htmlspecialchars($event['location']) ?>" required>
        </div>

        <button type="submit" class="btn-purple">Update Event</button>
    </form>
</div>
<?php endif; ?>

<?php
$master_content = ob_get_clean();
include 'resources/view/layouts/sportconnnect/master.php';
?>

