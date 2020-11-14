<?php
include_once('connect.php');
include_once('utils/utils.php');

authenticate(TRUE);

if (isset($_POST['item']) && !empty($_POST['item'])) {
    $id = explode('-', base64_decode(urldecode($_POST['item'])))[1];
    $item = SQL_GET_ITEM_BY_ID(intval($id));
    if (count($item) > 0) $item = $item[0];
}


if ((isset($_GET['item']) && !empty($_GET['item'])) || (isset($_POST['item']) && !empty($_POST['item']))) {
    if (isset($_GET['item'])) {
        $id = explode('-', base64_decode(urldecode($_GET['item'])))[1];
    } else {
        $id = explode('-', base64_decode(urldecode($_POST['item'])))[1];
    }

    $item = SQL_GET_ITEM_BY_ID(intval($id));
    //$file = SQL_GET_FILE(intval((($_GET['file']))));
    if (count($item) > 0) $item = $item[0];
}

if (isset($item)) {
    $title_tag = $item['title'];
    $allow_guests = TRUE;
    include_once('templates/header.php');
} else {
    header('Location: index.php?error=no-item');
}
?>

    <div class="container-fluid">
        <div class="row">
            <!-- SEARCH START -->
            <div class="col-sm-3 bg-light">
                <?php include('templates/search.php'); ?>
            </div>
            <!-- SEARCH END -->

            <!-- ITEM START -->
            <div class="col-sm-9" id="items">
                <?php include_once('templates/detailed.item.php'); ?>
            </div>
            <!-- ITEM END -->
        </div>
    </div>


<?php
include_once ('templates/footer.php');
