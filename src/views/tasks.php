<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// 1️⃣ Verificar login
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// 2️⃣ Obtener ID del board desde la URL
$board_id = $_GET['board_id'] ?? null;
if (!$board_id) {
    die("Error: No se especificó un board.");
}

// 3️⃣ Obtener datos del board
$stmt = $conn->prepare("SELECT * FROM boards WHERE id = :id LIMIT 1");
$stmt->bindParam(':id', $board_id, PDO::PARAM_INT);
$stmt->execute();
$board = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$board) {
    die("Error: Board no encontrado.");
}

// 4️⃣ Obtener tareas del board
$stmt = $conn->prepare("SELECT * FROM tasks WHERE board_id = :board_id");
$stmt->bindParam(':board_id', $board_id, PDO::PARAM_INT);
$stmt->execute();
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($board['name']) ?></title>
    <link rel="stylesheet" href="../CSS/tasks.css">
</head>

<body>
    <div class="dashboard_container">
        <div class="titulo_dashboard">
            <h3><?= htmlspecialchars($board['name']) ?></h3>
            <a href="new_task.php?board_id=<?= $board['id'] ?>">Nueva Task</a>
        </div>

        <div class="container_tasks">
            <?php if (!empty($tasks)): ?>
                <?php foreach ($tasks as $task): ?>
                    <div class="task">
                        <div class="task_semaforo">
                            <p>°</p>
                        </div>
                        <div class="task_informacion">
                            <p class="titulo"><?= htmlspecialchars($task['title']) ?></p>
                            <p class="status"><?= htmlspecialchars($task['status']) ?></p>
                        </div>
                        <div class="task_link">
                            <p>→</p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No hay tareas en este board.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
