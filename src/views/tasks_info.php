<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_GET["id"])) {
    die("No task ID provided");
}

// Traer información de la task
$task_id = $_GET["id"];
$stmt = $conn->prepare("SELECT * FROM tasks WHERE id = :id");
$stmt->bindParam(':id', $task_id, PDO::PARAM_INT);
$stmt->execute();

$task = $stmt->fetch(PDO::FETCH_ASSOC);
$board_id = $task["board_id"];

// Verificar si es supervisor
$is_supervisor = ($_SESSION['rol'] ?? '') === 'supervisor';

// Obtener todos los usuarios para el dropdown
if ($is_supervisor) {
    $stmt_users = $conn->prepare("SELECT id, name FROM users ORDER BY name");
    $stmt_users->execute();
    $all_users = $stmt_users->fetchAll(PDO::FETCH_ASSOC);
}

// Traer el nombre del usuario 
$stmt = $conn->prepare("
    SELECT tasks.*, users.name as responsible_name 
    FROM tasks 
    LEFT JOIN users ON tasks.assigned_to = users.id
    WHERE tasks.id = :id
");

$stmt->bindParam(':id', $task_id, PDO::PARAM_INT);
$stmt->execute();
$task = $stmt->fetch(PDO::FETCH_ASSOC);





if (!$task) {
    die("Task not found");
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../CSS/tasks.css">
    <title><?= htmlspecialchars($task["title"]) ?> - Detalles</title>
</head>
<body>
    <div class="task_detail_container">
        
        <!-- Header con título y botón volver -->
        <div class="task_detail_header">
            <div>
                <h1 class="task_title"><?= htmlspecialchars($task["title"]) ?></h1>
                <span class="task_status_badge status_<?= $task["status"] ?>">
                    <?= ucfirst(str_replace('_', ' ', $task["status"])) ?>
                </span>
            </div>
            <a href="tasks.php?board_id=<?= $board_id ?>" class="btn_back">← Volver al Board</a>
        </div>

        <!-- Grid de información -->
        <div class="task_detail_grid">
            
            <!-- Columna izquierda: Información -->
            <div class="task_detail_info">
                
                <!-- Card: Descripción -->
                <div class="info_card">
                    <h2 class="card_title">📝 Descripción</h2>
                    <p class="card_content">
                        <?= $task["description"] ? nl2br(htmlspecialchars($task["description"])) : '<em class="text_muted">Sin descripción</em>' ?>
                    </p>
                </div>

                <!-- Card: Responsable -->
                <div class="info_card">
                    <h2 class="card_title">👤 Responsable</h2>
                    <div class="responsible_info">
                        <div class="avatar">
                            <?= strtoupper(substr($task["responsible_name"] ?? 'N', 0, 1)) ?>
                        </div>
                        <span class="responsible_name">
                            <?= htmlspecialchars($task["responsible_name"] ?? 'Sin asignar') ?>
                        </span>
                    </div>
                </div>

            </div>

            <!-- Columna derecha: Acciones -->
            <div class="task_detail_actions">
                
                <form action="update_status.php" method="POST" class="task_form">
                    <input type="hidden" name="task_id" value="<?= $task["id"] ?>">
                    <input type="hidden" name="board_id" value="<?= htmlspecialchars($board_id) ?>">

                    <!-- Card: Cambiar responsable (solo supervisores) -->
                    <?php if ($is_supervisor): ?>
                    <div class="action_card">
                        <h2 class="card_title">👥 Reasignar Tarea</h2>
                        <select name="assigned_to" class="select_responsible">
                            <?php foreach ($all_users as $user): ?>
                                <option value="<?= $user['id'] ?>" 
                                    <?= $task['assigned_to'] == $user['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($user['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <!-- Card: Cambiar status -->
                    <div class="action_card">
                        <h2 class="card_title">🔄 Actualizar Estado</h2>
                        
                        <div class="status_options">
                            
                            <label class="status_option option_completada <?= $task["status"] === "completada" ? "selected" : "" ?>">
                                <input type="radio" name="status" value="completada"
                                    <?= $task["status"] === "completada" ? "checked" : "" ?>>
                                <div class="option_content">
                                    <span class="option_icon">✓</span>
                                    <span class="option_label">Completada</span>
                                </div>
                            </label>

                            <label class="status_option option_en_proceso <?= $task["status"] === "en_proceso" ? "selected" : "" ?>">
                                <input type="radio" name="status" value="en_proceso"
                                    <?= $task["status"] === "en_proceso" ? "checked" : "" ?>>
                                <div class="option_content">
                                    <span class="option_icon">⟳</span>
                                    <span class="option_label">En Proceso</span>
                                </div>
                            </label>

                            <label class="status_option option_pendiente <?= $task["status"] === "pendiente" ? "selected" : "" ?>">
                                <input type="radio" name="status" value="pendiente"
                                    <?= $task["status"] === "pendiente" ? "checked" : "" ?>>
                                <div class="option_content">
                                    <span class="option_icon">○</span>
                                    <span class="option_label">Pendiente</span>
                                </div>
                            </label>

                        </div>
                    </div>

                    <!-- Botón guardar -->
                    <button type="submit" class="btn_save">
                        <span>💾</span> Guardar Cambios
                    </button>

                </form>

            </div>

        </div>

    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const radios = document.querySelectorAll(".status_options input[type='radio']");

            radios.forEach(radio => {
                radio.addEventListener("change", () => {
                    // Quitar "selected" de todos
                    document.querySelectorAll(".status_option").forEach(option => {
                        option.classList.remove("selected");
                    });

                    // Agregar "selected" al seleccionado
                    radio.closest(".status_option").classList.add("selected");
                });
            });
        });
    </script>

</body>
</html>