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
$stmt = $conn->prepare("
        SELECT boards.* 
        FROM boards
        INNER JOIN board_users ON boards.id = board_users.board_id
        WHERE board_users.user_id = :user_id 
        ORDER BY boards.created_at DESC
    ");
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
            <button id="openModalBtn" class="btn_nuevo_board">+ Nuevo Board</button>
            <button id="openModalBtnUsuario" class="btn_nuevo_board">+ Añadir Nuevo Usuario</button>
        </div>

        <!-- Modal -->
        <div id="modalCrearBoard" class="modal">
            <div class="modal_content">
                <div class="modal_header">
                    <h2>Crear Nuevo Board</h2>
                    <span class="close">&times;</span>
                </div>
                
                <form action="create_board.php" method="POST" class="form_crear_board">
                    <div class="form_group">
                        <label for="name">Nombre del Board *</label>
                        <input type="text" id="name" name="name" placeholder="Ej: Proyecto Marketing Q1" required>
                    </div>
                    
                    <div class="form_group">
                        <label for="description">Descripción</label>
                        <textarea id="description" name="description" placeholder="Describe el propósito de este board..." rows="4"></textarea>
                    </div>
                    
                    <div class="modal_footer">
                        <button type="button" class="btn_cancelar" onclick="cerrarModal()">Cancelar</button>
                        <button type="submit" class="btn_crear">Crear Board</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Usuario -->
        <div id="modalNuevoUsuario" class="modal">
            <div class="modal_content">
                <div class="modal_header">
                    <h2>Crear nuevo usuario</h2>
                    <span class="close">&times;</span>
                </div>
                
                <form action="create_user.php" method="POST" class="form_crear_board">
                    <div class="form_group">
                        <label for="name">Nombre del usuario*</label>
                        <input type="text" id="name" name="name" placeholder="Ej: Francisco Acuña" required>
                    </div>
                    
                    <div class="form_group">
                        <label for="email">Email</label>
                        <input id="description" name="email" placeholder="XXXXXXXX@XXXXXX.XXX"></input>
                    </div>

                    <div class="form_group">
                        <label for="password">Password</label>
                        <input id="description" name="password" placeholder="XXXXXXXX"></input>
                    </div>

                    <div class="form_group">
                        <label for="role">Role</label>
                        <input id="description" name="role" placeholder="supervisor / simple"></input>
                    </div>
                    
                    <div class="modal_footer">
                        <button type="button" class="btn_cancelar" onclick="cerrarModal()">Cancelar</button>
                        <button type="submit" class="btn_crear">Crear Usuario</button>
                    </div>
                </form>
            </div>
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


    <script>
        // ==================== MODAL CREAR BOARD ====================
        const modalBoard = document.getElementById('modalCrearBoard');
        const btnBoard = document.getElementById('openModalBtn');
        const closeBoard = modalBoard.querySelector('.close');

        // Abrir modal board
        btnBoard.onclick = function() {
            modalBoard.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        // Cerrar modal board con X
        closeBoard.onclick = function() {
            cerrarModalBoard();
        }

        // Función para cerrar modal board
        function cerrarModalBoard() {
            modalBoard.style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // ==================== MODAL CREAR USUARIO ====================
        const modalUsuario = document.getElementById('modalNuevoUsuario');
        const btnUsuario = document.getElementById('openModalBtnUsuario'); // minúscula
        const closeUsuario = modalUsuario.querySelector('.close');

        // Abrir modal usuario
        btnUsuario.onclick = function() {
            modalUsuario.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        // Cerrar modal usuario con X
        closeUsuario.onclick = function() {
            cerrarModalUsuario();
        }

        // Función para cerrar modal usuario
        function cerrarModalUsuario() {
            modalUsuario.style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // ==================== EVENTOS GLOBALES ====================

        // Cerrar modales clickeando afuera
        window.onclick = function(event) {
            if (event.target == modalBoard) {
                cerrarModalBoard();
            }
            if (event.target == modalUsuario) {
                cerrarModalUsuario();
            }
        }

        // Cerrar modales con tecla ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                if (modalBoard.style.display === 'block') {
                    cerrarModalBoard();
                }
                if (modalUsuario.style.display === 'block') {
                    cerrarModalUsuario();
                }
            }
        });

    </script>
</body>
</html>
