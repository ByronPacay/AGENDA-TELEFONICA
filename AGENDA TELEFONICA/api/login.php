<?php
// Bloque de dependencias para utilidades y conexion a base de datos.
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/../config/conexion.php';

// Bloque de restriccion de metodo HTTP para aceptar solo login via POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJSON(405, [
        'ok' => false,
        'mensaje' => 'Metodo no permitido.'
    ]);
}

// Bloque de lectura y limpieza de datos enviados desde el formulario de login.
$datos = obtenerDatosJSON();
$correo = trim($datos['correo'] ?? '');
$password = (string) ($datos['password'] ?? '');

// Bloque de validaciones minimas de entrada.
if ($correo === '' || $password === '') {
    responderJSON(422, [
        'ok' => false,
        'mensaje' => 'Correo y contrasena son obligatorios.'
    ]);
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    responderJSON(422, [
        'ok' => false,
        'mensaje' => 'El correo no tiene un formato valido.'
    ]);
}

// Bloque de consulta para buscar el usuario por correo.
$sql = "SELECT id, nombre, correo, password_hash FROM usuarios WHERE correo = ? LIMIT 1";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    responderJSON(500, [
        'ok' => false,
        'mensaje' => 'Error al preparar consulta de acceso.'
    ]);
}

$stmt->bind_param('s', $correo);
$stmt->execute();
$stmt->store_result();

// Bloque de validacion: si no existe el correo, se devuelve error de credenciales.
if ($stmt->num_rows === 0) {
    $stmt->close();
    $conn->close();
    responderJSON(401, [
        'ok' => false,
        'mensaje' => 'Credenciales invalidas.'
    ]);
}

// Bloque de lectura de datos de usuario para verificar contrasena.
$stmt->bind_result($id, $nombre, $correoDB, $passwordHash);
$stmt->fetch();

if (!password_verify($password, $passwordHash)) {
    $stmt->close();
    $conn->close();
    responderJSON(401, [
        'ok' => false,
        'mensaje' => 'Credenciales invalidas.'
    ]);
}

// Bloque de creacion de sesion despues de autenticar correctamente.
iniciarSesionSegura();
session_regenerate_id(true);
$_SESSION['usuario_id'] = (int) $id;
$_SESSION['usuario_nombre'] = $nombre;
$_SESSION['usuario_correo'] = $correoDB;

$stmt->close();
$conn->close();

// Bloque de respuesta final exitosa.
responderJSON(200, [
    'ok' => true,
    'mensaje' => 'Inicio de sesion exitoso.'
]);
?>
