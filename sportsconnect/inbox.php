<?php
session_start();
require 'resources/database/db.php';

// Redirect to login page if not logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$error_message = "";
$conversations = [];
$group_chats = [];

try {
    $pdo = new PDO("mysql:host=localhost;dbname=sportconnect", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // Fetch private conversations
    $stmt = $pdo->prepare("SELECT c.conversation_id, 
                                  IF(c.user_1_id = ?, u2.full_name, u1.full_name) AS other_user_full_name
                           FROM conversations c
                           JOIN users u1 ON c.user_1_id = u1.id
                           JOIN users u2 ON c.user_2_id = u2.id
                           WHERE c.user_1_id = ? OR c.user_2_id = ?
                           ORDER BY c.conversation_id DESC");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
    $conversations = $stmt->fetchAll();

    // Fetch group chats the user is a member of
    $stmt = $pdo->prepare("SELECT cg.group_id, cg.group_name 
                           FROM chat_groups cg
                           JOIN group_members gm ON cg.group_id = gm.group_id
                           WHERE gm.user_id = ?
                           ORDER BY cg.created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $group_chats = $stmt->fetchAll();

} catch (PDOException $e) {
    $error_message = "Error loading chats: " . $e->getMessage();
}

$pageTitle = "Inbox | SportConnect";
ob_start();
?>

<!-- ✅ Inbox container wrapper -->
<div class="inbox">

    <h1>Inbox</h1>

    <?php if ($error_message): ?>
        <p class="text-danger"><?= htmlspecialchars($error_message) ?></p>
    <?php endif; ?>

    <!-- Actions -->
    <a href="start_conversation.php" class="btn-purple">Start a New Conversation</a>
    <a href="create_group.php" class="btn-purple">Create a New Group</a>

    <!-- Private Chats -->
    <h3 class="mt-4">Private Conversations</h3>
    <?php if (empty($conversations)): ?>
        <p>No private conversations found.</p>
    <?php else: ?>
        <ul class="list-group mb-4">
            <?php foreach ($conversations as $conversation): ?>
                <li class="list-group-item">
                    <a href="message.php?conversation_id=<?= htmlspecialchars($conversation['conversation_id']) ?>">
                        Chat with <?= htmlspecialchars($conversation['other_user_full_name']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <!-- Group Chats -->
    <h3>Group Chats</h3>
    <?php if (empty($group_chats)): ?>
        <p>You are not in any groups.</p>
    <?php else: ?>
        <ul class="list-group">
            <?php foreach ($group_chats as $group): ?>
                <li class="list-group-item">
                    <a href="group_chat.php?group_id=<?= htmlspecialchars($group['group_id']) ?>">
                        <?= htmlspecialchars($group['group_name']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

</div> <!-- End of .inbox -->

<?php
$master_content = ob_get_clean();
include 'resources/view/layouts/sportconnnect/master.php';
?>

