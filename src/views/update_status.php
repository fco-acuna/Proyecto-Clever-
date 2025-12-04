<?php
require_once __DIR__ . '/../config/db.php';

if (!isset($_POST["task_id"], $_POST["status"])) {
    die("Datos incompletos");
}

$task_id = $_POST["task_id"];
$status = $_POST["status"];
$board_id = $_POST["board_id"];

$stmt = $conn->prepare("UPDATE tasks SET status = :status WHERE id = :id");
$stmt->bindParam(':status', $status);
$stmt->bindParam(':id', $task_id);
$stmt->execute();


header("Location: tasks.php?board_id=" . $board_id);
exit;
?>
