<?php
/**
 * ============================================================================
 * HELPER PARA ENVÍO DE CORREOS (PHPMailer)
 * ============================================================================
 */

// Carga las clases de PHPMailer (ajustamos la ruta)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Requerimos los archivos que pusimos en la carpeta PHPMailer
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/Exception.php';
require 'PHPMailer/SMTP.php';

/**
 * Envía un email de reseteo de contraseña
 * @param string $email El email del destinatario
 * @param string $token El token de reseteo
 * @return bool True si se envió, false si hubo un error
 */
function enviarEmailReset($email, $token) {
    
    // IMPORTANTE: Cambia "code" por la URL real cuando lo subas a un hosting
    // Para localhost, esta ruta funciona.
    $resetLink = "http://localhost/code/reset_password.html?token=" . $token;

    $mail = new PHPMailer(true);

    try {
        // ====================================================================
        // CONFIGURACIÓN DEL SERVIDOR (¡ESTO ES LO MÁS IMPORTANTE!)
        // ====================================================================
        
        // Para probar, usaremos un servidor SMTP de prueba como Mailtrap
        // o puedes usar tus credenciales reales de Gmail/Outlook.
        
        // ¡NO USES ESTAS CREDENCIALES EN PRODUCCIÓN! SON DE EJEMPLO.
        // Ve a https://mailtrap.io/ para crear una cuenta de prueba gratis.
        
        $mail->isSMTP();
        $mail->Host       = 'sandbox.smtp.mailtrap.io'; // Servidor SMTP (Mailtrap)
        $mail->SMTPAuth   = true;
        $mail->Username   = 'b95118d399b24d';    // Tu usuario de Mailtrap
        $mail->Password   = 'f83a5983a3f33f';   // Tu password de Mailtrap
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 2525; // o 2525 si 587 no funciona
        
        /*
        // EJEMPLO SI USARAS GMAIL (Requiere "Contraseñas de Aplicación")
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'tu_correo@gmail.com';
        $mail->Password   = 'tu_contraseña_de_aplicacion'; // NO es tu contraseña normal
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        */

        // Remitente y Destinatario
        $mail->setFrom('no-reply@tuagenda.com', 'Sistema de Agenda');
        $mail->addAddress($email); // Añade el destinatario

        // Contenido del correo
        $mail->isHTML(true); // Fija el formato a HTML
        $mail->Subject = 'Recuperacion de Contraseña - Agenda de Contactos';
        $mail->Body    = "Hola,<br><br>Has solicitado restablecer tu contrasena.<br>"
                       . "Haz clic en el siguiente enlace para continuar:<br>"
                       . "<a href='$resetLink'>$resetLink</a><br><br>"
                       . "Este enlace expirara en 1 hora.<br><br>"
                       . "Si no solicitaste esto, ignora este mensaje.";
        
        // Texto plano para clientes de email que no soportan HTML
        $mail->AltBody = "Para restablecer tu contraseña, copia y pega este enlace en tu navegador: $resetLink";

        $mail->send();
        return true; // Éxito
        
    } catch (Exception $e) {
        // Guardar el error en el log del servidor para debugging
        error_log("Error al enviar email: {$mail->ErrorInfo}");
        return false; // Fracaso
    }
}
?>