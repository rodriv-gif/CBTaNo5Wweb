<?php
require "includes/sesion.php";
exigir_lector();
require "config/conexion.php"; // aquí viene la conexión $pdo
require "includes/consultas_lector.php";
$pagina_activa = "mi_cuenta.php";
$titulo = "Mis préstamos";
$subtitulo = "Libros que tienes en préstamo.";
$icono = "i-clock";

// Libros que el lector todavía no devuelve (con sus días restantes, retraso y multa)
$prestamos = prestamos_del_lector($pdo);

include "includes/inicio_cuenta.php";
?>
<div class="panel overflow-hidden">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Libro</th>
                    <th>Fecha de préstamo</th>
                    <th>Fecha límite</th>
                    <th>Días restantes</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($prestamos) === 0): ?>
                    <tr>
                        <td colspan="5" class="text-center text-secondary py-5">No tienes libros en préstamo.</td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($prestamos as $prestamo): ?>
                    <tr class="<?= $prestamo["vencido"] ? "row-late" : "" ?>">
                        <td>
                            <?= e($prestamo["titulo_libro"]) ?>
                            <?php if ($prestamo["inventario"]): ?>
                                <small class="d-block text-secondary">Ejemplar <?= e($prestamo["inventario"]) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= fecha_corta($prestamo["fecha_prestamo"]) ?></td>
                        <td><?= fecha_corta($prestamo["fecha_devolucion"]) ?></td>
                        <td class="<?= $prestamo["vencido"] ? "text-danger" : "" ?>">
                            <?= texto_dias_prestamo($prestamo["dias"]) ?>
                            <?php if ($prestamo["vencido"]): ?>
                                <small class="d-block">Multa acumulada: <?= dinero($prestamo["multa"]) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($prestamo["vencido"]): ?>
                                <span class="tag tag-danger"><svg class="icon"><use href="#i-triangle-alert" /></svg> Vencido</span>
                            <?php else: ?>
                                <span class="tag tag-ok"><svg class="icon"><use href="#i-circle-check" /></svg> Activo</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include "includes/fin_cuenta.php"; ?>
