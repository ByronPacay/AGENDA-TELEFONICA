<?php
// Bloque de dependencias para utilidades, conexion y reglas de contactos.
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/contact_rules.php';

// Bloque de restriccion de metodo para actualizacion solo via PUT.
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    responderJSON(405, [
        'ok' => false,
        'mensaje' => 'Metodo no permitido.'
    ]);
}

// Bloque de validacion de sesion y estructura de tabla.
$usuarioId = validarSesionUsuario();
asegurarEstructuraContactos($conn);

// Bloque de lectura de datos recibidos.
$datos = obtenerDatosJSON();
$id = isset($datos['id']) ? (int) $datos['id'] : 0;
$nombreRaw = (string) ($datos['nombre'] ?? '');
$paisIsoRaw = (string) ($datos['pais_iso'] ?? '');
$telefonoRaw = (string) ($datos['telefono'] ?? '');
$correoRaw = (string) ($datos['correo'] ?? '');
$direccionRaw = (string) ($datos['direccion'] ?? '');
$favorito = !empty($datos['favorito']) ? 1 : 0;
$notasRaw = (string) ($datos['notas'] ?? '');

// Bloque de validaciones iniciales.
if ($id <= 0) {
    responderJSON(422, ['ok' => false, 'mensaje' => 'El id del contacto es obligatorio.']);
}

$validacionNombre = validarNombreAgenda($nombreRaw);
if (!$validacionNombre['ok']) {
    responderJSON(422, ['ok' => false, 'mensaje' => $validacionNombre['mensaje']]);
}

$validacionTelefono = validarYNormalizarTelefonoAgenda($paisIsoRaw, $telefonoRaw);
if (!$validacionTelefono['ok']) {
    responderJSON(422, ['ok' => false, 'mensaje' => $validacionTelefono['mensaje']]);
}

$validacionCorreo = validarCorreoAgenda($correoRaw);
if (!$validacionCorreo['ok']) {
    responderJSON(422, ['ok' => false, 'mensaje' => $validacionCorreo['mensaje']]);
}

$validacionDireccion = validarDireccionAgenda($direccionRaw);
if (!$validacionDireccion['ok']) {
    responderJSON(422, ['ok' => false, 'mensaje' => $validacionDireccion['mensaje']]);
}

$validacionNotas = validarNotasAgenda($notasRaw);
if (!$validacionNotas['ok']) {
    responderJSON(422, ['ok' => false, 'mensaje' => $validacionNotas['mensaje']]);
}

$nombre = $validacionNombre['valor'];
$paisIso = $validacionTelefono['pais_iso'];
$paisNombre = $validacionTelefono['pais_nombre'];
$codigoPais = $validacionTelefono['codigo_pais'];
$telefono = $validacionTelefono['telefono'];
$telefonoE164 = $validacionTelefono['telefono_e164'];
$correo = $validacionCorreo['valor'];
$direccion = $validacionDireccion['valor'];
$notas = $validacionNotas['valor'];

// Bloque anti-duplicados: evita repetir telefono en otro contacto del mismo usuario.
$sqlDuplicado = "SELECT id FROM contactos WHERE usuario_id = ? AND telefono_e164 = ? AND id <> ? LIMIT 1";
$stmtDuplicado = $conn->prepare($sqlDuplicado);

if (!$stmtDuplicado) {
    responderJSON(500, ['ok' => false, 'mensaje' => 'Error al validar telefono duplicado.']);
}

$stmtDuplicado->bind_param('isi', $usuarioId, $telefonoE164, $id);
$stmtDuplicado->execute();
$stmtDuplicado->store_result();

if ($stmtDuplicado->num_rows > 0) {
    $stmtDuplicado->close();
    $conn->close();
    responderJSON(409, [
        'ok' => false,
        'mensaje' => 'Ese telefono ya existe en tu agenda. No se permiten duplicados.'
    ]);
}

$stmtDuplicado->close();

// Bloque de actualizacion completa del contacto.
$sql = "UPDATE contactos
        SET nombre = ?, pais_iso = ?, pais_nombre = ?, codigo_pais = ?, telefono = ?, telefono_e164 = ?, correo = ?, direccion = ?, favorito = ?, notas = ?, actualizado_en = NOW()
        WHERE id = ? AND usuario_id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    $conn->close();
    responderJSON(500, ['ok' => false, 'mensaje' => 'Error al preparar actualizacion de contacto.']);
}

$stmt->bind_param(
    'ssssssssisii',
    $nombre,
    $paisIso,
    $paisNombre,
    $codigoPais,
    $telefono,
    $telefonoE164,
    $correo,
    $direccion,
    $favorito,
    $notas,
    $id,
    $usuarioId
);
$stmt->execute();

if ($stmt->affected_rows < 0) {
    $stmt->close();
    $conn->close();
    responderJSON(500, ['ok' => false, 'mensaje' => 'No fue posible actualizar el contacto.']);
}

$stmt->close();
$conn->close();

// Bloque de respuesta final.
responderJSON(200, [
    'ok' => true,
    'mensaje' => 'Contacto actualizado correctamente.',
    'telefono' => $telefono
]);
?>
