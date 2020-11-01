
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon"/>
<?php
include_once('connect.php');
include_once('utils/utils.php');

authenticate();


function DownloadFile($file)
{
    header('Content-Description: File Transfer');
    header('Content-Type: ' . $file['type']);
    header('Content-Disposition: attachment; filename=' . $file['filename']);
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . $file['size']);
    ob_clean();
    flush();
    echo $file['content'];
    exit;
}

function ViewFile($file)
{
    header('Content-Description: View File');
    header('Content-Type: ' . $file['type']);
    header('Content-Disposition: inline; filename=' . $file['filename']);
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . $file['size']);
    ob_clean();
    flush();
    echo $file['content'];
    exit;
}

if (isset($_POST['file']) && !empty($_POST['file']) && is_valid_int($_POST['file'])) {
    $file = SQL_GET_FILE(intval($_POST['file']));

    if (count($file) > 0) {
        if (isset($_POST['download-file'])) {
            DownloadFile($file[0]);
        } elseif (isset($_POST['view-file'])) {
            ViewFile($file[0]);
        }
    }
}


if (isset($_GET['file']) && !empty($_GET['file']) && is_valid_int($_GET['file'])) {
    $file = SQL_GET_FILE(intval($_GET['file']));
    if (count($file) > 0) {
        if (isset($_GET['download'])) {
            DownloadFile($file[0]);
        } else {
            ViewFile($file[0]);
        }
    }
}

header('Location: index.php?error=file');
