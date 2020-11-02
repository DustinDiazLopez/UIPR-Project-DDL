<?php

include_once('connect.php');
include_once('utils/utils.php');

authenticate();

set_time_limit(0);

$errors = $item = ['title' => '', 'type' => '', 'published_date' => '', 'authors' => '', 'subjects' => '', 'description' => '', 'metadata' => '', 'image' => '', 'files' => ''];
$valid_title = $valid_type = $valid_date = $valid_authors = $valid_subjects = $valid_description = $valid_image = $valid_files = '';
$warning = false;

if (isset($_POST['submit'])) {
    // checks to see if the used checked year only button
    $yearOnly = isset($_POST['yearOnly']);

    //inits the values for the item with the inputs of the user
    $item = [
        'title' => trim(htmlspecialchars($_POST['title'])),
        'type' => trim(htmlspecialchars($_POST['type'])),
        'published_date' => trim(htmlspecialchars($_POST['published_date'])),
        'authors' => explode(',', trim(preg_replace('/\s\s+/', ' ', trim(htmlspecialchars($_POST['authors']))))),
        'subjects' => explode(',', trim(preg_replace('/\s\s+/', ' ', trim(htmlspecialchars($_POST['subjects']))))),
        'description' => trim(htmlspecialchars($_POST['description'])),
        'metadata' => trim(htmlspecialchars($_POST['metadata'])),
        'image' => '',
        'files' => array(),
    ];

    //validates that the title is not empty
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
    } elseif (!preg_match('/^[0-9]*-[0-9]*-[0-9]*$/', $item['published_date'])) {
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
    if (isset($_POST['image']) && !empty($_POST['image'])) {
        $image_type = 'image/jpeg';
        $item['image'] = base64_decode(str_replace("data:$image_type;base64,", '', $_POST['image']));
        $image_size = strlen($item['image']);
    } else {
        $item['image'] = NULL;
    }
    //image end

    if (isset($_POST['number-of-files'])) {
        $file_count = intval($_POST['number-of-files']);
        if ($file_count > 0) {
            $file_names = [];

            for ($i = 1; $i <= $file_count; $i++) {
                $file_names[] = "file-{$i}";
            }

            $tmpPath = $name = $size = $type = '';
            for ($i = 0; $i < count($file_names); $i++) {
                //Get the temp file path
                $tmpFilePath = $_FILES[$file_names[$i]]['tmp_name'];
                $name = $_FILES[$file_names[$i]]['name'];
                $size = $_FILES[$file_names[$i]]['size'];
                $type = $_FILES[$file_names[$i]]['type'];
                //Make sure we have a file path
                if (!empty($tmpFilePath)) {
                    $item['files'][] = [
                        'file_name' => $name,
                        'tmp_path' => $tmpFilePath,
                        'size' => $size,
                        'type' => $type,
                        'file' => $tmpFilePath
                    ];

                } else {
                    echo 'Empty file path ' . ($i + 1);
                    echo '<hr>';
                }
            }
        } else {
            die('Number of files is less than zero');
        }
    } else {
        die('Number of files is not set, provide an input with the name of number-of-files.');
    }

    if (array_filter($errors)) {
        echo showWarn('Error:', 'Errores se detectaron en la forma.');
    } else {
        $sql_errors = ['item' => '', 'authors' => '', 'type' => '', 'image' => '', 'files' => ''];

        if (isset($conn)) {
            $upload_item = [
                'title' => mysqli_real_escape_string($conn, $item['title']),
                'type' => mysqli_real_escape_string($conn, $item['type']),
                'published_date' => mysqli_real_escape_string($conn, $item['published_date']),
                'authors' => explode(',', trim(preg_replace('/\s\s+/', ' ', mysqli_real_escape_string($conn, $_POST['authors'])))),
                'subjects' => explode(',', trim(preg_replace('/\s\s+/', ' ', mysqli_real_escape_string($conn, $_POST['subjects'])))),
                'description' => mysqli_real_escape_string($conn, $item['description']),
                'metadata' => mysqli_real_escape_string($conn, $item['metadata'])
            ];
        } else die("Connection to database has not been established");

        $error = "";

        echo '<hr>INSERT TYPE START<br>';
        //insert type
        $sql_type = "INSERT INTO `type` (`type`) VALUES('{$upload_item['type']}')";
        $type_id = NULL;
        if (mysqli_query($conn, $sql_type)) {
            $type_id = mysqli_insert_id($conn);
            echo htmlspecialchars('success type ' . $upload_item['type'] . ' id: ' . $type_id) . '<br />';
        } else {
            $error = mysqli_error($conn);
            if (strpos($error, 'Duplicate entry') === false) {
                $sql_errors['type'] = 'Unexpected Insertion SQL ERROR (CONTACT A DEVELOPER): ' . $error . '<br />';
            } else {
                $qt = query("Select id from `type` where `type` = '{$upload_item['type']}'");
                if (count($qt) > 0) {
                    $type_id = $qt[0]['id'];
                    echo htmlspecialchars('type already exists ' . $upload_item['type'] . ' id: ' . $type_id) . '<br />';
                }
            }
        }
        echo 'INSERT TYPE END';

        echo '<hr />';

        echo '<hr>INSERT AUTHORS START<br>';
        //insert author (multi)
        $sql_author = "INSERT INTO `author` (`author_name`) VALUES";
        $author_ids = array();
        foreach ($upload_item['authors'] as $author) {
            if (mysqli_query($conn, $sql_author . "('$author')")) {
                $id = mysqli_insert_id($conn);
                $author_ids[] = $id;
                echo htmlspecialchars('success author ' . $author . ' id: ' . $id) . ' <br />';
            } else {
                $error = mysqli_error($conn);
                if (strpos($error, 'Duplicate entry') === false) {
                    $sql_errors['authors'] = 'Unexpected Insertion SQL ERROR (CONTACT A DEVELOPER): ' . $error . '<br />';
                } else {
                    $qt = query("Select id from `author` where `author_name` = '$author'");
                    if (count($qt) > 0) {
                        $id = $qt[0]['id'];
                        $author_ids[] = $id;
                        echo htmlspecialchars('author already exists ' . $author . ' id: ' . $id) . '<br />';
                    }
                }
            }
        }
        echo 'INSERT AUTHORS END';

        if (count($author_ids) <= 0) $author_ids = NULL;
        echo '<hr />';

        echo '<hr>INSERT IMAGE START<br>';
        //insert image
        $data = $name = $id = $q = $img_id = null;
        if (isset($item['image']) && !empty($item['image']) && $item['image'] !== NULL) {
            $data = mysqli_real_escape_string($conn, $item['image']);
            $sql_image = "INSERT INTO `image` (`type`, `size`, `image`) VALUES ('$image_type', $image_size, '$data')";
            $q = mysqli_query($conn, $sql_image);
            if (isset($q)) {
                $img_id = mysqli_insert_id($conn);
                echo htmlspecialchars('success image : ' . $name . ' id: ' . $img_id) . '<br />';
            } else {
                $error = mysqli_error($conn);
                echo htmlspecialchars($error);
                $sql_errors['image'] = 'Unexpected error ' . $error;
            }
        }
        echo '<hr /> image id: ' . htmlspecialchars($img_id) . '<hr/>';
        echo 'INSERT IMAGE END<hr>';

        echo '<hr>INSERT SUBJECTS START<br>';
        //insert subject (multi)
        $sql_subject = "INSERT INTO `subject` (`subject`) VALUES";
        $subject_ids = array();
        foreach ($upload_item['subjects'] as $subject) {
            if (mysqli_query($conn, $sql_subject . "('$subject')")) {
                $id = mysqli_insert_id($conn);
                $subject_ids[] = $id;
                echo htmlspecialchars('success subject ' . $subject . ' id: ' . $id) . '<br />';
            } else {
                $error = mysqli_error($conn);
                if (strpos($error, 'Duplicate entry') === false) {
                    $sql_errors['subjects'] = 'Unexpected Insertion SQL ERROR (CONTACT A DEVELOPER): ' . $error . '<br />';
                } else {
                    $qt = query("Select id from `subject` where `subject` = '$subject'");
                    if (count($qt) > 0) {
                        $id = $qt[0]['id'];
                        $author_ids[] = $id;
                        echo htmlspecialchars('subject already exists ' . $author . ' id: ' . $id) . '<br />';
                    }
                }
            }
        }
        echo 'INSERT SUBJECT END';
        echo  '<hr/>';

        echo 'INSERT ITEM START<br>';
        //insert item
        $sql_item = "INSERT INTO `item` (`title`, `type_id`, `image_id`, `published_date`, `year_only`, `description`, `meta`) VALUES ";
        $title = $upload_item['title'];
        $des = $upload_item['description'];
        $meta = $upload_item['metadata'];
        $date = $upload_item['published_date'];
        $img = $img_id === NULL ? 'null' : $img_id;
        $q = mysqli_query($conn, $sql_item . "('$title', $type_id, $img, '$date', '$yearOnly', '$des', '$meta')");
        $item_id = NULL;
        if (isset($q)) {
            $id = mysqli_insert_id($conn);
            $item_id = $id;
            echo htmlspecialchars('success item ' . $title . ' id: ' . $id) . '<br />';
            echo mysqli_error($conn);
        } else {
            $error = mysqli_error($conn);
            $sql_errors['item'] = $error;
            echo '<b>' . htmlspecialchars($error) . '</b>';
        }

        echo 'INSERT ITEM END';

        echo '<hr>INSERT FILE START<br>';

        // insert paths
        $file_ids = array();
        if (count($item['files']) > 0) {
            $insert_id = -1;
            foreach ($item['files'] as $file) {
                $insert_id = SQL_SEND_LONG_BLOB($file);
                $file_ids[] = $insert_id;
                query(SQL_FILE_INSERT($insert_id, $file));
            }
        } else {
            $sql_errors['files'] = "No files were submitted";
        }

        echo '<hr>';
        echo 'file ids: ';
        print_r($file_ids);
        echo '<hr>';

        echo 'INSERT FILE END<hr/>';

        echo '<hr>INSERT ITEM_HAS_SUBJECT START<br>';
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
        echo 'INSERT ITEM_HAS_SUBJECT END<hr>';
        $error = "";
        echo '<hr>INSERT FILE_HAS_ITEM START<br>';
        //insert file_has_item
        $sql_file_has_item = "INSERT INTO `file_has_item` (`file_id`, `item_id`) VALUES";
        foreach ($file_ids as $file_id) {
            $q = mysqli_query($conn, $sql_file_has_item . "($file_id, $item_id)");

            if (isset($q)) {
                echo "<br>" . htmlspecialchars("Relation created file [$file_id] -> item [$item_id]") . "<br>";
            } else {
                $error = mysqli_error($conn);

                echo '<b>' . htmlspecialchars($error) . '</b>';
            }
        }
        echo htmlspecialchars('File has item sql error: ' . $error);
        echo '<hr>INSERT FILE_HAS_ITEM END<hr>';

        echo '<hr>INSERT AUTHOR_HAS_ITEM START<br>';
        //insert author_has_item
        $sql_author_has_item = "INSERT INTO `author_has_item` (`item_id`, `author_id`) VALUES";
        foreach ($author_ids as $author_id) {
            $q = mysqli_query($conn, $sql_author_has_item . "($item_id, $author_id)");

            if (isset($q)) {
                echo "<br>" . htmlspecialchars("Relation created author [$author_id] -> item [$item_id]") . "<br>";
            } else {
                $error = mysqli_error($conn);
                echo '<b>' . htmlspecialchars($error) . '</b>';
            }
        }
        echo 'INSERT AUTHOR_HAS_ITEM END<hr>';

        echo '<hr>SQL ERROR CHECK START<br>';
        if (array_filter($errors)) {
            echo showWarn('SQL ERROR:', 'There were unexpected insertion errors. Last error: ' . $error);
            print_r($sql_errors);
        } else {
            //mysqli_close($conn);
            header("Location: index.php#$item_id");
        }
        echo 'SQL ERROR CHECK END<hr>';
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

include_once('templates/header.php');

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
            <input type="date" name="published_date" id="published_date" class="form-control <?php not_valid_class($valid_date); ?>" value="<?php echo $item['published_date']; ?>" required>
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
        <textarea class="form-control <?php not_valid_class($valid_description); ?>" id="description" name="description" aria-describedby="descriptionHelp" rows="3" required><?php echo $item['description']; ?></textarea>
        <small id="descriptionHelp" class="form-text text-muted">Presione control y enter (<code>CTRL+ENTER</code>) para una nueva línea donde esta el cursor</small>
        <?php echo_invalid_feedback(!$valid_description, $errors['description']); ?>
    </div>

    <!-- METADATA -->
    <div class="form-group">
        <label for="metadata">Metadata
            <?php hint('Información que no será visible, pero que se utilizará en la búsqueda (por ejemplo, texto completo del artículo, otros títulos, etc.). En otras palabras, cualquier información relacionada con el artículo.'); ?>
        </label>
        <textarea class="form-control" id="metadata" name="metadata" rows="3" aria-describedby="metaHelp"><?php echo $item['metadata']; ?></textarea>
        <small id="metaHelp" class="form-text text-muted">Presione control y enter (<code>CTRL+ENTER</code>) para una nueva línea donde esta el cursor</small>
    </div>

    <hr />
    <!-- IMAGE -->
    <div class="form-row">
        <label for="image">Imagen
            <?php hint('La imagen será la primera página del primer documento PDF.'); ?>
        </label>
        <div class="col-xs-1">
            <canvas id="the-canvas" style="display:none;"></canvas>
            <input type="hidden" id='image' name="image" value="">
            <img id="show" class="img-thumbnail rounded" src="" alt="">
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
        <div id="file-view list-group">
            <!-- <input class="btn form-control <?php not_valid_class($valid_files); ?>" type="file" id="files" name="files[]" multiple="multiple" required> -->
            <input type="hidden" value="0" name="number-of-files" id="number-of-files">
            <div class="col-xs-1 text-center">
                <input class="form-control btn <?php not_valid_class($valid_files); ?>" type="file" id="files" multiple="multiple" required>
            </div>
            <?php echo_invalid_feedback(!$valid_files, $errors['files']); ?>
        </div>

    </div>
    <hr />

    <div class="form-row">
        <div class="col-xs-1 container-fluid">
            <p id="file-info"></p>
            <ul id="fileList" class="list-group">
            </ul>
        </div>
    </div>
    <hr>

    <div class="form-row">
        <div class="col-xs-1 container-fluid" id="size-warning"></div>
    </div>

    <button class="btn btn-success" type="submit" name="submit" onclick="allowreload=true;addAllToReadonly('authorInput', 'authors');addAllToReadonly('subjectsInput', 'subjects');">Agregar Artículo</button>
</form>

<script charset="utf-8" src="js/jquery-3.2.1.slim.min.js"></script>
<script src="js/autocomplete.js"></script>
<script>
    /*An array containing all the article types*/
    const types = [
        <?php foreach (query(SQL_GET_DOC_TYPES) as $type) echo '"' . htmlspecialchars($type['type']) . '",'; ?>
    ];
</script>

<script type="text/javascript" src="js/pdf.js"></script>
<script src="js/add.js"></script>

<?php include_once('templates/footer.php'); ?>