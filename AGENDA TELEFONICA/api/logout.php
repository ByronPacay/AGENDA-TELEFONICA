<?php
// Bloque de dependencias para utilidades de respuesta y manejo de sesion.
require_once __DIR__ . '/helpers.php';

// Bloque de restriccion de metodo para cerrar sesion solo por POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJSON(405, [
        'ok' => false,
        'mensaje' => 'Metodo no permitido.'
    ]);
}

// Bloque de limpieza de sesion del usuario actual.
iniciarSesionSegura();
$_SESSION = [];
session_destroy();

// Bloque de respuesta final para notificar cierre de sesion.
responderJSON(200, [
    'ok' => true,
    'mensaje' => 'Sesion cerrada correctamente.'
]);
?>
