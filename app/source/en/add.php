<?php
$title_tag = 'Add an Item';
include_once('../connect.php');
include_once('utils/utils.php');
authenticate();
set_time_limit(0);

$orphaned_files = SQL_GET_ORPHANED_FILES();
$errors = $item = ['title' => '', 'type' => '', 'published_date' => '', 'authors' => '', 'subjects' => '', 'description' => '', 'metadata' => '', 'files' => ''];
$sql_errors = ['item' => '', 'authors' => '', 'subjects' => '', 'type' => '', 'image' => '', 'files' => '', 'item_has_subject' => '', 'file_has_item' => '', 'author_has_item' => ''];
$valid_title = $valid_type = $valid_date = $valid_authors = $valid_subjects = $valid_description = $valid_image = $valid_files = '';
$warning = false;
$provide_char_msg = 'you must provide at least one character (not white space).';

if (isset($_POST['submit'])) {
    /* checks to see if the used checked year only button */
    $yearOnly = isset($_POST['yearOnly']);

    $item['year_only'] = $yearOnly;
    /* VALIDATE START */
    validate_ddl($_POST, 'title', 'title', $valid_title, $errors);
    validate_ddl($_POST, 'type', 'type', $valid_type, $errors);
    validate_ddl($_POST, 'published_date', 'published date', $valid_date, $errors);
    validate_ddl($_POST, 'authors', 'authors', $valid_date, $errors);
    validate_ddl($_POST, 'subjects', 'subjects', $valid_date, $errors);
    validate_ddl($_POST, 'description', 'description', $valid_date, $errors);

    // no errors are logged for image validation, if something fails it will be ignored.
    $image = validate_ddl_image($_POST, $valid_image);
    $files = validate_files_form_ddl($_FILES, $errors['files']);

    // validate authors and subjects
    $authors = validate_post_csv('authors', 'author', $valid_authors, $errors);
    $subjects = validate_post_csv('subjects', 'subject', $valid_subjects, $errors);

    // validate date
    if ($valid_date) {
        if (!validateDate($_POST['published_date'])) {
            $errors['published_date'] = 'Please provide a valid date (yyyy-mm-dd)';
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
        'description' => trim(cleanHTML($_POST['description'])),
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
                SQL_INSERT_ITEM(
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
                header("Location: index.php#$item_id?created=$t");
            } // esleif ->>>> after the include header the errors will appear.

            // ...
            /* SQL ERROR CHECK END */
        }
    }
}

include_once('templates/header.php');

