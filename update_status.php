
<?php
require_once "auth.php";
requireLogin();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: tasks.php");
    exit;
}

checkCsrf();

$task_id = (int)($_POST["task_id"] ?? 0);
$status = $_POST["task_status"] ?? "";

if (!in_array($status, ["In Progress", "Completed"], true)) {
    exit("Invalid status.");
}

$stmt = $pdo->prepare(
    "UPDATE tasks t
     JOIN projects p ON t.project_id = p.project_id
     SET t.task_status = ?
     WHERE t.task_id = ? AND p.user_id = ?"
);

$stmt->execute([
    $status,
    $task_id,
    $_SESSION["user_id"]
]);

header("Location: tasks.php");
exit;
?>
