<?php
include_once('../connect.php');
include_once('utils/utils.php');

authenticate();

$errors = array();

if (isset($_POST['delete-item'])) {
    $id = intval($_POST['item-to-delete']);
    if (is_int($id)) {
        DELETE_ITEM_AND_RELATIONS($id);

        if (isset($conn)) {
            mysqli_close($conn);
        } else die("Not connected to database");

        if (!isset($_dont_redir)) {
            if (array_filter($errors)) {
                echo showWarn("SQL ERROR: ", "Errors were detected");
            } else {
                header("Location: index.php?deleted");
            }
        }

    } else {
        die("error parsing id of item \"{$_POST['item-to-delete']}\" is not an integer.");
    }
}
