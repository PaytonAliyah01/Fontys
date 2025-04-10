<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Retrieve user session data
$full_name = htmlspecialchars($_SESSION['full_name'] ?? 'Guest');
$email = htmlspecialchars($_SESSION['email'] ?? 'Unknown');
$profile_picture = !empty($_SESSION['profile_picture']) ? htmlspecialchars($_SESSION['profile_picture']) : 'public/images/default-profile.jpg';
$sports_interests_raw = $_SESSION['sports_interests'] ?? '';
$location = htmlspecialchars($_SESSION['location'] ?? 'Unknown');
$bio = htmlspecialchars($_SESSION['bio'] ?? 'No bio available');

// Convert sports interests string to array
$sports_interests_list = array_map('trim', explode(',', $sports_interests_raw));

// Function to display ranking as stars or a badge
function getRankingDisplay($rank) {
    if ($rank >= 90) return "🏆 Elite";
    if ($rank >= 70) return "⭐ Pro";
    if ($rank >= 50) return "🎯 Intermediate";
    if ($rank >= 30) return "🌱 Beginner";
    return "🔰 Newbie";
}

// Connect to the database to fetch the user's rankings for various sports and goal data
try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=localhost;dbname=sportconnect", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // Fetch rankings from user_sports_rankings table
    $stmt = $pdo->prepare("SELECT sport, ranking, rating_count, updated_at
                           FROM user_sports_rankings
                           WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $rankings = $stmt->fetchAll();  // Fetch all rankings for the user

    // Fetch goals and progress data
    $stmt_goals = $pdo->prepare("SELECT g.id, g.goal_title, g.target_value, g.current_value, 
                                        gp.value AS progress_value, gp.created_at AS progress_date
                                 FROM goals g
                                 LEFT JOIN goal_progress gp ON g.id = gp.goal_id
                                 WHERE g.user_id = ?");
    $stmt_goals->execute([$_SESSION['user_id']]);
    $goals = $stmt_goals->fetchAll();  // Fetch all goals and their progress

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}

$pageTitle = "Profile";
ob_start();
?>
<div class="profile-container">
    <h2><?php echo $full_name; ?>'s Profile</h2>
    <img src="<?php echo $profile_picture; ?>" alt="Profile Picture" style="width:100px; height:100px; border-radius:50%;">
    <p><strong>Email:</strong> <?php echo $email; ?></p>

    <!-- Updated Sports Interests Display -->
    <p><strong>Sports Interests:</strong>
        <?php if (!empty($sports_interests_list[0])): ?>
    <ul>
        <?php foreach ($sports_interests_list as $interest): ?>
            <li><?php echo htmlspecialchars($interest); ?></li>
        <?php endforeach; ?>
    </ul>
    <?php else: ?>
        Not specified
    <?php endif; ?>
    </p>

    <p><strong>Location:</strong> <?php echo $location; ?></p>
    <p><strong>Bio:</strong> <?php echo $bio; ?></p>

    <!-- Display rankings for each sport -->
    <h3>Sports Rankings</h3>
    <?php if (count($rankings) > 0): ?>
        <?php foreach ($rankings as $rank): ?>
            <div>
                <h4><?php echo htmlspecialchars($rank['sport']); ?> Ranking</h4>
                <p><strong>Ranking:</strong> <?php echo htmlspecialchars($rank['ranking']); ?></p>
                <p><strong>Rating Count:</strong> <?php echo htmlspecialchars($rank['rating_count']); ?></p>
                <p><strong>Last Updated:</strong> <?php echo htmlspecialchars($rank['updated_at']); ?></p>
                <p><strong>Ranking Badge:</strong> <?php echo getRankingDisplay($rank['ranking']); ?></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No rankings available for any sport.</p>
    <?php endif; ?>

    <!-- Analytics Section -->
    <h3>Your Goal Analytics</h3>
    <?php if (count($goals) > 0): ?>
        <?php foreach ($goals as $goal): ?>
            <div>
                <h4><?php echo htmlspecialchars($goal['goal_title']); ?></h4>
                <p><strong>Target Value:</strong> <?php echo htmlspecialchars($goal['target_value']); ?></p>
                <p><strong>Current Progress:</strong> <?php echo htmlspecialchars($goal['current_value']); ?></p>
                <p><strong>Progress:</strong> <?php echo ($goal['current_value'] / $goal['target_value']) * 100; ?>%</p> <!-- Shows progress as a percentage -->
                <p><strong>Last Update:</strong> <?php echo htmlspecialchars($goal['progress_date']); ?></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>You haven't set any goals yet. Start setting your goals now!</p>
    <?php endif; ?>

    <!-- Buttons for actions -->
    <div class="button-group">
        <a href="edit_profile.php" class="btn-outline-purple">Edit Profile</a>
        <a href="rate_player.php" class="btn-outline-purple">Rate Players</a>
        <a href="invitations.php" class="btn-outline-purple">Invitations</a>
        <a href="reviews.php" class="btn-outline-purple">Reviews</a>
        <a href="goal.php" class="btn-outline-purple">Set New Goal</a>
        <a href="track_progress.php" class="btn-outline-purple">Track Progress</a>
        <a href="friend_requests.php" class="btn-outline-purple">Friend Requests</a>
    </div>
</div>

<?php
$master_content = ob_get_clean();
include 'resources/view/layouts/sportconnnect/master.php';
?>
