<?php
// Bloque de control de acceso: solo los usuarios autenticados pueden entrar a la agenda.
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

// Bloque de variable segura para mostrar el nombre del usuario en pantalla.
$nombreUsuario = htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario', ENT_QUOTES, 'UTF-8');

// Bloque de versionado de assets para evitar cache del navegador.
$cssVersion = filemtime(__DIR__ . '/assets/css/styles.css');
$themeVersion = filemtime(__DIR__ . '/assets/js/theme.js');
$agendaVersion = filemtime(__DIR__ . '/assets/js/agenda.js');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Bloque de metadatos basicos -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda Telefonica | Panel</title>

    <!-- Bloque de tipografia moderna para la identidad visual -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Bloque de inicializacion temprana del tema para evitar parpadeo -->
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

    <!-- Bloque de Bootstrap para layout responsivo -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bloque de estilos personalizados encima de Bootstrap -->
    <link rel="stylesheet" href="assets/css/styles.css?v=<?php echo $cssVersion; ?>">
</head>
<body class="app-body">
    <!-- Bloque principal del dashboard con contenedor Bootstrap -->
    <main class="container py-4 py-lg-5">
        <!-- Bloque superior para cambio de tema -->
        <div class="d-flex justify-content-end mb-3">
            <button id="btn-theme-toggle" class="btn btn-theme theme-toggle" type="button" aria-label="Cambiar tema">
                <i class="bi bi-moon-stars-fill theme-icon" aria-hidden="true"></i>
                <span class="theme-text">Modo oscuro</span>
            </button>
        </div>

        <!-- Bloque superior con bienvenida -->
        <header class="card border-0 shadow-lg mb-4">
            <div class="card-body d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div>
                    <span class="badge text-bg-warning text-dark mb-2">Panel de usuario</span>
                    <h1 class="h3 mb-1">Hola, <?php echo $nombreUsuario; ?></h1>
                    <p class="text-secondary mb-0">Administra tus contactos personales con operaciones CRUD.</p>
                </div>
                <button id="btn-logout" class="btn btn-outline-secondary fw-semibold" type="button">Cerrar sesion</button>
            </div>
        </header>

        <!-- Bloque de formulario CRUD -->
        <section class="card border-0 shadow-lg mb-4">
            <div class="card-body">
                <h2 class="h4 mb-3">Formulario de contacto</h2>
                <form id="form-contacto" class="row g-3" novalidate>
                    <input type="hidden" id="contacto-id">

                    <div class="col-md-6">
                        <label for="contacto-nombre" class="form-label fw-semibold">Nombre</label>
                        <input id="contacto-nombre" type="text" class="form-control" required maxlength="50" placeholder="Ejemplo: Carlos Andrade">
                    </div>

                    <div class="col-md-4">
                        <label for="contacto-pais" class="form-label fw-semibold">Pais</label>
                        <select id="contacto-pais" class="form-select" required>
                            <option value="GT" data-code="+502" selected>Guatemala (+502)</option>
                            <option value="SV" data-code="+503">El Salvador (+503)</option>
                            <option value="HN" data-code="+504">Honduras (+504)</option>
                            <option value="NI" data-code="+505">Nicaragua (+505)</option>
                            <option value="CR" data-code="+506">Costa Rica (+506)</option>
                            <option value="PA" data-code="+507">Panama (+507)</option>
                            <option value="MX" data-code="+52">Mexico (+52)</option>
                            <option value="US" data-code="+1">Estados Unidos (+1)</option>
                            <option value="CO" data-code="+57">Colombia (+57)</option>
                            <option value="ES" data-code="+34">Espana (+34)</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="contacto-codigo" class="form-label fw-semibold">Codigo</label>
                        <input id="contacto-codigo" type="text" class="form-control" value="+502" readonly>
                    </div>

                    <div class="col-md-6">
                        <label for="contacto-telefono" class="form-label fw-semibold">Telefono</label>
                        <input id="contacto-telefono" type="text" class="form-control" required maxlength="25" placeholder="Ejemplo: +502-2300-5000">
                        <div class="form-text">Guatemala: +502-XXXX-XXXX o local XXXX-XXXX.</div>
                    </div>

                    <div class="col-md-6">
                        <label for="contacto-correo" class="form-label fw-semibold">Correo</label>
                        <input id="contacto-correo" type="email" class="form-control" maxlength="120" placeholder="usuario@empresa.com">
                    </div>

                    <div class="col-md-6">
                        <label for="contacto-direccion" class="form-label fw-semibold">Direccion</label>
                        <input id="contacto-direccion" type="text" class="form-control" maxlength="100" placeholder="Direccion opcional">
                    </div>

                    <div class="col-md-6">
                        <label for="contacto-notas" class="form-label fw-semibold">Observaciones</label>
                        <textarea id="contacto-notas" class="form-control" rows="2" maxlength="255" placeholder="Notas opcionales (trabajo, personal, horario, etc.)"></textarea>
                    </div>

                    <div class="col-md-6 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="contacto-favorito">
                            <label class="form-check-label fw-semibold" for="contacto-favorito">Marcar como favorito</label>
                        </div>
                    </div>

                    <div class="col-12 d-flex gap-2 flex-wrap">
                        <button class="btn btn-primary" type="submit" id="btn-guardar">Guardar contacto</button>
                        <button class="btn btn-outline-secondary hidden" type="button" id="btn-cancelar-edicion">Cancelar edicion</button>
                    </div>
                </form>
            </div>
        </section>

        <!-- Bloque de tabla responsiva -->
        <section class="card border-0 shadow-lg">
            <div class="card-body">
                <div class="d-flex flex-column flex-lg-row gap-2 justify-content-between align-items-lg-center mb-3">
                    <h2 class="h4 mb-0">Listado de contactos</h2>
                    <div class="d-flex gap-2 flex-wrap">
                        <input id="filtro-contactos" type="text" class="form-control" placeholder="Buscar por nombre o telefono" style="min-width: 260px;">
                        <button id="btn-exportar-csv" type="button" class="btn btn-outline-primary">
                            <i class="bi bi-download me-1"></i>Exportar CSV
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Pais</th>
                                <th>Telefono</th>
                                <th>Correo</th>
                                <th>Direccion</th>
                                <th class="text-nowrap">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-contactos">
                            <tr>
                                <td colspan="6" class="text-center text-secondary py-4">Cargando contactos...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <!-- Bloque de libreria SweetAlert2 para alertas y confirmaciones -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Bloque de JavaScript Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Bloque de logica global de tema (claro/oscuro) -->
    <script src="assets/js/theme.js?v=<?php echo $themeVersion; ?>"></script>

    <!-- Bloque de logica JavaScript para el CRUD de contactos -->
    <script src="assets/js/agenda.js?v=<?php echo $agendaVersion; ?>"></script>
</body>
</html>
