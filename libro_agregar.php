<?php
session_start();

// Candado de seguridad: verifica que exista sesión de bibliotecario activa
if (!isset($_SESSION['id_bibliotecario'])) {
    header('Location: login.php?error=acceso_denegado');
    exit;
}

require_once __DIR__ . '/config/conexion.php';
$pagina_activa = "libro_agregar.php";
$error = '';

// Procesa el formulario cuando se envía mediante el método POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo      = trim($_POST['titulo_libro'] ?? '');
    $isbn        = trim($_POST['isbn'] ?? '');
    $paginas     = trim($_POST['paginas'] ?? '');
    $idCategoria = (int)($_POST['id_categoria'] ?? 0);
    $idEditorial = (int)($_POST['id_editorial'] ?? 0);
    $idIdioma    = (int)($_POST['id_idioma'] ?? 0);
    $idAutor     = (int)($_POST['id_autor'] ?? 0);
    $ejemplares  = trim($_POST['ejemplares'] ?? '1');
    $ubicacion   = trim($_POST['localizacion'] ?? '');
    $sinopsis    = trim($_POST['sinopsis'] ?? '');
    $publicado   = isset($_POST['publicado']) ? 1 : 0;
    $portada     = $_FILES['portada'] ?? null;

    // Valida en el servidor los campos obligatorios y numéricos
    if (empty($titulo) || empty($isbn) || $idCategoria <= 0 || $idEditorial <= 0 || $idIdioma <= 0 || $idAutor <= 0) {
        $error = 'Servidor: Todos los campos marcados como obligatorios deben completarse.';
    } elseif ($paginas !== '' && (!is_numeric($paginas) || (int)$paginas <= 0)) {
        $error = 'Servidor: El número de páginas debe ser mayor a 0.';
    } elseif (!is_numeric($ejemplares) || (int)$ejemplares < 0) {
        $error = 'Servidor: La cantidad de ejemplares debe ser un número mayor o igual a 0.';
    } else {
        // Comprueba que el código ISBN no esté repetido en la base de datos
        $sqlIsbn  = "SELECT id_libro FROM tbllibros WHERE ISBN = ?";
        $stmtIsbn = mysqli_prepare($conexion, $sqlIsbn);
        mysqli_stmt_bind_param($stmtIsbn, "s", $isbn);
        mysqli_stmt_execute($stmtIsbn);
        $resIsbn = mysqli_stmt_get_result($stmtIsbn);
        if (mysqli_fetch_assoc($resIsbn)) {
            $error = 'Servidor: Ya existe un libro registrado con ese ISBN.';
        }
        mysqli_stmt_close($stmtIsbn);
    }

    // Valida formato y peso de la portada antes de insertar el libro en la base de datos
    $subioPortada = false;
    $ext          = '';
    if (empty($error) && $portada && $portada['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($portada['error'] !== UPLOAD_ERR_OK) {
            $error = 'Ocurrió un error al subir la portada.';
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
                $subioPortada = true;
            }
        }
    }

    // Inserta el libro primero para obtener su ID y nombrar la portada como libro + id
    if (empty($error)) {
        $paginasInt = ($paginas !== '') ? (int)$paginas : null;

        $sqlLibro  = "INSERT INTO tbllibros (id_editorial, id_categoria, id_idioma, titulo_libro, paginas, ISBN, estado) VALUES (?, ?, ?, ?, ?, ?, 'Activo')";
        $stmtLibro = mysqli_prepare($conexion, $sqlLibro);

        if ($stmtLibro) {
            mysqli_stmt_bind_param($stmtLibro, "iiisis", $idEditorial, $idCategoria, $idIdioma, $titulo, $paginasInt, $isbn);
            if (mysqli_stmt_execute($stmtLibro)) {
                $nuevoIdLibro = mysqli_insert_id($conexion);
                mysqli_stmt_close($stmtLibro);

                // Guarda el archivo físico en upload/libros/principales/ con el nombre libro + id_libro
                $nombrePortada = null;
                if ($subioPortada) {
                    if (!file_exists($dirPortadas)) {
                        mkdir($dirPortadas, 0777, true);
                    }
                    $nombreCandidato = 'libro' . $nuevoIdLibro . '.' . $ext;
                    if (move_uploaded_file($portada['tmp_name'], $dirPortadas . $nombreCandidato)) {
                        $nombrePortada = $nombreCandidato;
                    }
                }

                $sqlAutor  = "INSERT INTO tbllibro_autor (id_libro, id_autor) VALUES (?, ?)";
                $stmtAutor = mysqli_prepare($conexion, $sqlAutor);
                mysqli_stmt_bind_param($stmtAutor, "ii", $nuevoIdLibro, $idAutor);
                mysqli_stmt_execute($stmtAutor);
                mysqli_stmt_close($stmtAutor);

                $sqlPub  = "INSERT INTO tblpublicacion_catalogo (id_libro, portada, sinopsis, publicado, fecha_publicacion) VALUES (?, ?, ?, ?, NOW())";
                $stmtPub = mysqli_prepare($conexion, $sqlPub);
                mysqli_stmt_bind_param($stmtPub, "issi", $nuevoIdLibro, $nombrePortada, $sinopsis, $publicado);
                mysqli_stmt_execute($stmtPub);
                mysqli_stmt_close($stmtPub);

                $cantEjemplares = (int)$ejemplares;
                $ubicacionFinal = $ubicacion !== '' ? $ubicacion : 'Estante General';
                if ($cantEjemplares > 0) {
                    $sqlEj  = "INSERT INTO tblejemplares (id_libro, localizacion, inventario, fecha_adquisicion, estado) VALUES (?, ?, ?, CURDATE(), 'Activo')";
                    $stmtEj = mysqli_prepare($conexion, $sqlEj);
                    for ($numInv = 1; $numInv <= $cantEjemplares; $numInv++) {
                        mysqli_stmt_bind_param($stmtEj, "isi", $nuevoIdLibro, $ubicacionFinal, $numInv);
                        mysqli_stmt_execute($stmtEj);
                    }
                    mysqli_stmt_close($stmtEj);
                }

                header('Location: inventario_libros.php?categoria=' . $idCategoria . '&guardado=1');
                exit;
            } else {
                $error = 'Error al registrar el libro: ' . mysqli_error($conexion);
            }
        }
    }
}

