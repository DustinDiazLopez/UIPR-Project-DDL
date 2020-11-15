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
        $errors['username'] = 'Provide a username.';
    }

    if (empty($email)) {
        $errors['email'] = 'Provide an email.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Provide a valid email.';
    }

    if (empty($pwd)) {
        $errors['pwd'] = 'Provide a password.';
    } elseif (strlen($pwd) < $min_pwd_len) {
        $errors['pwd'] = 'PProvide a password with more than 6 characters.';
    }

    if (empty($repwd)) {
        $errors['repwd'] = 'Repite la contraseña.';
    } elseif (strlen($repwd) < $min_pwd_len) {
        $errors['pwd'] = 'Provide a password with more than 6 characters.';
    }

    if (array_filter($errors)) {
        $str = " ";
        foreach ($errors as $error) {
            if (!empty($error)) {
                $str .= "- $error ";
            }
        }
        redir_error($redirTitle, "Correct errors in the form: $str", $username, $email);
    } else {
        $usersByEmail = count(SQL_GET_ADMIN_BY_EMAIL($email));
        $usersByUsername = count(SQL_GET_ADMIN_BY_USERNAME($username));

        if ($usersByEmail !== 0) {
            $errors['email'] = 'There is already an administrator with that email.';
        }

        if ($usersByUsername !== 0) {
            $errors['username'] = 'An administrator with that username already exists.';
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
                    redir_success_error($redirTitle, "The administrator was created ($username - $email)");
                } elseif ($insert === FALSE) {
                    redir_error($redirTitle, "Failed to create administrator" . mysqli_error($conn), $username, $email);
                } elseif ($insert === NULL) {
                    redir_error($redirTitle, "There is already an administrator with that username or email", $username, $email);
                } else {
                    redir_error($redirTitle, "An unknown error occurred " . mysqli_error($conn), $username, $email);
                }
            } else {
                redir_error($redirTitle, "Passwords do not match.", $username, $email);
            }
        }
    }
} else {
    redir_fatal_error($redirTitle, 'Information is missing in POST.');
}