<?php
// Consultas del Panel de control del bibliotecario.
//
// Usa las mismas tablas y estados que gestion_reservas.php:
//   tblprestamos : id_estado_prestamo (1 y 3 = en curso), fecha_prestamo, fecha_devolucion, matricula, numero_control
//   tblreservas  : id_estado_reserva (1 = Activa), fecha_limite

require_once __DIR__ . "/bd.php";
require_once __DIR__ . "/utiles.php";
require_once __DIR__ . "/consultas_lector.php"; // aquí están PRESTAMO_EN_CURSO y RESERVA_ACTIVA

// Préstamos que siguen con el lector (incluye los vencidos)
function contar_prestamos_activos($pdo)
{
    return (int) consultar_valor(
        $pdo,
        "SELECT COUNT(*) FROM tblprestamos WHERE id_estado_prestamo IN (" . PRESTAMO_EN_CURSO . ")"
    );
}

// Préstamos en curso cuya fecha límite ya pasó (la fecha de hoy no cuenta como vencido)
function contar_prestamos_vencidos($pdo)
{
    return (int) consultar_valor(
        $pdo,
        "SELECT COUNT(*) FROM tblprestamos
         WHERE id_estado_prestamo IN (" . PRESTAMO_EN_CURSO . ") AND fecha_devolucion < ?",
        [date("Y-m-d")]
    );
}

// Reservas Activas que todavía no vencen (el mismo número que la pestaña "Activas" de Gestión de reservas)
function contar_reservas_pendientes($pdo)
{
    return (int) consultar_valor(
        $pdo,
        "SELECT COUNT(*) FROM tblreservas WHERE id_estado_reserva = " . RESERVA_ACTIVA . " AND fecha_limite >= ?",
        [date("Y-m-d H:i:s")]
    );
}

// Lista de préstamos para la tabla del panel.
// $solo_vencidos = true: solo los vencidos. false: todos los activos.
// Primero salen los de más retraso.
function prestamos_del_panel($pdo, $solo_vencidos, $limite = 100)
{
    $sql = "SELECT p.matricula, p.numero_control, p.fecha_prestamo, p.fecha_devolucion,
                   e.inventario, l.titulo_libro,
                   a.nombre AS a_nombre, a.apellido_paterno AS a_paterno, a.apellido_materno AS a_materno,
                   t.nombre AS t_nombre, t.apellido_paterno AS t_paterno, t.apellido_materno AS t_materno
            FROM tblprestamos p
            INNER JOIN tblejemplares e ON e.id_ejemplar = p.id_ejemplar
            INNER JOIN tbllibros l ON l.id_libro = e.id_libro
            LEFT JOIN tblalumnos a ON a.matricula = p.matricula
            LEFT JOIN tbltrabajadores t ON t.numero_control = p.numero_control
            WHERE p.id_estado_prestamo IN (" . PRESTAMO_EN_CURSO . ")";

    $parametros = [];

    if ($solo_vencidos) {
        $sql .= " AND p.fecha_devolucion < ?";
        $parametros[] = date("Y-m-d");
    }

    $sql .= " ORDER BY p.fecha_devolucion ASC LIMIT " . (int) $limite;

    $filas = calcular_prestamos(consultar($pdo, $sql, $parametros));

    // Se arma el nombre y la clave del lector (alumno o trabajador)
    foreach ($filas as $i => $fila) {
        $es_trabajador = !empty($fila["numero_control"]);
        $partes = $es_trabajador
            ? [$fila["t_nombre"], $fila["t_paterno"], $fila["t_materno"]]
            : [$fila["a_nombre"], $fila["a_paterno"], $fila["a_materno"]];

        $nombre = trim(implode(" ", array_filter($partes)));

        $filas[$i]["lector"] = $nombre !== "" ? $nombre : "Sin nombre";
        $filas[$i]["clave"] = $es_trabajador ? $fila["numero_control"] : $fila["matricula"];
        $filas[$i]["tipo_lector"] = $es_trabajador ? "Trabajador" : "Alumno";
    }

    return $filas;
}
