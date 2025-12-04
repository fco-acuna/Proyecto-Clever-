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

// 8️⃣ Manejo de mensajes de éxito/error
$message = $_GET['msg'] ?? '';
$error = $_GET['error'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($board['name']) ?></title>
    <link rel="stylesheet" href="../CSS/tasks.css">
    <style>
        /* Estilos adicionales para la gestión de usuarios */
        .users_section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .users_header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .user_item {
            background: white;
            padding: 10px 15px;
            margin: 8px 0;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .user_info {
            display: flex;
            flex-direction: column;
        }
        
        .user_name {
            font-weight: bold;
            color: #333;
        }
        
        .user_email {
            font-size: 0.9em;
            color: #666;
        }
        
        .user_role {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 0.85em;
            margin-left: 10px;
        }
        
        .role_supervisor {
            background: #e3f2fd;
            color: #1976d2;
        }
        
        .role_simple {
            background: #f3e5f5;
            color: #7b1fa2;
        }
        
        .remove_btn {
            background: #f44336;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
        }
        
        .remove_btn:hover {
            background: #d32f2f;
        }
        
        .assign_form {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .assign_form select {
            flex: 1;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .assign_btn {
            background: #4caf50;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .assign_btn:hover {
            background: #45a049;
        }
        
        .message {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .no_users {
            color: #666;
            font-style: italic;
            padding: 10px;
        }
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
                        <div class="task_semaforo">
                            <p>°</p>
                        </div>
                        <div class="task_informacion">
                            <p class="titulo"><?= htmlspecialchars($task['title']) ?></p>
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