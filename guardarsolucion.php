<?php
require 'sesion.php'; //requiere el inicio de sesion PHP
include 'db.php'; //Requiere conexion a Bases De Datos

// Cargar PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-6.10.0/src/Exception.php';
require 'PHPMailer-6.10.0/src/PHPMailer.php';
require 'PHPMailer-6.10.0/src/SMTP.php';

// 🚫 Validar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
    echo "<script>alert('Acceso no válido.'); window.history.back();</script>";
    exit;
}

// 🧾 Datos del formulario Leer y normalizar datos entrantes
$id = isset($_POST['id']) ? intval($_POST['id']) : 0; //$_POST['id'] / $_POST['solucion'] provienen del formulario (nombre de inputs).
/* 1.  $_POST['id']
Este es un array superglobal que PHP crea automáticamente cuando llega un formulario enviado con método POST (por ejemplo, <form method="POST">).
$_POST['id'] intenta obtener el valor del campo llamado id que el formulario envió.
Ejemplo:

<input type="hidden" name="id" value="45">

Entonces al procesar:

$_POST['id']; // vale "45"

2.  isset($_POST['id'])
isset() verifica si una variable está definida y no es nula.
En este caso, pregunta: “¿Existe la variable $_POST['id']? ¿El formulario realmente envió ese campo?”

3. intval($_POST['id'])
Convierte el valor a un entero (integer).
Ejemplo:

intval("45") // → 45
intval("abc") // → 0
intval(null) // → 0

Esto se usa para asegurar que el valor es numérico, especialmente si se va a usar en consultas SQL o cálculos.

4. Operador ternario ?

Esa parte:

isset($_POST['id']) ? intval($_POST['id']) : 0

significa literalmente:

Si existe $_POST['id'], usa su valor convertido a entero.
Si no existe, usa 0 por defecto.

*/

$usuarioSolucion = $_SESSION['user']; // Usuario actual que resuelve la falla

$solucion = isset($_POST['solucion']) ? trim($_POST['solucion']) : '';  //es muy parecida en estructura a la que analizamos antes, solo que en lugar de convertir a número (intval()), aquí usa trim(), y el valor por defecto no es 0, sino una cadena vacía ''.

// ⚠️ Validaciones básicas
if ($id <= 0 || $solucion === '') { //Comprobar que id sea válido y que solucion no sea vacío. Si falla, se vuelve atrás.
    echo "<script>alert('Faltan datos requeridos.'); window.history.back();</script>"; //Considera validar la longitud máxima y sanitizar HTML si vas a mostrar $solucion en páginas web.
    exit;
}

// ✅ Actualizar registro en la base de datos
$sqlUpdate = "
    UPDATE Fallas
    SET 
        Solucion = ?,
        FechaHoraSolucion = GETDATE(),
        TiempoFalla = DATEDIFF(MINUTE, FechaHoraFalla, GETDATE()),
        UsuarioSolucion = ?
        OUTPUT INSERTED.FechaHoraSolucion
    WHERE ID = ?
";
$paramsUpdate = [$solucion, $usuarioSolucion, $id];
$stmtUpdate = sqlsrv_query($conn, $sqlUpdate, $paramsUpdate);

if ($stmtUpdate === false) {
    die('<pre>' . print_r(sqlsrv_errors(), true) . '</pre>');
}

// Obtener la fecha exacta de solución devuelta por SQL Server
$rowUpdate = sqlsrv_fetch_array($stmtUpdate, SQLSRV_FETCH_ASSOC);
$fechaSolucionSQL = $rowUpdate['FechaHoraSolucion']->format('Y-m-d H:i:s');

// ✅ Obtener información completa de la falla (incluyendo MessageID)
$sqlSelect = "
    SELECT Usuario, Linea, Estacion, Descripcion, FechaHoraFalla, MessageID
    FROM Fallas
    WHERE ID = ?
";
$stmtSelect = sqlsrv_query($conn, $sqlSelect, [$id]);
$falla = sqlsrv_fetch_array($stmtSelect, SQLSRV_FETCH_ASSOC);

if (!$falla) {
    echo "<script>alert('No se encontró la falla.'); window.location='listar.php';</script>";
    exit;
}

