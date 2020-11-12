<?php
// this file is included in utils.php

/**
 * Replaces the first matching prefix of a string.
 *
 * @param string $prefix The prefix to change.
 * @param string $to The value to replace the prefix with.
 * @param string $string the string to apply this change
 * @return string|string[]|null returns the new string anything else if something went wrong.
 */
function strReplaceFirstStr($prefix, $to, $string)
{
    $prefix = '/' . preg_quote($prefix, '/') . '/';
    return preg_replace($prefix, $to, $string, 1);
}

/**
 * Removes all special characters in a string.
 *
 * @param string $string the string to clean.
 * @return string|string[] returns the cleaned string, or anything else if something went wrong.
 */
function cleanStr($string)
{
    return str_replace(
        '~~~', ' ', preg_replace(
            '/[^A-Za-z0-9\-]/', ' ', str_replace(' ', '~~~', $string)
        )
    ); // Removes special chars.
}

/**
 * Searches the database for a matching item.
 *
 * @param mysqli $conn The mysqli connection (the link).
 * @param string $q The text to find in the items.
 * @param string $only (optional) what attribute to use for the search [title, metadata, file, all], all by default.
 * @return array|null returns the result as an associative array or null if it couldn't find anything.
 */
function search($conn, $q, $only='all') {

    $keyword = mysqli_real_escape_string($conn, $q);
    $clean_keyword = cleanStr($keyword);
    $clean_sep = explode(' ', $clean_keyword);


    $only = strtolower($only);

    $q_types = query(SQL_GET_DOC_TYPES);
    $types = '';
    foreach ($q_types as $type) {
        if (isset($_GET[$type['type']])) {
            $types .= ' OR (`t`.`id` = ' . $type['id'] . ') ';
        }
    }

    $types = empty($types) ? "" : strReplaceFirstStr('OR', 'WHERE (', $types . ') ');
    $sql = SQL_ALL_ITEMS . $types;

    switch (strtolower($only)) {
        case 'título':
        case 'titulo':
        case 'title':

            $query = query($sql . " AND (`i`.`title` LIKE '%{$keyword}%')");

            if (count($query) == 0) {
                $query = query($sql . " AND (`i`.`title` LIKE '%{$clean_keyword}%')");
            }

            if (count($query) == 0) {
                $append = ' AND (';
                $clean_sep = array_unique($clean_sep);
                foreach ($clean_sep as $word) {
                    $append .= "(`i`.`title` LIKE '%{$word}%') AND ";
                }
                $query = query($sql . $append . ' TRUE) ORDER BY `i`.`title` ASC');
                //$query = array_unique($query);
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
            $query = query(GET_FILES_ITEM_ID . $types . " AND (`f`.`filename` LIKE '%{$keyword}%')");

            if (count($query) == 0) {
                $query = query(GET_FILES_ITEM_ID . $types . " AND (`f`.`filename` LIKE '%{$clean_keyword}%')");
            }

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
                $sql_2 = $sql . ' AND (';
                $clean_sep = array_unique($clean_sep);

                for ($i = 0; $i < count($clean_sep); $i++) {
                    if (empty($clean_sep[$i])) {
                        unset($clean_sep[$i]);
                    }
                }

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

            $files_query = query(GET_FILES_ITEM_ID . $types . " AND (`f`.`filename` LIKE '%{$keyword}%')");

            if (count($files_query) == 0) {
                $files_query = query(GET_FILES_ITEM_ID . $types . " AND (`f`.`filename` LIKE '%{$clean_keyword}%')");
            }

            $query = array_unique(array_merge($query, $files_query));

            break;
    }

    if (count($query) == 0) return NULL;
    echo '<p style="color:red;">' . mysqli_error($conn) . '</p>';
    return $query;
}
