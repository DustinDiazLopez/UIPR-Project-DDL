<?php
include('timepassed.php');

/**
 * Returns html elements to display a warning
 * 
 * @param string $title title of the warning message
 * @param string $msg message of the warning
 * @param bool $showCloseBtn shows a close button (not implemented)
 */
function showWarn($title, $msg, $showCloseBtn = false) 
{
    $ret = "<div class=\"alert alert-warning alert-dismissible fade show\" role=\"alert\">
        <strong>$title</strong> $msg";
    if ($showCloseBtn) {
       return $ret . "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\"><span aria-hidden=\"true\">&times;</span></button></div>";
    } else {
        return $ret . "</div>";
    }
}

/**
 * Returns html elements to display a success message
 * 
 * @param string $head title of the message
 * @param string $msg message
 * @param bool $footer buttom text of the message
 */
function showSuccess($head, $msg, $footer) 
{
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
        if ($result) {
            $fetched = mysqli_fetch_all($result, MYSQLI_ASSOC);
            mysqli_free_result($result);
            return $fetched;
        } else {
            return NULL;
        }
    }
}

define('SQL_ALL_ITEMS', 'SELECT i.id, i.title, t.`type`, i.image_id, i.description, i.create_at, i.published_date, i.year_only FROM item i INNER JOIN `type` t ON i.type_id = t.id ');
define('SQL_GET_FILES', 'SELECT fi.item_id, f.id, f.`path` FROM `file` f inner join file_has_item fi inner join item i on i.id = fi.item_id AND fi.file_id = f.id WHERE i.id = ');
define('SQL_GET_IMAGE', 'SELECT image FROM image where id = ');
define('SQL_GET_FILE', 'SELECT `file` FROM `file` where id = ');
define('SQL_GET_SUBJECTS_BY_ID', 'SELECT s.`subject` FROM `subject` s inner join item_has_subject `is` inner join item i on i.id = `is`.item_id AND `is`.subject_id = s.id WHERE i.id = ');
define('SQL_GET_SUBJECTS', 'SELECT `subject` FROM `subject`');
define('SQL_GET_DOC_TYPES', 'SELECT `id`, `type` FROM `type`');
define('SQL_GET_AUTHORS_BY_ID', "SELECT a.author_name FROM author a inner join author_has_item ai inner join item i on i.id = ai.item_id AND ai.author_id = a.id WHERE i.id = ");
define('SQL_GET_AUTHORS', "SELECT id, author_name FROM author");
define('SQL_GET_AUTHOR_ITEMS', 'SELECT i.id, i.title, t.`type`, i.image_id, i.description, i.meta, i.create_at, i.published_date, i.year_only FROM author_has_item ai INNER JOIN author a INNER JOIN item i INNER JOIN `type` t ON i.type_id = t.id WHERE a.id = author_id AND i.id = item_id AND a.id = ');

define('SQL_GET_PWD_BY_ID', "SELECT `password` FROM `admin` WHERE `id` = ");

define("SQL_FILES_ID_BY_ITEM_ID", "SELECT `file_id` FROM `file_has_item` WHERE `item_id` = ");
define("SQL_SUBJECTS_ID_BY_ITEM_ID", "SELECT `subject_id` FROM `item_has_subject` WHERE `item_id` = ");
define("SQL_AUTHORS_ID_BY_ITEM_ID", "SELECT `author_id` FROM `author_has_item` WHERE `item_id` = ");
define("SQL_IMAGE_ID_BY_ITEM_ID", "SELECT `image_id` FROM `item` WHERE `id` = ");

define("SQL_GET_USER_ID_BY_UE", "SELECT `id` FROM `admin` WHERE ");
define('GET_FILES_ITEM_ID', 'SELECT i.id, i.title, t.`type`, i.image_id, i.description, i.create_at, i.published_date, i.year_only FROM `file` f inner join file_has_item fi ON fi.file_id = f.id inner join item i on fi.item_id = i.id INNER JOIN `type` t ON i.type_id = t.id ');

function GET_FILES_ITEM_ID(){
    return query(GET_FILES_ITEM_ID);
}

function SQL_GET_AUTHOR_ITEMS($author_id) {
    return query(SQL_GET_AUTHOR_ITEMS . $author_id);
}
function SQL_FILES_ID_BY_ITEM_ID($id) 
{
    return SQL_FILES_ID_BY_ITEM_ID . $id;
}

function SQL_SUBJECTS_ID_BY_ITEM_ID($id)
{
    return SQL_SUBJECTS_ID_BY_ITEM_ID . $id;
}

function SQL_AUTHORS_ID_BY_ITEM_ID($id)
{
    return SQL_AUTHORS_ID_BY_ITEM_ID . $id;
}

function SQL_IMAGE_ID_BY_ITEM_ID($id)
{
    return SQL_IMAGE_ID_BY_ITEM_ID . $id;
}

function SQL_GET_ALL_ITEMS($append = '')
{
    return query(SQL_ALL_ITEMS . $append);
}

