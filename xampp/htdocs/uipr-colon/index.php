<?php

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
    'zh' // chinese.
);

$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);

$GLOBALS['AVAILABLE_LANGS'] = array();

foreach (scandir(__DIR__) as  $item) {
    if (is_dir(__DIR__ . DIRECTORY_SEPARATOR . $item) && in_array($item, $GLOBALS['_LANG'])) {
        $GLOBALS['AVAILABLE_LANGS'][] = $item;
    }
}

$lang = in_array($lang, $GLOBALS['AVAILABLE_LANGS']) ? $lang : 'es';


header("Location: $lang");
