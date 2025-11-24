<?php
/**
 * ===========================================================================
 * ACTUALIZAR CONTRASEÑA
 * ===========================================================================
 * 1. Valida los datos (token, contraseñas)
 * 2. Verifica que el token exista en 'password_resets'
 * 3. Verifica que el token NO haya expirado (usando la columna 'expira')
 * 4. Obtiene el email asociado al token
 * 5. Hashea la nueva contraseña
 * 6. Actualiza la contraseña en la tabla 'usuario'
 * 7. Elimina el token de 'password_resets' para que no se reuse
 */

require_once 'conexion.php';
header('Content-Type: application/json');

// 1. Validaciones iniciales
// ===========================================================================

// Solo permitir método POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$token = $_POST['token'] ?? '';
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validar campos vacíos
if (empty($token) || empty($password) || empty($confirm_password)) {
    echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos']);
    exit;
}
// Validar que contraseñas coincidan
if ($password !== $confirm_password) {
    echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden']);
    exit;
}
// Validar longitud (debe ser la misma que en tu 'registro.php')
if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres']);
    exit;
}

// 2. Conexión y Verificación del Token
// ===========================================================================

$conn = obtenerConexion();
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión']);
    exit;
}

// Buscamos el token Y nos aseguramos de que no haya expirado
// NOW() es la función de MySQL para la hora actual
$stmt = $conn->prepare("SELECT email FROM password_resets WHERE token = ? AND expira > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows == 0) {
    // Si no hay filas, es porque el token no existe o YA EXPIRÓ
    $stmt->close();
    cerrarConexion($conn);
    echo json_encode(['success' => false, 'message' => 'Token inválido o expirado. Solicita un nuevo enlace.']);
    exit;
}

// El token es válido, obtenemos el email
$fila = $resultado->fetch_assoc();
$email = $fila['email'];
$stmt->close();

// 3. Actualizar Contraseña y Limpiar Token
// ===========================================================================

// Hashear la nueva contraseña (¡NUNCA guardarla como texto plano!)
$password_hash = password_hash($password, PASSWORD_BCRYPT);

// Actualizar la contraseña en la tabla 'usuario'
$stmt_update = $conn->prepare("UPDATE usuario SET contraseña = ? WHERE correo = ?");
$stmt_update->bind_param("ss", $password_hash, $email);
$stmt_update->execute();
$stmt_update->close();

// ¡MUY IMPORTANTE! Borrar el token de la tabla 'password_resets'
// Esto previene que el mismo enlace se pueda usar dos veces.
$stmt_delete = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
$stmt_delete->bind_param("s", $token);
$stmt_delete->execute();
$stmt_delete->close();

cerrarConexion($conn);

// 4. Enviar respuesta de éxito
// ===========================================================================

echo json_encode(['success' => true, 'message' => '¡Contraseña actualizada! Redirigiendo a login...']);
?>