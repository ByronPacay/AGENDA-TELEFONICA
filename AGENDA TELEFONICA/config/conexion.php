<?php
// Bloque de configuracion: datos de acceso al servidor MySQL.
$Servidor = "localhost";
$Usuario = "root";
$password = "";
$BaseDeDatos = "bd_agenda_telefonica";

// Bloque para crear la conexion con MySQL.
$conn = new mysqli($Servidor, $Usuario, $password, $BaseDeDatos);

// Bloque de validacion de conexion; si falla, responde JSON para que el frontend pueda mostrar el error real.
if ($conn->connect_error) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'ok' => false,
        'mensaje' => 'Error de conexion a la base de datos.',
        'detalle' => $conn->connect_error
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Bloque de codificacion para soportar acentos y caracteres especiales.
$conn->set_charset("utf8mb4");

// Nota: en APIs no se imprime "Conexion exitosa" ni se cierra aqui para no romper respuestas JSON.
// echo "Conexion exitosa";
// $conn->close();
?>
