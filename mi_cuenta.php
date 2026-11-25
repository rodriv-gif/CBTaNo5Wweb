<?php
require "includes/sesion.php";
exigir_lector();
require "config/conexion.php"; // aquí viene la conexión $pdo
require "includes/consultas_lector.php";
$pagina_activa = "mi_cuenta.php";

// Datos de las tarjetas de resumen
$prestamos = prestamos_del_lector($pdo);
$reservas = reservas_del_lector($pdo);
[$multas, $total_multas] = adeudos_del_lector($pdo);
$avisos_nuevos = contar_avisos_nuevos($pdo);
$carrera = carrera_del_lector($pdo);

$vencidos = count($multas);

// Mensaje guardado por otra página (por ejemplo, cambiar_password.php).
// Si es un error de la ventana de contraseña, se muestra dentro de la ventana y esta se abre sola.
$mensaje_guardado = flash_tomar();
$error_en_ventana = $mensaje_guardado && !empty($mensaje_guardado["ventana"]) && $mensaje_guardado["tipo"] === "error";

// Tarjetas de resumen: icono, título, valor, texto, página, texto del enlace, estilo
$resumen = [
    [
        "i-clock",
        "Préstamos activos",
        count($prestamos),
        $vencidos > 0 ? plural($vencidos, "vencido", "vencidos") : "Ninguno vencido",
        "mis_prestamos.php",
        "Ver mis préstamos",
        "",
    ],
    [
        "i-bookmark",
        "Reservas activas",
        count($reservas),
        count($reservas) > 0 ? "Recógelas a tiempo" : "Sin reservas por ahora",
        "mis_reservas.php",
        "Ver mis reservas",
        "",
    ],
    [
        "i-triangle-alert",
        "Adeudos",
        dinero($total_multas),
        $total_multas > 0 ? "Multas pendientes" : "Sin multas pendientes",
        "mis_adeudos.php",
        "Ver mis adeudos",
        $total_multas > 0 ? "is-danger" : "",
    ],
    [
        "i-bell",
        "Avisos nuevos",
        $avisos_nuevos,
        $avisos_nuevos > 0 ? "Requieren tu atención" : "Estás al corriente",
        "mis_avisos.php",
        "Ver mis avisos",
        "",
    ],
];

