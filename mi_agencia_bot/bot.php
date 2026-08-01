<?php
// ============================================
// BOT DE TELEGRAM - CEREBRO PRINCIPAL
// ============================================
include 'config.php';
include 'cliente.php';
include 'generador.php';
include 'publicador.php';

// === 1. RECIBIR MENSAJE DE TELEGRAM ===
$contenido = file_get_contents('php://input');
$update = json_decode($contenido, true);

// Si no hay datos, salir
if (!$update) {
    http_response_code(200);
    exit;
}

// === 2. EXTRAER INFORMACIÓN ===
$mensaje = $update['message'] ?? null;
if (!$mensaje) exit;

$chat_id = $mensaje['chat']['id'];
$texto = $mensaje['text'] ?? '';
$nombre_usuario = $mensaje['from']['first_name'] ?? 'Usuario';

// === 3. PROCESAR COMANDOS ===
$respuesta = procesarMensaje($texto, $chat_id, $nombre_usuario);

// === 4. ENVIAR RESPUESTA ===
if ($respuesta) {
    enviarMensaje($chat_id, $respuesta);
}

// ============================================
// FUNCIONES PRINCIPALES
// ============================================

function enviarMensaje($chat_id, $mensaje) {
    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendMessage";
    $data = [
        'chat_id' => $chat_id,
        'text' => $mensaje,
        'parse_mode' => 'Markdown'
    ];
    
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => http_build_query($data)
        ]
    ];
    
    $context = stream_context_create($options);
    file_get_contents($url, false, $context);
}

function enviarImagen($chat_id, $imagen_path, $caption = '') {
    if (!file_exists($imagen_path)) {
        enviarMensaje($chat_id, "❌ Error: No se encontró la imagen");
        return;
    }
    
    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendPhoto";
    
    // Para PHP sin CURL, usamos file_get_contents con multipart
    $boundary = uniqid();
    $delimiter = '-------------' . $boundary;
    
    $file_data = file_get_contents($imagen_path);
    $base64 = base64_encode($file_data);
    
    $data = "--$delimiter\r\n" .
            "Content-Disposition: form-data; name=\"chat_id\"\r\n\r\n" .
            "$chat_id\r\n" .
            "--$delimiter\r\n" .
            "Content-Disposition: form-data; name=\"photo\"; filename=\"" . basename($imagen_path) . "\"\r\n" .
            "Content-Type: image/png\r\n\r\n" .
            $file_data . "\r\n" .
            "--$delimiter\r\n" .
            "Content-Disposition: form-data; name=\"caption\"\r\n\r\n" .
            "$caption\r\n" .
            "--$delimiter--\r\n";
    
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: multipart/form-data; boundary=$delimiter\r\n",
            'content' => $data
        ]
    ];
    
    $context = stream_context_create($options);
    file_get_contents($url, false, $context);
}

// ============================================
// PROCESADOR DE MENSAJES
// ============================================

