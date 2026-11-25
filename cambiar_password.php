<?php
// Cambia la contraseña de quien tiene la sesión abierta (lector o bibliotecario).
// Lo llaman las dos ventanas "Cambiar contraseña": la de Mi cuenta y la de la barra lateral del bibliotecario.
// Los campos son pw_actual, pw_nueva y pw_confirmar.
//
// No lleva token CSRF porque pide la contraseña actual: sin ella, un formulario falso no puede cambiar nada.

require "includes/sesion.php";
require "config/conexion.php"; // aquí viene la conexión $pdo
require "includes/consultas_lector.php"; // también carga bd.php y utiles.php

$es_lector = es_lector();

if (!$es_lector && !isset($_SESSION["id_bibliotecario"])) {
    header("Location: login.php");
    exit();
}

// A dónde regresar. El lector siempre a Mi cuenta; el bibliotecario al panel
// (o a Ayuda, si venía de ahí), porque son las páginas donde se muestra el mensaje.
$volver = $es_lector ? "mi_cuenta.php" : "panel_control.php";

if (!$es_lector && basename(parse_url($_SERVER["HTTP_REFERER"] ?? "", PHP_URL_PATH) ?? "") === "ayuda_admin.php") {
    $volver = "ayuda_admin.php";
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: " . $volver);
    exit();
}

$actual = $_POST["pw_actual"] ?? "";
$nueva = $_POST["pw_nueva"] ?? "";
$confirmar = $_POST["pw_confirmar"] ?? "";

// Cada rol tiene su tabla; el lector lo busca por id_cuenta y el bibliotecario por id_bibliotecario
if ($es_lector) {
    $tabla = "tblcuentas_lector";
    $campo_id = "id_cuenta";
    $id = (int) ($_SESSION["id_cuenta"] ?? 0);
} else {
    $tabla = "tblbibliotecario";
    $campo_id = "id_bibliotecario";
    $id = (int) $_SESSION["id_bibliotecario"];
}

$guardada = consultar_valor($pdo, "SELECT contrasenia FROM $tabla WHERE $campo_id = ?", [$id]);

// Igual que el login: se acepta la contraseña con hash o la vieja en texto plano
$actual_correcta = $guardada !== 0 && $guardada !== "" && (password_verify($actual, $guardada) || $actual === $guardada);

if (!$actual_correcta) {
    flash_poner("error", "La contraseña actual no es correcta.", true);
} elseif (strlen($nueva) < 8) {
    flash_poner("error", "La contraseña nueva debe tener mínimo 8 caracteres.", true);
} elseif ($nueva !== $confirmar) {
    flash_poner("error", "La contraseña nueva y su confirmación no coinciden.", true);
} elseif ($nueva === $actual) {
    flash_poner("error", "La contraseña nueva debe ser distinta de la actual.", true);
} else {
    $hash = password_hash($nueva, PASSWORD_DEFAULT);
    $cambiadas = ejecutar($pdo, "UPDATE $tabla SET contrasenia = ? WHERE $campo_id = ?", [$hash, $id]);

    // Se vuelve a leer para confirmar que de verdad quedó guardada (la columna podría ser muy corta para el hash)
    $quedo = consultar_valor($pdo, "SELECT contrasenia FROM $tabla WHERE $campo_id = ?", [$id]);

    if ($cambiadas === false || !password_verify($nueva, (string) $quedo)) {
        // Si no quedó bien, se regresa la contraseña anterior para no dejar la cuenta inservible
        ejecutar($pdo, "UPDATE $tabla SET contrasenia = ? WHERE $campo_id = ?", [$guardada, $id]);
        flash_poner("error", "No se pudo guardar la contraseña. Intenta más tarde.", true);
    } else {
        flash_poner("ok", "Tu contraseña se cambió correctamente.");
    }
}

header("Location: " . $volver);
exit();
