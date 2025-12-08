<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_POST["task_id"], $_POST["status"])) {
    die("Datos incompletos");
}

$task_id = $_POST["task_id"];
$status = $_POST["status"];
$board_id = $_POST["board_id"];
$assigned_to = $_POST["assigned_to"] ?? null;

// Verificar si el usuario es supervisor
$is_supervisor = ($_SESSION['rol'] ?? '') === 'supervisor';

if ($assigned_to !== null && !$is_supervisor) {
    $assigned_to = null;
}

// Preparar el update

if($assigned_to !== null && $is_supervisor) {
    // Permisos para que el supervisor actualice status y usuarios asignados
    $stmt = $conn->prepare("UPDATE tasks SET status = :status, assigned_to = :assigned_to WHERE id = :id");
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':assigned_to', $assigned_to, PDO::PARAM_INT);
    $stmt->bindParam(':id', $task_id, PDO::PARAM_INT);
} else {
    // Permisos para usuario simple (solo actualiza status)
    $stmt = $conn->prepare("UPDATE tasks SET status = :status WHERE id = :id");
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':id', $task_id, PDO::PARAM_INT);
}

// Ejecutamos el updat
$stmt->execute();


header("Location: tasks.php?board_id=" . $board_id);
exit;
?>
