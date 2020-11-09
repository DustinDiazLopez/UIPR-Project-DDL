<?php
include_once('connect.php');
include_once('utils/utils.php');

?>

<!DOCTYPE html>
<html lang="<?php echo LANG; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="favicon.ico">
    <title><?php echo 'Hello, ' . APP_NAME . '!'; ?></title>
</head>

<body>

<?php
if (isset($_GET['pwd']) && !empty($_GET['pwd'])) {
    $pwd = htmlspecialchars(ddl_hash($_GET['pwd']));
    echo $pwd === 'r4nwUWpjef1wJgwfW4WgSim2P0qskuBFmYQ/p56LZDONtVZiS6CHNBji25G9CTc/kOAjkvwnxeJw4Wr8CuTjS0'
        ? "<p><b>(please don't use the example password)</b></p>" : "";


    echo "<pre>USE UIPRCMSDDL;INSERT INTO `admin` (`email`, `username`, `password`) VALUES ('example@example.com', 'root', '$pwd');</pre>";
    echo '<a href="index.php">Login</a>';
    unset($_GET['pwd']);
    unset($pwd);
} else {
    phpinfo();
}
?>


</body>

</html>

