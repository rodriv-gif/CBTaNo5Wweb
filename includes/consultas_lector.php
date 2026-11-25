<?php
// Consultas de Mi cuenta del lector (préstamos, reservas, adeudos y avisos).
//
// Usa las tablas que ya tiene el proyecto, no crea ninguna nueva:
//   tblprestamos : id_ejemplar, matricula, numero_control, id_estado_prestamo, fecha_prestamo, fecha_devolucion
//   tblreservas  : id_reserva, folio, id_ejemplar, matricula, numero_control, id_estado_reserva, fecha_reserva, fecha_limite
//   tblejemplares: id_ejemplar, id_libro, inventario
//   tbllibros    : id_libro, titulo_libro
//   tblalumnos / tbltrabajadores : datos de la persona
//
// Estados que ya usa el resto del proyecto:
//   reserva  -> 1 Activa, 2 Confirmada (ya es préstamo), 3 Cancelada
//   préstamo -> 1 y 3 son "en curso" (el catálogo los toma como ejemplar ocupado)
//
// La base no tiene tabla de adeudos ni de avisos, así que se calculan con estos mismos datos:
//   - Multa     = días de retraso x MULTA_POR_DIA, de los préstamos en curso con la fecha límite pasada.
//   - Avisos    = se arman al momento con los préstamos y las reservas del lector.

require_once __DIR__ . "/bd.php";
require_once __DIR__ . "/utiles.php";

// Estados de tblprestamos en los que el libro sigue con el lector
const PRESTAMO_EN_CURSO = "1, 3";

// Estados de tblreservas
const RESERVA_ACTIVA = 1;
const RESERVA_CONFIRMADA = 2;
const RESERVA_CANCELADA = 3;

// ---------- Quién es el lector ----------

// Los alumnos se identifican por matrícula y los trabajadores por número de control.
// Regresa [condición SQL, parámetros] para usarla en un WHERE.
function condicion_lector($alias = "")
{
    $prefijo = $alias === "" ? "" : $alias . ".";
    $matricula = $_SESSION["matricula"] ?? "";

    if ($matricula !== "" && $matricula !== null) {
        return [$prefijo . "matricula = ?", [$matricula]];
    }

    return [$prefijo . "numero_control = ?", [$_SESSION["numero_control"] ?? 0]];
}

// "Alumno" o "Trabajador", según con qué clave entró
function tipo_de_lector()
{
    return ($_SESSION["matricula"] ?? "") !== "" ? "Alumno" : "Trabajador";
}

// Nombre completo del lector (en la sesión solo está el nombre de pila)
function nombre_completo_del_lector($pdo)
{
    $matricula = $_SESSION["matricula"] ?? "";

    if ($matricula !== "" && $matricula !== null) {
        $fila = consultar_una($pdo, "SELECT nombre, apellido_paterno, apellido_materno FROM tblalumnos WHERE matricula = ?", [$matricula], false);
    } else {
        $fila = consultar_una($pdo, "SELECT nombre, apellido_paterno, apellido_materno FROM tbltrabajadores WHERE numero_control = ?", [$_SESSION["numero_control"] ?? 0], false);
    }

    if (!$fila) {
        return $_SESSION["nombre"] ?? "";
    }

    return trim(($fila["nombre"] ?? "") . " " . ($fila["apellido_paterno"] ?? "") . " " . ($fila["apellido_materno"] ?? ""));
}

// Carrera o especialidad del alumno, si la tabla de alumnos tiene esa columna. Si no, regresa ""
// (y Mi cuenta muestra solo Alumno / Trabajador).
function carrera_del_lector($pdo)
{
    $matricula = $_SESSION["matricula"] ?? "";

    if ($matricula === "" || $matricula === null) {
        return "";
    }

    $alumno = consultar_una($pdo, "SELECT * FROM tblalumnos WHERE matricula = ?", [$matricula], false);

    foreach (["carrera", "especialidad"] as $columna) {
        if (!empty($alumno[$columna])) {
            return (string) $alumno[$columna];
        }
    }

    return "";
}

// ---------- Préstamos ----------

