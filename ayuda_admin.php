<?php
require "includes/sesion.php";
exigir_bibliotecario();
require "config/conexion.php"; // aquí viene la conexión ($pdo y $conexion)
require "includes/consultas_ayuda.php";
$pagina_activa = "ayuda_admin.php";

// ---------- Altas y bajas (el formulario se manda a esta misma página) ----------
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!csrf_valido()) {
        flash_poner("error", "El formulario venció. Intenta de nuevo.");
    } elseif (($_POST["accion"] ?? "") === "alta") {
        $pregunta = trim($_POST["pregunta"] ?? "");
        $respuesta = trim($_POST["respuesta"] ?? "");
        $orden = trim($_POST["orden"] ?? "") === "" ? null : (int) $_POST["orden"];

        if ($pregunta === "" || $respuesta === "") {
            flash_poner("error", "Escribe la pregunta y la respuesta.");
        } elseif (mb_strlen($pregunta) > AYUDA_MAX_PREGUNTA || mb_strlen($respuesta) > AYUDA_MAX_RESPUESTA) {
            flash_poner("error", "El texto es demasiado largo.");
        } elseif (agregar_tema_ayuda($pdo, $pregunta, $respuesta, $orden, $_SESSION["id_bibliotecario"] ?? null)) {
            flash_poner("ok", "El tema se agregó a la ayuda.");
        } else {
            flash_poner("error", "No se pudo guardar el tema. Intenta más tarde.");
        }
    } elseif (($_POST["accion"] ?? "") === "baja") {
        if (borrar_tema_ayuda($pdo, (int) ($_POST["id_ayuda"] ?? 0))) {
            flash_poner("ok", "El tema se eliminó.");
        } else {
            flash_poner("error", "No se pudo eliminar el tema.");
        }
    }

    // Se regresa a la página para que al recargar no se repita el envío
    header("Location: ayuda_admin.php");
    exit();
}

$temas = temas_de_ayuda($pdo);
?>
<!doctype html>
<html lang="es">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Ayuda del lector - Biblioteca Escolar CBTa No. 5</title>

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
                    <h1 class="page-title">Ayuda del lector</h1>
                    <p class="page-subtitle">Agrega o elimina los temas que el lector ve en la pantalla de Ayuda.</p>

                    <?php include "includes/mensajes.php"; ?>

                    <div class="row g-4">
                        <div class="col-xl-5">
                            <section class="panel p-4">
                                <h2 class="h5 fw-semibold section-green-title mb-3">Agregar tema</h2>

                                <form action="ayuda_admin.php" method="post">
                                    <?= csrf_campo() ?>
                                    <input type="hidden" name="accion" value="alta" />

                                    <div class="mb-3">
                                        <label for="pregunta" class="form-label fw-medium">Pregunta o título</label>
                                        <input
                                            type="text"
                                            id="pregunta"
                                            name="pregunta"
                                            class="form-control"
                                            maxlength="<?= AYUDA_MAX_PREGUNTA ?>"
                                            placeholder="Ejemplo: ¿Cómo reservo un libro?"
                                            required
                                        />
                                    </div>

                                    <div class="mb-3">
                                        <label for="respuesta" class="form-label fw-medium">Respuesta</label>
                                        <textarea
                                            id="respuesta"
                                            name="respuesta"
                                            class="form-control"
                                            rows="5"
                                            maxlength="<?= AYUDA_MAX_RESPUESTA ?>"
                                            placeholder="Escribe la explicación que verá el lector"
                                            required
                                        ></textarea>
                                    </div>

                                    <div class="mb-4">
                                        <label for="orden" class="form-label fw-medium">Posición en la lista (opcional)</label>
                                        <input type="number" id="orden" name="orden" class="form-control" min="1" placeholder="Si lo dejas vacío, va al final" />
                                    </div>

                                    <button type="submit" class="btn btn-brand">
                                        <svg class="icon"><use href="#i-plus" /></svg> Agregar tema
                                    </button>
                                </form>
                            </section>
                        </div>

                        <div class="col-xl-7">
                            <section class="panel overflow-hidden">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 px-4 py-3">
                                    <h2 class="h5 fw-semibold section-green-title mb-0">Temas publicados</h2>
                                    <span class="text-secondary"><?= plural(count($temas), "tema", "temas") ?></span>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-lib">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Tema</th>
                                                <th>Alta</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($temas) === 0): ?>
                                                <tr>
                                                    <td colspan="4" class="text-center text-secondary py-5">Todavía no hay temas de ayuda.</td>
                                                </tr>
                                            <?php endif; ?>

                                            <?php foreach ($temas as $posicion => $tema): ?>
                                                <tr>
                                                    <td><?= $posicion + 1 ?></td>
                                                    <td>
                                                        <?= e($tema["pregunta"]) ?>
                                                        <span class="sub"><?= e(mb_strimwidth($tema["respuesta"], 0, 110, "…", "UTF-8")) ?></span>
                                                    </td>
                                                    <td><?= fecha_corta($tema["fecha_alta"]) ?></td>
                                                    <td class="text-end">
                                                        <form action="ayuda_admin.php" method="post" onsubmit="return confirm('¿Eliminar este tema de la ayuda?');">
                                                            <?= csrf_campo() ?>
                                                            <input type="hidden" name="accion" value="baja" />
                                                            <input type="hidden" name="id_ayuda" value="<?= (int) $tema["id_ayuda"] ?>" />
                                                            <button type="submit" class="btn btn-danger-soft btn-sm">
                                                                <svg class="icon"><use href="#i-trash" /></svg> Eliminar
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </section>
                        </div>
                    </div>

                </div>
            </main>
        </div>

        <script src="./js/bootstrap.bundle.min.js"></script>
        <script src="./js/ver_password.js"></script>
    </body>
</html>
