<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Verifica sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Obtener boards del usuario
$stmt = $conn->prepare("SELECT * FROM boards WHERE created_by = :user_id ORDER BY created_at DESC");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$boards = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Board</title>
    <link rel="stylesheet" href="../CSS/board.css">
</head>
<body>

<?php require_once 'header.php'; ?>

<div class="container_dashboards">

    <?php if ($_SESSION['rol'] === "supervisor"): ?>
        <div class="container_titulo">
            <h1>Dashboards</h1>
            <form action="create_board.php" method="POST" class="form_crear_board">
                <input type="text" name="name" placeholder="Nombre del Board" required>
                <textarea name="description" placeholder="Descripción opcional"></textarea>
                <button type="submit">Crear Board</button>
            </form>
        </div>
    <?php endif; ?>

    <?php foreach ($boards as $board): ?>

        <?php
        // ---- CONTAR TAREAS PARA ESTE BOARD ----

        $stmt = $conn->prepare("
            SELECT status, COUNT(*) AS total
            FROM tasks
            WHERE board_id = :board_id
            GROUP BY status
        ");
        $stmt->bindParam(':board_id', $board['id'], PDO::PARAM_INT);
        $stmt->execute();
        $counts_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Normalizar arreglo
        $counts = [
            "pendiente"   => 0,
            "en_proceso"  => 0,
            "completada"  => 0,
        ];

        foreach ($counts_raw as $row) {
            $counts[$row["status"]] = $row["total"];
        }
        ?>

        <div class="dashboard">
            <div class="titulo_dashboard">
                <h2><?= htmlspecialchars($board['name']) ?></h2>
                <a href="tasks.php?board_id=<?= $board['id'] ?>">Ver Dashboard</a>
            </div>

            <div class="tasks">

                <div class="completed">
                    <p>Completadas</p>
                    <p><?= $counts["completada"] ?></p>
                </div>
                <div class="progress-bar">
                    <div class="progress completed"></div>
                </div>

                <div class="in_progress">
                    <p>Trabajando</p>
                    <p><?= $counts["en_proceso"] ?></p>
                </div>
                <div class="progress-bar">
                    <div class="progress in-progress"></div>
                </div>

                <div class="backlog">
                    <p>Pendientes</p>
                    <p><?= $counts["pendiente"] ?></p>
                </div>
                <div class="progress-bar">
                    <div class="progress backlog"></div>
                </div>

            </div>
        </div>

    <?php endforeach; ?>

</div>

</body>
</html>
