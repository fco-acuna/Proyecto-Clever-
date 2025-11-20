<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../config/db.php';

$board_id = (int) $_GET['board_id'];


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $status = trim($_POST['status']);

    if ($title === "") {
        $error = "El título es obligatorio.";
    } else {
        $stmt = $conn->prepare("INSERT INTO tasks (board_id, title, description, status, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([
            $board_id,
            $title,
            $description,
            $status
        ]);
        header("Location: tasks.php?board_id=". $board_id);
    }
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear nueva Task</title>
</head>
<body>

<h2>Crear Task para Board #<?= htmlspecialchars($board_id) ?></h2>

<?php if (isset($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST">
    <label>Título:</label>
    <input type="text" name="title" required><br><br>

    <label>Descripción:</label><br>
    <textarea name="description" rows="4"></textarea><br><br>

    <label>Status:</label>
    <select name="status">
        <option value="pendiente">Pendiente</option>
        <option value="en_proceso">En progreso</option>
        <option value="completada">Completada</option>
    </select><br><br>

    <button type="submit">Crear Task</button>
</form>

</body>
</html>