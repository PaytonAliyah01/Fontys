<?php
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$pageTitle = "Set Goal | SportConnect";
ob_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $goal_title = htmlspecialchars($_POST['goal_title']);
    $target_value = (int) $_POST['target_value'];
    $user_id = $_SESSION['user_id'];

    // Validate input
    if (empty($goal_title) || $target_value <= 0) {
        $error_message = "Please provide a valid goal title and target value.";
    } else {
        // Connect to the database
        try {
            $pdo = new PDO("mysql:host=localhost;dbname=sportconnect", "root", "", [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);

            // Insert goal into the database
            $stmt = $pdo->prepare("INSERT INTO goals (user_id, goal_title, target_value, current_value, created_at, updated_at) VALUES (?, ?, ?, 0, NOW(), NOW())");
            $stmt->execute([$user_id, $goal_title, $target_value]);

            $success_message = "Goal successfully set!";
        } catch (PDOException $e) {
            $error_message = "Error: " . $e->getMessage();
        }
    }
}

?>

<!-- Goal Setting Form -->
<div class="profile-container">
    <h1>Set a New Goal</h1>
    <?php if (isset($success_message)): ?>
        <p class="success"><?php echo $success_message; ?></p>
    <?php elseif (isset($error_message)): ?>
        <p class="error"><?php echo $error_message; ?></p>
    <?php endif; ?>

    <form method="POST">
        <label for="goal_title">Goal Title:</label>
        <input type="text" id="goal_title" name="goal_title" required>

        <label for="target_value">Target Value (e.g., distance, points):</label>
        <input type="number" id="target_value" name="target_value" required>

        <button type="submit">Set Goal</button>
    </form>
</div>

<?php
$master_content = ob_get_clean();
include 'resources/view/layouts/sportconnnect/master.php';
?>

