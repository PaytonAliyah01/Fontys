<?php
session_start();
require 'resources/database/db.php';

// Redirect to login page if not logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$pageTitle = "Invitations | SportConnect";
ob_start();

try {
    $pdo = new PDO("mysql:host=localhost;dbname=sportconnect", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // Fetch all events where the logged-in user is the creator
    $stmt = $pdo->prepare("SELECT id, title FROM events WHERE event_date >= CURDATE() AND user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $events = $stmt->fetchAll();

    if (empty($events)) {
        $error_message = "You are not authorized to invite people to events that you did not create.";
    }

    // Fetch all users to invite (excluding the logged-in user)
    $stmt = $pdo->prepare("SELECT id, full_name FROM users WHERE id != ?");
    $stmt->execute([$_SESSION['user_id']]);
    $users = $stmt->fetchAll();

    // Fetch invitations for the logged-in user (invitee)
    $stmt = $pdo->prepare("
        SELECT 
            invitations.id AS invitation_id,
            events.title AS event_title,
            events.event_date,
            events.location,
            events.sport_type,
            invitations.status 
        FROM invitations 
        JOIN events ON invitations.event_id = events.id 
        WHERE invitations.invitee_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $invitations = $stmt->fetchAll();

} catch (PDOException $e) {
    echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
}

// Process invitation form (sending invitation)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['event_id']) && isset($_POST['invitee_id'])) {
    $event_id = $_POST['event_id'];
    $invitee_id = $_POST['invitee_id'];

    if (empty($event_id) || empty($invitee_id)) {
        $error_message = "Please select both an event and a user to invite.";
    } else {
        try {
            // Check if the logged-in user is the creator of the event
            $stmt = $pdo->prepare("SELECT user_id FROM events WHERE id = ?");
            $stmt->execute([$event_id]);
            $event = $stmt->fetch();

            if ($event['user_id'] != $_SESSION['user_id']) {
                $error_message = "You are not authorized to invite people to this event.";
            } else {
                // If the user is the event creator, send the invitation
                $stmt = $pdo->prepare("INSERT INTO invitations (event_id, inviter_id, invitee_id, user_id) VALUES (?, ?, ?, ?)");
                $stmt->execute([$event_id, $_SESSION['user_id'], $invitee_id, $_SESSION['user_id']]);

                $success_message = "Invitation sent successfully!";
                header("Location: invitations.php");
                exit();
            }
        } catch (PDOException $e) {
            $error_message = "Error sending invitation: " . $e->getMessage();
        }
    }
}
?>
<!-- Main Content Area -->
<main>
<div class="profile-container">
    <h1 class="page-title">Invite Users to Events</h1>

    <!-- Display messages -->
    <?php if (isset($error_message)): ?>
        <p class="alert alert-danger"><?= htmlspecialchars($error_message) ?></p>
    <?php endif; ?>

    <?php if (isset($success_message)): ?>
        <p class="alert alert-success"><?= htmlspecialchars($success_message) ?></p>
    <?php endif; ?>

    <!-- Invitation Form -->
    <form method="POST" action="invitations.php" class="card">
        <div class="form-group">
            <label for="event_id">Select Event:</label>
            <select id="event_id" name="event_id" class="input-field" required>
                <option value="">Select an event</option>
                <?php foreach ($events as $event): ?>
                    <option value="<?= $event['id'] ?>"><?= htmlspecialchars($event['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="invitee_id">Invite User:</label>
            <select id="invitee_id" name="invitee_id" class="input-field" required>
                <option value="">Select a user</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['full_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn-purple">Send Invitation</button>
    </form>

    <!-- Invitations List -->
    <h2 class="section-title">My Invitations</h2>
    <?php if (!empty($invitations)): ?>
        <ul class="invitation-list">
            <?php foreach ($invitations as $invitation): ?>
                <li class="invitation-item">
                    <div class="invitation-info">
                        <strong class="invitation-title"><?= htmlspecialchars($invitation['event_title']) ?></strong> -
                        <span class="invitation-type"><?= htmlspecialchars($invitation['sport_type']) ?></span> <br>
                        <span class="invitation-date"><?= date("F j, Y", strtotime($invitation['event_date'])) ?></span> -
                        <span class="invitation-location"><?= htmlspecialchars($invitation['location']) ?></span>
                    </div>

                    <div class="invitation-status">
                        <span class="badge badge-<?=
                        $invitation['status'] == 'accepted' ? 'accepted' : (
                        $invitation['status'] == 'declined' ? 'declined' : 'pending'
                        ) ?>">
                            <?= ucfirst($invitation['status']) ?>
                        </span>

                        <a href="rsvp.php?invitation_id=<?= $invitation['invitation_id'] ?>" class="btn-outline-purple btn-sm">
                            View RSVP
                        </a>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No invitations yet.</p>
    <?php endif; ?>
</div>
</main>
<?php
$master_content = ob_get_clean();
include 'resources/view/layouts/sportconnnect/master.php';
?>

