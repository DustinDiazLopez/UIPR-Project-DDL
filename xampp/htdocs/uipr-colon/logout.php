<?php

$se = '';
if (isset($_GET['se'])) $se = '?se';

session_start();
session_unset();
session_destroy();
header("Location: login.php$se");
