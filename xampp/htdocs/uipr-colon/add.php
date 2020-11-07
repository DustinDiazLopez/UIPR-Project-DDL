<?php
$title_tag = 'Añadir un Artículo';
include_once('connect.php');
include_once('utils/utils.php');
authenticate();
set_time_limit(0);

$errors = $item = ['title' => '', 'type' => '', 'published_date' => '', 'authors' => '', 'subjects' => '', 'description' => '', 'metadata' => '', 'files' => ''];
$sql_errors = ['item' => '', 'authors' => '', 'subjects' => '', 'type' => '', 'image' => '', 'files' => '', 'item_has_subject' => '', 'file_has_item' => '', 'author_has_item' => ''];
$valid_title = $valid_type = $valid_date = $valid_authors = $valid_subjects = $valid_description = $valid_image = $valid_files = '';
$warning = false;
$provide_char_msg = 'debe proveer al menos un cáracter (no espacio en blanco).';

function split_clean_array_ddl($str)
{
    $arr = explode(',', $str);
    for ($i = 0; $i < count($arr); $i++) $arr[$i] = htmlspecialchars(trim($arr[$i]));
    $arr = array_unique($arr);
    return count($arr) > 0 ? $arr : NULL;
}

function validate_post_csv ($key, $alt_key, &$is_valid, &$error_buffer)
{
    if (isset($_POST[$key])) {
        $obj = split_clean_array_ddl($_POST[$key]);
        if ($obj === NULL) {
            $is_valid = FALSE;
            $error_buffer[$key] .= "Provee al menos un $alt_key";
        } else {
            return $obj;
        }
    } else {
        $is_valid = FALSE;
        $error_buffer[$key] .= "Provee al menos un $alt_key";
    }
    return NULL;
}


