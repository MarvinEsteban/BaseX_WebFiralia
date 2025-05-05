<nav>
    <a href="../lectura.php">Inicio</a> |
    <a href="Insertar.php">Insertar Evento</a>
</nav>
<hr>

<?php
include_once '../load.php';

use BaseXClient\Session;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nameToDelete = $_POST['name'];

    try {
        $session = new Session("localhost", 1984, "admin", "admin");
        $session->execute("OPEN eventos");

        // Elimina el evento cuyo <name> coincida exactamente
        $xquery = <<<XQ
for \$e in /events/event
where \$e/name = "$nameToDelete"
return delete node \$e
XQ;

        $session->execute("XQUERY " . $xquery);
        echo "✅ Evento eliminado correctamente.";

    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    } finally {
        $session->close();
    }
} else {
    // Mostrar lista de nombres de eventos
    try {
        $session = new Session("localhost", 1984, "admin", "admin");
        $session->execute("OPEN eventos");

        $result = $session->execute('XQUERY /events/event/name/string()');
        $names = explode("\n", trim($result));

    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    } finally {
        $session->close();
    }
?>

<h1>Eliminar Evento</h1>
<form method="post">
    <label>Selecciona el evento a eliminar:</label><br>
    <select name="name">
        <?php foreach ($names as $name): ?>
            <option value="<?= htmlspecialchars($name) ?>"><?= htmlspecialchars($name) ?></option>
        <?php endforeach; ?>
    </select><br><br>
    <div style="display: flex; ">
        <input type="submit" value="Eliminar Evento"> &nbsp;&nbsp;&nbsp;
        <a href="../lectura.php">
    <input type="button" value="Volver">
</a>


</div>
    
</form>

<?php
}
?>
