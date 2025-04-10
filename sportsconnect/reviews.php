<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Retrieve user session data (to personalize the reviews page, if needed)
$full_name = htmlspecialchars($_SESSION['full_name'] ?? 'Guest');
$email = htmlspecialchars($_SESSION['email'] ?? 'Unknown');
$profile_picture = !empty($_SESSION['profile_picture']) ? htmlspecialchars($_SESSION['profile_picture']) : 'public/images/default-profile.jpg';

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=localhost;dbname=sportconnect", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // Fetch reviews for the logged-in user from the player_ratings table
    $stmt = $pdo->prepare("SELECT * FROM player_ratings WHERE rated_user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $reviews = $stmt->fetchAll();  // Fetch all reviews for the user

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}

$pageTitle = "Player Reviews";
ob_start();
?>
<div class="reviews-container">
    <h2><?php echo $full_name; ?>'s Reviews</h2>
    <img src="<?php echo $profile_picture; ?>" alt="Profile Picture" style="width:100px; height:100px; border-radius:50%;">

    <!-- Display Reviews -->
    <?php if (count($reviews) > 0): ?>
        <div class="reviews-list">
            <?php foreach ($reviews as $review): ?>
                <div class="review-item">
                    <div class="review-header">
                        <strong>Sport:</strong> <?php echo htmlspecialchars($review['sport']); ?>
                        <br>
                        <strong>Rating:</strong> <?php echo htmlspecialchars($review['rating']); ?> / 5
                        <br>
                        <small>Reviewed by user ID: <?php echo htmlspecialchars($review['rater_user_id']); ?> on <?php echo $review['created_at']; ?></small>
                    </div>

                    <?php if (!empty($review['review'])): ?>
                        <div class="review-body">
                            <p><strong>Review:</strong> <?php echo htmlspecialchars($review['review']); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No reviews found for this player.</p>
    <?php endif; ?>

    <!-- Buttons for actions -->
    <div class="button-group">
        <a href="rate_player.php" class="btn-outline-purple">Rate a Player</a>
        <a href="invitations.php" class="btn-outline-purple">View Invitations</a>
        <a href="profile.php" class="btn-outline-purple"">Go to Profile</a>
    </div>
</div>

<?php
$master_content = ob_get_clean();
include 'resources/view/layouts/sportconnnect/master.php';
?>

