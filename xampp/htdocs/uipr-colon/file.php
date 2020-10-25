<?php
    include_once('connect.php');
    mysqli_close($conn);

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
            header('Content-Description: View PDF');
            header('Content-Type: application/pdf');
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

    header('Location: index.php');
?>