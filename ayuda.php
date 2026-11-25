<?php
require "includes/sesion.php";

// El bibliotecario administra la ayuda desde su propia pantalla
if (isset($_SESSION["id_bibliotecario"])) {
    header("Location: ayuda_admin.php");
    exit();
}

exigir_lector();
require "config/conexion.php"; // aquí viene la conexión $pdo
require "includes/consultas_ayuda.php";
$pagina_activa = "ayuda.php";

// Datos de contacto. Si la biblioteca tiene correo, escríbelo aquí y saldrá en la tarjeta.
$correo_biblioteca = "";

// Temas que dio de alta el bibliotecario
$temas = temas_de_ayuda($pdo);
?>
<!doctype html>
<html lang="es">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Ayuda - Biblioteca Escolar CBTa No. 5</title>

        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="./css/bootstrap.min.css" />
        <link rel="stylesheet" href="./css/estilos.css" />
        <link rel="stylesheet" href="./css/lector.css" />
        <link rel="stylesheet" href="./css/ayuda.css" />
        <link rel="stylesheet" href="./css/footer.css" />
    </head>

    <body class="d-flex flex-column min-vh-100">
        <?php include "includes/iconos.php"; ?>
        <?php include "includes/iconos_extra.php"; ?>
        <?php include "includes/header_lector.php"; ?>

        <main class="flex-grow-1 py-5">
            <div class="container">
                <h1 class="help-title display-6 fw-semibold mb-1">Ayuda</h1>
                <p class="text-secondary fs-5 mb-4">Resuelve tus dudas sobre la biblioteca y tu cuenta.</p>

                <?= aviso_errores_bd() ?>

                <div class="row g-4">
                    <div class="col-lg-8">
                        <?php if (count($temas) === 0): ?>
                            <div class="panel p-4 text-secondary">Por ahora no hay temas de ayuda publicados.</div>
                        <?php endif; ?>

                        <div class="accordion" id="helpList">
                            <?php foreach ($temas as $posicion => $tema): ?>
                                <?php $primero = $posicion === 0; ?>
                                <div class="accordion-item">
                                    <h3 class="accordion-header">
                                        <button
                                            class="accordion-button <?= $primero ? "" : "collapsed" ?>"
                                            type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#help<?= $posicion ?>"
                                            aria-expanded="<?= $primero ? "true" : "false" ?>"
                                            aria-controls="help<?= $posicion ?>"
                                        >
                                            <?= e($tema["pregunta"]) ?>
                                        </button>
                                    </h3>
                                    <div id="help<?= $posicion ?>" class="accordion-collapse collapse <?= $primero ? "show" : "" ?>" data-bs-parent="#helpList">
                                        <div class="accordion-body text-secondary"><?= e($tema["respuesta"]) ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="panel p-4">
                            <div class="icon-circle mb-3">
                                <svg class="icon"><use href="#i-life-buoy" /></svg>
                            </div>
                            <h2 class="h5">¿Necesitas más ayuda?</h2>
                            <p class="text-secondary small">Pregunta directamente en la biblioteca o escríbenos.</p>
                            <p class="d-flex align-items-center gap-2 small mb-2">
                                <svg class="icon"><use href="#i-clock" /></svg> Lunes a viernes, 8:00 a 15:00
                            </p>
                            <p class="d-flex align-items-center gap-2 small mb-2">
                                <svg class="icon"><use href="#i-map-pin" /></svg> CBTa No. 5, Huejutla, Hgo.
                            </p>
                            <?php if ($correo_biblioteca !== ""): ?>
                                <p class="d-flex align-items-center gap-2 small mb-0">
                                    <svg class="icon"><use href="#i-mail" /></svg> <?= e($correo_biblioteca) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <?php include "includes/footer.php"; ?>

        <script src="./js/bootstrap.bundle.min.js"></script>
    </body>
</html>
