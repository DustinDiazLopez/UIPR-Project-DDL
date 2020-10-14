<br />
<div class="container-ddl" id="<?php echo $item['id']; ?>">
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
        <?php echo "<img alt=\"\" class=\"img-thumbnail rounded\" src=\"" . SQL_GET_IMAGE($item['image_id']) . "\">"; ?>
    </div>
    <!-- IMAGE END -->

    <div class="inline">
        <!-- TITLE START -->
        <h4 title="The name of the document"><a href="#"><?php echo $item['title']; ?></a></h4>
        <!-- TITLE END -->

        <!-- AUTHORS START -->
        <?php
        $authors = SQL_GET_AUTHORS($item['id']);
        $icon = '<span class="fas fa-users">';
        switch(count($authors)) {
            case 1:
                $icon = '<span class="fas fa-user">';
                break;
            case 2:
                $icon = '<span class="fas fa-user-friends">';
                break;
        }

        echo '<h5 title="Authors">' . $icon . '</span> ' . AUTHORS_TO_CSV($authors) . '.</h5>';
        ?>
        <!-- AUTHORS END -->

        <!-- PUBLISHED DATE START -->
        <?php echo '<h5 title="Published date"><span class="fa fa-calendar-alt"></span> ' . FORMAT_DATE($item['published_date']) . '.</h5>'; ?>
        <!-- PUBLISHED DATE END -->

        <!-- SUBJECTS START -->
        <?php
        $subjects = SQL_GET_SUBJECTS($item['id']);
        echo '<h6 title="Subjects">';
        foreach ($subjects as $subject) echo "<span class=\"badge badge-dark\">{$subject['subject']}</span> ";
        echo '</h6>';
        ?>
        <!-- SUBJECTS END -->
    </div>

    <br class="clearBoth" />

    <!-- DESCRIPTION START -->
    <p title="Description of <?php echo $item['title']; ?>"><?php echo $item['description']; ?></p>
    <!-- DESCRIPTION END -->

    <hr />

    <!-- FILES START -->
    <h5><a title="Show files of <?php echo $item['title']; ?>" data-toggle="collapse" href="#collapseFilesFor<?php echo $item['title'] . $item['id']; ?>" role="button" aria-expanded="false" aria-controls="collapseFilesFor<?php echo $item['title'] . $item['id']; ?>">Files: </a></h5>
    <div class="collapse" id="collapseFilesFor<?php echo $item['title'] . $item['id']; ?>">
        <div class="container-fluid">
            <div class="row">

                <?php
                $files = SQL_GET_FILES($item['id']);
                foreach ($files as $f) {
                    echo "<div class=\"col-sm-6\" title=\"File of {$item['title']}\">";
                    echo '<div class="card text-center">';
                    echo "<div class=\"card-header\">{$f['name']}</div>";
                    echo '<div class="card-body">';
                    echo "<a style=\"color:white\" onclick=\"openPDFPHP('{$f['id']}', 'false', '')\" class=\"btn btn-primary\" target=\"_blank\" title=\"Open {$f['name']} in new tab.\">View</a> ";
                    echo "<a style=\"color:white\" onclick=\"openPDFPHP('{$f['id']}', 'true', '{$f['id']}-{$f['name']}')\" class=\"btn btn-primary\" title=\"Download {$f['name']}.\">Download</a>";
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';

                }

                unset($files);
                ?>

            </div>
        </div>
    </div>
    <!-- FILES END -->



    <hr />
    <!-- MOD DATE START -->
    <p class="card-text" style="position: relative;bottom:0;right:0;">
        <small class="text-muted">
            <?php echo 'Last modified on ' . FORMAT_DATE($item['create_at']); ?>
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