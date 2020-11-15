<?php
// this file is included in utils.php

/**
 * @deprecated
 * @param array $file array containing the file_name, type, tmp_path, and size (binary)
 * @param string $error_buffer variable to put the error if it occurs
 * @param string $key the key of the array containing the path of the file.
 * @return mixed returns the insert id
 * @author Dustin Díaz
 */
function SQL_SEND_LONG_BLOB($file, &$error_buffer, $key='tmp_path')
{
    $mysqli = connect_obj();
    $stmt = $mysqli->prepare(SQL_INSERT_FILE_CONTENT);
    $null = NULL;
    $stmt->bind_param("b", $null);
    $fp = fopen($file[$key], "r");
    while (!feof($fp)) {
        $stmt->send_long_data(0, fread($fp, SEND_LONG_BLOB_BUFFER_SIZE));
    }
    fclose($fp);
    $success = $stmt->execute();
    if (!$success) $error_buffer = $stmt->error;
    return $success ? $mysqli->insert_id : NULL;
}

/**
 * Counts the number of items in the database
 * @return int returns the number of items in the database
 * @author Dustin Díaz
 */
function SQL_GET_ITEM_COUNT()
{
    return intval(query(SQL_GET_ITEM_COUNT)[0]['count(*)']);
}

/**
 * Counts the number of authors in the database with a relation
 * @param $author string the id of the author
 * @return int returns the number of items in the database
 * @author Dustin Díaz
 */
function SQL_GET_ITEM_COUNT_AUTHOR($author)
{
    $author = SQL_GET_AUTHOR_BY_ID($author)[0]['author_name'];
    return intval(query(sprintf(SQL_GET_ITEM_COUNT_AUTHOR, $author))[0]['count(*)']);
}

/**
 * Counts the number of subjects in the database with a relation
 * @param $subject string the id of the subject
 * @return int returns the number of items in the database
 * @author Dustin Díaz
 */
function SQL_GET_ITEM_COUNT_SUBJECT($subject)
{
    $subject = SQL_GET_SUBJECT_BY_ID($subject)[0]['subject'];
    return intval(query(sprintf(SQL_GET_ITEM_COUNT_SUBJECT, $subject))[0]['count(*)']);
}

/**
 * Counts the number of types in the database with a relation
 * @param $type string the id of the type
 * @return int returns the number of items in the database
 * @author Dustin Díaz
 */
function SQL_GET_ITEM_COUNT_TYPE($type)
{
    $type = SQL_GET_TYPE_BY_ID($type)[0]['type'];
    return intval(query(sprintf(SQL_GET_ITEM_COUNT_TYPE, $type))[0]['count(*)']);
}

/**
 * @param $sql string the sql script
 * @param $sql_fallback_get string the fallback sql when sql fails
 * @param $error_buffer string where to put the error
 * @return integer|boolean
 * Returns the insert id of the inserted item, or if it already exists ({@link SQL_DUPLICATE_ERROR}) it returns the id of the existing item.
 * Returns false for another type of error (do {@link mysqli_error()}), the error will be passed to the
 * <b>$error_buffer</b>, if not fallback sql is set then returns true.
 * @author Dustin Díaz
 */
function INSERT($sql, $sql_fallback_get, &$error_buffer)
{
    global $conn;
    $insert_id = NULL;
    // execute the insert query, since it's an INSERT, mysqli_query returns either true or false.
    if (mysqli_query($conn, $sql)) {
        // get the inserted id
        return mysqli_insert_id($conn);
    } else {
        // check to see if it is a duplicate error
        if (mysqli_errno($conn) === SQL_DUPLICATE_ERROR) {
            // check to see the fallback sql is empty
            if (!empty($sql_fallback_get)) {
                // get the first value of the query
                return intval(query($sql_fallback_get)[0]['id']);
            }
        }

        // overrides the error buffer with the error message
        $error_buffer = mysqli_error($conn);
        error_log($error_buffer);
        return FALSE;
    }
}

/**
 * SQL script for an insert to the type table.
 * Please see {@link query()}
 * @param $type string the name of the type
 * @return array|bool|null
 * returns NULL if an error occurs while fetching the objects (unlikely)
 * returns true or false on success or failure
 * returns the array of objects fetched.
 * @author Dustin Díaz
 */
function SQL_INSERT_TYPE($type)
{
    return (sprintf(SQL_INSERT_TYPE, $type));
}