// Campos de la ventana "Cambiar contraseña": id, nombre del campo, etiqueta, texto de ayuda, autocomplete, nota
$campos = [
    ["pwActual", "pw_actual", "Contraseña actual", "Escribe tu contraseña actual", "current-password", ""],
    ["pwNueva", "pw_nueva", "Contraseña nueva", "Escribe tu contraseña nueva", "new-password", "Mínimo 8 caracteres."],
    ["pwConfirmar", "pw_confirmar", "Confirmar contraseña nueva", "Repite tu contraseña nueva", "new-password", ""],
];
?>
<!doctype html>
<html lang="es">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Mi cuenta - Biblioteca Escolar CBTa No. 5</title>

        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="./css/bootstrap.min.css" />
        <link rel="stylesheet" href="./css/estilos.css" />
        <link rel="stylesheet" href="./css/lector.css" />
        <link rel="stylesheet" href="./css/mi_cuenta.css" />
        <link rel="stylesheet" href="./css/footer.css" />
    </head>

    <body class="d-flex flex-column min-vh-100">
        <?php include "includes/iconos.php"; ?>
        <?php include "includes/header_lector.php"; ?>

        <main class="flex-grow-1">
            <section class="hero text-white">
                <div class="container">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                        <div>
                            <p class="hero-eyebrow">Biblioteca del CBTa No. 5</p>
                            <h1 class="display-5 fw-semibold mb-3">Hola, <?= $nombre_lector ?></h1>
                            <span class="tag tag-muted px-3 py-1"><?= e($carrera !== "" ? $carrera : tipo_de_lector()) ?></span>
                        </div>
                        <button type="button" class="btn btn-brand-outline" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                            <svg class="icon"><use href="#i-lock" /></svg> Cambiar contraseña
                        </button>
                    </div>
                </div>
            </section>

            <div class="container hero-cards">
                <?php if ($mensaje_guardado && !$error_en_ventana): ?>
                    <div class="alert alert-<?= $mensaje_guardado["tipo"] === "ok" ? "success" : "danger" ?> py-2" role="alert">
                        <?= e($mensaje_guardado["texto"]) ?>
                    </div>
                <?php endif; ?>
                <?= aviso_errores_bd() ?>

                <div class="row g-3">
                    <?php foreach ($resumen as [$icono, $titulo, $valor, $texto, $pagina, $enlace, $estilo]): ?>
                        <div class="col-sm-6 col-xl-3">
                            <div class="panel stat-card p-4 h-100 <?= $estilo ?>">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="icon-circle">
                                        <svg class="icon"><use href="#<?= $icono ?>" /></svg>
                                    </div>
                                    <h2 class="h6 mb-0 fw-semibold"><?= $titulo ?></h2>
                                </div>
                                <p class="stat-number mt-3 mb-0"><?= e($valor) ?></p>
                                <p class="text-secondary mb-2"><?= e($texto) ?></p>
                                <a href="<?= $pagina ?>" class="link-arrow">
                                    <?= $enlace ?> <svg class="icon"><use href="#i-chevron-right" /></svg>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-lg-6">
                        <div class="panel p-4 h-100 d-flex flex-column">
                            <div class="icon-circle">
                                <svg class="icon"><use href="#i-book-open" /></svg>
                            </div>
                            <h2 class="h4 mt-3 mb-2">Catálogo</h2>
                            <p class="text-secondary mb-4">Busca por título, autor o especialidad y revisa si hay ejemplares disponibles.</p>
                            <a href="catalogo.php" class="link-arrow mt-auto">
                                Explorar el catálogo <svg class="icon"><use href="#i-arrow-right" /></svg>
                            </a>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="panel p-4 h-100">
                            <h2 class="h5 mb-3">Hoy en la biblioteca</h2>
                            <span class="tag tag-ok">
                                <svg class="icon"><use href="#i-circle-check" /></svg> Abierta ahora
                            </span>
                            <p class="d-flex align-items-center gap-2 mt-3 mb-2 small">
                                <svg class="icon"><use href="#i-clock" /></svg> Lunes a viernes, 8:00 a 15:00
                            </p>
                            <p class="d-flex align-items-center gap-2 mb-3 small">
                                <svg class="icon"><use href="#i-map-pin" /></svg> CBTa No. 5, Huejutla, Hgo.
                            </p>
                            <p class="small text-secondary border-top pt-3 mb-0">Hasta 3 libros por préstamo. Retraso: $10 por día.</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Ventana: Cambiar contraseña -->
        <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-4">
                    <form action="cambiar_password.php" method="post" class="modal-body p-4">
                        <div class="d-flex align-items-start gap-3 mb-4">
                            <div class="modal-circle">
                                <svg class="icon"><use href="#i-lock" /></svg>
                            </div>
                            <div class="flex-grow-1">
                                <h2 id="changePasswordTitle" class="h4 fw-semibold mb-1">Cambiar contraseña</h2>
                                <p class="mb-0 small text-secondary">Escribe tu contraseña actual y elige una nueva.</p>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>

                        <?php if ($error_en_ventana): ?>
                            <div class="alert alert-danger py-2" role="alert"><?= e($mensaje_guardado["texto"]) ?></div>
                        <?php endif; ?>

                        <?php foreach ($campos as [$id, $nombre_campo, $etiqueta, $ayuda, $autocompletar, $nota]): ?>
                            <div class="mb-3">
                                <label for="<?= $id ?>" class="form-label fw-medium"><?= $etiqueta ?></label>
                                <div class="password-field">
                                    <svg class="icon field-icon"><use href="#i-lock" /></svg>
                                    <input type="password" id="<?= $id ?>" name="<?= $nombre_campo ?>" class="form-control" placeholder="<?= $ayuda ?>" minlength="8" autocomplete="<?= $autocompletar ?>" required />
                                    <button type="button" class="toggle-password" data-target="<?= $id ?>" aria-label="Mostrar contraseña">
                                        <svg class="icon"><use href="#i-eye" /></svg>
                                    </button>
                                </div>
                                <?php if ($nota): ?>
                                    <div class="form-text"><?= $nota ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-login px-4">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php include "includes/footer.php"; ?>
        <script src="./js/bootstrap.bundle.min.js"></script>
        <script src="./js/ver_password.js"></script>
        <?php if ($error_en_ventana): ?>
            <script>
                // Si la contraseña no se pudo cambiar, la ventana se vuelve a abrir con el mensaje
                new bootstrap.Modal(document.getElementById("changePasswordModal")).show();
            </script>
        <?php endif; ?>
    </body>
</html>
