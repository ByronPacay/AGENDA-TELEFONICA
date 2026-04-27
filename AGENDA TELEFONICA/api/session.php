<?php
// Bloque de dependencias para utilidades de respuesta.
require_once __DIR__ . '/helpers.php';

// Bloque que inicia sesion para consultar si el usuario ya esta autenticado.
iniciarSesionSegura();

// Bloque de respuesta: informa estado de sesion para redirecciones del frontend.
responderJSON(200, [
    'ok' => true,
    'autenticado' => isset($_SESSION['usuario_id']),
    'usuario' => [
        'id' => isset($_SESSION['usuario_id']) ? (int) $_SESSION['usuario_id'] : null,
        'nombre' => $_SESSION['usuario_nombre'] ?? null,
        'correo' => $_SESSION['usuario_correo'] ?? null
    ]
]);
?>
