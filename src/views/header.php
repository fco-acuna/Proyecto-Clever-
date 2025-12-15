<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../CSS/header.css">
</head>
<body>
    <div class="header_container">
        <div class="logo">
            <p>TaskManager.com</p>
        </div>

        <div class="login">
            <div>
                <nav>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Usuario logueado -->
                    <form action="logout.php" method="POST">
                        <button type="submit">Cerrar sesión</button>
                    </form>
                <?php else: ?>
                    <!-- Usuario no logueado -->
                    <a href="login.php">
                        <button>Iniciar sesión</button>
                    </a>
                <?php endif; ?>
                </nav>
            </div>
        </div>
    </div>
</body>
</html>