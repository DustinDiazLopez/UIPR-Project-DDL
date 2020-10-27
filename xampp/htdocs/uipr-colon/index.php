<?php include('templates/header.php'); ?>

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
                $items = search($conn, $_GET['q'], isset($_GET['only']) ? $_GET['only'] : 'all');
            } elseif (isset($_GET['author-search'])) {
                if (isset($_GET['author'])) {
                    $items = SQL_GET_AUTHOR_ITEMS(mysqli_real_escape_string($conn, $_GET['author']));
                }
            } else {
                $items = SQL_GET_ALL_ITEMS('ORDER BY i.create_at DESC');
            }
            if (isset($_GET['error'])) {
                if ($_GET['error'] == "file") {
                    echo showWarn("Warning:", "A request for a file was made, but failed. You may be missing form data expected key <code>view-file</code> or <code>download-file</code> to be present with another key <code>file</code>, specifying the path (path in the database).");
                }
            }
            // IF NOTHING IS FOUND
            if ($items === NULL || count($items) == 0 || !array_filter($items)) {
                echo '<div class="center-content">
                <svg height="100%" width="100%" xmlns:xlink="http://www.w3.org/1999/xlink"><a xlink:href="add.php" alt="Nada encontrado, añadir un articulo."> 
                <text x="100" y="100" style="fill:black;font-size:100px;" transform="rotate(0,0)">...</text> 
                </a></svg></div>';
            } else {
                foreach ($items as $item) include('templates/detailed.item.php');
            }
            ?>

        </div>
        <!-- ITEMS END -->
    </div>
</div>

<?php
$items = null;
include('templates/footer.php');
