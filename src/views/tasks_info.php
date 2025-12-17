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
    <title>Tasks info</title>
</head>
<body>
    <div class="edit_container">
        <div class="edit_titulo">
            <h1>Titulo de la tarea:</h1>
            <p><?= htmlspecialchars($task["title"]) ?></p>
        </div>
        <div class="edit_descripcion">
            <h1>Descripción:</h1>
            <p><?= nl2br(htmlspecialchars($task["description"])) ?></p>
        </div>

        <div class="edit_resposable">
            <h1>Responsable:</h1>
            <p><?= nl2br(htmlspecialchars($task["responsible_name"])) ?></p>
        </div>

        <form action="update_status.php" method="POST">
            <input type="hidden" name="task_id" value="<?= $task["id"] ?>">
            <input type="hidden" name="board_id" value="<?= htmlspecialchars($board_id) ?>">

            <!-- 🆕 NUEVO: Dropdown de responsable (solo para supervisores) -->
            <?php if ($is_supervisor): ?>
                <div class="edit_resposable_dropdown">
                    <h3>Cambiar responsable:</h3>
                    <select name="assigned_to">
                        <?php foreach ($all_users as $user): ?>
                            <option value="<?= $user['id'] ?>" 
                                <?= $task['assigned_to'] == $user['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($user['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <div class="edit_status">
                <div class="edit_status_titulo">
                    <h3>Status actual: <strong><?= htmlspecialchars($task["status"]) ?></strong></h3>
                </div>

                <div class="edit_status_opciones">

                    <label class="edit_opciones <?= $task["status"] === "completada" ? "selected" : "" ?>">
                        <input type="radio" name="status" value="completada"
                            <?= $task["status"] === "completada" ? "checked" : "" ?>>
                        <p>Completada</p>
                    </label>

                    <label class="edit_opciones <?= $task["status"] === "en_proceso" ? "selected" : "" ?>">
                        <input type="radio" name="status" value="en_proceso"
                            <?= $task["status"] === "en_proceso" ? "checked" : "" ?>>
                        <p>En Proceso</p>
                    </label>

                    <label class="edit_opciones <?= $task["status"] === "pendiente" ? "selected" : "" ?>">
                        <input type="radio" name="status" value="pendiente"
                            <?= $task["status"] === "pendiente" ? "checked" : "" ?>>
                        <p>Pendiente</p>
                    </label>
                    <input type="hidden" name="board_id" value="<?= htmlspecialchars($board_id) ?>">
                </div>
            </div>

            <div class="edit_task_boton">
                <button type="submit">Guardar Cambios</button>
            </div>
        </form>


    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const radios = document.querySelectorAll(".edit_status_opciones input[type='radio']");

            radios.forEach(radio => {
                radio.addEventListener("change", () => {

                    // Quitar "selected" de todos los labels
                    document.querySelectorAll(".edit_opciones").forEach(l => {
                        l.classList.remove("selected");
                    });

                    // Agregar "selected" al label del radio seleccionado
                    radio.closest("label").classList.add("selected");
                });
            });
        });
    </script>

</body>
</html>