<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../config/db.php';

$board_id = (int) ($_POST['board_id'] ?? 0);
$current_user_id = $_SESSION['user_id'];

// Obtener info del board
$stmt = $conn->prepare("SELECT name FROM boards WHERE id = :id LIMIT 1");
$stmt->bindParam(':id', $board_id, PDO::PARAM_INT);
$stmt->execute();
$board = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$board) {
    die("Error: Board no encontrado.");
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $status = trim($_POST['status']);

    if ($title === "") {
        $error = "El título es obligatorio.";
    } else {
        $stmt = $conn->prepare("INSERT INTO tasks (board_id, title, description, status, assigned_to, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            $board_id,
            $title,
            $description,
            $status,
            $current_user_id
        ]);
        header("Location: tasks.php?board_id=". $board_id);
    }
}
?>