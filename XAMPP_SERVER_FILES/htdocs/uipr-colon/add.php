<?php
include_once('templates/header.php');
set_time_limit(0);

function hint($msg = 'hint', $color = 'green')
{
    echo "<a style=\"color:$color;\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"$msg\"><i class=\"far fa-question-circle\"></i></a>";
}

$errors = $item = ['title' => '', 'type' => '', 'published_date' => '', 'authors' => '', 'subjects' => '', 'description' => '', 'metadata' => '', 'image' => '', 'files' => ''];
$valid_title = $valid_type = $valid_date = $valid_authors = $valid_subjects = $valid_description = $valid_image = $valid_files = '';
$warning = false;

if (isset($_POST['submit'])) {
    $yearOnly = isset($_POST['yearOnly']);
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
    $img_tmp_path = '';
    echo $_FILES["image"]["tmp_name"];
    if (isset($_FILES["image"]["tmp_name"]) && !empty($_FILES["image"]["tmp_name"])) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check !== false) {
            $name = $_FILES["image"]['name'];
            $type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            $item['image'] = file_get_contents($_FILES["image"]['tmp_name']);
            //echo $contents == $item['image'];

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
        $img_tmp_path = $_FILES["image"]['tmp_name'];
    }

    if (empty($img_tmp_path)) $img_tmp_path = false;

    //image end

    //files
    $valid_files = TRUE;
    $large_file = FALSE;
    for ($i = 0; $i < count($_FILES['files']['name']); $i++) {

        //Get the temp file path
        $tmpFilePath = $_FILES['files']['tmp_name'][$i];
        echo $tmpFilePath;
        //Make sure we have a file path
        if (!empty($tmpFilePath) && (filesize($tmpFilePath) < 41943040)) {
            $item['files'][] = [
                'file_name' => $_FILES['files']['name'][$i],
                'tmp_path' => $tmpFilePath,
                'file' => file_get_contents($tmpFilePath)
            ];

            if (filesize($tmpFilePath) > 41943040) $large_file = TRUE;
        } else {
            if (filesize($tmpFilePath) > 41943040) {
                $errors['files'] = $errors['files'] . 'Uno de los archivos es muy grande #' . ($i + 1) . ')<br />';
                $large_file = TRUE;
            } else {
                $valid_files = false;
            }
            echo 'Error con archivo numero ' . ($i + 1);
            $valid_files = false;
        }
    }

    if (!$valid_files) {
        echo showWarn('INVALID FILES', 'A file is not valid.');
    }

    //files end
    echo '<hr />';


    if (array_filter($errors) && !$valid_files && $large_file) {
        echo showWarn('Error:', 'Errores se detectaron en la forma.');
    } else {
        $sql_errors = ['item' => '', 'authors' => '', 'type' => '', 'image' => '', 'files' => ''];

        $upload_item = [
            'title' => mysqli_real_escape_string($conn, $item['title']),
            'type' => mysqli_real_escape_string($conn, $item['type']),
            'published_date' => mysqli_real_escape_string($conn, $item['published_date']),
            'authors' => explode(',', trim(preg_replace('/\s\s+/', ' ', mysqli_real_escape_string($conn, $_POST['authors'])))),
            'subjects' => explode(',', trim(preg_replace('/\s\s+/', ' ', mysqli_real_escape_string($conn, $_POST['subjects'])))),
            'description' => mysqli_real_escape_string($conn, $item['description']),
            'metadata' => mysqli_real_escape_string($conn, $item['metadata'])
        ];

        $error = "";

        //insert type
        $sql_type = "INSERT INTO `type` (`type`) VALUES('{$upload_item['type']}')";
        $type_id = NULL;
        if (mysqli_query($conn, $sql_type)) {
            $type_id = mysqli_insert_id($conn);
            echo 'success type ' . $upload_item['type'] . ' id: ' . $type_id . '<br />';
        } else {
            $error = mysqli_error($conn);
            if (strpos($error, 'Duplicate entry') === false) {
                $sql_errors['type'] = 'Unexpected Insertion SQL ERROR (CONTACT A DEVELOPER): ' . $error . '<br />';
            } else {
                $qt = query("Select id from `type` where `type` = '{$upload_item['type']}'");
                if (count($qt) > 0) {
                    $type_id = $qt[0]['id'];
                    echo 'type already exists ' . $upload_item['type'] . ' id: ' . $type_id . '<br />';
                }
            }
        }

        echo '<hr />';

        //insert author (multi)
        $sql_author = "INSERT INTO `author` (`author_name`) VALUES";
        $author_ids = array();
        foreach ($upload_item['authors'] as $author) {
            if (mysqli_query($conn, $sql_author . "('$author')")) {
                $id = mysqli_insert_id($conn);
                $author_ids[] = $id;
                echo 'success author ' . $author . ' id: ' . $id;
            } else {
                $error = mysqli_error($conn);
                if (strpos($error, 'Duplicate entry') === false) {
                    $sql_errors['authors'] = 'Unexpected Insertion SQL ERROR (CONTACT A DEVELOPER): ' . $error . '<br />';
                } else {
                    $qt = query("Select id from `author` where `author_name` = '$author'");
                    if (count($qt) > 0) {
                        $id = $qt[0]['id'];
                        $author_ids[] = $id;
                        echo 'author already exists ' . $author . ' id: ' . $id . '<br />';
                    }
                }
            }
        }

        if (count($author_ids) <= 0) $author_ids = NULL;
        echo '<hr />';

        //insert image
        $data = $name = $id = $q = $img_id = null;
        if (isset($item['image']) && !empty($item['image'])) {
            $data = mysqli_real_escape_string($conn, $item['image']);
            $sql_image = "INSERT INTO `image` (`type`, `size`, `image`) VALUES ('$type', $image_size, '$data')";
            $q = mysqli_query($conn, $sql_image);
            if (isset($q)) {
                $img_id = mysqli_insert_id($conn);
                echo 'success image : ' . $name . ' id: ' . $img_id . '<br />';
            } else {
                $error = mysqli_error($conn);
                echo $error;
                $sql_errors['image'] = 'Unexpected error ' . $error;
            }
        }
        echo '<hr /> image id: ' . $img_id . '<hr/>';

        //insert subject (multi)
        $sql_subject = "INSERT INTO `subject` (`subject`) VALUES";
        $subject_ids = array();
        foreach ($upload_item['subjects'] as $subject) {
            if (mysqli_query($conn, $sql_subject . "('$subject')")) {
                $id = mysqli_insert_id($conn);
                $subject_ids[] = $id;
                echo 'success subject ' . $subject . ' id: ' . $id . '<br />';
            } else {
                $error = mysqli_error($conn);
                if (strpos($error, 'Duplicate entry') === false) {
                    $sql_errors['subjects'] = 'Unexpected Insertion SQL ERROR (CONTACT A DEVELOPER): ' . $error . '<br />';
                } else {
                    $qt = query("Select id from `subject` where `subject` = '$subject'");
                    if (count($qt) > 0) {
                        $id = $qt[0]['id'];
                        $author_ids[] = $id;
                        echo 'subject already exists ' . $author . ' id: ' . $id . '<br />';
                    }
                }
            }
        }
        echo '<hr /> subject: ';
        print_r($subject_ids);
        echo  '<hr/>';

        //insert item
        $sql_item = "INSERT INTO `item` (`title`, `type_id`, `image_id`, `published_date`, `year_only`, `description`, `meta`) VALUES ";
        $title = $upload_item['title'];
        $des = $upload_item['description'];
        $meta = $upload_item['metadata'];
        $date = $upload_item['published_date'];
        $img = $img_id === NULL ? 'null' : $img_id;
        $q = mysqli_query($conn, $sql_item . "('$title', $type_id, $img, '$date', '$yearOnly', '$des', '$meta')");
        $item_id = NULL;
        $path = FILE_FOLDER;
        if (isset($q)) {
            $id = mysqli_insert_id($conn);
            $path = $path . "/$id";
            mkdir($path);
            $item_id = $id;
            echo 'success item ' . $title . ' id: ' . $id . '<br />';
            echo mysqli_error($conn);
        } else {
            $error = mysqli_error($conn);
            $sql_errors['item'] = $error;
            echo '<b>' . $error . '</b>';
        }

        // gen file ------------------------------------------------------------------------------------------------------------------------
        $file_paths = array();
        $file_name = $path;
        foreach ($item['files'] as $file) {
            $file_name = uniqid('uipr', true) . '.' . $file['file_name'];
            file_put_contents("$path/$file_name", $file['file']);
            $file_paths[] = "$id/$file_name";
        }

        // insert paths
        $file_ids = array();
        $sql_file = "INSERT INTO `file` (`path`) VALUES ";
        foreach ($file_paths as $file_path) {
            $q = mysqli_query($conn, $sql_file . "('$file_path')");
            if (isset($q)) {
                $id = mysqli_insert_id($conn);
                $file_ids[] = $id;
                echo "File with $id was created.";
            } else {
                $error = mysqli_error($conn);
                echo 'ERROR: ' . $error;
            }
        }

        echo (count($file_ids) == count($file_paths) ? "CHECK PASSED!" : "CHECK FAILED");
        echo '<hr/>';

        //insert item_has_subject
        $sql_item_has_subject = "INSERT INTO `item_has_subject` (`item_id`, `subject_id`) VALUES";
        foreach ($subject_ids as $subject_id) {
            $q = mysqli_query($conn, $sql_item_has_subject . "($item_id, $subject_id)");

            if (isset($q)) {
                echo "<br>Relation created subject [$subject_id] -> item [$item_id]<br>";
            } else {
                $error = mysqli_error($conn);
                echo '<b>' . $error . '</b>';
            }
        }

        //insert file_has_item
        $sql_file_has_item = "INSERT INTO `file_has_item` (`file_id`, `item_id`) VALUES";
        foreach ($file_ids as $file_id) {
            $q = mysqli_query($conn, $sql_file_has_item . "($file_id, $item_id)");

            if (isset($q)) {
                echo "<br>Relation created file [$file_id] -> item [$item_id]<br>";
            } else {
                $error = mysqli_error($conn);

                echo '<b>' . $error . '</b>';
            }
        }
        //insert author_has_item
        $sql_author_has_item = "INSERT INTO `author_has_item` (`item_id`, `author_id`) VALUES";
        foreach ($author_ids as $author_id) {
            $q = mysqli_query($conn, $sql_author_has_item . "($item_id, $author_id)");

            if (isset($q)) {
                echo "<br>Relation created author [$author_id] -> item [$item_id]<br>";
            } else {
                $error = mysqli_error($conn);
                echo '<b>' . $error . '</b>';
            }
        }

        if (array_filter($errors)) {
            echo showWarn('SQL ERROR:', 'There were unexpected insertion errors. Last error: ' . $error);
            print_r($sql_errors);
        } else {
            mysqli_close($conn);
            header("Location: index.php#$item_id");
        }
    }
}

