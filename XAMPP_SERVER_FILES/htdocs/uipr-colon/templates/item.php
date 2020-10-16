<br />
<!-- <?php echo $item['title']; ?> START -->
<div class="container-ddl" id="<?php echo $item['id']; ?>">
    <!-- <?php echo $item['title']; ?> TYPE START -->
    <h3 class="cap" title="Type of document">
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
            case "document":
                echo '<i class="fas fa-file-invoice"></i>';
                break;
            default:
                echo '<i class="far fa-file"></i>';
                break;
        }
        ?>
        <small><?php echo $item['type']; ?></small>
    </h3>
    <!-- <?php echo $item['title']; ?> TYPE END -->

    <hr>

    <!-- <?php echo $item['title']; ?> IMAGE START -->
    <div class="inline">
        <?php echo '<img alt="" class="img-thumbnail rounded" src="' . SQL_GET_IMAGE($item['image_id']) . '">'; ?>
    </div>
    <!-- <?php echo $item['title']; ?> IMAGE END -->

    <div class="inline">
        <!-- TITLE START -->
        <h4 title="The name of the document"><a href="#"><?php echo $item['title']; ?></a></h4>
        <!-- TITLE END -->

        <!-- <?php echo $item['title']; ?> AUTHORS START -->
        <?php
        $authors = SQL_GET_AUTHORS($item['id']);
        $icon = '<span class="fas fa-users">';
        switch (count($authors)) {
            case 1:
                $icon = '<span class="fas fa-user">';
                break;
            case 2:
                $icon = '<span class="fas fa-user-friends">';
                break;
        }

        echo '<h5 title="Authors">' . $icon . '</span> ' . AUTHORS_TO_CSV($authors) . '.</h5>';
        ?>
        <!-- <?php echo $item['title']; ?> AUTHORS END -->

        <!-- <?php echo $item['title']; ?> PUBLISHED DATE START -->
        <?php echo '<h5 title="Published date"><span class="fa fa-calendar-alt"></span> ' . FORMAT_DATE($item['published_date']) . '.</h5>'; ?>
        <!-- <?php echo $item['title']; ?> PUBLISHED DATE END -->

        <!-- <?php echo $item['title']; ?> SUBJECTS START -->
        <?php
        $subjects = SQL_GET_SUBJECTS($item['id']);
        echo '<h6 title="Subjects">';
        foreach ($subjects as $subject) echo "<span class=\"badge badge-dark\">{$subject['subject']}</span> ";
        echo '</h6>';
        ?>
        <!-- <?php echo $item['title']; ?> SUBJECTS END -->
    </div>

    <br class="clearBoth" />

    <!-- <?php echo $item['title']; ?> DESCRIPTION START -->
    <p class="text-justify border-right-0"><?php echo $item['description']; ?></p>
    <!-- <?php echo $item['title']; ?> DESCRIPTION END -->


    <!-- <?php echo $item['title']; ?> FILES START -->
    <h5>Files:</h5>
    <div class="container-fluid">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Filename</th>
                    <th scope="col">View</th>
                    <th scope="col">Download</th>
                    <th scope="col">File Size</th>
                </tr>
            </thead>
            <tbody>

                <?php foreach (SQL_GET_FILES($item['id']) as $f) : ?>

                    <tr>
                        <th scope="row"><?php echo $f['id']; ?></th>
                        <td scope="row"><?php echo $f['name']; ?></td>
                        <td scope="row">
                            <a class="font-weight-bold" href="javascript:void(0)" onclick="openPDFPHP('<?php echo $f['id']; ?>', false, '<?php echo $f['name']; ?>');" title="Open {$f['name']} in new tab.">
                                View
                            </a>
                        </td>
                        <td scope="row">
                            <a class="font-weight-bold" href="javascript:void(0)" onclick="openPDFPHP('<?php echo $f['id']; ?>', true, '<?php echo $f['id'] . '-' . $f['name']; ?>');" title="Download <?php echo $f['name']; ?>.">
                                Download
                            </a>
                        </td>
                        <td scope="row" class="font-weight-light"><?php echo $f['size']; ?> MB</td>
                    </tr>

                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <!-- <?php echo $item['title']; ?> FILES END -->



    <hr />
    <!-- <?php echo $item['title']; ?> MOD DATE START -->
    <p class="card-text" style="position: relative;bottom:0;right:0;">
        <small class="text-muted">
            <?php echo 'Last modified on ' . FORMAT_DATE($item['create_at']); ?>
        </small>
    </p>
    <!-- <?php echo $item['title']; ?> MOD DATE END -->

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
                    <h5 class="modal-title" id="exampleModalLabel">Delete <strong><?php echo $item['title']; ?></strong></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Please do keep in mind that this action is <strong title="cannot be undone"><u>irreversible</u></strong>.
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
<!-- <?php echo $item['title']; ?> END -->
<br />