function procesarMensaje($texto, $chat_id, $nombre) {
    $texto_original = $texto;
    $texto = strtolower(trim($texto));
    
    // === COMANDOS ===
    if ($texto === '/start' || $texto === 'hola' || $texto === 'hola bot') {
        return mostrarBienvenida($nombre);
    }
    
    if ($texto === '/nuevo_cliente' || $texto === 'nuevo cliente' || $texto === 'registrar') {
        $_SESSION['estado'] = 'nuevo_cliente';
        return "📋 *Registrar nuevo cliente*\n\n" .
               "¿Cuál es el nombre del negocio?\n" .
               "(Ej: 'Panadería El Trigal')";
    }
    
    if ($texto === '/clientes' || $texto === 'clientes' || $texto === 'lista') {
        return listarClientes($chat_id);
    }
    
    if ($texto === '/generar' || $texto === 'generar posts' || $texto === 'crear contenido') {
        $_SESSION['estado'] = 'generar';
        $clientes = obtenerClientes($chat_id);
        if (empty($clientes)) {
            return "❌ No tienes clientes registrados.\n" .
                   "Usa /nuevo_cliente para crear uno.";
        }
        return "📸 *¿Para qué cliente quieres generar contenido?*\n\n" .
               "Escribe el nombre del cliente:\n" .
               implode("\n", array_map(function($c) { 
                   return "• {$c['nombre']}"; 
               }, $clientes));
    }
    
    if ($texto === '/publicar' || $texto === 'publicar ahora' || $texto === 'subir') {
        $_SESSION['estado'] = 'publicar';
        $clientes = obtenerClientes($chat_id);
        if (empty($clientes)) {
            return "❌ No tienes clientes registrados.\n" .
                   "Usa /nuevo_cliente para crear uno.";
        }
        return "📤 *¿Para qué cliente quieres publicar?*\n\n" .
               "Escribe el nombre del cliente:\n" .
               implode("\n", array_map(function($c) { 
                   return "• {$c['nombre']}"; 
               }, $clientes));
    }
    
    if ($texto === '/estadisticas' || $texto === 'estadísticas' || $texto === 'reporte') {
        $_SESSION['estado'] = 'estadisticas';
        $clientes = obtenerClientes($chat_id);
        if (empty($clientes)) {
            return "❌ No tienes clientes registrados.";
        }
        return "📊 *¿Para qué cliente quieres estadísticas?*\n\n" .
               "Escribe el nombre del cliente:\n" .
               implode("\n", array_map(function($c) { 
                   return "• {$c['nombre']}"; 
               }, $clientes));
    }
    
    if ($texto === '/ayuda' || $texto === 'ayuda' || $texto === 'help') {
        return mostrarAyuda();
    }
    
    // === ESTADOS DE CONVERSACIÓN ===
    $estado = $_SESSION['estado'] ?? '';
    
    switch($estado) {
        case 'nuevo_cliente':
            return procesarNuevoCliente($texto_original, $chat_id);
            
        case 'nuevo_cliente_rubro':
            return procesarRubroCliente($texto_original, $chat_id);
            
        case 'nuevo_cliente_productos':
            return procesarProductosCliente($texto_original, $chat_id);
            
        case 'nuevo_cliente_colores':
            return procesarColoresCliente($texto_original, $chat_id);
            
        case 'generar':
            return procesarGeneracion($texto_original, $chat_id);
            
        case 'publicar':
            return procesarPublicacion($texto_original, $chat_id);
            
        case 'estadisticas':
            return procesarEstadisticas($texto_original, $chat_id);
            
        default:
            return manejarConversacionLibre($texto_original, $chat_id);
    }
}

// ============================================
// MANEJADORES DE ESTADOS
// ============================================

function procesarNuevoCliente($texto, $chat_id) {
    if (strlen($texto) < 3) {
        return "❌ El nombre debe tener al menos 3 caracteres.\n" .
               "¿Cuál es el nombre del negocio?";
    }
    
    $_SESSION['nuevo_cliente'] = ['nombre' => ucwords($texto)];
    $_SESSION['estado'] = 'nuevo_cliente_rubro';
    
    return "✅ Cliente '{$_SESSION['nuevo_cliente']['nombre']}' registrado.\n\n" .
           "📂 *¿Qué rubro es?*\n" .
           "1. Panadería\n" .
           "2. Cafetería\n" .
           "3. Restaurante\n" .
           "4. Otro (escribe el rubro)";
}

function procesarRubroCliente($texto, $chat_id) {
    $rubros = ['panadería', 'cafetería', 'restaurante'];
    $rubro = strtolower($texto);
    
    // Limpiar número si eligió opción
    if ($rubro === '1') $rubro = 'panadería';
    elseif ($rubro === '2') $rubro = 'cafetería';
    elseif ($rubro === '3') $rubro = 'restaurante';
    
    $_SESSION['nuevo_cliente']['rubro'] = $rubro;
    $_SESSION['estado'] = 'nuevo_cliente_productos';
    
    return "✅ Rubro: " . ucfirst($rubro) . "\n\n" .
           "📝 *¿Cuáles son sus 5 productos principales?*\n" .
           "Escribe separados por coma\n" .
           "(Ej: pan, facturas, tortas, empanadas, budín)";
}

function procesarProductosCliente($texto, $chat_id) {
    $productos = array_map('trim', explode(',', $texto));
    
    if (count($productos) < 2) {
        return "❌ Necesito al menos 2 productos.\n" .
               "Escrívelos separados por coma.";
    }
    
    $_SESSION['nuevo_cliente']['productos'] = json_encode($productos);
    $_SESSION['estado'] = 'nuevo_cliente_colores';
    
    return "✅ Productos guardados: " . implode(', ', $productos) . "\n\n" .
           "🎨 *¿Cuáles son los colores principales?*\n" .
           "Ej: 'amarillo y marrón' o '#FF5733 y #8B4513'";
}

