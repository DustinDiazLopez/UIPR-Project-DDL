<?php

include_once('sql.const.php');
include_once('sql.operations.php');
include_once('sql.utils.php');

define('APP_NAME', 'Catálogo UIPR CMS');

/**
 * Checks to see if the user has logged in, if not redirects to the login page, and if the user had tried to access a
 * url they will be redirected to that url once they've logged in.
 * @param float|int $secondsOfInactivity (optional) seconds to wait to log out user (default: 1 hour, i.e., 3600 seconds)
 */
function authenticate($secondsOfInactivity=3600)
{
    $current_path = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

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
 * Validates a date given a format.
 * @param string $date the date
 * @param string $format the expected format
 * @return bool returns <b>TRUE</b> if the date is valid, or <b>FALSE</b> if it is not.
 */
function validateDate($date, $format = 'Y-m-d')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

/**
 * Validates a post key (all values are expected to be strings, if they are set a {@link trim()} will be applied).
 * @param &$POST array the array containing the key
 * @param $key string the key to check if is set in the array
 * @param $alt_key string the title to show in the error message
 * @param &$is_valid boolean is valid boolean variable
 * @param &$error_buffer array the array that will contain all the errors (it will set the key with the a msg as the value)
 * @param $head_msg string (optional) the message to display (will use alt_key)
 * @param $empty_msg string (optional) the message to display when only space is provided
 */
function validate_ddl(&$POST, $key, $alt_key, &$is_valid, &$error_buffer, $head_msg="Favor de proveer",
                      $empty_msg="debe proveer al menos un cáracter, no espacios en blanco")
{
    // checks if the key is not set
    if (!isset($POST[$key])) {
        // adds a message to the buffer (array)
        $error_buffer[$key] = "$head_msg: $alt_key.";
        // logs error to the console
        error_log($error_buffer[$key]);
        // sets the reference boolean
        $is_valid = false;
    } else {
        // trims any spacial padding
        $POST[$key] = trim($POST[$key]);

        // checks to see if it's an empty string
        if (empty($POST[$key])) {
            // adds a message to the buffer (array)
            $error_buffer[$key] = "$head_msg: $alt_key ($empty_msg).";
            // logs error to the console
            error_log($error_buffer[$key]);
            // sets the reference boolean
            $is_valid = false;
        } else {
            // sets the reference boolean
            $is_valid = true;
        }
    }
}


/**
 * Tests to see if the image is set, if it is processes it accordingly (expects image to be a base64 object,
 * <code>data:image/[...];base64,[...]</code>).
 * @param $POST array the array containing the image
 * @param $key string [optional] the key value of the image in the array (default is 'image')
 * @return array|null returns null if an error occurs or the key is not set or empty. returns an array containing the
 * <code>content</code> (i.e., the image binary data),  <code>image_size</code>, <code>image_type</code>
 * (mime type, e.g., <code>image/jpeg</code>), and the original base64 string <code>base64</code>.
 */
function validate_ddl_image(&$POST, &$is_valid, $key='image')
{
    // checks to see if the key is set in the array and that it is not empty
    // no trim operation (since it has to be an image for it to return something)
    if (isset($POST[$key]) && !empty($POST[$key])) {

        if (function_exists('getimagesize')) {
            // default image mime type
            $image_info = ['mime' => 'image/jpeg'];

            // uses an available function in php for the image mime
            if (function_exists('getimagesize')) {
                $image_info = getimagesize($POST[$key]);
            } elseif (function_exists('mime_content_type')) {
                $image_info = ['mime' => mime_content_type($POST[$key])];
            }

            // checks if the function returned false (on failure) and if the mime is set on the returned object.
            if ($image_info && isset($image_info["mime"])) {
                $is_valid = true;
                // sets the mime
                $image_type = $image_info["mime"];
                // decodes the image to binary
                $image = base64_decode(str_replace("data:$image_type;base64,", '', $POST[$key]));
                // gets the image size
                $image_size = strlen($image);

                return [
                    'content' => $image,
                    'image_size' => $image_size,
                    'image_type' => $image_type
                ];
            }
        }
    }
    return NULL;
}

/**
 * Validates to see if the filenames are in the array and checks if the tmp_name is set and not <b>NULL</b>.
 * @param $FILES array the array containing the files (<b>$_FILES</b>)
 * @param $file_names array an array with the file names.
 * @param &$error_buffer string where the errors will be appended (they will be separated by a ~)
 * @return array|null returns <b>NULL</b> on an error (error should be appended to <b>&$error_buffer</b>)
 * returns an <b>array</b> containing the <code>file_name</code>, <code>tmp_path</code>, <code>size</code>,
 * and <code>type</code>.
 */
function validate_ddl_files($FILES, $file_names, &$error_buffer)
{
    $files = array();
    $form_file_names_count = count($file_names);
    for ($i = 0; $i < $form_file_names_count; $i++) {
        // gets the submit file name
        $file_name = $file_names[$i];

        // checks to see if the file name is set on the array
        if (isset($FILES[$file_name])) {
            // gets the file associated with the name
            $file = $FILES[$file_name];

            // checks to see if the file has a temp location (or if it is empty)
            if (isset($file['tmp_name']) && !empty($file['tmp_name'])) {
                $files[] = [
                    'file_name' => $file['name'],
                    'tmp_path' => $file['tmp_name'],
                    'size' => $file['size'],
                    'type' => $file['type']
                ];
            } else {
                // file tmp path is empty or was not set!
                $msg = "~The file submitted with id '$file_name' is set, but has an invalid file location";
                $error_buffer .= $msg;
                error_log($msg);
            }
        } else {
            // file with name is not set
            $msg = "~A file submitted with id '$file_name' is not set!";
            $error_buffer .= $msg;
            error_log($msg);
        }
    }

    $files_count = count($files);

    // few checks
    if ($form_file_names_count == $files_count) {
        if ($files_count > 0) {
            return $files;
        } else {
            $msg = "~No files.";
            $error_buffer .= $msg;
            error_log($msg);
        }
    } else {
        $msg = "~Mismatch file count (submitted: $files_count files, pre-processed successfully: $files_count)";
        $error_buffer .= $msg;
        error_log($msg);
    }

    return NULL;
}

/**
 * Generates the file id names for the array (e.g., file-1, file-2, file-3, ...), and calls on
 * {@link validate_ddl_files()} with the new generated names. Any errors will be appended on the error buffer.
 * @param $FILES array the array containing the files ($_FILES)
 * @param &$error_buffer string where the errors will be appended (they will be separated by a ~)
 * @param $key_format string (optional) the format of the key value (default is 'file-%d')
 * @return array|null see {@link validate_ddl_files()}
 */
function validate_files_form_ddl($FILES, &$error_buffer, $key_format='file-%d')
{
    $files = array();
    $i = 0;
    do {
        // formats the name
        $name = sprintf($key_format, ++$i);
        // checks to see if it is set in the array
        if (isset($FILES[$name])) {
            // adds the name in the array of files (name)
            $files[] = $name;
        } else {
            // breaks when the file with the name is not set in the array
            break;
        }
    } while($i <= 100); // hard stop on sub 100 files (should never happen)

    $len = count($files);
    if ($len > 0) {

        if (isset($_POST['number-of-files']) && (intval($_POST['number-of-files']) !== $len)) {
            error_log('Number of files specified do not match actual number. This value is not longer used the number of files are calculated automatically.');
        }

        return validate_ddl_files($FILES, $files, $error_buffer);
    } else {
        $msg = '~No files were submitted';

        $error_buffer .= $msg;
        error_log($msg);
        return NULL;
    }
}

/**
 * It parses ({@link explode}s) the csv as an array, {@link trim}s the values, and removes ({@link array_unique}) the duplicate values.
 * @param $str string the csv
 * @return string[]|null
 * returns <b>NULL</b> if the count is less than 0
 * returns an array of the values
 */
function split_clean_array_ddl($str)
{
    $arr = explode(',', $str);
    for ($i = 0; $i < count($arr); $i++) $arr[$i] = htmlspecialchars(trim($arr[$i]));
    $arr = array_unique($arr);
    return count($arr) > 0 ? $arr : NULL;
}

/**
 * Validates a <b>$_POST</b> value (a csv value)
 * @param $key string the key value in the <b>$_POST</b> and the <b>&$error_buffer</b>
 * @param $alt_key string the name that will show up in the <b>&$error_buffer</b>
 * @param &$is_valid boolean a refernce to a boolean to update weather the key->value was valid
 * @param &$error_buffer array where to store the error (if it happens)
 * @return string[]|null
 * returns the array of strings on success
 * returns <b>NULL</b> of an error occurred or didn't pass the checks
 */
function validate_post_csv ($key, $alt_key, &$is_valid, &$error_buffer)
{
    if (isset($_POST[$key])) {
        $obj = split_clean_array_ddl($_POST[$key]);
        if ($obj === NULL) {
            $is_valid = FALSE;
            $error_buffer[$key] .= "Provee al menos un $alt_key";
        } else {
            return $obj;
        }
    } else {
        $is_valid = FALSE;
        $error_buffer[$key] .= "Provee al menos un $alt_key";
    }
    return NULL;
}

/**
 * Either prints out 'is-valid' for when <b>TRUE</b> is passed, prints out 'is-invalid' for when <b>FALSE</b> is
 * passed, or does nothing if anything else is passed.
 * @param boolean|string $boolean the choice
 */
function not_valid_class($boolean = 'do nothing')
{
    if ($boolean === true) echo 'is-valid';
    elseif ($boolean === false) echo 'is-invalid';
}

/**
 * Prints an invalid feedback.
 * @param boolean $boolean weather to print it or not
 * @param string $msg the message to display
 */
function echo_invalid_feedback($boolean = false, $msg = 'Invalido')
{
    if ($boolean) echo "<div class=\"invalid-feedback\">$msg</div>";
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
 * Executes the SQL command inputted (uses global $conn). Please see {@link mysqli_query()}
 * @param string $sql SQL command or script.
 * @return array|bool|null
 * returns NULL if an error occurs while fetching the objects (unlikely)
 * returns true or false on success or failure
 * returns the array of objects fetched.
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
            if ($result) {
                if ($result === TRUE || $result === FALSE) {
                    return $result;
                } else {
                    return mysqli_fetch_all($result, MYSQLI_ASSOC);
                }
            } else {
                return NULL;
            }
        }
    } else {
        $result = mysqli_query($conn, $sql);
        if ($result) {
            if ($result === TRUE || $result === FALSE) {
                return $result;
            } else {
                return mysqli_fetch_all($result, MYSQLI_ASSOC);
            }
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
        if(!function_exists('hash_equals')) { /*for php 5.5*/
            function hash_equals($str1, $str2) {
                if(strlen($str1) != strlen($str2)) {
                    return false;
                } else {
                    $res = $str1 ^ $str2;
                    $ret = 0;
                    for($i = strlen($res) - 1; $i >= 0; $i--) $ret |= ord($res[$i]);
                    return !$ret;
                }
            }
        } else return hash_equals($hashed_password, crypt($pwd, $hashed_password));
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
