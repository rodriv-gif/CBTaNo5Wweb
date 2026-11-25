<?php
session_start();

// Candado de seguridad: solo permite el acceso si hay sesión de bibliotecario activa
if (!isset($_SESSION['id_bibliotecario'])) {
    header('Location: login.php?error=acceso_denegado');
    exit;
}

require_once __DIR__ . '/config/conexion.php';
$pagina_activa = "inventario_libros.php";

// Consulta las categorías para filtrar rápidamente los libros del inventario
$sqlCategorias = "SELECT id_categoria, nombre FROM tblcategorias ORDER BY nombre ASC";
$resCategorias = mysqli_query($conexion, $sqlCategorias);

// Recibe los parámetros de búsqueda, categoría y número de página desde la URL
$busqueda     = trim($_GET['q'] ?? '');
$idCategoria  = (isset($_GET['categoria']) && is_numeric($_GET['categoria'])) ? (int)$_GET['categoria'] : 0;
$paginaActual = (isset($_GET['p']) && is_numeric($_GET['p']) && (int)$_GET['p'] > 0) ? (int)$_GET['p'] : 1;
$porPagina    = 6;
$like         = '%' . $busqueda . '%';

// Cuenta el total de libros activos que coinciden con el filtro para calcular las páginas
$sqlConteo = "SELECT COUNT(DISTINCT l.id_libro) AS total
              FROM tbllibros l
              INNER JOIN tblcategorias c ON c.id_categoria = l.id_categoria
              LEFT JOIN tbllibro_autor la ON la.id_libro = l.id_libro
              LEFT JOIN tblautores a ON a.id_autor = la.id_autor
              WHERE l.estado = 'Activo'
                AND (? = '' OR l.titulo_libro LIKE ? OR l.ISBN LIKE ? OR CONCAT_WS(' ', a.nombre, a.apellido_paterno, a.apellido_materno) LIKE ?)
                AND (? = 0 OR l.id_categoria = ?)";

$totalLibros = 0;
$stmtConteo  = mysqli_prepare($conexion, $sqlConteo);
if ($stmtConteo) {
    mysqli_stmt_bind_param($stmtConteo, "ssssii", $busqueda, $like, $like, $like, $idCategoria, $idCategoria);
    mysqli_stmt_execute($stmtConteo);
    $resConteo = mysqli_stmt_get_result($stmtConteo);
    if ($filaConteo = mysqli_fetch_assoc($resConteo)) {
        $totalLibros = (int)$filaConteo['total'];
    }
    mysqli_stmt_close($stmtConteo);
}

$totalPaginas = $totalLibros > 0 ? (int)ceil($totalLibros / $porPagina) : 1;
if ($paginaActual > $totalPaginas) {
    $paginaActual = $totalPaginas;
}
$offset = ($paginaActual - 1) * $porPagina;

// Obtiene únicamente los libros de la página actual ordenados del más reciente al más antiguo
$sql = "SELECT l.id_libro, l.titulo_libro, l.ISBN, c.nombre AS categoria,
        pc.portada, COALESCE(pc.publicado, 0) AS publicado,
        GROUP_CONCAT(DISTINCT CONCAT_WS(' ', a.nombre, a.apellido_paterno, a.apellido_materno) SEPARATOR ', ') AS autor,
        (SELECT COUNT(*) FROM tblejemplares e WHERE e.id_libro = l.id_libro AND e.estado = 'Activo') AS total_ejemplares
        FROM tbllibros l
        INNER JOIN tblcategorias c ON c.id_categoria = l.id_categoria
        LEFT JOIN tblpublicacion_catalogo pc ON pc.id_libro = l.id_libro
        LEFT JOIN tbllibro_autor la ON la.id_libro = l.id_libro
        LEFT JOIN tblautores a ON a.id_autor = la.id_autor
        WHERE l.estado = 'Activo'
          AND (? = '' OR l.titulo_libro LIKE ? OR l.ISBN LIKE ? OR CONCAT_WS(' ', a.nombre, a.apellido_paterno, a.apellido_materno) LIKE ?)
          AND (? = 0 OR l.id_categoria = ?)
        GROUP BY l.id_libro, l.titulo_libro, l.ISBN, c.nombre, pc.portada, pc.publicado
        ORDER BY l.id_libro DESC
        LIMIT ? OFFSET ?";

