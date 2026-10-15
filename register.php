
<?php
require_once "auth.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    checkCsrf();

    $username = trim($_POST["username"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if (
        $username === "" ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($password) < 8
    ) {
        $error = "Enter valid information. Password must be at least 8 characters.";
    } elseif (strlen($username) > 50 ||
              strlen($email) > 100) {
        $error = "Username or email is too long.";
    } else {
        $check = $pdo->prepare(
            "SELECT user_id FROM users
             WHERE username = ? OR email = ?"
        );
        $check->execute([$username, $email]);

        if ($check->fetch()) {
            $error = "Username or email already exists.";
        } else {
            $hashed = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $stmt = $pdo->prepare(
                "INSERT INTO users
                 (username, email, password)
                 VALUES (?, ?, ?)"
            );

            $stmt->execute([
                $username,
                $email,
                $hashed
            ]);

            header("Location: login.php?registered=1");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1">
    <title>Register</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="auth-container">
    <h1>Create Account</h1>
    <p>Academic Task Management System</p>

    <?php if ($error): ?>
        <p class="error"><?= e($error) ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="csrf_token"
               value="<?= e(csrfToken()) ?>">

        <label>Username</label>
        <input type="text" name="username"
               maxlength="50" required>

        <label>Email</label>
        <input type="email" name="email"
               maxlength="100" required>

        <label>Password</label>
        <input type="password" name="password"
               minlength="8" required>

        <button type="submit">Register</button>
    </form>

    <p>Already have an account?
        <a href="login.php">Login</a>
    </p>
</div>
</body>
</html>
