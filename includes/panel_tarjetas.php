<?php
// Submódulo del Panel de control: las tres tarjetas de resumen.
// Las de préstamos muestran su tabla abajo; la de reservas va a Gestión de reservas.
// Usa las variables que arma panel_control.php: $ver, $total_activos, $total_vencidos y $total_reservas.
?>
<!-- Las tres tarjetas: las de préstamos muestran su tabla abajo; la de reservas va a Gestión de reservas -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <a href="panel_control.php?ver=activos#detalle" class="panel stat-card <?= $ver === "activos" ? "is-selected" : "" ?>">
            <span class="d-flex align-items-center gap-2">
                <span class="icon-circle"><svg class="icon"><use href="#i-clock" /></svg></span>
                <span class="h6 mb-0 fw-semibold">Préstamos activos</span>
            </span>
            <span class="stat-number d-block"><?= $total_activos ?></span>
            <span class="d-block small text-secondary mb-2">Sin devolución registrada. Incluye los vencidos.</span>
            <span class="stat-link"><?= $ver === "activos" ? "Viendo detalle" : "Ver detalle" ?> <svg class="icon"><use href="#i-arrow-right" /></svg></span>
        </a>
    </div>

    <div class="col-md-4">
        <a href="panel_control.php?ver=vencidos#detalle" class="panel stat-card <?= $ver === "vencidos" ? "is-selected" : "" ?>">
            <span class="d-flex align-items-center gap-2">
                <span class="icon-circle amber"><svg class="icon"><use href="#i-triangle-alert" /></svg></span>
                <span class="h6 mb-0 fw-semibold">Préstamos vencidos</span>
            </span>
            <span class="stat-number d-block"><?= $total_vencidos ?></span>
            <span class="d-block small text-secondary mb-2">Con la fecha límite ya pasada.</span>
            <span class="stat-link"><?= $ver === "vencidos" ? "Viendo detalle" : "Ver detalle" ?> <svg class="icon"><use href="#i-arrow-right" /></svg></span>
        </a>
    </div>

    <div class="col-md-4">
        <a href="gestion_reservas.php" class="panel stat-card">
            <span class="d-flex align-items-center gap-2">
                <span class="icon-circle blue"><svg class="icon"><use href="#i-bookmark" /></svg></span>
                <span class="h6 mb-0 fw-semibold">Reservas pendientes</span>
            </span>
            <span class="stat-number d-block"><?= $total_reservas ?></span>
            <span class="d-block small text-secondary mb-2">Reservas en estado Activa.</span>
            <span class="stat-link">Ir a Gestión de reservas <svg class="icon"><use href="#i-arrow-right" /></svg></span>
        </a>
    </div>
</div>
