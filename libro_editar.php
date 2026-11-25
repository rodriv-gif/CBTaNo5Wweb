<?php
session_start();

// Candado de seguridad: verifica que exista sesión de bibliotecario activa
if (!isset($_SESSION['id_bibliotecario'])) {
    header('Location: login.php?error=acceso_denegado');
    exit;
}

require_once __DIR__ . '/config/conexion.php';
$pagina_activa = "inventario_libros.php";
$error         = '';
$idLibro       = $_GET['id'] ?? $_POST['id_libro'] ?? null;

if (!$idLibro || !is_numeric($idLibro)) {
    header('Location: inventario_libros.php');
    exit;
}
$idLibroInt = (int)$idLibro;

// Procesa la actualización (UPDATE) cuando el formulario se envía por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo      = trim($_POST['titulo_libro'] ?? '');
    $isbn        = trim($_POST['isbn'] ?? '');
    $paginas     = trim($_POST['paginas'] ?? '');
    $idCategoria = (int)($_POST['id_categoria'] ?? 0);
    $idEditorial = (int)($_POST['id_editorial'] ?? 0);
    $idIdioma    = (int)($_POST['id_idioma'] ?? 0);
    $idAutor     = (int)($_POST['id_autor'] ?? 0);
    $sinopsis    = trim($_POST['sinopsis'] ?? '');
    $publicado   = isset($_POST['publicado']) ? 1 : 0;
    $portada     = $_FILES['portada'] ?? null;

    if (empty($titulo) || empty($isbn) || $idCategoria <= 0 || $idEditorial <= 0 || $idIdioma <= 0 || $idAutor <= 0) {
        $error = 'Todos los campos obligatorios deben completarse.';
    } elseif ($paginas !== '' && (!is_numeric($paginas) || (int)$paginas <= 0)) {
        $error = 'El número de páginas debe ser mayor a 0.';
    } else {
        $sqlIsbn  = "SELECT id_libro FROM tbllibros WHERE ISBN = ? AND id_libro != ?";
        $stmtIsbn = mysqli_prepare($conexion, $sqlIsbn);
        mysqli_stmt_bind_param($stmtIsbn, "si", $isbn, $idLibroInt);
        mysqli_stmt_execute($stmtIsbn);
        $resIsbn = mysqli_stmt_get_result($stmtIsbn);
        if (mysqli_fetch_assoc($resIsbn)) {
            $error = 'Otro libro ya utiliza ese código ISBN.';
        }
        mysqli_stmt_close($stmtIsbn);
    }

    // Consulta la portada actual para conservarla o sustituirla si suben una nueva
    $portadaActual = null;
    $stmtPort      = mysqli_prepare($conexion, "SELECT portada FROM tblpublicacion_catalogo WHERE id_libro = ?");
    mysqli_stmt_bind_param($stmtPort, "i", $idLibroInt);
    mysqli_stmt_execute($stmtPort);
    $resPort = mysqli_stmt_get_result($stmtPort);
    if ($filaPort = mysqli_fetch_assoc($resPort)) {
        $portadaActual = $filaPort['portada'];
    }
    mysqli_stmt_close($stmtPort);

    $portadaFinal = $portadaActual;
    if (empty($error) && $portada && $portada['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($portada['error'] !== UPLOAD_ERR_OK) {
            $error = 'Ocurrió un error al subir la nueva portada.';
        } else {
            $maxSize = 2 * 1024 * 1024;
            if ($portada['size'] > $maxSize) {
                $error = 'La portada supera el peso máximo de 2MB.';
            }

            $extPermitidas  = ['jpg', 'jpeg', 'png', 'webp'];
            $mimePermitidos = ['image/jpeg', 'image/png', 'image/webp'];
            $ext            = strtolower(pathinfo($portada['name'], PATHINFO_EXTENSION));

            $finfo    = finfo_open(FILEINFO_MIME_TYPE);
            $mimeReal = finfo_file($finfo, $portada['tmp_name']);
            finfo_close($finfo);

            if (!in_array($ext, $extPermitidas) || !in_array($mimeReal, $mimePermitidos)) {
                $error = 'Formato no válido. Solo se admiten imágenes JPG, PNG y WEBP.';
            }

            if (empty($error)) {
              $nombreNuevo = 'libro' . $idLibroInt . '.' . $ext;
                if (!file_exists($dirPortadas)) {
                  mkdir($dirPortadas, 0777, true);
             }
                    $anteriorLimpia = str_replace('libro_', 'libro', $portadaActual ?? '');
             if ($anteriorLimpia !== '' && $anteriorLimpia !== $nombreNuevo && file_exists($dirPortadas . $anteriorLimpia)) {
                unlink($dirPortadas . $anteriorLimpia);
                 }
            if (move_uploaded_file($portada['tmp_name'], $dirPortadas . $nombreNuevo)) {
                 $portadaFinal = $nombreNuevo;
                } else {
                 $error = 'No se pudo guardar la nueva portada en el servidor.';
                 }

            }
        }
    }

    // Actualiza los datos en tbllibros, tbllibro_autor y tblpublicacion_catalogo
    if (empty($error)) {
        $paginasInt = ($paginas !== '') ? (int)$paginas : null;
        $sqlUpd     = "UPDATE tbllibros SET id_editorial = ?, id_categoria = ?, id_idioma = ?, titulo_libro = ?, paginas = ?, ISBN = ? WHERE id_libro = ?";
        $stmtUpd    = mysqli_prepare($conexion, $sqlUpd);

        if ($stmtUpd) {
            mysqli_stmt_bind_param($stmtUpd, "iiisisi", $idEditorial, $idCategoria, $idIdioma, $titulo, $paginasInt, $isbn, $idLibroInt);
            if (mysqli_stmt_execute($stmtUpd)) {
                mysqli_stmt_close($stmtUpd);

                $stmtDelAut = mysqli_prepare($conexion, "DELETE FROM tbllibro_autor WHERE id_libro = ?");
                mysqli_stmt_bind_param($stmtDelAut, "i", $idLibroInt);
                mysqli_stmt_execute($stmtDelAut);
                mysqli_stmt_close($stmtDelAut);

                $stmtInsAut = mysqli_prepare($conexion, "INSERT INTO tbllibro_autor (id_libro, id_autor) VALUES (?, ?)");
                mysqli_stmt_bind_param($stmtInsAut, "ii", $idLibroInt, $idAutor);
                mysqli_stmt_execute($stmtInsAut);
                mysqli_stmt_close($stmtInsAut);

                $stmtExistePub = mysqli_prepare($conexion, "SELECT id_libro FROM tblpublicacion_catalogo WHERE id_libro = ?");
                mysqli_stmt_bind_param($stmtExistePub, "i", $idLibroInt);
                mysqli_stmt_execute($stmtExistePub);
                $resExistePub = mysqli_stmt_get_result($stmtExistePub);
                $tieneRegistroPub = mysqli_fetch_assoc($resExistePub);
                mysqli_stmt_close($stmtExistePub);

                if ($tieneRegistroPub) {
                    $sqlPub  = "UPDATE tblpublicacion_catalogo SET portada = ?, sinopsis = ?, publicado = ?, fecha_publicacion = NOW() WHERE id_libro = ?";
                    $stmtPub = mysqli_prepare($conexion, $sqlPub);
                    mysqli_stmt_bind_param($stmtPub, "ssii", $portadaFinal, $sinopsis, $publicado, $idLibroInt);
                } else {
                    $sqlPub  = "INSERT INTO tblpublicacion_catalogo (id_libro, portada, sinopsis, publicado, fecha_publicacion) VALUES (?, ?, ?, ?, NOW())";
                    $stmtPub = mysqli_prepare($conexion, $sqlPub);
                    mysqli_stmt_bind_param($stmtPub, "issi", $idLibroInt, $portadaFinal, $sinopsis, $publicado);
                }
                mysqli_stmt_execute($stmtPub);
                mysqli_stmt_close($stmtPub);

                header('Location: inventario_libros.php?categoria=' . $idCategoria . '&actualizado=1');
                exit;
            } else {
                $error = 'Error al actualizar el libro.';
            }
        }
    }
}

