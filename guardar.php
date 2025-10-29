<?php
require 'sesion.php'; //Asegura que este script solo funcione si hay una sesión activa.
include 'db.php'; //Conexion a mi SQL

// Cargar PHPMailer PHPMailer es una librería externa para enviar correos SMTP fácilmente.
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-6.10.0/src/Exception.php'; //Maneja errores y excepciones de PHPMailer.
require 'PHPMailer-6.10.0/src/PHPMailer.php'; //El archivo principal con la clase PHPMailer.
require 'PHPMailer-6.10.0/src/SMTP.php'; //El que maneja la comunicación SMTP con tu servidor de correo.

// 🚫 Validar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "<script>alert('Acceso no válido.'); window.history.back();</script>";
    exit;
/*Recuarda que en esta parte,  $_SERVER['REQUEST_METHOD'] ya viene definida por PHP, no la creas tú.
Es una variable superglobal —igual que $_POST, $_GET, $_SESSION, etc.— y PHP la llena automáticamente cada vez que alguien hace una petición HTTP (por ejemplo, cuando alguien abre o envía un formulario a tu página).

El operador !== significa “es diferente y además no es del mismo tipo”.
Entonces la condición completa se lee así:

“Si la petición no fue hecha con el método POST...”

Que pasa si no fue POST 

Muestra una alerta en el navegador que dice “Acceso no válido.”

Luego, con window.history.back(), regresa al usuario a la página anterior.

exit; detiene completamente la ejecución del script para que nada más se ejecute después.

*/
}

// 🧾 Datos del formulario
$usuario = $_SESSION['user'];
$linea = $_POST['linea'] ?? '';
$estacion = $_POST['estacion'] ?? '';
$descripcion = $_POST['descripcion'] ?? ''; //?? '' es el operador null coalescing: si la variable no existe, usa una cadena vacía ('').

// ⚠️ Validaciones básicas
if (empty($linea) || empty($estacion) || empty($descripcion)) {
    echo "<script>alert('Faltan datos requeridos.'); window.history.back();</script>";
    exit;
}

// ✅ Insertar registro en la base de datos
$sqlInsert = "
    INSERT INTO Fallas (Usuario, Linea, Estacion, Descripcion, FechaHoraFalla)
    OUTPUT INSERTED.ID, INSERTED.FechaHoraFalla
    VALUES (?, ?, ?, ?, GETDATE())
";
/*
INSERT INTO Fallas (...) → agrega una nueva fila a la tabla Fallas.

OUTPUT INSERTED.ID → devuelve el ID autogenerado de esa nueva fila (solo funciona en SQL Server).

VALUES (?, ?, ?, ?, GETDATE()) → usa parámetros (?) para evitar inyecciones SQL.

GETDATE() → GETDATE() es una función de SQL Server.
Esto significa que la fecha que se guarda en la tabla proviene del servidor SQL, no del equipo del usuario ni del servidor web.
Entonces el campo FechaHoraFalla guarda la fecha y hora del SQL Server, que depende de la zona horaria configurada ahí.
*/

$params = [$usuario, $linea, $estacion, $descripcion]; //$params sustituye los ? en orden
$stmt = sqlsrv_query($conn, $sqlInsert, $params);

if ($stmt === false) { //Si la consulta falla, detiene la ejecución (die()) y muestra los errores de SQL Server en pantalla.
    die('<pre>' . print_r(sqlsrv_errors(), true) . '</pre>');
}

$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
$id = $row['ID'];//Gracias al OUTPUT INSERTED.ID, el resultado del query contiene una fila con el ID recién creado. Esta parte lo extrae para incluirlo en el correo.
$fecha = $row['FechaHoraFalla']; //// 👈 la fecha del SQL Server

if ($fecha instanceof DateTime) {
    $fecha = $fecha->format('Y-m-d H:i:s');
}

// 🔧 Asignar correos por línea
$destinatarios = [];

