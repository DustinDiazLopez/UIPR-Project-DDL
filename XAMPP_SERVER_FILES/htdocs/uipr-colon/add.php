<?php
include_once('templates/header.php');

function hint($msg = 'hint', $color = 'green')
{
    echo "<a style=\"color:$color;\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"$msg\"><i class=\"far fa-question-circle\"></i></a>";
}

$errors = $item = ['title' => '', 'type' => '', 'published_date' => '', 'authors' => '', 'subjects' => '', 'description' => '', 'metadata' => '', 'image' => '', 'files' => ''];
$valid_title = $valid_type = $valid_date = $valid_authors = $valid_subjects = $valid_description = $valid_image = $valid_files = '';
$warning = false;

if (isset($_POST['submit'])) {
    $item = [
        'title' => htmlspecialchars($_POST['title']),
        'type' => htmlspecialchars($_POST['type']),
        'published_date' => htmlspecialchars($_POST['published_date']),
        'authors' => explode(',', trim(preg_replace('/\s\s+/', ' ', htmlspecialchars($_POST['authors'])))),
        'subjects' => explode(',', trim(preg_replace('/\s\s+/', ' ', htmlspecialchars($_POST['subjects'])))),
        'description' => htmlspecialchars($_POST['description']),
        'metadata' => htmlspecialchars($_POST['metadata']),
        'image' => '',
        'files' => array(),
    ];


    if (empty($item['title'])) {
        $errors['title'] = 'Favor de proveer un titulo.';
        $valid_title = false;
    } else $valid_title = true;

    if (empty($item['type'])) {
        $errors['type'] = 'Favor de proveer un tipo.';
        $valid_type = false;
    } else $valid_type = true;

    if (empty($item['published_date'])) {
        $errors['published_date'] = 'Favor de proveer un tipo.';
        $valid_date = false;
    } elseif (!preg_match('/^[0-9]*\-[0-9]*\-[0-9]*$/', $item['published_date'])) {
        $errors['published_date'] = 'Fecha invalida tiene que ser, por ejemplo, yyyy/mm/dd';
        $valid_date = false;
    } else $valid_date = true;

    if (count($item['authors']) < 1) {
        $errors['authors'] = 'Favor de proveer al menos un autor.';
        $valid_authors = false;
    } else $valid_authors = true;

    if (count($item['subjects']) < 1) {
        $errors['subjects'] = 'Favor de proveer al menos un sujeto.';
        $valid_subjects = false;
    } else $valid_subjects = true;

    if (empty($item['description'])) {
        $errors['description'] = 'Favor de proveer una descripción.';
        $valid_description = false;
    } else $valid_description = true;

    //image
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if ($check !== false) {
        $name = $_FILES["image"]['name'];
        $type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $item['image'] = file_get_contents($_FILES["image"]['tmp_name']);

        $image_size = strlen($item['image']) / 1e+6;

        //bytes to MB
        if ($image_size > 16) {
            $errors['image'] = "La imagen es muy grande: " . $image_size . " MB > 16 MB";
            $valid_image = false;
        }
    } else {
        $errors['image'] = "No es una imagen.";
        $valid_image = false;
    }
    //image end

    //files
    for ($i = 0; $i < count($_FILES['files']['name']); $i++) {

        //Get the temp file path
        $tmpFilePath = $_FILES['files']['tmp_name'][$i];

        //Make sure we have a file path
        if (!empty($tmpFilePath)) {
            $item['files'][] = [
                'file_name' => $_FILES['files']['name'][$i],
                'file' => file_get_contents($tmpFilePath)
            ];
        } else {
            $errors['files'] = 'Hubo un error con una de los archivos (archivo numero ' . ($i + 1) . ')';
            $valid_files = false;
        }
    }

    //files end


    if (array_filter($errors)) {
        echo showWarn('Error:', 'Errores se detectaron en la forma.');
    }
}

function not_valid_class($boolean='do nothing')
{
    if ($boolean === true) echo 'is-valid';
    elseif ($boolean === false) echo 'is-invalid';
}

function echo_invalid_feedback($boolean = false, $msg = 'Invalido')
{
    if ($boolean) echo "<div class=\"invalid-feedback\">$msg</div>";
}

// is-valid class for green
// <div class="valid-feedback">text</div>
// is-invalid class for red
//<div class="invalid-feedback">text</div>
?>

<link rel="stylesheet" href="css/autocomplete.css">


