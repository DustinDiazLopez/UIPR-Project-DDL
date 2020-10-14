<?php include('templates/header.php'); ?>

<div class="container-fluid">
    <div class="row">
        <!-- SEARCH START -->
        <div class="col-sm-3 bg-light">
            <?php include('templates/search.php'); ?>
        </div>
        <!-- SEARCH END -->

        <!-- ITEMS START -->
        <div class="col-sm-9">
            <?php
            $items = SQL_GET_ALL_ITEMS();
            // IF NOTHING IS FOUND
            if (count($items) == 0) {
                echo '<div class="center-div"><p>I couldn\'t find anything...<p></div>';
            } else {
                foreach ($items as $item) include('templates/item.php');
                unset($items);
            }
            ?>
        </div>
        <!-- ITEMS END -->
    </div>
</div>

<?php include('templates/footer.php'); ?>