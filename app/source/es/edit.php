<?php
include_once('../connect.php');
include_once('utils/utils.php');

if (isset($_POST['editItem']) && !empty($_POST['editItem'])) {
    header('Location: edit.php?editItem=' . $_POST['editItem']);
}

authenticate();

$errors = ['title' => '', 'type' => '', 'published_date' => '', 'authors' => '', 'subjects' => '', 'description' => '', 'metadata' => '', 'files' => ''];
$valid_title = $valid_type = $valid_date = $valid_authors = $valid_subjects = $valid_description = $valid_image = $valid_files = '';

if ((isset($_POST['editItem']) && !empty($_POST['editItem'])) || (isset($_GET['editItem']) && !empty($_GET['editItem']))) {
    $post_id = isset($_POST['editItem']) ? intval($_POST['editItem']) :  intval($_GET['editItem']);

    if ($post_id > 0) {
        $item = SQL_GET_ITEM_BY_ID_META($post_id);
        if (count($item) > 0) {
            $item = $item[0];
            $item['metadata'] = $item['meta'];

            $item['authors'] = authorsToCSV(SQL_GET_AUTHORS($item['id']));
            $item['subjects'] = authorsToCSV(SQL_GET_SUBJECTS($item['id']), 'subject');

            $image = $files = NULL;
            if (!empty($item['image_id']) || is_int($item['image_id'])) {
                $image = SQL_GET_IMAGE($item['image_id']);
            } else {
                $image = 'images/pdf-placeholder.jpg';
            }

            $item['image'] = $image;

            unset($image);
            unset($item['meta']);

            $files = SQL_GET_FILES($item['id']);
            $orphaned_files = SQL_GET_ORPHANED_FILES();
            $title_tag = $item['title'];

        } else {
            header('Location: index.php?error=no-item');
        }
    } else {
        header('Location: index.php?error=invalid-item-id');
    }
} else {
    header('Location: index.php?error=no-edit');
}

include_once('../connect.php');
include_once('utils/utils.php');
authenticate();
set_time_limit(0);

