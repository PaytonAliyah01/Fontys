<?php
session_start();
require 'resources/database/db.php';

if (!isset($_SESSION['email']) || !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error_message = "";
$messages = [];
$conversation_data = [];

// Get conversation_id
if (isset($_GET['conversation_id'])) {
    $conversation_id = (int)$_GET['conversation_id'];
} else {
    die("No conversation specified.");
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=sportconnect", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // Fetch messages
    $stmt = $pdo->prepare("SELECT m.message, m.sender_id, m.timestamp, u.email AS sender_email, u.profile_picture
                           FROM messages m
                           JOIN users u ON m.sender_id = u.id
                           WHERE m.conversation_id = :conversation_id
                           ORDER BY m.timestamp ASC");
    $stmt->execute([':conversation_id' => $conversation_id]);
    $messages = $stmt->fetchAll();

    // Get other user's email
    $stmt = $pdo->prepare("SELECT IF(c.user_1_id = :current_user_id, u2.full_name, u1.full_name) AS other_user_full_name
                           FROM conversations c
                           JOIN users u1 ON c.user_1_id = u1.id
                           JOIN users u2 ON c.user_2_id = u2.id
                           WHERE c.conversation_id = :conversation_id");
    $stmt->execute([
        ':current_user_id' => $_SESSION['user_id'],
        ':conversation_id' => $conversation_id
    ]);
    $conversation_data = $stmt->fetch();

} catch (PDOException $e) {
    $error_message = "Error fetching messages: " . $e->getMessage();
}

$pageTitle = "Conversation | SportConnect";
ob_start();
?>

<div class="group-chat-container">
    <h1>Conversation with <?= htmlspecialchars($conversation_data['other_user_full_name']) ?></h1>

    <?php if ($error_message): ?>
        <p class="text-danger"><?= htmlspecialchars($error_message) ?></p>
    <?php endif; ?>
<div class="chat-box">
    <!-- Message list -->
    <div id="message-container" class="messages">
        <?php if (empty($messages)): ?>
            <p>No messages in this conversation.</p>
        <?php else: ?>
            <?php foreach ($messages as $message): ?>
                <div class="message-bubble <?= $message['sender_id'] == $_SESSION['user_id'] ? 'me' : 'them' ?>">
                    <img src="<?= htmlspecialchars($message['profile_picture'] ?? 'default-profile.png') ?>" class="avatar" alt="Profile Picture">
                    <div>
                        <p class="text"><?= htmlspecialchars($message['message']) ?></p>
                        <p class="timestamp"><?= htmlspecialchars($message['timestamp']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

    <!-- Message input -->
    <div class="send-message">
        <form id="send-message-form">
            <textarea name="message" id="message" rows="3" required></textarea>
            <button type="submit">Send</button>
        </form>
    </div>
</div>

<!-- AJAX logic -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $('#send-message-form').on('submit', function(e) {
        e.preventDefault();
        const message = $('#message').val();
        if (message.trim() === '') return;

        $.post('send_message_ajax.php', {
            message: message,
            conversation_id: <?= json_encode($conversation_id) ?>
        }, function(response) {
            if (response.success) {
                $('#message').val('');
                $('#message-container').append(`
                    <div class="message-bubble me">
                        <img src="<?= $_SESSION['profile_picture'] ?? 'default-profile.png' ?>" class="avatar" alt="Me">
                        <div>
                            <p class="text">${$('<div>').text(message).html()}</p>
                            <p class="timestamp">just now</p>
                        </div>
                    </div>
                `);
                $('#message-container').scrollTop($('#message-container')[0].scrollHeight);
            } else {
                alert(response.error);
            }
        }, 'json');
    });
</script>

<?php
$master_content = ob_get_clean();
include 'resources/view/layouts/sportconnnect/master.php';
?>




