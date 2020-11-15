<?php
include_once('../connect.php');
include_once('utils/utils.php');


try {
    if ((isset($_GET['file']) && !empty($_GET['file'])) || (isset($_POST['file']) && !empty($_POST['file']))) {
        $original = '';
        if (isset($_GET['file'])) {
            $original = $_GET['file'];
            $id = explode('-', base64_decode(urldecode($_GET['file'])))[1];
        } else {
            $original = $_POST['file'];
            $id = explode('-', base64_decode(urldecode($_POST['file'])))[1];
        }
        $file = SQL_GET_FILE(intval($id));

        if (count($file) > 0)  {
            $file = $file[0];
            $file['filename'] = htmlspecialchars($file['filename']);
?>
            <!DOCTYPE html>
            <html lang="<?php echo LANG ?>" dir="ltr">

            <head>
                <meta charset="utf-8">
                <link rel="icon" href="./../favicon.ico">
                <link rel="stylesheet" href="./../css/file.css">
                <link rel="stylesheet" href="./../css/selection.color.css">
                <title><?php echo $file['filename'] ?></title>
            </head>

            <body>

                <?php include_once ('templates/loading.php'); ?>
                <main>
                    <div class="container">
                        <object data="fetch.file.php?file=<?php echo $original; ?>" id="pdf-object">
                            El navegador no soporta ver este tipo de archivo, descárguelo aquí:
                            <a href="fetch.file.php?file=<?php echo $original; ?>&download"
                                download="<?php echo $file['filename'] ?>">
                                Descargar <?php echo $file['filename'] ?>
                            </a>
                        </object>
                    </div>
                </main>
            </body>

            </html>

            <?php

        } else {
            header('Location: index.php?error=loading-file');
        }
    } else {
        header('Location: index.php?error=no-pdf');
    }
} catch (Exception $exception) {
    header('Location: index.php?error=no-pdf');
}


