<?php require "includes/sesion.php"; ?>
<!doctype html>
<html lang="es">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />

        <title>Nosotros - Biblioteca Escolar</title>

        <!-- Fuente Poppins (guia de estilos) -->
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />

        <link rel="stylesheet" href="./css/bootstrap.min.css" />
        <link rel="stylesheet" href="./css/nosotros.css" />
        <link rel="stylesheet" href="./css/footer.css" />
        <link rel="stylesheet" href="./css/header_lector.css" />
    </head>

    <body>
        <!-- Iconos (set Lucide, trazo lineal) -->
        <svg xmlns="http://www.w3.org/2000/svg" style="display: none">
            <symbol id="i-search" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8" />
                <path d="m21 21-4.3-4.3" />
            </symbol>
            <symbol id="i-school" viewBox="0 0 24 24">
                <path d="M14 22v-4a2 2 0 1 0-4 0v4" />
                <path d="m18 10 4 2v8a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-8l4-2" />
                <path d="M18 5v17" />
                <path d="m4 6 8-4 8 4" />
                <path d="M6 5v17" />
                <circle cx="12" cy="9" r="2" />
            </symbol>
            <symbol id="i-book-open" viewBox="0 0 24 24">
                <path d="M12 7v14" />
                <path
                    d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"
                />
            </symbol>
            <symbol id="i-bookmark" viewBox="0 0 24 24"><path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z" /></symbol>
            <symbol id="i-library" viewBox="0 0 24 24">
                <path d="m16 6 4 14" />
                <path d="M12 6v14" />
                <path d="M8 8v12" />
                <path d="M4 4v16" />
            </symbol>
            <symbol id="i-circle-check" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" />
                <path d="m9 12 2 2 4-4" />
            </symbol>
            <symbol id="i-circle-x" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" />
                <path d="m15 9-6 6" />
                <path d="m9 9 6 6" />
            </symbol>
            <symbol id="i-calendar" viewBox="0 0 24 24">
                <path d="M8 2v4" />
                <path d="M16 2v4" />
                <rect width="18" height="18" x="3" y="4" rx="2" />
                <path d="M3 10h18" />
            </symbol>
            <symbol id="i-clock" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" />
                <polyline points="12 6 12 12 16 14" />
            </symbol>
            <symbol id="i-map-pin" viewBox="0 0 24 24">
                <path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0" />
                <circle cx="12" cy="10" r="3" />
            </symbol>
            <symbol id="i-target" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" />
                <circle cx="12" cy="12" r="6" />
                <circle cx="12" cy="12" r="2" />
            </symbol>
            <symbol id="i-eye" viewBox="0 0 24 24">
                <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0" />
                <circle cx="12" cy="12" r="3" />
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
                            <li class="nav-item">
                                <a class="nav-link px-lg-3 active" aria-current="page" href="nosotros.php">Nosotros</a>
                            </li>
                            <li class="nav-item"><a class="nav-link px-lg-3" href="preguntas.php">Preguntas</a></li>
                            <li class="nav-item"><a class="nav-link px-lg-3" href="politicas.php">Políticas</a></li>
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

        <main>
            <!-- Encabezado de la pagina -->
            <section class="page-header">
                <div class="container">
                    <p class="breadcrumb-line mb-0"><a href="index.php">Inicio</a> › Nosotros</p>
                    <h1 class="page-title">Nosotros</h1>
                    <p class="page-subtitle">Conoce al CBTa No. 5 y a su biblioteca escolar.</p>
                </div>
            </section>

            <!-- El CBTa -->
            <section class="section-block">
                <div class="container">
                    <div class="row g-5 align-items-center">
                        <div class="col-lg-7">
                            <div class="d-flex align-items-center gap-4">
                                <img src="./img/logo-cbta5.png" alt="Escudo del CBTa No. 5" height="120" />
                                <div>
                                    <h2 class="section-title">Sobre el CBTa No. 5</h2>
                                    <p class="text-secondary mb-0">
                                        Centro de Bachillerato Tecnológico Agropecuario No. 5, en Huejutla, Hidalgo.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-5">
                            <div class="lema-box">
                                <p class="label">Nuestro lema</p>
                                <p>Respeto, Autoestima y Excelencia Académica.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- La biblioteca -->
            <section class="section-block section-gray">
                <div class="container">
                    <h2 class="section-title">La biblioteca escolar</h2>
                    <p class="text-secondary mb-4">
                        La biblioteca escolar del CBTa No. 5 pone a disposición de la comunidad su acervo para consulta y préstamo. En esta
                        página web puedes consultar el catálogo y administrar tu cuenta; las reservas se hacen desde la app móvil.
                    </p>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="info-card">
                                <div class="icon-circle">
                                    <svg class="icon"><use href="#i-target" /></svg>
                                </div>
                                <h3>Misión</h3>
                                <p>
                                    Ofrecer a la comunidad del CBTa No. 5 acervo y espacios que apoyen su formación académica y el gusto por
                                    la lectura.
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card">
                                <div class="icon-circle">
                                    <svg class="icon"><use href="#i-eye" /></svg>
                                </div>
                                <h3>Visión</h3>
                                <p>
                                    Ser una biblioteca moderna y accesible, que acerque el conocimiento mediante servicios digitales y un
                                    acervo actualizado.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Ubicacion y contacto -->
            <section class="section-block">
                <div class="container">
                    <h2 class="section-title">Dónde encontrarnos</h2>
                    <p class="section-subtitle">Visítanos o escríbenos.</p>

                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="info-card">
                                <div class="icon-circle">
                                    <svg class="icon"><use href="#i-map-pin" /></svg>
                                </div>
                                <h3>Ubicación</h3>
                                <p>CBTa No. 5, Huejutla de Reyes, Hgo.</p>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="info-card">
                                <div class="icon-circle">
                                    <svg class="icon"><use href="#i-clock" /></svg>
                                </div>
                                <h3>Horario</h3>
                                <p>Lunes a viernes, 8:00 a 15:00.<br />Sábado y domingo: cerrado.</p>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="info-card">
                                <div class="icon-circle">
                                    <svg class="icon"><use href="#i-mail" /></svg>
                                </div>
                                <h3>Contacto</h3>
                                <p>[correo de la biblioteca]<br />[teléfono de la biblioteca]</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <!-- Pie de pagina -->
        <?php include "includes/footer.php"; ?>

        <script src="./js/bootstrap.bundle.min.js"></script>
    </body>
</html>