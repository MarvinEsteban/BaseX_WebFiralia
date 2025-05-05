<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>Page Title</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
    <script src='main.js'></script>
</head>
 

<?php
/**
 * lectura.php
 * 
 * Este archivo muestra todo el contenido de la base de datos "eventos" de BaseX.
 * 
 * Variables:
 * - $session: instancia de la clase Session para conectarse con BaseX.
 * - $query: consulta XQuery que recupera todos los nodos <event> desde /events.
 * - $result: resultado XML formateado de la consulta.
 */

include_once 'load.php';
use BaseXClient\Session;
?>

<nav>
    <a href="lectura.php">Inicio</a> |
    <a href="crud/Insertar.php">Insertar Evento</a> |
    <a href="crud/Borrar.php">Eliminar Evento</a> |
    <a href="crud/filtrar.php">Filtrar Evento</a> |
    <a href="crud/editarEvento.php">Editar Evento</a>
</nav>
<hr>

<?php
try {
    $session = new Session("localhost", 1984, "admin", "admin");

    $session->execute("OPEN eventos");

    $query = <<<XQUERY
declare option output:method "xml";
declare option output:indent "yes";
<events>{
    /events/event
}</events>
XQUERY;

    $result = $session->execute("XQUERY " . $query);

    echo "<h1>Contenido de la Base de Datos</h1>";
    echo "<pre>" . htmlentities($result) . "</pre>";

} catch (Exception $e) {
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
} finally {
    if (isset($session)) {
        $session->close();
    }
}
?>