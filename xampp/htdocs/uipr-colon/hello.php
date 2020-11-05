<?php
include_once('connect.php');
include_once('utils/utils.php');

if (isset($_GET['pwd']) && !empty($_GET['pwd'])) {
    echo ddl_hash($_GET['pwd']);
} else {
    echo 'It works!';
}