// Consulta los datos actuales del libro para precargar el formulario de edición
$sqlActual = "SELECT l.*, pc.portada, pc.sinopsis, COALESCE(pc.publicado, 0) AS publicado,
              (SELECT la.id_autor FROM tbllibro_autor la WHERE la.id_libro = l.id_libro LIMIT 1) AS id_autor
              FROM tbllibros l
              LEFT JOIN tblpublicacion_catalogo pc ON pc.id_libro = l.id_libro
              WHERE l.id_libro = ? AND l.estado = 'Activo'";
$stmtActual = mysqli_prepare($conexion, $sqlActual);
mysqli_stmt_bind_param($stmtActual, "i", $idLibroInt);
mysqli_stmt_execute($stmtActual);
$resActual = mysqli_stmt_get_result($stmtActual);
$libro     = mysqli_fetch_assoc($resActual);
mysqli_stmt_close($stmtActual);

if (!$libro) {
    header('Location: inventario_libros.php');
    exit;
}

// Prepara el buscador rápido por categoría en la parte superior para cambiar de libro fácilmente
$catFiltro = (isset($_GET['cat_filtro']) && is_numeric($_GET['cat_filtro'])) ? (int)$_GET['cat_filtro'] : (int)$libro['id_categoria'];
$sqlRapida = "SELECT id_libro, titulo_libro FROM tbllibros WHERE estado = 'Activo' AND id_categoria = ? ORDER BY titulo_libro ASC";
$stmtRap   = mysqli_prepare($conexion, $sqlRapida);
mysqli_stmt_bind_param($stmtRap, "i", $catFiltro);
mysqli_stmt_execute($stmtRap);
$resLibrosCat = mysqli_stmt_get_result($stmtRap);

