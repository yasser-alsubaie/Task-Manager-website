
<?php
require_once "auth.php";
requireLogin();

$user_id = $_SESSION["user_id"];
$error = "";

$stmt = $pdo->prepare(
    "SELECT project_id, project_name
     FROM projects WHERE user_id = ?
     ORDER BY project_name"
);
$stmt->execute([$user_id]);
$projects = $stmt->fetchAll();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    checkCsrf();

    $action = $_POST["action"] ?? "";
    $task_id = (int)($_POST["task_id"] ?? 0);

    if ($action === "add" || $action === "update") {
        $title = trim($_POST["task_title"] ?? "");
        $description = trim($_POST["task_description"] ?? "");
        $status = $_POST["task_status"] ?? "In Progress";
        $due_date = $_POST["due_date"] ?? "";
        $project_id = (int)($_POST["project_id"] ?? 0);

        $check = $pdo->prepare(
            "SELECT project_id FROM projects
             WHERE project_id = ? AND user_id = ?"
        );
        $check->execute([$project_id, $user_id]);

        if (
            $title === "" ||
            strlen($title) > 100 ||
            !$check->fetch() ||
            !in_array($status, ["In Progress", "Completed"], true) ||
            ($due_date !== "" &&
             !preg_match('/^\d{4}-\d{2}-\d{2}$/', $due_date))
        ) {
            $error = "Please enter valid task information.";
        } else {
            $dateValue = $due_date ?: null;

            if ($action === "add") {
                $stmt = $pdo->prepare(
                    "INSERT INTO tasks
                     (task_title, task_description,
                      task_status, due_date, project_id)
                     VALUES (?, ?, ?, ?, ?)"
                );
                $stmt->execute([
                    $title, $description, $status,
                    $dateValue, $project_id
                ]);
            } else {
                $stmt = $pdo->prepare(
                    "UPDATE tasks t
                     JOIN projects p
                       ON t.project_id = p.project_id
                     SET t.task_title = ?,
                         t.task_description = ?,
                         t.task_status = ?,
                         t.due_date = ?,
                         t.project_id = ?
                     WHERE t.task_id = ?
                       AND p.user_id = ?"
                );
                $stmt->execute([
                    $title, $description, $status,
                    $dateValue, $project_id,
                    $task_id, $user_id
                ]);
            }
        }
    } elseif ($action === "delete") {
        $stmt = $pdo->prepare(
            "DELETE t FROM tasks t
             JOIN projects p ON t.project_id = p.project_id
             WHERE t.task_id = ? AND p.user_id = ?"
        );
        $stmt->execute([$task_id, $user_id]);
    }

    if ($error === "") {
        header("Location: tasks.php");
        exit;
    }
}

$filter = (int)($_GET["project_id"] ?? 0);

$sql = "SELECT t.*, p.project_name
        FROM tasks t
        JOIN projects p ON t.project_id = p.project_id
        WHERE p.user_id = ?";
$params = [$user_id];

if ($filter > 0) {
    $sql .= " AND p.project_id = ?";
    $params[] = $filter;
}

$sql .= " ORDER BY t.task_id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tasks = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1">
    <title>Manage Tasks</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<nav>
    <h2>Task Management System</h2>
    <div>
        <a href="dashboard.php">Dashboard</a>
        <a href="projects.php">Projects</a>
        <a href="tasks.php">Tasks</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<main>
    <h1>Manage Tasks</h1>

    <?php if ($error): ?>
        <p class="error"><?= e($error) ?></p>
    <?php endif; ?>

    <section class="panel">
        <h2>Add New Task</h2>

        <?php if (!$projects): ?>
            <p>Create a project first.</p>
            <a href="projects.php">Create Project</a>
        <?php else: ?>
            <form method="POST">
                <input type="hidden" name="csrf_token"
                       value="<?= e(csrfToken()) ?>">
                <input type="hidden" name="action" value="add">

                <label>Task Title</label>
                <input name="task_title" maxlength="100" required>

                <label>Description</label>
                <textarea name="task_description"></textarea>

                <label>Project</label>
                <select name="project_id" required>
                    <?php foreach ($projects as $project): ?>
                        <option value="<?= (int)$project["project_id"] ?>"
                          <?= $filter === (int)$project["project_id"]
                              ? "selected" : "" ?>>
                            <?= e($project["project_name"]) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label>Due Date</label>
                <input type="date" name="due_date">

                <label>Status</label>
                <select name="task_status">
                    <option value="In Progress">In Progress</option>
                    <option value="Completed">Completed</option>
                </select>

                <button type="submit">Add Task</button>
            </form>
        <?php endif; ?>
    </section>

    <h2>Task List</h2>

    <?php if (!$tasks): ?>
        <p>No tasks found.</p>
    <?php endif; ?>

    <div class="cards">
        <?php foreach ($tasks as $task): ?>
            <div class="project-card">
                <form method="POST">
                    <input type="hidden" name="csrf_token"
                           value="<?= e(csrfToken()) ?>">
                    <input type="hidden" name="action"
                           value="update">
                    <input type="hidden" name="task_id"
                           value="<?= (int)$task["task_id"] ?>">

                    <label>Task Title</label>
                    <input name="task_title"
                           maxlength="100"
                           value="<?= e($task["task_title"]) ?>"
                           required>

                    <label>Description</label>
                    <textarea name="task_description"><?= e($task["task_description"]) ?></textarea>

                    <label>Project</label>
                    <select name="project_id">
                        <?php foreach ($projects as $project): ?>
                            <option
                                value="<?= (int)$project["project_id"] ?>"
                                <?= (int)$project["project_id"] ===
                                    (int)$task["project_id"]
                                    ? "selected" : "" ?>>
                                <?= e($project["project_name"]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label>Due Date</label>
                    <input type="date" name="due_date"
                           value="<?= e($task["due_date"]) ?>">

                    <label>Status</label>
                    <select name="task_status">
                        <option value="In Progress"
                            <?= $task["task_status"] === "In Progress"
                                ? "selected" : "" ?>>
                            In Progress
                        </option>
                        <option value="Completed"
                            <?= $task["task_status"] === "Completed"
                                ? "selected" : "" ?>>
                            Completed
                        </option>
                    </select>

                    <button type="submit">Save Changes</button>
                </form>

                <p>Quick Status Update:</p>

                <form method="POST"
                      action="update_status.php">
                    <input type="hidden" name="csrf_token"
                           value="<?= e(csrfToken()) ?>">
                    <input type="hidden" name="task_id"
                           value="<?= (int)$task["task_id"] ?>">

                    <select name="task_status">
                        <option value="In Progress"
                            <?= $task["task_status"] === "In Progress"
                                ? "selected" : "" ?>>
                            In Progress
                        </option>
                        <option value="Completed"
                            <?= $task["task_status"] === "Completed"
                                ? "selected" : "" ?>>
                            Completed
                        </option>
                    </select>

                    <button type="submit">Update Status</button>
                </form>

                <form method="POST"
                      onsubmit="return confirm('Delete this task?');">
                    <input type="hidden" name="csrf_token"
                           value="<?= e(csrfToken()) ?>">
                    <input type="hidden" name="action"
                           value="delete">
                    <input type="hidden" name="task_id"
                           value="<?= (int)$task["task_id"] ?>">

                    <button class="danger" type="submit">
                        Delete Task
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</main>
</body>
</html>
