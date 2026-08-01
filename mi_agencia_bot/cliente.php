<?php
// ============================================
// GESTIÓN DE CLIENTES Y BASE DE DATOS
// ============================================
include 'config.php';

// === 1. CREAR BASE DE DATOS ===
function iniciarBD() {
    $db = new SQLite3(DB_FILE);
    
    // Tabla de clientes (negocios)
    $db->exec("CREATE TABLE IF NOT EXISTS clientes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        chat_id TEXT NOT NULL,
        nombre TEXT NOT NULL,
        rubro TEXT NOT NULL,
        productos TEXT NOT NULL,
        color1 TEXT DEFAULT '#FF5733',
        color2 TEXT DEFAULT '#33FF57',
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
        activo INTEGER DEFAULT 1
    )");
    
    // Tabla de posts generados
    $db->exec("CREATE TABLE IF NOT EXISTS posts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        cliente_id INTEGER,
        texto TEXT NOT NULL,
        imagen TEXT NOT NULL,
        horario TEXT,
        publicado INTEGER DEFAULT 0,
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    $db->close();
}

// === 2. GUARDAR NUEVO CLIENTE ===
function guardarCliente($chat_id, $nombre, $rubro, $productos, $color1, $color2) {
    iniciarBD();
    $db = new SQLite3(DB_FILE);
    
    $stmt = $db->prepare("INSERT INTO clientes (chat_id, nombre, rubro, productos, color1, color2) 
                          VALUES (:chat_id, :nombre, :rubro, :productos, :color1, :color2)");
    $stmt->bindValue(':chat_id', $chat_id, SQLITE3_TEXT);
    $stmt->bindValue(':nombre', $nombre, SQLITE3_TEXT);
    $stmt->bindValue(':rubro', $rubro, SQLITE3_TEXT);
    $stmt->bindValue(':productos', $productos, SQLITE3_TEXT);
    $stmt->bindValue(':color1', $color1, SQLITE3_TEXT);
    $stmt->bindValue(':color2', $color2, SQLITE3_TEXT);
    
    $result = $stmt->execute();
    $id = $db->lastInsertRowID();
    $db->close();
    
    return $id;
}

// === 3. OBTENER CLIENTES ===
function obtenerClientes($chat_id) {
    iniciarBD();
    $db = new SQLite3(DB_FILE);
    
    $stmt = $db->prepare("SELECT * FROM clientes WHERE chat_id = :chat_id AND activo = 1");
    $stmt->bindValue(':chat_id', $chat_id, SQLITE3_TEXT);
    $result = $stmt->execute();
    
    $clientes = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $clientes[] = $row;
    }
    $db->close();
    
    return $clientes;
}

// === 4. OBTENER CLIENTE POR ID ===
function obtenerClientePorId($id) {
    iniciarBD();
    $db = new SQLite3(DB_FILE);
    
    $stmt = $db->prepare("SELECT * FROM clientes WHERE id = :id");
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $cliente = $result->fetchArray(SQLITE3_ASSOC);
    $db->close();
    
    return $cliente;
}

// === 5. GUARDAR POST GENERADO ===
function guardarPost($cliente_id, $texto, $imagen, $horario) {
    iniciarBD();
    $db = new SQLite3(DB_FILE);
    
    $stmt = $db->prepare("INSERT INTO posts (cliente_id, texto, imagen, horario) 
                          VALUES (:cliente_id, :texto, :imagen, :horario)");
    $stmt->bindValue(':cliente_id', $cliente_id, SQLITE3_INTEGER);
    $stmt->bindValue(':texto', $texto, SQLITE3_TEXT);
    $stmt->bindValue(':imagen', $imagen, SQLITE3_TEXT);
    $stmt->bindValue(':horario', $horario, SQLITE3_TEXT);
    
    $result = $stmt->execute();
    $db->close();
    
    return $result;
}

// === 6. OBTENER POSTS NO PUBLICADOS ===
function obtenerPostsPendientes($cliente_id) {
    iniciarBD();
    $db = new SQLite3(DB_FILE);
    
    $stmt = $db->prepare("SELECT * FROM posts WHERE cliente_id = :cliente_id AND publicado = 0 ORDER BY fecha_creacion ASC");
    $stmt->bindValue(':cliente_id', $cliente_id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    
    $posts = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $posts[] = $row;
    }
    $db->close();
    
    return $posts;
}

// === 7. MARCAR POST COMO PUBLICADO ===
function marcarPostPublicado($post_id) {
    iniciarBD();
    $db = new SQLite3(DB_FILE);
    
    $stmt = $db->prepare("UPDATE posts SET publicado = 1 WHERE id = :id");
    $stmt->bindValue(':id', $post_id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $db->close();
    
    return $result;
}

// === 8. ELIMINAR CLIENTE (desactivar) ===
function eliminarCliente($cliente_id) {
    iniciarBD();
    $db = new SQLite3(DB_FILE);
    
    $stmt = $db->prepare("UPDATE clientes SET activo = 0 WHERE id = :id");
    $stmt->bindValue(':id', $cliente_id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $db->close();
    
    return $result;
}
?>