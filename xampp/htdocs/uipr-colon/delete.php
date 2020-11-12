<?php
include_once('connect.php');
include_once('utils/utils.php');

authenticate();


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
                if (isset($obj['image_id']) && !empty($obj['image_id'])) {
                    $sql = sql_delete_image($obj['image_id']);
                    exe_sql($sql);
                }
            }
        }

        //CHECKS for any images that have no item
        $arr = SQL_GET_ORPHANED_IMAGES();
        // and deletes them
        if (count($arr) > 0) {
            foreach ($arr as $obj) {
                if (isset($obj['id'])) {
                    exe_sql(sql_delete_image($obj['id']));
                }
            }
        }

        //CHECKS for any authors that have no item
        $arr = SQL_GET_ORPHANED_AUTHORS();
        // and deletes them
        if (count($arr) > 0) {
            foreach ($arr as $obj) {
                if (isset($obj['id'])) {
                    exe_sql(sql_delete_author_id($obj['id']));
                }
            }
        }

        //CHECKS for any subjects that have no item
        $arr = SQL_GET_ORPHANED_SUBJECTS();
        // and deletes them
        if (count($arr) > 0) {
            foreach ($arr as $obj) {
                if (isset($obj['id'])) {
                    exe_sql(sql_delete_subject_id($obj['id']));
                }
            }
        }

        if (isset($conn)) {
            mysqli_close($conn);
        } else die("Not connected to database");

        if (array_filter($errors)) {
            echo showWarn("SQL ERROR: ", "Errors were detected");
        } else {
            header("Location: index.php?deleted");
        }

    } else {
        die("error parsing id of item \"{$_POST['item-to-delete']}\" is not an integer.");
    }
}
