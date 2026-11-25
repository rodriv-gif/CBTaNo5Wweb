<?php
session_start();

// Candado de seguridad: solo permite eliminar libros con sesión de bibliotecario activa
if (!isset($_SESSION['id_bibliotecario'])) {
    header('Location: login.php?error=acceso_denegado');
    exit;
}

require_once __DIR__ . '/config/conexion.php';

$idLibro = $_GET['id'] ?? null;

// Valida que el ID recibido por GET sea numérico antes de procesar el borrado
if ($idLibro && is_numeric($idLibro)) {
    $idLibroInt = (int)$idLibro;

    // Busca el nombre de la portada antes de eliminar el registro para borrar el archivo físico
    $sqlBusq = "SELECT portada FROM tblpublicacion_catalogo WHERE id_libro = ?";
    $stmt    = mysqli_prepare($conexion, $sqlBusq);
    mysqli_stmt_bind_param($stmt, "i", $idLibroInt);
    mysqli_stmt_execute($stmt);
    $res  = mysqli_stmt_get_result($stmt);
    $dato = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);

    if ($dato && !empty($dato['portada'])) {
         $nombreLimpio = str_replace('libro_', 'libro', $dato['portada']);
         $rutaArchivo  = $dirPortadas . $nombreLimpio;
        if (file_exists($rutaArchivo)) {
            unlink($rutaArchivo);
        }
    }

    // Revisa si algún ejemplar tiene préstamos o reservas históricas ligadas por llave foránea
    $sqlUso  = "SELECT COUNT(*) AS total FROM tblejemplares e
                WHERE e.id_libro = ?
                  AND (e.id_ejemplar IN (SELECT id_ejemplar FROM tblprestamos)
                       OR e.id_ejemplar IN (SELECT id_ejemplar FROM tblreservas))";
    $stmtUso = mysqli_prepare($conexion, $sqlUso);
    mysqli_stmt_bind_param($stmtUso, "i", $idLibroInt);
    mysqli_stmt_execute($stmtUso);
    $resUso      = mysqli_stmt_get_result($stmtUso);
    $filaUso     = mysqli_fetch_assoc($resUso);
    $tieneUsos   = ((int)($filaUso['total'] ?? 0) > 0);
    mysqli_stmt_close($stmtUso);

    // Elimina por completo si es un libro sin préstamos previos o lo da de baja lógica si tiene historial
    $stmtPub = mysqli_prepare($conexion, "DELETE FROM tblpublicacion_catalogo WHERE id_libro = ?");
    mysqli_stmt_bind_param($stmtPub, "i", $idLibroInt);
    mysqli_stmt_execute($stmtPub);
    mysqli_stmt_close($stmtPub);

    if (!$tieneUsos) {
        $stmtAut = mysqli_prepare($conexion, "DELETE FROM tbllibro_autor WHERE id_libro = ?");
        mysqli_stmt_bind_param($stmtAut, "i", $idLibroInt);
        mysqli_stmt_execute($stmtAut);
        mysqli_stmt_close($stmtAut);

        $stmtEj = mysqli_prepare($conexion, "DELETE FROM tblejemplares WHERE id_libro = ?");
        mysqli_stmt_bind_param($stmtEj, "i", $idLibroInt);
        mysqli_stmt_execute($stmtEj);
        mysqli_stmt_close($stmtEj);

        $stmtDel = mysqli_prepare($conexion, "DELETE FROM tbllibros WHERE id_libro = ?");
        mysqli_stmt_bind_param($stmtDel, "i", $idLibroInt);
        mysqli_stmt_execute($stmtDel);
        mysqli_stmt_close($stmtDel);
    } else {
        $stmtBaja = mysqli_prepare($conexion, "UPDATE tbllibros SET estado = 'Inactivo' WHERE id_libro = ?");
        mysqli_stmt_bind_param($stmtBaja, "i", $idLibroInt);
        mysqli_stmt_execute($stmtBaja);
        mysqli_stmt_close($stmtBaja);
    }
}

header('Location: inventario_libros.php?eliminado=1');
exit;
?>