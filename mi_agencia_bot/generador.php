<?php
// ============================================
// GENERADOR DE CONTENIDO (TEXTO + IMÁGENES)
// ============================================
include 'cliente.php';

class GeneradorContenido {
    private $cliente_id;
    private $cliente_data;
    private $productos;
    
    public function __construct($cliente_id) {
        $this->cliente_id = $cliente_id;
        $this->cliente_data = obtenerClientePorId($cliente_id);
        $this->productos = json_decode($this->cliente_data['productos'], true);
    }
    
    // === GENERAR POSTS MENSUALES ===
    public function generarPostsMensuales($cantidad = 30) {
        $posts = [];
        $tipos = ['promocion', 'producto', 'testimonio', 'curiosidad', 'detras_camaras'];
        
        for ($i = 0; $i < $cantidad; $i++) {
            $tipo = $tipos[array_rand($tipos)];
            $producto = $this->productos[array_rand($this->productos)];
            $precio = rand(5000, 25000);
            
            // Generar texto según tipo
            switch($tipo) {
                case 'promocion':
                    $descuento = rand(10, 30);
                    $texto = $this->generarPromocion($producto, $precio, $descuento);
                    break;
                case 'producto':
                    $texto = $this->generarProducto($producto, $precio);
                    break;
                case 'testimonio':
                    $texto = $this->generarTestimonio($producto);
                    break;
                case 'curiosidad':
                    $texto = $this->generarCuriosidad($producto);
                    break;
                case 'detras_camaras':
                    $texto = $this->generarDetrasCamaras($producto);
                    break;
            }
            
            // Generar imagen
            $imagen_path = $this->generarImagen($texto, $producto);
            
            $posts[] = [
                'texto' => $texto,
                'imagen' => $imagen_path,
                'horario' => $this->sugerirHorario($i),
                'fecha' => date('Y-m-d', strtotime("+$i days"))
            ];
        }
        
        return $posts;
    }
    
    // === GENERAR UN POST INDIVIDUAL ===
    public function generarPostUnico($tipo = null) {
        $tipos = ['promocion', 'producto', 'testimonio', 'curiosidad', 'detras_camaras'];
        $tipo = $tipo ?? $tipos[array_rand($tipos)];
        $producto = $this->productos[array_rand($this->productos)];
        $precio = rand(5000, 25000);
        
        switch($tipo) {
            case 'promocion':
                $descuento = rand(10, 30);
                $texto = $this->generarPromocion($producto, $precio, $descuento);
                break;
            case 'producto':
                $texto = $this->generarProducto($producto, $precio);
                break;
            case 'testimonio':
                $texto = $this->generarTestimonio($producto);
                break;
            case 'curiosidad':
                $texto = $this->generarCuriosidad($producto);
                break;
            case 'detras_camaras':
                $texto = $this->generarDetrasCamaras($producto);
                break;
        }
        
        $imagen_path = $this->generarImagen($texto, $producto);
        
        return [
            'texto' => $texto,
            'imagen' => $imagen_path,
            'producto' => $producto
        ];
    }
    
    // === GENERADORES DE TEXTO ===
    private function generarPromocion($producto, $precio, $descuento) {
        $precio_final = $precio * (1 - $descuento/100);
        $frases = [
            "🔥 OFERTA IMPERDIBLE",
            "🎉 PROMOCIÓN ESPECIAL",
            "⚡ DESCUENTO RELÁMPAGO"
        ];
        $frase = $frases[array_rand($frases)];
        
        return "{$frase}\n\n" .
               "{$descuento}% OFF en {$producto}\n" .
               "Antes: $" . number_format($precio) . "\n" .
               "Ahora: $" . number_format($precio_final) . "\n\n" .
               "📍 ¡Ven y pruébalo!\n" .
               "🕐 Válido hasta agotar stock";
    }
    
    private function generarProducto($producto, $precio) {
        $adjetivos = ['Delicioso', 'Increíble', 'Artesanal', 'Premium', 'Casero', 'Exclusivo'];
        $adj = $adjetivos[array_rand($adjetivos)];
        $elogios = [
            "El favorito de nuestros clientes",
            "¡Una experiencia única!",
            "Calidad que se siente",
            "Hecho con amor"
        ];
        $elogio = $elogios[array_rand($elogios)];
        
        return "🌟 {$adj} {$producto}\n\n" .
               "{$elogio}\n" .
               "Solo $" . number_format($precio) . "\n\n" .
               "¿Ya lo probaste?\n" .
               "📍 Te esperamos";
    }
    
    private function generarTestimonio($producto) {
        $nombres = ['María', 'Carlos', 'Ana', 'Jorge', 'Lucía', 'Pedro', 'Laura'];
        $nombre = $nombres[array_rand($nombres)];
        $testimonios = [
            "El mejor {$producto} que he probado en mi vida",
            "Increíble, volveré por más {$producto}",
            "Excelente calidad y sabor",
            "Lo recomiendo 100%, {$producto} espectacular"
        ];
        $testimonio = $testimonios[array_rand($testimonios)];
        
        return "💬 \"{$testimonio}\"\n\n" .
               "- {$nombre}\n\n" .
               "¡Gracias por confiar en nosotros!\n" .
               "📍 Tu opinión nos importa";
    }
    
