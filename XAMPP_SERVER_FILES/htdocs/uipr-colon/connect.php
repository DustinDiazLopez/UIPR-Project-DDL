<?php
function connect() {
    $config = json_decode(file_get_contents("../../mysql_uiprcmsddl_config.json"), true);
    return mysqli_connect($config['host'], $config['username'], $config['password'], $config['database'], $config['port']);
}

$conn = connect();
?>