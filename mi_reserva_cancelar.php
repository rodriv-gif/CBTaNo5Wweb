<?php
// El lector cancela una de sus reservas Activas (botón "Cancelar reserva" de Mis reservas).
// Es distinto de reserva_cancelar.php, que es el del bibliotecario.
// Solo funciona por POST, con el token de la sesión, y solo sobre reservas del propio lector.

require "includes/sesion.php";
exigir_lector();
require "config/conexion.php"; // aquí viene la conexión $pdo
require "includes/consultas_lector.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST" || !csrf_valido()) {
    flash_poner("error", "No se pudo cancelar la reserva. Intenta de nuevo.");
} elseif (cancelar_reserva_del_lector($pdo, (int) ($_POST["id_reserva"] ?? 0))) {
    flash_poner("ok", "Tu reserva se canceló y el ejemplar quedó libre.");
} else {
    flash_poner("error", "No encontramos esa reserva o ya no está activa.");
}

header("Location: mis_reservas.php");
exit();
