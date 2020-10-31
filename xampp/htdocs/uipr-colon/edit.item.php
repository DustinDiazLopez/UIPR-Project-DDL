<?php
include_once('connect.php');
include_once('utils/utils.php');

authenticate();

if (isset($_POST['editItem']) && !empty($_POST['editItem'])) {
    print_r($_POST);
    die('stopped');
}

header('Location: index.php');