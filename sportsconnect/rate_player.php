<?php
session_start();
require 'resources/database/db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$rater_user_id = $_SESSION["user_id"];
$success_message = ""; // Initialize success message

// Connect to database
$pdo = new PDO("mysql:host=localhost;dbname=sportconnect", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

// Fetch friends list
$stmt = $pdo->prepare("SELECT u.id, u.full_name FROM users u
                       JOIN friends f ON (f.user_1_id = :user_id OR f.user_2_id = :user_id)
                       WHERE (f.user_1_id = :user_id OR f.user_2_id = :user_id) AND u.id != :user_id");
$stmt->execute([":user_id" => $rater_user_id]);
$friends = $stmt->fetchAll();

// Ranking logic
function getRankingDisplay($rank) {
    if ($rank >= 90) return "🏆 Elite";
    if ($rank >= 70) return "⭐ Pro";
    if ($rank >= 50) return "🎯 Intermediate";
    if ($rank >= 30) return "🌱 Beginner";
    return "🔰 Newbie";
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rated_user_id = (int)$_POST["rated_user_id"];
    $sport = $_POST["sport"];
    $rating = (int)$_POST["rating"];
    $review = htmlspecialchars($_POST["review"]);

    $stmt = $pdo->prepare("INSERT INTO player_ratings (rated_user_id, rater_user_id, sport, rating, review, created_at) 
                           VALUES (:rated_user, :rater_user, :sport, :rating, :review, NOW())");
    $stmt->execute([
        ":rated_user" => $rated_user_id,
        ":rater_user" => $rater_user_id,
        ":sport" => $sport,
        ":rating" => $rating,
        ":review" => $review
    ]);

    // Recalculate average
    $stmt = $pdo->prepare("SELECT AVG(rating) AS avg_rating, COUNT(*) AS rating_count 
                           FROM player_ratings 
                           WHERE rated_user_id = :rated_user AND sport = :sport");
    $stmt->execute([":rated_user" => $rated_user_id, ":sport" => $sport]);
    $result = $stmt->fetch();

    $new_avg_rating = $result['avg_rating'];
    $new_rating_count = $result['rating_count'];
    $rank_display = getRankingDisplay($new_avg_rating);

    // Update user_sports_rankings
    $stmt = $pdo->prepare("
        INSERT INTO user_sports_rankings (user_id, sport, ranking, rating_count, updated_at)
        VALUES (:user_id, :sport, :ranking, :rating_count, NOW())
        ON DUPLICATE KEY UPDATE ranking = :ranking, rating_count = :rating_count, updated_at = NOW()
    ");
    $stmt->execute([
        ":user_id" => $rated_user_id,
        ":sport" => $sport,
        ":ranking" => $new_avg_rating,
        ":rating_count" => $new_rating_count
    ]);

    $success_message = "<p style='color:green; font-weight: bold;'>✅ Rating submitted successfully! New rank: $rank_display</p>";
}

ob_start();
?>
<div class="profile-container">
    <h2>Rate a Friend</h2>

    <?php if (!empty($success_message)): ?>
        <div class="success-message"><?= $success_message ?></div>
    <?php endif; ?>

    <form method="POST">
        <div>
            <label for="rated_user_id">Choose a Friend to Rate:</label>
            <select name="rated_user_id" id="rated_user_id" class="input-field" required>
                <option value="">Select Friend</option>
                <?php foreach ($friends as $friend): ?>
                    <option value="<?= htmlspecialchars($friend['id']) ?>">
                        <?= htmlspecialchars($friend['full_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="sport">Sport:</label>
            <select name="sport" id="sport" class="input-field" required>
                <option value="">Select a Sport</option>
                <option value="Basketball">Basketball</option>
                <option value="Soccer">Soccer</option>
                <option value="Tennis">Tennis</option>
                <option value="Dodgeball">Dodgeball</option>
                <option value="Hockey">Hockey</option>
            </select>
        </div>

        <div>
            <label for="rating">Rating (0-100):</label>
            <input type="number" name="rating" id="rating" min="0" max="100" class="input-field" required>
        </div>

        <div>
            <label for="review">Review:</label>
            <textarea name="review" id="review" rows="4" placeholder="Write a review (optional)" class="input-field"></textarea>
        </div>

        <button type="submit" class="btn-purple">Submit Rating</button>
    </form>
</div>

<?php
$master_content = ob_get_clean();
include 'resources/view/layouts/sportconnnect/master.php';
?>
