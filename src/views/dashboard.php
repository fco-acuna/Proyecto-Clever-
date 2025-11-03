<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login_view.php");
    exit();
}
?>
<h1>Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario']); ?> 😎</h1>
<p>Tu rol: <?php echo htmlspecialchars($_SESSION['rol']); ?></p>
<a href="logout.php">Cerrar sesión</a>
