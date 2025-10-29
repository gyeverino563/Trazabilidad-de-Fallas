<?php
session_start();
session_unset();    // Elimina todas las variables de sesión
session_destroy();  // Destruye la sesión

// 🔒 Evitar cache después de logout
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

header("Location: login.php");
exit;
?>
