<?php 
require 'sesion.php';
include 'db.php';

/* =========================
   1) OBTENER OPCIONES PARA LOS SELECT (DISTINCT)
   ========================= */
function getDistinctValues($conn, $campo) {
    // Evita NULLs y ordena alfabéticamente
    $sql = "SELECT DISTINCT $campo AS val FROM Fallas WHERE $campo IS NOT NULL ORDER BY $campo ASC";
    $stmt = sqlsrv_query($conn, $sql);
    if ($stmt === false) die("<pre>" . print_r(sqlsrv_errors(), true) . "</pre>");
    $out = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $out[] = $row['val'];
    }
    return $out;
}

$usuarios     = getDistinctValues($conn, 'Usuario');
$lineas       = getDistinctValues($conn, 'Linea');
$estaciones   = getDistinctValues($conn, 'Estacion');
$descripciones= getDistinctValues($conn, 'Descripcion'); // ⚠️ si hay muchísimas, luego te propongo pasar esto a búsqueda por texto

/* =========================
   2) LEER FILTROS Y PÁGINA
   ========================= */
$f_usuario     = $_GET['usuario']      ?? '';
$f_linea       = $_GET['linea']        ?? '';
$f_estacion    = $_GET['estacion']     ?? '';
$f_descripcion = $_GET['descripcion']  ?? '';
$pagina        = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$porPagina     = 50;

/* =========================
   3) CONSTRUIR WHERE + PARAMS
   (con igualdad porque ya eliges desde un select)
   ========================= */
$where  = [];
$params = [];

if ($f_usuario !== '')     { $where[] = "Usuario = ?";     $params[] = $f_usuario; }
if ($f_linea !== '')       { $where[] = "Linea = ?";       $params[] = $f_linea; }
if ($f_estacion !== '')    { $where[] = "Estacion = ?";    $params[] = $f_estacion; }
if ($f_descripcion !== '') { $where[] = "Descripcion = ?"; $params[] = $f_descripcion; }

$sqlWhere = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

/* =========================
   4) CONSULTA PAGINADA
   ========================= */
$sql = "
  SELECT ID, Usuario, Linea, Estacion, Descripcion, FechaHoraFalla,
         UsuarioSolucion, Solucion, FechaHoraSolucion, TiempoFalla
  FROM Fallas
  $sqlWhere
  ORDER BY FechaHoraFalla DESC
  OFFSET ? ROWS FETCH NEXT ? ROWS ONLY
";
$paramsConLimite = array_merge($params, [($pagina - 1) * $porPagina, $porPagina]);
$stmt = sqlsrv_query($conn, $sql, $paramsConLimite);
if ($stmt === false) die("<pre>" . print_r(sqlsrv_errors(), true) . "</pre>");

