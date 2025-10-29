<?php
require 'sesion.php'; //Incluye y ejecuta sesion.php. Si ese archivo no existe o falla, PHP detiene la ejecución (diferencia con include).
$usuario_actual = $_SESSION['user']; //Obtiene el nombre almacenado de la sesion, esta variable es creada por mi para guardar el usuario de la sesion.
include 'db.php';  // conexion a base de datos 

// --- Buscar falla por ID ---
$fallaEncontrada = null; //Inicializa la variable para almacenar los datos de una falla encontrada.
if (isset($_POST['buscar_id'])) { //con la funcion isset revisar si es una variable y no es nulo, Recuerda que el metodo $_POST es una variable superglobal en PHP, que siempre existe y contiene todos los datos enviados por un formulario HTML con el metodo POST
    $id = $_POST['buscar_id']; //Captura el ID enviado por el usuario. Variable creada llamada $id. 
    $sql = "SELECT ID, Linea, Estacion, Descripcion, FechaHoraFalla
            FROM Fallas WHERE id = ?"; //El ? es un placeholder (marcador) o “espacio reservado” para el valor real. Esto para evitar que se rompa mi BD y salga una inyeccion SQL.
    $params = [$id]; // Parámetros que reemplazarán el "?" en la consulta. $params = arreglo de parámetros seguros que se sustituyen por orden en los ?. Es una variable definida por mi. 
    $stmt = sqlsrv_query($conn, $sql, $params); //Esta función de PHP ejecuta una consulta en SQL Server (usando la extensión sqlsrv).
    /*
    $conn → la conexión a la base de datos (creada en db.php).
    $sql → la consulta con los ?.
    $params → los valores que reemplazarán los ?.
*/
    if ($stmt && $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $fallaEncontrada = $row; 
    /*   Paso 1: Verificar que la consulta sí corrió
        if ($stmt && ...) → confirma que la consulta no falló.

        Paso 2: Extraer la fila
           sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)
           Toma la primera fila de los resultados.
           Devuelve un arreglo asociativo: las claves son los nombres de las columnas.

        Paso 3: Guardarlo para usarlo después
           $fallaEncontrada = $row;
           Después, en la parte del HTML, usas:
           <?= $fallaEncontrada['Descripcion'] ?>
    */
    }
}
?> 

<!DOCTYPE html> <!-- Indica que el documento usa HTML5 (la versión moderna del lenguaje)  -->
<html lang="es"> <!-- ideoma espanol -->
<head> <!--configuración invisible (meta, título, estilos) -->
    <meta charset="UTF-8">
    <title>Fallas IOT</title>
    <link rel="stylesheet" href="formstyle.css?v=<?php echo filemtime('formstyle.css'); ?>"> 
<!-- Importa tu archivo CSS externo (formstyle.css) para aplicar el diseño.
  El truco está en ?v=<php echo filemtime('formstyle.css'); >:
  filemtime() obtiene la fecha/hora de última modificación del archivo CSS.
  Se concatena como un parámetro de versión (?v=1729303930 por ejemplo).
  Así, cuando actualizas el CSS, el navegador detecta que cambió el archivo y no usa la versión en caché.
  ...Esto se llama cache-busting (evitar el caché antiguo).
