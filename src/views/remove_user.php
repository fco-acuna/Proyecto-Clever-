<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// 1️⃣ Verificar que el usuario está logueado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// 2️⃣ Verificar que es un POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: dashboard.php");
    exit();
}

// 3️⃣ Obtener datos del formulario
$board_id = $_POST['board_id'] ?? null;
$user_id = $_POST['user_id'] ?? null;

if (!$board_id || !$user_id) {
    header("Location: tasks.php?board_id=$board_id&error=" . urlencode("Datos incompletos"));
    exit();
}

// 4️⃣ Verificar que el usuario actual es supervisor
if (($_SESSION['rol'] ?? '') !== 'supervisor') {
    header("Location: tasks.php?board_id=$board_id&error=" . urlencode("No tienes permisos"));
    exit();
}

// 5️⃣ Verificar que el board existe
$stmt = $conn->prepare("SELECT id FROM boards WHERE id = :id");
$stmt->bindParam(':id', $board_id, PDO::PARAM_INT);
$stmt->execute();
if (!$stmt->fetch()) {
    header("Location: dashboard.php?error=" . urlencode("Board no encontrado"));
    exit();
}

// 6️⃣ Obtener el nombre del usuario para el mensaje de confirmación
$stmt = $conn->prepare("SELECT name FROM users WHERE id = :id");
$stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user_to_remove = $stmt->fetch(PDO::FETCH_ASSOC);

// 7️⃣ Quitar el usuario del board
try {
    $stmt = $conn->prepare("DELETE FROM board_users WHERE board_id = :board_id AND user_id = :user_id");
    $stmt->bindParam(':board_id', $board_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $user_name = $user_to_remove ? $user_to_remove['name'] : 'Usuario';
        $success_message = htmlspecialchars($user_name) . " ha sido removido del board";
        header("Location: tasks.php?board_id=$board_id&msg=" . urlencode($success_message));
    } else {
        header("Location: tasks.php?board_id=$board_id&error=" . urlencode("El usuario no estaba asignado a este board"));
    }
    exit();
    
} catch (PDOException $e) {
    error_log("Error al remover usuario: " . $e->getMessage());
    header("Location: tasks.php?board_id=$board_id&error=" . urlencode("Error al remover usuario"));
    exit();
}
?>