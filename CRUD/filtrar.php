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
 * filtrar.php
 *
 * Este script permite al usuario seleccionar un evento por su ID desde una lista desplegable
 * y muestra los detalles completos del evento, incluyendo una visualización del XML.
 *
 * Variables principales:
 * - $idToFilter: ID del evento seleccionado por el usuario.
 * - $name, $type, $start_date, $end_date, $about, $price: detalles del evento extraídos del XML.
 * - $errorMessage: mensaje de error si ocurre alguno durante la consulta o procesamiento.
 * - $ids: lista de todos los IDs disponibles en la base de datos.
 */
?>

<nav>
    <a href="../lectura.php">Inicio</a> |
    <a href="Insertar.php">Insertar Evento</a> |
    <a href="Borrar.php">Eliminar Evento</a> |
    <a href="editarEvento.php">Editar Evento</a>
</nav>
<hr>

<?php
include_once '../load.php';
use BaseXClient\Session;

$name = $type = $start_date = $end_date = $about = $price = null;
$errorMessage = "";

// Si se envió el formulario con un ID seleccionado
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['id'])) {
    $idToFilter = trim($_POST['id']);

    try {
        $session = new Session("localhost", 1984, "admin", "admin");
        $session->execute("OPEN eventos");

        // Consulta XQuery modificada para obtener estructura plana
        $query = <<<XQUERY
for \$e in /events/event
where \$e/id = "$idToFilter"
return
<event>
    <name>{\$e/name/string()}</name>
    <type>{\$e/type/string()}</type>
    <start_date>{\$e/start_date/string()}</start_date>
    <end_date>{\$e/end_date/string()}</end_date>
    <about>{\$e/about/string()}</about>
    <price>{\$e/price/string()}</price>
</event>
XQUERY;

        $result = $session->execute("XQUERY " . $query);

        if (trim($result) === "") {
            $errorMessage = "No se encontró el evento con ID $idToFilter.";
        } else {
            // Formatear el XML para mostrarlo con indentación
            $dom = new DOMDocument();
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            
            if ($dom->loadXML($result)) {
                $formattedXml = $dom->saveXML();
                echo "<h3>XML Obtenido:</h3><pre>" . htmlentities($formattedXml) . "</pre>";
                
                $event = simplexml_load_string($result);
                if ($event !== false) {
                    // Extraer valores (estructura plana gracias a la consulta modificada)
                    $name = (string)$event->name;
                    $type = (string)$event->type;
                    $start_date = (string)$event->start_date;
                    $end_date = (string)$event->end_date;
                    $about = (string)$event->about;
                    $price = (string)$event->price;
                } else {
                    $errorMessage = "Error al interpretar el XML: " . print_r(libxml_get_errors(), true);
                    libxml_clear_errors();
                }
            } else {
                $errorMessage = "El XML obtenido no es válido";
            }
        }

    } catch (Exception $e) {
        $errorMessage = "Error: " . $e->getMessage();
    } finally {
        if (isset($session)) $session->close();
    }
}

// Cargar todos los IDs disponibles
try {
    $session = new Session("localhost", 1984, "admin", "admin");
    $session->execute("OPEN eventos");
    $idsRaw = $session->execute('XQUERY for $e in /events/event return $e/id/string()');
    $ids = array_filter(explode("\n", trim($idsRaw)));
} catch (Exception $e) {
    $errorMessage = "Error al cargar los IDs: " . $e->getMessage();
    $ids = [];
} finally {
    if (isset($session)) $session->close();
}
?>

<h1>Filtrar Evento por ID</h1>
<form method="post">
    <label for="id">Selecciona el ID del evento:</label><br>
    <select name="id" id="id" onchange="this.form.submit()">
        <option value="">--Selecciona un evento--</option>
        <?php foreach ($ids as $id): ?>
            <option value="<?= htmlspecialchars($id) ?>" <?= (isset($idToFilter) && $idToFilter == $id) ? 'selected' : '' ?>>
                <?= htmlspecialchars($id) ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>
</form>

<?php if ($name): ?>
    <h3>Detalles del Evento</h3>
    <p><strong>Nombre:</strong> <?= htmlspecialchars($name) ?></p>
    <p><strong>Tipo:</strong> <?= htmlspecialchars($type) ?></p>
    <p><strong>Fecha de inicio:</strong> <?= htmlspecialchars($start_date) ?></p>
    <p><strong>Fecha de fin:</strong> <?= htmlspecialchars($end_date) ?></p>
    <p><strong>Descripción:</strong> <?= htmlspecialchars($about) ?></p>
    <p><strong>Precio:</strong> <?= htmlspecialchars($price) ?></p>
<?php elseif ($errorMessage): ?>
    <p style="color:red;"><?= $errorMessage ?></p>
<?php endif; ?>