<nav>
    <a href="../lectura.php">Inicio</a> |
    <a href="Insertar.php">Insertar Evento</a>
    <a href="Borrar.php">Eliminar Evento</a>

</nav>
<hr>

<?php
include_once '../load.php';

use BaseXClient\Session;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idToFilter = $_POST['id'];  // ID del evento a filtrar

    try {
        // Crear sesión de BaseX
        $session = new Session("localhost", 1984, "admin", "admin");
        $session->execute("OPEN eventos");

        // Filtrar el evento con el ID seleccionado
        $query = <<<XQUERY
let \$event := /events/event[id = "$idToFilter"]
return
    <event>
        <name>{\$event/name}</name>
        <type>{\$event/type}</type>
        <start_date>{\$event/start_date}</start_date>
        <end_date>{\$event/end_date}</end_date>
        <about>{\$event/about}</about>
        <price>{\$event/price}</price>
    </event>
XQUERY;

        // Ejecutar la consulta XQuery
        $eventDetails = $session->execute("XQUERY " . $query);
        $event = simplexml_load_string($eventDetails);  // Convertir el resultado a XML para manejarlo fácilmente

        if ($event) {
            // Si el evento fue encontrado, mostrar sus detalles
            $name = (string) $event->name;
            $type = (string) $event->type;
            $start_date = (string) $event->start_date;
            $end_date = (string) $event->end_date;
            $about = (string) $event->about;
            $price = (string) $event->price;
        } else {
            // Si no se encuentra el evento con ese ID
            $errorMessage = "No se encontró el evento con el ID seleccionado.";
        }

    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    } finally {
        $session->close();
    }
} else {
    // Obtener la lista de IDs de eventos para mostrarlos en el formulario
    try {
        $session = new Session("localhost", 1984, "admin", "admin");
        $session->execute("OPEN eventos");

        // Obtener los IDs de los eventos (suponiendo que el evento tiene un nodo 'id')
        $result = $session->execute('XQUERY /events/event/id/string()');
        $ids = explode("\n", trim($result));  // Dividir los IDs por líneas

    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    } finally {
        $session->close();
    }
}

?>

<h1>Filtrar Evento por ID</h1>
<form method="post">
    <label>Selecciona el ID del evento:</label><br>
    <select name="id" onchange="this.form.submit()">
        <option value="">--Selecciona un evento--</option>
        <?php foreach ($ids as $id): ?>
            <option value="<?= htmlspecialchars($id) ?>"><?= htmlspecialchars($id) ?></option>
        <?php endforeach; ?>
    </select><br><br>
</form>

<?php if (isset($name)): ?>
    <h3>Detalles del Evento</h3>
    <p><strong>Nombre:</strong> <?= htmlspecialchars($name) ?></p>
    <p><strong>Tipo:</strong> <?= htmlspecialchars($type) ?></p>
    <p><strong>Fecha de inicio:</strong> <?= htmlspecialchars($start_date) ?></p>
    <p><strong>Fecha de fin:</strong> <?= htmlspecialchars($end_date) ?></p>
    <p><strong>Descripción:</strong> <?= htmlspecialchars($about) ?></p>
    <p><strong>Precio:</strong> <?= htmlspecialchars($price) ?></p>
<?php elseif (isset($errorMessage)): ?>
    <p><?= $errorMessage ?></p>
<?php endif; ?>

