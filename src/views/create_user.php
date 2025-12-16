<?php
session_start();
require_once __DIR__ . '/../config/db.php'; 

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = trim(strtolower($_POST['role']));

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
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':role', $role);

        $stmt->execute();
        $user_id = $conn->lastInsertId();

        // Redirige de nuevo al dashboard o página de boards
        header("Location: board.php?success=1");
        exit();
    } catch (PDOException $e) {
        echo "❌ Error al crear el usuario: " . $e->getMessage();
    }
}
?>
