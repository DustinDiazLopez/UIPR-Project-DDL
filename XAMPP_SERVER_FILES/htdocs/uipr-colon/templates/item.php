<br />
<div class="container-ddl">
    <!-- TYPE START -->
    <h3 style="text-transform: uppercase;" title="Type of document">
        <?php
        switch (strtolower($item['type'])) {
            case "libro":
                echo '<i class="fas fa-book"></i>';
                break;
            case "arte":
                echo '<i class="fas fa-paint-brush"></i>';
                break;
            case "foto":
                echo '<i class="far fa-image"></i>';
                break;
            case "periódico":
                echo '<i class="far fa-newspaper"></i>';
                break;
            case "revista":
                echo '<i class="fas fa-book-open"></i>';
                break;
            default:
                echo '<i class="far fa-file"></i>';
                break;
        }
        ?>
        <small><?php echo $item['type']; ?></small>
    </h3>
    <!-- TYPE END -->

    <hr>

    <!-- IMAGE START -->
    <div class="inline">
        <?php
        $sql = "SELECT image FROM image where id = '{$item['image_id']}'";
        // make query and get result
        $result_helper = mysqli_query($conn, $sql);

        // get the items as an associative array (i.e., in a format which would be easy to use)
        $image = mysqli_fetch_all($result_helper, MYSQLI_ASSOC);

        // free results from memory
        mysqli_free_result($result_helper);

        if (count($image) >= 1) {
            $en = base64_encode($image[0]['image']);
            echo "<img alt=\"\" class=\"img-thumbnail rounded\" src=\"data:image/jpeg;base64,{$en}\">";
        } else {
            echo '<img alt="" class="img-thumbnail rounded" src="images/pdf-placeholder.jpg">';
        }
        ?>
    </div>
    <!-- IMAGE END -->

    <div class="inline">
        <!-- TITLE START -->
        <h4 title="The name of the document"><a href="#"><?php echo $item['title']; ?></a></h4>
        <!-- TITLE END -->

        <!-- AUTHORS START -->
        <?php
        $sql = "SELECT a.author_name FROM author a inner join author_has_item ai inner join item i on i.id = ai.item_id and ai.author_id = a.id where i.id = '{$item['image_id']}'";
        // make query and get result
        $result_helper = mysqli_query($conn, $sql);

        // get the items as an associative array (i.e., in a format which would be easy to use)
        $authors = mysqli_fetch_all($result_helper, MYSQLI_ASSOC);
        // free results from memory
        mysqli_free_result($result_helper);

        echo '<h5 title="Authors"><span class="fas fa-users"></span> ';
        $len = count($authors);
        for ($i = 0; $i < $len; $i++) {
            echo $authors[$i]['author_name'];
            if ($i != $len - 1) echo ', ';
        }
        echo '.</h5>';
        ?>
        <!-- AUTHORS END -->

        <!-- PUBLISHED DATE START -->
        <?php echo '<h5 title="Published date"><span class="fa fa-calendar-alt"></span> ' . date("F jS, Y", strtotime(htmlspecialchars($item['published_date']))) . '.</h5>'; ?>
        <!-- PUBLISHED DATE END -->

        <!-- SUBJECTS START -->
        <?php
        $sql = 'SELECT `subject` FROM `subject`';
        $result = mysqli_query($conn, $sql);
        $types = mysqli_fetch_all($result, MYSQLI_ASSOC);
        mysqli_free_result($result);
        echo '<h6 title="Subjects">';
        foreach ($types as $type) echo "<span class=\"badge badge-dark\">{$type['subject']}</span> ";
        echo '</h6>';
        ?>
        <!-- SUBJECTS END -->
    </div>

    <br class="clearBoth" />

    <!-- DESCRIPTION START -->
    <p title="Description of the <?php echo $item['title']; ?>">
        <?php echo $item['description']; ?>
    </p>
    <!-- DESCRIPTION END -->

    <hr />

    <!-- FILES START -->
    <h5><a title="Show files of <?php echo $item['title']; ?>" data-toggle="collapse" href="#collapseFilesFor<?php echo $item['title'] . $item['id']; ?>" role="button" aria-expanded="false" aria-controls="collapseFilesFor<?php echo $item['title'] . $item['id']; ?>">Files: </a></h5>
    <div class="collapse" id="collapseFilesFor<?php echo $item['title'] . $item['id']; ?>">
        <div class="container-fluid">
            <div class="row">

                <?php
                $sql = "SELECT fi.item_id, f.id, f.`file` FROM `file` f inner join file_has_item fi inner join item i on i.id = fi.item_id and fi.file_id = f.id where i.id = '{$item['image_id']}'";
                // make query and get result
                $result_helper = mysqli_query($conn, $sql);

                // get the items as an associative array (i.e., in a format which would be easy to use)
                $file = mysqli_fetch_all($result_helper, MYSQLI_ASSOC);
                // free results from memory
                mysqli_free_result($result_helper);

                $temp;
                $name;
                foreach ($file as $f) {
                    $temp = base64_encode($f['file']);
                    $name = $f['id'];
                    echo "<div class=\"col-sm-6\" title=\"File of {$item['title']}\">";
                    echo '<div class="card text-center">';
                    echo "<div class=\"card-header\">$name</div>";
                    echo '<div class="card-body">';
                    //echo "<object data=\"data:application/pdf;base64,$temp\" type=\"application/pdf\" style=\"height:200px;width:60%\"></object>";
                    echo "<a style=\"color:white\" onclick=\"openPDFDDL('data:application/pdf;base64,$temp')\" class=\"btn btn-primary\" target=\"_blank\" title=\"Open $name in new tab.\">View</a> ";
                    echo "<a download=\"{$f['id']}-$name\" href=\"data:application/pdf;base64,$temp\" class=\"btn btn-primary\" title=\"Download $name.\">Download</a>";
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
                ?>

            </div>
        </div>
    </div>
    <!-- FILES END -->



    <hr />
    <!-- MOD DATE START -->
    <p class="card-text" style="position: relative;bottom:0;right:0;">
        <small class="text-muted">
            <?php echo 'Last modified on ' . date("F jS, Y", strtotime(htmlspecialchars($item['create_at']))); ?>
        </small>
    </p>
    <!-- MOD DATE END -->

    <div class="overlay">
        <!-- <button class="icon-btn view" data-toggle="modal" data-target="#exampleModalLong"><i class="fa fa-external-link-alt" title="Open in new tab."></i></button> -->
        <button class="icon-btn edit"><i class="fa fa-edit" title="Edit <?php echo $item['title']; ?> in new tab."></i></button>
        <button class="icon-btn delete" data-toggle="modal" data-target="#deleteItem<?php echo $item['id']; ?>"><i class="fa fa-trash" title="Delete <?php echo $item['title']; ?>."></i></button>
    </div>

    <!-- Delete <?php echo $item['title']; ?> Modal START -->
    <div class="modal fade" id="deleteItem<?php echo $item['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="confirm deletion" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Delete item</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    ...
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger">Delete</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Delete <?php echo $item['title']; ?> Modal END -->
</div>
<br />