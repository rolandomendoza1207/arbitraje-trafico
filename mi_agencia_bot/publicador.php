<?php
// ============================================
// PUBLICADOR DE CONTENIDO
// ============================================
include 'cliente.php';

class Publicador {
    private $cliente_id;
    
    public function __construct($cliente_id) {
        $this->cliente_id = $cliente_id;
    }
    
    // === PUBLICAR EL SIGUIENTE POST PENDIENTE ===
    public function publicarSiguiente() {
        $posts = obtenerPostsPendientes($this->cliente_id);
        
        if (empty($posts)) {
            return [
                'success' => false,
                'mensaje' => "📭 No hay posts pendientes para publicar"
            ];
        }
        
        $post = $posts[0];
        
        // Marcar como publicado
        marcarPostPublicado($post['id']);
        
        return [
            'success' => true,
            'mensaje' => "✅ Publicado en Instagram:\n\n" . $post['texto'],
            'post' => $post
        ];
    }
    
    // === PUBLICAR UN POST ESPECÍFICO ===
    public function publicarPost($post_id) {
        $db = new SQLite3(DB_FILE);
        $stmt = $db->prepare("SELECT * FROM posts WHERE id = :id AND cliente_id = :cliente_id");
        $stmt->bindValue(':id', $post_id, SQLITE3_INTEGER);
        $stmt->bindValue(':cliente_id', $this->cliente_id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $post = $result->fetchArray(SQLITE3_ASSOC);
        $db->close();
        
        if (!$post) {
            return [
                'success' => false,
                'mensaje' => "❌ No se encontró el post"
            ];
        }
        
        marcarPostPublicado($post_id);
        
        return [
            'success' => true,
            'mensaje' => "✅ Post publicado:\n\n" . $post['texto'],
            'post' => $post
        ];
    }
    
    // === PROGRAMAR PUBLICACIONES (simulación) ===
    public function programarPublicaciones($horarios = null) {
        $posts = obtenerPostsPendientes($this->cliente_id);
        
        if (empty($posts)) {
            return [
                'success' => false,
                'mensaje' => "📭 No hay posts para programar"
            ];
        }
        
        // Si no se especifican horarios, usar los que ya tienen asignados
        $resultados = [];
        foreach ($posts as $post) {
            $resultados[] = [
                'texto' => substr($post['texto'], 0, 50) . "...",
                'horario' => $post['horario'] ?? 'Sin horario',
                'imagen' => $post['imagen']
            ];
        }
        
        return [
            'success' => true,
            'mensaje' => "📅 " . count($posts) . " posts programados",
            'posts' => $resultados
        ];
    }
    
    // === OBTENER ESTADÍSTICAS DE PUBLICACIÓN ===
    public function obtenerEstadisticas() {
        $db = new SQLite3(DB_FILE);
        
        // Total de posts
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM posts WHERE cliente_id = :cliente_id");
        $stmt->bindValue(':cliente_id', $this->cliente_id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $total = $result->fetchArray(SQLITE3_ASSOC)['total'];
        
        // Publicados
        $stmt = $db->prepare("SELECT COUNT(*) as publicados FROM posts WHERE cliente_id = :cliente_id AND publicado = 1");
        $stmt->bindValue(':cliente_id', $this->cliente_id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $publicados = $result->fetchArray(SQLITE3_ASSOC)['publicados'];
        
        // Pendientes
        $stmt = $db->prepare("SELECT COUNT(*) as pendientes FROM posts WHERE cliente_id = :cliente_id AND publicado = 0");
        $stmt->bindValue(':cliente_id', $this->cliente_id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $pendientes = $result->fetchArray(SQLITE3_ASSOC)['pendientes'];
        
        $db->close();
        
        return [
            'total' => $total,
            'publicados' => $publicados,
            'pendientes' => $pendientes,
            'progreso' => $total > 0 ? round(($publicados / $total) * 100, 2) : 0
        ];
    }
    
    // === SIMULAR PUBLICACIÓN EN REDES ===
    public function simularPublicacion($post_id) {
        $db = new SQLite3(DB_FILE);
        $stmt = $db->prepare("SELECT * FROM posts WHERE id = :id");
        $stmt->bindValue(':id', $post_id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $post = $result->fetchArray(SQLITE3_ASSOC);
        $db->close();
        
        if (!$post) {
            return [
                'success' => false,
                'mensaje' => "❌ Post no encontrado"
            ];
        }
        
        // Simular publicación en Instagram
        $simulacion = [
            'plataforma' => 'Instagram',
            'fecha_publicacion' => date('Y-m-d H:i:s'),
            'texto' => $post['texto'],
            'imagen' => $post['imagen'],
            'estado' => 'Simulado'
        ];
        
        return [
            'success' => true,
            'mensaje' => "✅ Post simulado en Instagram:\n\n" . $post['texto'],
            'simulacion' => $simulacion
        ];
    }
}
?>