<?php
require_once __DIR__ . '/../config/db.php';

if (!isset($_POST["task_id"], $_POST["status"])) {
    die("Datos incompletos");
}

$task_id = $_POST["task_id"];
$status = $_POST["status"];

$stmt = $conn->prepare("UPDATE tasks SET status = :status WHERE id = :id");
$stmt->bindParam(':status', $status);
$stmt->bindParam(':id', $task_id);
$stmt->execute();


header("Location: tasks_info.php?id=" . $task_id);
exit;
?>