// total para paginación
$sqlCount = "SELECT COUNT(*) AS Total FROM Fallas $sqlWhere";
$stmtCount = sqlsrv_query($conn, $sqlCount, $params);
if ($stmtCount === false) die("<pre>" . print_r(sqlsrv_errors(), true) . "</pre>");
$rowCount = sqlsrv_fetch_array($stmtCount, SQLSRV_FETCH_ASSOC);
$totalRegistros = (int)$rowCount['Total'];
$totalPaginas   = max(1, (int)ceil($totalRegistros / $porPagina));
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>📋 Listado de Fallas</title>
  <link rel="stylesheet" href="listadostyle.css?v=<?php echo filemtime('listadostyle.css'); ?>">
  <style>
    .filters { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:12px; }
    .filters select, .filters button, .filters a { padding:8px 10px; border-radius:6px; }
    .btn { padding:8px 12px; border-radius:6px; background:#007bff; color:#fff; text-decoration:none; }
    .btn-export { background: #1aa33d; color:#fff; }
    .btn-clear { background:#6c757d; color:#fff; text-decoration:none; }
    .pagination { margin-top:12px; display:flex; gap:8px; align-items:center; }
    .pagination a, .pagination span { padding:6px 10px; border-radius:6px; text-decoration:none; }
    .pagination a { background:#0d2c63; color:#fff; }
    .pagination span { background:#6c757d; color:#fff; }
  </style>
</head>
<body>
  <div class="container">
    <h2 class="title">📋 Listado de Fallas</h2>

    <div class="actions" style="display:flex; gap:10px; margin-bottom:10px;">
      <a href="formulario.php" class="btn">← Nueva Falla</a>
      <!-- pasa los filtros actuales al exportador -->
      <a href="exportar_excel.php?<?php echo http_build_query($_GET); ?>" class="btn btn-export">⬇ Exportar Excel</a>
    </div>

    <!-- 🔽 FILTROS CON SELECT (tipo Excel) -->
    <form method="GET" class="filters">
      <select name="usuario">
        <option value="">Filtrar por Usuario</option>
        <?php foreach ($usuarios as $u): ?>
          <option value="<?= htmlspecialchars($u, ENT_QUOTES, 'UTF-8') ?>" <?= ($u===$f_usuario?'selected':'') ?>>
            <?= htmlspecialchars($u, ENT_QUOTES, 'UTF-8') ?>
          </option>
        <?php endforeach; ?>
      </select>

      <select name="linea">
        <option value="">Filtrar por Línea</option>
        <?php foreach ($lineas as $l): ?>
          <option value="<?= htmlspecialchars($l, ENT_QUOTES, 'UTF-8') ?>" <?= ($l===$f_linea?'selected':'') ?>>
            <?= htmlspecialchars($l, ENT_QUOTES, 'UTF-8') ?>
          </option>
        <?php endforeach; ?>
      </select>

      <select name="estacion">
        <option value="">Filtrar por Estación</option>
        <?php foreach ($estaciones as $e): ?>
          <option value="<?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?>" <?= ($e===$f_estacion?'selected':'') ?>>
            <?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?>
          </option>
        <?php endforeach; ?>
      </select>

      <select name="descripcion">
        <option value="">Filtrar por Descripción</option>
        <?php foreach ($descripciones as $d): ?>
          <option value="<?= htmlspecialchars($d, ENT_QUOTES, 'UTF-8') ?>" <?= ($d===$f_descripcion?'selected':'') ?>>
            <?= htmlspecialchars($d, ENT_QUOTES, 'UTF-8') ?>
          </option>
        <?php endforeach; ?>
      </select>

      <button type="submit">🔍 Filtrar</button>
      <a class="btn-clear" href="listar.php">🧹 Limpiar</a>
    </form>

    <!-- 📄 TABLA -->
    <table class="styled-table">
      <thead>
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
      </thead>
      <tbody>
        <?php while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)): 
          $fechaF = ($row['FechaHoraFalla']    instanceof DateTime) ? $row['FechaHoraFalla']->format('Y-m-d H:i:s')    : '—';
          $fechaS = ($row['FechaHoraSolucion'] instanceof DateTime) ? $row['FechaHoraSolucion']->format('Y-m-d H:i:s') : '—';
          $pendiente = empty($row['Solucion']) ? 'pendiente' : '';
        ?>
        <tr class="<?= $pendiente ?>">
          <td><?= htmlspecialchars($row['ID']) ?></td>
          <td><?= htmlspecialchars($row['Usuario']) ?></td>
          <td><?= htmlspecialchars($row['Linea']) ?></td>
          <td><?= htmlspecialchars($row['Estacion']) ?></td>
          <td><?= htmlspecialchars($row['Descripcion']) ?></td>
          <td><?= $fechaF ?></td>
          <td><?= htmlspecialchars($row['UsuarioSolucion'] ?? 'Pendiente') ?></td>
          <td><?= htmlspecialchars($row['Solucion'] ?? 'Pendiente') ?></td>
          <td><?= $fechaS ?></td>
          <td><?= htmlspecialchars($row['TiempoFalla'] ?? '—') ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>

    <!-- 🔢 PAGINACIÓN -->
    <div class="pagination">
      <?php if ($pagina > 1): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['pagina'=>$pagina-1])) ?>">⬅ Anterior</a>
      <?php endif; ?>
      <span>Página <?= $pagina ?> de <?= $totalPaginas ?> (<?= $totalRegistros ?> registros)</span>
      <?php if ($pagina < $totalPaginas): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['pagina'=>$pagina+1])) ?>">Siguiente ➡</a>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