    private function generarCuriosidad($producto) {
        $datos = [
            "Nuestro {$producto} es 100% natural y sin conservantes",
            "Hacemos {$producto} con receta familiar de 3 generaciones",
            "Cada {$producto} se prepara al momento con ingredientes frescos",
            "Usamos productos locales para nuestro {$producto}",
            "Nuestro {$producto} tiene menos calorías que el tradicional"
        ];
        $dato = $datos[array_rand($datos)];
        
        return "📚 ¿Sabías que...\n\n" .
               "{$dato}\n\n" .
               "📍 Descubre más en nuestro local";
    }
    
    private function generarDetrasCamaras($producto) {
        $frases = [
            "Así preparamos nuestros {$producto}s día a día",
            "Detrás de cada {$producto} hay horas de dedicación",
            "El secreto está en la pasión con que hacemos {$producto}"
        ];
        $frase = $frases[array_rand($frases)];
        
        return "👨‍🍳 {$frase}\n\n" .
               "Con amor y dedicación todos los días\n" .
               "¿Te animas a probarlo?\n" .
               "📍 Te esperamos";
    }
    
    // === GENERADOR DE IMÁGENES ===
    private function generarImagen($texto, $producto) {
        $ancho = 1080;
        $alto = 1080;
        
        // Crear imagen
        $imagen = imagecreate($ancho, $alto);
        
        // Colores del cliente
        $color1 = $this->cliente_data['color1'] ?? '#FF5733';
        $color2 = $this->cliente_data['color2'] ?? '#33FF57';
        
        list($r1, $g1, $b1) = $this->hex2rgb($color1);
        list($r2, $g2, $b2) = $this->hex2rgb($color2);
        
        // Fondo degradado simple (usamos el color1 como base)
        $fondo = imagecolorallocate($imagen, $r1, $g1, $b1);
        $blanco = imagecolorallocate($imagen, 255, 255, 255);
        $negro = imagecolorallocate($imagen, 0, 0, 0);
        $dorado = imagecolorallocate($imagen, 255, 215, 0);
        $transparente = imagecolorallocatealpha($imagen, 0, 0, 0, 60);
        
        // === DISEÑO DE LA IMAGEN ===
        
        // Marco exterior dorado
        imagerectangle($imagen, 30, 30, $ancho-30, $alto-30, $dorado);
        imagerectangle($imagen, 50, 50, $ancho-50, $alto-50, $blanco);
        
        // Marco interior transparente (para el texto)
        imagefilledrectangle($imagen, 70, 70, $ancho-70, $alto-70, $transparente);
        
        // Línea decorativa superior
        for ($i = 100; $i < 300; $i += 20) {
            imagesetpixel($imagen, $i, 100, $dorado);
            imagesetpixel($imagen, $i, 101, $dorado);
        }
        for ($i = $ancho-100; $i > $ancho-300; $i -= 20) {
            imagesetpixel($imagen, $i, 100, $dorado);
            imagesetpixel($imagen, $i, 101, $dorado);
        }
        
        // === TEXTO ===
        $lineas = explode("\n", $texto);
        $y = 200;
        
        foreach ($lineas as $linea) {
            // Centrar texto manualmente (aproximado)
            $longitud = strlen($linea);
            $x = 150;
            
            // Si es un título, más grande
            if (strpos($linea, 'OFERTA') !== false || 
                strpos($linea, 'PROMOCIÓN') !== false || 
                strpos($linea, 'DESCUENTO') !== false) {
                imagestring($imagen, 5, $x, $y, $linea, $dorado);
                $y += 50;
            } else {
                imagestring($imagen, 4, $x, $y, $linea, $blanco);
                $y += 35;
            }
        }
        
        // === NOMBRE DEL NEGOCIO (parte inferior) ===
        $nombre = strtoupper($this->cliente_data['nombre']);
        imagestring($imagen, 5, 400, 980, $nombre, $dorado);
        
        // Línea decorativa inferior
        imagestring($imagen, 1, 400, 1000, "━━━━━━━━━━━━━━━━━━━━━━━━━━", $dorado);
        
        // === GUARDAR IMAGEN ===
        if (!file_exists(POSTS_DIR)) {
            mkdir(POSTS_DIR, 0777, true);
        }
        
        $nombre_archivo = POSTS_DIR . 'post_' . time() . '_' . rand(1000, 9999) . '.png';
        imagepng($imagen, $nombre_archivo);
        imagedestroy($imagen);
        
        return $nombre_archivo;
    }
    
    // === SUGERIR HORARIO DE PUBLICACIÓN ===
    private function sugerirHorario($dia) {
        // Lunes a viernes vs fin de semana
        $fecha = date('N', strtotime("+$dia days"));
        if ($fecha >= 6) {
            $horarios = ['10:00', '13:00', '17:00', '20:00'];
        } else {
            $horarios = ['09:00', '12:00', '15:00', '18:00', '21:00'];
        }
        return $horarios[array_rand($horarios)];
    }
    
    // === CONVERTIR HEX A RGB ===
    private function hex2rgb($hex) {
        $hex = str_replace('#', '', $hex);
        if (strlen($hex) == 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2))
        ];
    }
}
?>