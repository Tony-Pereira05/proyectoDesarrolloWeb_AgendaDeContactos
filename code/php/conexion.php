<?php
/**
 * ============================================================================
 * ARCHIVO DE CONEXIÓN A BASE DE DATOS
 * ============================================================================
 * Configuración centralizada para conectar con MySQL
 * Usar mysqli con manejo de errores
 */

// Configuración de la base de datos
define('DB_HOST', 'localhost');      // Servidor (normalmente localhost)
define('DB_USER', 'root');           // Usuario de MySQL (cambiar a root en caso de usar XAMPP)
define('DB_PASS', '');               // Contraseña  (dejar vacia en caso de usar XAMPP)
define('DB_NAME', 'agenda_contactos'); // Nombre de la base de datos
/*NOTA: en caso de usar la app por primera vez entrar phpmyadmin y crear una base de 
datos vacia con el nombre 'agenda_contactos' e importar el archivo agenda_contactos.sql*/

/**
 * Función para obtener conexión a la base de datos
 * @return mysqli|null Objeto de conexión o null si falla
 */
function obtenerConexion() {
    // Crear conexión
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Verificar conexión
    if ($conn->connect_error) {
        // En producción, NO mostrar detalles del error
        error_log("Error de conexión: " . $conn->connect_error);
        return null;
    }
    
    // Establecer charset UTF-8 para caracteres especiales
    $conn->set_charset("utf8mb4");
    
    return $conn;
}

/**
 * Función para cerrar conexión
 * @param mysqli $conn Objeto de conexión
 */
function cerrarConexion($conn) {
    if ($conn) {
        $conn->close();
    }
}
