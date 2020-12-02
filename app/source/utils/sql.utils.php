<?php
// this file is included in utils.php

/**
 * Replaces the first matching prefix of a string.
 *
 * @param string $prefix The prefix to change.
 * @param string $to The value to replace the prefix with.
 * @param string $string the string to apply this change
 * @return string|string[]|null returns the new string anything else if something went wrong.
 * @author Dustin Díaz
 */
function strReplaceFirstStr($prefix, $to, $string)
{
    $prefix = '/' . preg_quote($prefix, '/') . '/';
    return preg_replace($prefix, $to, $string, 1);
}

function strReplaceLastStr($search, $replace, $subject){
    $pos = strrpos($subject, $search);
    if($pos !== false){
        $subject = substr_replace($subject, $replace, $pos, strlen($search));
    }
    return $subject;
}

/**
 * Removes all special characters in a string.
 *
 * @param string $string the string to clean.
 * @return string|string[] returns the cleaned string, or anything else if something went wrong.
 * @author Dustin Díaz
 */
function cleanStr($string)
{
    return trim(
        str_replace(
            '~~~', ' ', preg_replace(
                '/[^A-Za-z0-9\-]/', ' ', str_replace(' ', '~~~', $string)
            )
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
 * @author Dustin Díaz
 */
function search($conn, $q, $only='all') {

    $keyword = mysqli_real_escape_string($conn, $q);
    $clean_keyword = trim($keyword);
    $clean_sep = explode(' ', trim($clean_keyword));
    $clean_sep = array_unique($clean_sep, SORT_REGULAR);

    $clean_sep_count = count($clean_sep);
    if ($clean_sep_count > 0) {
        for ($i = 0; $i < $clean_sep_count; $i++) {
            $str = cleanStr($clean_sep[$i]);
            $arr = explode(' ', $str);
            
            if (count($arr) === 1) {
                $clean_sep[$i] = $str;
            } else {
                $added = FALSE;
                foreach($arr as $partial_word) {
                    if (strlen($partial_word) >= 2) {
                        if (!$added) {
                            $clean_sep[$i] = $partial_word;
                            $added = TRUE;
                        } else {
                            $clean_sep[] = $partial_word;
                        }
                    }
                }
            }
        }

        for ($i = 0; $i < count($clean_sep); $i++) {
            $str = trim($clean_sep[$i]);
            if (!empty($str)) {
                $clean_sep[$i] = $str;
            } 
        }
    } else {
        $clean_sep = [];
    }

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
            $query = query($sql . " AND (`i`.`title` = '$keyword')");
            if (!is_array($query) || (is_array($query) && count($query) == 0)) {
                $search = "(`i`.`title` LIKE '%{$keyword}%')";
                $query = query(str_replace('FROM', ", $search as hits FROM", $sql) . " AND $search ORDER BY hits");
                if (!is_array($query) || (is_array($query) && count($query) == 0)) {
                    $search = "(`i`.`title` LIKE '%{$clean_keyword}%')";
                    $query = query(str_replace('FROM', ", $search as hits FROM", $sql) . " AND $search ORDER BY hits");
                }

                if (!is_array($query) || (is_array($query) && count($query) == 0)) {
                    $append = ' AND (';
                    $clean_sep = array_unique($clean_sep, SORT_REGULAR);
                    foreach ($clean_sep as $word) {
                        $append .= "(`i`.`title` LIKE '%{$word}%') AND ";
                    }
                    $query = query($sql . $append . ' TRUE) ORDER BY `i`.`title` ASC');
                    //$query = array_unique($query);
                }
            }
            break;
        case 'meta':
        case 'metadata':
        case 'description':
        case 'descripcion':
        case 'descripción':
            $search = "((`i`.`description` LIKE '%{$keyword}%') + (`i`.`meta` LIKE '%{$keyword}%'))";
            $query = query(str_replace('FROM', ", $search as hits FROM", $sql) . " AND $search ORDER BY hits");

            if (!is_array($query) || (is_array($query) && count($query) == 0)) {
                $search = "((`i`.`description` LIKE '%{$clean_keyword}%') OR (`i`.`meta` LIKE '%{$clean_keyword}%'))";
                $query = query(str_replace('FROM', ", $search as hits FROM", $sql) . " AND $search ORDER BY hits");
            }

            if (!is_array($query) || (is_array($query) && count($query) == 0)) {
                $append = ' AND (';
                foreach ($clean_sep as $word) {
                    $append .= "((`i`.`description` LIKE '%{$word}%') AND (`i`.`meta` LIKE '%{$word}%')) AND ";
                }
                $query = query($sql . $append . ' FALSE)');
            }

            if (!is_array($query) || (is_array($query) && count($query) == 0)) {
                $append = ' AND (';
                foreach ($clean_sep as $word) {
                    $append .= "((`i`.`description` LIKE '%{$word}%') AND (`i`.`meta` LIKE '%{$word}%')) OR ";
                }
                $query = query($sql . $append . ' FALSE)');
            }
            break;
        case 'archivo':
        case 'file':
            $search = "(`f`.`filename` LIKE '%{$keyword}%')";
            $query = query(str_replace('FROM', ", $search as hits FROM", GET_FILES_ITEM_ID . $types) . " AND $search ORDER BY hits");

            if (!is_array($query) || (is_array($query) && count($query) == 0)) {
                $search = "(`f`.`filename` LIKE '%{$clean_keyword}%')";
                $query = query(str_replace('FROM', ", $search as hits FROM", GET_FILES_ITEM_ID . $types) . " AND $search ORDER BY hits");
            }

            if (!is_array($query) || (is_array($query) && count($query) == 0)) {
                $append = ' AND (';
                foreach ($clean_sep as $word) {
                    if (!empty(trim($word))) {
                        $append .= "(`f`.`filename` LIKE '%{$word}%') AND ";
                    }
                }
                $query = query($sql . $append . ' FALSE)');
            }

            if (!is_array($query) || (is_array($query) && count($query) == 0)) {
                $append = ' AND (';
                foreach ($clean_sep as $word) {
                    if (!empty(trim($word))) {
                        $append .= "(`f`.`filename` LIKE '%{$word}%') OR ";
                    }
                }
                $query = query($sql . $append . ' FALSE)');
            }
            break;
        default:
            $search = "(`i`.`description` LIKE '%{$keyword}%' 
                OR `i`.`meta` LIKE '%{$keyword}%' 
                OR `i`.`title` LIKE '%{$keyword}%'
                OR `i`.`id` LIKE '%{$keyword}%'
                OR `i`.`create_at` LIKE '%{$keyword}%'
                OR `i`.`published_date` LIKE '%{$keyword}%'
                OR `i`.`year_only` LIKE '%{$keyword}%'
                )";

            $query = query(str_replace('FROM', ", $search as hits FROM", $sql) . " AND $search ORDER BY hits");

            if (!is_array($query) || (is_array($query) && count($query) == 0)) {
                $search = "(`i`.`description` LIKE '%{$clean_keyword}%' 
                    OR `i`.`meta` LIKE '%{$clean_keyword}%' 
                    OR `i`.`title` LIKE '%{$clean_keyword}%'
                    OR `i`.`id` LIKE '%{$clean_keyword}%'
                    OR `i`.`create_at` LIKE '%{$clean_keyword}%'
                    OR `i`.`published_date` LIKE '%{$clean_keyword}%'
                    OR `i`.`year_only` LIKE '%{$clean_keyword}%'
                    )";

                $query = query(str_replace('FROM', ", $search as hits FROM", $sql) . " AND $search ORDER BY hits");
            }

            if (!is_array($query) || (is_array($query) && count($query) == 0)) {
                $sql_2 = $sql . ' AND (';

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

    if ($query !== NULL) {
        $query = array_unique($query, SORT_REGULAR);
        if (!is_array($query) || (is_array($query) && count($query) == 0)) return NULL;
        echo '<p style="color:red;">' . mysqli_error($conn) . '</p>';
    }

    return $query;
}
