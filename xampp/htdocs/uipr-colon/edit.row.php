<?php

include_once('connect.php');
include_once('utils/utils.php');

authenticate();

print_r($_POST);

if (isset($_POST['edit-admin']) && isset($_POST['admin-to-edit']) && isset($_POST['newpwd'])
    && isset($_POST['username']) && isset($_POST['email']) && isset($_POST['userid']) && !empty($_POST['admin-to-edit'])
    && !empty($_POST['newpwd']) && !empty($_POST['username']) && !empty($_POST['email']) && !empty($_POST['userid'])) {
    $redir_title = "Editar Administrador(a):";
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
                        redir_fatal_error($redir_title, "El correo electrónico ya existe");
                    } elseif ($rows_affected === 'username') {
                        redir_fatal_error($redir_title, "Nombre de usuario ya existe");
                    } else {
                        if ($rows_affected > 0) {
                            redir_success_error('Administrador(a) Editado(a)', "El usuario {$new_user['username']} fue editado.");
                        } else {
                            redir_warn_error($redir_title, "Nada fue editado.");
                        }
                    }
                } else {
                    redir_fatal_error($redir_title, "Error al generar el comando SQL (es decir, algo no está configurado en la información del usuario anterior o no coincide el numero de identificación del usuario anterior y el nuevo)");
                }
            } else {
                redir_fatal_error($redir_title, "Administrador (id: {$_POST['admin-to-edit']}) no parece existir ...");
            }
        } else {
            redir_fatal_error($redir_title, "Los ID del usuario no coinciden (userid:{$_POST['userid']} !== admin-to-edit:{$_POST['admin-to-edit']})");
        }
    } else {
        redir_fatal_error($redir_title, 'Email inválido');
    }

} elseif (isset($_POST['edit-author']) && isset($_POST['author-to-edit']) && isset($_POST['edit-author-name'])
    && !empty($_POST['author-to-edit']) && !empty($_POST['edit-author-name'])) {
    $redir_title = 'Editar Autor(a)';
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
                        redir_fatal_error($redir_title, 'No coinciden el ID del autor.');
                    } elseif ($rows_affected === 'author_name') {
                        redir_fatal_error($redir_title, 'El autor(a) ya existe.');
                    } else {
                        if ($rows_affected > 0) {
                            redir_success_error('Autor(a) Editado(a)', "El autor(a) fue editado (de '{$old_value['author_name']}' a '{$new_value['author_name']}').");
                        } else {
                            redir_warn_error($redir_title, "Nada fue editado (ya existe o nada cambio).");
                        }
                    }
                } else {
                    redir_warn_error($redir_title, "Nada fue editado, ya que, tiene el mismo nombre.");
                }
            } else {
                redir_fatal_error($redir_title, 'No coinciden el ID del autor.');
            }
        } else {
            redir_fatal_error($redir_title, 'No se pudo encontrar al autor con el numero de identificación especificada');
        }

    } else {
        redir_fatal_error($redir_title, 'ID inválido');
    }

} elseif (isset($_POST['edit-subject']) && isset($_POST['subject-to-edit']) && isset($_POST['edit-subject-name'])
    && !empty($_POST['subject-to-edit']) && !empty($_POST['edit-subject-name'])) {
    $redir_title = 'Editar Sujeto';
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
                        redir_fatal_error($redir_title, 'No coinciden el ID del sujeto.');
                    } elseif ($rows_affected === 'subject') {
                        redir_fatal_error($redir_title, 'El sujeto ya existe.');
                    } else {
                        if ($rows_affected > 0) {
                            redir_success_error('Sujeto Editado', "El sujeto fue editado (de '{$old_value['subject']}' a '{$new_value['subject']}').");
                        } else {
                            redir_warn_error($redir_title, "Nada fue editado (ya existe o nada cambio).");
                        }
                    }
                } else {
                    redir_warn_error($redir_title, "Nada fue editado, ya que, tiene el mismo nombre.");
                }
            } else {
                redir_fatal_error($redir_title, 'No coinciden el ID del sujeto.');
            }
        } else {
            redir_fatal_error($redir_title, 'No se pudo encontrar al sujeto con el numero de identificación especificada');
        }

    } else {
        redir_fatal_error($redir_title, 'ID inválido');
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
                            redir_success_error('Tipo Editado', "El tipo fue editado (de '{$old_value['type']}' a '{$new_value['type']}').");
                        } else {
                            redir_warn_error($redir_title, "Nada fue editado (ya existe o nada cambio).");
                        }
                    }
                } else {
                    redir_warn_error($redir_title, "Nada fue editado, ya que, tiene el mismo nombre.");
                }
            } else {
                redir_fatal_error($redir_title, 'No coinciden el ID del tipo.');
            }
        } else {
            redir_fatal_error($redir_title, 'No se pudo encontrar al tipo con el numero de identificación especificada');
        }

    } else {
        redir_fatal_error($redir_title, 'ID inválido');
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
                            redir_success_error('Tipo Huérfanos Editado', "El tipo fue editado (de '{$old_value['type']}' a '{$new_value['type']}').");
                        } else {
                            redir_warn_error($redir_title, "Nada fue editado (ya existe o nada cambio).");
                        }
                    }
                } else {
                    redir_warn_error($redir_title, "Nada fue editado, ya que, tiene el mismo nombre.");
                }
            } else {
                redir_fatal_error($redir_title, 'No coinciden el ID del tipo.');
            }
        } else {
            redir_fatal_error($redir_title, 'No se pudo encontrar al tipo con el numero de identificación especificada');
        }

    } else {
        redir_fatal_error($redir_title, 'ID inválido');
    }
}

die('<hr>Um, why are you here?');