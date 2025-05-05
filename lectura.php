<nav>
    <a href="lectura.php">Inicio</a> |
    <a href="crud/Insertar.php">Insertar Evento</a>
    <a href="crud/Borrar.php">Eliminar Evento</a>
    <a href="crud/filtrar.php">Filtrar Evento</a>
</nav>
<hr>


<?php
include_once 'load.php';

use BaseXClient\Session;

try {
    $session = new Session("localhost", 1984, "admin", "admin");

    // Abre la base de datos
    $session->execute("OPEN eventos");

    // Ejecuta una consulta con opciones de salida formateada
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
    echo "Error: " . $e->getMessage();
} finally {
    $session->close();
}
?>

