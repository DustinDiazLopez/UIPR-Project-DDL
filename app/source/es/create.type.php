<?php

include_once('../connect.php');
include_once('utils/utils.php');

authenticate();

$redirTitle = 'Crear Tipo:';
$errors = ['type' => ''];

if (isset($_POST['type']) && isset($conn)) {
    $type = $_POST['type'];

    if (empty($type)) {
        $errors['type'] = 'Tiene que proveer un tipo.';
    }

    if (array_filter($errors)) {
        redir_error($redirTitle, "Corregir errores en la forma: {$errors['type']}", $type);
    } else {
        $createdType = query(SQL_INSERT_TYPE($type));

        if ($createdType === NULL) {
            $errors['email'] = 'Un error desconocido ocurrió cuando creando el tipo.';
        }

        if ($createdType === FALSE) {
            $errors['email'] = 'Ya existe un tipo con ese nombre.';
        }


        if (array_filter($errors)) {
            redir_error($redirTitle, $errors['type'], $type);
        } else {
            redir_success_error($redirTitle, "Se creó el tipo ($type)");
        }
    }
}