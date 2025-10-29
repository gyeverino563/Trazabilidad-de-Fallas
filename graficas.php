<?php
require 'sesion.php';
include 'db.php';

// =======================
// 🔹 Gráfica 1: Fallas por usuario que reporta
// =======================
$sql1 = "SELECT Usuario, COUNT(*) AS total_fallas 
         FROM Fallas 
         GROUP BY Usuario 
         ORDER BY total_fallas DESC";
$stmt1 = sqlsrv_query($conn, $sql1);
$usuarios = [];
$totales = [];
while ($row = sqlsrv_fetch_array($stmt1, SQLSRV_FETCH_ASSOC)) {
    $usuarios[] = $row['Usuario'];
    $totales[] = $row['total_fallas'];
}

// =======================
// 🔹 Gráfica 2: Tiempo promedio de falla por usuario que soluciona (minutos)
// =======================
$sql2 = "SELECT UsuarioSolucion,
        AVG(DATEDIFF(MINUTE, FechaHoraFalla, FechaHoraSolucion)) AS TiempoPromedio
        FROM Fallas
        WHERE FechaHoraSolucion IS NOT NULL AND UsuarioSolucion IS NOT NULL
        GROUP BY UsuarioSolucion
        ORDER BY TiempoPromedio DESC";
$stmt2 = sqlsrv_query($conn, $sql2);
$usuarios_tiempo = [];
$tiempos_usuarios = [];
while ($row = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC)) {
    $usuarios_tiempo[] = $row['UsuarioSolucion'];
    $tiempos_usuarios[] = $row['TiempoPromedio'];
}

// =======================
// 🔹 Gráfica 3: Tiempo promedio de falla por línea (minutos)
// =======================
$sql3 = "SELECT Linea,
        AVG(DATEDIFF(MINUTE, FechaHoraFalla, FechaHoraSolucion)) AS TiempoPromedio
        FROM Fallas
        WHERE FechaHoraSolucion IS NOT NULL
        GROUP BY Linea
        ORDER BY Linea";
$stmt3 = sqlsrv_query($conn, $sql3);
$lineas = [];
$tiempos_lineas = [];
while ($row = sqlsrv_fetch_array($stmt3, SQLSRV_FETCH_ASSOC)) {
    $lineas[] = $row['Linea'];
    $tiempos_lineas[] = $row['TiempoPromedio'];
}

// =======================
// 🔹 Gráfica 4: Tiempo promedio de falla por estación (minutos)
// =======================
$sql4 = "SELECT Estacion,
        AVG(DATEDIFF(MINUTE, FechaHoraFalla, FechaHoraSolucion)) AS TiempoPromedio
        FROM Fallas
        WHERE FechaHoraSolucion IS NOT NULL
        GROUP BY Estacion
        ORDER BY TiempoPromedio DESC";
$stmt4 = sqlsrv_query($conn, $sql4);
$estaciones = [];
$tiempos_estaciones = [];
while ($row = sqlsrv_fetch_array($stmt4, SQLSRV_FETCH_ASSOC)) {
    $estaciones[] = $row['Estacion'];
    $tiempos_estaciones[] = $row['TiempoPromedio'];
}

