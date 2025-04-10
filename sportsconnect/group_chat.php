<?php
session_start();
require 'resources/database/db.php';

if (!isset($_SESSION['email']) || !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error_message = "";
$group_id = $_GET['group_id'] ?? null;
$group = null;
$members = [];
$messages = [];

if (!$group_id) {
    $error_message = "No group selected.";
} else {
    try {
        // Connect to the database
        $pdo = new PDO("mysql:host=localhost;dbname=sportconnect", "root", "", [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

        // Fetch group details
        $stmt = $pdo->prepare("SELECT * FROM chat_groups WHERE group_id = ?");
        $stmt->execute([$group_id]);
        $group = $stmt->fetch();

        if (!$group) {
            $error_message = "Group not found.";
        } else {
            // Fetch group members
            $stmt = $pdo->prepare("SELECT u.id, u.full_name FROM group_members gm JOIN users u ON gm.user_id = u.id WHERE gm.group_id = ?");
            $stmt->execute([$group_id]);
            $members = $stmt->fetchAll();

            // Fetch group messages
            $stmt = $pdo->prepare("SELECT gm.message, gm.timestamp, u.full_name, gm.sender_id
                                   FROM group_messages gm 
                                   JOIN users u ON gm.sender_id = u.id 
                                   WHERE gm.group_id = ? 
                                   ORDER BY gm.timestamp DESC");
            $stmt->execute([$group_id]);
            $messages = $stmt->fetchAll();
        }
    } catch (PDOException $e) {
        $error_message = "Error loading group data: " . $e->getMessage();
    }

    // Handle new message posting
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['message'])) {
        $message = trim($_POST['message']);
        if (!empty($message)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO group_messages (group_id, sender_id, message) VALUES (?, ?, ?)");
                $stmt->execute([$group_id, $_SESSION['user_id'], $message]);

                // Redirect to the same page to show the new message
                header("Location: group_chat.php?group_id=" . $group_id);
                exit();
            } catch (PDOException $e) {
                $error_message = "Error sending message: " . $e->getMessage();
            }
        } else {
            $error_message = "Message cannot be empty.";
        }
    }
}

$pageTitle = "Group Chat | SportConnect";
ob_start();
?>

<div class="group-chat-container">
    <h1><?= htmlspecialchars($group['name'] ?? 'Group') ?> - Chat</h1>

    <?php if ($error_message): ?>
        <p class="text-danger"><?= htmlspecialchars($error_message) ?></p>
    <?php endif; ?>

    <div class="group-members">
        <h3>Group Members</h3>
        <ul class="list-group">
            <?php foreach ($members as $member): ?>
                <li class="list-group-item"><?= htmlspecialchars($member['full_name']) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="chat-messages">
        <h3>Messages</h3>
        <div class="chat-box" >
            <?php foreach ($messages as $message): ?>
                <div class="message-bubble <?= ($_SESSION['user_id'] == $message['sender_id']) ? 'me' : 'them' ?>">
                    <div class="text">
                        <strong><?= htmlspecialchars($message['full_name']) ?>:</strong>
                        <p><?= htmlspecialchars($message['message']) ?></p>
                        <small class="timestamp"><?= htmlspecialchars($message['timestamp']) ?></small>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <form method="POST" action="group_chat.php?group_id=<?= htmlspecialchars($group_id) ?>">
        <div class="form-group">
            <label for="message">Type a message:</label>
            <textarea id="message" name="message" class="input-field" rows="3" required></textarea>
        </div>
        <button type="submit" class="btn-purple">Send Message</button>
    </form>
</div>

<?php
$master_content = ob_get_clean();
include 'resources/view/layouts/sportconnnect/master.php';
?>

