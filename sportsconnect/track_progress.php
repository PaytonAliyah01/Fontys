<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$pageTitle = "Track Progress | SportConnect";
ob_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $goal_id = (int) $_POST['goal_id'];
    $value = (int) $_POST['value'];

    if ($value <= 0) {
        $error_message = "Please provide a valid progress value.";
    } else {
        try {
            $pdo = new PDO("mysql:host=localhost;dbname=sportconnect", "root", "", [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);

            // Insert progress into the database
            $stmt = $pdo->prepare("INSERT INTO goal_progress (goal_id, value, created_at) VALUES (?, ?, NOW())");
            $stmt->execute([$goal_id, $value]);

            // Update current value of the goal
            $stmt = $pdo->prepare("UPDATE goals SET current_value = current_value + ? WHERE id = ?");
            $stmt->execute([$value, $goal_id]);

            $success_message = "Progress successfully updated!";
        } catch (PDOException $e) {
            $error_message = "Error: " . $e->getMessage();
        }
    }
}

// Fetch goals for the user to track progress
try {
    $pdo = new PDO("mysql:host=localhost;dbname=sportconnect", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    $stmt = $pdo->prepare("SELECT id, goal_title, target_value, current_value FROM goals WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $goals = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = "Error: " . $e->getMessage();
}

?>

<!-- Progress Tracking Form -->
<div class="progress-container">
    <h1>Track Your Progress</h1>
    <?php if (isset($success_message)): ?>
        <p class="success"><?php echo $success_message; ?></p>
    <?php elseif (isset($error_message)): ?>
        <p class="error"><?php echo $error_message; ?></p>
    <?php endif; ?>

    <form method="POST">
        <label for="goal_id">Select Goal:</label>
        <select id="goal_id" name="goal_id">
            <?php foreach ($goals as $goal): ?>
                <option value="<?php echo $goal['id']; ?>"><?php echo htmlspecialchars($goal['goal_title']); ?> (Current: <?php echo $goal['current_value']; ?> / <?php echo $goal['target_value']; ?>)</option>
            <?php endforeach; ?>
        </select>

        <label for="value">Progress Value:</label>
        <input type="number" id="value" name="value" required>

        <button type="submit">Update Progress</button>
    </form>
</div>

<?php
$master_content = ob_get_clean();
include 'resources/view/layouts/sportconnnect/master.php';
?>

