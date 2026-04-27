<?php
// Bloque de dependencias para utilidades, conexion y reglas de contactos.
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/contact_rules.php';

// Bloque de restriccion de metodo para listar contactos solo via GET.
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    responderJSON(405, [
        'ok' => false,
        'mensaje' => 'Metodo no permitido.'
    ]);
}

// Bloque de validacion de sesion y estructura de tabla.
$usuarioId = validarSesionUsuario();
asegurarEstructuraContactos($conn);

// Bloque de lectura de filtro de busqueda por nombre o telefono.
$busqueda = limpiarEspaciosInternos((string) ($_GET['q'] ?? ''));

if ($busqueda !== '') {
    $termino = '%' . $busqueda . '%';
    $sql = "SELECT id, nombre, pais_iso, pais_nombre, codigo_pais, telefono, telefono_e164, correo, direccion, favorito, notas
            FROM contactos
            WHERE usuario_id = ?
              AND (nombre LIKE ? OR telefono LIKE ? OR telefono_e164 LIKE ?)
            ORDER BY favorito DESC, nombre ASC, id DESC";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        responderJSON(500, ['ok' => false, 'mensaje' => 'Error al preparar consulta de contactos.']);
    }

    $stmt->bind_param('isss', $usuarioId, $termino, $termino, $termino);
} else {
    $sql = "SELECT id, nombre, pais_iso, pais_nombre, codigo_pais, telefono, telefono_e164, correo, direccion, favorito, notas
            FROM contactos
            WHERE usuario_id = ?
            ORDER BY favorito DESC, nombre ASC, id DESC";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        responderJSON(500, ['ok' => false, 'mensaje' => 'Error al preparar consulta de contactos.']);
    }

    $stmt->bind_param('i', $usuarioId);
}

$stmt->execute();
$stmt->bind_result($id, $nombre, $paisIso, $paisNombre, $codigoPais, $telefono, $telefonoE164, $correo, $direccion, $favorito, $notas);

$contactos = [];

// Bloque de armado de respuesta con todos los campos del contacto.
while ($stmt->fetch()) {
    $contactos[] = [
        'id' => (int) $id,
        'nombre' => $nombre,
        'pais_iso' => $paisIso ?: 'GT',
        'pais_nombre' => $paisNombre ?: 'Guatemala',
        'codigo_pais' => $codigoPais ?: '+502',
        'telefono' => $telefono,
        'telefono_e164' => $telefonoE164,
        'correo' => $correo,
        'direccion' => $direccion,
        'favorito' => ((int) $favorito) === 1,
        'notas' => $notas
    ];
}

$stmt->close();
$conn->close();

// Bloque de respuesta final.
responderJSON(200, [
    'ok' => true,
    'contactos' => $contactos
]);
?>
