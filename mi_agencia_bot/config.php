<?php
// ============================================
// CONFIGURACIÓN PRINCIPAL DEL BOT
// ============================================

// === 1. TOKEN DE TELEGRAM ===
// Obtenlo de @BotFather en Telegram
define('BOT_TOKEN', '8637118963:AAFSMEmDDSjxVsWxyddIEq9LHdgAHynahm4');

// === 2. BASE DE DATOS ===
// Usaremos SQLite (no necesitas instalar MySQL)
define('DB_FILE', __DIR__ . '/agencia.db');

// === 3. CARPETAS ===
define('ASSETS_DIR', __DIR__ . '/assets/');
define('POSTS_DIR', __DIR__ . '/posts/');

// === 4. SESIÓN ===
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// === 5. CREAR CARPETAS SI NO EXISTEN ===
if (!file_exists(ASSETS_DIR)) mkdir(ASSETS_DIR, 0777, true);
if (!file_exists(POSTS_DIR)) mkdir(POSTS_DIR, 0777, true);

// === 6. CONFIGURACIÓN DE ERRORES ===
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>