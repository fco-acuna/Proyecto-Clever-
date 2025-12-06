<?php
session_start();
require_once __DIR__ . '/../config/db.php'; // ajusta la ruta según tu estructura

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';

    // Validación mínima
    if (empty($name)) {
        die("❌ El nombre del board es obligatorio.");
    }

    // Asegúrate de que el usuario esté logueado
    if (!isset($_SESSION['user_id'])) {
        die("⚠️ Debes iniciar sesión");
    } else {
    // Aquí ya sabemos que SÍ está logueado
    // Ahora verificamos: ¿es supervisor?
    if ($_SESSION['rol'] !== 'supervisor') { // <-- Fíjate aquí
        die("⚠️ Solo los supervisores pueden crear boards");
    }
}

    $created_by = $_SESSION['user_id'];

    try {
        // Inserta en la base de datos / crear el board
        $stmt = $conn->prepare("INSERT INTO boards (name, description, created_by) VALUES (:name, :description, :created_by)");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':created_by', $created_by);

        $stmt->execute();
        $board_id = $conn->lastInsertId();

        // Asignar automáticamente al supervisor que lo creó
        $stmt = $conn->prepare("INSERT INTO board_users (board_id, user_id) VALUES (:board_id, :user_id)");
        $stmt->bindParam(':board_id', $board_id);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();

        // Redirige de nuevo al dashboard o página de boards
        header("Location: board.php?success=1");
        exit();
    } catch (PDOException $e) {
        echo "❌ Error al crear el board: " . $e->getMessage();
    }
}
?>