function procesarColoresCliente($texto, $chat_id) {
    // Extraer colores
    preg_match_all('/([#a-fA-F0-9]{6}|[a-z]+)/i', $texto, $matches);
    $colores = $matches[1] ?? ['#FF5733', '#33FF57'];
    
    $color1 = $colores[0] ?? '#FF5733';
    $color2 = $colores[1] ?? '#33FF57';
    
    // Si son nombres en español, convertir
    $mapa_colores = [
        'amarillo' => '#FFD700',
        'rojo' => '#FF0000',
        'azul' => '#0000FF',
        'verde' => '#00AA00',
        'negro' => '#000000',
        'blanco' => '#FFFFFF',
        'marrón' => '#8B4513',
        'naranja' => '#FF8C00',
        'rosa' => '#FF69B4',
        'violeta' => '#8B00FF',
        'gris' => '#808080'
    ];
    
    if (isset($mapa_colores[strtolower($color1)])) {
        $color1 = $mapa_colores[strtolower($color1)];
    }
    if (isset($mapa_colores[strtolower($color2)])) {
        $color2 = $mapa_colores[strtolower($color2)];
    }
    
    // Guardar en base de datos
    $cliente_id = guardarCliente(
        $chat_id,
        $_SESSION['nuevo_cliente']['nombre'],
        $_SESSION['nuevo_cliente']['rubro'],
        $_SESSION['nuevo_cliente']['productos'],
        $color1,
        $color2
    );
    
    $_SESSION['estado'] = '';
    unset($_SESSION['nuevo_cliente']);
    
    return "🎉 *¡Cliente registrado con éxito!*\n\n" .
           "📌 *Comandos disponibles:*\n" .
           "/generar - Crear contenido\n" .
           "/clientes - Ver todos\n" .
           "/publicar - Subir posts\n" .
           "/estadisticas - Ver reportes";
}

function procesarGeneracion($texto, $chat_id) {
    $clientes = obtenerClientes($chat_id);
    $cliente = null;
    
    foreach ($clientes as $c) {
        if (strpos(strtolower($c['nombre']), strtolower($texto)) !== false) {
            $cliente = $c;
            break;
        }
    }
    
    if (!$cliente) {
        return "❌ No encontré un cliente con ese nombre.\n" .
               "Usa /clientes para ver la lista.";
    }
    
    // Generar contenido
    $generador = new GeneradorContenido($cliente['id']);
    $posts = $generador->generarPostsMensuales(5); // 5 posts de prueba
    
    foreach ($posts as $post) {
        guardarPost($cliente['id'], $post['texto'], $post['imagen'], $post['horario']);
    }
    
    $_SESSION['estado'] = '';
    
    return "📸 *¡Contenido generado!*\n\n" .
           "✅ 5 posts para *{$cliente['nombre']}*\n" .
           "✅ Imágenes guardadas en /posts/\n\n" .
           "📋 *Próximos pasos:*\n" .
           "/publicar - Subir a redes\n" .
           "/estadisticas - Ver progreso";
}

function procesarPublicacion($texto, $chat_id) {
    $clientes = obtenerClientes($chat_id);
    $cliente = null;
    
    foreach ($clientes as $c) {
        if (strpos(strtolower($c['nombre']), strtolower($texto)) !== false) {
            $cliente = $c;
            break;
        }
    }
    
    if (!$cliente) {
        return "❌ No encontré un cliente con ese nombre.\n" .
               "Usa /clientes para ver la lista.";
    }
    
    $publicador = new Publicador($cliente['id']);
    $resultado = $publicador->publicarSiguiente();
    
    $_SESSION['estado'] = '';
    
    if ($resultado['success']) {
        return $resultado['mensaje'] . "\n\n" .
               "📊 Quedan " . count(obtenerPostsPendientes($cliente['id'])) . " posts pendientes.";
    } else {
        return $resultado['mensaje'];
    }
}

function procesarEstadisticas($texto, $chat_id) {
    $clientes = obtenerClientes($chat_id);
    $cliente = null;
    
    foreach ($clientes as $c) {
        if (strpos(strtolower($c['nombre']), strtolower($texto)) !== false) {
            $cliente = $c;
            break;
        }
    }
    
    if (!$cliente) {
        return "❌ No encontré un cliente con ese nombre.\n" .
               "Usa /clientes para ver la lista.";
    }
    
    $publicador = new Publicador($cliente['id']);
    $stats = $publicador->obtenerEstadisticas();
    
    $_SESSION['estado'] = '';
    
    return "📊 *Estadísticas de {$cliente['nombre']}*\n\n" .
           "📝 Total de posts: {$stats['total']}\n" .
           "✅ Publicados: {$stats['publicados']}\n" .
           "⏳ Pendientes: {$stats['pendientes']}\n" .
           "📈 Progreso: {$stats['progreso']}%\n\n" .
           "🏪 Rubro: " . ucfirst($cliente['rubro']) . "\n" .
           "📦 Productos: " . implode(', ', json_decode($cliente['productos'], true));
}

