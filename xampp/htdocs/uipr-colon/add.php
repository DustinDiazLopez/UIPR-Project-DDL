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
        if (!validateDate($_POST['published_date'])) {
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

    $form_errors = array_filter($errors);
    // if ^ the user will be notified after the inclusion of the header

    if (!$form_errors) {
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

            /*********/

            /* SQL ERROR CHECK START */
            $errors_present = array_filter($sql_errors);

            // redirect on no errors
            if (!$errors_present) {
                $t = json_decode($item['title']);
                header("Location: index.php#$item_id?created=$t}]");
            } // esleif ->>>> after the include header the errors will appear.

            // ...
            /* SQL ERROR CHECK END */
        }
    }
}

include_once('templates/header.php');

if (isset($form_errors) && $form_errors) {
    echo showWarn('Error:', 'Errores se detectaron en la forma.');
}

if (isset($errors_present) && $errors_present) {

    echo showDanger('SQL ERROR:', "There were unexpected insertion errors");

    // check if it was a upload file error
    if (!empty($sql_errors['files'])) {
        echo showDanger('SQL UPLOAD ERROR:', 'Error uploading files! Due to: ' . $sql_errors['files']);
        // remove it from the list
        unset($sql_errors['files']);
    }

    $inserted = TRUE;
    // check if any other error exists
    if (array_filter($sql_errors)) {
        $keys = array_keys($sql_errors);
        foreach ($keys as $key) {
            if ($key === 'item') $inserted = FALSE;
            $err = trim($sql_errors[$key]);
            if (!empty($err)) {
                echo showWarn("Insert $key Error:", $err);
            }
        }

        if (!$inserted) {

            echo showWarn('Important: ', 'The item was created)');
        }
    }

}

?>

<link rel="stylesheet" href="css/autocomplete.css">
<link rel="stylesheet" href="css/add.css">

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
        <li class="list-group-item list-group-item-danger" id="progress-date">Fecha de Publicación</li>
        <li class="list-group-item list-group-item-danger" id="progress-description">Descripción del artículo</li>
        <li class="list-group-item list-group-item-danger" id="progress-author">Autores</li>
        <li class="list-group-item list-group-item-danger" id="progress-subject">Sujetos</li>
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
                        <label for="type"><span id="iconShowType"></span> Tipo
                            <?php
                            hint('Si desea un tipo no existente lo puede añadir a traves del Panel del Administrador > Data > Tipos');
                            ?>

                        </label>
                        <select class="custom-select" id="type" name="type">
                            <?php
                            $types = query(SQL_GET_DOC_TYPES);
                            foreach ($types as $type) {
                                echo "<option value=\"{$type['type']}\"><span>{$type['type']}</span></option>";
                            }

                            ?>
                        </select>


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

                <!-- DESCRIPTION -->
                <div class="form-group">
                    <label for="description">Descripción del artículo</label>
                    <textarea class="form-control <?php not_valid_class($valid_description); ?>" id="description" name="description" aria-describedby="descriptionHelp" rows="3" required><?php echo $item['description']; ?></textarea>
                    <small id="descriptionHelp" class="form-text text-muted">Presione control y enter (<code>CTRL+ENTER</code>) para una nueva línea donde esta el cursor</small>
                    <?php echo_invalid_feedback(!$valid_description, $errors['description']); ?>
                </div>
                <hr />
                <div class="form-group">
                    <label for="metadata">Metadata
                        <?php hint('Información que no será visible, pero que se utilizará en la búsqueda (por ejemplo, texto completo del artículo, otros títulos, etc.). En otras palabras, cualquier información relacionada con el artículo.'); ?>
                    </label>
                    <textarea class="form-control" id="metadata" name="metadata" rows="3" aria-describedby="metaHelp"><?php echo $item['metadata']; ?></textarea>
                    <small id="metaHelp" class="form-text text-muted">Presione control y enter (<code>CTRL+ENTER</code>) para una nueva línea donde esta el cursor</small>
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

            </div>
            <!-- COL 1 END -->

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

            <!-- COL 2 START -->
            <div >
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
            <img loading="lazy" alt="espere mientras procesamos la información" src="images/processing.gif"/>
        </object>
    </div>
</div>

<script>
    const types = [
        <?php foreach (query(SQL_GET_DOC_TYPES) as $type) echo '"' . htmlspecialchars($type['type']) . '",'; ?>
    ];
</script>
<script charset="utf-8" src="js/jquery-3.2.1.slim.min.js"></script>
<script src="js/autocomplete.js"></script>
<script type="text/javascript" src="js/pdf.js"></script>
<script src="js/generic.js"></script>
<script src="js/add.js"></script>

<?php include_once('templates/footer.php'); ?>

