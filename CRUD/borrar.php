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
 * Borrar.php
 * 
 * Este archivo permite eliminar un evento de la base de datos "eventos" en BaseX según su nombre.
 * 
 * Variables:
 * - $message: cadena para mostrar el estado del borrado (éxito o error).
 * - $nameToDelete: nombre del evento a eliminar, obtenido desde el formulario.
 * - $names: lista de nombres de eventos obtenidos para mostrar en el selector del formulario.
 * - $session: instancia de la clase Session para conectarse con BaseX.
 * - $xquery: consulta XQuery que elimina el nodo <event> cuyo <name> coincide.
 */

 ?>

<nav>
    <a href="../lectura.php">Inicio</a> |
    <a href="Insertar.php">Insertar Evento</a> |
    <a href="Filtrar.php">Filtrar Evento</a> |
    <a href="editarEvento.php">Editar Evento</a>
</nav>
<hr>

<?php
include_once '../load.php';
use BaseXClient\Session;

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['name'])) {
    $nameToDelete = trim($_POST['name']);

    try {
        $session = new Session("localhost", 1984, "admin", "admin");
        $session->execute("OPEN eventos");

        // Usamos normalize-space y comillas simples para evitar errores
        $xquery = <<<XQ
for \$e in /events/event
where normalize-space(\$e/name) = '$nameToDelete'
return delete node \$e
XQ;

        $result = $session->execute("XQUERY " . $xquery);

        // Validación muy básica: si no hay error y se ejecutó, asumimos que eliminó
        $message = "✅ Evento eliminado correctamente.";
    } catch (Exception $e) {
        $message = "❌ Error al eliminar: " . $e->getMessage();
    } finally {
        if (isset($session)) $session->close();
    }
}

// Cargar nombres de eventos para el formulario
try {
    $session = new Session("localhost", 1984, "admin", "admin");
    $session->execute("OPEN eventos");
    $result = $session->execute('XQUERY for $e in /events/event return $e/name/string()');
    $names = array_filter(explode("\n", trim($result)));
} catch (Exception $e) {
    $message = "❌ Error al cargar eventos: " . $e->getMessage();
    $names = [];
} finally {
    if (isset($session)) $session->close();
}
?>

<h1>Eliminar Evento</h1>

<?php if ($message): ?>
    <p style="color: <?= str_starts_with($message, '✅') ? 'green' : 'red' ?>;"><?= $message ?></p>
<?php endif; ?>

<form method="post">
    <label>Selecciona el evento a eliminar:</label><br>
    <select name="name" required>
        <option value="">--Selecciona un evento--</option>
        <?php foreach ($names as $name): ?>
            <option value="<?= htmlspecialchars($name) ?>"><?= htmlspecialchars($name) ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <div style="display: flex; gap: 20px;">
        <input type="submit" value="Eliminar Evento">
        <button type="button" onclick="location.href='../lectura.php'">Volver</button>
    </div>
</form>
