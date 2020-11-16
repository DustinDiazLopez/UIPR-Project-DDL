<?php
include_once('../connect.php');
include_once('utils/utils.php');

function viewFile($file) {
    if(file_exists($file['path'])) {

        header('Content-Description: File Transfer');
        header('Content-Disposition: inline; filename="'.(basename($file['path'])) . '"');
        header('Content-Type: '. $file['type']);
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . $file['size']);

        ob_clean();
        flush();
        readfile($file['path']);
        exit;
    }
}

function downloadFile($file) {
    if(file_exists($file['path'])) {
        header('Content-Description: File Transfer');
        header('Content-Type: '. $file['type']);
        header('Content-Disposition: attachment; filename="'.(basename($file['path'])) . '"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . $file['size']);
        ob_clean();
        flush();
        readfile($file['path']);
        exit;
    }
}



try {
    if ((isset($_GET['file']) && !empty($_GET['file'])) || (isset($_POST['file']) && !empty($_POST['file']))) {
        if (isset($_GET['file'])) {
            $id = explode('-', base64_decode(urldecode($_GET['file'])))[1];
        } else {
            $id = explode('-', base64_decode(urldecode($_POST['file'])))[1];
        }
        $file = SQL_GET_FILE(intval($id));

        if (count($file) > 0) $file = $file[0];
        $file['path'] = PATH_TO_FILES_FOLDER . $file['path'];
        $file['filename'] = htmlspecialchars($file['filename']);
        $file['type'] = mime_content_type($file['path']);
        $file['size'] = filesize($file['path']);

        if (isset($_GET['download'])) {
            downloadFile($file);
        } else {
            viewFile($file);
        }

    } else {
        header('Location: index.php?error=no-pdf');
    }
} catch (Exception $exception) {
    header('Location: index.php?error=no-pdf');
}