// Préstamos en curso del lector, del que vence primero al que vence después
function prestamos_del_lector($pdo)
{
    [$condicion, $parametros] = condicion_lector("p");

    $filas = consultar(
        $pdo,
        "SELECT p.fecha_prestamo, p.fecha_devolucion, e.inventario, l.titulo_libro
         FROM tblprestamos p
         INNER JOIN tblejemplares e ON e.id_ejemplar = p.id_ejemplar
         INNER JOIN tbllibros l ON l.id_libro = e.id_libro
         WHERE $condicion AND p.id_estado_prestamo IN (" . PRESTAMO_EN_CURSO . ")
         ORDER BY p.fecha_devolucion ASC",
        $parametros
    );

    return calcular_prestamos($filas);
}

// ---------- Reservas ----------

// Reservas Activas del lector que todavía no vencen
function reservas_del_lector($pdo)
{
    [$condicion, $parametros] = condicion_lector("r");
    $parametros[] = date("Y-m-d H:i:s");

    return consultar(
        $pdo,
        "SELECT r.id_reserva, r.folio, r.fecha_reserva, r.fecha_limite, e.inventario, l.titulo_libro
         FROM tblreservas r
         INNER JOIN tblejemplares e ON e.id_ejemplar = r.id_ejemplar
         INNER JOIN tbllibros l ON l.id_libro = e.id_libro
         WHERE $condicion AND r.id_estado_reserva = " . RESERVA_ACTIVA . " AND r.fecha_limite >= ?
         ORDER BY r.fecha_limite ASC",
        $parametros
    );
}

// Una reserva Activa de este lector (para el comprobante). Si es de otra persona regresa null.
function reserva_del_lector($pdo, $id_reserva)
{
    [$condicion, $parametros] = condicion_lector("r");
    array_unshift($parametros, $id_reserva);
    $parametros[] = date("Y-m-d H:i:s");

    return consultar_una(
        $pdo,
        "SELECT r.id_reserva, r.folio, r.fecha_reserva, r.fecha_limite,
                e.id_libro, e.inventario, l.titulo_libro
         FROM tblreservas r
         INNER JOIN tblejemplares e ON e.id_ejemplar = r.id_ejemplar
         INNER JOIN tbllibros l ON l.id_libro = e.id_libro
         WHERE r.id_reserva = ? AND $condicion AND r.id_estado_reserva = " . RESERVA_ACTIVA . " AND r.fecha_limite >= ?",
        $parametros
    );
}

// Cancela una reserva Activa del lector. Regresa true si se canceló.
// Usa el mismo cambio que reserva_cancelar.php del bibliotecario (estado 3), así el ejemplar queda libre.
function cancelar_reserva_del_lector($pdo, $id_reserva)
{
    [$condicion, $parametros] = condicion_lector();
    array_unshift($parametros, $id_reserva);

    $cambiadas = ejecutar(
        $pdo,
        "UPDATE tblreservas SET id_estado_reserva = " . RESERVA_CANCELADA . "
         WHERE id_reserva = ? AND $condicion AND id_estado_reserva = " . RESERVA_ACTIVA,
        $parametros
    );

    return $cambiadas === 1;
}

// Autores de un libro, separados por coma ("" si no tiene)
function autores_del_libro($pdo, $id_libro)
{
    $filas = consultar(
        $pdo,
        "SELECT a.nombre, a.apellido_paterno, a.apellido_materno
         FROM tbllibro_autor la
         INNER JOIN tblautores a ON a.id_autor = la.id_autor
         WHERE la.id_libro = ?",
        [$id_libro],
        false
    );

    $nombres = [];

    foreach ($filas as $fila) {
        $nombres[] = trim(($fila["nombre"] ?? "") . " " . ($fila["apellido_paterno"] ?? "") . " " . ($fila["apellido_materno"] ?? ""));
    }

    return implode(", ", $nombres);
}

// ---------- Adeudos ----------

// Multas pendientes: un préstamo en curso con la fecha límite vencida genera $10 por día.
// Regresa [lista de multas, total].
function adeudos_del_lector($pdo)
{
    $multas = [];
    $total = 0;

    foreach (prestamos_del_lector($pdo) as $prestamo) {
        if ($prestamo["vencido"]) {
            $multas[] = $prestamo;
            $total += $prestamo["multa"];
        }
    }

    return [$multas, $total];
}

// ---------- Avisos ----------

