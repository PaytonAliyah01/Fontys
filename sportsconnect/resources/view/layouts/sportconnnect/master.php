<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ Updated BASE_URL to match correct structure
define('BASE_URL', '/sportsconnect/');

$user_logged_in = isset($_SESSION['email']);
$full_name = $_SESSION['full_name'] ?? 'Guest';

// ✅ Adjusted path: No more /public/, just point to /uploads
$relative_path = $_SESSION['profile_picture'] ?? 'uploads/default-profile.jpg';
$server_path = $_SERVER['DOCUMENT_ROOT'] . '/sportsconnect/' . $relative_path;

// ✅ Set profile picture with fallback
$profile_picture = file_exists($server_path)
    ? BASE_URL . $relative_path
    : BASE_URL . 'uploads/default-profile.jpg';

$sports_interests = $_SESSION['sports_interests'] ?? 'No interests specified';
$overall_ranking = $_SESSION['overall_ranking'] ?? 'Unranked';

$pageTitle = $pageTitle ?? 'SportConnect';
$master_content = $master_content ?? '';
$master_styles = $master_styles ?? '';
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link href="<?= BASE_URL ?>public/css/style.css" rel="stylesheet" />
    <?php echo $master_styles; ?>
</head>
<body>

<header class="header_section">
    <div class="container-fluid">
        <nav class="navbar navbar-expand-lg custom_nav-container">
            <a class="navbar-brand" href="index.php" style="display: flex; align-items: center;">
                <img src="<?= BASE_URL ?>public/images/logo.png" style="width: 150px; height: auto; margin-right: 10px;">
                <span>SportsConnect</span>
            </a>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">Home</a>
                    </li>


                    <?php if ($user_logged_in): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'events.php' ? 'active' : ''; ?>" href="events.php">Events</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'search.php' ? 'active' : ''; ?>" href="search.php">Search</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'inbox.php' ? 'active' : ''; ?>" href="inbox.php">Inbox</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php">
                                <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile" style="width: 30px; height: 30px; border-radius: 50%; margin-right: 5px;">
                                <?php echo htmlspecialchars($full_name); ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Log Out</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'login.php' ? 'active' : ''; ?>" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'register.php' ? 'active' : ''; ?>" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
    </div>
</header>

<main class="container my-4">
    <?php echo $master_content; ?>
</main>

<footer class="footer_section">
    <div class="container">
        <p>&copy; <?php echo date('Y'); ?> SportConnect. All Rights Reserved.</p>
        <p><a href="terms.php">Terms & Conditions</a> | <a href="privacy.php">Privacy Policy</a></p>
    </div>
</footer>

</body>
</html>