/**
 * SQL script for an insert to the image table.
 * Please see {@link query()}
 * @param $type string type (mime type) of the image (e.g., image/jpeg)
 * @param $size integer size of the image in bytes
 * @param $image string the binary (content) of the image.
 * @return array|bool|null
 * returns NULL if an error occurs while fetching the objects (unlikely)
 * returns true or false on success or failure
 * returns the array of objects fetched.
 * @author Dustin Díaz
 */
function SQL_INSERT_IMAGE($type, $size, $image)
{
    return (sprintf(SQL_INSERT_IMAGE, $type, $size, $image));
}

/**
 * SQL script for an insert to the auhtor table.
 * Please see {@link query()}
 * @param $author_name string the name of the author
 * @return array|bool|null
 * returns NULL if an error occurs while fetching the objects (unlikely)
 * returns true or false on success or failure
 * returns the array of objects fetched.
 * @author Dustin Díaz
 */
function SQL_INSERT_AUTHOR($author_name)
{
    return (sprintf(SQL_INSERT_AUTHOR, $author_name));
}

/**
 * SQL script for an insert to the subject table.
 * Please see {@link query()}
 * @param $subject string the subject name
 * @return array|bool|null
 * returns NULL if an error occurs while fetching the objects (unlikely)
 * returns true or false on success or failure
 * returns the array of objects fetched.
 * @author Dustin Díaz
 */
function SQL_INSERT_SUBJECT($subject)
{
    return (sprintf(SQL_INSERT_SUBJECT, $subject));
}

/**
 * SQL script for an insert to the item table.
 * Please see {@link query()}
 * @param $title string the title of the item
 * @param $type_id integer the type id of the item
 * @param $image_id integer the image id of the item
 * @param $pub_date string the published date of the item (yyyy-mm-dd)
 * @param $year_only boolean weather to only show the year or the whole $pub_date
 * @param $description string the description of the item
 * @param $metadata string the metadata of the item
 * @return array|bool|null
 * returns NULL if an error occurs while fetching the objects (unlikely)
 * returns true or false on success or failure
 * returns the array of objects fetched.
 * @author Dustin Díaz
 */
function SQL_INSERT_ITEM($title, $type_id, $image_id, $pub_date, $year_only, $description, $metadata)
{
    return (sprintf(SQL_INSERT_ITEM, $title, $type_id, $image_id, $pub_date, $year_only, $description, $metadata));
}

/**
 * SQL script for an insert to the item_has_subject table.
 * Please see {@link query()}
 * @param $item_id integer the item id
 * @param $subject_id integer the subject id
 * @return array|bool|null
 * returns NULL if an error occurs while fetching the objects (unlikely)
 * returns true or false on success or failure
 * returns the array of objects fetched.
 * @author Dustin Díaz
 */
function SQL_INSERT_ITEM_HAS_SUBJECT($item_id, $subject_id)
{
    return (sprintf(SQL_INSERT_ITEM_HAS_SUBJECT, $item_id, $subject_id));
}

/**
 * SQL script for an insert to the file_has_item table.
 * Please see {@link query()}
 * @param $file_id integer the file id
 * @param $item_id integer  the item id
 * @return array|bool|null
 * returns NULL if an error occurs while fetching the objects (unlikely)
 * returns true or false on success or failure
 * returns the array of objects fetched.
 * @author Dustin Díaz
 */
function SQL_INSERT_FILE_HAS_ITEM($file_id, $item_id)
{
    return (sprintf(SQL_INSERT_FILE_HAS_ITEM, $file_id, $item_id));
}

/**
 * SQL script for an insert to the type author_has_item.
 * Please see {@link query()}
 * @param $item_id integer the item id
 * @param $author_id integer the author id
 * @return array|bool|null
 * returns NULL if an error occurs while fetching the objects (unlikely)
 * returns true or false on success or failure
 * returns the array of objects fetched.
 * @author Dustin Díaz
 */
function SQL_INSERT_AUTHOR_HAS_ITEM($item_id, $author_id)
{
    return (sprintf(SQL_INSERT_AUTHOR_HAS_ITEM, $item_id, $author_id));
}

/**
 * SQL script to find the id of the specified type
 * @param $type string the name of the type
 * @return string returns the sql script
 * @author Dustin Díaz
 */
function SQL_GET_ID_OF_TYPE_BY_TYPE($type)
{
    return sprintf(SQL_GET_ID_OF_TYPE_BY_TYPE, $type);
}