// Avisos del lector, del más reciente al más antiguo. Cada uno trae:
// icono, titulo, texto, fecha (Y-m-d H:i:s), nuevo (true/false).
// "Nuevo" son los que piden atención (vencidos, por vencer) y los de los últimos 2 días.
function avisos_del_lector($pdo)
{
    $avisos = [];
    $hace_2_dias = strtotime("-2 days");

    foreach (prestamos_del_lector($pdo) as $p) {
        if ($p["vencido"]) {
            $avisos[] = [
                "icono" => "i-triangle-alert",
                "titulo" => "Préstamo vencido",
                "texto" => "Tu préstamo de " . $p["titulo_libro"] . " tiene " . plural($p["retraso"], "día", "días")
                    . " de retraso. Multa acumulada: " . dinero($p["multa"]) . ".",
                "fecha" => $p["fecha_devolucion"],
                "nuevo" => true,
            ];
        } elseif ($p["dias"] <= 2) {
            $cuando = $p["dias"] === 0 ? "vence hoy" : ($p["dias"] === 1 ? "vence mañana" : "vence en 2 días");
            $avisos[] = [
                "icono" => "i-clock",
                "titulo" => "Préstamo por vencer",
                "texto" => "Tu préstamo de " . $p["titulo_libro"] . " " . $cuando . " (" . fecha_corta($p["fecha_devolucion"]) . ").",
                "fecha" => $p["fecha_devolucion"],
                "nuevo" => true,
            ];
        }
    }

    [$condicion, $parametros] = condicion_lector("r");
    $parametros[] = date("Y-m-d H:i:s", strtotime("-7 days"));

    $reservas = consultar(
        $pdo,
        "SELECT r.id_estado_reserva, r.fecha_reserva, r.fecha_limite, l.titulo_libro
         FROM tblreservas r
         INNER JOIN tblejemplares e ON e.id_ejemplar = r.id_ejemplar
         INNER JOIN tbllibros l ON l.id_libro = e.id_libro
         WHERE $condicion AND r.fecha_reserva >= ?
         ORDER BY r.fecha_reserva DESC",
        $parametros
    );

    foreach ($reservas as $r) {
        $estado = (int) $r["id_estado_reserva"];
        $reciente = strtotime($r["fecha_reserva"]) >= $hace_2_dias;

        if ($estado === RESERVA_ACTIVA && segundos_para($r["fecha_limite"]) >= 0) {
            $urgente = segundos_para($r["fecha_limite"]) < 86400;

            $avisos[] = [
                "icono" => $urgente ? "i-clock" : "i-bookmark",
                "titulo" => $urgente ? "Reserva por vencer" : "Reserva registrada",
                "texto" => "Recoge " . $r["titulo_libro"] . " antes del " . fecha_con_hora($r["fecha_limite"]) . ".",
                "fecha" => $r["fecha_reserva"],
                "nuevo" => $urgente || $reciente,
            ];
        } elseif ($estado === RESERVA_ACTIVA) {
            $avisos[] = [
                "icono" => "i-circle-x",
                "titulo" => "Reserva vencida",
                "texto" => "No recogiste " . $r["titulo_libro"] . " a tiempo y la reserva venció.",
                "fecha" => $r["fecha_limite"],
                "nuevo" => false,
            ];
        } elseif ($estado === RESERVA_CONFIRMADA) {
            $avisos[] = [
                "icono" => "i-circle-check",
                "titulo" => "Reserva entregada",
                "texto" => "Recogiste " . $r["titulo_libro"] . " y quedó registrado como préstamo.",
                "fecha" => $r["fecha_reserva"],
                "nuevo" => false,
            ];
        } else {
            $avisos[] = [
                "icono" => "i-circle-x",
                "titulo" => "Reserva cancelada",
                "texto" => "Tu reserva de " . $r["titulo_libro"] . " (hecha el " . fecha_corta($r["fecha_reserva"]) . ") fue cancelada.",
                "fecha" => $r["fecha_reserva"],
                "nuevo" => false,
            ];
        }
    }

    // Más reciente primero
    usort($avisos, function ($a, $b) {
        return strtotime($b["fecha"]) <=> strtotime($a["fecha"]);
    });

    return $avisos;
}

// Cuántos avisos nuevos tiene (el número rojo de la campana)
function contar_avisos_nuevos($pdo)
{
    $nuevos = 0;

    foreach (avisos_del_lector($pdo) as $aviso) {
        if ($aviso["nuevo"]) {
            $nuevos++;
        }
    }

    return $nuevos;
}
