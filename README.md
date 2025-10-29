# Trazabilidad-de-Fallas
Aplicación web desarrollada para asegurar la trazabilidad de reportes de fallas en entornos productivos, simulando un sistema Andon industrial.
Permite notificaciones automáticas, análisis de datos y visualización de métricas mediante gráficas interactivas.

Características principales

- Registro de fallas: formulario web para capturar incidentes con usuario, línea, estación y descripción.

- Notificaciones automáticas: envío de correos mediante PHPMailer al área correspondiente.

- Cálculo de métricas: tiempo promedio de atención, tiempos de falla, usuario que reporta y que soluciona.

- Dashboard de análisis: gráficas interactivas con Chart.js para visualización por usuario, línea y estación.

- Base de datos SQL Server: almacenamiento estructurado de eventos y resultados.

- Autenticación de usuarios: control de sesión mediante login.php y logout.php.

- Servidor Apache (XAMPP 3.3.0): entorno web local de pruebas y despliegue.

 🧱 Arquitectura
Front-End: Interfaz de usuario moderna e intuitiva: HTML5, CSS3, JavaScript
Back-End: Lógica del servidor, consultas SQL y envío de notificaciones: PHP 8
Base de Datos: Almacenamiento de reportes y métricas: Microsoft SQL Server
Servidor Web: Entorno local de ejecución: Apache (XAMPP 3.3.0)

📁 andon-system/
├── formulario.php        # Registro de nuevas fallas
├── listar.php            # Listado filtrable de reportes
├── graficas.php          # Dashboard con Chart.js
├── guardarsolucion.php   # Cierre de fallas y notificación por correo
├── login.php / logout.php
├── db.php                # Conexión a SQL Server
├── PHPMailer-6.10.0/     # Librería para envío de correos
├── style/                # Archivos CSS personalizados
└── assets/               # Íconos, logos y recursos visuales

🔧 Requisitos técnicos

PHP ≥ 8.0

Apache (incluido en XAMPP 3.3.0)

Microsoft SQL Server

Extensión sqlsrv habilitada en PHP

Librería PHPMailer 6.10.0

Navegador moderno (Chrome, Edge)
