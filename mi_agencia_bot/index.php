<?php
// ============================================
// PÁGINA DE PRUEBA DEL BOT
// ============================================
include 'config.php';

echo "✅ Bot funcionando correctamente<br>";
echo "📁 PHP versión: " . phpversion() . "<br>";
echo "📁 Carpeta assets: " . (file_exists(ASSETS_DIR) ? "✅" : "❌") . "<br>";
echo "📁 Carpeta posts: " . (file_exists(POSTS_DIR) ? "✅" : "❌") . "<br>";

// Mostrar información completa de PHP
phpinfo();
?>