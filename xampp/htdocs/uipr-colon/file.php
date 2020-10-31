<?php
include_once('connect.php');
include_once('utils/utils.php');
if (isset($conn)) {
    mysqli_close($conn);
} else die("Connection to the database has not been set");

authenticate();

function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    if (!$length) {
        return true;
    }
    return substr($haystack, -$length) === $needle;
}


function DownloadFile($file)
{ 
    if (file_exists($file)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        ob_clean();
        flush();
        readfile($file);
        exit;
    }
}

function ViewFile($file)
{
    if (file_exists($file)) {
        $mime_content_type = mime_content_type($file);
        header('Content-Description: View File');
        header("Content-Type: $mime_content_type; charset=utf-8");
        header('Content-Disposition: inline; filename=' . basename($file));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        ob_clean();
        flush();
        readfile($file);
        exit;
    }
}

if (isset($_POST['file'])) {
    $file = FILE_FOLDER . '/' . $_POST['file'];
    if (isset($_POST['download-file'])) {
        DownloadFile($file);
    } elseif (isset($_POST['view-file'])) {
        ViewFile($file);
    }
}

header('Location: index.php?error=file');
