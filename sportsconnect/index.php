<?php
session_start();
$pageTitle = "Home | SportConnect";
ob_start();
?>
<div class="profile-container">
<!-- Hero Section with Background Image -->
<section class="hero">
        <h1>Welcome to SportConnect!</h1>
        <p>Your ultimate social network for sports events, connections, and community building.</p>
        <?php if (isset($_SESSION['email'])): ?>
            <!-- If user is logged in, show the navigation links -->
            <p>Explore our features:</p>
            <a href="profile.php" class="btn-purple">Your Profile</a>
            <a href="events.php" class="btn-purple">Events</a>
            <a href="inbox.php" class="btn-purple">inbox</a>
        <?php else: ?>
            <!-- If user is not logged in, show signup/login options -->
            <a href="register.php" class="btn-purple">Join Now</a>
        <?php endif; ?>
    </v>
</section>

<!-- Featured Sections with Icons and Hover Effects -->
<section class="features">
    <div class="feature-item">
        <i class="fas fa-users fa-3x"></i> <!-- FontAwesome Icon -->
        <h2>Connect with Fellow Sports Enthusiasts</h2>
        <p>Meet people who share your passion for sports. Whether you're into soccer, basketball, or tennis, SportConnect has a community for you.</p>
    </div>
    <div class="feature-item">
        <i class="fas fa-calendar-check fa-3x"></i> <!-- FontAwesome Icon -->
        <h2>Discover Local Sports Events</h2>
        <p>Find and join events happening near you. From casual games to tournaments, SportConnect helps you stay connected to your local sports scene.</p>
    </div>
    <div class="feature-item">
        <i class="fas fa-chart-line fa-3x"></i> <!-- FontAwesome Icon -->
        <h2>Track Your Progress</h2>
        <p>Keep track of your performance with our ranking and rating system. Set goals, measure progress, and challenge others in your favorite sports!</p>
    </div>
</section>

<!-- Call to Action Section with Animation -->
<section class="cta-section">
    <h2>Ready to get started?</h2>
    <p>Join SportConnect today and unlock a world of sports connections and events.</p>
    <div class="cta-buttons">
        <?php if (!isset($_SESSION['email'])): ?>
            <!-- If user is not logged in, show signup/login options -->
            <a href="register.php" class="btn-purple">Sign Up</a>
            <a href="login.php" class="btn-purple">Log In</a>
        <?php endif; ?>
    </div>
</section>

<?php
$master_content = ob_get_clean();
include 'resources/view/layouts/sportconnnect/master.php';
?>


