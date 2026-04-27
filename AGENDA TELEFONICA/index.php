<?php
// Bloque de control de sesion: si el usuario ya inicio sesion, lo enviamos a la agenda.
session_start();
if (isset($_SESSION['usuario_id'])) {
    header('Location: agenda.php');
    exit;
}

// Bloque de versionado de assets para evitar cache al actualizar estilos y scripts.
$cssVersion = filemtime(__DIR__ . '/assets/css/styles.css');
$themeVersion = filemtime(__DIR__ . '/assets/js/theme.js');
$authVersion = filemtime(__DIR__ . '/assets/js/auth.js');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Bloque de metadatos basicos del documento -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda Telefonica | Acceso</title>

    <!-- Bloque de tipografia moderna para mejorar presencia visual -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Bloque de inicializacion temprana del tema para evitar parpadeo al cargar -->
    <script>
        (function () {
            var temaGuardado = null;
            try {
                temaGuardado = localStorage.getItem("app-theme");
            } catch (e) {
                temaGuardado = null;
            }
            var temaSistemaOscuro = window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches;
            var temaFinal = temaGuardado || (temaSistemaOscuro ? "dark" : "light");
            document.documentElement.setAttribute("data-bs-theme", temaFinal);
            document.documentElement.setAttribute("data-app-theme", temaFinal);
        })();
    </script>

    <!-- Bloque de Bootstrap para diseno responsivo y componentes visuales -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bloque de estilos propios para personalizar Bootstrap -->
    <link rel="stylesheet" href="assets/css/styles.css?v=<?php echo $cssVersion; ?>">
</head>
<body class="app-body">
    <!-- Bloque contenedor principal usando grid de Bootstrap -->
    <main class="container py-4 py-lg-5">
        <!-- Bloque superior para acciones globales como cambiar tema -->
        <div class="d-flex justify-content-end mb-3">
            <button id="btn-theme-toggle" class="btn btn-theme theme-toggle" type="button" aria-label="Cambiar tema">
                <i class="bi bi-moon-stars-fill theme-icon" aria-hidden="true"></i>
                <span class="theme-text">Modo oscuro</span>
            </button>
        </div>

        <div class="row g-4 align-items-stretch">
            <!-- Bloque informativo lateral -->
            <section class="col-lg-6">
                <div class="card border-0 shadow-lg h-100 hero-card">
                    <div class="card-body p-4 p-lg-5 d-flex flex-column justify-content-center">
                        <span class="badge text-bg-warning text-dark mb-3">Proyecto Web</span>
                        <h1 class="display-6 fw-bold mb-3">Agenda Telefonica</h1>
                        <p class="text-secondary mb-0">
                            Gestiona tus contactos por usuario con autenticacion,
                            base de datos MySQL y operaciones CRUD seguras.
                        </p>
                    </div>
                </div>
            </section>

            <!-- Bloque de autenticacion con pestanas -->
            <section class="col-lg-6">
                <div class="card border-0 shadow-lg h-100 auth-card">
                    <div class="card-body p-4">
                        <div class="d-grid gap-2 d-sm-flex mb-4">
                            <button id="btn-tab-login" class="btn btn-primary flex-fill active-tab" type="button">Iniciar sesion</button>
                            <button id="btn-tab-register" class="btn btn-outline-primary flex-fill" type="button">Crear cuenta</button>
                        </div>

                        <!-- Bloque del formulario de login -->
                        <form id="form-login" class="row g-3" novalidate>
                            <h2 class="h4 mb-1">Bienvenido</h2>

                            <div class="col-12">
                                <label for="login-correo" class="form-label fw-semibold">Correo</label>
                                <input id="login-correo" name="correo" type="email" class="form-control" required placeholder="correo@ejemplo.com">
                            </div>

                            <div class="col-12">
                                <label for="login-password" class="form-label fw-semibold">Contrasena</label>
                                <input id="login-password" name="password" type="password" class="form-control" required minlength="6" placeholder="Minimo 6 caracteres">
                            </div>

                            <div class="col-12 d-grid">
                                <button class="btn btn-primary" type="submit">Entrar</button>
                            </div>
                        </form>

                        <!-- Bloque del formulario de registro -->
                        <form id="form-register" class="row g-3 hidden" novalidate>
                            <h2 class="h4 mb-1">Crear nueva cuenta</h2>

                            <div class="col-12">
                                <label for="reg-nombre" class="form-label fw-semibold">Nombre completo</label>
                                <input id="reg-nombre" name="nombre" type="text" class="form-control" required minlength="3" maxlength="100" placeholder="Tu nombre">
                            </div>

                            <div class="col-12">
                                <label for="reg-correo" class="form-label fw-semibold">Correo</label>
                                <input id="reg-correo" name="correo" type="email" class="form-control" required placeholder="correo@ejemplo.com">
                            </div>

                            <div class="col-md-6">
                                <label for="reg-password" class="form-label fw-semibold">Contrasena</label>
                                <input id="reg-password" name="password" type="password" class="form-control" required minlength="6" placeholder="Minimo 6 caracteres">
                            </div>

                            <div class="col-md-6">
                                <label for="reg-confirm" class="form-label fw-semibold">Confirmar contrasena</label>
                                <input id="reg-confirm" name="confirm" type="password" class="form-control" required minlength="6" placeholder="Repite la contrasena">
                            </div>

                            <div class="col-12 d-grid">
                                <button class="btn btn-primary" type="submit">Registrar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Bloque de libreria SweetAlert2 para mostrar alertas elegantes -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Bloque de JavaScript Bootstrap para componentes interactivos -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Bloque de logica global de tema (claro/oscuro) -->
    <script src="assets/js/theme.js?v=<?php echo $themeVersion; ?>"></script>

    <!-- Bloque de logica JavaScript para autenticacion -->
    <script src="assets/js/auth.js?v=<?php echo $authVersion; ?>"></script>
</body>
</html>
