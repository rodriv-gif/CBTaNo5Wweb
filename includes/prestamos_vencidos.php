<?php
// Submódulo del Panel de control: tabla de préstamos que aparece debajo de las tarjetas.
// Según $ver muestra los vencidos o todos los activos.
// Usa las variables que arma panel_control.php: $ver, $prestamos y $total_en_tabla.
?>
<section class="panel overflow-hidden" id="detalle">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 px-4 py-3 border-bottom">
        <div>
            <h2 class="h5 fw-semibold section-green-title mb-0">
                <?= $ver === "vencidos" ? "Préstamos vencidos" : "Préstamos activos" ?>
            </h2>
            <span class="small text-secondary">
                <?php if (count($prestamos) < $total_en_tabla): ?>
                    Mostrando <?= count($prestamos) ?> de <?= $total_en_tabla ?>
                <?php else: ?>
                    <?= plural($total_en_tabla, "préstamo", "préstamos") ?>
                <?php endif; ?>
            </span>
        </div>
        <span class="small text-secondary">Multa de <?= dinero(MULTA_POR_DIA) ?> por día de retraso</span>
    </div>

    <div class="table-responsive">
        <table class="table table-lib">
            <thead>
                <tr>
                    <th>Lector</th>
                    <th>Libro</th>
                    <th>Préstamo</th>
                    <th>Fecha límite</th>
                    <th><?= $ver === "vencidos" ? "Retraso y multa" : "Estado" ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($prestamos) === 0): ?>
                    <tr>
                        <td colspan="5" class="detalle-vacio">
                            <?= $ver === "vencidos" ? "No hay préstamos vencidos." : "No hay préstamos activos." ?>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($prestamos as $p): ?>
                    <tr>
                        <td>
                            <?= e($p["lector"]) ?>
                            <span class="sub"><?= e($p["clave"]) ?> · <?= $p["tipo_lector"] ?></span>
                        </td>
                        <td>
                            <?= e($p["titulo_libro"]) ?>
                            <?php if ($p["inventario"]): ?>
                                <span class="sub">Ejemplar <?= e($p["inventario"]) ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= fecha_corta($p["fecha_prestamo"]) ?></td>
                        <td><?= fecha_corta($p["fecha_devolucion"]) ?></td>
                        <td>
                            <?php if ($p["vencido"]): ?>
                                <span class="text-amber fw-medium"><?= plural($p["retraso"], "día", "días") ?> · <?= dinero($p["multa"]) ?></span>
                            <?php else: ?>
                                <span class="tag tag-ok"><?= texto_dias_prestamo($p["dias"]) ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
