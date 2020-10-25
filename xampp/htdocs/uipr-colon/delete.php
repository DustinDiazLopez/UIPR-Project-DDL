<?php
include_once('connect.php');
include_once('utils/utils.php');

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
        $sql_files = "SELECT `file_id` FROM `file_has_item` WHERE `item_id` = $id";
        $sql_subjects = "SELECT `subject_id` FROM `item_has_subject` WHERE `item_id` = $id";
        $sql_authors = "SELECT `author_id` FROM `author_has_item` WHERE `item_id` = $id";
        $sql_image = "SELECT `image_id` FROM `item` WHERE `id` = $id";

        $query_files = query($sql_files);
        $query_subjects = query($sql_subjects);
        $query_authors = query($sql_authors);
        $query_image = query($sql_image);


        $sql = '';
        // DELETE AUTHORS REL
        if (count($query_authors) >= 1) {
            foreach ($query_authors as $obj) {
                $sql = sql_delete_author($id, $obj['author_id']);
                exe_sql($sql);
            }
        }

        $query_authors = NULL;
        
        // DELETE SUBJECT REL
        if (count($query_subjects) >= 1) {
            foreach ($query_subjects as $obj) {
                $sql = sql_delete_subject($id, $obj['subject_id']);
                exe_sql($sql);
            }
        }

        $query_subjects = NULL;

        // DELETE FILES REL
        if (count($query_files) >= 1) {
            foreach ($query_files as $obj) {
                $sql = sql_delete_file($id, $obj['file_id']);
                exe_sql($sql);
            }
        }

        $query_files = NULL;

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

        $query_image = NULL;
        $sql = NULL;

        mysqli_close($conn);

        if (array_filter($errors)) {
            echo showWarn("SQL ERROR: ", "Errors were detected");
        } else {
            header('Location: index.php');
        }

    } else die("error parsing id of item \"{$_POST['item-to-delete']}\" is not an integer.");
}
?>