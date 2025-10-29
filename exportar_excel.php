<?php
require 'sesion.php';
include 'db.php';

/* ======================================================
   1️⃣ LEER FILTROS IGUALES A listar.php
   ====================================================== */
$f_usuario     = $_GET['usuario']     ?? '';
$f_linea       = $_GET['linea']       ?? '';
$f_estacion    = $_GET['estacion']    ?? '';
$f_descripcion = $_GET['descripcion'] ?? '';

$where  = [];
$params = [];

if ($f_usuario !== '')     { $where[] = "Usuario = ?";     $params[] = $f_usuario; }
if ($f_linea !== '')       { $where[] = "Linea = ?";       $params[] = $f_linea; }
if ($f_estacion !== '')    { $where[] = "Estacion = ?";    $params[] = $f_estacion; }
if ($f_descripcion !== '') { $where[] = "Descripcion = ?"; $params[] = $f_descripcion; }

$sqlWhere = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

/* ======================================================
   2️⃣ CONSULTA SQL (MISMA QUE listar.php SIN PAGINAR)
   ====================================================== */
$sql = "
  SELECT ID, Usuario, Linea, Estacion, Descripcion, FechaHoraFalla,
         UsuarioSolucion, Solucion, FechaHoraSolucion, TiempoFalla
  FROM Fallas
  $sqlWhere
  ORDER BY FechaHoraFalla DESC
";
$stmt = sqlsrv_query($conn, $sql, $params);
if ($stmt === false) {
    die("<pre>" . print_r(sqlsrv_errors(), true) . "</pre>");
}

/* ======================================================
   3️⃣ CABECERAS PARA FORZAR DESCARGA DE EXCEL
   ====================================================== */
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Fallas_Andon.xls");
header("Pragma: no-cache");
header("Expires: 0");

/* ======================================================
   4️⃣ ENCABEZADO DEL ARCHIVO (con estilo simple)
   ====================================================== */
echo "<table border='1' style='border-collapse:collapse; font-family:Arial;'>";
echo "<thead style='background-color:#0D2C63; color:#fff; font-weight:bold; text-align:center;'>
        <tr>
            <th>ID</th>
            <th>Usuario Reporta</th>
            <th>Línea</th>
            <th>Estación</th>
            <th>Descripción</th>
            <th>Fecha y Hora Falla</th>
            <th>Usuario Soluciona</th>
            <th>Solución</th>
            <th>Fecha y Hora Solución</th>
            <th>⏱ Tiempo Falla (min)</th>
        </tr>
      </thead><tbody>";

/* ======================================================
   5️⃣ LLENAR DATOS
   ====================================================== */
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $fechaF = ($row['FechaHoraFalla'] instanceof DateTime)
        ? $row['FechaHoraFalla']->format('Y-m-d H:i:s') : '';
    $fechaS = ($row['FechaHoraSolucion'] instanceof DateTime)
        ? $row['FechaHoraSolucion']->format('Y-m-d H:i:s') : '';

    echo "<tr style='text-align:center;'>
            <td>{$row['ID']}</td>
            <td>{$row['Usuario']}</td>
            <td>{$row['Linea']}</td>
            <td>{$row['Estacion']}</td>
            <td>{$row['Descripcion']}</td>
            <td>{$fechaF}</td>
            <td>" . ($row['UsuarioSolucion'] ?: 'Pendiente') . "</td>
            <td>" . ($row['Solucion'] ?: 'Pendiente') . "</td>
            <td>{$fechaS}</td>
            <td>{$row['TiempoFalla']}</td>
          </tr>";
}

echo "</tbody></table>";
exit;
?>
