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
 * editarEvento.php
 *
 * Este archivo permite editar un evento existente en la base de datos "eventos" de BaseX, seleccionándolo por ID.
 *
 * Variables principales:
 * - $id: ID del evento a editar (seleccionado por el usuario).
 * - $name, $type, $start_date, $end_date, $about, $price: nuevos valores del evento introducidos por el usuario.
 * - $errorMessage, $successMessage: mensajes de retroalimentación para el usuario.
 * - $ids: lista de IDs de eventos cargados desde la base de datos para poblar el selector.
 */
?>

<nav>
    <a href="../lectura.php">Inicio</a> |
    <a href="Insertar.php">Insertar Evento</a> |
    <a href="Borrar.php">Eliminar Evento</a> |
    <a href="filtrar.php">Filtrar Evento</a>
</nav>
<hr>

<?php
include_once '../load.php';
use BaseXClient\Session;

$id = $name = $type = $start_date = $end_date = $about = $price = null;
$errorMessage = $successMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['id'])) {
    $id = trim($_POST['id']);

    // Solo continuar con actualización si el resto de los campos están presentes
    if (isset($_POST['name'], $_POST['type'], $_POST['start_date'], $_POST['end_date'], $_POST['about'], $_POST['price'])) {
        $name = $_POST['name'];
        $type = $_POST['type'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $about = $_POST['about'];
        $price = $_POST['price'];

        // Aquí continúa la lógica para actualizar...


        try {
            $session = new Session("localhost", 1984, "admin", "admin");
            $session->execute("OPEN eventos");

            $query = <<<XQUERY
declare variable \$id external;
declare variable \$name external;
declare variable \$type external;
declare variable \$start_date external;
declare variable \$end_date external;
declare variable \$about external;
declare variable \$price external;

let \$event := /events/event[normalize-space(id) = \$id]
return (
    replace node \$event/name with <name>{\$name}</name>,
    replace node \$event/type with <type>{\$type}</type>,
    replace node \$event/start_date with <start_date>{\$start_date}</start_date>,
    replace node \$event/end_date with <end_date>{\$end_date}</end_date>,
    replace node \$event/about with <about>{\$about}</about>,
    replace node \$event/price with <price>{\$price}</price>
)
XQUERY;

            $queryObj = $session->query($query);
            $queryObj->bind("id", $id);
            $queryObj->bind("name", $name);
            $queryObj->bind("type", $type);
            $queryObj->bind("start_date", $start_date);
            $queryObj->bind("end_date", $end_date);
            $queryObj->bind("about", $about);
            $queryObj->bind("price", $price);

            $queryObj->execute();
            $queryObj->close();

            $successMessage = "Evento actualizado correctamente.";
        } catch (Exception $e) {
            $errorMessage = "Error al actualizar: " . $e->getMessage();
        } finally {
            if (isset($session))
                $session->close();
        }
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
    if (isset($session))
        $session->close();
}
?>

<h1>Editar Evento</h1>

<?php if ($successMessage): ?>
    <p style="color:green;"><?= $successMessage ?></p>
<?php elseif ($errorMessage): ?>
    <p style="color:red;"><?= $errorMessage ?></p>
<?php endif; ?>

<form method="post">
    <label for="id">ID del evento:</label><br>
    <select name="id" id="id" required onchange="this.form.submit()">
        <option value="">--Selecciona un evento--</option>
        <?php foreach ($ids as $idOption): ?>
            <option value="<?= htmlspecialchars($idOption) ?>" <?= ($id == $idOption) ? 'selected' : '' ?>>
                <?= htmlspecialchars($idOption) ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <?php if ($id): ?>
        <label for="name">Nombre:</label><br>
        <input type="text" name="name" id="name" value="<?= htmlspecialchars($name ?? '') ?>" required><br><br>

        <label for="type">Tipo:</label><br>
        <input type="text" name="type" id="type" value="<?= htmlspecialchars($type ?? '') ?>" required><br><br>

        <label for="start_date">Fecha de inicio:</label><br>
        <input type="date" name="start_date" id="start_date" value="<?= htmlspecialchars($start_date ?? '') ?>"
            required><br><br>

        <label for="end_date">Fecha de fin:</label><br>
        <input type="date" name="end_date" id="end_date" value="<?= htmlspecialchars($end_date ?? '') ?>" required><br><br>

        <label for="about">Descripción:</label><br>
        <textarea name="about" id="about" required><?= htmlspecialchars($about ?? '') ?></textarea><br><br>

        <label for="price">Precio:</label><br>
        <input type="number" name="price" id="price" value="<?= htmlspecialchars($price ?? '') ?>" required><br><br>

        <button type="submit">Actualizar</button>
    <?php endif; ?>
</form>