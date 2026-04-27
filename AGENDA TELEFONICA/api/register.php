<?php
// Bloque de dependencias para utilidades y conexion a base de datos.
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/../config/conexion.php';

// Bloque de restriccion de metodo HTTP para aceptar solo registro via POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJSON(405, [
        'ok' => false,
        'mensaje' => 'Metodo no permitido.'
    ]);
}

// Bloque de lectura y limpieza de datos recibidos desde el frontend.
$datos = obtenerDatosJSON();
$nombre = trim($datos['nombre'] ?? '');
$correo = trim($datos['correo'] ?? '');
$password = (string) ($datos['password'] ?? '');

// Bloque de validaciones basicas del formulario.
if ($nombre === '' || $correo === '' || $password === '') {
    responderJSON(422, [
        'ok' => false,
        'mensaje' => 'Todos los campos son obligatorios.'
    ]);
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    responderJSON(422, [
        'ok' => false,
        'mensaje' => 'El correo no tiene un formato valido.'
    ]);
}

if (strlen($nombre) < 3 || strlen($nombre) > 100) {
    responderJSON(422, [
        'ok' => false,
        'mensaje' => 'El nombre debe tener entre 3 y 100 caracteres.'
    ]);
}

if (!preg_match('/^[\p{L}\s\.\'-]+$/u', $nombre)) {
    responderJSON(422, [
        'ok' => false,
        'mensaje' => 'El nombre contiene caracteres no permitidos.'
    ]);
}

if (strlen($password) < 6) {
    responderJSON(422, [
        'ok' => false,
        'mensaje' => 'La contrasena debe tener al menos 6 caracteres.'
    ]);
}

// Bloque de verificacion para impedir correos duplicados.
$sqlExiste = "SELECT id FROM usuarios WHERE correo = ? LIMIT 1";
$stmtExiste = $conn->prepare($sqlExiste);

if (!$stmtExiste) {
    responderJSON(500, [
        'ok' => false,
        'mensaje' => 'Error al preparar consulta de validacion.'
    ]);
}

$stmtExiste->bind_param('s', $correo);
$stmtExiste->execute();
$stmtExiste->store_result();

if ($stmtExiste->num_rows > 0) {
    $stmtExiste->close();
    $conn->close();
    responderJSON(409, [
        'ok' => false,
        'mensaje' => 'Ya existe una cuenta con ese correo.'
    ]);
}

$stmtExiste->close();

// Bloque de insercion del nuevo usuario con contrasena cifrada.
$passwordHash = password_hash($password, PASSWORD_DEFAULT);
$sqlInsert = "INSERT INTO usuarios (nombre, correo, password_hash) VALUES (?, ?, ?)";
$stmtInsert = $conn->prepare($sqlInsert);

if (!$stmtInsert) {
    $conn->close();
    responderJSON(500, [
        'ok' => false,
        'mensaje' => 'Error al preparar insercion de usuario.'
    ]);
}

$stmtInsert->bind_param('sss', $nombre, $correo, $passwordHash);

if (!$stmtInsert->execute()) {
    $stmtInsert->close();
    $conn->close();
    responderJSON(500, [
        'ok' => false,
        'mensaje' => 'No fue posible registrar al usuario.'
    ]);
}

// Bloque de inicio automatico de sesion tras crear la cuenta.
iniciarSesionSegura();
session_regenerate_id(true);
$_SESSION['usuario_id'] = $stmtInsert->insert_id;
$_SESSION['usuario_nombre'] = $nombre;
$_SESSION['usuario_correo'] = $correo;

$stmtInsert->close();
$conn->close();

// Bloque de respuesta final exitosa.
responderJSON(201, [
    'ok' => true,
    'mensaje' => 'Cuenta creada correctamente.'
]);
?>