/**
 * SQL script to find the id of the specified author
 * @param $author_name string the name of the author
 * @return string returns the sql script
 * @author Dustin Díaz
 */
function SQL_GET_ID_OF_AUTHOR_BY_AUTHOR_NAME($author_name)
{
    return sprintf(SQL_GET_ID_OF_AUTHOR_BY_AUTHOR_NAME, $author_name);
}

/**
 * SQL script to find the id of the specified subject
 * @param $subject string the name of the subject
 * @return string returns the sql script
 * @author Dustin Díaz
 */
function SQL_GET_ID_OF_SUBJECT_BY_SUBJECT($subject)
{
    return sprintf(SQL_GET_ID_OF_SUBJECT_BY_SUBJECT, $subject);
}

/**
 * SQL script that sets the file information in the database
 * @param array $file array containing the file_name, type, tmp_path, path, and size
 * @return string returns the sql script to insert a file
 * @author Dustin Díaz
 */
function SQL_INSERT_FILE($file)
{
    return sprintf(SQL_INSERT_FILE_PATH, $file['file_name'], $file['path']);
}

/**
 * Queries the database for all the files that do not have an item associated with them.
 * @return array|null returns the associative array of the files (id, and path), or NULL if something went wrong.
 * @author Dustin Díaz
 */
function SQL_GET_ORPHANED_FILES()
{
    return query(SQL_GET_ORPHANED_FILES_NO_CONTENT);
}

/**
 * Queries the database for all the authors that do not have an item associated with them.
 * @return array|null returns the associative array of the authors (id, and author_name), or NULL if something went
 * wrong.
 * @author Dustin Díaz
 */
function SQL_GET_ORPHANED_AUTHORS()
{
    return query(SQL_GET_ORPHANED_AUTHORS);
}

/**
 * Queries the database for all the subjects that do not have an item associated with them.
 * @return array|null returns the associative array of the subjects (id, and subject), or NULL if something went wrong.
 * @author Dustin Díaz
 */
function SQL_GET_ORPHANED_SUBJECTS()
{
    return query(SQL_GET_ORPHANED_SUBJECTS);
}

/**
 * Queries the database for all the images that do not have an item associated with them.
 * @return array|null returns the associative array of the images (id), or NULL if something went wrong.
 * @author Dustin Díaz
 */
function SQL_GET_ORPHANED_IMAGES()
{
    return query(SQL_GET_ORPHANED_IMAGES);
}

/**
 * Queries the database for all the types that do not have an item associated with them.
 * @return array|null returns the associative array of the types (id, and type), or NULL if something went wrong.
 * @author Dustin Díaz
 */
function SQL_GET_ORPHANED_TYPES()
{
    return query(SQL_GET_ORPHANED_TYPES);
}

/**
 * Queries the database for all the admins (users).
 * @return array|null returns the associative array of the admins, or NULL if something went wrong.
 * @author Dustin Díaz
 */
function SQL_GET_ADMINS()
{
    return query(SQL_GET_ADMINS);
}

/**
 * Queries the database for an admin given an id.
 * @param integer $id the id of the admin.
 * @return array|null returns the associative array of the admin (id, email, username, password), or NULL if something
 * went wrong.
 * @author Dustin Díaz
 */
function SQL_GET_ADMIN_BY_ID($id) {
    global $conn;
    $id = mysqli_real_escape_string($conn, $id);
    return query(SQL_GET_ADMIN_BY_ID . $id);
}

/**
 * Queries the database for an admin given a username.
 * @param string $username the username of the admin.
 * @return array|null returns the associative array of the admin (id, email, username, password), or NULL if something
 * went wrong.
 * @author Dustin Díaz
 */
function SQL_GET_ADMIN_BY_USERNAME($username) {
    global $conn;
    $username = mysqli_real_escape_string($conn, $username);
    return query(SQL_GET_ADMIN_BY_USERNAME . "'$username'");
}

/**
 * Queries the database for an admin given an email.
 * @param string $email the email of the admin.
 * @return array|null returns the associative array of the admin (id, email, username, password), or NULL if something
 * went wrong.
 * @author Dustin Díaz
 */
function SQL_GET_ADMIN_BY_EMAIL($email)
{
    global $conn;
    $email = mysqli_real_escape_string($conn, $email);
    return query(SQL_GET_ADMIN_BY_EMAIL . "'$email'");
}

/**
 * Tests whether the email exists
 * @param string $email the email of the admin.
 * @return bool true if a user exists with the specified email
 * @author Dustin Díaz
 */
