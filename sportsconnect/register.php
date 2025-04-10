<?php
session_start();
require 'resources/database/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    // Validate and sanitize form data
    $full_name = htmlspecialchars(trim($_POST['full_name']));
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if form data is valid
    if (!empty($full_name) && $email && !empty($_POST['password'])) {
        // Perform database operation to insert the data
        try {
            // Establish database connection
            $pdo = new PDO("mysql:host=localhost;dbname=sportconnect", "root", "");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Prepare INSERT statement
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password) VALUES (:full_name, :email, :password)");

            // Bind parameters
            $stmt->bindParam(':full_name', $full_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $password);

            // Execute the statement
            $stmt->execute();
            // Redirect to the profile page
            header("Location: profile.php");
            exit();
        } catch (PDOException $e) {
            // Handle database connection errors
            echo "Connection failed: " . $e->getMessage();
        }
    } else {
        // Handle invalid form data (e.g., display error messages)
        echo "Invalid form data!";
    }
}
ob_start();
?>

<div class="register-container">
    <h2 class="text-center">Register</h2>
    <form method="post" action="" name="register.php">
        <div>
            <label for="full_name">Fullname</label>
            <input type="text" name="full_name" id="full_name" pattern="[a-zA-Z0-9 ]+" class="input-field" required />
        </div>

        <div>
            <label for="email">Email</label>
            <input type="email" name="email" id="email" class="input-field" required />
        </div>

        <div>
            <label for="password">Password</label>
            <input type="password" name="password" id="password" class="input-field" required />
        </div>

        <button type="submit" name="register" class="btn-purple">Register</button>
    </form>

    <h5>Already have an account? <a href="login.php">Login here</a></h5>
</div>

<?php
// Store output buffer into master content
$master_content = ob_get_clean();

// Include master layout
include 'resources/view/layouts/sportconnnect/master.php';
?>
