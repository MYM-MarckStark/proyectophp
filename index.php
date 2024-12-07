<?php
// --- CONFIGURACIÓN INICIAL ---
session_start(); // Para mantener las sesiones del contador de visitas

// Contador de visitas
if (!isset($_SESSION['visitado'])) {
    $_SESSION['visitado'] = true;
    if (file_exists("contador.txt")) {
        $contador = (int)file_get_contents("contador.txt");
        $contador++;
        file_put_contents("contador.txt", $contador);
    } else {
        $contador = 1;
        file_put_contents("contador.txt", $contador);
    }
} else {
    $contador = (int)file_get_contents("contador.txt");
}

// --- OBTENER DATOS DEL CLIMA ---
$apiKey = "2b99edaa130ef3a66415d4962b420a18"; // Clave de OpenWeatherMap
$ciudad = "Mexico City"; // Cambia por otra ciudad si lo deseas
$clima_url = "http://api.openweathermap.org/data/2.5/weather?q=" . urlencode($ciudad) . "&appid=" . $apiKey . "&units=metric";
$respuesta = @file_get_contents($clima_url);

if ($respuesta !== false) {
    $clima = json_decode($respuesta, true);
    if (isset($clima['main'])) {
        $temperatura = $clima['main']['temp'];
        $condiciones = $clima['weather'][0]['description'];
        $humedad = $clima['main']['humidity'];
        $viento = $clima['wind']['speed'];
    } else {
        $temperatura = "No disponible";
        $condiciones = "Error en los datos: " . $clima['message'];
        $humedad = "N/A";
        $viento = "N/A";
    }
} else {
    $temperatura = "No disponible";
    $condiciones = "No se pudo conectar con la API";
    $humedad = "N/A";
    $viento = "N/A";
}

// --- PROCESAR FORMULARIO ---
$mensaje_formulario = "";
$archivo_subido = "";

// Procesar datos del formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = htmlspecialchars($_POST['nombre'] ?? 'Anónimo');
    $email = htmlspecialchars($_POST['email'] ?? 'No proporcionado');
    $mensaje_formulario = "¡Gracias por enviar tus datos, $nombre! Hemos recibido tu correo: $email.";

    // Procesar archivo subido
    if (isset($_FILES['archivo'])) {
        $directorio_archivos = "archivos/";
        if (!is_dir($directorio_archivos)) {
            mkdir($directorio_archivos, 0777, true); // Crear directorio si no existe
        }
        
        $archivo = $_FILES['archivo'];
        $nombre_archivo = basename($archivo['name']);
        $ruta_archivo = $directorio_archivos . $nombre_archivo;
        
        if (move_uploaded_file($archivo['tmp_name'], $ruta_archivo)) {
            $archivo_subido = "Archivo subido con éxito: " . $nombre_archivo;
        } else {
            $archivo_subido = "Error al subir el archivo.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proyecto PHP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
        }
        h1, h2 {
            color: #333;
        }
        .section {
            margin-bottom: 20px;
        }
        .map {
            width: 100%;
            height: 300px;
        }
        .form-group {
            margin-bottom: 10px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input, .form-group button {
            padding: 10px;
            width: 100%;
            font-size: 16px;
        }
        .form-group textarea {
            padding: 10px;
            width: 100%;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <h1>Proyecto PHP</h1>

    <!-- Contador de visitas -->
    <div class="section">
        <h2>Visitas:</h2>
        <p><?php echo $contador; ?></p>
    </div>

    <!-- Estado del clima -->
    <div class="section">
        <h2>Estado del Clima</h2>
        <p>Ciudad: <?php echo htmlspecialchars($ciudad); ?></p>
        <p>Temperatura: <?php echo htmlspecialchars($temperatura); ?> °C</p>
        <p>Condiciones: <?php echo htmlspecialchars($condiciones); ?></p>
        <p>Humedad: <?php echo htmlspecialchars($humedad); ?>%</p>
        <p>Velocidad del Viento: <?php echo htmlspecialchars($viento); ?> m/s</p>
    </div>

    <!-- Ubicación del cliente -->
    <div class="section">
        <h2>Ubicación del Cliente</h2>
        <div class="map">
            <!-- Google Maps embebido -->
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d60209.19828288988!2d-99.23120462655614!3d19.432607449389453!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x85d1ff3627950dfb%3A0x9cba591870e5a1e!2sCiudad%20de%20M%C3%A9xico!5e0!3m2!1ses!2smx!4v1691187470912!5m2!1ses!2smx" 
                width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
        </div>
    </div>

    <!-- Formulario dinámico -->
    <div class="section">
        <h2>Formulario Dinámico</h2>
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" required>
            </div>
            <div class="form-group">
                <label for="email">Correo Electrónico:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="archivo">Subir Archivo:</label>
                <input type="file" id="archivo" name="archivo" accept=".jpg,.png,.pdf,.docx">
            </div>
            <div class="form-group">
                <button type="submit">Enviar</button>
            </div>
        </form>

        <?php if ($mensaje_formulario): ?>
            <p><?php echo $mensaje_formulario; ?></p>
        <?php endif; ?>
        
        <?php if ($archivo_subido): ?>
            <p><?php echo $archivo_subido; ?></p>
        <?php endif; ?>
    </div>
</body>
</html>
