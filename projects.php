
<?php
require_once "auth.php";
requireLogin();

$user_id = $_SESSION["user_id"];
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    checkCsrf();

    $action = $_POST["action"] ?? "";
    $project_id = (int)($_POST["project_id"] ?? 0);

    if ($action === "add" || $action === "update") {
        $name = trim($_POST["project_name"] ?? "");
        $description = trim($_POST["description"] ?? "");

        if ($name === "" || strlen($name) > 100) {
            $error = "Enter a valid project name.";
        } elseif ($action === "add") {
            $stmt = $pdo->prepare(
                "INSERT INTO projects
                 (project_name, description, created_at, user_id)
                 VALUES (?, ?, CURDATE(), ?)"
            );
            $stmt->execute([$name, $description, $user_id]);

        } else {
            $stmt = $pdo->prepare(
                "UPDATE projects
                 SET project_name = ?, description = ?
                 WHERE project_id = ? AND user_id = ?"
            );
            $stmt->execute([
                $name, $description, $project_id, $user_id
            ]);
        }
    } elseif ($action === "delete") {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                "DELETE t FROM tasks t
                 JOIN projects p ON t.project_id = p.project_id
                 WHERE p.project_id = ? AND p.user_id = ?"
            );
            $stmt->execute([$project_id, $user_id]);

            $stmt = $pdo->prepare(
                "DELETE FROM projects
                 WHERE project_id = ? AND user_id = ?"
            );
            $stmt->execute([$project_id, $user_id]);

            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Could not delete project.";
        }
    }

    if ($error === "") {
        header("Location: projects.php");
        exit;
    }
}

$stmt = $pdo->prepare(
    "SELECT * FROM projects
     WHERE user_id = ?
     ORDER BY project_id DESC"
);
$stmt->execute([$user_id]);
$projects = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1">
    <title>Manage Projects</title>
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
    <h1>Manage Projects</h1>

    <?php if ($error): ?>
        <p class="error"><?= e($error) ?></p>
    <?php endif; ?>

    <section class="panel">
        <h2>Add New Project</h2>

        <form method="POST">
            <input type="hidden" name="csrf_token"
                   value="<?= e(csrfToken()) ?>">
            <input type="hidden" name="action" value="add">

            <label>Project Name</label>
            <input name="project_name" maxlength="100" required>

            <label>Description</label>
            <textarea name="description"></textarea>

            <button type="submit">Add Project</button>
        </form>
    </section>

    <h2>My Projects</h2>

    <div class="cards">
        <?php foreach ($projects as $project): ?>
            <div class="project-card">
                <form method="POST">
                    <input type="hidden" name="csrf_token"
                           value="<?= e(csrfToken()) ?>">
                    <input type="hidden" name="action"
                           value="update">
                    <input type="hidden" name="project_id"
                           value="<?= (int)$project["project_id"] ?>">

                    <label>Project Name</label>
                    <input name="project_name"
                           maxlength="100"
                           value="<?= e($project["project_name"]) ?>"
                           required>

                    <label>Description</label>
                    <textarea name="description"><?= e($project["description"]) ?></textarea>

                    <button type="submit">Save Changes</button>
                </form>

                <a class="btn" href="tasks.php?project_id=<?= (int)$project["project_id"] ?>">
                    View Tasks
                </a>

                <form method="POST"
                      onsubmit="return confirm('Delete this project and all its tasks?');">
                    <input type="hidden" name="csrf_token"
                           value="<?= e(csrfToken()) ?>">
                    <input type="hidden" name="action"
                           value="delete">
                    <input type="hidden" name="project_id"
                           value="<?= (int)$project["project_id"] ?>">
                    <button class="danger" type="submit">
                        Delete Project
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</main>
</body>
</html>