switch ($linea) {
    // ======== FORD / NISSAN ========
    case 'FORD/NISSAN Linea 1':
    case 'FORD/NISSAN Linea 2':
    case 'FORD/NISSAN Linea 3':
    case 'FORD/NISSAN Linea 4':
    case 'FORD/NISSAN Linea 6':
    case 'FORD/NISSAN Linea 10':
    case 'FORD/NISSAN Linea 11':
    case 'FORD/NISSAN Linea 12':
    case 'FORD/NISSAN Linea 13':
        $destinatarios = ['gyeverino@borgwarner.com'];
        break;
    case 'FORD/NISSAN Linea 14':
        $destinatarios = ['gyeverino@borgwarner.com', 'icommunication_ramos@borgwarner.com'];
        break;

    // ======== STELLANTIS ========
    case 'Stellantis Linea 5':
    case 'Stellantis Linea 7':
    case 'Stellantis Linea 8':
    case 'Stellantis Linea 18':
    case 'Stellantis Linea 19':
    case 'Stellantis Linea 20':
    case 'Stellantis Linea 23':
    case 'Stellantis Linea 24':
        $destinatarios = ['gyeverino@borgwarner.com'];
        break;

    // ======== GM ========
    case 'GM Linea 9':
    case 'GM Linea 15':
    case 'GM Linea 16':
    case 'GM Linea 21':
    case 'GM Linea 22':
    case 'GM Linea 25':
    case 'GM Linea 26':
        $destinatarios = ['gyeverino@borgwarner.com'];
        break;

    // ======== OTRAS ÁREAS ========
    case 'Lavadoras':
    case 'HIGH SPEED CORE BALANCING':
    case 'HERRAMIENTAS DE TORQUE INGERSOL':
    case 'Shaft & Wheel':
    case 'TURBINE HOUSING':
        $destinatarios = ['gyeverino@borgwarner.com'];
        break;

    default:
        $destinatarios = ['andon@borgwarner.com'];
        break;
}

// ✅ Configurar y enviar correo
$mail = new PHPMailer(true);
$mail->CharSet = 'UTF-8'; //sporte acentos, emojis, etc
$mail->Encoding = 'base64';//base64 es una forma de convertir texto o binarios a un formato seguro de solo letras y números, que puede ser enviado sin corromperse.

try {
    $mail->isSMTP();
    $mail->Host       = 'NCSASMTP.borgwarner.net'; //servidor de correo interno
    $mail->SMTPAuth   = false; //no requiere usuario/contraseña (probablemente es una red corporativa segura).
    $mail->Port       = 25; //estándar SMTP sin cifrado (interno).

    $mail->setFrom('andon@borgwarner.com', 'Andon System');

    foreach ($destinatarios as $correo) { //añade el destinatario
        $mail->addAddress($correo);
    }

    // Copia oculta de control
    $mail->addBCC('gyeverino@borgwarner.com');

    // Asunto y cuerpo
    $mail->isHTML(true);
    $mail->Subject = '🚨 Nueva Falla Registrada - ' . $linea;
    $mail->Body = "
    <h3>🚨 Nueva Falla Registrada</h3>
    <p>Se ha registrado una nueva falla:</p>
    <table border='1' cellpadding='6' cellspacing='0' style='border-collapse: collapse; font-family: Arial;'>
        <tr><td><b>ID</b></td><td>$id</td></tr>
        <tr><td><b>Línea</b></td><td>$linea</td></tr>
        <tr><td><b>Estación</b></td><td>$estacion</td></tr>
        <tr><td><b>Descripción</b></td><td>$descripcion</td></tr>
        <tr><td><b>Registró</b></td><td>$usuario</td></tr>
        <tr><td><b>Fecha</b></td><td>$fecha</td></tr> 
    </table> 
    <p><i>Favor de dar seguimiento a esta falla en el sistema Andon.</i></p>
    "; //En esta parte revisar si se esta mandando bien la fecha. 

    $mail->send();

    // 💾 Guardar el Message-ID real generado por el servidor SMTP
    $messageId = $mail->getLastMessageID();
    if ($messageId) {
        $sqlMsgId = "UPDATE Fallas SET MessageID = ? WHERE ID = ?";
        sqlsrv_query($conn, $sqlMsgId, [$messageId, $id]);
    }

    echo "<script>alert('✅ Falla registrada y correo enviado correctamente.'); window.location='listar.php';</script>";

} catch (Exception $e) {
    echo "<script>alert('⚠️ Falla registrada, pero el correo no se pudo enviar. Error: {$mail->ErrorInfo}'); window.location='listar.php';</script>";
}
?>