function SQL_ADMIN_EMAIL_EXIST($email)
{
    return count(SQL_GET_ADMIN_BY_EMAIL($email)) > 0;
}

/**
 * Tests whether the username exists
 * @param string $username the username of the admin.
 * @return bool true if a user exists with the specified username
 * @author Dustin Díaz
 */
function SQL_ADMIN_USERNAME_EXIST($username)
{
    return count(SQL_GET_ADMIN_BY_USERNAME($username)) > 0;
}

/**
 * Tests whether the username and email exists
 * @param string $email the email of the admin.
 * @param string $username the username of the admin.
 * @return bool true if a user exists with the specified email or username
 * @author Dustin Díaz
 */
function SQL_ADMIN_USERNAME_AND_EMAIL_EXIST($email, $username)
{
    return (count(SQL_GET_ADMIN_BY_USERNAME($username)) > 0) && (count(SQL_GET_ADMIN_BY_EMAIL($email)) > 0);
}

/**
 * Updates the author in the database
 * @param integer $id the id of the author
 * @param array $old_author the old author (id, author_name)
 * @param array $new_author the new author (id, author_name)
 * @return int|string|null returns the number of rows, a string ('author_name') when the author already exists, or null
 * if ids do not match (or something is not set).
 * @author Dustin Díaz
 */
function SQL_UPDATE_AUTHOR($id, $old_author, $new_author)
{
    if (isset($old_author['id']) && isset($new_author['id']) && isset($old_author['author_name']) && isset($new_author['author_name'])) {
        global $conn;
        if ($id == $old_author['id'] && $id == $new_author['id']) {
            $sql = "UPDATE `author` SET `author_name` = '{$new_author['author_name']}' WHERE `author`.`id` = '{$new_author['id']}'";
            if ($old_author['author_name'] != $new_author['author_name']) {
                $current_author = query("SELECT `id`, `author_name` FROM `author` where `author_name` = '{$new_author['author_name']}'");
                if (count($current_author) <= 0) {
                    query($sql);
                    return mysqli_affected_rows($conn);
                } else {
                    return 'author_name';
                }
            } else {
                query($sql);
                return mysqli_affected_rows($conn);
            }
        }
    }

    return NULL;
}

/**
 * Updates the subject in the database
 * @param integer $id the id of the subject
 * @param array $old_subject the old subject (id, subject)
 * @param array $new_subject the new subject (id, subject)
 * @return int|string|null returns the number of rows, a string ('subject') when the subject already exists, or null if ids do not match (or something is not set).
 * @author Dustin Díaz
 */
function SQL_UPDATE_SUBJECT($id, $old_subject, $new_subject)
{

    if (isset($old_subject['id']) && isset($new_subject['id']) && isset($old_subject['subject']) && isset($new_subject['subject'])) {
        global $conn;
        if ($id == $old_subject['id'] && $id == $new_subject['id']) {
            $sql = "UPDATE `subject` SET `subject` = '{$new_subject['subject']}' WHERE `subject`.`id` = '{$new_subject['id']}'";
            if ($old_subject['subject'] != $new_subject['subject']) {
                echo '<hr>SQL :: ' . $sql . '<hr>';
                $current_author = query("SELECT `id`, `subject` FROM `subject` where `subject` = '{$new_subject['subject']}'");

                if (count($current_author) <= 0) {
                    query($sql);
                    return mysqli_affected_rows($conn);
                } else {
                    return 'subject';
                }
            } else {
                query($sql);
                return mysqli_affected_rows($conn);
            }
        }
    }

    return NULL;
}

/**
 * Updates the type in the database
 * @param integer $id the id of the type
 * @param array $old_type the old type (id, type)
 * @param array $new_type the new type (id, type)
 * @return int|string|null returns the number of rows, a string ('type') when the type already exists, or null if ids
 * do not match (or something is not set).
 * @author Dustin Díaz
 */
function SQL_UPDATE_TYPE($id, $old_type, $new_type)
{
    if (isset($old_type['id']) && isset($new_type['id']) && isset($old_type['type']) && isset($new_type['type'])) {
        global $conn;
        if ($id == $old_type['id'] && $id == $new_type['id']) {
            $sql = "UPDATE `type` SET `type` = '{$new_type['type']}' WHERE `type`.`id` = '{$new_type['id']}'";
            if ($old_type['type'] != $new_type['type']) {
                $current_author = query("SELECT `id`, `type` FROM `type` where `type` = '{$new_type['type']}'");
                if (count($current_author) <= 0) {
                    query($sql);
                    return mysqli_affected_rows($conn);
                } else {
                    return 'type';
                }
            } else {
                query($sql);
                return mysqli_affected_rows($conn);
            }
        }
    }

    return NULL;
}

