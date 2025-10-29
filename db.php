<?php //ABRE BLOQUE PHP, CUALQUIER COSA DESPUES SE EJECUTA COMO PHP

//Parametros de conexion
$serverName = "RAMLTOFF0002"; // Host o servidor. 
// Detalles importantes: si tu SQL Server es una instancia con nombre debes usar "HOST\\INSTANCE" (doble \\ en la cadena PHP), p. ej. "RAMLTOFF0002\\SQLEXPRESS".
//Si usas un puerto no estándar o necesitas forzar TCP, usa el prefijo tcp: y la coma del puerto: "tcp:RAMLTOFF0002,1433".

$connectionOptions = [ //array asociativo con opciones para sqlsrv_connect.
    "Database" => "AndonSystem", //Base de datos
    "Uid" => "sa",     // reemplaza con tu usuario de SQL Server
    "PWD" => "#1password", // reemplaza con tu contraseña de SQL Server
    "CharacterSet" => "UTF-8" //"UTF-8" — define la codificación para la conexión (muy importante para nvarchar y caracteres especiales).
];

// Conexión
$conn = sqlsrv_connect($serverName, $connectionOptions); //Llama la funcion de la extension sqlsrv para abrir una conexion en SQL. Recibe dos parametros: El nombre del servidor y array asociado con opciones.

// Verificar conexión
if (!$conn) { //Evalua si $conn es falso o vacio, ! significa no o no cumplir. si conn es no hace lo demas.
    die(print_r(sqlsrv_errors(), true)); //sqlsrv_errors() devuelve un array con información de los errores que el driver generó.
/*
die() es una funcion (alias de exit()) en PHP que sirve para detener por completo la ejecucion del script en ese punto. Es decir, cuando PHP llega a die(), ya no ejecuta nada más después.
Además, puedes mostrar un mensaje o variable antes de detener el programa.

print_r() se usa para imprimir estructuras complejas (como arrays u objetos) de forma legible. Sirve mucho para depuración (debug).
El segundo parámetro (true) hace que no lo imprima directamente, sino que devuelva el resultado como texto.

sqlsrv_errors() es una función de PHP (de la extensión SQLSRV, usada para conectarte a Microsoft SQL Server) que devuelve un array de errores si hubo un problema en la conexión o consulta.
*/
}

/*Recomendaciones de seguridad inmediatas
Nunca uses sa en producción. Crea un usuario con los permisos mínimos necesarios (INSERT, UPDATE, SELECT en la tabla Fallas, etc.).
No hardcodees credenciales en archivos dentro del htdocs/public. Usa:
-variables de entorno (getenv()), o
-archivo de configuración fuera del web root, o
-.env manejado por vlucas/phpdotenv si usas composer.

No muestres errores SQL al usuario. Registra errores con error_log() y muestra mensajes genéricos.
Usa consultas parametrizadas (prepared statements) siempre para evitar SQL injection.
Usa HTTPS en tu aplicación web para proteger datos en tránsito. 


/*CharacterSet y manejo de cadenas (unicode)
CharacterSet => "UTF-8" le indica al driver que los datos de texto se envían/reciben en UTF-8. Esto es crítico cuando trabajas con nvarchar, nvarchar(max) y caracteres especiales (acentos, emojis, etc.).
Si no configuras correctamente, verás caracteres raros (??? o signos de interrogación) o pérdida de datos.


Formas de especificar el servidor / puerto / instancia
Host simple: "RAMLTOFF0002"
Instancia con nombre: "RAMLTOFF0002\\SQLEXPRESS" (en PHP string, doble backslash)
Forzar TCP y puerto: "tcp:RAMLTOFF0002,1433"

Conexión persistente / pooling
SQL Server y la extensión sqlsrv soportan pooling en ciertos escenarios; pero para la mayoría de apps con Apache + mod_php/XAMPP, la conexión se abre por petición PHP y se cierra al final. Evita conexiones persistentes sin control.
Si tienes alta carga, considera pooling a nivel de servidor o implementar una API de backend que administre conexiones (o uso de FastCGI / PHP-FPM).

Manejo de tipos en PHP (cómo llegan los datos)
nvarchar / nvarchar(max) → llegan como strings en PHP; con CharacterSet correcto, vienen en UTF-8.
datetime → muchas veces llegan como strings (por seguridad asume string) y la forma segura de tratarlos es: new DateTime($row['FechaHoraFalla']) si necesitas manipular la fecha en PHP. (Siempre es seguro parsear a DateTime).
decimal(18,2) → puede venir como string o float. Si necesitas precisión matemática exacta (finanzas), no uses float; usa bcmath o mantén valores como strings y opera en enteros menores (p. ej. centavos).
IDENTITY (ID autoincrement) → para obtener el último id insertado usa SCOPE_IDENTITY() o OUTPUT INSERTED.ID; no confíes en rowcount para obtener el id.

Diferencias prácticas: sqlsrv vs pdo_sqlsrv
sqlsrv:
API procedural (sqlsrv_connect, sqlsrv_query, sqlsrv_prepare).
Buen soporte nativo para tipos de SQL Server.
El control de parámetros usa arrays especiales para tipos/flags.

pdo_sqlsrv:
API PDO: $pdo = new PDO(...); $stmt = $pdo->prepare(...); $stmt->execute([...]);
Mejores excepciones (PDOException) y errores, fetch modes unificados.
Portabilidad: si en el futuro cambias DB (MySQL, Postgres) solo cambias DSN y algunos detalles.
Recomendación: si estás empezando y quieres mejor estructura, manejo de excepciones, y prepared statements limpios, usa pdo_sqlsrv. Si ya usas sqlsrv y te sientes cómodo, continúa pero sigue patrones seguros.

*/

?>