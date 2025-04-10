<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

require 'resources/database/db.php';

$user_id = $_SESSION['user_id'];
$message = "";

// Connect to database
$pdo = new PDO("mysql:host=localhost;dbname=sportconnect", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

// Fetch user data
$stmt = $pdo->prepare("SELECT full_name, profile_picture, sports_interests, location, bio FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    die("User not found.");
}

$current_interests = array_map('trim', explode(",", $user['sports_interests'] ?? ''));

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $_POST['full_name'];
    $sports_interests = $_POST['sports_interests'] ?? [];
    $location = $_POST['location'];
    $bio = $_POST['bio']; // Added bio field
    $new_password = $_POST['password'];
    $profile_picture = $user['profile_picture'];

    if (!empty($_FILES['profile_picture']['name'])) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
            $profile_picture = $target_file;
        } else {
            $message = "Error uploading profile picture.";
        }
    }

    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashed_password, $user_id]);
    }

    $sports_interests_str = implode(", ", $sports_interests);

    // Update the bio, sports_interests, and other profile data
    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, sports_interests = ?, location = ?, bio = ?, profile_picture = ? WHERE id = ?");
    $stmt->execute([$full_name, $sports_interests_str, $location, $bio, $profile_picture, $user_id]);

    $_SESSION['full_name'] = $full_name;
    $_SESSION['sports_interests'] = $sports_interests_str;
    $_SESSION['location'] = $location;
    $_SESSION['bio'] = $bio; // Update the session with the new bio
    $_SESSION['profile_picture'] = $profile_picture;

    header("Location: profile.php");
    exit();
}

$pageTitle = "Edit Profile";
ob_start();
?>

<div class="edit-profile-container">
    <h2>Edit Profile</h2>

    <?php if (!empty($message)) { echo "<p style='color:green;'>$message</p>"; } ?>

    <form action="edit_profile.php" method="POST" enctype="multipart/form-data">
        <label>Full Name:</label>
        <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" class="input-field" required>

        <label>Sports Interests:</label>
        <div class="checkbox-group">
            <?php
            $all_sports = ["Basketball", "Soccer", "Tennis", "Dodgeball", "Hockey"];
            foreach ($all_sports as $sport):
                ?>
                <label>
                    <input type="checkbox" name="sports_interests[]" value="<?php echo $sport; ?>"
                        <?php echo in_array($sport, $current_interests) ? 'checked' : ''; ?> class="input-field">
                    <?php echo $sport; ?>
                </label>
            <?php endforeach; ?>
        </div>

        <label>Location:</label>
        <input type="text" name="location" value="<?php echo htmlspecialchars($user['location']); ?>" class="input-field">

        <label>Bio:</label>
        <textarea name="bio" class="input-field"><?php echo htmlspecialchars($user['bio']); ?></textarea> <!-- Added bio textarea -->

        <label>Profile Picture:</label>
        <input type="file" name="profile_picture">
        <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" width="100" height="100">

        <label>New Password (Leave blank to keep current):</label>
        <input type="password" name="password" class="input-field">

        <button type="submit" class="btn-purple">Update Profile</button>
    </form>
</div>

</body>
</html>

<?php
$master_content = ob_get_clean();
include 'resources/view/layouts/sportconnnect/master.php';
?>
