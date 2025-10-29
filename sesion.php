<?php //inicia mi codigo PHP

session_start(); //inicia la sesión de PHP o continúa la sesión existente.

/*session_start() Es una función nativa del núcleo de PHP (es decir, viene instalada por defecto) que sirve para inicializar o reanudar una sesión en tu aplicación web.
Que hace session_start() ?
1. PHP busca si el navegador ya tiene una cookie con un ID de sesión (PHPSESSID).
2. Si no la tiene, crea una nueva sesión en el servidor y genera un nuevo ID.
3. Si ya existe, PHP recupera las variables de sesión asociadas a ese ID.
4. Después de eso, puedes usar la superglobal $_SESSION como si fuera un arreglo normal.
*/

/*🔒 Encabezados para evitar cache en todas las páginas protegidas o en el navegador
Estos encabezados HTTP le dicen al navegador que no guarde en caché la página. Esto es crítico en páginas protegidas porque evita que un usuario pueda:
Presionar el botón “atrás” después de cerrar sesión y ver contenido protegido.
Usar versiones antiguas de la página con información sensible. */
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0"); //Evita chache moderno.Los navegadores modernos (como Chrome y Edge) usan “Cache-Control”.
header("Cache-Control: post-check=0, pre-check=0", false); //Compatibilidad con navegadores antiguos. Los navegadores antiguos (como IE6 o IE7) usaban “Pragma: no-cache” y “Expires: 0”.
header("Pragma: no-cache"); //HTTP/1.0, compatibilidad legacy.
header("Expires: 0");  //indica que la página ya expiró.
 
// Tiempo máximo de sesión en segundos (15 minutos)
$tiempoMaximo = 900; //Definir el valor de la variable tiempoMaximo a 900 segundos. 


// Verificar si hay sesión activa
if (!isset($_SESSION['user'])) { //isset () es una función de PHP que sirve para verificar si una variable existe o esta definida y no es null. El signo ! significa “NO” o “negación lógica”.

/* $_SESSION es una variable superglobal de PHP (un arreglo especial disponible en todas las páginas después de session_start()).
$_SESSION['user'] = $username; El programa guarda el usuario que inicio sesion. 
*/
    header("Location: login.php"); //Si no existe una variable llamada 'user' dentro de la sesión...” Redirige al formulario de login para que el usuario se autentique otra vez.
    exit; //detiene la ejecución del script inmediatamente para evitar mostrar contenido protegido.
}

// Revisar inactividad
if (isset($_SESSION['ultimo_acceso'])) { //Comprueba si ya existe la varible de sesión $_SESSION['ultimo_acceso'].
/* Esto tiene sentido, porque solo existirá después de que el usuario haya iniciado sesión exitosamente (recuerda que se define en tu login.php cuando el login es correcto).
$_SESSION['ultimo_acceso'] = time(); 
Entonces este if se asegura de no intentar calcular nada si el usuario todavía no tiene esa variable (por ejemplo, si no ha iniciado sesión o es la primera carga).
*/

$inactividad = time() - $_SESSION['ultimo_acceso']; //calcula cuánto tiempo ha pasado desde el último acceso guardado en la variable $inactividad. 
/*
time() → devuelve la hora actual en segundos desde el 1 de enero de 1970. (esto se llama timestamp Unix).
$_SESSION['ultimo_acceso'] → guarda el timestamp del último acceso registrado (por ejemplo, cuando inició sesión o cuando se actualizó la sesión en una nueva página).
*/

    if ($inactividad > $tiempoMaximo) { //si el tiempo de inactividad supera el límite:
        session_unset(); //elimina todas las variables de sesión.
        session_destroy(); //destruye la sesión por completo.
        header("Location: login.php?expired=1"); 
/*1. la función header() no muestra texto en pantalla. Lo que hace es enviar encabezados HTTP al navegador antes de que se mande cualquier contenido.
     le dice al navegador:“No muestres nada aquí. En lugar de eso, haz una nueva solicitud a esta URL: login.php?expired=1.”

 2. El navegador recibe una redirección (HTTP 302)
Internamente, el servidor web (Apache, Nginx, etc.) devuelve al navegador una respuesta HTTP parecida a:
HTTP/1.1 302 Found
Location: login.php?expired=1
Content-Type: text/html

3. El navegador obedece la redireccion. (Chrome, Edge, Firefox, etc.)
Entonces automáticamente realiza una nueva petición HTTP GET a esa dirección:
GET /login.php?expired=1 HTTP/1.1
Host: tu-sitio.com

Es decir, ahora carga login.php pero con un parámetro en la URL:
?expired=1
*/    

exit; //SALIR DEL IF
    }
}

// Actualizar timestamp de último acceso
$_SESSION['ultimo_acceso'] = time(); //Cada vez que el usuario carga la página, se actualiza el último acceso. 
/*Esto reinicia el contador de inactividad, por lo que mientras el usuario esté activo, la sesión no caduca.

Cada vez que el usuario carga una página protegida (por ejemplo, formulario.php, dashboard.php, etc.), esa línea guarda el momento exacto (en segundos) en que el usuario hizo esa acción.

  time() → devuelve el timestamp actual (número de segundos desde el 1 de enero de 1970).
  $_SESSION['ultimo_acceso'] → guarda ese número como referencia.
*/

/* NOTAS:
$_SESSION es una variable especial predefinida en PHP, llamada superglobal.

Es un arreglo asociativo que PHP ya tiene listo para usar.

Su propósito: almacenar información del usuario mientras navega por varias páginas.
EJEMPLO :
$_SESSION['user'] = "Gilberto";
$_SESSION['ultimo_acceso'] = time();
Aquí estamos guardando dentro del arreglo _SESSION:

'user' → nombre del usuario

'ultimo_acceso' → timestamp de último acceso

Lo importante: esta variable ya existe cuando haces session_start(), no la tienes que definir tú.

*/
?>

