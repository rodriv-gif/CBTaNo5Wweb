<?php
require "includes/sesion.php";
exigir_lector();
require "config/conexion.php"; // aquí viene la conexión $pdo
require "includes/consultas_lector.php";
$pagina_activa = "mi_cuenta.php";
$titulo = "Mis adeudos";
$subtitulo = "Multas pendientes por devolver tarde.";
$icono = "i-triangle-alert";

// Multas: $10 por cada día de retraso de un libro que sigue sin devolverse
[$multas, $total_multas] = adeudos_del_lector($pdo);

include "includes/inicio_cuenta.php";
?>
<div class="panel overflow-hidden">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Libro</th>
                    <th>Retraso</th>
                    <th class="text-end">Monto</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($multas) === 0): ?>
                    <tr>
                        <td colspan="4" class="text-center text-secondary py-5">No tienes adeudos. ¡Qué bien!</td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($multas as $multa): ?>
                    <tr>
                        <td>Multa por retraso</td>
                        <td>
                            <?= e($multa["titulo_libro"]) ?>
                            <?php if ($multa["inventario"]): ?>
                                <small class="d-block text-secondary">Ejemplar <?= e($multa["inventario"]) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= plural($multa["retraso"], "día", "días") ?></td>
                        <td class="text-end fw-semibold"><?= dinero($multa["multa"]) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="debt-total d-flex justify-content-between align-items-center px-4 py-3">
        <span class="fs-5">Total de multas pendientes</span>
        <strong class="fs-3 <?= $total_multas > 0 ? "text-danger" : "" ?>"><?= dinero($total_multas) ?></strong>
    </div>

    <p class="d-flex align-items-center gap-2 border-top px-4 py-3 mb-0 text-secondary">
        <svg class="icon"><use href="#i-info" /></svg> Los adeudos se liquidan de forma presencial en la biblioteca.
    </p>
</div>
<?php include "includes/fin_cuenta.php"; ?>
