<?php
session_start();
require 'resources/database/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'], $_POST['message'], $_POST['conversation_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized or missing data']);
    exit();
}

$message = trim($_POST['message']);
$conversation_id = (int)$_POST['conversation_id'];

if ($message === '') {
    echo json_encode(['success' => false, 'error' => 'Empty message']);
    exit();
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=sportconnect", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $stmt = $pdo->prepare("INSERT INTO messages (conversation_id, sender_id, message, timestamp)
                           VALUES (?, ?, ?, NOW())");
    $stmt->execute([$conversation_id, $_SESSION['user_id'], $message]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'DB error: ' . $e->getMessage()]);
}
?>

