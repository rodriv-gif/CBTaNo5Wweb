<?php
// Ayudas para consultar la base de datos sin que la página se caiga si una consulta falla.
// Necesita que antes se haya cargado config/conexion.php (ahí se crea $pdo).

require_once __DIR__ . "/utiles.php";

// Cámbialo a false cuando todo funcione, para que el usuario no vea detalles técnicos
const MODO_DEPURACION = true;

// Guarda (y devuelve) la lista de errores de consulta de esta petición
function errores_bd($mensaje = null)
{
    static $errores = [];

    if ($mensaje !== null) {
        $errores[] = $mensaje;
    }

    return $errores;
}

// SELECT: regresa todas las filas (o una lista vacía si falla la consulta).
// $avisar = false sirve para datos opcionales: si fallan, no se muestra el aviso amarillo.
function consultar($pdo, $sql, $parametros = [], $avisar = true)
{
    try {
        $consulta = $pdo->prepare($sql);
        $consulta->execute($parametros);

        return $consulta->fetchAll();
    } catch (PDOException $error) {
        error_log("Consulta fallida: " . $error->getMessage());

        if ($avisar) {
            errores_bd($error->getMessage());
        }

        return [];
    }
}

// SELECT de una sola fila: regresa un arreglo o null
function consultar_una($pdo, $sql, $parametros = [], $avisar = true)
{
    $filas = consultar($pdo, $sql, $parametros, $avisar);

    return $filas[0] ?? null;
}

// SELECT de un solo valor (por ejemplo un COUNT): regresa el valor o 0
function consultar_valor($pdo, $sql, $parametros = [], $avisar = true)
{
    $fila = consultar_una($pdo, $sql, $parametros, $avisar);

    if ($fila === null) {
        return 0;
    }

    return array_values($fila)[0];
}

// INSERT, UPDATE o DELETE: regresa cuántas filas cambió, o false si falla
function ejecutar($pdo, $sql, $parametros = [])
{
    try {
        $consulta = $pdo->prepare($sql);
        $consulta->execute($parametros);

        return $consulta->rowCount();
    } catch (PDOException $error) {
        error_log("Consulta fallida: " . $error->getMessage());
        errores_bd($error->getMessage());

        return false;
    }
}

// Aviso amarillo que se pone arriba de la página cuando alguna consulta falló
function aviso_errores_bd()
{
    $errores = errores_bd();

    if (count($errores) === 0) {
        return "";
    }

    $html = '<div class="alert alert-warning" role="alert">';
    $html .= "No se pudo cargar parte de la información. Intenta de nuevo en un momento.";

    if (MODO_DEPURACION) {
        $html .= '<ul class="small mb-0 mt-2">';

        foreach (array_unique($errores) as $error) {
            $html .= "<li>" . e($error) . "</li>";
        }

        $html .= "</ul>";
    }

    $html .= "</div>";

    return $html;
}