/**
 * @param integer $id the id of the user
 * @param array $old_user the old user
 * @param array $updated_user the new user
 * @return integer|null|string if old_user is missing information or id mismatch it will return null, it will return
 * the number of affected rows, or a string indicating the field that already exists (email, username).
 * @author Dustin Díaz
 */
function SQL_UPDATE_ADMIN($id, $old_user, $updated_user)
{
    global $conn;
    if (isset($old_user['id']) && isset($old_user['username']) && isset($old_user['email']) &&
        isset($old_user['password']) && !empty($old_user['id']) && !empty($old_user['username']) &&
        !empty($old_user['email']) && !empty($old_user['password']) && isset($updated_user['id']) &&
        ($id == $updated_user['id'] && $id == $old_user['id'])) {

        if (!isset($updated_user['email']) || empty($updated_user['email'])) {
            $updated_user['email'] = $old_user['email'];
        } else {
            if ($updated_user['email'] != $old_user['email']) {
                if (SQL_ADMIN_EMAIL_EXIST($updated_user['email'])) {
                    return 'email';
                }
            }
        }

        if (!isset($updated_user['username']) || empty($updated_user['username'])) {
            $updated_user['username'] = $old_user['username'];
        } else {
            if ($updated_user['username'] != $old_user['username']) {
                if (SQL_ADMIN_USERNAME_EXIST($updated_user['username'])) {
                    return 'username';
                }
            }
        }

        if (!isset($updated_user['password']) || empty($updated_user['password'])) {
            $updated_user['password'] = $old_user['password'];
        }


        $updated_user = [
            'id' => mysqli_real_escape_string($conn, $updated_user['id']),
            'username' => mysqli_real_escape_string($conn, $updated_user['username']),
            'email' => mysqli_real_escape_string($conn, $updated_user['email']),
            'password' => mysqli_real_escape_string($conn, $updated_user['password'])
        ];

        query(
            SQL_UPDATE_ADMIN_BY_ID .
            "`email` = '{$updated_user['email']}', 
            `username` = '{$updated_user['username']}', 
            `password` = '{$updated_user['password']}' 
            WHERE `admin`.`id` = '{$updated_user['id']}'"
        );

        return mysqli_affected_rows($conn);


    } else {
        echo 'failed';
        return NULL;
    }
}


/**
 * Inserts into the database an admin
 * @param string $email the email for the admin.
 * @param string $username the username for the admin.
 * @param string $password the passowrd for the admin.
 * @return bool|null returns weather it was successful, or NULL if the admin already exists.
 * @author Dustin Díaz
 */
function SQL_INSERT_ADMIN($email, $username, $password) {
    global $conn;
    if (SQL_ADMIN_USERNAME_AND_EMAIL_EXIST($email, $username)) {
        return NULL;
    } else {
        $email = mysqli_real_escape_string($conn, $email);
        $username = mysqli_real_escape_string($conn, $username);
        $password = mysqli_real_escape_string($conn, $password);
        query(SQL_INSERT_ADMIN . "('$email', '$username', '$password')");
        return mysqli_affected_rows($conn) > 0;
    }
}


/**
 * Queries the database for items related to an author given its id.
 * @param integer $author_id the author id
 * @param string $append
 * @return array|null returns the associative array of the author related to the id
 * @author Dustin Díaz
 */
function SQL_GET_ITEMS_BY_AUTHOR_ID($author_id, $append = '')
{
    return query(SQL_GET_ITEMS_BY_AUTHOR_ID . $author_id . ' ' . $append);
}


/**
 * Queries the database for an author given their id.
 * @param integer $author_id the author id
 * @return array|null returns the associative array of the author related to the id
 * @author Dustin Díaz
 */
function SQL_GET_AUTHOR_BY_ID($author_id)
{
    return query(SQL_GET_AUTHOR_BY_ID . $author_id);
}

/**
 * Queries the database for a subject given their id.
 * @param integer $subject_id the subject id
 * @return array|null returns the associative array of the subject related to the id
 * @author Dustin Díaz
 */
function SQL_GET_SUBJECT_BY_ID($subject_id)
{
    return query(SQL_GET_SUBJECT_BY_ID . $subject_id);
}

