<?php
// Bloque de catalogo: define los paises permitidos y sus reglas de numeracion.
function obtenerPaisesPermitidosAgenda(): array
{
    return [
        'GT' => ['nombre' => 'Guatemala', 'codigo' => '+502', 'digitos_min' => 8, 'digitos_max' => 8, 'grupos' => [4, 4]],
        'SV' => ['nombre' => 'El Salvador', 'codigo' => '+503', 'digitos_min' => 8, 'digitos_max' => 8, 'grupos' => [4, 4]],
        'HN' => ['nombre' => 'Honduras', 'codigo' => '+504', 'digitos_min' => 8, 'digitos_max' => 8, 'grupos' => [4, 4]],
        'NI' => ['nombre' => 'Nicaragua', 'codigo' => '+505', 'digitos_min' => 8, 'digitos_max' => 8, 'grupos' => [4, 4]],
        'CR' => ['nombre' => 'Costa Rica', 'codigo' => '+506', 'digitos_min' => 8, 'digitos_max' => 8, 'grupos' => [4, 4]],
        'PA' => ['nombre' => 'Panama', 'codigo' => '+507', 'digitos_min' => 8, 'digitos_max' => 8, 'grupos' => [4, 4]],
        'MX' => ['nombre' => 'Mexico', 'codigo' => '+52', 'digitos_min' => 10, 'digitos_max' => 10, 'grupos' => [2, 4, 4]],
        'US' => ['nombre' => 'Estados Unidos', 'codigo' => '+1', 'digitos_min' => 10, 'digitos_max' => 10, 'grupos' => [3, 3, 4]],
        'CO' => ['nombre' => 'Colombia', 'codigo' => '+57', 'digitos_min' => 10, 'digitos_max' => 10, 'grupos' => [3, 3, 4]],
        'ES' => ['nombre' => 'Espana', 'codigo' => '+34', 'digitos_min' => 9, 'digitos_max' => 9, 'grupos' => [3, 3, 3]]
    ];
}

// Bloque utilitario: comprime espacios repetidos y limpia extremos.
function limpiarEspaciosInternos(string $texto): string
{
    $texto = trim($texto);
    $texto = preg_replace('/\s+/u', ' ', $texto);
    return $texto ?? '';
}

// Bloque utilitario: obtiene longitud de texto con soporte UTF-8 y fallback seguro.
function longitudTextoSeguro(string $texto): int
{
    if (function_exists('mb_strlen')) {
        return (int) mb_strlen($texto, 'UTF-8');
    }

    return strlen($texto);
}

// Bloque utilitario: extrae solo numeros para comparaciones y reglas de longitud.
function extraerSoloDigitos(string $valor): string
{
    $digitos = preg_replace('/\D+/', '', $valor);
    return $digitos ?? '';
}

// Bloque utilitario: formatea una cadena numerica segun grupos definidos por pais.
function formatearConGrupos(string $digitos, array $grupos): string
{
    $partes = [];
    $cursor = 0;

    foreach ($grupos as $tamano) {
        if ($cursor >= strlen($digitos)) {
            break;
        }

        $partes[] = substr($digitos, $cursor, $tamano);
        $cursor += $tamano;
    }

    if ($cursor < strlen($digitos)) {
        $partes[] = substr($digitos, $cursor);
    }

    return implode('-', $partes);
}

// Bloque de validacion: valida nombre con reglas reales de agenda.
function validarNombreAgenda(string $nombre): array
{
    $nombreLimpio = limpiarEspaciosInternos($nombre);

    if ($nombreLimpio === '') {
        return ['ok' => false, 'mensaje' => 'El nombre es obligatorio.'];
    }

    if (longitudTextoSeguro($nombreLimpio) > 50) {
        return ['ok' => false, 'mensaje' => 'El nombre no puede superar 50 caracteres.'];
    }

    if (!preg_match('/[A-Za-z\p{L}]/u', $nombreLimpio)) {
        return ['ok' => false, 'mensaje' => 'El nombre debe contener letras reales.'];
    }

    if (!preg_match('/^[A-Za-z\p{L}\s\.\'-]+$/u', $nombreLimpio)) {
        return ['ok' => false, 'mensaje' => 'El nombre contiene caracteres no permitidos.'];
    }

    return ['ok' => true, 'valor' => $nombreLimpio];
}

