<?php //inicio de sesion PHP
session_start(); //inicia o continúa una sesión PHP para almacenar variables en $_SESSION. Esto permite mantener información del usuario mientras navega entre páginas.

// 🔒 Encabezados para evitar cache del navegador
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// Mostrar mensaje si la sesión expiró
if (isset($_GET['expired'])) { //$_GET es una variable superglobal predefinida en PHP, igual que $_SESSION, pero con otro propósito
/*isset() → verifica si existe esa variable. Si existe, imprime un mensaje en color rojo avisando que la sesión caducó.

Almacena datos enviados en la URL mediante el método GET.  

Cómo funciona $_GET['expired']:
Cuando haces algo como: header("Location: login.php?expired=1"); Que esta en mi sesion.php
Estás redirigiendo a login.php y pasando un parámetro expired en la URL.
PHP automáticamente coloca este valor en la superglobal $_GET:
$_GET['expired']; // contiene '1'
or eso, luego puedes hacer:

if (isset($_GET['expired'])) {
    echo "Tu sesión expiró";
}
Significa: “si en la URL hay un parámetro llamado expired, muestra el mensaje”. 
*/
    echo "<p style='color:red;'>Tu sesión expiró, vuelve a iniciar sesión.</p>"; // Si existe, imprime un mensaje en color rojo avisando que la sesión caducó.
}


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// 🔒 Si ya existe sesión activa, redirige automáticamente
if (isset($_SESSION['user'])) {  //Comprueba si la sesión ya tiene el usuario ($_SESSION['user']).
    header("Location: formulario.php"); //Si ya está logueado, lo redirige automáticamente a la página interna formulario.php.
    exit; //detiene el script
}

$error = ''; //Variable para almacenar mensajes de error de login. Inicialmente está vacía, se llenará si el usuario pone mal credenciales.

//Verificar si envio el formulario. 
if ($_SERVER['REQUEST_METHOD'] == 'POST') { //$_SERVER variable superglobal que contiene información del servidor y del entorno HTTP.

/* $_SERVER['REQUEST_METHOD'] → Método HTTP usado por el navegador al hacer la petición (GET, POST, PUT, etc.)
En este caso es == 'POST' → significa: “si la página fue enviada mediante el método POST”.
Esto evita que el código de login se ejecute si alguien solo abre la página sin enviar el formulario.
//POST es uno de los métodos HTTP que usan los navegadores para enviar datos a un servidor.
Se usa principalmente cuando quieres enviar información que no debe aparecer en la URL, como contraseñas, formularios o archivos.
Comparación == 'POST' → asegura que este bloque solo se ejecute cuando el usuario envía el formulario, no cuando solo carga la página.
Esto evita errores o accesos no deseados si alguien entra directamente a login.php sin enviar datos. */

    $username = $_POST['username']; //$_POST → superglobal predefinida que contiene datos enviados desde un formulario con method="POST"
    $password = $_POST['password'];//'username' y 'password' → son los nombres de los inputs en el HTML. Ahora $username y $password contienen los valores que el usuario escribió.

    // Servidor y base de AD
    $ldap_server = "ldap://AZRUSE2DOM2.global.borgwarner.net";  //$ldap_server → dirección del servidor Active Directory (LDAP).
    $ldap_dn_base = "DC=global,DC=borgwarner,DC=net"; //$ldap_dn_base → base DN (“Distinguished Name”) que define desde dónde buscar usuarios en el directorio. La variable $ldap_conn guarda la conexión.
//Esto es necesario para que PHP sepa dónde autenticar al usuario.

    // Conexión al servidor  LDAP
    $ldap_conn = ldap_connect($ldap_server); //ldap_connect($ldap_server) → función integrada de PHP (requiere extensión LDAP) que crea una conexión con el servidor LDAP.
    ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3); //ldap_set_option(..., LDAP_OPT_PROTOCOL_VERSION, 3) → establece la versión del protocolo LDAP a usar (la versión 3 es la estándar moderna).

    // Usuario completo para bind
    $ldap_user = $username . "@borgwarner.com"; //Esto es necesario porque AD espera el usuario completo en formato UPN para autenticar.

    if (@ldap_bind($ldap_conn, $ldap_user, $password)) {//ldap_bind() → función de PHP que intenta “ligar” la sesión con las credenciales del usuario en el AD. El @ antes de la función → silencia errores (no mostrará warnings si falla la conexión o las credenciales son incorrectas).
        // Login correcto
        $_SESSION['user'] = $username; // Guardar usuario en sesión
        $_SESSION['ultimo_acceso'] = time(); // Timestamp solo después de login válido
        header("Location: formulario.php"); //si es correcto lo lleva al formulario. 
        exit;
    } else {
        $error = "Usuario o contraseña incorrecta.";
    }
}
?>