/**
 * Queries the database for a type given their id.
 * @param integer $type_id the type id
 * @return array|null returns the associative array of the type related to the id
 * @author Dustin Díaz
 */
function SQL_GET_TYPE_BY_ID($type_id)
{
    return query(SQL_GET_TYPE_BY_ID . $type_id);
}

/**
 * Queries the database for items related to a subject given its id.
 * @param integer $subject_id the subject id
 * @param string $append
 * @return array|null returns the associative array of the subject related to the id
 * @author Dustin Díaz
 */
function SQL_GET_ITEMS_BY_SUBJECT_ID($subject_id, $append = '')
{
    return query(SQL_GET_ITEMS_BY_SUBJECT_ID . $subject_id . ' ' . $append);
}

/**
 * Queries the database for items related to a type given its id.
 * @param integer $type_id the type id
 * @param string $append
 * @return array|null returns the associative array of the subject related to the id
 * @author Dustin Díaz
 */
function SQL_GET_ITEMS_BY_TYPE_ID($type_id, $append = '')
{
    return query(SQL_GET_ITEMS_BY_TYPE_ID . $type_id . ' ' . $append);
}

/**
 * Generates the SQL command needed to find the files given an item id
 * @param integer $id item id
 * @return string SQL command to find the file ids of an item.
 * @author Dustin Díaz
 */
function SQL_FILES_ID_BY_ITEM_ID($id)
{
    return SQL_FILES_ID_BY_ITEM_ID . $id;
}

/**
 * Generates the SQL command needed to find the subjects given an item id
 * @param integer $id item id
 * @return string SQL command to find the subjects of an item.
 * @author Dustin Díaz
 */
function SQL_SUBJECTS_ID_BY_ITEM_ID($id)
{
    return SQL_SUBJECTS_ID_BY_ITEM_ID . $id;
}

/**
 * Generates the SQL command needed to find the authors given an item id
 * @param integer $id item id
 * @return string SQL command to find the authors of an item.
 * @author Dustin Díaz
 */
function SQL_AUTHORS_ID_BY_ITEM_ID($id)
{
    return SQL_AUTHORS_ID_BY_ITEM_ID . $id;
}

/**
 * Generates the SQL command needed to find an image given an item id
 * @param integer $id item id
 * @return string SQL command to find an image.
 * @author Dustin Díaz
 */
function SQL_IMAGE_ID_BY_ITEM_ID($id)
{
    return SQL_IMAGE_ID_BY_ITEM_ID . $id;
}

/**
 * Queries the database for all the items.
 * @param string $append (optional) append a SQL command (e.g., ORDER BY ASC)
 * @return array|null returns the associative array of the items, or NULL if something went wrong.
 * @author Dustin Díaz
 */
function SQL_GET_ALL_ITEMS($append = '')
{
    return query(SQL_ALL_ITEMS . $append);
}

/**
 * Queries the database for the files matching an item id.
 * @param integer $item_id the item id.
 * @return array returns the associative array of the files matching the item id.
 * @author Dustin Díaz
 */
function SQL_GET_FILES($item_id)
{
    $files = array();
    foreach (query(SQL_GET_FILES_NO_CONTENT . " '$item_id'") as $f) {
        $files[] = [
            'id' => $f['id'],
            'filename' => $f['filename'],
            'path' => $f['path']
        ];
    }

    return $files;
}

/**
 * Queries the database for the file matching an id.
 * @param integer $file_id the file id
 * @return array|null returns the associative array of the files, or NULL if something went wrong.
 * @author Dustin Díaz
 */
function SQL_GET_FILE($file_id)
{
    global $conn;
    $file_id = mysqli_real_escape_string($conn, $file_id);
    return query(SQL_GET_FILE . " '$file_id'");
}

/**
 * Queries the database for the subjects matching an item id.
 * @param integer $item_id the item id
 * @return array|null returns the associative array of the subjects, or NULL if something went wrong.
 * @author Dustin Díaz
 */
function SQL_GET_SUBJECTS($item_id)
{
    global $conn;
    $item_id = mysqli_real_escape_string($conn, $item_id);
    return query(SQL_GET_SUBJECTS_BY_ID . " '$item_id'");
}

/**
 * Queries the database for the password of an admin
 * @param integer $id admin identification number
 * @return array|null returns the associative array of the password, or NULL if something went wrong.
 * @author Dustin Díaz
 */
