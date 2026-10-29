<?php
session_start();

if (!isset($_SESSION['contactos'])) {
    $_SESSION['contactos'] = [];
}

$mensaje = $_SESSION['mensaje'] ?? '';
unset($_SESSION['mensaje']);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registro de contacto - PHP</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<main class="container">
    <h1>Registro de contacto</h1>
    <p class="badge">PHP / Apache :8080</p>
    <?php if ($mensaje !== ''): ?>
        <p class="success"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <form action="registro.php" method="post">
        <label for="nombre">Nombre completo</label>
        <input id="nombre" name="nombre" type="text" maxlength="80" required>

        <label for="email">Correo electrónico</label>
        <input id="email" name="email" type="email" maxlength="120" required>

        <label for="telefono">Teléfono</label>
        <input id="telefono" name="telefono" type="text" maxlength="20" required>

        <label for="mensaje">Mensaje</label>
        <textarea id="mensaje" name="mensaje" maxlength="500" required></textarea>
        <button type="submit">Guardar contacto</button>
    </form>

    <h2>Contactos de esta sesión</h2>
    <?php if (count($_SESSION['contactos']) === 0): ?>
        <p>No hay contactos registrados.</p>
    <?php else: ?>
        <table>
            <thead><tr><th>Nombre</th><th>Correo</th><th>Teléfono</th><th>Mensaje</th></tr></thead>
            <tbody>
            <?php foreach ($_SESSION['contactos'] as $contacto): ?>
                <tr>
                    <td><?= htmlspecialchars($contacto['nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($contacto['email'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($contacto['telefono'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= nl2br(htmlspecialchars($contacto['mensaje'], ENT_QUOTES, 'UTF-8')) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>
</body>
</html>
