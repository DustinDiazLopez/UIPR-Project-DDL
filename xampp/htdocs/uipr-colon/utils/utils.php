<?php

include_once('sql.const.php');
include_once('sql.operations.php');
include_once('sql.utils.php');

$current_path = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

/**
 * Checks to see if the user has logged in, if not redirects to the login page, or if
 * @param float|int $secondsOfInactivity (optional) seconds to wait to log out user (default: 30 minutes)
 */
function authenticate($secondsOfInactivity=60*30)
{
    if (session_status() == PHP_SESSION_NONE) session_start();
    if (isset($_SESSION['session_started'])){
        if ((mktime() - $_SESSION['session_started'] - $secondsOfInactivity) > 0){
            header("Location: logout.php?se");
        } else {
            $_SESSION['session_started'] = mktime();
        }
    } else {
        $_SESSION['session_started'] = mktime();
    }

    if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] === FALSE) {
        session_destroy();
        if (isset($current_path)) {
            header("Location: login.php?noauth=$current_path");
        } else {
            header("Location: login.php");
        }
    }
}

/**
 * Tests weather the variable is an integer.
 * @param string|integer $id variable to test
 * @return bool returns true if the input is an integer
 */
function is_valid_int($id)
{
    return (isset($id) && !empty($id)) && gettype(intval($id)) === "integer";
}

function clean($string, $replace='')
{
    $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
    return preg_replace('/[^A-Za-z0-9\-]/', $replace, $string); // Removes special chars.
}

/**
 * Tests if this string starts with the specified prefix.
 *
 * @param string $string the string to test
 * @param string $prefix the prefix.
 * @return bool TRUE if the prefix exists in the string.
 */
function strStartsWith($string, $prefix)
{
    return substr($string, 0, strlen($prefix)) === $prefix;
}

/**
 * Tests if this string ends with the specified suffix.
 *
 * @param string $string the string to test
 * @param string $suffix the suffix.
 * @return bool TRUE if the suffix exists in the string.
 */
function strEndsWith($string, $suffix)
{
    $length = strlen($suffix);
    if (!$length)  return true;
    return substr($string, -$length) === $suffix;
}

/**
 * Get the html string to display a warning message.
 *
 * @param string $title title of the warning message
 * @param string $msg message of the warning
 * @param bool $showCloseBtn (optional) weather to show a close button (needs to be enabled with JS).
 * @return string Returns html elements to display a warning
 */
function showWarn($title, $msg, $showCloseBtn = false) 
{
    $ret = "<div class=\"alert alert-warning alert-dismissible fade show\" role=\"alert\"><strong>$title</strong> $msg";
    if ($showCloseBtn) {
       return $ret . "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\"><span aria-hidden=\"true\">&times;</span></button></div>";
    } else {
        return $ret . "</div>";
    }
}

/**
 * Get the html string to display the danger message.
 *
 * @param string $title title of the warning message
 * @param string $msg message of the danger
 * @param bool $showCloseBtn (optional) weather to show a close button (needs to be enabled with JS).
 * @return string Returns html elements to display the danger
 */