// Bloque de validacion: valida correo con formato completo y casos bloqueados.
function validarCorreoAgenda(string $correo): array
{
    $correoLimpio = strtolower(trim($correo));

    if ($correoLimpio === '') {
        return ['ok' => true, 'valor' => null];
    }

    if ($correoLimpio === 'opcional@correo.com') {
        return ['ok' => false, 'mensaje' => 'El correo "opcional@correo.com" no es un valor permitido.'];
    }

    $regex = '/^[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}$/i';
    if (!preg_match($regex, $correoLimpio)) {
        return ['ok' => false, 'mensaje' => 'El correo debe tener formato usuario@dominio.extension.'];
    }

    if (!filter_var($correoLimpio, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'mensaje' => 'El correo no es valido.'];
    }

    return ['ok' => true, 'valor' => $correoLimpio];
}

// Bloque de validacion: limpia direccion opcional y valida longitud maxima.
function validarDireccionAgenda(string $direccion): array
{
    $direccionLimpia = limpiarEspaciosInternos($direccion);

    if ($direccionLimpia === '') {
        return ['ok' => true, 'valor' => null];
    }

    if (longitudTextoSeguro($direccionLimpia) > 100) {
        return ['ok' => false, 'mensaje' => 'La direccion no puede superar 100 caracteres.'];
    }

    return ['ok' => true, 'valor' => $direccionLimpia];
}

// Bloque de validacion: limpia notas opcionales y valida longitud maxima.
function validarNotasAgenda(string $notas): array
{
    $notasLimpias = limpiarEspaciosInternos($notas);

    if ($notasLimpias === '') {
        return ['ok' => true, 'valor' => null];
    }

    if (longitudTextoSeguro($notasLimpias) > 255) {
        return ['ok' => false, 'mensaje' => 'Las observaciones no pueden superar 255 caracteres.'];
    }

    return ['ok' => true, 'valor' => $notasLimpias];
}

// Bloque de validacion: normaliza telefono segun el pais y genera formato consistente.
function validarYNormalizarTelefonoAgenda(string $paisIso, string $telefono): array
{
    $paises = obtenerPaisesPermitidosAgenda();
    $paisIso = strtoupper(trim($paisIso));

    if (!isset($paises[$paisIso])) {
        return ['ok' => false, 'mensaje' => 'Debes seleccionar un pais valido.'];
    }

    $reglaPais = $paises[$paisIso];
    $codigoPais = $reglaPais['codigo'];
    $telefonoLimpio = strtoupper(trim($telefono));

    if ($telefonoLimpio === '') {
        return ['ok' => false, 'mensaje' => 'El telefono es obligatorio.'];
    }

    if (!preg_match('/^[0-9+\-\s()]+$/', $telefonoLimpio)) {
        return ['ok' => false, 'mensaje' => 'El telefono solo permite numeros, guiones, espacios, parentesis y +.'];
    }

    $codigoSinMas = ltrim($codigoPais, '+');
    $digitosTotales = extraerSoloDigitos($telefonoLimpio);
    $nacional = '';

    // Bloque especial para Guatemala: acepta formato local 2300-5000 y lo convierte a +502-2300-5000.
    if ($paisIso === 'GT') {
        if (preg_match('/^\d{8}$/', $telefonoLimpio)) {
            return ['ok' => false, 'mensaje' => 'Para Guatemala usa 2300-5000 o +502-2300-5000.'];
        }

        if (preg_match('/^\d{4}-\d{4}$/', $telefonoLimpio)) {
            $nacional = str_replace('-', '', $telefonoLimpio);
        } else {
            if (!str_starts_with($telefonoLimpio, '+')) {
                return ['ok' => false, 'mensaje' => 'Para Guatemala usa +502-XXXX-XXXX o local XXXX-XXXX.'];
            }

            if (!str_starts_with($telefonoLimpio, $codigoPais)) {
                return ['ok' => false, 'mensaje' => 'El codigo de pais no coincide con Guatemala (+502).'];
            }

            if (!str_starts_with($digitosTotales, $codigoSinMas)) {
                return ['ok' => false, 'mensaje' => 'El telefono no contiene un codigo de pais valido.'];
            }

            $nacional = substr($digitosTotales, strlen($codigoSinMas));
        }
    } else {
        if (str_starts_with($telefonoLimpio, '+')) {
            if (!str_starts_with($telefonoLimpio, $codigoPais)) {
                return ['ok' => false, 'mensaje' => 'El codigo de pais no coincide con el pais seleccionado.'];
            }

            if (!str_starts_with($digitosTotales, $codigoSinMas)) {
                return ['ok' => false, 'mensaje' => 'El telefono no contiene un codigo de pais valido.'];
            }

            $nacional = substr($digitosTotales, strlen($codigoSinMas));
        } else {
            $nacional = $digitosTotales;
        }
    }

    $digitosNacionales = strlen($nacional);
    if ($digitosNacionales < $reglaPais['digitos_min'] || $digitosNacionales > $reglaPais['digitos_max']) {
        return ['ok' => false, 'mensaje' => 'La cantidad de digitos del telefono no coincide con el pais seleccionado.'];
    }

    $digitosE164 = strlen($codigoSinMas . $nacional);
    if ($digitosE164 < 9 || $digitosE164 > 15) {
        return ['ok' => false, 'mensaje' => 'El telefono debe cumplir el estandar internacional E.164 (9 a 15 digitos).'];
    }

    $telefonoFormateado = $codigoPais . '-' . formatearConGrupos($nacional, $reglaPais['grupos']);
    $telefonoE164 = '+' . $codigoSinMas . $nacional;

    return [
        'ok' => true,
        'pais_iso' => $paisIso,
        'pais_nombre' => $reglaPais['nombre'],
        'codigo_pais' => $codigoPais,
        'telefono' => $telefonoFormateado,
        'telefono_e164' => $telefonoE164
    ];
}

