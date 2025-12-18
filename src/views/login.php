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
    <link rel="stylesheet" href="/CSS/login.css">
    <title>Log-In</title>
</head>
<body>
    <div class="login_container">
        
        <!-- Lado izquierdo: Branding -->
        <div class="login_brand">
            <div class="brand_content">
                <h1 class="brand_title">TaskManager</h1>
                <p class="brand_subtitle">Organiza tu trabajo, simplifica tu día</p>
                <div class="brand_illustration">
                    <div class="illustration_circle circle_1"></div>
                    <div class="illustration_circle circle_2"></div>
                    <div class="illustration_circle circle_3"></div>
                </div>
            </div>
        </div>

        <!-- Lado derecho: Formulario -->
        <div class="login_form_section">
            <div class="form_container">
                <div class="form_header">
                    <h2>Bienvenido de vuelta</h2>
                    <p>Ingresa tus credenciales para continuar</p>
                </div>

                <form action="login.php" method="POST" class="login_form">
                    <div class="input_group">
                        <label for="email">Email</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            placeholder="tu@email.com" 
                            required
                            autocomplete="email"
                        >
                    </div>

                    <div class="input_group">
                        <label for="password">Contraseña</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="••••••••" 
                            required
                        >
                    </div>

                    <button type="submit" class="btn_login">
                        Iniciar Sesión
                    </button>
                </form>
            </div>
        </div>

    </div>
</body>
</html>