function showDanger($title, $msg, $showCloseBtn = false)
{
    $ret = "<div class=\"alert alert-danger alert-dismissible fade show\" role=\"alert\">
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
 * @param string $footer buttom text of the message
 * @return string Returns html elements to display the success
 */
function showSuccess($head, $msg="", $footer="") 
{
    $foot = "";
    if (!empty($footer)) $foot = "<hr /><p class=\"mb-0\">$footer</p>";
    return "<div class=\"alert alert-success\" role=\"alert\"><h4 class=\"alert-heading\">$head</h4><p>$msg</p>$foot</div>";
}

/**
 * Executes the SQL command inputted (uses global $conn).
 * @param string $sql SQL command or script.
 * @return array|null returns an associative array of the results, or NULL if something went wrong.
 */
function query($sql) 
{
    global $conn;
    if (!$conn) {
        $conn = connect(); 

        if (!$conn) {
            die("Failed to connect to database...");
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

/**
 * Returns the csv of a associative array by default for the authors table in the db, but an attribute (key) can be specified.
 * @param array $authors the associative array.
 * @param string $atr (optional) the key value
 * @return string returns the csv of the associative array
 */
function authorsToCSV($authors, $atr='author_name')
{
    $str = '';
    $len = count($authors);
    for ($i = 0; $i < $len; $i++) {
        $str = $str . $authors[$i][$atr];
        if ($i != $len - 1) $str = $str . ', ';
    }

    return $str;
}

/**
 * Formats an inputted date into a easily (intuitively) readable format.
 * @param string $date date to format (e.g., 10-30-2020, could also be in another format).
 * @param false $yearOnly (optional) shows the year only
 * @param string $locale (optional) language to use (e.g., en, es, etc.)
 * @param string $format (optional) format of the date to return
 * @return string returns the formatted date as a string.
 */
function formatDate($date, $yearOnly = false, $locale = 'es', $format = "%e de %B de %Y")
{
    $currentLocale = setlocale(LC_ALL, 0);
    setlocale(LC_ALL, $locale);
    if ($yearOnly === true || $yearOnly == '1') {
        $re = strftime("%Y", strtotime($date));
    } else {
        $re = strftime($format, strtotime($date));
    }
    setlocale(LC_ALL, $currentLocale);
    return $re;
}

/**
 * Returns the array as a csv.
 * @param array $list the array.
 * @return string returns an array as a csv.
 */
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

/**
 * Shows a hint via a tooltip on a icon question mark (needs to be handled, refer to adminpanel.php)
 * @param string $msg The message of the tooltip.
 * @param string $color (optional) the color of the icon.
 */
function hint($msg, $color = 'green')
{
    echo "<a style=\"color:$color;\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"$msg\"><i class=\"far fa-question-circle\"></i></a>";
}

/**
 * Redirects back to a location with messages on the url (needs to be handled, refer to adminpanel.php)
 * @param string $title title of the error
 * @param string $msg the message
 * @param string $location location to redirect
 */
function redir_fatal_error($title, $msg, $location='adminpanel.php')
{
    header("Location: $location?fatalerror=$title&msg=$msg");
}

/**
 * Redirects back to a location with messages on the url (needs to be handled, refer to adminpanel.php)
 * @param string $title title of the error
 * @param string $msg the message
 * @param string $location location to redirect
 */
function redir_warn_error($title, $msg, $location='adminpanel.php')
{
    header("Location: $location?error=$title&msg=$msg");
}

/**
 * Redirects back to a location with messages on the url
 * @param string $title title of the error
 * @param string $msg the message
 * @param string $location location to redirect
 */
function redir_success_error($title, $msg, $location='adminpanel.php')
{
    header("Location: $location?success=$title&msg=$msg");
}


/**
 * Applies a one-way hash to the inputted text, and removes the salt information.
 * (The salt is specified in the config folder)
 *
 * @param string $text the text to hash.
 * @return string|string[]|null returns the hashed version of the text, and returns anything else if something went wrong.
 */
function ddl_hash($text)
{
    global $config;
    return str_replace($config['salt'], '', crypt($text, $config['salt']));
}

/**
 * Applies a one-way hash to the inputted text, and removes the salt information.
 * (The salt is specified in the config folder)
 *
 * @param string $pwd non-hashed password
 * @param int $user_id The id of the user in the database
 * @return bool|null returns weather the passwords match or NULL if the user does not exist.
 */
function ddl_comp_pwd($pwd, $user_id)
{
    global $config;
    $query = SQL_GET_PWD_BY_ID($user_id);
    if (count($query) > 0 && isset($query[0]['password'])) {
        $hashed_password = $config['salt'] . $query[0]['password'];
        return hash_equals($hashed_password, crypt($pwd, $hashed_password));
    } else {
        return NULL;
    }
}

/**
 * Returns html string with an icon corresponding to the inputted text (look at source code for the options).
 *
 * @param string $icon_name (optional) name of the icon
 * @return string returns an italic tag with the appropriate font
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
