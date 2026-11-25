<?php
// Funciones pequeñas que usan Mi cuenta, el Panel de control y Ayuda.

date_default_timezone_set("America/Mexico_City");

// Multa por cada día de retraso en la devolución
const MULTA_POR_DIA = 10;

// Escapa el texto antes de imprimirlo en el HTML (evita que alguien meta código en un nombre o título)
function e($texto)
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, "UTF-8");
}

// 2026-10-28 (o 2026-10-28 10:15:00) -> 28/10/2026
function fecha_corta($fecha)
{
    if (!$fecha) {
        return "—";
    }

    $tiempo = strtotime($fecha);

    if ($tiempo === false) {
        return "—";
    }

    return date("d/m/Y", $tiempo);
}

// 2026-10-28 10:15:00 -> 28/10/2026, 10:15
function fecha_con_hora($fecha)
{
    if (!$fecha) {
        return "—";
    }

    $tiempo = strtotime($fecha);

    if ($tiempo === false) {
        return "—";
    }

    return date("d/m/Y, H:i", $tiempo);
}

// Días que faltan para una fecha. Si ya pasó, regresa un número negativo.
// Compara solo el día, sin la hora.
function dias_para($fecha)
{
    $hoy = new DateTime(date("Y-m-d"));
    $limite = new DateTime(date("Y-m-d", strtotime($fecha)));

    return (int) $hoy->diff($limite)->format("%r%a");
}

// 1 -> "1 día", 3 -> "3 días"
function plural($cantidad, $singular, $plural)
{
    return $cantidad . " " . ($cantidad === 1 ? $singular : $plural);
}

// 20 -> $20
function dinero($monto)
{
    return '$' . number_format($monto, 0);
}

// Agrega a cada préstamo sus días restantes, si está vencido, los días de retraso y la multa
function calcular_prestamos($filas)
{
    $resultado = [];

    foreach ($filas as $fila) {
        $dias = dias_para($fila["fecha_devolucion"]);
        $retraso = $dias < 0 ? abs($dias) : 0;

        $fila["dias"] = $dias;
        $fila["vencido"] = $dias < 0;
        $fila["retraso"] = $retraso;
        $fila["multa"] = $retraso * MULTA_POR_DIA;

        $resultado[] = $fila;
    }

    return $resultado;
}

// Texto de la columna "Días restantes" de un préstamo
function texto_dias_prestamo($dias)
{
    if ($dias < 0) {
        return plural(abs($dias), "día", "días") . " de retraso";
    }

    if ($dias === 0) {
        return "Vence hoy";
    }

    return ($dias === 1 ? "Falta " : "Faltan ") . plural($dias, "día", "días");
}

// Segundos que faltan para una fecha con hora (negativo si ya pasó)
function segundos_para($fecha)
{
    return strtotime($fecha) - time();
}

// Texto de "Tiempo restante" de una reserva: Quedan 2 días, Queda 5 h, Quedan 20 min, Venció
function texto_tiempo_reserva($fecha_limite)
{
    $segundos = segundos_para($fecha_limite);

    if ($segundos < 0) {
        return "Venció";
    }

    $dias = (int) floor($segundos / 86400);
    $horas = (int) floor(($segundos % 86400) / 3600);
    $minutos = max(1, (int) floor(($segundos % 3600) / 60));

    if ($dias >= 1) {
        return ($dias === 1 ? "Queda " : "Quedan ") . plural($dias, "día", "días");
    }

    if ($horas >= 1) {
        return ($horas === 1 ? "Queda " : "Quedan ") . $horas . " h";
    }

    return "Quedan " . $minutos . " min";
}

// ---------- Mensajes que se muestran después de redirigir (guardar, cancelar, etc.) ----------

// $tipo: "ok" o "error". $en_ventana: true si el mensaje debe salir dentro de la ventana de contraseña
function flash_poner($tipo, $texto, $en_ventana = false)
{
    $_SESSION["flash"] = [
        "tipo" => $tipo,
        "texto" => $texto,
        "ventana" => $en_ventana,
    ];
}

// Regresa el mensaje guardado (o null) y lo borra para que salga una sola vez
function flash_tomar()
{
    $flash = $_SESSION["flash"] ?? null;
    unset($_SESSION["flash"]);

    return $flash;
}

// ---------- Protección de formularios (CSRF) ----------

function csrf_token()
{
    if (empty($_SESSION["csrf"])) {
        $_SESSION["csrf"] = bin2hex(random_bytes(32));
    }

    return $_SESSION["csrf"];
}

// Campo oculto que va dentro de cada formulario POST
function csrf_campo()
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '" />';
}

// true si el formulario enviado trae el token de esta sesión
function csrf_valido()
{
    $enviado = $_POST["csrf"] ?? "";

    return $enviado !== "" && hash_equals($_SESSION["csrf"] ?? "", $enviado);
}
