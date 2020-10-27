<?php
    include_once('connect.php');
    mysqli_close($conn);
    session_start();
    
    if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] === FALSE) {
        header('Location: login.php');
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
            header("Content-Type: $mime_content_type");
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
        // $data = 'data:application/pdf;base64,' . base64_encode(file_get_contents($file));
    }

    header('Location: index.php?error=file');
?>