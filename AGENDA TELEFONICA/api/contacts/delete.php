<?php
// Bloque de dependencias para utilidades y conexion a BD.
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../../config/conexion.php';

// Bloque de restriccion de metodo para eliminar solo via DELETE.
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    responderJSON(405, [
        'ok' => false,
        'mensaje' => 'Metodo no permitido.'
    ]);
}

// Bloque de validacion de sesion y lectura del id enviado.
$usuarioId = validarSesionUsuario();
$datos = obtenerDatosJSON();
$id = isset($datos['id']) ? (int) $datos['id'] : 0;

// Bloque de validacion del id a eliminar.
if ($id <= 0) {
    responderJSON(422, [
        'ok' => false,
        'mensaje' => 'Debes indicar un id valido.'
    ]);
}

// Bloque de eliminacion restringida al propietario del contacto.
$sql = "DELETE FROM contactos WHERE id = ? AND usuario_id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    responderJSON(500, [
        'ok' => false,
        'mensaje' => 'Error al preparar eliminacion de contacto.'
    ]);
}

$stmt->bind_param('ii', $id, $usuarioId);
$stmt->execute();

if ($stmt->affected_rows === 0) {
    $stmt->close();
    $conn->close();
    responderJSON(404, [
        'ok' => false,
        'mensaje' => 'No existe el contacto o no pertenece al usuario.'
    ]);
}

$stmt->close();
$conn->close();

// Bloque de respuesta final exitosa.
responderJSON(200, [
    'ok' => true,
    'mensaje' => 'Contacto eliminado correctamente.'
]);
?>
