<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../config/db.php';

$board_id = (int) $_GET['board_id'];

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
    <title>Crear nueva tarea</title>
</head>
<body>

<h2>Crear Nueva Tarea dentro del Board <?= htmlspecialchars($board["name"]) ?></h2>

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
        <option value="pendiente">Backlog</option>
        <option value="en_proceso">En progreso</option>
        <option value="completada">Completada</option>
    </select><br><br>

    <button type="submit">Crear Nueva Tarea</button>
</form>

</body>
</html>