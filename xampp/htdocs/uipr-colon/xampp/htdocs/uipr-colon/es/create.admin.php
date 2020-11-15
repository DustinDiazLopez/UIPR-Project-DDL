<?php

include_once('../connect.php');
include_once('utils/utils.php');

authenticate();
$min_pwd_len = 6;

function redir_error($title, $msg, $username, $email)
{
    redir_fatal_error($title, "$msg&username=$username&email=$email");
}

print_r($_POST);
$redirTitle = 'Crear Administrador:';
$errors = ['username' => '', 'email' => '', 'pwd' => '', 'repwd' => ''];
if (isset($_POST['username']) && isset($_POST['email']) && isset($_POST['pwd']) && isset($_POST['repwd']) && isset($conn)) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $pwd = $_POST['pwd'];
    $repwd = $_POST['repwd'];

    if (empty($username)) {
        $errors['username'] = 'Provee una nombre de usuario.';
    }

    if (empty($email)) {
        $errors['email'] = 'Provee un correo electrónico.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Provee un correo electrónico valido.';
    }

    if (empty($pwd)) {
        $errors['pwd'] = 'Provee una contraseña.';
    } elseif (strlen($pwd) < $min_pwd_len) {
        $errors['pwd'] = 'Provee una contraseña con más de 6 caracteres.';
    }

    if (empty($repwd)) {
        $errors['repwd'] = 'Repite la contraseña.';
    } elseif (strlen($repwd) < $min_pwd_len) {
        $errors['pwd'] = 'Provee una contraseña con más de 6 caracteres.';
    }

    if (array_filter($errors)) {
        $str = " ";
        foreach ($errors as $error) {
            if (!empty($error)) {
                $str .= "- $error ";
            }
        }
        redir_error($redirTitle, "Corregir errores en la forma: $str", $username, $email);
    } else {
        $usersByEmail = count(SQL_GET_ADMIN_BY_EMAIL($email));
        $usersByUsername = count(SQL_GET_ADMIN_BY_USERNAME($username));

        if ($usersByEmail !== 0) {
            $errors['email'] = 'Ya existe un administrador con ese correo electrónico.';
        }

        if ($usersByUsername !== 0) {
            $errors['username'] = 'Ya existe un administrador con ese nombre de usuario.';
        }

        if (array_filter($errors)) {
            $str = " ";
            foreach ($errors as $error) {
                if (!empty($error)) {
                    $str .= "- $error ";
                }
            }
            redir_error($redirTitle, $str, $username, $email);
        } else {
            if ($pwd === $repwd) {
                unset($repwd);
                $pwd = ddl_hash($pwd);
                $insert = SQL_INSERT_ADMIN($email, $username, $pwd);
                unset($pwd);
                if ($insert) {
                    redir_success_error($redirTitle, "Se creó el administrador ($username - $email)");
                } elseif ($insert === FALSE) {
                    redir_error($redirTitle, "Error al crear el administrador" . mysqli_error($conn), $username, $email);
                } elseif ($insert === NULL) {
                    redir_error($redirTitle, "Ya existe un administrador con ese nombre de usuario o correo electrónico", $username, $email);
                } else {
                    redir_error($redirTitle, "An unknown error occurred " . mysqli_error($conn), $username, $email);
                }
            } else {
                redir_error($redirTitle, "Las contraseñas no coinciden.", $username, $email);
            }
        }
    }
} else {
    redir_fatal_error($redirTitle, 'Falta información en POST.');
}