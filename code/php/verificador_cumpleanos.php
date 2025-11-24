<?php
/**
 * ============================================================================
 * VERIFICADOR DE CUMPLEAÑOS (CON CONTROL DE FRECUENCIA)
 * ============================================================================
 * Incluye lógica de Cookies para notificar solo 1 vez al día.
 */

error_reporting(0);
ini_set('display_errors', 0);

session_start();
require_once 'conexion.php';

// Cargar librerías de PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/Exception.php';
require 'PHPMailer/SMTP.php';

header('Content-Type: application/json; charset=utf-8');

// 1. Validar Sesión
if (!isset($_SESSION['logueado']) || !$_SESSION['logueado']) {
    echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
    exit;
}

$email_usuario = $_SESSION['email'];
$hoy_mes_dia = date('m-d');

// ============================================================================
// CONTROL DE COOKIES (NUEVO)
// Generamos un ID único para la cookie basado en el usuario y la fecha de hoy
// ============================================================================
$nombre_cookie = 'aviso_cumple_' . md5($email_usuario . date('Y-m-d'));

// Si la cookie existe, significa que ya notificamos hoy. Detenemos todo.
if (isset($_COOKIE[$nombre_cookie])) {
    // Devolvemos lista vacía para que el JS no muestre Toast
    echo json_encode(['status' => 'success', 'cumpleaneros' => []]);
    exit;
}

// ============================================================================
// LÓGICA NORMAL
// ============================================================================

$conn = obtenerConexion();
if (!$conn) {
    echo json_encode(['status' => 'error', 'message' => 'Error de conexión']);
    exit;
}

// Buscar Cumpleañeros
$stmt = $conn->prepare("
    SELECT nombre, FCumple 
    FROM contacto 
    WHERE correo_usuario = ? 
    AND DATE_FORMAT(FCumple, '%m-%d') = ?
");

$stmt->bind_param("ss", $email_usuario, $hoy_mes_dia);
$stmt->execute();
$resultado = $stmt->get_result();

$lista_nombres = []; 
$lista_detalles = []; 

while ($fila = $resultado->fetch_assoc()) {
    $nombre = $fila['nombre'];
    
    // Calcular edad
    $fecha_nac = new DateTime($fila['FCumple']);
    $hoy_date = new DateTime();
    $edad = $hoy_date->diff($fecha_nac)->y;

    $lista_nombres[] = $nombre;
    $lista_detalles[] = ['nombre' => $nombre, 'edad' => $edad];
}

$stmt->close();
cerrarConexion($conn);

// Si encontramos cumpleañeros, enviamos correo y CREAMOS LA COOKIE
if (count($lista_nombres) > 0) {
    
    // 1. Crear la cookie para que no se repita hoy (expira en 20 horas aprox)
    setcookie($nombre_cookie, 'true', time() + 72000, "/");

    // 2. Enviar Correo
    $mail = new PHPMailer(true);

    try {
        // Configuración Mailtrap
        $mail->isSMTP();
        $mail->Host       = 'sandbox.smtp.mailtrap.io';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'b95118d399b24d'; // Tus credenciales
        $mail->Password   = 'f83a5983a3f33f'; // Tus credenciales
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 2525;

        $mail->setFrom('recordatorios@tuagenda.com', 'Agenda Contactos');
        $mail->addAddress($email_usuario);

        // Construir mensaje
        $cantidad = count($lista_nombres);
        $titulo = ($cantidad > 1) ? "🎉 ¡Hoy hay $cantidad cumpleaños!" : "🎉 ¡Hoy cumple años {$lista_nombres[0]}!";
        
        // Lista HTML
        $html_lista = "<ul>";
        $texto_lista = "";
        foreach ($lista_detalles as $item) {
            $html_lista .= "<li style='margin-bottom: 5px;'><strong>{$item['nombre']}</strong></li>";
            $texto_lista .= "- {$item['nombre']}\n";
        }
        $html_lista .= "</ul>";

        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $titulo;
        
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; color: #333; padding: 20px; border: 1px solid #ddd;'>
                <h2 style='color: #d63384;'>$titulo</h2>
                <p>Hola,</p>
                <p>Aquí tienes la lista de celebraciones para hoy:</p>
                $html_lista
                <br>
                <p>¡No olvides felicitarlos!</p>
                <hr>
                <small>Tu Sistema de Gestión de Contactos</small>
            </div>
        ";
        
        $mail->AltBody = "Hoy cumplen años:\n$texto_lista\n¡Felicítalos!";

        $mail->send();

    } catch (Exception $e) {
        error_log("Error enviando correo: {$mail->ErrorInfo}");
    }
}

// Devolver la lista al JS (si la cookie se acaba de crear, se mostrará el toast esta única vez)
echo json_encode([
    'status' => 'success',
    'cumpleaneros' => $lista_nombres
]);
?>