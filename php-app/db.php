<?php
declare(strict_types=1);

function obtenerConexion(): mysqli
{
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $conexion = new mysqli('127.0.0.1', 'root', '', 'registro_contactos', 3306);
    $conexion->set_charset('utf8mb4');
    return $conexion;
}