$errors = ['title' => '', 'type' => '', 'published_date' => '', 'authors' => '', 'subjects' => '', 'description' => '', 'metadata' => '', 'files' => ''];
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
    $files = validate_files_form_ddl($_FILES, $errors['files'], TRUE);

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
    $title_tag = htmlspecialchars($_POST['title']);
    $item = [
        'id' => intval($_POST['item_id']),
        'title' => htmlspecialchars($_POST['title']),
        'type' => htmlspecialchars($_POST['type']),
        'published_date' => htmlspecialchars($_POST['published_date']),
        'year_only' => $yearOnly,
        'authors' => $authors,
        'subjects' => $subjects,
        'description' => trim(cleanHTML($_POST['description'])),
        'metadata' => trim(htmlspecialchars($_POST['metadata'])),
        'image' => $image,
        'files' => $files,
    ];

    $form_errors = array_filter($errors);
    // if ^ the user will be notified after the inclusion of the header

    if (!$form_errors) {
        if (isset($conn)) {

            DELETE_ITEM_AND_RELATIONS($item['id']);

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
                        $image['image_type'],
                        $image['image_size'],
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
                SQL_INSERT_ITEM_W_ID(
                    $item['id'],
                    $upload_item['title'],
                    $type_id,
                    $image_id,
                    $upload_item['published_date'],
                    $yearOnly,
                    $upload_item['description'],
                    $upload_item['metadata']
                ),
                '',
                $sql_errors['item']
            );

            $item_id = $item['id'];

            /* INSERT ITEM END */

            /*********/

            /* INSERT FILES START */

            define('PATH_TO_FILES', PATH_TO_FILES_FOLDER . $item_id . '/');
            $file_ids = array();
            if (mkdir(PATH_TO_FILES) || is_dir(PATH_TO_FILES)) {
                $num_of_files = count($files);
                $target = $path = NULL;
                foreach ($files as $file) {
                    $target = PATH_TO_FILES . $file['file_name'];
                    $file['path'] = escapeMySQL($item_id . '/' . $file['file_name']);

                    $moved = move_uploaded_file($file['tmp_path'], $target);

                    $insert_id = NULL;
                    if ($moved !== FALSE) {
                        query(SQL_INSERT_FILE($file));
                        $insert_id = mysqli_insert_id($conn);
                        $file_ids[] = mysqli_insert_id($conn);
                    } else {
                        $sql_errors['files'] = 'Failed to move file to folder ' . PATH_TO_FILES_FOLDER . ' ' . $item_id;
                        error_log($sql_errors['files']);
                    }
                }
            } else {
                $sql_errors['files'] = 'Failed to create folder in ' . PATH_TO_FILES_FOLDER . ' ' . $item_id;
                error_log($sql_errors['files']);
            }
            /* INSERT FILES END */

            /*********/

            /* GET ORPHANED FILES START */
            if (isset($_POST['orphaned-files']) && !empty(trim($_POST['orphaned-files']))) {
                $oFiles = explode(',', trim($_POST['orphaned-files']));
                $oFilesLen = count($oFiles);
                if ($oFilesLen > 0) {
                    for ($i = 0; $i < $oFilesLen; $i++) {
                        $oFiles[$i] = intval(trim($oFiles[$i]));
                    }
                }

                for ($i = 0; $i < $oFilesLen; $i++) {
                    $file_ids[] = $oFiles[$i];
                }
            }
            /* GET ORPHANED FILES END */

            /*********/

            /* GET EXISTING FILES START */
            if (isset($_POST['existing-files']) && !empty(trim($_POST['existing-files']))) {
                $eFiles = explode(',', trim($_POST['existing-files']));
                $eFilesLen = count($eFiles);
                if ($eFilesLen > 0) {
                    for ($i = 0; $i < $eFilesLen; $i++) {
                        $eFiles[$i] = intval(trim($eFiles[$i]));
                    }
                }

                for ($i = 0; $i < $eFilesLen; $i++) {
                    $file_ids[] = $eFiles[$i];
                }
            }
            /* GET EXISTING FILES END */

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
                header("Location: index.php#$item_id?altered={$item['title']}");
            } // esleif ->>>> after the include header the errors will appear.

            // ...
            /* SQL ERROR CHECK END */

            die(SQL_INSERT_ITEM_W_ID(
                $item['id'],
                $upload_item['title'],
                $type_id,
                $image_id,
                $upload_item['published_date'],
                $yearOnly,
                $upload_item['description'],
                $upload_item['metadata']
            ));
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

    <link rel="stylesheet" href="./../css/add.css">
    <link rel="stylesheet" href="./../css/summernote.min.css">

    <!-- PROGRESS CARD START -->
    <div class="card" id="stick-top" style="width: 18rem;">
    <span class="badge badge-dark badge-pill close-progress" id="close-btn-progress" style="display: none;" onclick="document.getElementById('stick-top').style.display = 'none';">
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
        </ul>
    </div>


    <!-- PROGRESS CARD END -->

    <div class="container-fluid">
        <form autocomplete="off" style="color:black;" action="edit.php" method="POST" enctype="multipart/form-data" id="form">
            <div class="form-row" style="text-align: center;">
                <h1>Editar <?php echo $item['title']; ?></h1>
            </div>

            <!-- COL 1 START -->
            <div>
                <div>
                    <!-- TITLE AND TYPE -->
                    <div class="form-row">
                        <input style="display: none;" type="text" id="item_id" name="item_id" value="<?php echo $item['id']; ?>" required readonly aria-disabled="true" aria-hidden="true">
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
                                    echo $item['type'] === $type['type']
                                        ? "<option value=\"{$type['type']}\" selected><span>{$type['type']}</span></option>"
                                        : "<option value=\"{$type['type']}\"><span>{$type['type']}</span></option>";
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
                                    <input name="yearOnly" id="yearOnly"
                                           onclick="changePubDateToYear('pub-date-label')" type="checkbox"
                                           aria-label="Checkbox for para demostrar en el articulo el año de publicacion solamente."
                                           title="Sólo enseñar el año"
                                           <?php echo $item['year_only'] ? 'checked' : ''; ?>
                                    >
                                </div>
                            </div>
                            <input type="date" name="published_date" id="published_date" class="form-control <?php not_valid_class($valid_date); ?>" value="<?php echo $item['published_date']; ?>" required>
                            <?php echo_invalid_feedback(!$valid_date, $errors['published_date']); ?>
                        </div>

                    </div>

                    <hr />

                    <!-- DESCRIPTION -->
                    <div class="form-group">
                        <label for="description">Descripción</label>
                        <textarea width="1000px" class="form-control <?php not_valid_class($valid_description); ?>" id="description" name="description" aria-describedby="descriptionHelp" rows="3" required><?php echo $item['description']; ?></textarea>
                        <?php echo_invalid_feedback(!$valid_description, $errors['description']); ?>
                    </div>

                    <hr />

                    <!-- METADATA -->
                    <div class="form-group">
                        <label for="metadata">Metadata
                            <?php hint('Información que no será visible, pero que se utilizará en la búsqueda (por ejemplo, texto completo del artículo, otros títulos, etc.). En otras palabras, cualquier información relacionada con el artículo.'); ?>
                        </label>
                        <textarea class="form-control" id="metadata" name="metadata" rows="3" aria-describedby="metaHelp"><?php echo $item['metadata']; ?></textarea>
                    </div>
                    <hr />
                    <!-- AUTHORS -->
                    <div class="form-row">
                        <label for="authors">Autores</label>
                        <div class="input-group mb-3">
                            <ul class="list-group container-fluid" id="readOnlyListViewAuthor">

                            </ul>
                        </div>
                        <div class="input-group mb-3">
                            <input class="form-control <?php not_valid_class($valid_authors); ?>" type="text" placeholder="" id="authors" name="authors" value="<?php
                            echo $item['authors']
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
                        <label for="subjects">Sujetos</label>
                        <div class="input-group mb-3">
                            <ul class="list-group container-fluid" id="readOnlyListViewSubject">

                            </ul>
                        </div>
                        <div class="input-group mb-3">
                            <input class="form-control <?php not_valid_class($valid_subjects); ?>" type="text" placeholder="" id="subjects" name="subjects" value="<?php
                            echo $item['subjects'];
                            ?>" readonly required>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" onclick="deleteReadonly('subjectsInput', 'subjects');parseReadonlySubject();" title="Editar todos los sujetos."><i class="fas fa-cogs"></i></button>
                                <button class="btn btn-outline-secondary" type="button" onclick="deleteLastReadonly('subjectsInput', 'subjects');parseReadonlySubject();" title="Editar el último sujeto entrado."><i class="fas fa-cog"></i></button>
                            </div>
                        </div>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control <?php not_valid_class($valid_subjects); ?>" placeholder="Novela De Aventuras, Caballerías, Novela Realista" aria-label="Sujetos del articulo" id="subjectsInput">
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

                <!-- EXISTING FILES START -->
                <?php
                $e_current_files_count = count($files);
                if ($e_current_files_count > 0) :
                ?>

                    <div class="form-row" style="padding-top: 10px;">
                        <label for="e-files">Quitar archivos
                            <?php hint(
                                "Aquí podrá quitar los archivos existentes para $item[title]"
                            ); ?>
                        </label>

                        <div class="input-group">
                            <div class="input-group-append">
                                <label class="input-group-text" for="selectedFileInput">Seleccionar</label>
                            </div>
                            <select class="custom-select" id="e-files">
                                <?php
                                foreach ($files as $file) {
                                    echo "<option value=\"{$file['id']}\"><span>{$file['filename']}</span></option>";
                                }

                                ?>
                            </select>

                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary"
                                        type="button"
                                        title="Añadir el archivo huérfano seleccionado"
                                        id="add-e-file-btn" onclick="addExistingFile();">
                                    <i class="fas fa-link"></i>
                                </button>
                            </div>

                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-row" style="width: 100%">
                            <div class="col-xs-1 container-fluid">
                                <br>
                                <p id="orphaned-file-info"></p>
                                <ul id="existingFileList" class="list-group">

                                </ul>
                            </div>
                        </div>
                    </div>
                    <br>

                    <div class="form-row" style="display: none">
                        <label for="e-files-selected">Archivos Existentes Seleccionados</label>
                        <input class="form-control" placeholder="" id="e-files-selected" name="existing-files" type="text" readonly>
                    </div>
                <?php endif; ?>
                <!-- EXISTING FILES END -->

                <hr />

                <!-- ORPHANED FILES START -->
                <?php if (count($orphaned_files) > 0) : ?>

                    <div class="form-row" style="padding-top: 10px;">
                        <label for="o-files">Seleccionar los Archivos Huérfanos
                            <?php hint(
                                'Aquí podrá seleccionar los archivos cual no tienen una relación (un archivo huérfano) con 
                        un artículo. Si desea borrar estos archivos vaya al Panel del Administrador > Data > Archivos 
                        Huérfanos'
                            ); ?>
                        </label>

                        <div class="input-group">
                            <div class="input-group-append">
                                <label class="input-group-text" for="selectedFileInput">Seleccionar</label>
                            </div>
                            <select class="custom-select" id="o-files">
                                <?php
                                foreach ($orphaned_files as $oFile) {
                                    echo "<option value=\"{$oFile['id']}\"><span>{$oFile['filename']}</span></option>";
                                }

                                ?>
                            </select>

                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary"
                                        type="button"
                                        title="Añadir el archivo huérfano seleccionado"
                                        id="add-o-file-btn" onclick="addOrphanedFile();">
                                    <i class="fas fa-link"></i>
                                </button>
                            </div>

                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-row" style="width: 100%">
                            <div class="col-xs-1 container-fluid">
                                <br>
                                <p id="orphaned-file-info"></p>
                                <ul id="orphanedFileList" class="list-group">

                                </ul>
                            </div>
                        </div>
                    </div>
                    <br>

                    <div class="form-row" style="display: none">
                        <label for="o-files-selected">Archivos Huérfanos Seleccionados</label>
                        <input class="form-control" placeholder="" id="o-files-selected" name="orphaned-files" type="text" readonly>
                    </div>
                <?php endif; ?>
                <!-- ORPHANED FILES END -->

                <hr />

                <!-- FILES -->
                <div class="form-row">
                    <label for="files">Agregar mas archivos
                        <?php hint(
                            'Puede seleccionar más de un archivo. El máximo tamaño combinado es de 40 megabytes. Este 
                        límite está expuesto por el servidor Favor de referirse al README en los enlaces, la parte de 
                        Configuring PHP & MySLQ (Step 0), en la sección de la configuración recomendada para PHP y 
                        MySQL.'
                        ); ?>
                    </label>

                    <div id="file-view list-group">
                        <input type="hidden" value="0" name="number-of-files" id="number-of-files" style="overflow: hidden;">
                        <div class="col-xs-1 text-center">
                            <input class="form-control btn <?php not_valid_class($valid_files); ?>"
                                   type="file" id="files" multiple="multiple" accept=".pdf">
                        </div>
                        <small class="form-text text-muted">
                            Los archivos van a estar filtrados por PDFs, si quiere otro tipo de archivo tendrá que cambiar
                            el filtro en su explorador de archivos (arriba de los botones Open y Cancel en Windows).
                        </small>
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
                <div>
                    <!-- IMAGE -->
                    <div class="form-row">
                        <label for="image">Cambiar imagen para <b><?php echo $item['title']; ?></b>
                            <?php hint('La imagen será la primera página del primer documento PDF.'); ?>
                        </label>
                        <div class="form-row">
                            <small class="form-text text-muted">
                                Déjelo en blanco si desea utilizar una página del archivo. Si no aparece la imagen
                                adecuada, recorra las páginas para restablecerla.
                            </small>
                            <input type="file" id="customImage" onchange="insertCustomImage(this)" accept="image/*" style="overflow: hidden;">

                        </div>

                        <div class="col-xs-1">
                            <canvas id="the-canvas" style="display:none;"></canvas>
                            <input type="hidden" id='image' name="image" value="<?php echo $item['image']; ?>">

                            <img id="show" class="img-thumbnail rounded" src="<?php echo $item['image']; ?>" alt="">
                        </div>
                    </div>
                    <div class="form-row" style="padding-top: 10px;">
                        <div class="input-group">
                            <div class="input-group-append">
                                <label class="input-group-text" for="selectedFileInput">Archivo</label>
                            </div>
                            <select class="custom-select" id="selectedFileInput" onchange="changeImage(
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
                                        );">



                            <div class="input-group-append">
                                <button class="btn btn-outline-danger" type="button" onclick="clearImage();">
                                    <i class="far fa-trash-alt"></i>
                                    <span class="sr-only">borrar la imagen actual</span>
                                </button>
                            </div>

                        </div>
                    </div>
                    <!-- IMAGE END -->
                </div>
                <!-- COL 2 END -->
            </div>

            <hr>

            <button class="btn btn-success" type="submit" name="submit" id="submitButton" style="width:100%;height:auto;"
                    onclick="allowreload=true;addAllToReadonly('authorInput', 'authors');addAllToReadonly('subjectsInput', 'subjects');" disabled>
                Editar <?php echo $item['title']; ?>
            </button>
        </form>
    </div>

    <div id="overlay">
        <div class="floatCenter" id="loading-splash">
            <object data="images/processing.svg" type="image/svg+xml">
                <img loading="lazy" alt="espere mientras procesamos la información" src="images/processing.gif" />
            </object>
        </div>
    </div>

    <script>
        const types = [
            <?php foreach (query(SQL_GET_DOC_TYPES) as $type) echo '"' . htmlspecialchars($type['type']) . '",'; ?>
        ];
    </script>
    <script charset="utf-8" type="text/javascript" src="./../js/jquery-3.2.1.slim.min.js"></script>
    <script charset="utf-8" type="text/javascript" src="./../js/pdf.js"></script>
    <script charset="utf-8" type="text/javascript" src="./../js/generic.js"></script>
    <script charset="utf-8" type="text/javascript" src="./../js/summernote.min.js"></script>
    <script charset="utf-8" type="text/javascript" src="./../js/textarea.config.js"></script>
    <script charset="utf-8" type="text/javascript" src="js/edit.js"></script>

    <script>
        changePubDateToYear('pub-date-label');
        parseReadonlyAuthors();
        parseReadonlySubject();
        const _eFiles = <?php echo $e_current_files_count; ?>;
        for (let i = 0; i < _eFiles; i++) addExistingFile();
        validate();
        $('#description').summernote({
            placeholder: '<b>Don Quijote de la Mancha</b> es una novela escrita por el <u>español</u> <i>Miguel de Cervantes Saavedra</i>...',
            tabsize: __DDL_TEXTAREA_TAB_SIZE__ ,
            height: __DDL_TEXTAREA_HEIGHT__ ,
            toolbar: __DDL_TEXTAREA_TOOLBAR__
        });
    </script>

<?php include_once('templates/footer.php'); ?>