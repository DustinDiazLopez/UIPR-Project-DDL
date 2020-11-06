<?php
include_once('connect.php');
include_once('utils/utils.php');

if (isset($_GET['pwd']) && !empty($_GET['pwd'])) {
    $pwd = ddl_hash($_GET['pwd']);
    echo $pwd === 'r4nwUWpjef1wJgwfW4WgSim2P0qskuBFmYQ/p56LZDONtVZiS6CHNBji25G9CTc/kOAjkvwnxeJw4Wr8CuTjS0'
        ? "$pwd <hr><b>(please don't use the example password)</b>"
        : $pwd;
} else {
    echo 'It works!';
}
