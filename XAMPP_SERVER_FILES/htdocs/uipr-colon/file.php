<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Icon -->
    <link rel="icon" href="favicon.ico">

    <!-- Bootstrap -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/selection.color.css">

    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: black;
        }

        iframe {
            display: block;
            background: white;
            border: none;
            height: 100vh;
            width: 100vw;
            border: 0;
        }
    </style>

    <?php
    include('connect.php');
    include('utils/utils.php');
    $id_download = explode('&', $_SERVER['QUERY_STRING']);

    if (count($id_download) < 3) {
        echo showWarn('Malformed URL:', 'There is missing information in the URL.');
        die("");
    }

    $id = explode('=', $id_download[0])[1];
    $download = explode('=', $id_download[1])[1];
    $name = str_replace('%20', ' ', htmlspecialchars(explode('=', $id_download[2])[1]));
    $file = SQL_GET_FILE($id);
    mysqli_close($conn);
    
    ?>
    <title><?php echo $name; ?></title>
    <?php

    if (count($file) >= 1) {
        $file = 'data:application/pdf;base64,' . base64_encode($file[0]['file']);
    } else {
        header("HTTP/1.0 404 Not Found");

        echo showWarn('Failed to load PDF:', 'Sorry, that PDF doesn\'t seem to exist...');
        die("");
    }

    if ($download != 'false') {
    ?>
        <script>
            var a = document.createElement("a");
            a.href = '<?php echo $file; ?>';
            a.download = '<?php echo htmlspecialchars($name); ?>';
            a.click();
        </script>
    <?php

        echo showWarn('Download Request:', 'An attempt was made to download the file (please refresh the page if nothing happened).');
        die("");
    }
    ?>
</head>

<body>
    <iframe src="<?php echo $file ?>" frameborder="0" allowfullscreen>
        <p>Your browser does not support iframe. Please download the pdf.</p>
    </iframe>
</body>

</html>