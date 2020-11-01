<?php
define('DDL_PATH', '../../colon-uipr-cms-ddl-files-and-config');

// DO NOT CHANGE ANYTHING AFTER THIS LINE
define('PATH_TO_CONFIG', DDL_PATH . "/mysql_uiprcmsddl_config.json");

if (!is_dir(DDL_PATH))
    if (mkdir(DDL_PATH))
        echo 'Please create a directory ' . DDL_PATH . ' from current path.';

if (!file_exists(PATH_TO_CONFIG)) {
    $put = file_put_contents(PATH_TO_CONFIG, '{ "host": "localhost", "port": "3306", "username": "dustin", "password": "password", "database": "UIPRCMSDDL", "salt": "$6$rounds=5000$exampleSalt$" }');
    if ($put === FALSE)
        echo 'Please create a json config file ' . PATH_TO_CONFIG . ' inside ' . DDL_PATH;

}

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