$resultado = false;
$stmt      = mysqli_prepare($conexion, $sql);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ssssiiii", $busqueda, $like, $like, $like, $idCategoria, $idCategoria, $porPagina, $offset);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
}
?>
<!doctype html>
<html lang="es">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Inventario de libros - Biblioteca Escolar CBTa No. 5</title>

        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="./css/bootstrap.min.css" />
        <link rel="stylesheet" href="./css/estilos.css" />
        <link rel="stylesheet" href="./css/bibliotecario.css" />
    </head>

    <body class="d-flex flex-column min-vh-100">
        <?php include "includes/iconos.php"; ?>
        <?php include "includes/iconos_catalogo.php"; ?>

        <div class="admin-layout flex-grow-1">
            <?php include "includes/sidebar_bibliotecario.php"; ?>

            <main class="admin-main">
                <div class="admin-content">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                        <div>
                            <h1 class="page-title">Inventario de libros</h1>
                            <p class="page-subtitle mb-0">Filtra por categoría para verificar tus libros registrados, edita su publicación o elimínalos.</p>
                        </div>
                        <a href="libro_agregar.php" class="btn btn-brand">
                            <svg class="icon"><use href="#i-plus" /></svg> Dar de alta libro
                        </a>
                    </div>

                    <?php if (isset($_GET['guardado'])): ?>
                        <div class="alert alert-success py-2">El libro se dio de alta correctamente en el inventario.</div>
                    <?php elseif (isset($_GET['actualizado'])): ?>
                        <div class="alert alert-success py-2">Los datos del libro se actualizaron correctamente.</div>
                    <?php elseif (isset($_GET['eliminado'])): ?>
                        <div class="alert alert-warning py-2">El libro fue eliminado del inventario y del catálogo.</div>
                    <?php endif; ?>

                    <form method="GET" action="inventario_libros.php" class="panel p-3 mb-4">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-5">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0">
                                        <svg class="icon text-secondary"><use href="#i-search" /></svg>
                                    </span>
                                    <input
                                        type="search"
                                        name="q"
                                        class="form-control border-start-0"
                                        value="<?= htmlspecialchars($busqueda) ?>"
                                        placeholder="Buscar por título, autor o ISBN"
                                    />
                                </div>
                            </div>

                            <div class="col-md-4">
                                <select name="categoria" class="form-select" onchange="this.form.submit()" aria-label="Filtrar por categoría">
                                    <option value="0">Todas las categorías</option>
                                    <?php if ($resCategorias && mysqli_num_rows($resCategorias) > 0): ?>
                                        <?php while ($cat = mysqli_fetch_assoc($resCategorias)): ?>
                                            <option value="<?= $cat['id_categoria'] ?>" <?= $idCategoria === (int)$cat['id_categoria'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($cat['nombre']) ?>
                                            </option>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="col-md-3 d-flex gap-2">
                                <button type="submit" class="btn btn-brand flex-grow-1">Filtrar</button>
                                <a href="inventario_libros.php" class="btn btn-outline-brand">Limpiar</a>
                            </div>
                        </div>
                    </form>

                    <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                        <span class="small text-secondary">Mostrando <?= $resultado ? mysqli_num_rows($resultado) : 0 ?> de <?= $totalLibros ?> libros</span>
                        <span class="small text-secondary">Página <?= $paginaActual ?> de <?= $totalPaginas ?></span>
                    </div>

                    <section class="panel overflow-hidden">
                        <div class="table-responsive">
                            <table class="table table-hover table-lib align-middle">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Libro</th>
                                        <th>Categoría</th>
                                        <th>Ejemplares</th>
                                        <th>Catálogo</th>
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($resultado && mysqli_num_rows($resultado) > 0): ?>
                                        <?php while ($libro = mysqli_fetch_assoc($resultado)): ?>
                                            <?php
                                            $nombrePortada = str_replace('libro_', 'libro', $libro['portada'] ?? '');
                                            $rutaPortada   = ($nombrePortada !== '') ? $dirPortadas . $nombrePortada : '';
                                            $tienePortada  = ($rutaPortada !== '' && file_exists($rutaPortada));
                                            $autorTexto    = trim($libro['autor'] ?? '') !== '' ? $libro['autor'] : 'Sin autor';
                                            ?>
                                            <tr>
                                                <td class="fw-semibold"><?= $libro['id_libro'] ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-3">
                                                        <?php if ($tienePortada): ?>
                                                            <img src="<?= htmlspecialchars($urlPortadas . $nombrePortada) ?>" alt="Portada" class="thumb-cover" />
                                                        <?php else: ?>
                                                            <div class="thumb-cover">CBTa</div>
                                                        <?php endif; ?>
                                                        <div>
                                                            <span class="fw-semibold"><?= htmlspecialchars($libro['titulo_libro']) ?></span>
                                                            <span class="sub"><?= htmlspecialchars($autorTexto) ?> · ISBN: <?= htmlspecialchars($libro['ISBN']) ?></span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?= htmlspecialchars($libro['categoria']) ?></td>
                                                <td><?= (int)$libro['total_ejemplares'] ?></td>
                                                <td>
                                                    <?php if ((int)$libro['publicado'] === 1): ?>
                                                        <span class="tag tag-ok">
                                                            <svg class="icon"><use href="#i-circle-check" /></svg> Publicado
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="tag tag-off">
                                                            <svg class="icon"><use href="#i-circle-x" /></svg> No publicado
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end text-nowrap">
                                                    <a href="libro_editar.php?id=<?= $libro['id_libro'] ?>" class="btn btn-outline-brand btn-sm me-1">
                                                        <svg class="icon"><use href="#i-pencil" /></svg> Editar
                                                    </a>
                                                    <a
                                                        href="libro_eliminar.php?id=<?= $libro['id_libro'] ?>"
                                                        class="btn btn-danger-soft btn-sm"
                                                        onclick="return confirm('¿Seguro que deseas eliminar el libro: <?= htmlspecialchars($libro['titulo_libro'], ENT_QUOTES) ?>?');"
                                                    >
                                                        <svg class="icon"><use href="#i-trash" /></svg> Eliminar
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">No hay libros registrados con los filtros seleccionados.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <?php if ($totalPaginas > 1): ?>
                        <nav class="pagination-admin d-flex justify-content-center mt-4" aria-label="Paginación de inventario">
                            <ul class="pagination mb-0">
                                <li class="page-item <?= $paginaActual <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="inventario_libros.php?q=<?= urlencode($busqueda) ?>&categoria=<?= $idCategoria ?>&p=<?= $paginaActual - 1 ?>">
                                        <svg class="icon"><use href="#i-arrow-left" /></svg> Anterior
                                    </a>
                                </li>

                                <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                                    <li class="page-item <?= $i === $paginaActual ? 'active' : '' ?>">
                                        <a class="page-link" href="inventario_libros.php?q=<?= urlencode($busqueda) ?>&categoria=<?= $idCategoria ?>&p=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <li class="page-item <?= $paginaActual >= $totalPaginas ? 'disabled' : '' ?>">
                                    <a class="page-link" href="inventario_libros.php?q=<?= urlencode($busqueda) ?>&categoria=<?= $idCategoria ?>&p=<?= $paginaActual + 1 ?>">
                                        Siguiente <svg class="icon"><use href="#i-arrow-right" /></svg>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </main>
        </div>

        <?php
        if ($stmt) {
            mysqli_stmt_close($stmt);
        }
        include "includes/footer.php";
        ?>
        <script src="./js/ver_password.js"></script>
    </body>
</html>