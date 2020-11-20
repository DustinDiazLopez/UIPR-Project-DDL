<?php

include_once('../connect.php');
include_once('utils/utils.php');

authenticate();

$redirTitle = 'Create Type:';
$errors = ['type' => ''];

if (isset($_POST['type']) && isset($conn)) {
    $type = $_POST['type'];

    if (empty($type)) {
        $errors['type'] = 'You have to provide a type.';
    }

    if (array_filter($errors)) {
        redir_error_type($redirTitle, "Correct errors in the form: {$errors['type']}", $type);
    } else {

        $createdType = INSERT(
            SQL_INSERT_TYPE($type),
            '',
            $errors['type']
        );

        if ($createdType === NULL) {
            $errors['type'] = 'An unknown error occurred when creating the type.';
        }

        if ($createdType === FALSE) {
            $errors['type'] = 'A type with that name already exists.';
        }


        if (array_filter($errors)) {
            redir_error_type($redirTitle, $errors['type'], $type);
        } else {
            redir_success_error($redirTitle, "The type was created ($type)");
        }
    }
}