function manejarConversacionLibre($texto, $chat_id) {
    $texto = strtolower($texto);
    
    if (strpos($texto, 'registrar') !== false || 
        strpos($texto, 'nuevo') !== false ||
        strpos($texto, 'crear cliente') !== false) {
        $_SESSION['estado'] = 'nuevo_cliente';
        return "📋 Vamos a registrar un nuevo cliente.\n" .
               "¿Cuál es el nombre del negocio?";
    }
    
    if (strpos($texto, 'generar') !== false || 
        strpos($texto, 'post') !== false ||
        strpos($texto, 'contenido') !== false) {
        $clientes = obtenerClientes($chat_id);
        if (empty($clientes)) {
            return "❌ No tienes clientes registrados.\n" .
                   "Primero usa /nuevo_cliente para crear uno.";
        }
        $_SESSION['estado'] = 'generar';
        return "📸 ¿Para qué cliente quieres generar contenido?\n" .
               "Escribe el nombre del cliente:\n" .
               implode("\n", array_map(function($c) { return "• {$c['nombre']}"; }, $clientes));
    }
    
    if (strpos($texto, 'publicar') !== false || 
        strpos($texto, 'subir') !== false) {
        $clientes = obtenerClientes($chat_id);
        if (empty($clientes)) {
            return "❌ No tienes clientes registrados.";
        }
        $_SESSION['estado'] = 'publicar';
        return "📤 ¿Para qué cliente quieres publicar?\n" .
               "Escribe el nombre del cliente:\n" .
               implode("\n", array_map(function($c) { return "• {$c['nombre']}"; }, $clientes));
    }
    
    return mostrarAyuda();
}

// ============================================
// MENSAJES DE AYUDA Y BIENVENIDA
// ============================================

function mostrarBienvenida($nombre) {
    return "🤖 *¡Hola {$nombre}!*\n\n" .
           "Soy tu asistente para crear contenido para negocios.\n\n" .
           "📌 *Comandos principales:*\n" .
           "/nuevo_cliente - Registrar un negocio\n" .
           "/generar - Crear posts para un cliente\n" .
           "/clientes - Ver todos tus clientes\n" .
           "/publicar - Publicar en redes\n" .
           "/estadisticas - Ver reportes\n" .
           "/ayuda - Mostrar ayuda completa\n\n" .
           "💡 También puedes *hablarme normal* y te entenderé.";
}

function mostrarAyuda() {
    return "🤖 *Ayuda del Bot*\n\n" .
           "📌 *Comandos disponibles:*\n\n" .
           "🏪 *Clientes*\n" .
           "/nuevo_cliente - Registrar nuevo negocio\n" .
           "/clientes - Listar todos tus clientes\n\n" .
           "📸 *Contenido*\n" .
           "/generar - Generar posts para un cliente\n" .
           "/publicar - Publicar el siguiente post\n" .
           "/estadisticas - Ver reporte de progreso\n\n" .
           "💡 *Lenguaje natural*\n" .
           "También puedes decir:\n" .
           "- 'Registrar un nuevo cliente'\n" .
           "- 'Genera posts para Panadería El Trigal'\n" .
           "- 'Publica ahora para Café Central'\n\n" .
           "🔄 /start - Volver a empezar\n" .
           "❓ /ayuda - Mostrar esta ayuda";
}

function listarClientes($chat_id) {
    $clientes = obtenerClientes($chat_id);
    
    if (empty($clientes)) {
        return "📭 *No tienes clientes registrados.*\n\n" .
               "Usa /nuevo_cliente para crear uno.";
    }
    
    $mensaje = "📋 *Tus clientes:*\n\n";
    
    foreach ($clientes as $cliente) {
        $productos = json_decode($cliente['productos'], true);
        $mensaje .= "🏪 *{$cliente['nombre']}*\n";
        $mensaje .= "   📂 Rubro: " . ucfirst($cliente['rubro']) . "\n";
        $mensaje .= "   📦 Productos: " . implode(', ', array_slice($productos, 0, 3)) . "...\n";
        
        // Contar posts pendientes
        $pendientes = obtenerPostsPendientes($cliente['id']);
        $mensaje .= "   📸 Posts: " . count($pendientes) . " pendientes\n\n";
    }
    
    $mensaje .= "💡 Usa /generar para crear contenido para uno de ellos.";
    
    return $mensaje;
}
?>