-->
</head>
<body> <!-- Todo lo visible -->
    <div class="form-wrapper">  <!-- <div class="form-wrapper"> es un contenedor principal (div = bloque de agrupación) para centrar o estilizar el contenido. -->
        <!-- Encabezado con logo y título -->
        <div class="form-header"> <!-- agrupa el logo y el título. -->
            <img src="borgwarner_logo.jpg" alt="BorgWarner Logo"> <!-- muestra el logo; el atributo alt es texto alternativo (importante para accesibilidad). -->
            <h2>Fallas IOT Turbo 🖥️🛠️</h2> <!-- <h2> → título principal visible en la página. -->
        </div>

        <!-- Mensaje de bienvenida y logout -->
        <div class="user-info"> <!-- agrupa la bienvenida y el botón de cerrar sesión. -->
            <p>Bienvenido, <strong><?= htmlspecialchars($usuario_actual) ?></strong></p>
    <!-- htmlspecialchars($usuario_actual)
              htmlspecialchars() es vital:
              Convierte caracteres especiales (<, >, ") en versiones seguras (&lt;, &gt;…).
              Previene inyecciones HTML o XSS si el nombre del usuario tuviera código malicioso.
    -->
            <a class="logout" href="logout.php">Cerrar sesión 🔐</a> <!--enlace que manda al script que destruye la sesión. -->
        </div>

        <!-- Formulario de registro de fallas -->
        <form action="guardar.php" method="POST" class="form-container">
    <!-- Action="guardar.php" → indica a qué archivo se enviarán los datos al presionar Guardar.
         Method="POST" → define cómo se envían los datos (de forma oculta en el cuerpo HTTP, más segura que GET).
         Class="form-container" → se usa para aplicar estilos CSS.
 -->
            <label for="linea">Línea:</label> <!--<label for="linea"> → asocia el texto “Línea:” al <select> con id="linea". <select> → menú desplegable. required → hace obligatorio el campo.Cada <option> tiene: value (el dato que se envía a PHP) el texto visible (lo que ve el usuario).  -->
            <select name="linea" required id= "linea" required>
                <option value="">-- Selecciona --</option>
                <option value="FORD/NISSAN Linea 1">FORD/NISSAN Linea 1</option>
                <option value="FORD/NISSAN Linea 2">FORD/NISSAN Linea 2</option>
                <option value="FORD/NISSAN Linea 3">FORD/NISSAN Linea 3</option>
                <option value="FORD/NISSAN Linea 4">FORD/NISSAN Linea 4</option>
                <option value="FORD/NISSAN Linea 6">FORD/NISSAN Linea 6</option>
                <option value="FORD/NISSAN Linea 10">FORD/NISSAN Linea 10</option>
                <option value="FORD/NISSAN Linea 11">FORD/NISSAN Linea 11</option>
                <option value="FORD/NISSAN Linea 12">FORD/NISSAN Linea 12</option>
                <option value="FORD/NISSAN Linea 13">FORD/NISSAN Linea 13</option>
                <option value="FORD/NISSAN Linea 14">FORD/NISSAN Linea 14</option>
                <option value="Stellantis Linea 5">Stellantis Linea 5</option>
                <option value="Stellantis Linea 7">Stellantis Linea 7</option>
                <option value="Stellantis Linea 8">Stellantis Linea 8</option>
                <option value="Stellantis Linea 18">Stellantis Linea 18</option>
                <option value="Stellantis Linea 19">Stellantis Linea 19</option>
                <option value="Stellantis Linea 20">Stellantis Linea 20</option>
                <option value="Stellantis Linea 23">Stellantis Linea 23</option>
                <option value="Stellantis Linea 24">Stellantis Linea 24</option>
                <option value="GM Linea 9">GM Linea 9</option>
                <option value="GM Linea 15">GM Linea 15</option>
                <option value="GM Linea 16">GM Linea 16</option>
                <option value="GM Linea 21">GM Linea 21</option>
                <option value="GM Linea 22">GM Linea 22</option>
                <option value="GM Linea 25">GM Linea 25</option>
                <option value="GM Linea 26">GM Linea 26</option>
                <option value="Lavadoras">Lavadoras</option>
                <option value="HIGH SPEED CORE BALANCING">HIGH SPEED CORE BALANCING</option>
                <option value="HERRAMIENTAS DE TORQUE INGERSOL">HERRAMIENTAS DE TORQUE INGERSOL</option>
                <option value="Shaft & Wheel">Shaft & Wheel</option>
                <option value="TURBINE HOUSING">TURBINE HOUSING</option>

            </select> <!-- Este <select> empieza vacío, porque las estaciones dependen de la línea elegida. Luego el JavaScript (al final del archivo) lo llenará dinámicamente. -->
           <label for="estacion">Estación:</label>
            <select name="estacion" id="estacion" required>
            <option value="">-- Selecciona línea primero --</option>
            </select>


            <label for="descripcion">Descripción de la falla:</label> <!-- <textarea> permite escribir texto largo (multilínea). rows="4" define la altura inicial. required lo hace obligatorio. -->
            <textarea name="descripcion" rows="4" required></textarea>

            <button type="submit">Guardar</button> <!-- Envía el formulario a guardar.php vía POST. -->
        </form>

        <!-- Buscar falla por ID -->
        <div class="buscar-falla">
            <h3>Buscar falla registrada 🔎</h3>
            <form method="POST" class="form-container">
                <label for="buscar_id">ID de la falla:</label>
                <input type="number" name="buscar_id" required>
                <button type="submit">Buscar</button>
            </form>
    <!--Este formulario envía un nuevo POST a la misma página (formulario.php) sin action.
Cuando detecta $_POST['buscar_id'] en el bloque PHP de arriba, ejecuta el query de búsqueda y guarda el resultado en $fallaEncontrada.
  -->

            <!-- Mostrar resultado si se encontró -->
            <?php if ($fallaEncontrada): ?>
                <div class="resultado"> <!-- Nombre de la clase CSS -->
                    <h4>Información de la Falla</h4>
                    <p><strong>ID:</strong> <?= $fallaEncontrada['ID'] ?></p>
                    <p><strong>Línea:</strong> <?= $fallaEncontrada['Linea'] ?></p>
                    <p><strong>Estación:</strong> <?= $fallaEncontrada['Estacion'] ?></p>
                    <p><strong>Descripción:</strong> <?= $fallaEncontrada['Descripcion'] ?></p>
                    <p><strong>FechaHoraFalla:</strong> 
                           <?= $fallaEncontrada['FechaHoraFalla']  
                            ? $fallaEncontrada['FechaHoraFalla']->format('Y-m-d H:i:s.v') 
                            : 'Sin registrar' ?>
                    </p>

                    <!-- Formulario para registrar solución -->
                    <form action="guardarsolucion.php" method="POST" class="form-container">
                        <input type="hidden" name="id" value="<?= $fallaEncontrada['ID'] ?>">
                        <label for="solucion">Solución aplicada:</label>
                        <textarea name="solucion" rows="3" required></textarea>
                        <button type="submit">Guardar solución</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <a class="view-fallas" href="listar.php">Ver fallas registradas 🚨</a>
        <a href="graficas.php" class="view-fallas">Ver Reporte de Fallas 📊</a>
    </div>

<!-- JavaScript para estaciones dependientes de línea -->
    <script>
        const estacionesPorLinea = { //const estacionesPorLinea = { ... }; 
        // Crea un objeto literal en JavaScript (parecido a un “diccionario” o mapa)
        /* Cada clave es el texto de la opción de la línea ("FORD/NISSAN Linea 1") — debe coincidir exactamente con el value de tu <select name="linea">.
           Cada valor es un array de strings (las estaciones).
           const indica que la variable no será reasignada (pero puedes modificar su contenido).
        */
            "FORD/NISSAN Linea 1": ["Sto 10 A Torque a bearing y conector", "Sto 10 LK Prueba de fuga de bearing", "Sto 110 Ensamble de snap y journal beari","Sto 120 Ensamble de bearing con shaft", "Sto 130 Ensamble de circlip","Sto 140 Ensamble de compresor weel y tor","Sto 150 Prueba de fuga", "Sto 180 Torque en la nut de core"],
            "FORD/NISSAN Linea 2": ["Op. 210 C ", "Op. 210 D", "Op. 220", "Op. 230 ", "Op. 240", "Op. 240 D ", "Op. 250 A ", "Op. 250 B ", "Op. 250 C ", "Op. 250 ", "Op. 255 ", "Op. 250 F"],
            "FORD/NISSAN Linea 3": ["Op. 10 A Torque a bearing y conector de", "Op. 10 LK Prueba de fuga de bearing", "Op. 310 Ensamble de snap y journal beari", "Op. 320 Ensamble de bearing con shaft", "Op. 330 Ensamble de circlip", "Op. 340 Ensamble de compresor weel y tor", "Op. 350 Prueba de fuga", "Op. 380 Torque en la nut de core"],
            "FORD/NISSAN Linea 4": ["Op. 450 F ", "Op. 410 C ", "Op. 410 D ", "Op. 420 ", "Op. 430 ", "Op. 440 ", "Op. 440 D ", "Op. 450 A ", "Op. 450 B ", "Op. 450 C ", "Op. 450 ", "Op. 455 "],
            "FORD/NISSAN Linea 6": ["Op. 610 ", "Op. 620", "Op. 630 ", "Op. 640 A ", "Op. 640 D ", "Op. 650 A ", "Op. 650 B  ", "Op. 655 "],
            "FORD/NISSAN Linea 10": ["Op. 1010 Remache de name plate y ensambl", "Op. 1020 Ensamble de super core con turb", "Op. 1030 Ajuste de actuador ", "Op. 1040 Inspeccion final de turbo"],
            "FORD/NISSAN Linea 11": ["Op. 1110 A Torque a trush bearing en cor", "Op. 1110 A Prueba de fuga", "Op. 1110 Ensamble de finger con back pla", "Op. 1120 Ensamble de shaft en bearing", "Op. 1130 Ensamble de circlip", "Op. 1130 B Torque en Black plate", "Op. 1135 Verificacion de oil deflector", "Op. 1140 Ensamble de compresor weel", "Op. 1150 Prueba de fuga", "Op. 1180 Torque en la nut"],
            "FORD/NISSAN Linea 12": ["Op. 1210 Impresion de QR en compresor", "Op. 1220 Ensamble de core en compresor c", "Op. 1230 Torque al super core", "Op. 1240 A Ensamble de super core con tu", "Op. 1240 D Torque a V Band", "Op. 1250 A Ajuste de actuador", "Op. 1250 B Ajuste de actuador", "Op. 1255 Inspeccion final de turbo"],
            "FORD/NISSAN Linea 13": ["Op. 1310 A Torque a trush bearing en cor", "Op. 1310 A Prueba de fuga", "Op. 1310 Ensamble de finger con back pla", "Op. 1320 Ensamble de shaft en bearing", "Op. 1330 Ensamble de circlip", "Op. 1330 B Torque en Black plate", "Op. 1335 Verificacion de oil deflector", "Op. 1340 Ensamble de compresor weel", "Op. 1350 Prueba de fuga", "Op. 1380 Torque en la nut"],
            "FORD/NISSAN Linea 14": ["Op. 1410 Impresion de QR en compresor", "Op. 1420 Ensamble de core en compresor c", "Op. 1430 Torque al super core", "Op. 1440 A Ensamble de super core con tu", "Op. 1440 D Torque a V Band", "Op. 1450 A Ajuste de actuador", "Op. 1450 B Ajuste de actuador", "Op. 1455 Inspeccion final de turbo"],
            "Stellantis Linea 5": ["Op. 510 A Prueba de fuga de bearing", "Op. 510 Ensamble de journal con bearing", "Op. 520 Ensamble de shaft con bearing", "Op. 530 Ensamble de circlip", "Op. 540 Ensamble de compresor weel y tor", "Op. 550 Prueba de fuga", "Op. 580 torque en la nut"],
            "Stellantis Linea 7": ["Op. 710 A Prueba de fuga de bearing", "Op. 710 Ensamble de journal con bearing", "Op. 720 Ensamble de shaft con bearing", "Op. 730 Ensamble de circlip", "Op. 740 Ensamble de compresor weel y tor", "Op. 750 Prueba de fuga", "Op. 780 torque en la nut"],
            "Stellantis Linea 8": ["Op. 805.2 Ensamble de E-CRV y Fuga", "Op. 805.3 Ensamble de Actuador", "Op. 805.1 Ensamble MUAPORT", "Op. 810 Impresion de codigo compresor co", "Op. 820 Ensamble de core con compresor c", "Op. 830 Torque a super core", "Op. 840 A Ensamble de super core con tur", "Op. 840 D Torque a V Band", "Op. 850 B Ajuste al actuador", "Op. 855 Inspeccion final de turbo"],
            "Stellantis Linea 18": ["F10 - Cargar Compressor Cover y TH", "ENSAMBLADO MUA", "F20 - Subensamble de Compressor Cover", "F20A Prueba de fuga FCA", "F30 - Torque, Silenciador, Actuador", "F40 - Ensamble de Super Core", "F50 - Torque de V-Band y medicion LKR", "F60 - Subensamble y Ajuste de LKR", "F70 - Instalacion de LKR", "F80-AEOLT (End of line testerAUDI)", "F80-C Grabado laser de Nameplate", "F90 - Instalacion de Shipping Caps y emp"],
            "Lavadoras": ["Lavado A", "Lavado B"]
            // Agrega aquí el resto de tus líneas y estaciones
        };

        const lineaSelect = document.getElementById('linea'); //document.getElementById('linea') devuelve el elemento DOM con id="linea".
        //Guardas referencias a ambos <select> en variables para trabajar con ellos.
        //Si alguno no existe, la variable será null — conviene comprobar en código robusto.
        const estacionSelect = document.getElementById('estacion');

        lineaSelect.addEventListener('change', function () { //addEventListener('change', ...) registra una función que se ejecuta cuando cambia la selección (cuando el usuario selecciona otra línea).
            //function () { ... } es una función anónima — adentro accedes al elemento con this (porque usas función tradicional).
            const linea = this.value; //this.value es el value del <select> que cambió — es la línea escogida.
                                      //También podrías usar event.target.value si recibes el event como parámetro.
            estacionSelect.innerHTML = '<option value="">-- Selecciona estación --</option>';
            //Limpia el <select> de estaciones y deja una opción por defecto.
            //innerHTML = reemplaza el HTML interno del elemento (rápido y simple).
           //Atención: usar innerHTML borra cualquier evento o datos previos asociados a los hijos del elemento.

            if (estacionesPorLinea[linea]) { //Comprueba si existe una entrada para la línea seleccionada dentro del objeto estacionesPorLinea.
                estacionesPorLinea[linea].forEach(estacion => { //forEach recorre cada string de estaciones.
                    const option = document.createElement('option'); //document.createElement('option') crea un <option> vacío en memoria.
                    option.value = estacion; //option.value = estacion; asigna el valor que se enviará en el formulario.
                    option.textContent = estacion; //option.textContent = estacion; pone el texto visible en la lista.
                    estacionSelect.appendChild(option); //estacionSelect.appendChild(option); agrega el <option> al <select> en la página.
                });
            }
    /*
                            Conceptos clave y por qué funciona así

                    -Objeto vs array: estacionesPorLinea es un objeto con keys (líneas). forEach itera arrays (las estaciones).

                    -Case-sensitive: "FORD/NISSAN Linea 1" debe coincidir exactamente con el value del <option> de línea (mayúsculas, espacios, etc.).

                    -DOM: createElement y appendChild manipulan el DOM de forma segura (mejor que concatenar HTML cuando trabajas con muchos elementos).

                     -Evento change se dispara cuando el control pierde focus o el usuario confirma una selección (es el correcto para selects).
    */
        });
    </script>
</body>
</html>