<?php include('templates/header.php'); ?>

<?php


// write query for all the itemds
$sql = 'SELECT i.id, i.title, t.`type`, i.image_id, i.description, i.meta, i.create_at, i.published_date 
	FROM item i INNER JOIN `type` t ON i.type_id = t.id  LIMIT 10';

// make query and get result
$result = mysqli_query($conn, $sql);

// get the items as an associative array (i.e., in a format which would be easy to use)
$items = mysqli_fetch_all($result, MYSQLI_ASSOC);

for ($j = 0; $j < 100; $j++) {
    $items[] = $items[0];
}

//$items = array();

// free results from memory
mysqli_free_result($result);


//print_r($items);

?>

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
            // IF NOTHING IS FOUND
            if (count($items) == 0) echo '<div class="center-div"><p>I couldn\'t find anything...<p></div>';
            ?>
            <?php foreach ($items as $item) include('templates/item.php'); ?>
        </div>
        <!-- ITEMS END -->
    </div>
</div>

<?php include('templates/footer.php'); ?>