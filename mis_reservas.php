<?php
require "includes/sesion.php";
exigir_lector();
require "config/conexion.php"; // aquí viene la conexión $pdo
require "includes/consultas_lector.php";
$pagina_activa = "mi_cuenta.php";
$titulo = "Mis reservas";
$subtitulo = "Recoge tu libro en la biblioteca antes de la fecha límite.";
$icono = "i-bookmark";

// Reservas Activas del lector (las vencidas ya no se pueden recoger)
$reservas = reservas_del_lector($pdo);

include "includes/inicio_cuenta.php";
?>
<div class="panel overflow-hidden">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Libro</th>
                    <th>Estado</th>
                    <th>Fecha límite para recogerla</th>
                    <th>Tiempo restante</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($reservas) === 0): ?>
                    <tr>
                        <td colspan="5" class="text-center text-secondary py-5">No tienes reservas activas.</td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($reservas as $reserva): ?>
                    <?php $ultimo_dia = segundos_para($reserva["fecha_limite"]) < 86400; ?>
                    <tr>
                        <td>
                            <?= e($reserva["titulo_libro"]) ?>
                            <small class="d-block text-secondary">
                                Folio <?= e($reserva["folio"]) ?>
                                <?php if ($reserva["inventario"]): ?>
                                    · Ejemplar <?= e($reserva["inventario"]) ?>
                                <?php endif; ?>
                            </small>
                        </td>
                        <td>
                            <span class="tag tag-info"><svg class="icon"><use href="#i-bookmark" /></svg> Activa</span>
                        </td>
                        <td><?= fecha_con_hora($reserva["fecha_limite"]) ?></td>
                        <td class="<?= $ultimo_dia ? "text-amber fw-semibold" : "" ?>"><?= texto_tiempo_reserva($reserva["fecha_limite"]) ?></td>
                        <td>
                            <div class="d-flex justify-content-end gap-2">
                                <a href="comprobante.php?id=<?= (int) $reserva["id_reserva"] ?>" class="btn btn-sm btn-outline-green">
                                    <svg class="icon"><use href="#i-download" /></svg> Descargar comprobante
                                </a>
                                <form action="mi_reserva_cancelar.php" method="post" onsubmit="return confirm('¿Seguro que quieres cancelar esta reserva?');">
                                    <?= csrf_campo() ?>
                                    <input type="hidden" name="id_reserva" value="<?= (int) $reserva["id_reserva"] ?>" />
                                    <button type="submit" class="btn btn-sm btn-outline-red">Cancelar reserva</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include "includes/fin_cuenta.php"; ?>