// Bloque utilitario: verifica si una columna ya existe para evitar errores en alteraciones.
function existeColumnaTabla(mysqli $conn, string $tabla, string $columna): bool
{
    $sql = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('ss', $tabla, $columna);
    $stmt->execute();
    $stmt->bind_result($conteo);
    $stmt->fetch();
    $stmt->close();

    return ((int) $conteo) > 0;
}

// Bloque utilitario: verifica existencia de indices para crearlos solo una vez.
function existeIndiceTabla(mysqli $conn, string $tabla, string $indice): bool
{
    $sql = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('ss', $tabla, $indice);
    $stmt->execute();
    $stmt->bind_result($conteo);
    $stmt->fetch();
    $stmt->close();

    return ((int) $conteo) > 0;
}

// Bloque de mantenimiento: asegura que la estructura de contactos tenga los campos nuevos.
function asegurarEstructuraContactos(mysqli $conn): void
{
    $sqlTabla = "CREATE TABLE IF NOT EXISTS contactos (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT UNSIGNED NOT NULL,
        nombre VARCHAR(50) NOT NULL,
        pais_iso CHAR(2) NOT NULL DEFAULT 'GT',
        pais_nombre VARCHAR(80) NOT NULL DEFAULT 'Guatemala',
        codigo_pais VARCHAR(6) NOT NULL DEFAULT '+502',
        telefono VARCHAR(30) NOT NULL,
        telefono_e164 VARCHAR(20) DEFAULT NULL,
        correo VARCHAR(120) DEFAULT NULL,
        direccion VARCHAR(100) DEFAULT NULL,
        favorito TINYINT(1) NOT NULL DEFAULT 0,
        notas VARCHAR(255) DEFAULT NULL,
        creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT fk_contactos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB";
    $conn->query($sqlTabla);

    $alteraciones = [
        'pais_iso' => "ALTER TABLE contactos ADD COLUMN pais_iso CHAR(2) NOT NULL DEFAULT 'GT' AFTER nombre",
        'pais_nombre' => "ALTER TABLE contactos ADD COLUMN pais_nombre VARCHAR(80) NOT NULL DEFAULT 'Guatemala' AFTER pais_iso",
        'codigo_pais' => "ALTER TABLE contactos ADD COLUMN codigo_pais VARCHAR(6) NOT NULL DEFAULT '+502' AFTER pais_nombre",
        'telefono_e164' => "ALTER TABLE contactos ADD COLUMN telefono_e164 VARCHAR(20) DEFAULT NULL AFTER telefono",
        'favorito' => "ALTER TABLE contactos ADD COLUMN favorito TINYINT(1) NOT NULL DEFAULT 0 AFTER direccion",
        'notas' => "ALTER TABLE contactos ADD COLUMN notas VARCHAR(255) DEFAULT NULL AFTER favorito"
    ];

    foreach ($alteraciones as $columna => $sqlAlter) {
        if (!existeColumnaTabla($conn, 'contactos', $columna)) {
            $conn->query($sqlAlter);
        }
    }

    if (!existeIndiceTabla($conn, 'contactos', 'idx_contactos_usuario_nombre')) {
        $conn->query("CREATE INDEX idx_contactos_usuario_nombre ON contactos (usuario_id, nombre)");
    }

    if (!existeIndiceTabla($conn, 'contactos', 'idx_contactos_usuario_telefono')) {
        $conn->query("CREATE INDEX idx_contactos_usuario_telefono ON contactos (usuario_id, telefono_e164)");
    }

    if (!existeIndiceTabla($conn, 'contactos', 'uk_contactos_usuario_telefono')) {
        $conn->query("CREATE UNIQUE INDEX uk_contactos_usuario_telefono ON contactos (usuario_id, telefono_e164)");
    }
}
?>
