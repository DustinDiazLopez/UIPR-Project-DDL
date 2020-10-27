<?php
include_once('connect.php');
include_once('utils/utils.php');

session_start();

if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] === FALSE) {
    header('Location: login.php');
}

$errors = array();
function exe_sql($sql) {
    global $conn;
    global $errors;
    echo '<hr/>' . htmlspecialchars('Executing: ' . $sql) . '<br />';
    mysqli_query($conn, $sql);
    $error = mysqli_error($conn);
    if (!empty($error)) {
        echo htmlspecialchars('Errors: ' . $error);
        $errors[] = $error;
    } else {
        echo 'No errors.';
    }

    echo '<hr/>';
}

if (isset($_POST['delete-item'])) {
    $id = intval($_POST['item-to-delete']);
    if (is_int($id)) {
        $query_files = query(SQL_FILES_ID_BY_ITEM_ID($id));
        $query_subjects = query(SQL_SUBJECTS_ID_BY_ITEM_ID($id));
        $query_authors = query(SQL_AUTHORS_ID_BY_ITEM_ID($id));
        $query_image = query(SQL_IMAGE_ID_BY_ITEM_ID($id));

        $sql = '';
        // DELETE AUTHORS REL
        if (count($query_authors) >= 1) {
            foreach ($query_authors as $obj) {
                $sql = sql_delete_author($id, $obj['author_id']);
                exe_sql($sql);
            }
        }
        
        // DELETE SUBJECT REL
        if (count($query_subjects) >= 1) {
            foreach ($query_subjects as $obj) {
                $sql = sql_delete_subject($id, $obj['subject_id']);
                exe_sql($sql);
            }
        }

        // DELETE FILES REL
        if (count($query_files) >= 1) {
            foreach ($query_files as $obj) {
                $sql = sql_delete_file($id, $obj['file_id']);
                exe_sql($sql);
            }
        }

        //DELETE ITEM
        $sql = sql_delete_item($id);
        exe_sql($sql);

        //DELETE IMAGE
        if (count($query_image) >= 1) {
            foreach ($query_image as $obj) {
                $sql = sql_delete_file($id, $obj['image_id']);
                exe_sql($sql);
            }
        }

        mysqli_close($conn);

        if (array_filter($errors)) {
            echo showWarn("SQL ERROR: ", "Errors were detected");
        } else {
            header("Location: index.php");
        }

    } else {
        die("error parsing id of item \"{$_POST['item-to-delete']}\" is not an integer.");
    }
}
