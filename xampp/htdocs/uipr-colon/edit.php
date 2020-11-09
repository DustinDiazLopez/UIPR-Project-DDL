<?php
include_once('connect.php');
include_once('utils/utils.php');

authenticate();

if (isset($_POST['editItem']) && !empty($_POST['editItem'])) {
    $post_id = intval($_POST['editItem']);
    if ($post_id > 0) {
        $item = SQL_GET_ITEM_BY_ID($post_id);
        if (count($item) > 0) {
            $item = $item[0];
            $image = $files = NULL;

            if (!empty($item['image_id']) || is_int($item['image_id'])) {
                $image = SQL_GET_IMAGE($item['image_id']);
            }

            $files = SQL_GET_FILES($item['id'], true);
            $orphaned_files = SQL_GET_ORPHANED_FILES(true);

            print_r($item);
            print_r($files);
            print_r($orphaned_files);

            echo "<img src='$image' alt='' width='100px' height='auto'>";
        } else {
            echo 'item DNE';
        }
    } else {
        echo 'invalid id';
    }
    die('Not implemented');
}

header('Location: index.php');