// 📋 Datos de la falla
$usuarioFalla = $falla['Usuario'];
$linea = $falla['Linea'];
$estacion = $falla['Estacion'];
$descripcion = $falla['Descripcion'];
$fechaFalla = $falla['FechaHoraFalla']->format('Y-m-d H:i:s');
$messageIdOriginal = trim($falla['MessageID']); // 🔹 ID real del correo original

// 🔧 Asignar correos por línea
$destinatarios = [];

switch ($linea) {
    case 'FORD/NISSAN Linea 1':
    case 'FORD/NISSAN Linea 2':
    case 'FORD/NISSAN Linea 3':
    case 'FORD/NISSAN Linea 4':
    case 'FORD/NISSAN Linea 6':
    case 'FORD/NISSAN Linea 10':
    case 'FORD/NISSAN Linea 11':
    case 'FORD/NISSAN Linea 12':
    case 'FORD/NISSAN Linea 13':
    case 'FORD/NISSAN Linea 14':
    case 'Stellantis Linea 5':
    case 'Stellantis Linea 7':
    case 'Stellantis Linea 8':
    case 'Stellantis Linea 18':
    case 'Stellantis Linea 19':
    case 'Stellantis Linea 20':
    case 'Stellantis Linea 23':
    case 'Stellantis Linea 24':
    case 'GM Linea 9':
    case 'GM Linea 15':
    case 'GM Linea 16':
    case 'GM Linea 21':
    case 'GM Linea 22':
    case 'GM Linea 25':
    case 'GM Linea 26':
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

// ✅ Configurar PHPMailer
$mail = new PHPMailer(true);
$mail->CharSet = 'UTF-8';
$mail->Encoding = 'base64';

try {
    $mail->isSMTP();
    $mail->Host       = 'NCSASMTP.borgwarner.net';
    $mail->SMTPAuth   = false;
    $mail->Port       = 25;

    $mail->setFrom('andon@borgwarner.com', 'Andon System');

    foreach ($destinatarios as $correo) {
        $mail->addAddress($correo);
    }

    $mail->addBCC('gyeverino@borgwarner.com');

    // 📩 Vincular el correo a la cadena original
    if (!empty($messageIdOriginal)) {
        // Outlook y Exchange agrupan los hilos cuando estos headers coinciden
        $mail->addCustomHeader('In-Reply-To', $messageIdOriginal);
        $mail->addCustomHeader('References', $messageIdOriginal);
    }

    $mail->isHTML(true);
    $mail->Subject = '✅ Falla Solucionada - ' . $linea;
    $mail->Body = "
    <h3>✅ Falla Solucionada</h3>
    <p>El siguiente registro ha sido cerrado:</p>
    <table border='1' cellpadding='6' cellspacing='0' style='border-collapse: collapse; font-family: Arial;'>
        <tr><td><b>ID</b></td><td>$id</td></tr>
        <tr><td><b>Línea</b></td><td>$linea</td></tr>
        <tr><td><b>Estación</b></td><td>$estacion</td></tr>
        <tr><td><b>Descripción</b></td><td>$descripcion</td></tr>
        <tr><td><b>Registró</b></td><td>$usuarioFalla</td></tr>
        <tr><td><b>Solucionó</b></td><td>$usuarioSolucion</td></tr>
        <tr><td><b>Solución</b></td><td>$solucion</td></tr>
        <tr><td><b>Fecha Falla</b></td><td>$fechaFalla</td></tr>
        <tr><td><b>Fecha Solución</b></td><td>$fechaSolucionSQL</td></tr>
    </table>
    <p><i>Este mensaje es una respuesta automática al correo original de la falla #$id.</i></p>
    ";

    $mail->send();

    // ✅ Guardar también el Message-ID de este correo por si deseas rastrear la conversación
    $newMessageId = $mail->getLastMessageID();
    if ($newMessageId) {
        $sqlMsg = "UPDATE Fallas SET MessageID = ? WHERE ID = ?";
        sqlsrv_query($conn, $sqlMsg, [$newMessageId, $id]);
    }

    echo "<script>alert('✅ Solución registrada y correo enviado como respuesta real al hilo original.'); window.location='listar.php';</script>";

} catch (Exception $e) {
    echo "<script>alert('⚠️ Solución guardada, pero el correo no se pudo enviar. Error: {$mail->ErrorInfo}'); window.location='listar.php';</script>";
}
?>
