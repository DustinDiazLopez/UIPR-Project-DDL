<?php
include_once('connect.php');
include_once('utils/utils.php');

authenticate();

if (isset($_POST['file']) && !empty($_POST['file']) && is_valid_int($_POST['file'])) {
    $file = SQL_GET_FILE(intval($_POST['file']));
    if (count($file) > 0) $file = $file[0];
}


if (isset($_GET['file']) && !empty($_GET['file']) && is_valid_int($_GET['file'])) {
    $file = SQL_GET_FILE(intval($_GET['file']));
    if (count($file) > 0) $file = $file[0];
}

if (isset($file['content']) || !empty($file['content'])) {
    $file['filename'] = htmlspecialchars($file['filename']);
    $file['type'] = htmlspecialchars($file['type']);
    $file['content'] = base64_encode($file['content']);
} else {
    header('Location: index.php?error=no-pdf');
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="favicon.ico">
    <title><?php echo isset($file['filename']) && !empty($file['filename']) ? $file['filename'] : 'No Name'; ?></title>

    <style>
        * {
            padding: 0;
            margin: 0;
        }

        .container {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
        }

        #pdf-object {
            width: 100%;
            height: 100%;
        }

    </style>
</head>

<body>
    <div class="container">
        <object type="<?php echo isset($file['type']) && !empty($file['type']) ? $file['type'] : 'application/pdf'; ?>" data="#" title="<?php echo isset($file['filename']) && !empty($file['filename']) ? $file['filename'] : 'No Name'; ?>" id="pdf-object">
            <p>Este navegador no soporta ver archivos este tipo de archivo. Descargue el documento para verlo:
                <a id="blob-download" href="#"
                   download="<?php echo isset($file['filename']) && !empty($file['filename']) ? $file['filename'] : 'No Name'; ?>">
                    Descargar <?php echo isset($file['filename']) && !empty($file['filename']) ? $file['filename'] : 'No Name'; ?>
                </a>.
            </p>
        </object>
    </div>

    <script type="text/javascript" src="js/blob.util.js"></script>

    <script>
        const blobUrl = URL.createObjectURL(b64toBlob(
            "<?php echo isset($file['content']) && !empty($file['content']) ? $file['content'] : ''; ?>",
            "<?php echo isset($file['type']) && !empty($file['type']) ? $file['type'] : 'application/pdf'; ?>"
        ));

        document.getElementById('blob-download').href = blobUrl;

        try {
            document.getElementById('pdf-object').data = blobUrl;
        } catch (err) {
            console.log('Object tag not initialized or not supported.');
        }
    </script>
</body>

</html>
