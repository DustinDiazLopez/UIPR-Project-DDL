<?php
include('timepassed.php');
function showWarn($title, $msg, $showCloseBtn = false) {
    $ret = "<div class=\"alert alert-warning alert-dismissible fade show\" role=\"alert\">
        <strong>$title</strong> $msg";
    if ($showCloseBtn) {
       return $ret . "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\"><span aria-hidden=\"true\">&times;</span></button></div>";
    } else {
        return $ret . "</div>";
    }
}

function showSuccess($head, $msg, $footer) {
    return "<div class=\"alert alert-success\" role=\"alert\"><h4 class=\"alert-heading\">$head</h4><p>$msg</p><hr /><p class=\"mb-0\">$footer</p></div>";
}

function query($sql) 
{
    global $conn;
    if (!$conn) {
        $conn = connect(); 

        if (!$conn) {
            die("Failed to connect to database...");
            return null;
        } else {
            $result = mysqli_query($conn, $sql);
            $fetched = mysqli_fetch_all($result, MYSQLI_ASSOC);
            mysqli_free_result($result);
            mysqli_close($conn);
            unset($conn);
            return $fetched;
        }
    } else {
        $result = mysqli_query($conn, $sql);
        $fetched = mysqli_fetch_all($result, MYSQLI_ASSOC);
        mysqli_free_result($result);
        return $fetched;
    }
}

define('SQL_ALL_ITEMS', 'SELECT i.id, i.title, t.`type`, i.image_id, i.description, i.meta, i.create_at, i.published_date FROM item i INNER JOIN `type` t ON i.type_id = t.id');
define('SQL_GET_FILES', 'SELECT fi.item_id, f.id, f.`file`, f.file_name FROM `file` f inner join file_has_item fi inner join item i on i.id = fi.item_id and fi.file_id = f.id where i.id = ');
define('SQL_GET_IMAGE', 'SELECT image FROM image where id = ');
define('SQL_GET_FILE', 'SELECT `file` FROM `file` where id = ');
define('SQL_GET_SUBJECTS_BY_ID', 'SELECT s.`subject` FROM `subject` s inner join item_has_subject `is` inner join item i on i.id = `is`.item_id and `is`.subject_id = s.id where i.id = ');
define('SQL_GET_SUBJECTS', 'SELECT `subject` FROM `subject`');
define('SQL_GET_DOC_TYPES', 'SELECT `type` FROM `type`');
define('SQL_GET_AUTHORS_BY_ID', "SELECT a.author_name FROM author a inner join author_has_item ai inner join item i on i.id = ai.item_id and ai.author_id = a.id where i.id = ");
define('SQL_GET_AUTHORS', "SELECT author_name FROM author");

function SQL_GET_ALL_ITEMS()
{
    return query(SQL_ALL_ITEMS);
}

function SQL_GET_FILES($item_id)
{
    $files = array();
    foreach (query(SQL_GET_FILES . " '$item_id'") as $f) {
        $files[] = [
            'id' => $f['id'],
            'name' => $f['file_name'],
            'file' => 'data:application/pdf;base64,' . base64_encode($f['file']),
            'size' => strlen($f['file']) / 1e+6
        ];
    }
    
    return $files;
}

function SQL_GET_FILE($file_id)
{
    global $conn;
    $file_id = mysqli_real_escape_string($conn, $file_id);
    return query(SQL_GET_FILE . " '$file_id'");
}

function SQL_GET_SUBJECTS($item_id)
{
    return query(SQL_GET_SUBJECTS_BY_ID . " '$item_id'");
}

function SQL_GET_IMAGE($image_id)
{
    $image = query(SQL_GET_IMAGE . " '$image_id'");
    if (count($image) >= 1) {
        $mime_type = finfo_buffer(finfo_open(), $image[0]['image'], FILEINFO_MIME_TYPE);
        return "data:$mime_type;base64," . base64_encode($image[0]['image']);
    } else return 'images/pdf-placeholder.jpg';

}

function SQL_GET_AUTHORS($item_id)
{
    return query(SQL_GET_AUTHORS_BY_ID . " '$item_id'");
}

function AUTHORS_TO_CSV($authors, $atr)
{
    $str = '';
    $len = count($authors);
    for ($i = 0; $i < $len; $i++) {
        $str = $str . $authors[$i][$atr];
        if ($i != $len - 1) $str = $str . ', ';
    }

    return $str;
}

function FORMAT_DATE($date)
{
    return date("F jS, Y", strtotime($date));
}

function listToCSV($list)
{
    $str = '';
    $len = count($list);
    for ($i = 0; $i < $len; $i++) {
        $str = $str . $list[$i];
        if ($i != $len - 1) $str = $str . ', ';
    }

    return $str;
}

?>