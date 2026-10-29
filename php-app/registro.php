<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/db.php';

function responderError(int $codigo, string $mensaje): void
{
    http_response_code($codigo);
    header('Content-Type: text/html; charset=UTF-8');
    $seguro = htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8');
    echo "<!doctype html><html lang=\"es\"><head><meta charset=\"utf-8\"><title>Error {$codigo}</title>";
    echo '<link rel="stylesheet" href="styles.css"></head><body><main class="container">';
    echo "<h1>Error {$codigo}</h1><p class=\"error\">{$seguro}</p>";
    echo '<p><a href="index.php">Volver al formulario</a></p></main></body></html>';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderError(405, 'Método no permitido. Use POST para registrar un contacto.');
}

if (!isset($_SESSION['contactos']) || !is_array($_SESSION['contactos'])) {
    $_SESSION['contactos'] = [];
}

$nombre = trim((string) filter_input(INPUT_POST, 'nombre', FILTER_UNSAFE_RAW));
$email = trim((string) filter_input(INPUT_POST, 'email', FILTER_UNSAFE_RAW));
$telefono = trim((string) filter_input(INPUT_POST, 'telefono', FILTER_UNSAFE_RAW));
$mensaje = trim((string) filter_input(INPUT_POST, 'mensaje', FILTER_UNSAFE_RAW));
$errores = [];

if ($nombre === '' || mb_strlen($nombre) < 2 || mb_strlen($nombre) > 80) {
    $errores[] = 'El nombre debe tener entre 2 y 80 caracteres.';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 120) {
    $errores[] = 'El correo electrónico no es válido.';
}
if (!preg_match('/^[0-9+()\-\s]{7,20}$/', $telefono)) {
    $errores[] = 'El teléfono solo puede contener números, espacios y + ( ) - (7 a 20 caracteres).';
}
if ($mensaje === '' || mb_strlen($mensaje) > 500) {
    $errores[] = 'El mensaje es obligatorio y no debe superar 500 caracteres.';
}

if ($errores !== []) {
    http_response_code(422);
    header('Content-Type: text/html; charset=UTF-8');
    ?>
    <!doctype html><html lang="es"><head><meta charset="utf-8"><title>Datos inválidos</title>
    <link rel="stylesheet" href="styles.css"></head><body><main class="container">
    <h1>Datos inválidos</h1><ul class="error">
    <?php foreach ($errores as $error): ?><li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?>
    </ul><p><a href="index.php">Volver al formulario</a></p></main></body></html>
    <?php
    exit;
}

try {
    $conexion = obtenerConexion();
    $consulta = $conexion->prepare(
        'INSERT INTO contactos (nombre, email, telefono, mensaje) VALUES (?, ?, ?, ?)'
    );
    $consulta->bind_param('ssss', $nombre, $email, $telefono, $mensaje);
    $consulta->execute();
    $id = $conexion->insert_id;
    $consulta->close();
    $conexion->close();
} catch (mysqli_sql_exception $ex) {
    responderError(500, 'No fue posible guardar el contacto en la base de datos.');
}

$_SESSION['contactos'][] = [
    'id' => $id,
    'nombre' => $nombre,
    'email' => $email,
    'telefono' => $telefono,
    'mensaje' => $mensaje,
    'registrado_en' => date('c'),
];
$_SESSION['mensaje'] = 'Contacto registrado correctamente.';
header('Location: index.php', true, 303);
exit;
