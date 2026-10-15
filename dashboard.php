
<?php
require_once "auth.php";
requireLogin();

$user_id = $_SESSION["user_id"];

$stmt = $pdo->prepare(
    "SELECT COUNT(*) FROM projects WHERE user_id = ?"
);
$stmt->execute([$user_id]);
$totalProjects = $stmt->fetchColumn();

$stmt = $pdo->prepare(
    "SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN t.task_status = 'Completed'
            THEN 1 ELSE 0 END) AS completed,
        SUM(CASE WHEN t.task_status = 'In Progress'
            THEN 1 ELSE 0 END) AS progress
     FROM tasks t
     JOIN projects p ON t.project_id = p.project_id
     WHERE p.user_id = ?"
);
$stmt->execute([$user_id]);
$stats = $stmt->fetch();

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
    <title>Dashboard</title>
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
    <h1>Welcome, <?= e($_SESSION["username"]) ?>!</h1>
    <p>Manage your academic projects and tasks.</p>

    <div class="stats">
        <div class="stat-card">
            <h3>Total Projects</h3>
            <h1><?= (int)$totalProjects ?></h1>
        </div>

        <div class="stat-card">
            <h3>Total Tasks</h3>
            <h1><?= (int)$stats["total"] ?></h1>
        </div>

        <div class="stat-card">
            <h3>Completed Tasks</h3>
            <h1><?= (int)$stats["completed"] ?></h1>
        </div>

        <div class="stat-card">
            <h3>In Progress</h3>
            <h1><?= (int)$stats["progress"] ?></h1>
        </div>
    </div>

    <h2>My Projects</h2>
    <a class="btn" href="projects.php">Manage Projects</a>

    <div class="cards">
        <?php foreach ($projects as $project): ?>
            <div class="project-card">
                <h3><?= e($project["project_name"]) ?></h3>
                <p><?= e($project["description"]) ?></p>
                <p>Created: <?= e($project["created_at"]) ?></p>
                <a href="tasks.php?project_id=<?= (int)$project["project_id"] ?>">
                    View Tasks
                </a>
            </div>
        <?php endforeach; ?>

        <?php if (!$projects): ?>
            <p>No projects yet. Create your first project!</p>
        <?php endif; ?>
    </div>
</main>
</body>
</html>