function SQL_GET_FILES($item_id)
{
    $files = array();
    foreach (query(SQL_GET_FILES . " '$item_id'") as $f) {
        $files[] = [
            'id' => $f['id'],
            'path' => $f['path']
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

function SQL_GET_PWD_BY_ID($id)
{
    return query(SQL_GET_PWD_BY_ID . $id);
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

function sql_delete_author($item_id, $author_id)
{
    return "DELETE FROM `author_has_item` WHERE `author_has_item`.`item_id` = $item_id AND `author_has_item`.`author_id` = $author_id";
}

function sql_delete_subject($item_id, $subject_id)
{
    return "DELETE FROM `item_has_subject` WHERE `item_has_subject`.`item_id` = $item_id AND `item_has_subject`.`subject_id` = $subject_id";
}

function sql_delete_file($item_id, $file_id)
{
    return "DELETE FROM `file_has_item` WHERE `file_has_item`.`file_id` = $file_id AND `file_has_item`.`item_id` = $item_id";
}

function sql_delete_image($image_id)
{
    return "DELETE FROM `image` WHERE `image`.`id` = $image_id";
}

function sql_delete_item($item_id)
{
    return "DELETE FROM `item` WHERE `item`.`id` = $item_id";
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

function FORMAT_DATE($date, $yearOnly = false, $locale = 'es', $format = "%e de %B de %Y")
{
    $currentLocale = setlocale(LC_ALL, 0);
    $re = '';
    setlocale(LC_ALL, $locale);
    if ($yearOnly === true || $yearOnly == '1') {
        $re = strftime("%Y", strtotime($date));
    } else {
        $re = strftime($format, strtotime($date));
    }
    setlocale(LC_ALL, $currentLocale);
    return $re;
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

function hint($msg = 'hint', $color = 'green')
{
    echo "<a style=\"color:$color;\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"$msg\"><i class=\"far fa-question-circle\"></i></a>";
}


/**
 * Returns html element with an icon corresponding to the inputted text.
 * 
 * @param string $icon_name name of the icon
 */
function icon($icon_name='') 
{
    switch (strtolower($icon_name)) {
        case "libro":
        case "book":
            return '<i class="fas fa-book"></i>';

        case "novel":
        case "novela":
            return '<i class="fas fa-book-reader"></i>';

        case "arte":
        case "art":
            return '<i class="fas fa-paint-brush"></i>';
            
        case "foto":
        case "photo":
        case "picture":
            return '<i class="far fa-image"></i>';

        case "periódico":
        case "periodico":
        case "newspaper":
            return '<i class="far fa-newspaper"></i>';

        case "revista":
        case "magazine":
            return '<i class="fas fa-book-open"></i>';

        case "document":
        case "documento":
            return '<i class="fas fa-file-invoice"></i>';

        case "word":
        case "word document":
        case "doc":
        case "docx":
            return '<i class="far fa-file-word"></i>';
        case "ppt":
        case "pptx":
        case "powerpoint":
        case "powerpoint presentation":
            return '<i class="far fa-file-powerpoint"></i>';

        case "excel":
        case "xlsx":
        case "xls":
        case "excel spreadsheet":
            return '<i class="far fa-file-excel"></i>';

        case "csv":
        case "comma-separated values":
        case "comma separated values":
            return '<i class="fas fa-file-csv"></i>';

        case "pdf":
            return '<i class="fas fa-file-pdf"></i>';

        case "zip":
        case "archive":
            return '<i class="fas fa-file-archive"></i>';

        case "code":
        case "programming":
            return '<i class="fas fa-file-code"></i>';

        case "video":
        case "movie":
        case "animation":
            return '<i class="far fa-file-video"></i>';

        case "audio":
        case "song":
        case "music":
            return '<i class="far fa-file-audio"></i>';

        case "media":
            return '<i class="fas fa-photo-video"></i>';

        case "atlas":
        case "map":
            return '<i class="fas fa-atlas"></i>';

        case "bible":
            return '<i class="fas fa-bible"></i>';

        case "quran":
            return '<i class="fas fa-quran"></i>';

        case "torah":
            return '<i class="fas fa-torah"></i>';

        default:
            return '<i class="far fa-file-alt"></i>';
    }
}

function SQL_GET_USER_ID_BY_UE($user_or_email)
{
    return query(SQL_GET_USER_ID_BY_UE . "`email` = '$user_or_email' OR `username` = '$user_or_email'");

}

/**
 * Applies a one-way hash to the inputted text, and removes the salt information. 
 * (The salt is specified in the config folder)
 * 
 * @param string $text the text to hash. 
 */
function ddl_hash($text) {
    global $config;
    return str_replace($config['salt'], '', crypt($text, $config['salt']));
}

/**
 * Applies a one-way hash to the inputted text, and removes the salt information. 
 * (The salt is specified in the config folder)
 * 
 * @param string $pwd non-hashed password 
 * @param int $user_id The id of the user in the database
 */
function ddl_comp_pwd($pwd, $user_id) {
    global $config;
    $query = SQL_GET_PWD_BY_ID($user_id);
    if (count($query) > 0 && isset($query[0]['password'])) {
        $hashed_password = $config['salt'] . $query[0]['password'];
        return hash_equals($hashed_password, crypt($pwd, $hashed_password));
    } else {
        return FALSE;
    }
}

function str_replace_first($from, $to, $content)
{
    $from = '/' . preg_quote($from, '/') . '/';

    return preg_replace($from, $to, $content, 1);
}

function clean($string)
{
    return str_replace('~~~', ' ', preg_replace('/[^A-Za-z0-9\-]/', ' ', str_replace(' ', '~~~', $string))); // Removes special chars.
}

function search($conn, $q, $only='all') {

    $keyword = mysqli_real_escape_string($conn, $q);
    $clean_keyword = clean($keyword);
    $clean_sep = explode(' ', $clean_keyword);


    $only = strtolower($only);

    $q_types = query(SQL_GET_DOC_TYPES);
    $types = '';
    foreach ($q_types as $type) {
        if (isset($_GET[$type['type']])) {
            $types .= ' OR (`t`.`id` = ' . $type['id'] . ') ';
        }
    }

    $types = empty($types) ? "" : str_replace_first('OR', 'WHERE (', $types . ') ');
    $sql = SQL_ALL_ITEMS . $types;

    $query = array();
    switch (strtolower($only)) {
        case 'título':
        case 'titulo':
        case 'title':
            $query = query($sql . " AND (`i`.`title` LIKE '%{$keyword}%')");

            if (count($query) == 0) {
                $query = query($sql . " AND (`i`.`title` LIKE '%{$clean_keyword}%')");
            }
            break;
        case 'meta':
        case 'metadata':
        case 'description':
        case 'descripcion':
        case 'descripción':
            $query = query($sql . " AND (`i`.`description` LIKE '%{$keyword}%' OR `i`.`meta` LIKE '%{$keyword}%')");

            if (count($query) == 0) {
                $query = query($sql . " AND (`i`.`description` LIKE '%{$clean_keyword}%' OR `i`.`meta` LIKE '%{$clean_keyword}%')");
            }
            break;
        case 'archivo':
        case 'file':
            $query = query(GET_FILES_ITEM_ID . $types . " AND (`f`.`path` LIKE '%{$keyword}%')");

            if (count($query) == 0) {
                $query = query(GET_FILES_ITEM_ID . $types . " AND (`f`.`path` LIKE '%{$clean_keyword}%')");
            }

            $query = array_unique($query);

            break;
        default:
            $query =
                query($sql . " AND 
            (`i`.`description` LIKE '%{$keyword}%' 
            OR `i`.`meta` LIKE '%{$keyword}%' 
            OR `i`.`title` LIKE '%{$keyword}%'
            OR `i`.`id` LIKE '%{$keyword}%'
            OR `i`.`create_at` LIKE '%{$keyword}%'
            OR `i`.`published_date` LIKE '%{$keyword}%'
            OR `i`.`year_only` LIKE '%{$keyword}%'
            )");

            if (count($query) == 0) {
                $query = query($sql . " AND 
            (`i`.`description` LIKE '%{$clean_keyword}%' 
            OR `i`.`meta` LIKE '%{$clean_keyword}%' 
            OR `i`.`title` LIKE '%{$clean_keyword}%'
            OR `i`.`id` LIKE '%{$clean_keyword}%'
            OR `i`.`create_at` LIKE '%{$clean_keyword}%'
            OR `i`.`published_date` LIKE '%{$clean_keyword}%'
            OR `i`.`year_only` LIKE '%{$clean_keyword}%'
            )");
            }

            if (count($query) == 0) {
                $query = array();
                $sql_2 = $sql . ' AND (';
                $clean_sep = array_unique($clean_sep);

                for ($i = 0; $i < count($clean_sep); $i++) {
                    if (empty($clean_sep[$i])) {
                        unset($clean_sep[$i]);
                    }
                }

                var_dump($clean_sep);
                echo '<hr>';

                foreach ($clean_sep as $word) {
                    $sql_2 .= "
                        (`i`.`description` LIKE '%{$word}%' 
                        OR `i`.`meta` LIKE '%{$word}%' 
                        OR `i`.`title` LIKE '%{$word}%'
                        OR `i`.`id` LIKE '%{$word}%'
                        OR `i`.`create_at` LIKE '%{$word}%'
                        OR `i`.`published_date` LIKE '%{$word}%'
                        OR `i`.`year_only` LIKE '%{$word}%'
                        ) OR";
                }

                $sql_2 = rtrim($sql_2, 'OR') . ')';

                $query = query($sql_2);

            }
            
            break;
    }

    if (count($query) == 0) {
        return NULL;
    }

    echo '<p style="color:red;">' . mysqli_error($conn) . '</p>';
    return $query;
}