if (isset($_POST['submit'])) {
    /* checks to see if the used checked year only button */
    $yearOnly = isset($_POST['yearOnly']);

    /* VALIDATE START */
    validate_ddl($_POST, 'title', 'título', $valid_title, $errors);
    validate_ddl($_POST, 'type', 'tipo', $valid_type, $errors);
    validate_ddl($_POST, 'published_date', 'fecha de publicación', $valid_date, $errors);
    validate_ddl($_POST, 'authors', 'autores', $valid_date, $errors);
    validate_ddl($_POST, 'subjects', 'sujetos', $valid_date, $errors);
    validate_ddl($_POST, 'description', 'descripción', $valid_date, $errors);

    // no errors are logged for image validation, if something fails it will be ignored.
    $image = validate_ddl_image($_POST, $valid_image);
    $files = validate_files_form_ddl($_FILES, $errors['files']);

    // validate authros and subjects
    $authors = validate_post_csv('authors', 'autor(a)', $valid_authors, $errors);
    $subjects = validate_post_csv('subjects', 'sujeto', $valid_subjects, $errors);

    // validate date
    if ($valid_date) {
        if (!preg_match("/[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]/", $_POST['published_date'])) {
            $errors['published_date'] = 'Favor the proveer una fecha en el formato yyyy-mm-dd';
            $valid_date = false;
        }
    }

    //inits the values for the item with the inputs of the user
    $item = [
        'title' => htmlspecialchars($_POST['title']),
        'type' => htmlspecialchars($_POST['type']),
        'published_date' => htmlspecialchars($_POST['published_date']),
        'authors' => $authors,
        'subjects' => $subjects,
        'description' => trim(htmlspecialchars($_POST['description'])),
        'metadata' => trim(htmlspecialchars($_POST['metadata'])),
        'image' => $image,
        'files' => $files,
    ];


    if (array_filter($errors)) {
        print_r($errors);
        echo showWarn('Error:', 'Errores se detectaron en la forma.');
    } else {
        if (isset($conn)) {

            $upload_item = [
                'title' => mysqli_real_escape_string($conn, $item['title']),
                'type' => mysqli_real_escape_string($conn, $item['type']),
                'published_date' => mysqli_real_escape_string($conn, $item['published_date']),
                'authors' => array_unique(explode(',', trim(preg_replace('/\s\s+/', ' ', mysqli_real_escape_string($conn, $_POST['authors']))))),
                'subjects' => array_unique(explode(',', trim(preg_replace('/\s\s+/', ' ', mysqli_real_escape_string($conn, $_POST['subjects']))))),
                'description' => mysqli_real_escape_string($conn, $item['description']),
                'metadata' => mysqli_real_escape_string($conn, $item['metadata'])
            ];

            /*********/

            /* INSERT TYPE START */

            // tries to insert the type or get back the existing type (hence the fallback)
            $type_id = INSERT(
                SQL_INSERT_TYPE($upload_item['type']),
                SQL_GET_ID_OF_TYPE_BY_TYPE($upload_item['type']),
                $sql_errors['type']
            );


            if ($type_id === FALSE) {
                echo "<hr>Type: $type_id<br>Err: {$sql_errors['type']}<br>";
            }
            echo "<hr>Type: $type_id<br>Err: {$sql_errors['type']}<br>";
            /* INSERT TYPE END */

            /*********/

            /* INSERT IMAGE START */
            $image_id = "NULL";
            if ($valid_image === TRUE) {
                $image_id = INSERT(
                    SQL_INSERT_IMAGE(
                            $image['image_type'], $image['image_size'],
                            mysqli_real_escape_string($conn, $image['content'])
                    ),
                    '',
                    $sql_errors['image']
                );
            }

            $image_id = is_int($image_id) ? $image_id : "NULL";

            /* INSERT IMAGE END */

            /*********/

            /* INSERT ITEM START */
            $item_id = INSERT(
                SQL_INSERT_ITEM(
                        $upload_item['title'], $type_id, $image_id, $upload_item['published_date'], $yearOnly,
                        $upload_item['description'], $upload_item['metadata']
                ),
                '',
                $sql_errors['item']
            );

            echo "<hr>Item: $item_id<br>Err: {$sql_errors['item']}<br>";
            /* INSERT ITEM END */

            /*********/

            /* INSERT FILES START */
            $file_ids = array();
            $num_of_files = count($files);
            foreach ($files as $file) {
                // sends the file piece by piece
                $insert_id = SQL_SEND_LONG_BLOB($file, $sql_errors['files']);
                if ($insert_id !== NULL) {
                    // adds the inserted id to the list of ids
                    $file_ids[] = $insert_id;
                    // updates the inserted file information to match the inputted file object.
                    query(SQL_FILE_INSERT($insert_id, $file));
                } else {
                    error_log('FAILED TO UPLOAD FILE: ' . $file['file_name']);
                    error_log('Possible cause: ' . $sql_errors['files']);
                }
            }


            /* INSERT FILES END */

            /*********/

            /* INSERT FILES_HAS_ITEM START */
            foreach ($file_ids as $id) {
                $ihs = INSERT(
                    SQL_INSERT_FILE_HAS_ITEM($id, $item_id),
                    '',
                    $sql_errors['file_has_item']
                );
            }
            /* INSERT FILES_HAS_ITEM END */

            /*********/

            /* INSERT AUTHORS START */
            $author_ids = array();
            foreach ($upload_item['authors'] as $author) {
                // tries to insert author or get back the existing author
                $id = INSERT(
                    SQL_INSERT_AUTHOR($author),
                    SQL_GET_ID_OF_AUTHOR_BY_AUTHOR_NAME($author),
                    $sql_errors['authors']
                );

                // if it is an int add to the list
                if (is_int(intval($id))) {
                    $author_ids[] = $id;
                }
            }

            print_r($author_ids);
            echo 'Err: ' . $sql_errors['authors'];
            /* INSERT AUTHORS END */

            /*********/

            /* INSERT SUBJECTS START */
            $subject_ids = array();
            foreach ($upload_item['subjects'] as $subject) {
                $id = INSERT(
                    SQL_INSERT_SUBJECT($subject),
                    SQL_GET_ID_OF_SUBJECT_BY_SUBJECT($subject),
                    $sql_errors['subjects']
                );

                if (is_int($id)) {
                    $subject_ids[] = $id;
                }
            }

            print_r($author_ids);
            echo 'Err: ' . $sql_errors['authors'];
            /* INSERT SUBJECTS END */

            /*********/

            /* INSERT AUTHORS_HAS_ITEM START */
            foreach ($author_ids as $id) {
                $ihs = INSERT(
                    SQL_INSERT_AUTHOR_HAS_ITEM($item_id, $id),
                    '',
                    $sql_errors['author_has_item']
                );
            }
            /* INSERT AUTHORS_HAS_ITEM END */

            /*********/

            /* INSERT ITEM_HAS_SUBJECTS START */

            foreach ($subject_ids as $id) {
                $ihs = INSERT(
                    SQL_INSERT_ITEM_HAS_SUBJECT($item_id, $id),
                    '',
                    $sql_errors['item_has_subject']
                );
            }

            /* INSERT ITEM_HAS_SUBJECTS END */

            echo '<hr>SQL ERROR CHECK START<br>';
            if (array_filter($sql_errors)) {
                echo showWarn('SQL ERROR:', 'There were unexpected insertion errors.');
            } else {
                //mysqli_close($conn);
                header("Location: index.php#$item_id");
            }
            echo 'SQL ERROR CHECK END<hr>';
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

include_once('templates/header.php');

?>

<link rel="stylesheet" href="css/autocomplete.css">

<style>
    .floatCenter {
        position: fixed;
        top: 50%;
        left: 50%;
        margin-top: -50px;
        margin-left: -50px;
        width: 100%;
        height: 100%;
    }

    #overlay {
        position: fixed;
        display: none;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(255,255,255,0.5);
        z-index: 2;
        cursor: pointer;
    }

    #stick-top {
        position: fixed;
        margin: 10px;
        z-index: 99;
        bottom: 20px;
        right: 30px;

    }

    #stick-top span {
        position: absolute;
        margin-top: 10px;
        margin-right: 15px;
        top: 0;
        right: 0;
    }

    .close-progress {
        background: white;
        color: gray;
    }

    .close-progress:hover {
        background: gray;
        color: white;
    }

    .hover-times {
        background: white;
        color: gray;
    }

    .hover-times:hover {
        background: gray;
        color: white;
    }

    .hover-times:active {
        background: gray;
        color: red;
    }

