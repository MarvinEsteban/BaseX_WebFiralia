Después de haber realizado los diversos pasos especificados anteriormente vamos a empezar con los siguientes:

|                      1.Importar la base de xml de eventos de nuestro proyecto                              |
|                      2.Modificar lectura.php para presentar la BBDD completa                               |
|                       3.Página php de inserción un registro: Insertar.php                                  |
|                       4.Página php de borrado de un registro: Borrar.php                                   |
|                      5.Página php de filtrado de un registro por id: filtrar.php                           |
|                        6.Página html para editar un registro de la tabla.                                  |
|           7.Página html con diversos forms que te permita realizar todas las acciones anteriores           |



-----------1.IMPORTAR LA BASE DEL XML DE EVENTOS DE NUESTRO PROYECTO-----------

Vale aquí simplemente nos encargaremos de que nuestra base de xml que hemos utilizado con anterioridad se pueda utilizar en nuestro proyecto, siguiendo los pasos del enunciamo ya conseguimos esto, cabe destacar que se importan en aquellos archivos que forman parte del CRUD y en el lectura.php.

-----------2.MODIFICAR LECTURA.PHP PARA PRESENTAR LA BBDD COMPLETA-----------

Aquí haremos unos pequeños cambios en el lectura.php para poder ver nuestra base de datos, para ello solo tendremos que introducir el 
nombre de nuestro xml y introducir el código siguiente:

$session = new Session("localhost", 1984, "admin", "admin");

Abre la base de datos
$session->execute("OPEN eventos");

 Ejecuta una consulta con opciones de salida formateada
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


-----------3.PÁGINA PHP DE INSERCIÓN DE UN REGISTRO: INSERTAR.PHP-----------

Más de lo mismo en este caso, introduciremos las diferentes variables de nuestro xml y crearemos tanto el Xquery para la inserción como las diferentes validaciones y ejecuciones, para conseguir que nuestra base de datos no tenga errores y que vaya generando de manera incremental los nuevos id de las inserciones que se realicen, parte del código:

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

        echo "Evento insertado correctamente con ID $newId.";
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    } finally {
        $session->close();
    }

    -----------4.PÁGINA PHP DE BORRADO DE UN REGISTRO: BORRAR.PHP-----------

    Este apartado es simple le indicamos que busque el registro a eliminar y hacemos que lo elimine, código utilizado:

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
        $message = "Evento eliminado correctamente.";
    } catch (Exception $e) {
        $message = "Error al eliminar: " . $e->getMessage();
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
    $message = "Error al cargar eventos: " . $e->getMessage();
    $names = [];
} finally {
    if (isset($session)) $session->close();
}
?>


    -----------5.PÁGINA PHP DE FILTRADO DE UN REGISTRO POR ID: FILTRAR.PHP-----------

En este apartado aparte de transformar nuestro resultado a XML del Xquery para poder manejarlo mejor, también pondremos algunas validaciones en caso de que no se encuentre el id que introduciremos, caso que no debería ocurrir debido a que a la hora de introducir el id, se tiene que elegir entre los ya creamos anteriormente y solo estos, así todos deberían existir, una vez seleccionado el id, nos mostrara todos sus datos, parte del código:



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



 -----------6.PÁGINA HTML PARA EDITAR UN REGISTRO EN LA TABLA-----------

Aquí a partir de la id podremos modificar el registro correspondiente a esa id, en este podremos modificar todos los campos, menos el id, una vez introducidos todos los datos, podremos actualizar el registro, es importante que todos los campos esten utilizados, una vez hecho esto se introducirán los valoresde los campos nuevos por los antiguos, parte del código :

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





-----------7.PÁGINA HTML CON DIVERSOS FORMS QUE TE PRMITAN RELIZAR TODAS LAS ACCIONES ANTERIORES-----------

En nuestro caso podemos ir viendo los diefrentes forms en cada uno de los archivos como: borrar.php, filtrar-php, insertar.php, editarEvento.php y lectura.php.
 Todos siguen el mismo estilo que os enseño a continuación:

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

 <nav>
    <a href="../lectura.php">Inicio</a> |
    <a href="Insertar.php">Insertar Evento</a> |
    <a href="Filtrar.php">Filtrar Evento</a> |
    <a href="editarEvento.php">Editar Evento</a>
</nav>



