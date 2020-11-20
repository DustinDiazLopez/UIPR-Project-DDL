<?php

$se = '?';
if (isset($_GET['se'])) {
    $se .= '&se';
}

if (isset($_GET['noauth'])) {
    $se .= ('&noauth=' . $_GET['noauth']);
}


session_start();
session_unset();
session_destroy();
header("Location: login.php$se");
