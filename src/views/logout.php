<?php
session_start();
session_unset();    // Limpia variables
session_destroy();  // Destruye la sesión

header("Location: board.php");
exit;
?>
