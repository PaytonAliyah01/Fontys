<?php
session_start();
require 'resources/database/db.php';

if (!isset($_SESSION['email']) || !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error_message = "";
$success_message = "";
$friends = [];
$similar_users = [];

try {
    // Connect to the database
    $pdo = new PDO("mysql:host=localhost;dbname=sportconnect", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // Fetch all friends of the logged-in user
    $stmt = $pdo->prepare("
        SELECT u.id, u.email, u.full_name
        FROM friends f
        JOIN users u ON (u.id = f.user_1_id AND f.user_2_id = :current_user)
                     OR (u.id = f.user_2_id AND f.user_1_id = :current_user)
        WHERE u.id != :current_user
    ");
    $stmt->execute([':current_user' => $_SESSION['user_id']]);
    $friends = $stmt->fetchAll();

    // Fetch similar users when a friend is selected
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['recipient_id'])) {
        $recipient_id = $_POST['recipient_id'];

        // Fetch the friend's full name
        $stmt = $pdo->prepare("SELECT full_name FROM users WHERE id = ?");
        $stmt->execute([$recipient_id]);
        $recipient = $stmt->fetch();

        if ($recipient) {
            $friend_name = $recipient['full_name'];

            // Find users with a similar name (e.g., users with similar first names or full names)
            $stmt = $pdo->prepare("SELECT id, full_name FROM users WHERE full_name LIKE ? AND id != ?");
            $stmt->execute([ "%" . $friend_name . "%", $_SESSION['user_id']]);
            $similar_users = $stmt->fetchAll();
        }
    }

} catch (PDOException $e) {
    $error_message = "Error loading friends: " . $e->getMessage();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $recipient_id = $_POST['recipient_id'] ?? '';

    if (empty($recipient_id)) {
        $error_message = "Please select a friend.";
    } else {
        try {
            // Check if selected user exists
            $stmt = $pdo->prepare("SELECT id, email FROM users WHERE id = ?");
            $stmt->execute([$recipient_id]);
            $recipient = $stmt->fetch();

            if (!$recipient) {
                $error_message = "Selected user does not exist.";
            } else {
                // Double check they are friends (for safety)
                $stmt = $pdo->prepare("SELECT * FROM friends 
                                       WHERE (user_1_id = :current_user AND user_2_id = :friend_id) 
                                          OR (user_1_id = :friend_id AND user_2_id = :current_user)");
                $stmt->execute([
                    ':current_user' => $_SESSION['user_id'],
                    ':friend_id' => $recipient_id
                ]);
                $is_friend = $stmt->fetch();

                if (!$is_friend) {
                    $error_message = "You can only message users you are friends with.";
                } else {
                    // Check if conversation exists
                    $stmt = $pdo->prepare("SELECT * FROM conversations 
                                           WHERE (user_1_id = ? AND user_2_id = ?) 
                                              OR (user_1_id = ? AND user_2_id = ?)");
                    $stmt->execute([$_SESSION['user_id'], $recipient_id, $recipient_id, $_SESSION['user_id']]);
                    $conversation = $stmt->fetch();

                    if ($conversation) {
                        header("Location: message.php?conversation_id=" . $conversation['conversation_id']);
                        exit();
                    } else {
                        // Create new conversation
                        $stmt = $pdo->prepare("INSERT INTO conversations (user_1_id, user_2_id) VALUES (?, ?)");
                        $stmt->execute([$_SESSION['user_id'], $recipient_id]);
                        $conversation_id = $pdo->lastInsertId();

                        header("Location: message.php?conversation_id=" . $conversation_id);
                        exit();
                    }
                }
            }
        } catch (PDOException $e) {
            $error_message = "Error starting conversation: " . $e->getMessage();
        }
    }
}

$pageTitle = "Start Conversation | SportConnect";
ob_start();
?>
<div class="profile-container">
<h1>Start a New Conversation</h1>

<?php if ($error_message): ?>
    <p class="text-danger"><?= htmlspecialchars($error_message) ?></p>
<?php endif; ?>

<form method="POST" action="start_conversation.php">
    <div>
        <label for="recipient_id">Select a Friend:</label>
        <select id="recipient_id" name="recipient_id" class="form-control" required>
            <option value="">-- Choose a friend --</option>
            <?php foreach ($friends as $friend): ?>
                <option value="<?= htmlspecialchars($friend['id']) ?>">
                    <?= htmlspecialchars($friend['full_name'] ?? $friend['email']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="btn-purple">Start Conversation</button>
</form>

<?php if (!empty($similar_users)): ?>
    <h3>Users with similar names:</h3>
    <ul class="list-group">
        <?php foreach ($similar_users as $user): ?>
            <li class="list-group-item">
                <?= htmlspecialchars($user['full_name']) ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php
$master_content = ob_get_clean();
include 'resources/view/layouts/sportconnnect/master.php';
?>



