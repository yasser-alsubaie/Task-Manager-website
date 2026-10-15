
<?php
require_once "db.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireLogin() {
    if (!isset($_SESSION["user_id"])) {
        header("Location: login.php");
        exit;
    }
}

function e($value) {
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        "UTF-8"
    );
}

function csrfToken() {
    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] =
            bin2hex(random_bytes(32));
    }
    return $_SESSION["csrf_token"];
}

function checkCsrf() {
    if (
        !isset($_POST["csrf_token"]) ||
        !hash_equals(
            csrfToken(),
            $_POST["csrf_token"]
        )
    ) {
        exit("Invalid request.");
    }
}
?>
