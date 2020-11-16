<?php
include_once('../consts.php');

if (isset($_GET['release']) && !empty($_GET['release']) ) {
    redir_release(urldecode($_GET['release']));
} else {
    redir_releases();
}