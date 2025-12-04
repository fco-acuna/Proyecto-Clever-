<div class="header_container">
    <div class="logo">
        <p>TaskManager.com</p>
    </div>

    <div class="login">
        <div>
            <nav style="padding:10px; text-align:right;">
            <?php if (isset($_SESSION['user_id'])): ?>
                <!-- Usuario logueado -->
                <form action="logout.php" method="POST" style="display:inline;">
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