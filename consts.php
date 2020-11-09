<?php
define('PATH', 'https://github.com/DustinDiazLopez/UIPR-Project-DDL');
define('LICENSE_PATH', '/blob/main/LICENSE');
define('PRE_REQ_PATH', '#prerequisites');
define('USER_DOC_PATH', '/wiki/User-Doc');
define('DEV_DOC_PATH', '/wiki/Dev-Doc');
define('SOURCE_CODE_PATH', '');

function redir_path($subpath) {
    header('Location: ' . PATH . $subpath);
}

function redir_source_code() {
    redir_path(SOURCE_CODE_PATH);
}

function redir_pre_req() {
    redir_path(PRE_REQ_PATH);
}

function redir_license() {
    redir_path(LICENSE_PATH);
}

function redir_user_doc() {
    redir_path(USER_DOC_PATH);
}

function redir_dev_doc() {
    redir_path(DEV_DOC_PATH);
}

function echoPath($subPath, $title) {
    echo '<li><a target="_blank" href="' . PATH . "$subPath\">$title</a></li>";
}

function echoPaths($msg='Site is no longer under development.') {
    echo "<p>$msg</p>";
    echo '<ol>';
    echoPath(SOURCE_CODE_PATH, 'Source Code');
    echoPath(USER_DOC_PATH, 'User Documentation');
    echoPath(PRE_REQ_PATH, 'Prerequisites');
    echoPath(DEV_DOC_PATH, 'Developer Documentation');
    echoPath(LICENSE_PATH, 'LICENSE');
    echo '</ol>';

    echo 'LICENSE:<pre>';
    readFile('LICENSE');
    echo '</pre>';
}