<?php
session_start();
require 'resources/database/db.php';

// Redirect to login page if not logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$error_message = ""; // To store error messages
$success_message = ""; // To store success messages

// List of sports types for the dropdown (updated list)
$sports_list = [
    'Basketball' => 'Basketball',
    'Soccer' => 'Soccer',
    'Tennis' => 'Tennis',
    'Dodgeball' => 'Dodgeball',
    'Hockey' => 'Hockey'
];

// Process form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validate CSRF token (optional for security)
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token mismatch.");
    }

    // Validate form fields
    $title = trim($_POST['title']);
    $event_date = $_POST['event_date'];
    $location = trim($_POST['location']);
    $sport_type = trim($_POST['sport_type']);

    if (empty($title) || empty($event_date) || empty($location) || empty($sport_type)) {
        $error_message = "Please fill out all fields.";
    } else {
        try {
            // Retrieve the user_id from the session
            $user_id = $_SESSION['user_id'];

            // Create PDO connection
            $pdo = new PDO("mysql:host=localhost;dbname=sportconnect", "root", "", [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);

            // Insert the event into the database
            $stmt = $pdo->prepare("INSERT INTO events (title, event_date, location, sport_type, user_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$title, $event_date, $location, $sport_type, $user_id]);

            // Get the ID of the newly created event
            $event_id = $pdo->lastInsertId();

            // Insert the creator as an attendee with "accepted" RSVP status in the rsvps table
            $rsvp_stmt = $pdo->prepare("INSERT INTO rsvps (user_id, event_id, status) VALUES (?, ?, 'accepted')");
            $rsvp_stmt->execute([$user_id, $event_id]);

            // Insert the creator as an invited user with "accepted" status in the invitations table
            $invitation_stmt = $pdo->prepare("INSERT INTO invitations (event_id, invitee_id, status) VALUES (?, ?, 'accepted')");
            $invitation_stmt->execute([$event_id, $user_id]);

            $success_message = "Event created successfully! You have been added as an attendee and invited.";
        } catch (PDOException $e) {
            // Enhanced error handling with detailed error message
            $error_message = "Error creating event: " . $e->getMessage();
            // Log the error for debugging purposes (you could also log it to a file)
            error_log("Error occurred in create_event.php: " . $e->getMessage());
        } catch (Exception $e) {
            // Handle any other exceptions
            $error_message = "Unexpected error: " . $e->getMessage();
            error_log("Unexpected error occurred: " . $e->getMessage());
        }
    }
}

// Generate a CSRF token for form security
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pageTitle = "Create Event | SportConnect";
ob_start();
?>
<div class="profile-container">
<h1>Create Event</h1>

<?php if ($error_message): ?>
    <p class="text-danger"><?= htmlspecialchars($error_message) ?></p>
<?php endif; ?>

<?php if ($success_message): ?>
    <p class="text-success"><?= htmlspecialchars($success_message) ?></p>
<?php endif; ?>

<!-- Event Creation Form -->
<form method="POST" action="create_event.php">
    <div>
        <label for="title">Event Title:</label>
        <input type="text" id="title" name="title" class="form-control" required>
    </div>
    <div>
        <label for="event_date">Event Date:</label>
        <input type="date" id="event_date" name="event_date" class="input-field" required>
    </div>
    <div>
        <label for="location">Location:</label>
        <input type="text" id="location" name="location" class="form-control" required>
    </div>
    <div>
        <label for="sport_type">Sport Type:</label>
        <select id="sport_type" name="sport_type" class="input-field" required>
            <option value="">Select a sport</option>
            <?php foreach ($sports_list as $key => $value): ?>
                <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($value) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    <button type="submit" class="btn-purple">Create Event</button>
</form>
</div>
<?php
$master_content = ob_get_clean();
include 'resources/view/layouts/sportconnnect/master.php';
?>



