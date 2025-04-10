<?php
session_start(); // Start the session

// Generate CSRF token if not set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error_message = ""; // To store error messages

// Process login form submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token mismatch.");
    }

    try {
        $pdo = new PDO("mysql:host=localhost;dbname=sportconnect", "root", "", [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$_POST['email']]);
        $user = $stmt->fetch();

        if ($user && password_verify($_POST['password'], $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            // ✅ Fixed path (remove 'public/')
            $_SESSION['profile_picture'] = $user['profile_picture'] ?? 'uploads/default-profile.jpg';
            $_SESSION['sports_interests'] = $user['sports_interests'];
            $_SESSION['overall_ranking'] = $user['overall_ranking'];

            // Redirect to profile page
            header("Location: profile.php");
            exit();
        } else {
            $error_message = "Invalid email or password.";
        }
    } catch (PDOException $e) {
        $error_message = "Something went wrong. Please try again later.";
    }
}

$pageTitle = "Login | SportConnect";
ob_start();
?>

<!-- Login Container -->
<div class="login-container">
    <h2 class="text-center">Login</h2>
    <?php if (!empty($error_message)) : ?>
        <p class="text-danger"><?php echo htmlspecialchars($error_message); ?></p>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" class="input-field" required>
        </div>
        <div>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" class="input-field" required>
        </div>
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <button type="submit" name="login" class="btn-purple">Login</button>
    </form>

    <p class="text-center">Don't have an account? <a href="register.php">Register here</a></p>
</div>

<?php
// Store output buffer into master content
$master_content = ob_get_clean();

// Include master layout
include 'resources/view/layouts/sportconnnect/master.php';
?>
