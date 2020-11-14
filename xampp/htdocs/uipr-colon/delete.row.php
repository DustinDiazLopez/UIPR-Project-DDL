<?php

include_once('connect.php');
include_once('utils/utils.php');

authenticate();

if (isset($conn)) {
    if (isset($_POST['delete-file']) && isset($_POST['file-to-delete'])) {
        $redirTitle = 'Archivos Huérfanos:';
        $id = intval($_POST['file-to-delete']);

        if (is_valid_int($id)) {
            $file = SQL_GET_FILE($id);
            // checks to see if it exists
            if (count($file) > 0) {
                // delete row from database
                query(sql_delete_file_by_id($id));
                // delete database row
                if (mysqli_affected_rows($conn) > 0) {
                    redir_success_error($redirTitle, "El archivo fue borrado!");
                } else {
                    redir_warn_error($redirTitle, "El archivo no se pudo borrar de la base de datos " . mysqli_error($conn));
                }
            } else {
                redir_warn_error($redirTitle, "El archivo no fue encontrado " . mysqli_error($conn));
            }
        } else {
            redir_warn_error($redirTitle, "$id no es un id válido.");
        }
    } elseif (isset($_POST['delete-oprphaned-type']) && isset($_POST['oprphaned-type-to-delete'])) {
        $redirTitle = 'Tipo Huérfano:';
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
                    redir_success_error($redirTitle, "El tipo {$type[0]['type']} fue borrado!");
                } else {
                    redir_warn_error($redirTitle, "El tipo {$type[0]['type']} no se pudo borrar de la base de datos " . mysqli_error($conn));
                }
            } else {
                redir_warn_error($redirTitle, "El tipo no fue encontrado " . mysqli_error($conn));
            }
        } else {
            redir_warn_error($redirTitle, "$id no es un id válido.");
        }
    } elseif (isset($_POST['delete-admin']) && isset($_POST['admin-to-delete'])) {
        $redirTitle = 'Borrar Administrador:';
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
                    redir_success_error($redirTitle, "El administrador(a) {$admin[0]['username']} ({$admin[0]['email']}) fue borrado!");
                } else {
                    redir_fatal_error($redirTitle, "El administrador(a) {$admin[0]['username']} ({$admin[0]['email']}) no se pudo borrar de la base de datos " . mysqli_error($conn));
                }
            } else {
                redir_warn_error($redirTitle, "El administrador(a) no fue encontrado " . mysqli_error($conn));
            }
        } else {
            redir_warn_error($redirTitle, "$id no es un id válido.");
        }
    } elseif (isset($_POST['delete-all-orphaned-files'])) {
        $redirTitle = 'Borrar todos los Archivos Huérfanos';
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
            redir_success_error($redirTitle, "$count de $total archivos huérfanos fueron borrados ($total_size MB).");
        } else {
            redir_warn_error('No hay archivos huérfanos', '');
        }
    } elseif (isset($_POST['delete-all-orphaned-types'])) {
        $redirTitle = 'Borrar todos los Tipos Huérfanos';
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

            redir_success_error($redirTitle, "$count de $total tipos huérfanos fueron borrados.");
        } else {
            redir_warn_error('No hay tipos huérfanos', '');
        }
    } else {
        redir_warn_error("Redirigido(a):", "No proporcionó ningun tipo de información para eliminar algun tipo de dato.");
    }
} else die("No esta conectado a la base de datos");
