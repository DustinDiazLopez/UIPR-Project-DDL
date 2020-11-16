<?php

define('DDL_PATH', './../../../ddl-config');

// DO NOT CHANGE ANYTHING AFTER THIS LINE
define('PATH_TO_CONFIG', DDL_PATH . "/ddl-config.json");
define('PATH_TO_FILES_FOLDER', DDL_PATH . "/files/");

if (!is_dir(PATH_TO_FILES_FOLDER))
    echo 'ERR 1: Please create the directory ' . PATH_TO_FILES_FOLDER . ' from current path.<br>';

if (!is_dir(DDL_PATH))
    echo 'ERR 2: Please create the directory ' . DDL_PATH . ' from current path.<br>';

if (!file_exists(PATH_TO_CONFIG))
    echo 'ERR 3: Please create the json config file ' . PATH_TO_CONFIG . ' inside ' . DDL_PATH . '<br>';


$config = json_decode(file_get_contents(PATH_TO_CONFIG), true);
$config['salt'] = '$6$rounds=5000$' . $config['salt'] . '$';

/**
 * Establishes a connection to the database with the login information specified in {@link DDL_PATH} / {@link PATH_TO_CONFIG}
 * @return false|mysqli object which represents the connection to a MySQL Server or false if an error occurred.
 * @author Dustin Díaz
 */
function connect() 
{
    global $config;
    return mysqli_connect($config['host'], $config['username'], $config['password'], $config['database'], $config['port']);
}

/**
 * Establishes a connection to the database with the login information specified in {@link DDL_PATH} / {@link PATH_TO_CONFIG}
 * @return mysqli the {@link mysqli::__construct} object.
 * @author Dustin Díaz
 */
function connect_obj()
{
    global $config;
    $mysqli = new mysqli($config['host'], $config['username'], $config['password'], $config['database'], $config['port']);
    /* check connection */
    if (mysqli_connect_errno()) {
        printf("Connect failed: %s\n", mysqli_connect_error());
        exit();
    }
    return $mysqli;
}

$conn = connect();
