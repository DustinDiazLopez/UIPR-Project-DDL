<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Icon -->
    <link rel="icon" href="favicon.ico">

    <!-- Bootstrap -->
    <link rel="stylesheet" href="css/bootstrap.min.css">

    <?php
    include('connect.php');
    include('utils/utils.php');
    $id_download = explode('&', $_SERVER['QUERY_STRING']);

    $id = explode('=', $id_download[0])[1];
    $download = explode('=', $id_download[1])[1];
    $name = explode('=', $id_download[2])[1];
    $file = SQL_GET_FILE($id);
    ?>
    <title><?php echo $name; ?></title>
    <?php

    if (count($file) >= 1) {
        $file = 'data:application/pdf;base64,' . base64_encode($file[0]['file']);
    } else {
        echo showWarn('Failed to load PDF:', 'Sorry, that PDF doesn\'t seem to exist...');
    }

    if ($download != 'false') {
    ?>
        <script>
            var a = document.createElement("a");
            a.href = '<?php echo $file; ?>';
            a.download = '<?php echo $name; ?>';
            a.click();
        </script>
    <?php
        echo showWarn('Download Request:', 'An attempt was made to download the file (please refresh the page if nothing happened).');
        die("");
    }
    ?>

    <style>
        body {
            margin: 0;
        }

        iframe {
            display: block;
            background: white;
            border: none;
            height: 100vh;
            width: 100vw;
            border: 0;
            /* top: 0px;
            left: 0px;
            bottom: 0px;
            right: 0px;
            width: 100%;
            height: 100%; */
        }
    </style>
</head>

<body>
    <iframe src="<?php echo $file ?>" frameborder="0" allowfullscreen>
        <p>Your browser does not support iframe. Please download the pdf.</p>
    </iframe>
    <?php mysqli_close($conn); ?>