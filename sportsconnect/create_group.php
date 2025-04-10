<?php
session_start();
require 'resources/database/db.php';

// Redirect to login page if not logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$error_message = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $group_name = trim($_POST['group_name']);
    $full_names = explode(",", trim($_POST['full_names']));  // Split full names into an array

    // Validate full names
    $full_names = array_map('trim', $full_names);  // Remove extra spaces
    $valid_full_names = [];

    foreach ($full_names as $full_name) {
        if (!empty($full_name)) {
            $valid_full_names[] = $full_name;
        } else {
            $error_message = "Invalid full name: $full_name";
            break;
        }
    }

    if (empty($group_name)) {
        $error_message = "Group name cannot be empty.";
    } elseif (empty($valid_full_names)) {
        $error_message = "At least one valid full name is required to add members.";
    } else {
        try {
            // Create PDO connection
            $pdo = new PDO("mysql:host=localhost;dbname=sportconnect", "root", "", [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);

            // Insert group into database
            $stmt = $pdo->prepare("INSERT INTO chat_groups (group_name, created_by) VALUES (?, ?)");
            $stmt->execute([$group_name, $_SESSION['user_id']]);
            $group_id = $pdo->lastInsertId();

            // Add creator as member of the group
            $stmt = $pdo->prepare("INSERT INTO group_members (group_id, user_id) VALUES (?, ?)");
            $stmt->execute([$group_id, $_SESSION['user_id']]);

            // Add additional users as members using full names
            foreach ($valid_full_names as $full_name) {
                // Check if the full name exists in the users table
                $stmt = $pdo->prepare("SELECT id FROM users WHERE full_name = ?");
                $stmt->execute([$full_name]);
                $user = $stmt->fetch();

                if ($user) {
                    // Add user to the group
                    $stmt = $pdo->prepare("INSERT INTO group_members (group_id, user_id) VALUES (?, ?)");
                    $stmt->execute([$group_id, $user['id']]);
                } else {
                    $error_message = "User with full name $full_name does not exist.";
                    break;
                }
            }

            if (!$error_message) {
                $success_message = "Group created successfully!";
                header("Location: group_chat.php?group_id=" . $group_id); // Redirect to group chat page
                exit();
            }

        } catch (PDOException $e) {
            $error_message = "Error creating group: " . $e->getMessage();
        }
    }
}

$pageTitle = "Create Group | SportConnect";
ob_start();
?>
<div class="profile-container">
<h1>Create a New Group</h1>

<?php if ($error_message): ?>
    <p class="text-danger"><?= htmlspecialchars($error_message) ?></p>
<?php endif; ?>

<?php if ($success_message): ?>
    <p class="text-success"><?= htmlspecialchars($success_message) ?></p>
<?php endif; ?>

<form method="POST">
    <div>
        <label for="group_name">Group Name:</label>
        <input type="text" name="group_name" id="group_name" class="form-control" required>
    </div>

    <div>
        <label for="full_names">Add Members (Start typing their full name):</label>
        <input type="text" name="full_names" id="full_names" class="form-control" placeholder="Start typing to find users..." required>
        <div id="suggestions" style="border: 1px solid #ccc; margin-top: 5px; max-height: 200px; overflow-y: auto; display: none;"></div>
    </div>

    <button type="submit" class="btn-purple">Create Group</button>
</form>

<script>
    document.getElementById('full_names').addEventListener('input', function () {
        var query = this.value;

        if (query.length > 2) {  // Trigger search after typing 3 characters
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'search_users.php?query=' + encodeURIComponent(query), true);
            xhr.onload = function () {
                if (xhr.status === 200) {
                    var users = JSON.parse(xhr.responseText);
                    var suggestions = document.getElementById('suggestions');
                    suggestions.innerHTML = '';
                    suggestions.style.display = 'none';

                    if (users.length > 0) {
                        suggestions.style.display = 'block';
                        users.forEach(function (user) {
                            var div = document.createElement('div');
                            div.classList.add('suggestion');
                            div.innerText = user.full_name;
                            div.onclick = function () {
                                document.getElementById('full_names').value += user.full_name + ', ';
                                suggestions.innerHTML = '';
                                suggestions.style.display = 'none';
                            };
                            suggestions.appendChild(div);
                        });
                    }
                }
            };
            xhr.send();
        } else {
            document.getElementById('suggestions').style.display = 'none';
        }
    });
</script>

<?php
$master_content = ob_get_clean();
include 'resources/view/layouts/sportconnnect/master.php';
?>

