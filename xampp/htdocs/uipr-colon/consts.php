<?php

/**
 * URL of the dev site
 */
define('DEV_SITE', 'http://34.66.186.123/index.html');

/**
 * URL to the project
 */
define('PATH', 'https://github.com/DustinDiazLopez/UIPR-Project-DDL');

/**
 * Subpath to the user documentation
 */
define('USER_DOC_PATH', '/wiki/User-Doc');

/**
 * Subpath to the issues
 */
define('NEW_ISSUE', '/issues');

/**
 * Subpath to the wiki of the project
 */
define('WIKI', '/wiki');

/**
 * Subpath to the releases
 */
define('RELEASES', '/releases');

/**
 * Subpath to the README
 */
define('README', '#readme');

/**
 * Subpath to the source code
 */
define('SOURCE_CODE_PATH', '');

/**
 * Subpath to the license
 */
define('LICENSE_PATH', '/blob/main/LICENSE');

/**
 * Redirects to a subpath of {@link PATH}
 * @param string $subpath the subpath for {@link PATH}
 */
function redir_path($subpath) {
    header('Location: ' . PATH . $subpath);
}

/**
 * Redirects to the {@link PATH} / {@link USER_DOC_PATH}
 */
function redir_user_doc() {
    redir_path(USER_DOC_PATH);
}

/**
 * Redirects to the {@link PATH} / {@link NEW_ISSUE}
 */
function redir_new_issue() {
    redir_path(NEW_ISSUE);
}

/**
 * Redirects to the {@link PATH} / {@link WIKI}
 */
function redir_wiki() {
    redir_path(WIKI);
}

/**
 * Redirects to the {@link PATH} / {@link RELEASES}
 */
function redir_releases() {
    redir_path(RELEASES);
}

/**
 * Redirects to the {@link PATH} / {@link SOURCE_CODE_PATH}
 */
function redir_source_code() {
    redir_path(SOURCE_CODE_PATH);
}

/**
 * Redirects to the {@link PATH} / {@link README}
 */
function redir_readme() {
    redir_path(README);
}

/**
 * Redirects to the {@link PATH} / {@link LICENSE_PATH}
 */
function redir_license() {
    redir_path(LICENSE_PATH);
}

/**
 * @param string $subPath subpath to a part of the github project ({@link PATH} is the main path).
 * @param string $title The title of the link
 * @param false $in_progress weather the part is in progress '(Nothing Yet)' will be displayed besides the <b>$title</b>
 * @param string $color background color for the <b>$title</b>
 * @param string $color_in_progress the background color of the '(Nothing Yet)'
 */
function echoPath($subPath, $title='No Title', $in_progress=FALSE, $color='white', $color_in_progress='#ffcccb') {
    $append = '';
    if ($in_progress === TRUE) {
        $append = "<span style=\"background: $color_in_progress;\">(Nothing Yet)</span>";
    }
    echo "<li><a target=\"_blank\" href=\"" . PATH . "$subPath\"><span style=\"background: $color;\">$title</span></a> $append</li>";
}

/**
 * Displays a list of links to various parts of the github project.
 * @param string $msg The message to display on-top of the list
 */
function echoPaths($msg='Site is no longer under development.') {
    echo '<hr/>';
    echo "<p>$msg</p>";
    echo '<ol>';
    echoPath(USER_DOC_PATH, 'User Documentation', TRUE);
    echoPath(NEW_ISSUE, 'Submit an Issue', FALSE, 'yellow');
    echoPath(WIKI, 'The Application\'s Wiki', TRUE);
    echoPath(RELEASES, 'Download the latest releases');
    echoPath(README, 'View the README (prerequisites and installation process)');
    echoPath(SOURCE_CODE_PATH, 'Source Code');
    echoPath(LICENSE_PATH, 'LICENSE');
    echo '</ol>';

    echo '<hr/>';
    echo '<p>Please read the license:</p><pre>';
    readFile('LICENSE');
    echo '</pre>';
}