function SQL_GET_PWD_BY_ID($id)
{
    return query(SQL_GET_PWD_BY_ID . $id);
}

/**
 * Returns the image as base64 (as a link) or a default image if none exist.
 * @param integer $image_id the image id.
 * @return string returns the image as a link (base64) or a default image if there is not image related to the id.
 * @author Dustin Díaz
 */
function SQL_GET_IMAGE($image_id)
{
    $image = query(SQL_GET_IMAGE . " '$image_id'");
    if (count($image) >= 1) {
        $mime_type = finfo_buffer(finfo_open(), $image[0]['image'], FILEINFO_MIME_TYPE);
        return "data:$mime_type;base64," . base64_encode($image[0]['image']);
    } else return 'images/pdf-placeholder.jpg';

}

/**
 * Queries the database for the authors related to an item.
 * @param integer $item_id the item id.
 * @return array|null returns the authors related to the item or NULL if something went wrong.
 * @author Dustin Díaz
 */
function SQL_GET_AUTHORS($item_id)
{
    return query(SQL_GET_AUTHORS_BY_ID . " '$item_id'");
}

/**
 * Queries the database for a user matching an email or password.
 * @param string $user_or_email the username or email of the admin user
 * @return array|null returns the admin user or NULL if something went wrong.
 * @author Dustin Díaz
 */
function SQL_GET_USER_ID_BY_UE($user_or_email)
{
    return query("SELECT `id`, `username`, `email` FROM `admin` WHERE `email` = '$user_or_email' OR `username` = '$user_or_email'");
}

/**
 * Generates the SQL command needed to delete am author.
 * @param integer $item_id the item id related to the author
 * @param integer $author_id the author id
 * @return string returns the SQL script to delete an author
 * @author Dustin Díaz
 */
function sql_delete_author($item_id, $author_id)
{
    return "DELETE FROM `author_has_item` WHERE `author_has_item`.`item_id` = $item_id AND `author_has_item`.`author_id` = $author_id";
}

/**
 * Generates the SQL command needed to delete a subject.
 * @param integer $item_id the item id related to the subject
 * @param integer $subject_id the subject id
 * @return string returns the SQL script to delete a subject.
 * @author Dustin Díaz
 */
function sql_delete_subject($item_id, $subject_id)
{
    return "DELETE FROM `item_has_subject` WHERE `item_has_subject`.`item_id` = $item_id AND `item_has_subject`.`subject_id` = $subject_id";
}

/**
 * Generates the SQL command needed to delete an author.
 * @param integer $author_id the author id
 * @return string returns the SQL script to delete an author
 * @author Dustin Díaz
 */
function sql_delete_author_id($author_id)
{
    return SQL_DELETE_AUTHOR_BY_ID . $author_id;
}

/**
 * Generates the SQL command needed to delete a subject.
 * @param integer $subject_id the subject id
 * @return string returns the SQL script to delete a subject
 * @author Dustin Díaz
 */
function sql_delete_subject_id($subject_id)
{
    return SQL_DELETE_SUBJECT_BY_ID. $subject_id;
}

/**
 * Generates the SQL command needed to delete a file.
 * @param integer $item_id the item id related to the file
 * @param integer $file_id the file id
 * @return string returns the SQL script to delete a file
 * @author Dustin Díaz
 */
function sql_delete_file($item_id, $file_id)
{
    return "DELETE FROM `file_has_item` WHERE `file_has_item`.`file_id` = $file_id AND `file_has_item`.`item_id` = $item_id";
}


/**
 * Generates the SQL command needed to delete a file.
 * @param integer $file_id the file id
 * @return string returns the SQL script to delete a file
 * @author Dustin Díaz
 */
function sql_delete_file_by_id($file_id)
{
    global $conn;
    $file_id = mysqli_real_escape_string($conn, $file_id);
    return SQL_DELETE_FILE_BY_ID . $file_id;
}

/**
 * Generates the SQL command needed to delete a type.
 * @param integer $type_id the type id
 * @return string returns the SQL script to delete a type
 * @author Dustin Díaz
 */
function sql_delete_type_by_id($type_id)
{
    global $conn;
    $type_id = mysqli_real_escape_string($conn, $type_id);
    return SQL_DELETE_TYPE_BY_ID . $type_id;
}


/**
 * Generates the SQL command needed to delete an image.
 * @param integer $image_id the image id.
 * @return string returns the SQL script to delete an image
 * @author Dustin Díaz
 */
function sql_delete_image($image_id)
{
    return SQL_DELETE_IMAGE_BY_ID . $image_id;
}

