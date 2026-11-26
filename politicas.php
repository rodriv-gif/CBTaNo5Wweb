<?php require "includes/sesion.php"; ?>
<!doctype html>
<html lang="es">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />

        <title>Políticas y condiciones del servicio bibliotecario - Biblioteca Escolar</title>

        <!-- Fuente Poppins (guia de estilos) -->
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />

        <link rel="stylesheet" href="./css/bootstrap.min.css" />
        <link rel="stylesheet" href="./css/politicas.css" />
        <link rel="stylesheet" href="./css/footer.css" />
        <link rel="stylesheet" href="./css/header_lector.css" />
    </head>

    <body data-bs-spy="scroll" data-bs-target="#policyIndex" data-bs-offset="40">
        <!-- Iconos (set Lucide, trazo lineal) -->
        <svg xmlns="http://www.w3.org/2000/svg" style="display: none">
            <symbol id="i-circle-check" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" />
                <path d="m9 12 2 2 4-4" />
            </symbol>
            <symbol id="i-arrow-left" viewBox="0 0 24 24">
                <path d="m12 19-7-7 7-7" />
                <path d="M19 12H5" />
            </symbol>
            <symbol id="i-map-pin" viewBox="0 0 24 24">
                <path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0" />
                <circle cx="12" cy="10" r="3" />
            </symbol>
            <symbol id="i-mail" viewBox="0 0 24 24">
                <rect width="20" height="16" x="2" y="4" rx="2" />
                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
            </symbol>
            <symbol id="i-phone" viewBox="0 0 24 24">
                <path
                    d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"
                />
            </symbol>
        </svg>

        <!-- Encabezado -->
        <?php if (es_lector()): ?>
            <?php include "includes/iconos.php"; ?>
            <?php include "includes/header_lector.php"; ?>
        <?php else: ?>
        <header class="site-header d-flex align-items-center">
            <nav class="navbar navbar-expand-lg py-2 w-100">
                <div class="container">
                    <a class="navbar-brand d-flex align-items-center gap-2 m-0" href="index.php">
                        <img src="./img/logo-cbta5-blanco.png" alt="Logotipo del CBTa No. 5" class="brand-logo" />
                        <span>
                            <span class="brand-name d-block">Biblioteca Escolar</span>
                            <span class="brand-sub d-block">CBTa No. 5 · Huejutla, Hgo.</span>
                        </span>
                    </a>

                    <button
                        class="navbar-toggler border-light"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#mainMenu"
                        aria-controls="mainMenu"
                        aria-expanded="false"
                        aria-label="Abrir menú"
                    >
                        <span class="navbar-toggler-icon" style="filter: invert(1)"></span>
                    </button>

                    <div class="collapse navbar-collapse" id="mainMenu">
                        <ul class="navbar-nav mx-lg-auto mb-3 mb-lg-0 gap-lg-3">
                            <li class="nav-item"><a class="nav-link px-lg-3" href="index.php">Inicio</a></li>
                            <li class="nav-item"><a class="nav-link px-lg-3" href="nosotros.php">Nosotros</a></li>
                            <li class="nav-item"><a class="nav-link px-lg-3" href="preguntas.php">Preguntas</a></li>
                            <li class="nav-item">
                                <a class="nav-link px-lg-3 active" aria-current="page" href="politicas.php">Políticas</a>
                            </li>
                        </ul>

                        <div class="d-flex gap-2">
                            <a href="registro.php" class="btn btn-brand-outline">Registrarse</a>
                            <a href="login.php" class="btn btn-brand-light">Iniciar sesión</a>
                        </div>
                    </div>
                </div>
            </nav>
        </header>
        <?php endif; ?>

        <main class="page-block">
            <div class="container">
                <div class="breadcrumb-line">
                    <a href="index.php">Inicio</a>
                    <span>›</span>
                    <span>Políticas y condiciones del servicio bibliotecario</span>
                </div>

                <h1 class="page-title">Políticas y condiciones del servicio bibliotecario</h1>
                <p class="page-lead">
                    Conoce las reglas para reservar, pedir prestados y devolver libros, y cómo cuidamos tus datos al usar la biblioteca del
                    CBTa No. 5.
                </p>

                <div class="row g-4 mt-2">
                    <!-- Indice de la pagina -->
                    <div class="col-lg-3">
                        <nav class="index-card" id="policyIndex">
                            <h2>En esta página</h2>
                            <div class="nav flex-column">
                                <a class="nav-link" href="#politica1"> <span>1</span>Objetivo del servicio </a>
                                <a class="nav-link" href="#politica2"> <span>2</span>Quién puede usar el servicio </a>
                                <a class="nav-link" href="#politica3"> <span>3</span>Cuenta y seguridad </a>
                                <a class="nav-link" href="#politica4"> <span>4</span>Reservas </a>
                                <a class="nav-link" href="#politica5"> <span>5</span>Préstamos y devoluciones </a>
                                <a class="nav-link" href="#politica6"> <span>6</span>Retrasos, multas y reposición </a>
                                <a class="nav-link" href="#politica7"> <span>7</span>Privacidad y datos personales </a>
                            </div>
                        </nav>
                    </div>

                    <!-- Secciones -->
                    <div class="col-lg-9">
                        <section class="policy-card" id="politica1">
                            <div class="policy-head">
                                <div class="policy-number">1</div>
                                <h2>Objetivo del servicio</h2>
                            </div>

                            <p>
                                La biblioteca escolar del CBTa No. 5 pone a disposición de la comunidad su acervo, la reserva de libros
                                desde la web y la app, y el préstamo en la biblioteca o a domicilio. Estas políticas explican cómo usar el
                                servicio y qué se espera de cada lector.
                            </p>
                        </section>
                        <section class="policy-card" id="politica2">
                            <div class="policy-head">
                                <div class="policy-number">2</div>
                                <h2>Quién puede usar el servicio</h2>
                            </div>

                            <p>
                                Pueden usar el servicio los alumnos con matrícula activa y los trabajadores con clave registrada. Para
                                reservar libros necesitas crear tu cuenta con tu matrícula o tu clave de trabajador.
                            </p>
                        </section>
                        <section class="policy-card" id="politica3">
                            <div class="policy-head">
                                <div class="policy-number">3</div>
                                <h2>Cuenta y seguridad</h2>
                            </div>

                            <ul class="policy-list">
                                <li>
                                    <svg class="icon"><use href="#i-circle-check" /></svg>
                                    <span>Tu contraseña debe tener mínimo 8 caracteres y no debes compartirla.</span>
                                </li>
                                <li>
                                    <svg class="icon"><use href="#i-circle-check" /></svg>
                                    <span>Después de 5 intentos fallidos, el acceso se bloquea por 10 minutos.</span>
                                </li>
                                <li>
                                    <svg class="icon"><use href="#i-circle-check" /></svg>
                                    <span>Por seguridad, la sesión se cierra tras 15 minutos de inactividad.</span>
                                </li>
                            </ul>
                        </section>
                        <section class="policy-card" id="politica4">
                            <div class="policy-head">
                                <div class="policy-number">4</div>
                                <h2>Reservas</h2>
                            </div>

                            <ul class="policy-list">
                                <li>
                                    <svg class="icon"><use href="#i-circle-check" /></svg>
                                    <span>La reserva aparta el libro y te da un folio con tu comprobante; no es un préstamo.</span>
                                </li>
                                <li>
                                    <svg class="icon"><use href="#i-circle-check" /></svg>
                                    <span>Debes recoger el libro dentro del plazo indicado en tu comprobante, o la reserva expira.</span>
                                </li>
                                <li>
                                    <svg class="icon"><use href="#i-circle-check" /></svg>
                                    <span>Presenta tu credencial o Código Rápido en la biblioteca para recogerlo.</span>
                                </li>
                            </ul>
                        </section>
                        <section class="policy-card" id="politica5">
                            <div class="policy-head">
                                <div class="policy-number">5</div>
                                <h2>Préstamos y devoluciones</h2>
                            </div>

                            <ul class="policy-list">
                                <li>
                                    <svg class="icon"><use href="#i-circle-check" /></svg>
                                    <span>Puedes tener hasta 3 libros por préstamo al mismo tiempo.</span>
                                </li>
                                <li>
                                    <svg class="icon"><use href="#i-circle-check" /></svg>
                                    <span>El préstamo puede ser en la biblioteca o a domicilio, según lo indique el bibliotecario.</span>
                                </li>
                                <li>
                                    <svg class="icon"><use href="#i-circle-check" /></svg>
                                    <span>Devuelve el libro antes de la fecha límite y confirma el recibido desde la app.</span>
                                </li>
                            </ul>
                        </section>
                        <section class="policy-card" id="politica6">
                            <div class="policy-head">
                                <div class="policy-number">6</div>
                                <h2>Retrasos, multas y reposición</h2>
                            </div>

                            <ul class="policy-list">
                                <li>
                                    <svg class="icon"><use href="#i-circle-check" /></svg>
                                    <span>Por cada día de retraso en la devolución se genera un adeudo de $10.</span>
                                </li>
                                <li>
                                    <svg class="icon"><use href="#i-circle-check" /></svg>
                                    <span
                                        >Si un libro se pierde o se daña, el bibliotecario registra el motivo y te indica cómo
                                        reponerlo.</span
                                    >
                                </li>
                            </ul>
                        </section>
                        <section class="policy-card" id="politica7">
                            <div class="policy-head">
                                <div class="policy-number">7</div>
                                <h2>Privacidad y datos personales</h2>
                            </div>

                            <p>
                                Tus datos se usan únicamente para operar el servicio: cuenta, reservas, préstamos y avisos. Las contraseñas
                                se guardan cifradas y no se comparten con terceros.
                            </p>
                        </section>

                        <div class="update-box">
                            <div>
                                <strong>Última actualización</strong>
                                12/11/2026, 10:15
                            </div>
                            <a href="index.php" class="btn-back">
                                <svg class="icon"><use href="#i-arrow-left" /></svg> Volver al inicio
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Pie de pagina -->
        <?php include "includes/footer.php"; ?>

        <script src="./js/bootstrap.bundle.min.js"></script>
    </body>
</html>