<form autocomplete="off" style="color:black;" action="#" method="POST" enctype="multipart/form-data">
    <div class="form-row" style="text-align: center;">
        <h1>Añadir un Artículo</h1>
    </div>
    <!-- TITLE AND TYPE -->
    <div class="form-row">
        <div class="col-md-7 mb-3">
            <label for="title">Título</label>
            <input type="text" id="title" name="title" title="Título del artículo." placeholder="Don Quijote de la Mancha" class="form-control <?php not_valid_class($valid_title); ?>" value="<?php echo $item['title']; ?>" required>
            <?php echo_invalid_feedback(!$valid_title, $errors['title']); ?>
        </div>
        <div class="col-md-5 mb-3 autocomplete">
            <label for="type">Tipo de Documento
                <?php
                $str = '';
                $types = query(SQL_GET_DOC_TYPES);
                $len = count($types);
                for ($i = 0; $i < $len; $i++) {
                    $str = $str . $types[$i]['type'];
                    if ($i != $len - 1) $str = $str . ', ';
                }

                hint('Los tipos disponibles son: ' . $str . '. Si no existe uno cual describe su artículo lo puede añadir y luego estará como opción en el sistema.');

                unset($types);
                unset($len);
                ?>

            </label>
            <input type="text" id="type" name="type" placeholder="<?php echo $str; ?>" title="Por ejemplo, <?php echo $str; ?>" class="form-control <?php not_valid_class($valid_type); ?>" value="<?php echo $item['type']; ?>">
            <?php echo_invalid_feedback(!$valid_type, $errors['type']); ?>

        </div>
    </div>


    <!-- PUB DATE -->
    <div class="form-row">
        <label for="published_date" class="col-5 col-form-label">Fecha de Publicación:</label>
        <div class="col-7">
            <input type="date" name="published_date" id="published_date" class="form-control <?php not_valid_class($valid_date); ?>" name="published_date" value="<?php echo $item['published_date']; ?>" required>
            <?php echo_invalid_feedback(!$valid_date, $errors['published_date']); ?>
        </div>
    </div>

    <hr />
    <!-- AUTHORS -->
    <div class="form-row">
        <label for="authors">Autores
            <?php hint('Favor no utilizar commas, especificar el nombre completo sin commas. Si se detectan commas, se eliminarán.'); ?>
        </label>
        <div class="input-group mb-3">
            <input class="form-control <?php not_valid_class($valid_authors); ?>" type="text" placeholder="" id="authors" name="authors" 
            value="<?php 
            if ($item['authors'] !== '') {
                echo listToCSV($item['authors']); 
            }
            ?>" readonly required>
            <div class="input-group-append">
                <button class="btn btn-outline-secondary" type="button" onclick="deleteReadonly('authors')" title="Borrar todos los autores."><i class="far fa-trash-alt"></i></button>
                <button class="btn btn-outline-secondary" type="button" onclick="deleteLastReadonly('authors')" title="Borrar el último autor/a entrado/a."><i class="fas fa-backspace"></i></button>
            </div>
        </div>
        <div class="input-group mb-3">
            <input type="text" class="form-control <?php not_valid_class($valid_authors); ?>" placeholder="Miguel de Cervante" aria-label="Nombre del autor" id="authorInput">
            <div class="input-group-append">
                <button class="btn btn-outline-secondary" type="button" onclick="addToReadonly('authorInput', 'authors')" title="Añadir autor"><i class="fas fa-plus"></i></button>
            </div>
            <?php echo_invalid_feedback(!$valid_authors, $errors['authors']); ?>
        </div>
    </div>
    <hr />

    <!-- SUBJECTS -->
    <div class="form-row">
        <label for="subjects">Sujetos
            <?php hint('Favor no utilizar commas, especificar el sujeto sin commas. Si se detectan commas, se eliminarán.'); ?>
        </label>
        <div class="input-group mb-3">
            <input class="form-control <?php not_valid_class($valid_subjects); ?>" type="text" placeholder="" id="subjects" name="subjects" 
            value="<?php 
            if ($item['subjects'] !== '') {
                echo listToCSV($item['subjects']); 
            }
            ?>" readonly required>
            <div class="input-group-append">
                <button class="btn btn-outline-secondary" type="button" onclick="deleteReadonly('subjects')" title="Borrar todos los sujetos."><i class="far fa-trash-alt"></i></button>
                <button class="btn btn-outline-secondary" type="button" onclick="deleteLastReadonly('subjects')" title="Borrar el último sujeto entrado."><i class="fas fa-backspace"></i></button>
            </div>
        </div>
        <div class="input-group mb-3">
            <input type="text" class="form-control <?php not_valid_class($valid_subjects); ?>" placeholder="Caballerias" aria-label="Sujetos del articulo" id="subjectsInput">
            <div class="input-group-append">
                <button class="btn btn-outline-secondary" type="button" onclick="addToReadonly('subjectsInput', 'subjects')" title="Añadir sujeto"><i class="fas fa-plus"></i></button>
            </div>
            <?php echo_invalid_feedback(!$valid_subjects, $errors['subjects']); ?>
        </div>
    </div>
    <hr />

    <!-- DESCRIPTION -->
    <div class="form-group">
        <label for="description">Descripción del artículo</label>
        <textarea class="form-control <?php not_valid_class($valid_description); ?>" id="description" name="description" rows="3" required><?php echo $item['description']; ?></textarea>
        <?php echo_invalid_feedback(!$valid_description, $errors['description']); ?>
    </div>

    <!-- METADATA -->
    <div class="form-group">
        <label for="metadata">Metadata
            <?php hint('Información que no será visible, pero que se utilizará en la búsqueda (por ejemplo, texto completo del artículo, otros títulos, etc.). En otras palabras, cualquier información relacionada con el artículo.'); ?>
        </label>
        <textarea class="form-control" id="metadata" name="metadata" rows="3"><?php echo $item['metadata']; ?></textarea>
    </div>

    <hr />
    <!-- IMAGE -->
    <div class="form-row">
        <label for="image">Subir una Imagen
            <?php hint('El máximo tamaño para la imagen es de 16 megabytes (MB).'); ?>
        </label>
        <div class="col-xs-1">
            <input class="form-control <?php not_valid_class($valid_image); ?>" type="file" class="btn" id="image" name="image">
            <?php echo_invalid_feedback(!$valid_image, $errors['image']); ?>
        </div>
    </div>
    <hr />
    <!-- FILES -->
    <div class="form-row">
        <label for="files">Seleccionar los Archivos
            <?php hint('Puede seleccionar más de un archivo. El máximo tamaño para cada archivo es de 40 megabytes. Un archivo con un tamaño más grande podrá funcionar, pero NO SE RECOMIENDA.'); ?>
        </label>
        <div class="col-xs-1 text-center">
            <input class="form-control <?php not_valid_class($valid_files); ?>" type="file" class="btn" id="files" name="files[]" multiple="multiple" required>
            <?php echo_invalid_feedback(!$valid_files, $errors['files']); ?>
        </div>
    </div>
    <hr />

    <button class="btn btn-success" type="submit" name="submit" onclick="allowreload=true">Agregar Artículo</button>
