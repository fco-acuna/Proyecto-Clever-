<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// 🔹 Verifica que haya sesión activa
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 🔹 Consulta los boards del usuario
$stmt = $conn->prepare("SELECT * FROM boards WHERE created_by = :user_id ORDER BY created_at DESC");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();

$boards = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Board</title>
    <link rel="stylesheet" href="../CSS/board.css">
    
</head>
<body>
    <div class="container_dashboards">
        <div class="container_titulo">
            <h1>Dashboards</h1>
            <form action="create_board.php" method="POST" class="form_crear_board">
                <input type="text" name="name" placeholder="Nombre del Board" required>
                <textarea name="description" placeholder="Descripción opcional"></textarea>
                <button type="submit">Crear Board</button>
            </form>
        </div>
        <?php foreach ($boards as $board): ?>
            <div class="dashboard">
                <div class="titulo_dashboard">
                    <h2><?= htmlspecialchars($board['name']) ?></h2>
                    <a href="tasks.php?board_id=<?= $board['id'] ?>">Ver Dashboard</a>
                </div>
                <div class="tasks">
                    <div class="completed">
                        <p>Completadas</p>
                        <p>32 tareas</p>
                    </div>
                    <div class="progress-bar">
                        <div class="progress completed"></div>
                    </div>
                    <div class="in_progress">
                        <p>Trabajando</p>
                        <p>15 tareas</p>
                    </div>
                    <div class="progress-bar">
                        <div class="progress in-progress "></div>
                    </div>
                    <div class="backlog">
                        <p>Pendientes</p>
                        <p>26 tareas</p>
                    </div>
                    <div class="progress-bar">
                        <div class="progress backlog"></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>