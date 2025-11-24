<?php
/**
 * ===========================================================================
 * SOLICITAR RESETEO DE CONTRASEÑA
 * ===========================================================================
 * 1. Valida el email
 * 2. Verifica si el usuario existe
 * 3. Genera un token seguro y una fecha de expiración
 * 4. Guarda el token en la tabla 'password_resets'
 * 5. Llama al 'helper' para enviar el email con el token
 */

// Requerimos los archivos necesarios
require_once 'conexion.php';
require_once 'enviar_email_reset.php'; // El archivo que creamos en el Paso 3

// Siempre respondemos con JSON
header('Content-Type: application/json');

// 1. Validaciones iniciales
// ===========================================================================

// Solo permitir método POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$email = trim($_POST['email'] ?? '');

// Validar que el email no esté vacío y tenga formato válido
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Email inválido']);
    exit;
}

// 2. Conexión y verificación de usuario
// ===========================================================================

$conn = obtenerConexion();
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión']);
    exit;
}

// Verificar si el email existe en la tabla 'usuario'
$stmt = $conn->prepare("SELECT COUNT(*) FROM usuario WHERE correo = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

if ($count == 0) {
    // ¡IMPORTANTE! Por seguridad, no le decimos al usuario que el email
    // no existe. Simplemente mostramos un mensaje genérico.
    // Esto previene que un atacante adivine qué correos están registrados.
    echo json_encode(['success' => true, 'message' => 'Si el correo está registrado, recibirás un enlace']);
    cerrarConexion($conn);
    exit;
}

// 3. Generar y guardar el Token
// ===========================================================================

try {
    // Generar un token criptográficamente seguro
    $token = bin2hex(random_bytes(32));
    
    // Definir expiración (1 hora desde ahora)
    $expira = date('Y-m-d H:i:s', time() + 3600); 

    // Borrar tokens antiguos para este email (buena práctica)
    $stmt_delete = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
    $stmt_delete->bind_param("s", $email);
    $stmt_delete->execute();
    $stmt_delete->close();

    // Insertar el nuevo token en nuestra tabla
    $stmt_insert = $conn->prepare("INSERT INTO password_resets (email, token, expira) VALUES (?, ?, ?)");
    $stmt_insert->bind_param("sss", $email, $token, $expira);

    if (!$stmt_insert->execute()) {
        // Error si no se pudo guardar el token
        $stmt_insert->close();
        cerrarConexion($conn);
        echo json_encode(['success' => false, 'message' => 'Error al guardar el token']);
        exit;
    }
    $stmt_insert->close();

} catch (Exception $e) {
    // Captura error si random_bytes falla (muy raro)
    cerrarConexion($conn);
    echo json_encode(['success' => false, 'message' => 'Error al generar el token']);
    exit;
}

// 4. Enviar el Email
// ===========================================================================

if (enviarEmailReset($email, $token)) {
    // ¡Éxito!
    echo json_encode(['success' => true, 'message' => 'Si el correo está registrado, recibirás un enlace']);
} else {
    // Fracaso (PHPMailer falló)
    echo json_encode(['success' => false, 'message' => 'Error al enviar el correo. Intenta más tarde.']);
}

cerrarConexion($conn);
?>