<?php

include_once('../utils/utils.php');

/**
 * What will appear in the header and the title of every page.
 */
define('APP_NAME', 'UIPR Catalog CMS');

/**
 * This will be used in the lang attribute of the html pages, and the date of the items.
 */
define('LANG', 'en');


/**
 * Validates a post key (all values are expected to be strings, if they are set a {@link trim()} will be applied).
 * @param &$POST array the array containing the key
 * @param $key string the key to check if is set in the array
 * @param $alt_key string the title to show in the error message
 * @param &$is_valid boolean is valid boolean variable
 * @param &$error_buffer array the array that will contain all the errors (it will set the key with the a msg as the value)
 * @param $head_msg string (optional) the message to display (will use alt_key)
 * @param $empty_msg string (optional) the message to display when only space is provided
 * @author Dustin Díaz
 */
function validate_ddl(&$POST, $key, $alt_key, &$is_valid, &$error_buffer, $head_msg="Missing Information",
                      $empty_msg="must provide at least one character (no blank spaces)")
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
 * Generates the file id names for the array (e.g., file-1, file-2, file-3, ...), and calls on
 * {@link validate_ddl_files()} with the new generated names. Any errors will be appended on the error buffer.
 * @param $FILES array the array containing the files ($_FILES)
 * @param &$error_buffer string where the errors will be appended (they will be separated by a ~)
 * @param $key_format string (optional) the format of the key value (default is 'file-%d')
 * @return array|null see {@link validate_ddl_files()}
 * @author Dustin Díaz
 */
function validate_files_form_ddl($FILES, &$error_buffer, $ignore_no_files = FALSE, $key_format='file-%d')
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
            error_log('Number of files specified do not match actual number. This value is no longer used the number of files are calculated automatically.');
        }

        return validate_ddl_files($FILES, $files, $error_buffer);
    } else {
        if ($ignore_no_files) {
            return array();
        } else {
            $msg = '~No files were submitted';

            $error_buffer .= $msg;
            error_log($msg);
            return NULL;
        }
    }
}

/**
 * Validates to see if the filenames are in the array and checks if the tmp_name is set and not <b>NULL</b>.
 * @param $FILES array the array containing the files (<b>$_FILES</b>)
 * @param $file_names array an array with the file names.
 * @param &$error_buffer string where the errors will be appended (they will be separated by a ~)
 * @return array|null returns <b>NULL</b> on an error (error should be appended to <b>&$error_buffer</b>)
 * returns an <b>array</b> containing the <code>file_name</code>, <code>tmp_path</code>, <code>size</code>,
 * and <code>type</code>.
 * @author Dustin Díaz
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
                    'file_name' => escapeMySQL($file['name']),
                    'tmp_path' => $file['tmp_name'],
                    'path' => ''
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


$GLOBALS['_LANG_ASSOC'] = array(
    'af' => 'afrikaans',    // afrikaans.
    'ar' => 'arabic',       // arabic.
    'bg' => 'bulgarian',    // bulgarian.
    'ca' => 'catalan',      // catalan.
    'cs' => 'czech',        // czech.
    'da' => 'danish',       // danish.
    'de' => 'german',       // german.
    'el' => 'greek',        // greek.
    'en' => 'english',      // english.
    'es' => 'spanish',      // spanish.
    'et' => 'estonian',     // estonian.
    'fi' => 'finnish',      // finnish.
    'fr' => 'french',       // french.
    'gl' => 'galician',     // galician.
    'he' => 'hebrew',       // hebrew.
    'hi' => 'hindi',        // hindi.
    'hr' => 'croatian',     // croatian.
    'hu' => 'hungarian',    // hungarian.
    'id' => 'indonesian',   // indonesian.
    'it' => 'italian',      // italian.
    'ja' => 'japanese',     // japanese.
    'ko' => 'korean',       // korean.
    'ka' => 'georgian',     // georgian.
    'lt' => 'lithuanian',   // lithuanian.
    'lv' => 'latvian',      // latvian.
    'ms' => 'malay',        // malay.
    'nl' => 'dutch',        // dutch.
    'no' => 'norwegian',    // norwegian.
    'pl' => 'polish',       // polish.
    'pt' => 'portuguese',   // portuguese.
    'ro' => 'romanian',     // romanian.
    'ru' => 'russian',      // russian.
    'sk' => 'slovak',       // slovak.
    'sl' => 'slovenian',    // slovenian.
    'sq' => 'albanian',     // albanian.
    'sr' => 'serbian',      // serbian.
    'sv' => 'swedish',      // swedish.
    'th' => 'thai',         // thai.
    'tr' => 'turkish',      // turkish.
    'uk' => 'ukrainian',    // ukrainian.
    'zh' => 'chinese'       // chinese.
);


$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);

$GLOBALS['AVAILABLE_LANGS'] = array();

foreach (scandir('..') as  $item) {
    if (is_dir('..' . DIRECTORY_SEPARATOR . $item) && in_array($item, $GLOBALS['_LANG'])) {
        $GLOBALS['AVAILABLE_LANGS'][] = $item;
    }
}

$lang = in_array($lang, $GLOBALS['AVAILABLE_LANGS']) ? $lang : LANG;
