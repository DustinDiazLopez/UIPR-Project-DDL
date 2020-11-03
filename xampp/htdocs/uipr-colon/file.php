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

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <style>
        body {
            margin: 0;
            padding: 0;
        }
    </style>
    <div class="container">
        <object type="<?php echo $file['type'] ?>" data="#" title="<?php echo $file['filename']; ?>" id="pdf-object" width="750" height="750">
            <param name="<?php echo $file['filename']; ?>" value="<?php echo $file['filename']; ?>">
            <embed type="<?php echo $file['type'] ?>" src="#" title="<?php echo $file['filename']; ?>" id="pdf-embed">
            <p>Este navegador no soporta ver archivos este tipo de archivo. Descargue el documento para verlo:
                <a id="blob-download" href="#" download="<?php echo $file['filename']; ?>">Descargar <?php echo $file['filename']; ?></a>.
            </p>
            </embed>
        </object>
    </div>

    <script>
        const b64toBlob = (b64Data, contentType = '', sliceSize = 512) => {
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

            const blob = new Blob(byteArrays, {
                type: contentType
            });
            return blob;
        }
        const blob = b64toBlob("<?php echo base64_encode($file['content']); ?>", "<?php echo $file['type'] ?>");
        blob.name = "<?php echo $file['filename']; ?>";
        const blobUrl = URL.createObjectURL(blob);
        document.getElementById('blob-download').href = blobUrl;

        try {
            document.getElementById('pdf-object').data = blobUrl;
        } catch (err) {
            console.log('Object not initilized or not supported.');
        }
        try {
            document.getElementById('pdf-embed').src = blobUrl;
        } catch (err) {
            console.log('Object not initilized or not supported.');
        }


        function resizePDF() {
            const padding = 5;
            const w = (window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth) - padding;
            const h = (window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight) - padding;
            try {
                document.getElementById('pdf-object').width = w;
                document.getElementById('pdf-object').height = h;
            } catch (err) {
                console.log('Object not initilized or not supported.');
            }

            try {
                document.getElementById('pdf-embed').width = w;
                document.getElementById('pdf-embed').height = h;
            } catch (err) {
                console.log('Embed not initilized or not supported.');
            }
        }

        window.onresize = resizePDF;
        resizePDF();
    </script>
</body>

</html>