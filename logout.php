<?php
// =========================================================================
// SCRIPT DE CIERRE DE SESIÓN
// =========================================================================
session_start();

// Destruimos todas las variables de sesión
session_destroy();

// Redirigimos al usuario a la página de inicio de sesión
header("Location: index.php");
exit;
?>
