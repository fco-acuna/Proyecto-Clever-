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

// 4️⃣ Verificar si el usuario actual es supervisor
$current_user_id = $_SESSION['user_id'] ?? null;
$is_supervisor = ($_SESSION['rol'] ?? '') === 'supervisor';

// 5️⃣ Obtener tareas del board
$stmt = $conn->prepare("SELECT * FROM tasks WHERE board_id = :board_id");
$stmt->bindParam(':board_id', $board_id, PDO::PARAM_INT);
$stmt->execute();
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 6️⃣ Obtener usuarios asignados al board
$stmt = $conn->prepare("
    SELECT u.id, u.name, u.email, u.role 
    FROM users u
    INNER JOIN board_users bu ON u.id = bu.user_id
    WHERE bu.board_id = :board_id
    ORDER BY u.name
");
$stmt->bindParam(':board_id', $board_id, PDO::PARAM_INT);
$stmt->execute();
$assigned_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 7️⃣ Obtener usuarios disponibles para asignar (que NO están en el board)
$stmt = $conn->prepare("
    SELECT u.id, u.name, u.email, u.role 
    FROM users u
    WHERE u.id NOT IN (
        SELECT user_id FROM board_users WHERE board_id = :board_id
    )
    ORDER BY u.name
");
$stmt->bindParam(':board_id', $board_id, PDO::PARAM_INT);
$stmt->execute();
$available_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener el name del usuario 
$stmt = $conn->prepare("
    SELECT tasks.*, users.name as responsible_name 
    FROM tasks 
    LEFT JOIN users ON tasks.assigned_to = users.id
    WHERE tasks.board_id = :board_id
");
$stmt->bindParam(':board_id', $board_id, PDO::PARAM_INT);
$stmt->execute();  // <-- Ejecutar
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener el total de tareas
$stmt = $conn->prepare ("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'completada' THEN 1 ELSE 0 END) as completadas,
        SUM(CASE WHEN status = 'en_proceso' THEN 1 ELSE 0 END) as en_proceso,
        SUM(CASE WHEN status = 'pendiente' THEN 1 ELSE 0 END) as pendiente
    FROM tasks
    WHERE board_id = :board_id
");

$stmt->bindParam(':board_id', $board_id, PDO::PARAM_INT);
$stmt->execute();
$metrics = $stmt->fetch(PDO::FETCH_ASSOC);

// Calcular el porcentaje de eficacia
$total = $metrics['total'] ?? 0;
$completadas = $metrics['completadas'] ?? 0;

if ($total > 0){
    $eficacia = ($completadas / $total) * 100;
} else {
    $eficacia = 0;
}

$eficacia = round($eficacia, 1);

// 8️⃣ Manejo de mensajes de éxito/error
$message = $_GET['msg'] ?? '';
$error = $_GET['error'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($board['name']) ?></title>
    <link rel="stylesheet" href="/CSS/tasks.css">
    <style>
        /* Estilos adicionales para la gestión de usuarios */
        
    </style>
</head>

<body>
    <div class="dashboard_container">
        <div class="titulo_dashboard">
            <h3><?= htmlspecialchars($board['name']) ?></h3>
            <a href="new_task.php?board_id=<?= $board['id'] ?>">Nueva Task</a>
        </div>

        <!-- Sección de Gestión de Usuarios (solo para supervisores) -->
        <?php if ($is_supervisor): ?>
        <div class="metricas">
            <div class="metrica">
                <h3>Tareas totales</h3>
                <p><?= $metrics['total']?></p>
            </div>
            <div class="metrica">
                <h3>Completadas</h3>
                <p><?= $metrics['completadas']?></p>
            </div>
            <div class="metrica">
                <h3>En proceso</h3>
                <p><?= $metrics['en_proceso']?></p>
            </div>
            <div class="metrica">
                <h3>Backlog</h3>
                <p><?= $metrics['pendiente']?></p>
            </div>
            <div class="metrica">
                <h3>Porcentaje de Eficacia</h3>
                <p><?= $eficacia?>%</p>
            </div>
        </div>

        <div class="users_section">
            <div class="users_header">
                <h3>👥 Usuarios Asignados (<?= count($assigned_users) ?>)</h3>
            </div>

            <!-- Mensajes de éxito/error -->
            <?php if ($message): ?>
                <div class="message success"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="message error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <!-- Lista de usuarios asignados -->
            <?php if (!empty($assigned_users)): ?>
                <?php foreach ($assigned_users as $user): ?>
                    <div class="user_item">
                        <div class="user_info">
                            <span class="user_name">
                                <?= htmlspecialchars($user['name']) ?>
                                <span class="user_role role_<?= $user['role'] ?>">
                                    <?= ucfirst($user['role']) ?>
                                </span>
                            </span>
                            <span class="user_email"><?= htmlspecialchars($user['email']) ?></span>
                        </div>
                        <form method="POST" action="remove_user.php" style="margin: 0;">
                            <input type="hidden" name="board_id" value="<?= $board_id ?>">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <button type="submit" class="remove_btn" 
                                    onclick="return confirm('¿Seguro que deseas quitar a <?= htmlspecialchars($user['name']) ?>?')">
                                ✕ Quitar
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no_users">No hay usuarios asignados a este board.</p>
            <?php endif; ?>

            <!-- Formulario para asignar nuevos usuarios -->
            <?php if (!empty($available_users)): ?>
                <form method="POST" action="assign_user.php" class="assign_form">
                    <input type="hidden" name="board_id" value="<?= $board_id ?>">
                    <select name="user_id" required>
                        <option value="">-- Seleccionar usuario --</option>
                        <?php foreach ($available_users as $user): ?>
                            <option value="<?= $user['id'] ?>">
                                <?= htmlspecialchars($user['name']) ?> (<?= htmlspecialchars($user['email']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="assign_btn">➕ Asignar Usuario</button>
                </form>
            <?php else: ?>
                <p class="no_users">Todos los usuarios disponibles ya están asignados.</p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Sección de Tareas -->
        <div class="container_tasks">
            <?php if (!empty($tasks)): ?>
                <?php foreach ($tasks as $task): ?>
                    <div class="task">
                        <?php if (($task['status']) == 'completada'): ?>
                            <div class="task_semaforo">
                                <p style='color: #4caf50;'>°</p>
                            </div>
                        <?php elseif (($task['status']) == 'en_proceso'): ?>
                            <div class="task_semaforo">
                                <p style='color: #ff9800;'>°</p>
                            </div>
                        <?php elseif (($task['status']) == 'pendiente'): ?>
                            <div class="task_semaforo">
                                <p style='color: #f44336;'>°</p>
                            </div>
                        <?php endif; ?>
                        
                        

                        <div class="task_informacion">
                            <p class="titulo"><?= htmlspecialchars($task['title']) ?></p>
                            <p class="responsable">
                                <?= htmlspecialchars($task['responsible_name'] ?? "Sin asignar") ?>
                            </p>
                            <p class="status"><?= htmlspecialchars($task['status']) ?></p>
                        </div>
                        <div class="task_link">
                            <a href="tasks_info.php?id=<?= $task['id'] ?>">
                                <?= htmlspecialchars($task['title']) ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No hay tareas en este board.</p>
            <?php endif; ?>
        </div>

        <div>
            <a href="../views/board.php">Regreso a Boards</a>
        </div>
    </div>
</body>
</html>