<?php
session_start();
require 'resources/database/db.php';

if (!isset($_SESSION['email']) || !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$current_user_id = $_SESSION['user_id'];
$user_id = $_GET['user_id'] ?? $current_user_id;

$error_message = "";
$success_message = "";
$user = null;

try {
    $pdo = new PDO("mysql:host=localhost;dbname=sportconnect", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // Fetch user profile
    $stmt = $pdo->prepare("SELECT id, full_name, email, profile_picture, sports_interests, location, bio FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        $error_message = "User not found.";
    }

    // Rankings
    $stmt = $pdo->prepare("SELECT sport, ranking, rating_count, updated_at FROM user_sports_rankings WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $rankings = $stmt->fetchAll();

    // Reviews
    $stmt = $pdo->prepare("SELECT rater_user_id, sport, rating, review, created_at FROM player_ratings WHERE rated_user_id = ?");
    $stmt->execute([$user_id]);
    $ratings = $stmt->fetchAll();

    // Check friendship
    $stmt = $pdo->prepare("SELECT 1 FROM friends 
        WHERE (user_1_id = :uid AND user_2_id = :rid) OR (user_1_id = :rid AND user_2_id = :uid)");
    $stmt->execute([":uid" => $current_user_id, ":rid" => $user_id]);
    $are_friends = $stmt->fetch() ? true : false;

    // Check pending
    $stmt = $pdo->prepare("SELECT 1 FROM friend_requests 
        WHERE sender_id = :uid AND receiver_id = :rid AND status = 'pending'");
    $stmt->execute([":uid" => $current_user_id, ":rid" => $user_id]);
    $is_pending = $stmt->fetch() ? true : false;

    // Handle friend request
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'send_friend_request') {
        if (!$are_friends && !$is_pending) {
            $stmt = $pdo->prepare("INSERT INTO friend_requests (sender_id, receiver_id, status) 
                VALUES (:sender, :receiver, 'pending')");
            $stmt->execute([
                ":sender" => $current_user_id,
                ":receiver" => $user_id
            ]);
            $success_message = "<p style='color:green; font-weight: bold;'>🎉 Friend request sent!</p>";
            $is_pending = true;
        } else {
            $success_message = "<p style='color:orange; font-weight: bold;'>⚠️ Already friends or request pending.</p>";
        }
    }

} catch (PDOException $e) {
    $error_message = "Error loading profile: " . $e->getMessage();
}

function getRankingDisplay($rank) {
    if ($rank >= 90) return "🏆 Elite";
    if ($rank >= 70) return "⭐ Pro";
    if ($rank >= 50) return "🎯 Intermediate";
    if ($rank >= 30) return "🌱 Beginner";
    return "🔰 Newbie";
}

$pageTitle = "User Profile | SportConnect";
ob_start();
?>
<div class="profile-container">
<h1><?= htmlspecialchars($user['full_name'] ?? 'User Profile') ?></h1>

<?php if ($error_message): ?>
    <p class="text-danger"><?= htmlspecialchars($error_message) ?></p>
<?php else: ?>

    <?= $success_message ?>

    <div>
        <img src="<?= htmlspecialchars($user['profile_picture'] ?? 'default.jpg') ?>" alt="Profile Picture" style="max-width:150px;">
    </div>
    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
    <p><strong>Sports Interests:</strong> <?= htmlspecialchars($user['sports_interests'] ?? 'Not specified') ?></p>
    <p><strong>Location:</strong> <?= htmlspecialchars($user['location'] ?? 'Unknown') ?></p>
    <p><strong>Bio:</strong> <?= htmlspecialchars($user['bio'] ?? 'No bio available') ?></p>

    <h3>Sports Rankings</h3>
    <?php if ($rankings): ?>
        <?php foreach ($rankings as $rank): ?>
            <div>
                <h4><?= htmlspecialchars($rank['sport']) ?> Ranking</h4>
                <p><strong>Ranking:</strong> <?= htmlspecialchars($rank['ranking']) ?></p>
                <p><strong>Rating Count:</strong> <?= htmlspecialchars($rank['rating_count']) ?></p>
                <p><strong>Last Updated:</strong> <?= htmlspecialchars($rank['updated_at']) ?></p>
                <p><strong>Ranking Badge:</strong> <?= getRankingDisplay($rank['ranking']) ?></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No rankings available for this user.</p>
    <?php endif; ?>

    <h3>User Ratings & Reviews</h3>
    <?php if ($ratings): ?>
        <?php foreach ($ratings as $rating): ?>
            <div class="rating-review">
                <p><strong>Sport:</strong> <?= htmlspecialchars($rating['sport']) ?></p>
                <p><strong>Rating:</strong> <?= htmlspecialchars($rating['rating']) ?> / 5</p>
                <p><strong>Review:</strong> <?= htmlspecialchars($rating['review']) ?></p>
                <p><strong>Reviewed by User ID:</strong> <?= htmlspecialchars($rating['rater_user_id']) ?></p>
                <p><strong>Reviewed on:</strong> <?= htmlspecialchars($rating['created_at']) ?></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No ratings or reviews available for this user.</p>
    <?php endif; ?>

    <!-- 🤝 Send Friend Request (if not self) -->
    <?php if ($current_user_id != $user_id): ?>
        <form method="POST" style="margin-top: 10px;">
            <input type="hidden" name="action" value="send_friend_request">
            <button type="submit" class="btn btn-success"
                <?= $are_friends ? 'disabled title="You are already friends!"' : '' ?>
                <?= $is_pending ? 'disabled title="Request already sent!"' : '' ?>>
                <?= $are_friends ? "✅ Already Friends" : ($is_pending ? "⏳ Request Pending" : "Send Friend Request") ?>
            </button>
        </form>
    <?php endif; ?>

<?php endif; ?>

<?php
$master_content = ob_get_clean();
include 'resources/view/layouts/sportconnnect/master.php';
?>



