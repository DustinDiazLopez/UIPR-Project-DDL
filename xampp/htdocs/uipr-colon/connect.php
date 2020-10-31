<?php
define('DDL_PATH', '../../colon-uipr-cms-ddl-files-and-config');

// DO NOT CHANGE ANYTHING AFTER THIS LINE
define('FILE_FOLDER', DDL_PATH . "/files");
define('PATH_TO_CONFIG', DDL_PATH . "/mysql_uiprcmsddl_config.json");

if (!is_dir(DDL_PATH)) 
    mkdir(DDL_PATH);

if (!is_dir(FILE_FOLDER)) 
    mkdir(FILE_FOLDER);

if (!file_exists(PATH_TO_CONFIG)) 
    file_put_contents(PATH_TO_CONFIG, '{ "host": "localhost", "port": "3306", "username": "dustin", "password": "password", "database": "UIPRCMSDDL", "salt": "$6$rounds=5000$exampleSalt$" }');


$config = json_decode(file_get_contents(PATH_TO_CONFIG), true);

/**
 * @return false|mysqli
 */
function connect() 
{
    global $config;
    return mysqli_connect($config['host'], $config['username'], $config['password'], $config['database'], $config['port']);
}

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
