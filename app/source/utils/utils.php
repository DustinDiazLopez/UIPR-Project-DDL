<?php

include_once('sql.const.php');
include_once('sql.operations.php');
include_once('sql.utils.php');

define('DDL_VERSION', 'v1.0');

/**
 * Checks to see if the user has logged in, if not redirects to the login page, and if the user had tried to access a
 * url they will be redirected to that url once they've logged in.
 * @param boolean $allowGuests (optional) whether to allow guests on the page where this function is specified.
 * @author Dustin Díaz
 */
function authenticate($allowGuests=FALSE)
{
    $current_path = rawurlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");

    if (session_status() == PHP_SESSION_NONE) session_start();
    if (isset($_SESSION['session_started'])){
        $expirationTime = isset($GLOBALS['secondsOfInactivity']) ? $GLOBALS['secondsOfInactivity'] : 3600;

        if ((mktime() - $_SESSION['session_started'] - $expirationTime) > 0){
            if (!($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($_SERVER['REQUEST_URI'], 'add.php'))) {
                header("Location: logout.php?se&noauth=$current_path");
            } else {
                $_SESSION['session_started'] = mktime();
            }
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

    if (isset($_SESSION['guest']) && $_SESSION['guest'] === TRUE && $allowGuests === FALSE) {
        header("Location: index.php?error=403");
    }
}

/**
 * Redirects to a location, only to host
 * @param string $redir_loc the encoded url
 * @author Dustin Díaz
 */
function redir($redir_loc='')
{
    $redir_loc = rawurldecode($redir_loc);

    if (isset($_SESSION['redir']) && !empty($_SESSION['redir'])) {
        $redir_loc = trim($_SESSION['redir']);
    }

    if (strpos($redir_loc, $_SERVER['HTTP_HOST']) === FALSE) {
        if (empty($redir_loc)) {
            header("Location: index.php");
        } else {
            header("Location: index.php?error=invalidurl");
        }
    } else {
        header("Location: $redir_loc");
    }
}

/**
 * Validates a date given a format.
 * @param string $date the date
 * @param string $format the expected format
 * @return bool returns <b>TRUE</b> if the date is valid, or <b>FALSE</b> if it is not.
 * @author Dustin Díaz
 */
function validateDate($date, $format = 'Y-m-d')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}


/**
 * Tests to see if the image is set, if it is processes it accordingly (expects image to be a base64 object,
 * <code>data:image/[...];base64,[...]</code>).
 * @param $POST array the array containing the image
 * @param &$is_valid boolean
 * @param $key string [optional] the key value of the image in the array (default is 'image')
 * @return array|null returns null if an error occurs or the key is not set or empty. returns an array containing the
 * <code>content</code> (i.e., the image binary data),  <code>image_size</code>, <code>image_type</code>
 * (mime type, e.g., <code>image/jpeg</code>), and the original base64 string <code>base64</code>.
 * @author Dustin Díaz
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

                if (strStartsWith('data:', $image) && strEndsWith('.jpg', $image)) {
                    $is_valid = FALSE;
                    return NULL;
                }

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
 * Escaped a string for it be be safely stored in the database
 * @param $str string the srtring with the possible SQL Injection keywords
 * @return string returns the database safe string
 * @author Dustin Díaz
 */
function escapeMySQL($str) {
    global $conn;
    return mysqli_real_escape_string($conn, $str);
}

/**
 * It parses ({@link explode}s) the csv as an array, {@link trim}s the values, and removes ({@link array_unique}) the duplicate values.
 * @param $str string the csv
 * @return string[]|null
 * returns <b>NULL</b> if the count is less than 0
 * returns an array of the values
 * @author Dustin Díaz
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
 * @author Dustin Díaz
 */
function validate_post_csv ($key, $alt_key, &$is_valid, &$error_buffer)
{
    if (isset($_POST[$key])) {
        $obj = split_clean_array_ddl($_POST[$key]);
        if ($obj === NULL) {
            $is_valid = FALSE;
            $error_buffer[$key] .= "Provide at least one $alt_key";
        } else {
            return $obj;
        }
    } else {
        $is_valid = FALSE;
        $error_buffer[$key] .= "Provide at least one $alt_key";
    }
    return NULL;
}

/**
 * Either prints out 'is-valid' for when <b>TRUE</b> is passed, prints out 'is-invalid' for when <b>FALSE</b> is
 * passed, or does nothing if anything else is passed.
 * @param boolean|string $boolean the choice
 * @author Dustin Díaz
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
 * @author Dustin Díaz
 */
function echo_invalid_feedback($boolean = false, $msg = 'Invalid')
{
    if ($boolean) echo "<div class=\"invalid-feedback\">$msg</div>";
}

/**
 * Tests weather the variable is an integer.
 * @param string|integer $id variable to test
 * @return bool returns true if the input is an integer
 * @author Dustin Díaz
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
 * @author Dustin Díaz
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
 * @author Dustin Díaz
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
 * @author Dustin Díaz
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
 * @author Dustin Díaz
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
 * @author Dustin Díaz
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
 * @author Dustin Díaz
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
 * @author Dustin Díaz
 */
function authorsToCSV($authors, $atr='author_name')
{
    $str = '';
    $len = count($authors);
    for ($i = 0; $i < $len; $i++) {
        $str = $str . trim($authors[$i][$atr]);
        if ($i != $len - 1) $str = $str . ', ';
    }

    return $str;
}

function addAndToEndOfCSV($csv) {
    switch (LANG) {
        case 'es':
            $subject = ' y ';
            break;
        case 'en':
            $subject = ', and ';
            break;
        default:
            return $csv;
    }


    return strReplaceLastStr(', ', $subject, $csv);
}

/**
 * Formats an inputted date into a easily (intuitively) readable format.
 * @param string $date date to format (e.g., 10-30-2020, could also be in another format).
 * @param false $yearOnly (optional) shows the year only
 * @param string $format (optional) format of the date to return {@link strftime()}
 * @return string returns the formatted date as a string.
 * @author Dustin Díaz
 */
function formatDate($date, $yearOnly = false, $format = "%B %e, %Y")
{
    $currentLocale = setlocale(LC_ALL, 0);
    setlocale(LC_ALL, LANG);
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
 * @author Dustin Díaz
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
 * @author Dustin Díaz
 */
function hint($msg, $color = 'green')
{
    echo "<a style=\"color:$color;\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"$msg\"><i class=\"far fa-question-circle\"></i></a>";
}

/**
 * see {@link redir_fatal_error()}
 * @param string $title the title of the failure
 * @param string $msg the message of the failure
 * @param string $username the existing username
 * @param string $email the existing email
 * @author Dustin Díaz
 */
function redir_error($title, $msg, $username, $email)
{
    redir_fatal_error($title, "$msg&username=$username&email=$email");
}

/**
 * Redirects back to a location with messages on the url (needs to be handled, refer to adminpanel.php)
 * @param string $title title of the error
 * @param string $msg the message
 * @param string $location location to redirect
 * @author Dustin Díaz
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
 * @author Dustin Díaz
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
 * @author Dustin Díaz
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
 * @return string|string[]|null returns the hashed version of the text, and returns anything else if something went
 * wrong.
 * @author Dustin Díaz
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
 * @author Dustin Díaz
 */
function ddl_comp_pwd($pwd, $user_id)
{
    global $config;
    $query = SQL_GET_PWD_BY_ID($user_id);
    if (count($query) > 0 && isset($query[0]['password'])) {
        $hashed_password = $config['salt'] . $query[0]['password'];

        // hash_equals does not exists for php5.5
        if(!function_exists('hash_equals')) {
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
        }

        return hash_equals($hashed_password, crypt($pwd, $hashed_password));
    } else {
        return NULL;
    }
}

/**
 * @param float $var the value to round
 * @param int $places the number of places to round to.
 * @return float|int
 * Returns a float of the rounded number
 * Returns an integer when the round value ($places) is 1
 * @author Dustin Díaz
 */
function round_ddl($var, $places=2)
{
    if ($places < 0) $places = 0;
    $round_val = 1;
    for ($i = 1; $i <= $places; $i++) $round_val *= 10;
    return round($var * $round_val) / $round_val;
}


/**
 * Encrypts text
 * @param string $plaintext the text to encrupt
 * @param string $cipher the encryption method to use
 * @return string returns the encrypted text encoded in base64
 * @author Dustin Díaz
 */
function encrypt($plaintext, $cipher="AES-128-CBC")
{
    global $config;
    $key = $config['salt'];
    $ivlen = openssl_cipher_iv_length($cipher);
    $iv = openssl_random_pseudo_bytes($ivlen);
    $ciphertext_raw = openssl_encrypt($plaintext, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
    $hmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary=true);
    return base64_encode( $iv.$hmac.$ciphertext_raw );

}

/**
 * Decrypts text
 * @param string $ciphertext the base64 encoded ciphered text
 * @param string $cipher the encryption method used
 * @return false|string
 * returns <b>FALSE</b> is the comparison fails
 * or returns the deciphered text
 * @author Dustin Díaz
 */
function decrypt($ciphertext, $cipher="AES-128-CBC")
{
    global $config;
    $key = $config['salt'];
    $ivlen = openssl_cipher_iv_length($cipher);
    $c = base64_decode($ciphertext);
    $iv = substr($c, 0, $ivlen);
    $hmac = substr($c, $ivlen, $sha2len=32);
    $ciphertext_raw = substr($c, $ivlen+$sha2len);
    $calcmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary=true);

    //PHP 5.6+ timing attack safe comparison
    return hash_equals($hmac, $calcmac)
        ? openssl_decrypt($ciphertext_raw, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv)
        : FALSE;

}

$url_share = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$head_share='head-';

/**
 * Generates the URL to the file or item to be shared.
 * @param $id string|int the id of the item or file
 * @param string $path the sub-path to the file or item
 * @return string the complete url path to the item or file
 * @author Dustin Díaz
 */
function shareURL($id, $path='/file.php?file=') {
    global $head_share;
    global $url_share;
    $url = $url_share;


    $cut = strpos($url, '?');
    if ($cut !== FALSE) {
        $url = substr($url, 0, $cut);
    }

    $cut = strpos($url, 'file.php');
    if ($cut !== FALSE) {
        $url = substr($url, 0, $cut);
    }

    $cut = strpos($url, 'index.php');
    if ($cut !== FALSE) {
        $url = substr($url, 0, $cut);
    }

    $cut = strpos($url, 'adminpanel.php');
    if ($cut !== FALSE) {
        $url = substr($url, 0, $cut);
    }

    $basename = basename($_SERVER['REQUEST_URI'], '?');

    if (strpos($basename, '.php')) {
        $pos2 = strpos($url, $basename);
        if ($pos2 !== FALSE) {
            $url = substr($url, 0, $pos2);
        }
    }

    if (strEndsWith($url, '/'))  {
        $url = substr($url, 0, strlen($url) - 1);
    }

    $encoded_id = urlencode(base64_encode($head_share . $id));
    return $url.$path.$encoded_id;
}

/**
 * Removes tags from the inputted HTML text.
 * @param $html string The HTML text
 * @param string[] $tags the tags to remove from the HTML
 * @return string returns the cleaned HTML
 * @author Dustin Díaz
 */
function cleanHTML ($html, $tags=['script','style','object','iframe','embed','button','input','canvas','h1','h2','h3', 'img', 'picture', 'textarea', 'video']) {
    $dom = new DOMDocument();
    $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
    $remove = [];
    foreach ($tags as $tag) {
        $script = $dom->getElementsByTagName($tag);
        foreach($script as $item) $remove[] = $item;
    }
    // remove all tags
    foreach ($remove as $item) $item->parentNode->removeChild($item);

    $rms = ['onclick=\'', 'onclick="', 'href=\'javascript:', 'href="javascript:'];

    return str_replace($rms, '', $dom->saveHTML());
}

/**
 * Returns html string with an icon corresponding to the inputted text (look at source code for the options).
 * <p>If it keeps returning the same icon, and you are on Linux (LAMP) you have to install {@link mb_strtolower}
 * with this command: <br>'<code>sudo apt-get install php7.4-mbstring</code>' (will require a reboot of <b>PHP</b> or
 * <b>Apache</b>, e.g., '<code>sudo systemctl restart apache2</code>').
 *
 * @param string $icon_name (optional) name of the icon
 * @return string returns an italic tag with the appropriate font
 * @author Dustin Díaz
 */
function icon($icon_name='') 
{
    if(function_exists('mb_strtolower')) {
        switch (mb_strtolower($icon_name, 'UTF-8')) {
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
            case "codigo":
            case "código":
            case "programming":
                return '<i class="fas fa-file-code"></i>';

            case "video":
            case "vídeo":
            case "película":
            case "pelicula":
            case "movie":
            case "animation":
                return '<i class="far fa-file-video"></i>';

            case "audio":
            case "cancion":
            case "canción":
            case "song":
            case "music":
                return '<i class="far fa-file-audio"></i>';

            case "media":
                return '<i class="fas fa-photo-video"></i>';

            case "atlas":
            case "map":
            case "mapa":
                return '<i class="fas fa-atlas"></i>';

            case "biblia":
            case "bible":
                return '<i class="fas fa-bible"></i>';

            case "coran":
            case "corán":
            case "quran":
                return '<i class="fas fa-quran"></i>';

            case "tora":
            case "torah":
                return '<i class="fas fa-torah"></i>';

            default:
                return '<i class="far fa-file-alt"></i>';
        }
    } else return '<i class="far fa-file-alt"></i>';
}

/**
 * Languages to implement
 */
$GLOBALS['_LANG'] = array(
    'af', // afrikaans.
    'ar', // arabic.
    'bg', // bulgarian.
    'ca', // catalan.
    'cs', // czech.
    'da', // danish.
    'de', // german.
    'el', // greek.
    'en', // english.
    'es', // spanish.
    'et', // estonian.
    'fi', // finnish.
    'fr', // french.
    'gl', // galician.
    'he', // hebrew.
    'hi', // hindi.
    'hr', // croatian.
    'hu', // hungarian.
    'id', // indonesian.
    'it', // italian.
    'ja', // japanese.
    'ko', // korean.
    'ka', // georgian.
    'lt', // lithuanian.
    'lv', // latvian.
    'ms', // malay.
    'nl', // dutch.
    'no', // norwegian.
    'pl', // polish.
    'pt', // portuguese.
    'ro', // romanian.
    'ru', // russian.
    'sk', // slovak.
    'sl', // slovenian.
    'sq', // albanian.
    'sr', // serbian.
    'sv', // swedish.
    'th', // thai.
    'tr', // turkish.
    'uk', // ukrainian.
    'zh'  // chinese.
);

$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);

$GLOBALS['AVAILABLE_LANGS'] = array();

foreach (scandir('.') as  $item) {
    if (is_dir('.' . DIRECTORY_SEPARATOR . $item) && in_array($item, $GLOBALS['_LANG'])) {
        $GLOBALS['AVAILABLE_LANGS'][] = $item;
    }
}

