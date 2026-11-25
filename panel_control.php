<?php
require "includes/sesion.php";
exigir_bibliotecario();
require "config/conexion.php"; // aquí viene la conexión ($pdo y $conexion)
require "includes/consultas_panel.php";
$pagina_activa = "panel_control.php";

// Qué tabla se muestra debajo de las tarjetas: activos o vencidos (por defecto, vencidos)
$ver = ($_GET["ver"] ?? "vencidos") === "activos" ? "activos" : "vencidos";

// Número de cada tarjeta
$total_activos = contar_prestamos_activos($pdo);
$total_vencidos = contar_prestamos_vencidos($pdo);
$total_reservas = contar_reservas_pendientes($pdo);

// Tabla de abajo, según la tarjeta que se tocó
$prestamos = prestamos_del_panel($pdo, $ver === "vencidos");
$total_en_tabla = $ver === "vencidos" ? $total_vencidos : $total_activos;
?>
<!doctype html>
<html lang="es">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Panel de control - Biblioteca Escolar CBTa No. 5</title>

        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="./css/bootstrap.min.css" />
        <link rel="stylesheet" href="./css/estilos.css" />
        <link rel="stylesheet" href="./css/bibliotecario.css" />
        <link rel="stylesheet" href="./css/panel_control.css" />
    </head>

    <body>
        <?php include "includes/iconos.php"; ?>
        <?php include "includes/iconos_catalogo.php"; ?>
        <?php include "includes/iconos_extra.php"; ?>

        <div class="admin-layout">
            <?php include "includes/sidebar_bibliotecario.php"; ?>

            <main class="admin-main">
                <div class="admin-content">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                        <div>
                            <h1 class="page-title">Panel de control</h1>
                            <p class="page-subtitle mb-0">Consulta el estado de los préstamos y las reservas de la biblioteca.</p>
                        </div>
                        <a href="panel_control.php?ver=<?= $ver ?>" class="btn btn-outline-brand">
                            <svg class="icon"><use href="#i-refresh" /></svg> Actualizar
                        </a>
                    </div>

                    <?php include "includes/mensajes.php"; ?>

                    <?php include "includes/panel_tarjetas.php"; ?>

                    <?php include "includes/prestamos_vencidos.php"; ?>
                </div>
            </main>
        </div>

        <script src="./js/bootstrap.bundle.min.js"></script>
        <script src="./js/ver_password.js"></script>
    </body>
</html>