</style>

<!-- PROGRESS CARD START -->
<div class="card" id="stick-top" style="width: 18rem;">
    <span class="badge badge-dark badge-pill close-progress" id="close-btn-progress"
          style="display: none;" onclick="document.getElementById('stick-top').style.display = 'none';">
        <i class="fas fa-times"></i>
    </span>
    <div class="card-body">
        <h5 class="card-title" id="progress-heading">Completar</h5>
        <p class="card-text" id="progress-msg">Favor de completar la forma</p>
    </div>
    <ul class="list-group list-group-flush">
        <li class="list-group-item list-group-item-danger" id="progress-title">Título</li>
        <li class="list-group-item list-group-item-danger" id="progress-type">Tipo</li>
        <li class="list-group-item list-group-item-danger" id="progress-date">Fecha de Publicación</li>
        <li class="list-group-item list-group-item-danger" id="progress-author">Autores</li>
        <li class="list-group-item list-group-item-danger" id="progress-subject">Sujetos</li>
        <li class="list-group-item list-group-item-danger" id="progress-description">Descripción del artículo</li>
        <li class="list-group-item list-group-item-danger" id="progress-files">Archivos</li>
    </ul>
</div>

<!-- PROGRESS CARD END -->

<div class="container-fluid">
    <form autocomplete="off" style="color:black;" action="#" method="POST" enctype="multipart/form-data" id="form">
        <div class="form-row" style="text-align: center;">
            <h1>Añadir un Artículo</h1>
        </div>

        <!-- COL 1 START -->
        <div>
            <div>
                <!-- TITLE AND TYPE -->
                <div class="form-row">
                    <div class="col-md-7 mb-3">
                        <label for="title">Título</label>
                        <input type="text" id="title" name="title" title="Título del artículo." placeholder="Don Quijote de la Mancha" class="form-control <?php not_valid_class($valid_title); ?>" value="<?php echo $item['title']; ?>" required>
                        <?php echo_invalid_feedback(!$valid_title, $errors['title']); ?>
                    </div>
                    <div class="col-md-5 mb-3 autocomplete">
                        <label for="type"><i id="iconShowType" class=""></i> Tipo
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
                        <input type="text" id="type" name="type" placeholder="<?php echo $str; ?>" title="Por ejemplo, <?php echo $str; ?>" class="form-control <?php not_valid_class($valid_type); ?>" value="<?php echo $item['type']; ?>" oninput="changeIcon(this, document.getElementById('iconShowType'))" required>
                        <?php echo_invalid_feedback(!$valid_type, $errors['type']); ?>

                    </div>
                </div>


                <!-- PUB DATE -->
                <div class="form-row">
                    <label for="published_date" class="col-5 col-form-label" id='pub-date-label'>Fecha de Publicación:</label>
                    <div class="col-7 input-group">
                        <div class="input-group-prepend">
                            <div class="input-group-text">
                                <input name="yearOnly" id="yearOnly" onclick="changePubDateToYear('pub-date-label')" type="checkbox" aria-label="Checkbox for para demostrar en el articulo el año de publicacion solamente." title="Sólo enseñar el año">
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
                        <?php //hint('Favor no utilizar commas, especificar el nombre completo sin commas. Si se detectan commas, se eliminarán.'); ?>
                    </label>
                    <div class="input-group mb-3">
                        <ul class="list-group container-fluid" id="readOnlyListViewAuthor">

                        </ul>
                    </div>
                    <div class="input-group mb-3">
                        <input class="form-control <?php not_valid_class($valid_authors); ?>" type="text" placeholder="" id="authors" name="authors" value="<?php
                        if ($item['authors'] !== '') {
                            echo listToCSV($item['authors']);
                        }
                        ?>" readonly required>
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" onclick="deleteReadonly('authorInput', 'authors');parseReadonlyAuthors();" title="Editar todos los autores."><i class="fas fa-users-cog"></i></button>
                            <button class="btn btn-outline-secondary" type="button" onclick="deleteLastReadonly('authorInput', 'authors');parseReadonlyAuthors();" title="Editar el último autor(a) entrado(a)."><i class="fas fa-user-cog"></i></button>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control <?php not_valid_class($valid_authors); ?>" placeholder="Miguel de Cervante" aria-label="Nombre del autor" id="authorInput">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" onclick="addAllToReadonly('authorInput', 'authors');parseReadonlyAuthors();" title="Añadir todos los autores separados por commas (CSV)"><i class="fas fa-users"></i></button>
                            <button class="btn btn-outline-secondary" type="button" onclick="addToReadonly('authorInput', 'authors');parseReadonlyAuthors();" title="Añadir autor"><i class="fas fa-user-plus"></i></button>
                        </div>
                        <?php echo_invalid_feedback(!$valid_authors, $errors['authors']); ?>
                    </div>
                </div>
                <hr />

                <!-- SUBJECTS -->
                <div class="form-row">
                    <label for="subjects">Sujetos
                        <?php //hint('Favor no utilizar commas, especificar el sujeto sin commas. Si se detectan commas, se eliminarán.'); ?>
                    </label>
                    <div class="input-group mb-3">
                        <ul class="list-group container-fluid" id="readOnlyListViewSubject">

                        </ul>
                    </div>
                    <div class="input-group mb-3">
                        <input class="form-control <?php not_valid_class($valid_subjects); ?>" type="text" placeholder="" id="subjects" name="subjects" value="<?php
                        if ($item['subjects'] !== '') {
                            echo listToCSV($item['subjects']);
                        }
                        ?>" readonly required>
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" onclick="deleteReadonly('subjectsInput', 'subjects');parseReadonlySubject();" title="Editar todos los sujetos."><i class="fas fa-cogs"></i></button>
                            <button class="btn btn-outline-secondary" type="button" onclick="deleteLastReadonly('subjectsInput', 'subjects');parseReadonlySubject();" title="Editar el último sujeto entrado."><i class="fas fa-cog"></i></button>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control <?php not_valid_class($valid_subjects); ?>" placeholder="Caballerias" aria-label="Sujetos del articulo" id="subjectsInput">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" onclick="addAllToReadonly('subjectsInput', 'subjects');parseReadonlySubject();" title="Añadir todos loss sujetos separados por commas (CSV)"><i class="fas fa-project-diagram"></i></button>
                            <button class="btn btn-outline-secondary" type="button" onclick="addToReadonly('subjectsInput', 'subjects');parseReadonlySubject();" title="Añadir sujeto"><i class="fab fa-hive"></i></button>
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
            </div>
            <!-- COL 1 END -->

            <hr />

            <!-- COL 2 START -->
            <div >

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
                        <input type="hidden" value="0" name="number-of-files" id="number-of-files" style="overflow: hidden;">
                        <div class="col-xs-1 text-center">
                            <input class="form-control btn <?php not_valid_class($valid_files); ?>" type="file" id="files" multiple="multiple" accept=".pdf" required>
                        </div>
                        <?php echo_invalid_feedback(!$valid_files, $errors['files']); ?>

                    </div>


                    <div class="form-row">
                        <div class="col-xs-1 container-fluid">
                            <hr />
                            <p id="file-info"></p>
                            <ul id="fileList" class="list-group">
                            </ul>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-xs-1 container-fluid" id="size-warning"></div>
                    </div>
                </div>
                <!-- FILES END -->
                <hr />
                <!-- IMAGE -->
                <div class="form-row">
                    <label for="image">Imágen
                        <?php hint('La imagen será la primera página del primer documento PDF.'); ?>
                    </label>
                    <div class="form-row">
                        <small id="customImage" class="form-text text-muted">
                            Déjelo en blanco si desea utilizar una página del archivo. Si no aparece la imagen
                            adecuada, recorra las páginas para restablecerla.
                        </small>
                        <input type="file" id="customImage" onchange="insertCustomImage(this)" accept="image/*" style="overflow: hidden;">

                    </div>

                    <div class="col-xs-1">
                        <canvas id="the-canvas" style="display:none;"></canvas>
                        <input type="hidden" id='image' name="image" value="">

                        <img id="show" class="img-thumbnail rounded" src="images/pdf-placeholder.jpg" alt="">
                    </div>
                </div>
                <div class="form-row" style="padding-top: 10px;">
                    <div class="input-group">
                        <div class="input-group-append">
                            <label class="input-group-text" for="selectedFileInput">Archivo</label>
                        </div>
                        <select class="custom-select" id="selectedFileInput"
                                onchange="changeImage(
                                            document.getElementById('selectedFileInput').value,
                                            document.getElementById('pageNumber').value,
                                            true
                                        );">

                        </select>
                        <div class="input-group-append">
                            <label class="input-group-text" for="pageNumber">Página #</label>
                        </div>
                        <input type="number" class="form-control" min="1" value="1" id="pageNumber" onchange="changeImage(
                                            document.getElementById('selectedFileInput').value,
                                            document.getElementById('pageNumber').value
                                        );" >



                        <div class="input-group-append">
                            <button class="btn btn-outline-danger" type="button"
                                    onclick="clearImage();">
                                <i class="far fa-trash-alt"></i>
                            </button>
                        </div>

                    </div>
                </div>
                <!-- IMAGE END -->

                <hr />
                <div class="form-row">
                    <!-- METADATA -->
                    <div class="form-group">
                        <label for="metadata">Metadata
                            <?php hint('Información que no será visible, pero que se utilizará en la búsqueda (por ejemplo, texto completo del artículo, otros títulos, etc.). En otras palabras, cualquier información relacionada con el artículo.'); ?>
                        </label>
                        <textarea class="form-control" id="metadata" name="metadata" rows="3" aria-describedby="metaHelp"><?php echo $item['metadata']; ?></textarea>
                        <small id="metaHelp" class="form-text text-muted">Presione control y enter (<code>CTRL+ENTER</code>) para una nueva línea donde esta el cursor</small>
                    </div>
                </div>
            </div>
            <!-- COL 2 END -->
        </div>

        <hr>

        <button class="btn btn-success" type="submit" name="submit" id="submitButton" style="width:100%;height:auto;" onclick="allowreload=true;addAllToReadonly('authorInput', 'authors');addAllToReadonly('subjectsInput', 'subjects');" disabled>Agregar Artículo</button>
    </form>
</div>

<div id="overlay">
    <div class="floatCenter" id="loading-splash">
        <object data="images/processing.svg" type="image/svg+xml">
            <img alt="procesando" src="images/processing.gif"/>
        </object>
    </div>
</div>

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