// Consulta catálogos de categorías, editoriales, idiomas y autores para los select del formulario
$resCategorias  = mysqli_query($conexion, "SELECT id_categoria, nombre FROM tblcategorias ORDER BY nombre ASC");
$resEditoriales = mysqli_query($conexion, "SELECT id_editorial, nombre_editorial FROM tbleditoriales ORDER BY nombre_editorial ASC");
$resIdiomas     = mysqli_query($conexion, "SELECT id_idioma, nombre_idioma FROM tblidiomas ORDER BY nombre_idioma ASC");
$resAutores     = mysqli_query($conexion, "SELECT id_autor, CONCAT_WS(' ', nombre, apellido_paterno, apellido_materno) AS nombre_completo FROM tblautores ORDER BY nombre ASC");
?>
<!doctype html>
<html lang="es">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Alta de libro - Biblioteca Escolar CBTa No. 5</title>

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
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                        <div>
                            <h1 class="page-title">Alta de libro</h1>
                            <p class="page-subtitle mb-0">Registra un nuevo libro en el inventario y configura su publicación en el catálogo.</p>
                        </div>
                        <a href="inventario_libros.php" class="btn btn-outline-brand">
                            <svg class="icon"><use href="#i-arrow-left" /></svg> Ir al inventario
                        </a>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form id="formLibro" method="POST" action="" enctype="multipart/form-data" novalidate class="panel p-4">
                        <div class="row g-4">
                            <div class="col-lg-7">
                                <h2 class="h5 fw-semibold section-green-title mb-3">Datos generales del libro</h2>

                                <div class="mb-3">
                                    <label for="titulo_libro" class="form-label fw-medium">Título del libro *</label>
                                    <input type="text" name="titulo_libro" id="titulo_libro" class="form-control" value="<?= htmlspecialchars($_POST['titulo_libro'] ?? '') ?>" />
                                    <div id="feedback-titulo" class="invalid-feedback"></div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-sm-6">
                                        <label for="id_autor" class="form-label fw-medium">Autor *</label>
                                        <select name="id_autor" id="id_autor" class="form-select">
                                            <option value="0">Selecciona un autor</option>
                                            <?php while ($aut = mysqli_fetch_assoc($resAutores)): ?>
                                                <option value="<?= $aut['id_autor'] ?>" <?= ((int)($_POST['id_autor'] ?? 0) === (int)$aut['id_autor']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($aut['nombre_completo']) ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                        <div id="feedback-autor" class="invalid-feedback"></div>
                                    </div>

                                    <div class="col-sm-6">
                                        <label for="id_categoria" class="form-label fw-medium">Categoría *</label>
                                        <select name="id_categoria" id="id_categoria" class="form-select">
                                            <option value="0">Selecciona una categoría</option>
                                            <?php while ($cat = mysqli_fetch_assoc($resCategorias)): ?>
                                                <option value="<?= $cat['id_categoria'] ?>" <?= ((int)($_POST['id_categoria'] ?? 0) === (int)$cat['id_categoria']) ? 'selected' : '' ?>>
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
                                        <select name="id_editorial" id="id_editorial" class="form-select">
                                            <option value="0">Selecciona una editorial</option>
                                            <?php while ($ed = mysqli_fetch_assoc($resEditoriales)): ?>
                                                <option value="<?= $ed['id_editorial'] ?>" <?= ((int)($_POST['id_editorial'] ?? 0) === (int)$ed['id_editorial']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($ed['nombre_editorial']) ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                        <div id="feedback-editorial" class="invalid-feedback"></div>
                                    </div>

                                    <div class="col-sm-6">
                                        <label for="id_idioma" class="form-label fw-medium">Idioma *</label>
                                        <select name="id_idioma" id="id_idioma" class="form-select">
                                            <option value="0">Selecciona un idioma</option>
                                            <?php while ($idm = mysqli_fetch_assoc($resIdiomas)): ?>
                                                <option value="<?= $idm['id_idioma'] ?>" <?= ((int)($_POST['id_idioma'] ?? 0) === (int)$idm['id_idioma']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($idm['nombre_idioma']) ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                        <div id="feedback-idioma" class="invalid-feedback"></div>
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-sm-6">
                                        <label for="isbn" class="form-label fw-medium">ISBN *</label>
                                        <input type="text" name="isbn" id="isbn" class="form-control" value="<?= htmlspecialchars($_POST['isbn'] ?? '') ?>" />
                                        <div id="feedback-isbn" class="invalid-feedback"></div>
                                    </div>

                                    <div class="col-sm-6">
                                        <label for="paginas" class="form-label fw-medium">Páginas</label>
                                        <input type="number" min="1" name="paginas" id="paginas" class="form-control" value="<?= htmlspecialchars($_POST['paginas'] ?? '') ?>" />
                                        <div id="feedback-paginas" class="invalid-feedback"></div>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-sm-5">
                                        <label for="ejemplares" class="form-label fw-medium">Ejemplares iniciales *</label>
                                        <input type="number" min="0" name="ejemplares" id="ejemplares" class="form-control" value="<?= htmlspecialchars($_POST['ejemplares'] ?? '1') ?>" />
                                        <div id="feedback-ejemplares" class="invalid-feedback"></div>
                                    </div>

                                    <div class="col-sm-7">
                                        <label for="localizacion" class="form-label fw-medium">Ubicación en estante</label>
                                        <input type="text" name="localizacion" id="localizacion" class="form-control" placeholder="Ej. Estante A1, Nivel 2" value="<?= htmlspecialchars($_POST['localizacion'] ?? '') ?>" />
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-5 border-start-lg">
                                <h2 class="h5 fw-semibold section-green-title mb-3">Publicación en catálogo</h2>

                                <div class="mb-3">
                                    <label for="portada" class="form-label fw-medium">Portada (opcional)</label>
                                    <input type="file" name="portada" id="portada" class="form-control" accept="image/jpeg, image/png, image/webp" />
                                    <div class="form-text">JPG, PNG o WEBP hasta 2 MB. Se guardará como libro + ID en principales.</div>
                                </div>

                                <div class="mb-3">
                                    <label for="sinopsis" class="form-label fw-medium">Sinopsis</label>
                                    <textarea name="sinopsis" id="sinopsis" rows="5" maxlength="1000" class="form-control" placeholder="Escribe de qué trata el libro"><?= htmlspecialchars($_POST['sinopsis'] ?? '') ?></textarea>
                                    <p id="contador-sinopsis" class="small text-secondary text-end mt-1 mb-0">0 / 1,000 caracteres</p>
                                </div>

                                <div class="form-check mb-4">
                                    <?php
                                    $marcarPublicado = ($_SERVER['REQUEST_METHOD'] === 'POST') ? isset($_POST['publicado']) : true;
                                    ?>
                                    <input type="checkbox" name="publicado" id="publicado" class="form-check-input" value="1" <?= $marcarPublicado ? 'checked' : '' ?> />
                                    <label for="publicado" class="form-check-label fw-medium">Publicar en el catálogo</label>
                                    <p class="small text-secondary mb-0">Si lo desmarcas, quedará guardado en inventario sin mostrarse a los lectores.</p>
                                </div>

                                <button type="submit" class="btn btn-brand w-100 py-2">Guardar libro</button>
                            </div>
                        </div>
                    </form>
                </div>
            </main>
        </div>
         <?php include "includes/footer.php"; ?>
        <script src="./js/ver_password.js"></script>                                      
        <script src="./js/validar_libro.js"></script>
        <script src="./js/bootstrap.bundle.min.js"></script>
    </body>
</html>