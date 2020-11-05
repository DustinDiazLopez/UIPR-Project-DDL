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
        body {
            margin: 0;
            padding: 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <object type="<?php echo isset($file['type']) && !empty($file['type']) ? $file['type'] : 'application/pdf'; ?>" data="#" title="<?php echo isset($file['filename']) && !empty($file['filename']) ? $file['filename'] : 'No Name'; ?>" id="pdf-object" width="750" height="750">
            <p>Este navegador no soporta ver archivos este tipo de archivo. Descargue el documento para verlo:
                <a id="blob-download" href="#"
                   download="<?php echo isset($file['filename']) && !empty($file['filename']) ? $file['filename'] : 'No Name'; ?>">
                    Descargar <?php echo isset($file['filename']) && !empty($file['filename']) ? $file['filename'] : 'No Name'; ?>
                </a>.
            </p>
        </object>
    </div>

    <script>
        function b64toBlob(b64Data, contentType = '', sliceSize = 512) {
            const byteCharacters = atob(b64Data);
            const byteArrays = [];

            for (let offset = 0; offset < byteCharacters.length; offset += sliceSize) {
                const slice = byteCharacters.slice(offset, offset + sliceSize);

                const byteNumbers = new Array(slice.length);
                for (let i = 0; i < slice.length; i++) {
                    byteNumbers[i] = slice.charCodeAt(i);
                }

                const byteArray = new Uint8Array(byteNumbers);
                byteArrays.push(byteArray);
            }

            return new Blob(byteArrays, {
                type: contentType
            });
        }

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

        function resizePDF() {
            const padding = 5;
            const w = (window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth) - padding;
            const h = (window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight) - padding;
            try {
                document.getElementById('pdf-object').width = w;
                document.getElementById('pdf-object').height = h;
            } catch (err) {
                console.log('Object tag not initialized or not supported.');
            }
        }

        window.onresize = resizePDF;
        resizePDF();
    </script>
</body>

</html>
