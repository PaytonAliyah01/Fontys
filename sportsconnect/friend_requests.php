<?php
session_start();
require 'resources/database/db.php';

// Redirect to login page if not logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Get the logged-in user ID
$user_id = $_SESSION['user_id'];

// Send Friend Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_friend_request'])) {
    $receiver_id = $_POST['receiver_id'];
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=sportconnect", "root", "", [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        $stmt = $pdo->prepare("INSERT INTO friend_requests (sender_id, receiver_id, status) 
                               VALUES (?, ?, 'pending')");
        $stmt->execute([$user_id, $receiver_id]);
        echo "<p class='alert alert-success'>Friend request sent.</p>";
    } catch (PDOException $e) {
        echo "<p class='alert alert-danger'>Error sending friend request: " . $e->getMessage() . "</p>";
    }
}

// Accept Friend Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accept_request'])) {
    $request_id = $_POST['request_id'];

    try {
        $pdo = new PDO("mysql:host=localhost;dbname=sportconnect", "root", "", [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        // Update the status of the friend request to 'accepted'
        $stmt = $pdo->prepare("UPDATE friend_requests SET status = 'accepted' WHERE id = ?");
        $stmt->execute([$request_id]);

        // Add friendship to the friends table
        $stmt = $pdo->prepare("INSERT INTO friends (user_1_id, user_2_id) 
                               SELECT sender_id, receiver_id FROM friend_requests WHERE id = ?");
        $stmt->execute([$request_id]);

        echo "<p class='alert alert-success'>Friend request accepted.</p>";
    } catch (PDOException $e) {
        echo "<p class='alert alert-danger'>Error accepting friend request: " . $e->getMessage() . "</p>";
    }
}

// Decline Friend Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['decline_request'])) {
    $request_id = $_POST['request_id'];

    try {
        $pdo = new PDO("mysql:host=localhost;dbname=sportconnect", "root", "", [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        // Update the status of the friend request to 'declined'
        $stmt = $pdo->prepare("UPDATE friend_requests SET status = 'declined' WHERE id = ?");
        $stmt->execute([$request_id]);
        echo "<p class='alert alert-info'>Friend request declined.</p>";
    } catch (PDOException $e) {
        echo "<p class='alert alert-danger'>Error declining friend request: " . $e->getMessage() . "</p>";
    }
}

// Fetch Friend Requests for the logged-in user
$pdo = new PDO("mysql:host=localhost;dbname=sportconnect", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);
$stmt = $pdo->prepare("SELECT fr.id, u.email AS sender_email 
                       FROM friend_requests fr
                       JOIN users u ON fr.sender_id = u.id
                       WHERE fr.receiver_id = ? AND fr.status = 'pending'");
$stmt->execute([$user_id]);
$friend_requests = $stmt->fetchAll();

// Fetch All Users (You may want to optimize this query if you have many users)
$users_stmt = $pdo->prepare("SELECT id, email FROM users WHERE id != ?");
$users_stmt->execute([$user_id]);
$users = $users_stmt->fetchAll();

$pageTitle = "Friend Requests | SportConnect";
ob_start();
?>
<div class="profile-container">
<h2>Friend Requests</h2>
<!-- Display Friend Requests -->
<h2>Pending Friend Requests</h2>
<?php if (empty($friend_requests)): ?>
    <p>No pending friend requests.</p>
<?php else: ?>
    <ul class="list-group">
        <?php foreach ($friend_requests as $request): ?>
            <li class="invitation-list">
                <strong><?= htmlspecialchars($request['sender_email']) ?></strong> sent you a friend request.
                <form method="POST" action="" style="display:inline;">
                    <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                    <button type="submit" name="accept_request" class="btn-outline-purple">Accept</button>
                </form>
                <form method="POST" action="" style="display:inline;">
                    <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                    <button type="submit" name="decline_request" class="btn-outline-purple">Decline</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php
$master_content = ob_get_clean();
include 'resources/view/layouts/sportconnnect/master.php';
?>

