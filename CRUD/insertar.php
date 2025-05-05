<nav>
    <a href="../lectura.php">Inicio</a> |
    <a href="Borrar.php">Eliminar Evento</a>
</nav>
<hr>

<?php
include_once '../load.php';
use BaseXClient\Session;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['name']);
    $type = htmlspecialchars($_POST['type']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $about = htmlspecialchars($_POST['about']);
    $price = htmlspecialchars($_POST['price']);

    try {
        $session = new Session("localhost", 1984, "admin", "admin");
        $session->execute("OPEN eventos");

        // Obtener el último ID existente (si no hay ninguno, usar 0)
        $lastIdQuery = "XQUERY if (empty(/events/event/id)) then 0 else max(/events/event/id)";
        $lastId = (int) $session->execute($lastIdQuery);
        $newId = $lastId + 1;

        // Escapar contenido para XML seguro
        $newEvent = "<event>
  <id>$newId</id>
  <name>$name</name>
  <type>$type</type>
  <start_date>$start_date</start_date>
  <end_date>$end_date</end_date>
  <about>$about</about>
  <price>$price</price>
</event>";

        // Insertar el nuevo evento
        $session->execute("XQUERY insert node $newEvent into /events");

        echo "✅ Evento insertado correctamente con ID $newId.";
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage();
    } finally {
        $session->close();
    }
} else {
?>

<h1>Insertar Nuevo Evento</h1>
<form method="post">
    <label>Nombre:</label><br>
    <input type="text" name="name" required><br><br>

    <label>Tipo:</label><br>
    <input type="text" name="type" required><br><br>

    <label>Fecha de inicio:</label><br>
    <input type="date" name="start_date" required><br><br>

    <label>Fecha de fin:</label><br>
    <input type="date" name="end_date" required><br><br>

    <label>Descripción:</label><br>
    <input type="text" name="about" required><br><br>

    <label>Precio:</label><br>
    <input type="text" name="price" required><br><br>

    <div style="display: flex; gap: 10px;">
        <input type="submit" value="Insertar Evento">
        <button type="button" onclick="window.location.href='../lectura.php'">Volver</button>
    </div>
</form>

<?php
}
?>
