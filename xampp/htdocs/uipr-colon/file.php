<?php
include_once('connect.php');
include_once('utils/utils.php');

authenticate(TRUE);


if ((isset($_GET['file']) && !empty($_GET['file'])) || (isset($_POST['file']) && !empty($_POST['file']))) {
    if (isset($_GET['file'])) {
        $id = explode('-', base64_decode(urldecode($_GET['file'])))[1];
    } else {
        $id = explode('-', base64_decode(urldecode($_POST['file'])))[1];
    }
    $file = SQL_GET_FILE(intval($id));
    //$file = SQL_GET_FILE(intval((($_GET['file']))));
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
<html lang="<?php echo LANG; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="favicon.ico">
    <link rel="stylesheet" href="css/file.css">
    <link rel="stylesheet" href="css/selection.color.css">

    <title><?php echo isset($file['filename']) && !empty($file['filename']) ? $file['filename'] : 'No Name'; ?></title>
</head>

<body>

<section id="lda">
    <?php include_once('templates/loading.php');?>
</section>


<main>
    <div class="container">
        <object type="<?php echo isset($file['type']) && !empty($file['type']) ? $file['type'] : 'application/pdf'; ?>" data="#" title="<?php echo isset($file['filename']) && !empty($file['filename']) ? $file['filename'] : 'No Name'; ?>" id="pdf-object">
            <p>El navegador no soporta ver este tipo de documento o un error paso. Favor de descargar el documento a través de este enlace:
                <a id="blob-download" href="#" download="<?php echo isset($file['filename']) && !empty($file['filename']) ? $file['filename'] : 'No Name'; ?>">
                    Descargar <?php echo isset($file['filename']) && !empty($file['filename']) ? $file['filename'] : 'No Name'; ?>
                </a>.
            </p>
        </object>
    </div>
</main>

<script type="text/javascript" src="js/blob.util.js"></script>

<script>
    const blob = b64toBlob(
        "<?php echo isset($file['content']) && !empty($file['content']) ? $file['content'] : ''; ?>",
        "<?php echo isset($file['type']) && !empty($file['type']) ? $file['type'] : 'application/pdf'; ?>",
        512
    );
    blob.name = '<?php echo isset($file['filename']) && !empty($file['filename']) ? $file['filename'] : 'No Name'; ?>';
    const blobUrl = URL.createObjectURL(blob);

    try {
        document.getElementById('blob-download').href = blobUrl;
        document.getElementById('pdf-object').data = blobUrl;
    } catch (err) {
        console.log('Object tag not initialized or not supported.');
    }

    var ua = window.navigator.userAgent;
    var msie = ua.indexOf("MSIE ");

    // if Internet Explorer download
    if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) {
        if (window.navigator && window.navigator.msSaveOrOpenBlob) {
            window.navigator.msSaveOrOpenBlob(blob, blob.name);
        } else {
            window.open(blobUrl);
        }
    }

    // hacky fix to loading animation after content has been loaded (don't know why it's happening though...)
    stopLoadingAnimation();
</script>

</body>

</html>