<!DOCTYPE html>  <!-- indica que es un documento HTML5.  -->
<html lang="es"> <!-- define el idioma de la página (español). -->
<head> <!-- el head es una sección especial del documento donde se colocan metadatos, configuraciones y enlaces a recursos externos, pero no contiene contenido visible directamente en la página. -->
    <meta charset="UTF-8"> <!--Define la codificación de caracteres del documento (UTF-8 es estándar moderno y soporta acentos, emojis, caracteres especiales).
                           Esto evita que los textos aparezcan con símbolos extraños. -->
    <title>Login</title> <!-- Define el título de la página, que aparece en la pestaña del navegador.
                          No aparece dentro del contenido de la página directamente. -->
    <link rel="stylesheet" href="loginstyle.css"> <!-- Conecta la página con un archivo de hoja de estilos CSS externa (loginstyle.css) para definir cómo se ve la página.
                                                   rel="stylesheet" indica que es una hoja de estilo.
                                                   href="loginstyle.css" es la ruta del archivo CSS. -->
</head> <!-- se acaba el head -->
<body> <!-- es lo que se ve en el navegador -->
    <div class="login-container"> <!-- <div> es un contenedor genérico que agrupa elementos.
class="login-container" le da un nombre de clase para poder aplicar estilos CSS desde loginstyle.css.
En tu caso, sirve para centrar y dar formato al formulario de login. -->
        <h2>Login</h2> <!-- <h2> es un encabezado de segundo nivel, más pequeño que <h1>. Muestra el título “Login” dentro del contenedor, visible en la página. --> 
        <?php if ($error !== '') echo "<p>$error</p>"; 
  /*Esto es PHP embebido en HTML.

if ($error !== '') comprueba si la variable $error contiene algún mensaje (por ejemplo, si el login falló).

echo "<p>$error</p>"; imprime un párrafo <p> con el mensaje de error dentro del HTML.

Resultado: si el usuario pone mal usuario o contraseña, verá el mensaje en la página
  
  */
  ?>
        <form method="POST"> <!--<form> crea un formulario interactivo.
                             method="POST" indica que los datos del formulario se enviarán al servidor usando el método POST, que no muestra los datos en la URL (ideal para contraseñas). -->
            <label for="username">Usuario de BW👤:</label> <!--<label> es una etiqueta descriptiva para un input; for="username" conecta la etiqueta con el input correspondiente. -->
            <input type="text" name="username" required><br> <!-- <input type="text" name="username" required> crea un campo de texto donde el usuario escribe su nombre.
                                                                 name="username" es el nombre que PHP usará para recibir el dato ($_POST['username']).
                                                                 required indica que el campo no puede quedar vacío.
                                                                <br> inserta un salto de línea para separar visualmente los campos. -->
            <label for="password">Contraseña 🔒:</label> <!-- Similar al anterior, pero con <input type="password"> que oculta lo que se escribe con puntos o asteriscos.
                                                            name="password" permitirá acceder a la contraseña en PHP con $_POST['password']. -->
            <input type="password" name="password" required><br>

            <button type="submit">Entrar</button> <!--<button> crea un botón interactivo.
                                                   type="submit" indica que al presionarlo se enviarán los datos del formulario al servidor (al mismo archivo si no se define action).
                                                Texto “Entrar” aparece en el botón. -->
        </form>
    </div>
</body>
</html>
<!-- </form> cierra el formulario.

</div> cierra el contenedor de login.

</body> cierra el contenido visible de la página.

</html> cierra el documento HTML completo. --> 