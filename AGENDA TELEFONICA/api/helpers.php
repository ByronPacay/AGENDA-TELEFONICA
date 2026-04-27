<?php
// Bloque utilitario: inicia sesion solo si aun no esta activa.
function iniciarSesionSegura(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Bloque utilitario: devuelve respuestas JSON estandarizadas y finaliza el script.
function responderJSON(int $codigo, array $datos): void
{
    http_response_code($codigo);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

// Bloque utilitario: lee y decodifica el cuerpo JSON enviado desde JavaScript.
function obtenerDatosJSON(): array
{
    $entrada = file_get_contents('php://input');

    if ($entrada === false || $entrada === '') {
        return [];
    }

    $datos = json_decode($entrada, true);
    return is_array($datos) ? $datos : [];
}

// Bloque utilitario: valida que exista sesion iniciada y retorna el id del usuario autenticado.
function validarSesionUsuario(): int
{
    iniciarSesionSegura();

    if (!isset($_SESSION['usuario_id'])) {
        responderJSON(401, [
            'ok' => false,
            'mensaje' => 'Debes iniciar sesion para continuar.'
        ]);
    }

    return (int) $_SESSION['usuario_id'];
}
?>
