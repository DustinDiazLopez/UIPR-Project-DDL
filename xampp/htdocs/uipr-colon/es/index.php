<?php
$title_tag = 'Inicio';
$allow_guests = TRUE;

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
            }

            include('templates/search.php'); 
            ?>
        </div>
        <!-- SEARCH END -->

        <!-- ITEMS START -->
        <div class="col-sm-9" id="items">
            <?php
            
            if (isset($_GET['q'])) {
                $only = 'all';
                if (isset($_GET['only'])) {
                    $only = $_GET['only'];
                }
                if (isset($conn)) {
                    $items = search($conn, $_GET['q'], isset($_GET['only']) ? $_GET['only'] : 'all');
                }

            } elseif (isset($_GET['author-search'])) {
                if (isset($_GET['author'])) {
                    if (isset($conn)) {
                        $items = SQL_GET_ITEMS_BY_AUTHOR_ID(mysqli_real_escape_string($conn, $_GET['author']));
                    }
                }
            } elseif (isset($_GET['subject-search'])) {
                if (isset($_GET['subject'])) {
                    if (isset($conn)) {
                        $items = SQL_GET_ITEMS_BY_SUBJECT_ID(mysqli_real_escape_string($conn, $_GET['subject']));
                    }
                }
            } elseif (isset($_GET['type-search'])) {
                if (isset($_GET['type'])) {
                    if (isset($conn)) {
                        $items = SQL_GET_ITEMS_BY_TYPE_ID(mysqli_real_escape_string($conn, $_GET['type']));
                    }
                }
            } else {
                $total = SQL_GET_ITEM_COUNT();
                if ($total > 0) {
                    $limit = 10;
                    $pages = ceil($total / $limit);
                    $page = min($pages, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, array(
                        'options' => array(
                            'default'   => 1,
                            'min_range' => 1,
                        ),
                    )));

                    $offset = ($page - 1)  * $limit;
                    // Some information to display to the user
                    $start = $offset + 1;
                    $end = min(($offset + $limit), $total);


                    $items = SQL_GET_ALL_ITEMS("ORDER BY i.create_at DESC LIMIT $limit OFFSET $offset");
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
                        "El archivo solicitado no parece existir..."
                    );
                } elseif ($_GET['error'] == "no-item") {
                    echo showWarn(
                        "404:",
                        "El articulo solicitado no parece existir..."
                    );
                } elseif ($_GET['error'] == "403") {
                    echo showDanger(
                        "403:",
                        "No tiene los privilegios para acceder esa área"
                    );
                }
            } elseif (isset($_GET['deleted'])) {
                echo showSuccess("Éxito - Se borró un artículo:", "Todavía tienes acceso a los PDFs relacionado con el artículo borrado.");
            } elseif (isset($_GET['created'])) {
                $t = json_decode($_GET['created']);
                echo showSuccess("Éxito:", "Se creo el articulo \"$t\"");
            }
            // IF NOTHING IS FOUND
            if (empty($items) || $items === NULL || count($items) == 0 || !array_filter($items)) {
                echo '<div class="center-content">
                <svg height="100%" width="100%" xmlns:xlink="http://www.w3.org/1999/xlink"><a xlink:href="add.php"> 
                <text x="100" y="100" style="fill:black;font-size:50px;" transform="rotate(0,0,0)">Nada encontrado, añadir un articulo.</text> 
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
