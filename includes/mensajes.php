<?php
// Mensajes de arriba de la página: el aviso de "guardado / error" que dejó otra página
// y el aviso amarillo si alguna consulta falló.
// Requiere que antes se haya cargado includes/utiles.php y includes/bd.php (las consultas_* ya lo hacen).

$mensaje = $mensaje_guardado ?? flash_tomar();
?>
<?php if ($mensaje): ?>
    <div class="alert alert-<?= $mensaje["tipo"] === "ok" ? "success" : "danger" ?> py-2" role="alert">
        <?= e($mensaje["texto"]) ?>
    </div>
<?php endif; ?>
<?= aviso_errores_bd() ?>