if (isset($form_errors) && $form_errors) {
    echo showWarn('Error:', 'Errors were detected in the form.');
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
        <h5 class="card-title" id="progress-heading">To complete</h5>
        <p class="card-text" id="progress-msg">Please complete the following</p>
    </div>
    <ul class="list-group list-group-flush">
        <li class="list-group-item list-group-item-danger" id="progress-title">Title</li>
        <li class="list-group-item list-group-item-danger" id="progress-date">Publication Date</li>
        <li class="list-group-item list-group-item-danger" id="progress-description">Description</li>
        <li class="list-group-item list-group-item-danger" id="progress-author">Authors</li>
        <li class="list-group-item list-group-item-danger" id="progress-subject">Subjects</li>
        <li class="list-group-item list-group-item-danger" id="progress-files">Files</li>
    </ul>
</div>


<!-- PROGRESS CARD END -->

<div class="container-fluid">
    <form autocomplete="off" style="color:black;" action="#" method="POST" enctype="multipart/form-data" id="form">
        <div class="form-row" style="text-align: center;">
            <h1>Add an Item</h1>
        </div>

        <!-- COL 1 START -->
        <div>
            <div>
                <!-- TITLE AND TYPE -->
                <div class="form-row">
                    <div class="col-md-7 mb-3">
                        <label for="title">Title</label>
                        <input type="text" id="title" name="title" title="Title of the item." placeholder="Don Quixote" class="form-control <?php not_valid_class($valid_title); ?>" value="<?php echo $item['title']; ?>" required>
                        <?php echo_invalid_feedback(!$valid_title, $errors['title']); ?>
                    </div>
                    <div class="col-md-5 mb-3 autocomplete">
                        <label for="type"><span id="iconShowType"></span> Type
                            <?php
                            hint('If you want a non-existent type, you can add it through the Admin Panel > Data > Types');
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
                    <label for="published_date" class="col-5 col-form-label" id='pub-date-label'>Publication Date:</label>
                    <div class="col-7 input-group">
                        <div class="input-group-prepend">
                            <div class="input-group-text">
                                <input name="yearOnly" id="yearOnly" onclick="changePubDateToYear('pub-date-label')"
                                       type="checkbox"
                                       aria-label="Checkbox to only show the year of publication."
                                       title="Just show the year"
                                        <?php echo isset($item['year_only']) && $item['year_only'] ? 'checked' : ''; ?>
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
                    <label for="description">Description</label>
                    <textarea width="1000px" class="form-control <?php not_valid_class($valid_description); ?>" id="description" name="description" aria-describedby="descriptionHelp" rows="3" required><?php echo $item['description']; ?></textarea>
                    <?php echo_invalid_feedback(!$valid_description, $errors['description']); ?>
                </div>

                <hr />

                <!-- METADATA -->
                <div class="form-group">
                    <label for="metadata">Metadata
                        <?php hint('Information that will not be visible, but that will be used in the search (e.g., full text of a file, other titles, etc.). In other words, any information related to the article.'); ?>
                    </label>
                    <textarea class="form-control" id="metadata" name="metadata" rows="3" aria-describedby="metaHelp"><?php echo $item['metadata']; ?></textarea>
                </div>
                <hr />
                <!-- AUTHORS -->
                <div class="form-row">
                    <label for="authors">Authors </label>
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
                            <button class="btn btn-outline-secondary" type="button" onclick="deleteReadonly('authorInput', 'authors');parseReadonlyAuthors();" title="Edit all authors."><i class="fas fa-users-cog"></i></button>
                            <button class="btn btn-outline-secondary" type="button" onclick="deleteLastReadonly('authorInput', 'authors');parseReadonlyAuthors();" title="Edit the last author"><i class="fas fa-user-cog"></i></button>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control <?php not_valid_class($valid_authors); ?>" placeholder="Miguel de Cervante" aria-label="Nombre del autor" id="authorInput">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" onclick="addAllToReadonly('authorInput', 'authors');parseReadonlyAuthors();" title="Add all authors separated by commas (CSV)"><i class="fas fa-users"></i></button>
                            <button class="btn btn-outline-secondary" type="button" onclick="addToReadonly('authorInput', 'authors');parseReadonlyAuthors();" title="Add (last) author"><i class="fas fa-user-plus"></i></button>
                        </div>
                        <?php echo_invalid_feedback(!$valid_authors, $errors['authors']); ?>
                    </div>
                </div>

                <hr />

                <!-- SUBJECTS -->
                <div class="form-row">
                    <label for="subjects">Subject</label>
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
                            <button class="btn btn-outline-secondary" type="button" onclick="deleteReadonly('subjectsInput', 'subjects');parseReadonlySubject();" title="Edit all subjects."><i class="fas fa-cogs"></i></button>
                            <button class="btn btn-outline-secondary" type="button" onclick="deleteLastReadonly('subjectsInput', 'subjects');parseReadonlySubject();" title="Edit the last subject."><i class="fas fa-cog"></i></button>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control <?php not_valid_class($valid_subjects); ?>" placeholder="Adventure Novel, Knighthood, Realistic Novel" aria-label="Subjects of the item" id="subjectsInput">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" onclick="addAllToReadonly('subjectsInput', 'subjects');parseReadonlySubject();" title="Add all subjects separated by commas (CSV)"><i class="fas fa-project-diagram"></i></button>
                            <button class="btn btn-outline-secondary" type="button" onclick="addToReadonly('subjectsInput', 'subjects');parseReadonlySubject();" title="Add (last) subject"><i class="fab fa-hive"></i></button>
                        </div>
                        <?php echo_invalid_feedback(!$valid_subjects, $errors['subjects']); ?>
                    </div>
                </div>

            </div>
            <!-- COL 1 END -->

            <hr />

            <!-- ORPHANED FILES START -->
            <?php if (count($orphaned_files) > 0) : ?>

                <div class="form-row" style="padding-top: 10px;">
                    <label for="o-files">Choose Orphaned Files
                        <?php hint(
                            'Here you can select files which do not have a relationship (an orphaned file) with an 
                            article. If you want to delete these files go to the Admin Panel > Data > Orphan Files'
                        ); ?>
                    </label>

                    <div class="input-group">
                        <div class="input-group-append">
                            <label class="input-group-text" for="selectedFileInput">Choose</label>
                        </div>
                        <select class="custom-select" id="o-files">
                            <?php
                            foreach ($orphaned_files as $oFile) {
                                echo "<option value=\"{$oFile['id']}\"><span>{$oFile['filename']}</span></option>";
                            }

                            ?>
                        </select>

                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" title="Add the selected orphaned file" id="add-o-file-btn" onclick="addOrphanedFile();">
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
                    <label for="o-files">Choose the Orphaned Files
                        <?php hint(
                            'Here you can select files which do not have a relationship (an orphaned file). 
                                If you want to delete these files go to the Admin Panel > Data > Orphaned Files'
                        ); ?>
                    </label>
                    <input class="form-control" placeholder="" id="o-files-selected" name="orphaned-files" type="text" readonly>
                </div>
            <?php endif; ?>
            <!-- ORPHANED FILES END -->

            <hr />

            <!-- FILES -->
            <div class="form-row">
                <label for="files">Choose Files
                    <?php hint(
                        'You can select more than one file. The maximum combined size is 40 megabytes (by default). This limit is 
                        set by the server. Please refer to the README in the links, Configuring PHP & MySLQ (Step 0) 
                        section, and view the recommended configuration for PHP (the php.ini).'
                    ); ?>
                </label>

                <div id="file-view list-group">
                    <input type="hidden" value="0" name="number-of-files" id="number-of-files" style="overflow: hidden;">
                    <div class="col-xs-1 text-center">
                        <input class="form-control btn <?php not_valid_class($valid_files); ?>" type="file" id="files" multiple="multiple" accept=".pdf" required>
                    </div>
                    <small class="form-text text-muted">
                        The files will be filtered by PDFs, if you want another type of file you will have to change
                        the filter in your file explorer (e.g., above the Open and Cancel buttons in Windows).
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
                    <label for="image">Image
                        <?php hint('The image will automatically selected from the fist (or lastly added) PDF.'); ?>
                    </label>
                    <div class="form-row">
                        <small class="form-text text-muted">
                            Leave blank if you want to use a page from the uploaded files. If the correct image does
                            not appear, scroll through the pages to reset it.
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
                            <label class="input-group-text" for="selectedFileInput">File</label>
                        </div>
                        <select class="custom-select" id="selectedFileInput" onchange="changeImage(
                                            document.getElementById('selectedFileInput').value,
                                            document.getElementById('pageNumber').value,
                                            true
                                        );">

                        </select>
                        <div class="input-group-append">
                            <label class="input-group-text" for="pageNumber">Page #</label>
                        </div>
                        <input type="number" class="form-control" min="1" value="1" id="pageNumber" onchange="changeImage(
                                            document.getElementById('selectedFileInput').value,
                                            document.getElementById('pageNumber').value
                                        );">



                        <div class="input-group-append">
                            <button class="btn btn-outline-danger" type="button" onclick="clearImage();">
                                <i class="far fa-trash-alt"></i>
                                <span class="sr-only">delete the current image</span>
                            </button>
                        </div>

                    </div>
                </div>
                <!-- IMAGE END -->
            </div>
            <!-- COL 2 END -->
        </div>

        <hr>

        <button class="btn btn-success" type="submit" name="submit" id="submitButton" style="width:100%;height:auto;" onclick="allowreload=true;addAllToReadonly('authorInput', 'authors');addAllToReadonly('subjectsInput', 'subjects');" disabled>Add</button>
    </form>
</div>

<div id="overlay">
    <div class="floatCenter" id="loading-splash">
        <object data="images/processing.svg" type="image/svg+xml">
            <img loading="lazy" alt="please wait until your information has uploaded and processed, you will be redirected to the home page." src="images/processing.gif" />
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
<script charset="utf-8" type="text/javascript" src="js/add.js"></script>
<script>
    changePubDateToYear('pub-date-label');
    parseReadonlyAuthors();
    parseReadonlySubject();
    validate();
    $('#description').summernote({
        placeholder: '<b>The Ingenious Gentleman Don Quixote of La Mancha</b>, or just <i>Don Quixote</i>, is a Spanish novel by <u>Miguel de Cervantes</u>...',
        tabsize: __DDL_TEXTAREA_TAB_SIZE__ ,
        height: __DDL_TEXTAREA_HEIGHT__ ,
        toolbar: __DDL_TEXTAREA_TOOLBAR__
    });
</script>

<?php include_once('templates/footer.php'); ?>