$resCategoriasFiltro = mysqli_query($conexion, "SELECT id_categoria, nombre FROM tblcategorias ORDER BY nombre ASC");
$resCategorias       = mysqli_query($conexion, "SELECT id_categoria, nombre FROM tblcategorias ORDER BY nombre ASC");
$resEditoriales      = mysqli_query($conexion, "SELECT id_editorial, nombre_editorial FROM tbleditoriales ORDER BY nombre_editorial ASC");
$resIdiomas          = mysqli_query($conexion, "SELECT id_idioma, nombre_idioma FROM tblidiomas ORDER BY nombre_idioma ASC");
$resAutores          = mysqli_query($conexion, "SELECT id_autor, CONCAT_WS(' ', nombre, apellido_paterno, apellido_materno) AS nombre_completo FROM tblautores ORDER BY nombre ASC");

$nombrePortada = str_replace('libro_', 'libro', $libro['portada'] ?? '');
$rutaPortada   = ($nombrePortada !== '') ? $dirPortadas . $nombrePortada : '';
$tienePortada  = ($rutaPortada !== '' && file_exists($rutaPortada));
?>
<!doctype html>
<html lang="es">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Editar libro - Biblioteca Escolar CBTa No. 5</title>

        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="./css/bootstrap.min.css" />
        <link rel="stylesheet" href="./css/estilos.css" />
        <link rel="stylesheet" href="./css/bibliotecario.css" />
    </head>

    <body>
        <?php include "includes/iconos.php"; ?>
        <?php include "includes/iconos_catalogo.php"; ?>

        <div class="admin-layout">
            <?php include "includes/sidebar_bibliotecario.php"; ?>

            <main class="admin-main">
                <div class="admin-content">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                        <div>
                            <h1 class="page-title">Editar libro (ID: <?= $libro['id_libro'] ?>)</h1>
                            <p class="page-subtitle mb-0">Modifica los datos del libro o selecciona otra categoría arriba para buscar rápido otro título.</p>
                        </div>
                        <a href="inventario_libros.php" class="btn btn-outline-brand">
                            <svg class="icon"><use href="#i-arrow-left" /></svg> Volver al inventario
                        </a>
                    </div>

                    <div class="panel p-3 mb-4">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-5">
                                <form method="GET" action="libro_editar.php">
                                    <input type="hidden" name="id" value="<?= $libro['id_libro'] ?>" />
                                    <label for="cat_filtro" class="form-label small fw-semibold mb-1">1. Selecciona categoría para buscar rápido:</label>
                                    <select name="cat_filtro" id="cat_filtro" class="form-select" onchange="this.form.submit()">
                                        <?php while ($cf = mysqli_fetch_assoc($resCategoriasFiltro)): ?>
                                            <option value="<?= $cf['id_categoria'] ?>" <?= $catFiltro === (int)$cf['id_categoria'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($cf['nombre']) ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </form>
                            </div>

                            <div class="col-md-7">
                                <form method="GET" action="libro_editar.php" class="d-flex gap-2 align-items-end">
                                    <input type="hidden" name="cat_filtro" value="<?= $catFiltro ?>" />
                                    <div class="flex-grow-1">
                                        <label for="selector_libro" class="form-label small fw-semibold mb-1">2. Libros guardados en esta categoría:</label>
                                        <select name="id" id="selector_libro" class="form-select" onchange="this.form.submit()">
                                            <?php if (mysqli_num_rows($resLibrosCat) > 0): ?>
                                                <?php while ($item = mysqli_fetch_assoc($resLibrosCat)): ?>
                                                    <option value="<?= $item['id_libro'] ?>" <?= $idLibroInt === (int)$item['id_libro'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($item['titulo_libro']) ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <option value="<?= $libro['id_libro'] ?>">No hay libros en esta categoría</option>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-outline-brand">Cargar</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form id="formLibro" method="POST" action="" enctype="multipart/form-data" novalidate class="panel p-4">
                        <input type="hidden" name="id_libro" value="<?= $libro['id_libro'] ?>" />

                        <div class="row g-4">
                            <div class="col-lg-7">
                                <h2 class="h5 fw-semibold section-green-title mb-3">Datos del libro</h2>

                                <div class="mb-3">
                                    <label for="titulo_libro" class="form-label fw-medium">Título del libro *</label>
                                    <input type="text" name="titulo_libro" id="titulo_libro" class="form-control" value="<?= htmlspecialchars($_POST['titulo_libro'] ?? $libro['titulo_libro']) ?>" />
                                    <div id="feedback-titulo" class="invalid-feedback"></div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-sm-6">
                                        <label for="id_autor" class="form-label fw-medium">Autor *</label>
                                        <?php $autorSel = (int)($_POST['id_autor'] ?? $libro['id_autor']); ?>
                                        <select name="id_autor" id="id_autor" class="form-select">
                                            <option value="0">Selecciona un autor</option>
                                            <?php while ($aut = mysqli_fetch_assoc($resAutores)): ?>
                                                <option value="<?= $aut['id_autor'] ?>" <?= $autorSel === (int)$aut['id_autor'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($aut['nombre_completo']) ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                        <div id="feedback-autor" class="invalid-feedback"></div>
                                    </div>

                                    <div class="col-sm-6">
                                        <label for="id_categoria" class="form-label fw-medium">Categoría *</label>
                                        <?php $catSel = (int)($_POST['id_categoria'] ?? $libro['id_categoria']); ?>
                                        <select name="id_categoria" id="id_categoria" class="form-select">
                                            <option value="0">Selecciona una categoría</option>
                                            <?php while ($cat = mysqli_fetch_assoc($resCategorias)): ?>
                                                <option value="<?= $cat['id_categoria'] ?>" <?= $catSel === (int)$cat['id_categoria'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($cat['nombre']) ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                        <div id="feedback-categoria" class="invalid-feedback"></div>
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-sm-6">
                                        <label for="id_editorial" class="form-label fw-medium">Editorial *</label>
                                        <?php $edSel = (int)($_POST['id_editorial'] ?? $libro['id_editorial']); ?>
                                        <select name="id_editorial" id="id_editorial" class="form-select">
                                            <option value="0">Selecciona una editorial</option>
                                            <?php while ($ed = mysqli_fetch_assoc($resEditoriales)): ?>
                                                <option value="<?= $ed['id_editorial'] ?>" <?= $edSel === (int)$ed['id_editorial'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($ed['nombre_editorial']) ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                        <div id="feedback-editorial" class="invalid-feedback"></div>
                                    </div>

                                    <div class="col-sm-6">
                                        <label for="id_idioma" class="form-label fw-medium">Idioma *</label>
                                        <?php $idmSel = (int)($_POST['id_idioma'] ?? $libro['id_idioma']); ?>
                                        <select name="id_idioma" id="id_idioma" class="form-select">
                                            <option value="0">Selecciona un idioma</option>
                                            <?php while ($idm = mysqli_fetch_assoc($resIdiomas)): ?>
                                                <option value="<?= $idm['id_idioma'] ?>" <?= $idmSel === (int)$idm['id_idioma'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($idm['nombre_idioma']) ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                        <div id="feedback-idioma" class="invalid-feedback"></div>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <label for="isbn" class="form-label fw-medium">ISBN *</label>
                                        <input type="text" name="isbn" id="isbn" class="form-control" value="<?= htmlspecialchars($_POST['isbn'] ?? $libro['ISBN']) ?>" />
                                        <div id="feedback-isbn" class="invalid-feedback"></div>
                                    </div>

                                    <div class="col-sm-6">
                                        <label for="paginas" class="form-label fw-medium">Páginas</label>
                                        <input type="number" min="1" name="paginas" id="paginas" class="form-control" value="<?= htmlspecialchars($_POST['paginas'] ?? ($libro['paginas'] ?? '')) ?>" />
                                        <div id="feedback-paginas" class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-5">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h2 class="h5 fw-semibold section-green-title mb-0">Publicación en catálogo</h2>
                                    <?php if ((int)$libro['publicado'] === 1): ?>
                                        <span class="tag tag-ok"><svg class="icon"><use href="#i-circle-check" /></svg> Publicado</span>
                                    <?php else: ?>
                                        <span class="tag tag-off"><svg class="icon"><use href="#i-circle-x" /></svg> No publicado</span>
                                    <?php endif; ?>
                                </div>

                                <div class="d-flex gap-3 align-items-center mb-3">
                                    <?php if ($tienePortada): ?>
                                        <img src="./uploads/portadas/<?= htmlspecialchars($libro['portada']) ?>" alt="Portada actual" class="cover-mini" />
                                    <?php else: ?>
                                        <div class="cover-mini d-flex align-items-center justify-content-center text-white text-center small fw-semibold">
                                            <?= htmlspecialchars($libro['titulo_libro']) ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="flex-grow-1">
                                        <label for="portada" class="form-label fw-medium">Reemplazar portada</label>
                                        <input type="file" name="portada" id="portada" class="form-control form-control-sm" accept="image/jpeg, image/png, image/webp" />
                                        <p class="small text-secondary mt-1 mb-0">JPG, PNG o WEBP hasta 2 MB.</p>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="sinopsis" class="form-label fw-medium">Sinopsis</label>
                                    <textarea name="sinopsis" id="sinopsis" rows="4" maxlength="1000" class="form-control"><?= htmlspecialchars($_POST['sinopsis'] ?? ($libro['sinopsis'] ?? '')) ?></textarea>
                                    <p id="contador-sinopsis" class="small text-secondary text-end mt-1 mb-0">0 / 1,000 caracteres</p>
                                </div>

                                <div class="form-check mb-4">
                                    <?php
                                    $esPublicado = ($_SERVER['REQUEST_METHOD'] === 'POST') ? isset($_POST['publicado']) : ((int)$libro['publicado'] === 1);
                                    ?>
                                    <input type="checkbox" name="publicado" id="publicado" class="form-check-input" value="1" <?= $esPublicado ? 'checked' : '' ?> />
                                    <label for="publicado" class="form-check-label fw-medium">Publicar en el catálogo</label>
                                </div>

                                <button type="submit" class="btn btn-brand w-100 py-2">Actualizar libro</button>
                            </div>
                        </div>
                    </form>
                </div>
            </main>
        </div>

        <?php mysqli_stmt_close($stmtRap); ?>
        <?php include "includes/footer.php"; ?>
        <script src="./js/ver_password.js"></script>
        <script src="./js/validar_libro.js"></script>
        <script src="./js/bootstrap.bundle.min.js"></script>
    </body>
</html>