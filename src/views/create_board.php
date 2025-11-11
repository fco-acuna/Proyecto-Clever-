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
        die("⚠️ Debes iniciar sesión para crear un board.");
    }

    $created_by = $_SESSION['user_id'];

    try {
        // Inserta en la base de datos
        $stmt = $conn->prepare("INSERT INTO boards (name, description, created_by) VALUES (:name, :description, :created_by)");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':created_by', $created_by);

        $stmt->execute();

        // Redirige de nuevo al dashboard o página de boards
        header("Location: board.php?success=1");
        exit();
    } catch (PDOException $e) {
        echo "❌ Error al crear el board: " . $e->getMessage();
    }
}
?>