// =======================
// 🔸 Convertir a JSON
// =======================
$usuarios_json = json_encode($usuarios);
$totales_json = json_encode($totales);
$usuarios_tiempo_json = json_encode($usuarios_tiempo);
$tiempos_usuarios_json = json_encode($tiempos_usuarios);
$lineas_json = json_encode($lineas);
$tiempos_lineas_json = json_encode($tiempos_lineas);
$estaciones_json = json_encode($estaciones);
$tiempos_estaciones_json = json_encode($tiempos_estaciones);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Dashboard 📊</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    body {
        background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
        margin: 0;
        font-family: "Poppins", Arial, sans-serif;
        color: #fff;
    }
    h1 { margin: 10px 0; letter-spacing: 1px; }
    .dashboard {
        display: grid;
        grid-template-columns: 1fr 1fr;
        grid-template-rows: 1fr 1fr;
        gap: 25px;
        padding: 40px;
        height: 100vh;
        box-sizing: border-box;
    }
    .panel {
        background: rgba(255, 255, 255, 0.08);
        backdrop-filter: blur(12px);
        border-radius: 20px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        padding: 25px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .panel:hover { transform: translateY(-6px); box-shadow: 0 8px 25px rgba(255, 255, 255, 0.15); }
    canvas { max-width: 100%; max-height: 80%; }
    .btn {
        background-color: #1d3557;
        color: white;
        text-decoration: none;
        padding: 10px 18px;
        border-radius: 12px;
        font-weight: 600;
        transition: background 0.3s ease;
    }
    .btn:hover { background-color: #457b9d; }
</style>
</head>

<body>
    <div style="text-align:center; margin-top:20px;">
        <a href="formulario.php" class="btn">← Regresar al formulario</a>
        <h1>📊 Dashboard de Fallas</h1>
    </div>

    <div class="dashboard">
        <div class="panel">
            <h3>Fallas Reportadas por Usuario</h3>
            <canvas id="grafica1"></canvas>
        </div>

        <div class="panel">
            <h3>Tiempo Promedio de Falla por Usuario que Soluciona (minutos)</h3>
            <canvas id="grafica2"></canvas>
        </div>

        <div class="panel">
            <h3>Tiempo Promedio de Falla por Línea</h3>
            <canvas id="grafica3"></canvas>
        </div>

        <div class="panel">
            <h3>Tiempo Promedio de Falla por Estación (minutos)</h3>
            <canvas id="grafica4"></canvas>
        </div>
    </div>

<script>
const colores = ['#4E79A7','#F28E2B','#E15759','#76B7B2','#59A14F','#EDC949','#AF7AA1','#FF9DA7','#9C755F','#BAB0AC'];

// 📊 Gráfica 1 (Barras)
new Chart(document.getElementById('grafica1'), {
    type: 'bar',
    data: { labels: <?php echo $usuarios_json; ?>, datasets: [{ label: 'Fallas reportadas', data: <?php echo $totales_json; ?>, backgroundColor: colores, borderRadius: 12 }] },
    options: { responsive: true, plugins: { legend: { display: false }, title: { display: true, text: 'Cantidad de fallas por usuario', color: '#fff' } }, scales: { y: { beginAtZero: true, grid: { color: '#ffffff30' }, ticks: { color: '#fff' } }, x: { ticks: { color: '#fff' } } } }
});

// 🍩 Gráfica 2 (Doughnut)
new Chart(document.getElementById('grafica2'), {
    type: 'doughnut',
    data: { labels: <?php echo $usuarios_tiempo_json; ?>, datasets: [{ label: 'Tiempo promedio (min)', data: <?php echo $tiempos_usuarios_json; ?>, backgroundColor: colores, borderColor: '#fff', borderWidth: 2 }] },
    options: { responsive: true, cutout: '65%', plugins: { legend: { position: 'right', labels: { color: '#fff' } }, title: { display: true, text: 'Tiempo promedio de falla por usuario que soluciona', color: '#fff' } } }
});

// 📈 Gráfica 3 (Línea)
new Chart(document.getElementById('grafica3'), {
    type: 'line',
    data: { labels: <?php echo $lineas_json; ?>, datasets: [{ label: 'Minutos promedio por línea', data: <?php echo $tiempos_lineas_json; ?>, borderColor: '#06D6A0', backgroundColor: 'rgba(6,214,160,0.25)', fill: true, tension: 0.35, pointBackgroundColor: '#06D6A0', pointRadius: 5 }] },
    options: { responsive: true, scales: { y: { beginAtZero: true, grid: { color: '#ffffff33' }, ticks: { color: '#fff' } }, x: { title: { display: true, text: 'Línea', color: '#fff' }, ticks: { color: '#fff' } } }, plugins: { legend: { position: 'top', labels: { color: '#fff' } }, title: { display: true, text: 'Tendencia de tiempo promedio por línea (min)', color: '#fff' } } }
});

// 📉 Gráfica 4 (Barras horizontales)
new Chart(document.getElementById('grafica4'), {
    type: 'bar',
    data: { labels: <?php echo $estaciones_json; ?>, datasets: [{ label: 'Minutos promedio por estación', data: <?php echo $tiempos_estaciones_json; ?>, backgroundColor: '#F4A261', borderRadius: 8 }] },
    options: { indexAxis: 'y', responsive: true, plugins: { legend: { display: false }, title: { display: true, text: 'Tiempo promedio de falla por estación (min)', color: '#fff' } }, scales: { x: { beginAtZero: true, ticks: { color: '#fff' }, grid: { color: '#ffffff33' } }, y: { ticks: { color: '#fff' } } } }
});
</script>
</body>
</html>