function not_valid_class($boolean = 'do nothing')
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
            <input type="text" id="type" name="type" placeholder="<?php echo $str; ?>" title="Por ejemplo, <?php echo $str; ?>" class="form-control <?php not_valid_class($valid_type); ?>" value="<?php echo $item['type']; ?>" required>
            <?php echo_invalid_feedback(!$valid_type, $errors['type']); ?>

        </div>
    </div>


    <!-- PUB DATE -->
    <div class="form-row">
        <label for="published_date" class="col-5 col-form-label" id='pub-date-label'>Fecha de Publicación:</label>
        <div class="col-7 input-group">
            <div class="input-group-prepend">
                <div class="input-group-text">
                    <input name="yearOnly" id="yearOnly" onclick="changePubDateToYear('pub-date-label')" type="checkbox" aria-label="Checkbox for para demostrar en el articulo el año de publicacion solamente.">
                </div>
            </div>
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
            <input class="form-control <?php not_valid_class($valid_authors); ?>" type="text" placeholder="" id="authors" name="authors" value="<?php
                                                                                                                                                if ($item['authors'] !== '') {
                                                                                                                                                    echo listToCSV($item['authors']);
                                                                                                                                                }
                                                                                                                                                ?>" readonly required>
            <div class="input-group-append">
                <button class="btn btn-outline-secondary" type="button" onclick="deleteReadonly('authorInput', 'authors')" title="Borrar todos los autores."><i class="fas fa-users-slash"></i></button>
                <button class="btn btn-outline-secondary" type="button" onclick="deleteLastReadonly('authorInput', 'authors')" title="Borrar el último autor/a entrado/a."><i class="fas fa-user-minus"></i></button>
            </div>
        </div>
        <div class="input-group mb-3">
            <input type="text" class="form-control <?php not_valid_class($valid_authors); ?>" placeholder="Miguel de Cervante" aria-label="Nombre del autor" id="authorInput">
            <div class="input-group-append">
                <button class="btn btn-outline-secondary" type="button" onclick="addAllToReadonly('authorInput', 'authors')" title="Añadir autor"><i class="fas fa-users"></i></button>
                <button class="btn btn-outline-secondary" type="button" onclick="addToReadonly('authorInput', 'authors')" title="Añadir autor"><i class="fas fa-user-plus"></i></button>
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
            <input class="form-control <?php not_valid_class($valid_subjects); ?>" type="text" placeholder="" id="subjects" name="subjects" value="<?php
                                                                                                                                                    if ($item['subjects'] !== '') {
                                                                                                                                                        echo listToCSV($item['subjects']);
                                                                                                                                                    }
                                                                                                                                                    ?>" readonly required>
            <div class="input-group-append">
                <button class="btn btn-outline-secondary" type="button" onclick="deleteReadonly('subjectsInput', 'subjects')" title="Borrar todos los sujetos."><i class="far fa-trash-alt"></i></button>
                <button class="btn btn-outline-secondary" type="button" onclick="deleteLastReadonly('subjectsInput', 'subjects')" title="Borrar el último sujeto entrado."><i class="fas fa-minus"></i></button>
            </div>
        </div>
        <div class="input-group mb-3">
            <input type="text" class="form-control <?php not_valid_class($valid_subjects); ?>" placeholder="Caballerias" aria-label="Sujetos del articulo" id="subjectsInput">
            <div class="input-group-append">
                <button class="btn btn-outline-secondary" type="button" onclick="addAllToReadonly('subjectsInput', 'subjects')" title="Añadir sujeto"><i class="fas fa-reply-all"></i></button>
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
            <?php hint(
                'Puede seleccionar más de un archivo. 
                El máximo tamaño combinado es de 40 megabytes. Este limite esta expuesto por el servidor
                Si desea un tamaño mas grande habla con el webmaster para que edite la configuración de PHP 
                (php.ini -> upload_max_filesize y post_max_size, Requerirá un reinicio de XAMPP).'
            ); ?>
        </label>
        <div class="col-xs-1 text-center">
            <input class="form-control <?php not_valid_class($valid_files); ?>" type="file" class="btn" id="files" name="files[]" multiple="multiple" required>
            <?php echo_invalid_feedback(!$valid_files, $errors['files']); ?>
        </div>
    </div>
    <hr />

    <button class="btn btn-success" type="submit" name="submit" onclick="allowreload=true;addAllToReadonly('authorInput', 'authors');addAllToReadonly('subjectsInput', 'subjects');">Agregar Artículo</button>
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

    function deleteLastReadonly(input, output) {
        const field = document.getElementById(input);
        const readonly = document.getElementById(output);
        if (readonly.value.trim() === "") return;
        let arr = readonly.value.trim().split(",");
        let popped = arr.pop().trim();
        readonly.value = (arr + "").trim();
        if (field.value === "") {
            field.value = popped;
        } else {
            field.value += `, ${popped}`;
        }
    }

    function deleteReadonly(input, output) {
        const values = document.getElementById(output).value;
        document.getElementById(output).value = "";
        const field = document.getElementById(input);
        if (field.value === "") {
            field.value = values.trim();
        } else {
            field.value += `, ${values.trim()}`;
        }
    }

    function addAllToReadonly(input, output) {
        const field = document.getElementById(input);
        const readonly = document.getElementById(output);
        let fieldVal = field.value.trim();

        if (fieldVal === "") {
            field.value = "";
            return;
        }

        let arr = fieldVal.split(",");

        for (i = 0; i < arr.length; i++) {
            if (readonly.value === "") {
                readonly.value += `${arr[i].trim()}`
            } else {
                readonly.value += `, ${arr[i].trim()}`
            }
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

    function addToReadonly(input, output) {
        const field = document.getElementById(input);
        const readonly = document.getElementById(output);
        let fieldVal = field.value.trim();
        if (fieldVal === "") {
            field.value = "";
            return;
        } else if (fieldVal.includes(',')) {
            field.value = field.value.replaceAll(",", "");
            return;
        }

        if (readonly.value === "") {
            readonly.value += `${fieldVal.trim()}`
        } else {
            readonly.value += `, ${fieldVal.trim()}`
        }

        readonly.value = titleCase(readonly.value.trim());
        readonly.title = readonly.value.trim();
        field.value = "";

        function titleCase(str) {
            let splitStr = str.toLowerCase().split(' ');
            for (i = 0; i < splitStr.length; i++)
                splitStr[i] = splitStr[i].charAt(0).toUpperCase() + splitStr[i].substring(1);

            return splitStr.join(' ');
        }
    }

    function changePubDateToYear(id) {
        const label = document.getElementById(id);
        const input = document.getElementById('yearOnly');
        const from = 'Fecha';
        const to = 'Año';

        if (input.checked) {
            label.innerHTML = label.innerHTML.replace(from, to).trim();
        } else {
            label.innerHTML = label.innerHTML.replace(to, from).trim();
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