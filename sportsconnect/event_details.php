<?php
session_start();
require 'resources/database/db.php';

// Redirect to login page if not logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Check if the event ID is passed in the URL
if (!isset($_GET['id'])) {
    die("Event ID not specified.");
}

$event_id = $_GET['id'];

$pageTitle = "Event Details | SportConnect";
ob_start();

try {
    $pdo = new PDO("mysql:host=localhost;dbname=sportconnect", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // Fetch event details from the database
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch();

    if (!$event) {
        die("Event not found.");
    }

    // Check if the logged-in user is the creator
    $is_creator = ($_SESSION['user_id'] == $event['user_id']); // Assuming 'user_id' is stored in session

    // Fetch the list of users who have RSVPed to the event, along with their RSVP status
    $rsvp_stmt = $pdo->prepare("SELECT users.email, rsvps.status FROM rsvps JOIN users ON rsvps.user_id = users.id WHERE rsvps.event_id = ?");
    $rsvp_stmt->execute([$event_id]);
    $rsvps = $rsvp_stmt->fetchAll();

    // Fetch existing reviews for this event
    $review_stmt = $pdo->prepare("SELECT r.rating, r.review_text, r.created_at, u.email FROM event_reviews r JOIN users u ON r.user_id = u.id WHERE r.event_id = ? ORDER BY r.created_at DESC");
    $review_stmt->execute([$event_id]);
    $reviews = $review_stmt->fetchAll();

    // Handle new review submission
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $rating = $_POST['rating'];
        $review_text = trim($_POST['review_text']);

        if (empty($review_text) || !is_numeric($rating) || $rating < 1 || $rating > 5) {
            $error_message = "Please provide a valid rating (1-5) and review text.";
        } else {
            try {
                // Insert the new review into the database
                $stmt = $pdo->prepare("INSERT INTO event_reviews (event_id, user_id, rating, review_text) VALUES (?, ?, ?, ?)");
                $stmt->execute([$event_id, $_SESSION['user_id'], $rating, $review_text]);

                // Redirect to the same page to show the new review
                header("Location: event_details.php?id=" . $event_id);
                exit();
            } catch (PDOException $e) {
                $error_message = "Error submitting review: " . $e->getMessage();
            }
        }
    }

} catch (PDOException $e) {
    echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
}
?>
<div class="profile-container">
<h2>Event Details: <?= htmlspecialchars($event['title']) ?></h2>

<p><strong>Sport Type:</strong> <?= htmlspecialchars($event['sport_type']) ?></p>
<p><strong>Date:</strong> <?= date("F j, Y", strtotime($event['event_date'])) ?></p>
<p><strong>Location:</strong> <?= htmlspecialchars($event['location']) ?></p>

<!-- If the logged-in user is the creator, they can see the 'Manage' button -->
<?php if ($is_creator): ?>
    <a href="manage_event.php?id=<?= $event['id'] ?>" class="btn-purple">Manage Event</a>
<?php endif; ?>

<!-- Display the list of people who RSVPed to the event -->
<h2>Who's attending:</h2>
<?php
// Fetch attendees who have RSVP'd with "accepted" status
$attendeeStmt = $pdo->prepare("SELECT u.full_name FROM invitations i JOIN users u ON i.invitee_id = u.id WHERE i.event_id = ? AND i.status = 'accepted'");
$attendeeStmt->execute([$event_id]);
$attendees = $attendeeStmt->fetchAll();

if (count($attendees) > 0) {
    echo "<ul>";
    foreach ($attendees as $attendee) {
        echo "<li>" . htmlspecialchars($attendee['full_name']) . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No one has RSVP'd yet.</p>";
}
?>

<!-- Review and Rating Section -->
<h2>Leave a Review</h2>

<?php if (isset($error_message)): ?>
    <p class="text-danger"><?= htmlspecialchars($error_message) ?></p>
<?php endif; ?>

<form method="POST" action="">
    <div class="form-group">
        <label for="rating">Rating (1-5 stars):</label>
        <input type="number" id="rating" name="rating" class="input-field" min="1" max="5" required>
    </div>
    <div class="form-group">
        <label for="review_text">Review:</label>
        <textarea id="review_text" name="review_text" class="input-field" rows="4" required></textarea>
    </div>
    <button type="submit" class="btn-purple">Submit Review</button>
</form>

<h2>Existing Reviews</h2>
<?php if (empty($reviews)): ?>
    <p>No reviews yet. Be the first to leave a review!</p>
<?php else: ?>
    <ul class="list-group">
        <?php foreach ($reviews as $review): ?>
            <li class="list-group-item">
                <strong><?= htmlspecialchars($review['email']) ?>:</strong>
                <div>Rating: <?= str_repeat('⭐', $review['rating']) ?></div>
                <div><?= htmlspecialchars($review['review_text']) ?></div>
                <small>Reviewed on: <?= htmlspecialchars($review['created_at']) ?></small>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php
$master_content = ob_get_clean();
include 'resources/view/layouts/sportconnnect/master.php';
?>


