<?php
session_start();
require 'resources/database/db.php';

if (!isset($_SESSION['email']) || !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error_message = "";
$users = [];
$search_term = $_GET['search'] ?? '';

if ($_SERVER["REQUEST_METHOD"] === "GET" && !empty($search_term)) {
    try {
        // Connect to the database
        $pdo = new PDO("mysql:host=localhost;dbname=sportconnect", "root", "", [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

        // Search for users by name or email using wildcards for partial matches
        $stmt = $pdo->prepare("SELECT id, full_name, email FROM users WHERE full_name LIKE ? OR email LIKE ?");
        $stmt->execute(['%' . $search_term . '%', '%' . $search_term . '%']);
        $users = $stmt->fetchAll(); // Fetch matching users

    } catch (PDOException $e) {
        $error_message = "Error searching for users: " . $e->getMessage();
        // Log error message for debugging
        error_log($e->getMessage());
    }
}

$pageTitle = "Search Users | SportConnect";
ob_start();
?>

<div class="profile-container">
<h1>Search Users</h1>

<form method="GET" action="search.php">
    <div>
        <label for="search">Search by name or email:</label>
        <input type="text" id="search" name="search" class="form-control" value="<?= htmlspecialchars($search_term) ?>" required>
    </div>
    <button type="submit" class="btn-purple">Search</button>
</form>

<?php if ($error_message): ?>
    <p class="text-danger"><?= htmlspecialchars($error_message) ?></p>
<?php endif; ?>

<?php if (empty($users)): ?>
    <p>No users found.</p>
<?php else: ?>
    <h3>Results:</h3>
    <ul class="list-group">
        <?php foreach ($users as $user): ?>
            <li class="list-group-item">
                <a href="view_profile.php?user_id=<?= htmlspecialchars($user['id']) ?>">
                    <?= htmlspecialchars($user['full_name']) ?> (<?= htmlspecialchars($user['email']) ?>)
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php
$master_content = ob_get_clean();
include 'resources/view/layouts/sportconnnect/master.php';
?>


