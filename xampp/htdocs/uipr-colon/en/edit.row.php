<?php

include_once('../connect.php');
include_once('utils/utils.php');

authenticate();

print_r($_POST);

if (isset($_POST['edit-admin']) && isset($_POST['admin-to-edit']) && isset($_POST['newpwd'])
    && isset($_POST['username']) && isset($_POST['email']) && isset($_POST['userid']) && !empty($_POST['admin-to-edit'])
    && !empty($_POST['newpwd']) && !empty($_POST['username']) && !empty($_POST['email']) && !empty($_POST['userid'])) {
    $redir_title = "Edit Admin:";
    $pwd = ddl_hash($_POST['newpwd']);
    unset($_POST['newpwd']);

    if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        if ($_POST['admin-to-edit'] === $_POST['userid']) {
            $id = intval($_POST['admin-to-edit']);
            $old_user = SQL_GET_ADMIN_BY_ID($id);

            if (count($old_user) > 0) {
                $old_user = $old_user[0];
                $new_user = [
                    'id' => $id,
                    'username' => $_POST['username'],
                    'email' => $_POST['email'],
                    'password' => $pwd
                ];
                $rows_affected = SQL_UPDATE_ADMIN($id, $old_user, $new_user);

                if ($rows_affected !== NULL) {
                    if ($rows_affected === 'email') {
                        redir_fatal_error($redir_title, "That email already exists");
                    } elseif ($rows_affected === 'username') {
                        redir_fatal_error($redir_title, "That username already exists");
                    } else {
                        if ($rows_affected > 0) {
                            redir_success_error('Edited', "The user {$new_user['username']} was edited.");
                        } else {
                            redir_warn_error($redir_title, "No changed were detected.");
                        }
                    }
                } else {
                    redir_fatal_error($redir_title, "Error when generating the SQL command (that is, something is not configured in the information of the previous user or the identification number of the old and new user does not match)");
                }
            } else {
                redir_fatal_error($redir_title, "Admin (id: {$_POST['admin-to-edit']}) doesn't seem to exist...");
            }
        } else {
            redir_fatal_error($redir_title, "User IDs do not match (userid:{$_POST['userid']} !== admin-to-edit:{$_POST['admin-to-edit']})");
        }
    } else {
        redir_fatal_error($redir_title, 'Invalid email');
    }

} elseif (isset($_POST['edit-author']) && isset($_POST['author-to-edit']) && isset($_POST['edit-author-name'])
    && !empty($_POST['author-to-edit']) && !empty($_POST['edit-author-name'])) {
    $redir_title = 'Edit Author';
    if (filter_var($_POST['author-to-edit'], FILTER_VALIDATE_INT)) {
        $id = intval($_POST['author-to-edit']);
        $old_value = SQL_GET_AUTHOR_BY_ID($id);

        if (count($old_value) > 0) {
            $old_value = $old_value[0];
            $new_value = [
                'id' => $id,
                'author_name' => htmlspecialchars($_POST['edit-author-name'])
            ];
            if ($old_value['id'] == $id && $id == $new_value['id']) {
                if ($new_value['author_name'] !== $old_value['author_name']) {
                    $rows_affected = SQL_UPDATE_AUTHOR($id, $old_value, $new_value);

                    if ($rows_affected === NULL) {
                        redir_fatal_error($redir_title, 'Author ID does not match.');
                    } elseif ($rows_affected === 'author_name') {
                        redir_fatal_error($redir_title, 'The author already exists..');
                    } else {
                        if ($rows_affected > 0) {
                            redir_success_error('Edited', "The author was edited (from '{$old_value['author_name']}' to '{$new_value['author_name']}').");
                        } else {
                            redir_warn_error($redir_title, "No changes were detected.");
                        }
                    }
                } else {
                    redir_warn_error($redir_title, "Nothing was edited, since they have the same name.");
                }
            } else {
                redir_fatal_error($redir_title, 'Author IDs do not match.');
            }
        } else {
            redir_fatal_error($redir_title, 'The author with the specified identification number could not be found');
        }

    } else {
        redir_fatal_error($redir_title, 'Invalid ID');
    }

} elseif (isset($_POST['edit-subject']) && isset($_POST['subject-to-edit']) && isset($_POST['edit-subject-name'])
    && !empty($_POST['subject-to-edit']) && !empty($_POST['edit-subject-name'])) {
    $redir_title = 'Edit Subject';
    if (filter_var($_POST['subject-to-edit'], FILTER_VALIDATE_INT)) {
        $id = intval($_POST['subject-to-edit']);
        $old_value = SQL_GET_SUBJECT_BY_ID($id);

        if (count($old_value) > 0) {
            $old_value = $old_value[0];
            $new_value = [
                'id' => $id,
                'subject' => htmlspecialchars($_POST['edit-subject-name'])
            ];
            if ($old_value['id'] == $id && $id == $new_value['id']) {
                if ($new_value['subject'] !== $old_value['subject']) {
                    $rows_affected = SQL_UPDATE_SUBJECT($id, $old_value, $new_value);

                    if ($rows_affected === NULL) {
                        redir_fatal_error($redir_title, 'Subject IDs do not match.');
                    } elseif ($rows_affected === 'subject') {
                        redir_fatal_error($redir_title, 'The subject already exists');
                    } else {
                        if ($rows_affected > 0) {
                            redir_success_error('Edited', "The subject was edited (from '{$old_value['subject']}' to '{$new_value['subject']}').");
                        } else {
                            redir_warn_error($redir_title, "No changed were detected");
                        }
                    }
                } else {
                    redir_warn_error($redir_title, "Nothing was edited, since it has the same name.");
                }
            } else {
                redir_fatal_error($redir_title, 'Subject IDs do not match.');
            }
        } else {
            redir_fatal_error($redir_title, 'The subject with the specified identification number could not be found');
        }

    } else {
        redir_fatal_error($redir_title, 'Invalid ID');
    }

} elseif (isset($_POST['edit-type']) && isset($_POST['type-to-edit']) && isset($_POST['edit-type-name'])
    && !empty($_POST['type-to-edit']) && !empty($_POST['edit-type-name'])) {
    $redir_title = 'Editar Tipo';
    if (filter_var($_POST['type-to-edit'], FILTER_VALIDATE_INT)) {
        $id = intval($_POST['type-to-edit']);
        $old_value = SQL_GET_TYPE_BY_ID($id);

        if (count($old_value) > 0) {
            $old_value = $old_value[0];
            $new_value = [
                'id' => $id,
                'type' => htmlspecialchars($_POST['edit-type-name'])
            ];
            if ($old_value['id'] == $id && $id == $new_value['id']) {
                if ($new_value['type'] !== $old_value['type']) {
                    $rows_affected = SQL_UPDATE_TYPE($id, $old_value, $new_value);

                    if ($rows_affected === NULL) {
                        redir_fatal_error($redir_title, 'No coinciden el ID del tipo.');
                    } elseif ($rows_affected === 'type') {
                        redir_fatal_error($redir_title, 'El tipo ya existe.');
                    } else {
                        if ($rows_affected > 0) {
                            redir_success_error('Edited', "The type (from '{$old_value['type']}' to '{$new_value['type']}').");
                        } else {
                            redir_warn_error($redir_title, "No changed were detected");
                        }
                    }
                } else {
                    redir_warn_error($redir_title, "Nothing was edited, since it has the same name.");
                }
            } else {
                redir_fatal_error($redir_title, 'Type IDs do not match.');
            }
        } else {
            redir_fatal_error($redir_title, 'The type with the specified identification number could not be found');
        }

    } else {
        redir_fatal_error($redir_title, 'Invalid ID');
    }
} elseif (isset($_POST['edit-oprphaned-type']) && isset($_POST['oprphaned-type-to-edit']) && isset($_POST['new-o-type-name'])
    && !empty($_POST['oprphaned-type-to-edit']) && !empty($_POST['new-o-type-name'])) {
    $redir_title = 'Editar Tipo Huérfanos';
    if (filter_var($_POST['oprphaned-type-to-edit'], FILTER_VALIDATE_INT)) {
        $id = intval($_POST['oprphaned-type-to-edit']);
        $old_value = SQL_GET_TYPE_BY_ID($id);

        if (count($old_value) > 0) {
            $old_value = $old_value[0];
            $new_value = [
                'id' => $id,
                'type' => htmlspecialchars($_POST['new-o-type-name'])
            ];
            if ($old_value['id'] == $id && $id == $new_value['id']) {
                if ($new_value['type'] !== $old_value['type']) {
                    $rows_affected = SQL_UPDATE_TYPE($id, $old_value, $new_value);

                    if ($rows_affected === NULL) {
                        redir_fatal_error($redir_title, 'No coinciden el ID del tipo.');
                    } elseif ($rows_affected === 'type') {
                        redir_fatal_error($redir_title, 'El tipo ya existe.');
                    } else {
                        if ($rows_affected > 0) {
                            redir_success_error('Edited', "The orphaned type (from '{$old_value['type']}' to '{$new_value['type']}').");
                        } else {
                            redir_warn_error($redir_title, "No changed were detected");
                        }
                    }
                } else {
                    redir_warn_error($redir_title, "Nothing was edited, since it has the same name.");
                }
            } else {
                redir_fatal_error($redir_title, 'Type IDs do not match.');
            }
        } else {
            redir_fatal_error($redir_title, 'The type with the specified identification number could not be found');
        }

    } else {
        redir_fatal_error($redir_title, 'Invalid ID');
    }
}

die('<hr>Um, why are you here?');