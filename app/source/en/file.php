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

            try {
                $has_item = TRUE;
                $item = SQL_GET_ITEM_BY_ID(
                    query(SQL_ITEM_ID_BY_FILE_ID($file['id']))[0]['item_id']
                )[0];
            } catch (Exception $ignored) {
                $has_item = FALSE;
            }

?>
            <!DOCTYPE html>
            <html lang="<?php echo LANG ?>" dir="ltr">

            <head>
                <meta charset="utf-8">
                <link rel="icon" href="./../favicon.ico">
                <link rel="stylesheet" href="./../css/file.css">
                <link rel="stylesheet" href="./../css/selection.color.css">
                <link rel="stylesheet" href="./../css/fa/css/all.css"

                <title><?php echo $file['filename'] ?></title>

            </head>

            <body>

                <?php include_once ('templates/loading.php'); ?>
                <main>
                    <div class="container">
                        <object data="fetch.file.php?file=<?php echo $original; ?>" id="pdf-object">
                            The browser does not support viewing this type of file, download the file here:
                            <a href="fetch.file.php?file=<?php echo $original; ?>&download"
                                download="<?php echo $file['filename'] ?>">
                                Download <?php echo $file['filename'] ?>
                            </a>
                        </object>

                        <div class="overlay">
                            <a href="./" id="back-btn" title="Return Home"><i class="fas fa-home icon"></i></a>

                            <?php if(isset($item) && isset($item['title']) && $has_item === TRUE && !empty($item['title'])): ?>
                            <a href="<?php echo shareURL($item['id'], '/item.view.php?item='); ?>" id="back-btn"
                               title="Go to the related item '<?php echo $item['title']; ?>'">
                                <?php echo str_replace('class="', 'class="icon ', icon($item['type'])); ?>
                            </a>
                            <?php endif; ?>
                        </div>
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


