<?php
// Bloque de dependencias para sesion, conexion y reglas de estructura.
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/contact_rules.php';

// Bloque de validacion de metodo para exportar solo por GET.
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    responderJSON(405, [
        'ok' => false,
        'mensaje' => 'Metodo no permitido.'
    ]);
}

// Bloque de validacion de sesion y sincronizacion de tabla.
$usuarioId = validarSesionUsuario();
asegurarEstructuraContactos($conn);

// Bloque de consulta de contactos para exportacion ordenada.
$sql = "SELECT nombre, pais_nombre, telefono_e164, correo, direccion, favorito, notas
        FROM contactos
        WHERE usuario_id = ?
        ORDER BY favorito DESC, nombre ASC, id DESC";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    responderJSON(500, [
        'ok' => false,
        'mensaje' => 'No fue posible preparar la exportacion CSV.'
    ]);
}

$stmt->bind_param('i', $usuarioId);
$stmt->execute();
$resultado = $stmt->get_result();

// Bloque de encabezados HTTP para descargar archivo CSV.
$nombreArchivo = 'contactos_' . date('Ymd_His') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');

// Bloque de escritura de CSV.
$salida = fopen('php://output', 'w');
fputcsv($salida, ['NOMBRE', 'PAIS', 'TELEFONO_E164', 'CORREO', 'DIRECCION', 'FAVORITO', 'OBSERVACIONES']);

while ($fila = $resultado->fetch_assoc()) {
    fputcsv($salida, [
        $fila['nombre'],
        $fila['pais_nombre'],
        $fila['telefono_e164'],
        $fila['correo'] ?? '',
        $fila['direccion'] ?? '',
        ((int) ($fila['favorito'] ?? 0) === 1) ? 'SI' : 'NO',
        $fila['notas'] ?? ''
    ]);
}

fclose($salida);
$stmt->close();
$conn->close();
exit;
?>
