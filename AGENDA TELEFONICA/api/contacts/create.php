<?php
// Bloque de dependencias para utilidades, conexion y reglas de contactos.
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/contact_rules.php';

// Bloque de restriccion de metodo para crear contactos solo via POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJSON(405, [
        'ok' => false,
        'mensaje' => 'Metodo no permitido.'
    ]);
}

// Bloque de validacion de sesion y sincronizacion de estructura de tabla.
$usuarioId = validarSesionUsuario();
asegurarEstructuraContactos($conn);

// Bloque de lectura de datos del frontend.
$datos = obtenerDatosJSON();
$nombreRaw = (string) ($datos['nombre'] ?? '');
$paisIsoRaw = (string) ($datos['pais_iso'] ?? '');
$telefonoRaw = (string) ($datos['telefono'] ?? '');
$correoRaw = (string) ($datos['correo'] ?? '');
$direccionRaw = (string) ($datos['direccion'] ?? '');
$favorito = !empty($datos['favorito']) ? 1 : 0;
$notasRaw = (string) ($datos['notas'] ?? '');

// Bloque de validacion por campo con mensajes especificos.
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

// Bloque anti-duplicados: no permite guardar el mismo numero para el mismo usuario.
$sqlExiste = "SELECT id FROM contactos WHERE usuario_id = ? AND telefono_e164 = ? LIMIT 1";
$stmtExiste = $conn->prepare($sqlExiste);

if (!$stmtExiste) {
    responderJSON(500, ['ok' => false, 'mensaje' => 'Error al validar telefono duplicado.']);
}

$stmtExiste->bind_param('is', $usuarioId, $telefonoE164);
$stmtExiste->execute();
$stmtExiste->store_result();

if ($stmtExiste->num_rows > 0) {
    $stmtExiste->close();
    $conn->close();
    responderJSON(409, [
        'ok' => false,
        'mensaje' => 'Ese telefono ya existe en tu agenda. No se permiten duplicados.'
    ]);
}

$stmtExiste->close();

// Bloque de insercion del nuevo contacto con todos los campos del formulario.
$sql = "INSERT INTO contactos (usuario_id, nombre, pais_iso, pais_nombre, codigo_pais, telefono, telefono_e164, correo, direccion, favorito, notas)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    $conn->close();
    responderJSON(500, ['ok' => false, 'mensaje' => 'Error al preparar insercion de contacto.']);
}

$stmt->bind_param(
    'issssssssis',
    $usuarioId,
    $nombre,
    $paisIso,
    $paisNombre,
    $codigoPais,
    $telefono,
    $telefonoE164,
    $correo,
    $direccion,
    $favorito,
    $notas
);

if (!$stmt->execute()) {
    $stmt->close();
    $conn->close();
    responderJSON(500, ['ok' => false, 'mensaje' => 'No fue posible crear el contacto.']);
}

$stmt->close();
$conn->close();

// Bloque de respuesta final exitosa.
responderJSON(201, [
    'ok' => true,
    'mensaje' => 'Contacto creado correctamente.',
    'telefono' => $telefono
]);
?>
