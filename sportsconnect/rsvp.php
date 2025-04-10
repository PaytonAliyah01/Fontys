<?php
session_start();
require 'resources/database/db.php';

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$pageTitle = "RSVP | SportConnect";
ob_start();
$invitation_id = $_GET['invitation_id'] ?? null;

if (!$invitation_id) {
    echo "<p style='color:red;'>Invalid invitation.</p>";
} else {
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=sportconnect", "root", "", [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

        // Fetch invitation info
        $stmt = $pdo->prepare("SELECT i.*, e.title AS event_title, e.event_date, e.location, e.sport_type FROM invitations i JOIN events e ON i.event_id = e.id WHERE i.id = ? AND i.invitee_id = ?");
        $stmt->execute([$invitation_id, $_SESSION['user_id']]);
        $invitation = $stmt->fetch();

        if (!$invitation) {
            echo "<p style='color:red;'>Invitation not found.</p>";
        } else {
            // Handle RSVP submission
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
                $action = $_POST['action'];
                if ($action === 'accepted' || $action === 'declined') {
                    // Update the invitation status in the invitations table
                    $update = $pdo->prepare("UPDATE invitations SET status = ? WHERE id = ?");
                    $update->execute([$action, $invitation_id]);

                    // Store the RSVP in the rsvp table (if it doesn't already exist)
                    $stmt = $pdo->prepare("SELECT * FROM rsvps WHERE event_id = ? AND user_id = ?");
                    $stmt->execute([$invitation_id, $_SESSION['user_id']]);
                    $existingRsvp = $stmt->fetch();

                    if (!$existingRsvp) {
                        // Insert new RSVP
                        $insert = $pdo->prepare("INSERT INTO rsvps (event_id, user_id, status) VALUES (?, ?, ?)");
                        $insert->execute([$invitation_id, $_SESSION['user_id'], $action]);
                    } else {
                        // Update existing RSVP if it already exists
                        $updateRsvp = $pdo->prepare("UPDATE rsvps SET status = ? WHERE event_id = ? AND user_id = ?");
                        $updateRsvp->execute([$action, $invitation_id, $_SESSION['user_id']]);
                    }

                    // Refresh invitation data
                    header("Location: rsvp.php?invitation_id=" . $invitation_id);
                    exit();
                }
            }

            // Profile Container
            echo '<div class="profile-container">';
            echo "<h2>You're invited to: " . htmlspecialchars($invitation['event_title']) . "</h2>";
            echo "<p><strong>Date:</strong> " . date("F j, Y", strtotime($invitation['event_date'])) . "</p>";
            echo "<p><strong>Location:</strong> " . htmlspecialchars($invitation['location']) . "</p>";
            echo "<p><strong>Sport:</strong> " . htmlspecialchars($invitation['sport_type']) . "</p>";

            // Status display logic
            if ($invitation['status'] === 'pending') {
                echo "<p><strong>Status:</strong> <span class='badge badge-warning'>Pending</span></p>";
            } else {
                echo "<p><strong>Status:</strong> <span class='badge badge-" .
                    ($invitation['status'] === 'accepted' ? 'success' : 'danger') .
                    "'>" . ucfirst($invitation['status']) . "</span></p>";
            }

            // Show RSVP buttons if status is pending
            if ($invitation['status'] === 'pending') {
                ?>
                <form method="POST">
                    <button name="action" value="accepted" class="btn btn-success">Accept</button>
                    <button name="action" value="declined" class="btn btn-danger">Decline</button>
                </form>
                <?php
            }

            // Show attendees
            echo "<h2>Who's attending:</h2>";
            $attendeeStmt = $pdo->prepare("SELECT u.full_name FROM invitations i JOIN users u ON i.invitee_id = u.id WHERE i.event_id = ? AND i.status = 'accepted'");
            $attendeeStmt->execute([$invitation['event_id']]);
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
            echo '</div>'; // Close profile-container div
        }
    } catch (PDOException $e) {
        echo "<p style='color:red;'>Database error: " . $e->getMessage() . "</p>";
    }
}

$master_content = ob_get_clean();
include 'resources/view/layouts/sportconnnect/master.php';
?>

