<?php
$title_tag = 'Home';
$allow_guests = TRUE;
// limit of items per page
$limit = isset($_GET['item-per-page']) && is_valid_int($_GET['item-per-page']) ? intval($_GET['item-per-page']) : 10;

$items = NULL;
$_APPEND_LIMITER = ''; // will be overwritten
include_once('templates/header.php');
?>

<div class="container-fluid">
    <div class="row">
        <!-- SEARCH START -->
        <div class="col-sm-3 bg-light">
            <?php
            $searched_value = '';
            if (isset($_GET['q'])) {
                $searched_value = htmlspecialchars($_GET['q']);

                $only = 'all';
                if (isset($_GET['only'])) {
                    $only = $_GET['only'];
                }

                if (strlen($_GET['q']) > 1) {
                    if (isset($conn)) {
                        $items = search($conn, $_GET['q'], $only);
                    }
                } else {
                    $invalid_search_len = 'Please provide a longer search query';
                    $valid_search = false;
                }
            }

            include('templates/search.php'); 
            ?>
        </div>
        <!-- SEARCH END -->

        <!-- ITEMS START -->
        <div class="col-sm-9" id="items">
            <?php

            // custom search
            if (isset($_GET['q'])) {
                $only = 'all';
                if (isset($_GET['only'])) {
                    $only = $_GET['only'];
                }
                if (isset($conn)) {
                    $items = search($conn, $_GET['q'], $only);
                }

            } elseif (isset($_GET['author-search'])) {
                if (isset($_GET['author'])) {
                    $author = escapeMySQL($_GET['author']);
                    $total = SQL_GET_ITEM_COUNT_AUTHOR($author);
                    if ($total > 0) {
                        if (isset($conn)) {
                            include ('templates/pagination.setter.php');
                            $items = SQL_GET_ITEMS_BY_AUTHOR_ID($author, $_APPEND_LIMITER);
                            $current_count = count($items);
                        }
                    }
                }
            } elseif (isset($_GET['subject-search'])) {
                if (isset($_GET['subject'])) {
                    $subject = escapeMySQL($_GET['subject']);
                    $total = SQL_GET_ITEM_COUNT_SUBJECT($subject);
                    if ($total > 0) {
                        if (isset($conn)) {
                            include ('templates/pagination.setter.php');
                            $items = SQL_GET_ITEMS_BY_SUBJECT_ID($subject, $_APPEND_LIMITER);
                            $current_count = count($items);
                        }
                    }
                }
            } elseif (isset($_GET['type-search'])) {
                if (isset($_GET['type'])) {
                    $type = escapeMySQL($_GET['type']);
                    $total = SQL_GET_ITEM_COUNT_TYPE($type);
                    if ($total > 0) {
                        include ('templates/pagination.setter.php');
                        $items = SQL_GET_ITEMS_BY_TYPE_ID($type, $_APPEND_LIMITER);
                        $current_count = count($items);
                    }
                }
            } else {
                $total = SQL_GET_ITEM_COUNT();
                if ($total > 0) {
                    include ('templates/pagination.setter.php');
                    $items = SQL_GET_ALL_ITEMS("ORDER BY i.create_at DESC $_APPEND_LIMITER");
                    $current_count = count($items);
                }
            }


            if (isset($_GET['error'])) {
                if ($_GET['error'] == "invalid-item-id") {
                    echo showWarn(
                            "400:",
                            "Invalid item id."
                    );
                } elseif ($_GET['error'] == "invalid-item-edit-req") {
                    echo showWarn(
                        "400:",
                        "Invalid item id."
                    );
                } elseif ($_GET['error'] == "no-pdf") {
                    echo showWarn(
                        "404:",
                        "The requested file does not appear to exist..."
                    );
                } elseif ($_GET['error'] == "no-item") {
                    echo showWarn(
                        "404:",
                        "The requested item does not appear to exist or was modified..."
                    );
                } elseif ($_GET['error'] == "403") {
                    echo showDanger(
                        "403:",
                        "You don't have access to that area."
                    );
                } elseif ($_GET['error'] == "no-edit") {
                    echo showDanger(
                        "400:",
                        "No item was specified to edit"
                    );
                }
            } elseif (isset($_GET['deleted'])) {
                echo showSuccess("Success - An article was deleted:", "You still have access to the files related to the deleted article (Admin Panel > Data > Orphaned Files).");
            } elseif (isset($_GET['created'])) {
                echo showSuccess("Success:", "An item was created.");
            }
            // IF NOTHING IS FOUND
            if (empty($items) || $items === NULL || count($items) == 0 || !array_filter($items)) {
                echo '<div class="center-content">
                <svg height="100%" width="100%" xmlns:xlink="http://www.w3.org/1999/xlink"><a xlink:href="add.php"> 
                <text x="100" y="100" style="fill:black;font-size:50px;" transform="rotate(0,0,0)">Nothing found, add an item.</text> 
                </a></svg></div>';

            } else {
                foreach ($items as $item) include('templates/detailed.item.php');
                include_once ('templates/pagination.php');
            }
            ?>

        </div>
        <!-- ITEMS END -->
    </div>
</div>

<?php
$items = NULL;
include_once('templates/footer.php');
