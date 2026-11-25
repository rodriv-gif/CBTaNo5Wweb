<?php
require "includes/sesion.php";
exigir_lector();
require "config/conexion.php"; // aquí viene la conexión $pdo
require "includes/consultas_lector.php";
$pagina_activa = "mi_cuenta.php";
$titulo = "Mis avisos";
$subtitulo = "Del más reciente al más antiguo.";
$icono = "i-bell";

// Avisos armados con tus préstamos y reservas (vencimientos, reservas registradas, canceladas, etc.)
$avisos = avisos_del_lector($pdo);

include "includes/inicio_cuenta.php";
?>
<div class="panel overflow-hidden">
    <?php if (count($avisos) === 0): ?>
        <p class="text-center text-secondary py-5 mb-0">No tienes avisos por ahora.</p>
    <?php endif; ?>

    <?php foreach ($avisos as $aviso): ?>
        <div class="notice d-flex align-items-start gap-3 px-4 py-3 <?= $aviso["nuevo"] ? "notice-new" : "" ?>">
            <div class="icon-circle notice-icon">
                <svg class="icon"><use href="#<?= e($aviso["icono"]) ?>" /></svg>
            </div>
            <div class="flex-grow-1">
                <h2 class="h6 fw-semibold mb-1 d-flex align-items-center gap-2">
                    <?= e($aviso["titulo"]) ?>
                    <?php if ($aviso["nuevo"]): ?>
                        <span class="tag tag-new">Nuevo</span>
                    <?php endif; ?>
                </h2>
                <p class="text-secondary mb-0"><?= e($aviso["texto"]) ?></p>
            </div>
            <span class="small text-secondary"><?= fecha_corta($aviso["fecha"]) ?></span>
        </div>
    <?php endforeach; ?>
</div>
<?php include "includes/fin_cuenta.php"; ?>
