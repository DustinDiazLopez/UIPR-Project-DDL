<?php

include_once('../connect.php');
include_once('utils/utils.php');

authenticate();

if (isset($conn)) {
    if (isset($_POST['delete-file']) && isset($_POST['file-to-delete'])) {
        $redirTitle = 'Orphaned Files:';
        $id = intval($_POST['file-to-delete']);

        if (is_valid_int($id)) {
            $file = SQL_GET_FILE($id);
            // checks to see if it exists
            if (count($file) > 0) {
                $file = $file[0];
                // delete row from database
                query(sql_delete_file_by_id($id));
                if (mysqli_affected_rows($conn) > 0) {
                    $size = filesize(PATH_TO_FILES_FOLDER . $file['path']);
                    $count = 0;
                    $total_size = 0;
                    if (unlink(PATH_TO_FILES_FOLDER . $file['path'])) {
                        $count += mysqli_affected_rows($conn);
                        $total_size += $size;

                        $total_size = round_ddl($total_size / 1e+6);
                        redir_success_error($redirTitle, "$count of 1 orphaned files were deleted ($total_size MB).");
                    } else {
                        redir_warn_error($redirTitle, "The file could not be deleted " . mysqli_error($conn));
                    }
                }
            } else {
                redir_warn_error($redirTitle, "The file was not found " . mysqli_error($conn));
            }
        } else {
            redir_warn_error($redirTitle, "$id is not a valid id.");
        }
    } elseif (isset($_POST['delete-oprphaned-type']) && isset($_POST['oprphaned-type-to-delete'])) {
        $redirTitle = 'Orphaned Type:';
        $id = intval($_POST['oprphaned-type-to-delete']);
        echo 'delete oprphaned type with id: ' . $_POST['oprphaned-type-to-delete'];
        if (is_valid_int($id)) {
            $type = query(SQL_GET_TYPE . $id);

            // checks to see if it exists
            if (count($type) > 0) {
                // delete row from database
                query(SQL_DELETE_TYPE_BY_ID . $id);

                // delete database row
                if (mysqli_affected_rows($conn) > 0) {
                    redir_success_error($redirTitle, "The type {$type[0]['type']} was deleted!");
                } else {
                    redir_warn_error($redirTitle, "The type {$type[0]['type']} couldn't be deleted from the database " . mysqli_error($conn));
                }
            } else {
                redir_warn_error($redirTitle, "Couldn't find the specified type " . mysqli_error($conn));
            }
        } else {
            redir_warn_error($redirTitle, "$id is not a valid id.");
        }
    } elseif (isset($_POST['delete-admin']) && isset($_POST['admin-to-delete'])) {
        $redirTitle = 'Delete Admin';
        $id = intval($_POST['admin-to-delete']);
        echo 'delete admin with id: ' . $_POST['admin-to-delete'];
        if (is_valid_int($id)) {
            $admin = SQL_GET_ADMIN_BY_ID($id);

            // checks to see if it exists
            if (count($admin) > 0) {
                // delete row from database
                query(SQL_DELETE_ADMIN_BY_ID . $id);

                // delete database row
                if (mysqli_affected_rows($conn) > 0) {
                    redir_success_error($redirTitle, "The admin {$admin[0]['username']} ({$admin[0]['email']}) was deleted!");
                } else {
                    redir_fatal_error($redirTitle, "The admin {$admin[0]['username']} ({$admin[0]['email']}) couldn't be deleted from the database " . mysqli_error($conn));
                }
            } else {
                redir_warn_error($redirTitle, "Couldn't find the specified admin  " . mysqli_error($conn));
            }
        } else {
            redir_warn_error($redirTitle, "$id is not a valid id.");
        }
    } elseif (isset($_POST['delete-all-orphaned-files'])) {
        $redirTitle = 'Delete all Orphaned Files';
        $arr = SQL_GET_ORPHANED_FILES();
        $count = 0;
        $total = count($arr);
        $total_size = 0;
        // and deletes them
        if ($total > 0) {
            foreach ($arr as $obj) {
                if (isset($obj['id'])) {
                    query(sql_delete_file_by_id($obj['id']));
                    if (mysqli_affected_rows($conn) > 0) {
                        $size = filesize(PATH_TO_FILES_FOLDER . $obj['path']);
                        if (unlink(PATH_TO_FILES_FOLDER . $obj['path'])) {
                            $count += mysqli_affected_rows($conn);
                            $total_size += $size;
                        }
                    }
                }
            }

            $total_size = round_ddl($total_size / 1e+6);
            redir_success_error($redirTitle, "$count of $total orphaned files were deleted ($total_size MB).");
        } else {
            redir_warn_error('No orphaned files were found...', '');
        }
    } elseif (isset($_POST['delete-all-orphaned-types'])) {
        $redirTitle = 'Delete all Orphaned Types';
        $arr = SQL_GET_ORPHANED_TYPES();
        $count = 0;
        $total = count($arr);
        // and deletes them
        if ($total > 0) {
            foreach ($arr as $obj) {
                if (isset($obj['id'])) {
                    query(sql_delete_type_by_id($obj['id']));
                    $count += mysqli_affected_rows($conn);
                }
            }

            redir_success_error($redirTitle, "$count of $total orphaned types were deleted.");
        } else {
            redir_warn_error('No orphaned types were found', '');
        }
    } else {
        redir_warn_error("Redirected", "No action was specified.");
    }
} else die("You are not connected to the database");