/**
 * Generates the SQL command needed to delete an item.
 * @param integer $item_id the id of the item to be deleted
 * @return string returns the SQL script to delete an item
 * @author Dustin Díaz
 */
function sql_delete_item($item_id)
{

    return SQL_DELETE_ITEM_BY_ID. $item_id;
}

/**
 * Returns the item in the database matching the id.
 * @param $item_id integer the id of the item
 * @return array|bool|null {@link query()}
 * @author Dustin Díaz
 */
function SQL_GET_ITEM_BY_ID($item_id)
{
    return query(sprintf(SQL_GET_ITEM, $item_id));
}

/**
 * Returns the item (w/ meta) in the database matching the id.
 * @param $item_id integer the id of the item
 * @return array|bool|null {@link query()}
 * @author Dustin Díaz
 */
function SQL_GET_ITEM_BY_ID_META($item_id)
{
    return query(sprintf(SQL_GET_ITEM_META, $item_id));
}

/**
 * Executes a SQL string on the database, this is a helper method for {@link DELETE_ITEM_AND_RELATIONS()}
 * @param $sql string SQL string to execute
 * @author Dustin Díaz
 */
function exe_sql($sql)
{
    global $conn;
    global $errors;
    echo '<hr/>' . htmlspecialchars('Executing: ' . $sql) . '<br />';
    mysqli_query($conn, $sql);
    $error = mysqli_error($conn);
    if (!empty($error)) {
        echo htmlspecialchars('Errors: ' . $error);
        $errors[] = $error;
    } else {
        echo 'No errors.';
    }

    echo '<hr/>';
}

/**
 * This will delete
 * the item entry on the database,
 * the authors (and relation with item),
 * the subjects(and relation with item),
 * the image,
 * the files relationship (BUT NOT THE FILES),
 * it will also delete all subjects, authors, and images which don't have a relation.
 * @param $id integer the id of the item to delete
 * @author Dustin Díaz
 */
function DELETE_ITEM_AND_RELATIONS($id)
{
    $query_files = query(SQL_FILES_ID_BY_ITEM_ID($id));
    $query_subjects = query(SQL_SUBJECTS_ID_BY_ITEM_ID($id));
    $query_authors = query(SQL_AUTHORS_ID_BY_ITEM_ID($id));
    $query_image = query(SQL_IMAGE_ID_BY_ITEM_ID($id));

    // DELETE AUTHORS REL
    if (count($query_authors) >= 1) {
        foreach ($query_authors as $obj) {
            $sql = sql_delete_author($id, $obj['author_id']);
            exe_sql($sql);
        }
    }

    // DELETE SUBJECT REL
    if (count($query_subjects) >= 1) {
        foreach ($query_subjects as $obj) {
            $sql = sql_delete_subject($id, $obj['subject_id']);
            exe_sql($sql);
        }
    }

    // DELETE FILES REL
    if (count($query_files) >= 1) {
        foreach ($query_files as $obj) {
            $sql = sql_delete_file($id, $obj['file_id']);
            exe_sql($sql);
        }
    }

    //DELETE ITEM
    $sql = sql_delete_item($id);
    exe_sql($sql);

    //DELETE IMAGE
    if (count($query_image) >= 1) {
        foreach ($query_image as $obj) {
            if (isset($obj['image_id']) && !empty($obj['image_id'])) {
                $sql = sql_delete_image($obj['image_id']);
                exe_sql($sql);
            }
        }
    }

    //CHECKS for any images that have no item
    $arr = SQL_GET_ORPHANED_IMAGES();
    // and deletes them
    if (count($arr) > 0) {
        foreach ($arr as $obj) {
            if (isset($obj['id'])) {
                exe_sql(sql_delete_image($obj['id']));
            }
        }
    }

    //CHECKS for any authors that have no item
    $arr = SQL_GET_ORPHANED_AUTHORS();
    // and deletes them
    if (count($arr) > 0) {
        foreach ($arr as $obj) {
            if (isset($obj['id'])) {
                exe_sql(sql_delete_author_id($obj['id']));
            }
        }
    }

    //CHECKS for any subjects that have no item
    $arr = SQL_GET_ORPHANED_SUBJECTS();
    // and deletes them
    if (count($arr) > 0) {
        foreach ($arr as $obj) {
            if (isset($obj['id'])) {
                exe_sql(sql_delete_subject_id($obj['id']));
            }
        }
    }
}