</form>

<script charset="utf-8" src="js/jquery-3.2.1.slim.min.js"></script>
<script src="js/autocomplete.js"></script>

<script>
    /*An array containing all the country names in the world:*/
    const types = [
        <?php foreach (query(SQL_GET_DOC_TYPES) as $type) echo '"' . htmlspecialchars($type['type']) . '",'; ?>
    ];

    let allowReload = false;

    /*initiate the autocomplete function on the "myInput" element, and pass along the countries array as possible autocomplete values:*/
    autocomplete(document.getElementById("type"), types);

    function deleteLastReadonly(output) {
        const readonly = document.getElementById(output);
        if (readonly.value.trim() === "") return;
        let arr = readonly.value.trim().split(",");
        arr.pop()
        readonly.value = (arr + "").trim();
    }

    function deleteReadonly(output) {
        document.getElementById(output).value = "";
    }

    function addToReadonly(input, output) {
        const field = document.getElementById(input);
        const readonly = document.getElementById(output);
        let fieldVal = field.value.trim();
        if (fieldVal === "") {
            field.value = "";
            return;
        } else if (fieldVal.includes(',')) {
            field.value = field.value.replace(",", "");
            return;
        }

        if (readonly.value === "") {
            readonly.value += `${fieldVal}`
        } else {
            readonly.value += `, ${fieldVal}`
        }

        readonly.value = titleCase(readonly.value.trim());
        readonly.title = readonly.value;
        field.value = "";

        function titleCase(str) {
            let splitStr = str.toLowerCase().split(' ');
            for (i = 0; i < splitStr.length; i++)
                splitStr[i] = splitStr[i].charAt(0).toUpperCase() + splitStr[i].substring(1);

            return splitStr.join(' ');
        }
    }

    $(document).ready(function() {
        $(window).keydown(function(event) {
            if (event.keyCode == 13) {
                event.preventDefault();
                return false;
            }
        });
    });

    window.onbeforeunload = function(e) {
        if (!allowReload) {
            e = e || window.event;

            // For IE and Firefox prior to version 4
            if (e) e.returnValue = 'Sure?';

            // For Safari
            return 'Sure?';
        }
    };
</script>

<?php include_once('templates/footer.php'); ?>