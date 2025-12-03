<?php
session_start();
require_once __DIR__ . '/../config/db.php'; 

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Prepara la consulta
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['password'] === $password) { // ⚠️ luego cambiaremos esto a password_hash
        // 🔹 Guardar los datos en la sesión
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['usuario'] = $user['name'];
        $_SESSION['rol'] = $user['role'];

        // 🔹 Redirigir 
        if (isset($_SESSION['user_id'])) {
            header("Location: ../views/board.php");// está logueado
        }
    }
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../CSS/global.css">
    <link rel="stylesheet" href="../CSS/login.css">
    <title>Log-In</title>
</head>
<body>
    <div class="container_login">
        <div class="contenido_izquierda">
            <h1>Bienvenido al Task Manager!</h1>
            <p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Excepturi numquam vel culpa quo ratione, similique esse architecto quod asperiores aliquam?</p>
        </div>
        <div class="contenido_derecha">
            <div class="informacion">
                <h3>Bienvenido de vuelta</h3>
                <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Accusamus, tempore.</p>
            </div>
            <div class="formaulario_login">
                <!-- Aquí empieza el formulario real -->
                <form action="login.php" method="POST">
                    <div class="inputs">
                        <input type="text" name="email" placeholder="E-mail" required>
                        <input type="password" name="password" placeholder="Contraseña" required>
                    </div>
                    <div>
                        <button type="submit" class="button_login">Log-In</button>
                    </div>
                </form>
                <!-- Fin del formulario -->
            </div>
        </div>
    </div>
</body>
</html>
