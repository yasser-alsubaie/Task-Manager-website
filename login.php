
<?php
require_once "auth.php";

// Redirect if already logged in
if (isset($_SESSION["user_id"])) {
    header("Location: dashboard.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    checkCsrf();

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if (empty($email) || empty($password)) {
        $error = "Please enter email and password.";
    } else {

        // Search for user
        $stmt = $pdo->prepare(
            "SELECT user_id, username, email, password
             FROM users
             WHERE email = ?
             LIMIT 1"
        );

        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Debug information in PHP error log
        if ($user) {
            error_log("Login Debug - User found: YES");

            $passwordMatch = password_verify(
                $password,
                $user["password"]
            );

            error_log(
                "Login Debug - Password match: " .
                ($passwordMatch ? "YES" : "NO")
            );
        } else {
            error_log("Login Debug - User found: NO");
            $passwordMatch = false;
        }

        // Check login credentials
        if ($user && $passwordMatch) {

            session_regenerate_id(true);

            $_SESSION["user_id"] = $user["user_id"];
            $_SESSION["username"] = $user["username"];

            header("Location: dashboard.php");
            exit;

        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Login - Task Management System</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<div class="auth-container">

    <h1>Welcome Back!</h1>

    <p>Academic Task & Project Management System</p>

    <?php if (isset($_GET["registered"])): ?>
        <p class="success">
            Account created successfully! Please login.
        </p>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <p class="error">
            <?= e($error) ?>
        </p>
    <?php endif; ?>

    <form method="POST" action="login.php">

        <input
            type="hidden"
            name="csrf_token"
            value="<?= e(csrfToken()) ?>"
        >

        <label>Email Address</label>

        <input
            type="email"
            name="email"
            placeholder="Enter your email"
            maxlength="100"
            required
        >

        <label>Password</label>

        <input
            type="password"
            name="password"
            placeholder="Enter your password"
            required
        >

        <button type="submit">
            Login
        </button>

    </form>

    <p>
        Don't have an account?
        <a href="register.php">Create Account</a>
    </p>

</div>

</body>
</html>
