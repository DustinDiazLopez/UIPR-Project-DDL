<br/>
<!-- <?php if (isset($item)) echo $item['title']; ?> START -->
<div class="container-ddl" id="<?php echo $item['id']; ?>">
    <!-- TYPE START -->
    <h3 class="cap" title="Type of the item">
        <?php echo icon($item['type']) . " <small class='type'>{$item['type']}</small>"; ?>
    </h3>
    <!-- TYPE END -->

    <hr>

    <!-- IMAGE START -->
    <div class="inline">
        <?php echo '<img loading="lazy" alt="" class="img-thumbnail rounded" src="' . SQL_GET_IMAGE($item['image_id']) . '">'; ?>
    </div>
    <!-- IMAGE END -->

    <div class="inline">
        <!-- TITLE START -->
        <h4 title="Title of the item" class="title"><a
                    href="#<?php echo $item['id']; ?>"><?php echo $item['title']; ?></a></h4>
        <!-- TITLE END -->

        <!-- AUTHORS START -->
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

        echo '<h5 title="Authors">' . $icon . '</span> <span class="authors">' . authorsToCSV($authors, 'author_name') . '</span>.</h5>';
        ?>
        <!-- AUTHORS END -->

        <!-- PUBLISHED DATE START -->
        <h5 title="Publication date"><span class="fa fa-calendar-alt"></span>
            <?php echo formatDate($item['published_date'], $item['year_only'] == '1'); ?>
        </h5>
        <!-- PUBLISHED DATE END -->

        <!-- SUBJECTS START -->
        <?php
        $subjects = SQL_GET_SUBJECTS($item['id']);
        echo '<h6 title="Subjects">';
        foreach ($subjects as $subject) echo "<span class=\"badge badge-dark subject\">{$subject['subject']}</span> ";
        echo '</h6>';
        ?>
        <!-- SUBJECTS END -->
    </div>

    <br class="clearBoth"/>

    <!-- DESCRIPTION START -->
    <p class="border-right-0 description"><?php echo cleanHTML($item['description']); ?></p>
    <!-- DESCRIPTION END -->


    <?php $files = SQL_GET_FILES($item['id']); ?>
    <!-- FILES START -->
    <h5>File<?php echo count($files) == 1 ? '' : 's'; ?>:</h5>
    <div class="container-fluid">
        <table class="table table-hover">
            <thead>
            <tr>
                <?php if (isset($_SESSION['guest']) && $_SESSION['guest'] === FALSE): ?>
                    <th scope="col">ID</th>
                <?php endif; ?>
                <th scope="col">File Name</th>
                <th scope="col">Share</th>
                <th scope="col">Download</th>
                <th scope="col">New Tab</th>
                <th scope="col">Popup Window</th>
                <th scope="col">File Type</th>
                <th scope="col">File Size</th>
            </tr>
            </thead>
            <tbody>

            <?php
            $item_id = $item['id'];
            foreach ($files as $f) :
                ?>

                <tr>
                    <?php if (isset($_SESSION['guest']) && $_SESSION['guest'] === FALSE): ?>
                        <th scope="row"><?php echo $f['id']; ?></th>
                    <?php endif; ?>
                    <td scope="row" class="file"><?php echo $f['filename']; ?></td>
                    <td scope="row">
                        <input type="text" style="display: none" value="<?php
                        $encoded_id = urlencode(base64_encode('head-' . $f['id']));
                        echo shareURL($f['id']);

                        ?>" id="share-<?php echo $f['id']; ?>">
                        <button type="submit" class="btn btn-light copy-btn" id="share-btn-<?php echo $f['id']; ?>"
                                style="width:100%;height:100%;"
                                onclick="copyValueToClipboard('share-<?php echo $f['id']; ?>', 'share-btn-<?php echo $f['id']; ?>', true)"
                                onmouseover="changeIcon(this)" onmouseout="revertIcon(this)">
                            <i class="fas fa-share-alt"
                               onclick="copyValueToClipboard('share-<?php echo $f['id']; ?>', 'share-btn-<?php echo $f['id']; ?>', true)"></i>
                            <span class="sr-only">Share the file <?php echo $f['filename']; ?>.</span>
                        </button>
                    </td>
                    <td scope="row">
                        <form action="fetch.file.php" method="GET" style="padding:0px;margin:0px;" target="_blank">
                            <input type="hidden" id="<?php echo $f['filename'] . $f['id']; ?>ViewTab" name="file"
                                   value="<?php echo $encoded_id; ?>">
                            <button type="submit" class="btn btn-light" name="download" style="width:100%;height:100%;">
                                <i class="fas fa-download"></i> <span
                                        class="sr-only">Download <?php echo $f['filename']; ?></span>
                            </button>
                        </form>
                    </td>
                    <td scope="row">
                        <form action="file.php" method="GET" style="padding:0px;margin:0px;" target="_blank">
                            <input type="hidden" id="<?php echo $f['filename'] . $f['id']; ?>ViewTab" name="file"
                                   value="<?php echo $encoded_id; ?>">
                            <button type="submit" class="btn btn-light" name="view-file"
                                    style="width:100%;height:100%;"><i class="fas fa-external-link-alt"></i> <span
                                        class="sr-only">Open <?php echo $f['filename']; ?> in a new window</span>
                            </button>
                        </form>
                    </td>
                    <td scope="row">
                        <form action="file.php" method="GET" style="padding:0px;margin:0px;"
                              onsubmit='window.open("", "open-pdf-view-", "width=800,height=600,resizable=yes")'
                              target="open-pdf-view-">
                            <input type="hidden" id="<?php echo $f['filename'] . $f['id']; ?>ViewPopup" name="file"
                                   value="<?php echo $encoded_id; ?>">
                            <button type="submit" class="btn btn-light" name="view-file"
                                    style="width:100%;height:100%;"><i class="far fa-window-restore"></i> <span
                                        class="sr-only">Open <?php echo $f['filename']; ?> in pop-up window</span>
                            </button>
                        </form>
                    </td>
                    <td scope="row"
                        class="font-weight-light"><?php echo mime_content_type(PATH_TO_FILES_FOLDER . $f['path']); ?></td>
                    <td scope="row"
                        class="font-weight-light"><?php echo filesize(PATH_TO_FILES_FOLDER . $f['path']) / 1e+6; ?> MB
                    </td>
                </tr>

            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <!-- FILES END -->

    <hr/>
    <!-- MOD DATE START -->
    <p class="card-text" style="position: relative;bottom:0;right:0;">
        <small class="text-muted">
            <?php echo 'Last edited on ' . formatDate($item['create_at']); ?>
        </small>
    </p>
    <!-- MOD DATE END -->

    <!-- OVERLAY START -->
    <form action="edit.php" method="POST" style="padding:0;margin:0;">
        <div class="overlay">
            <input type="text" style="display: none" value="<?php


            echo shareURL($item['id'], '/item.view.php?item=');

            ?>" id="share-item-<?php echo $item['id']; ?>">
            <button type="button" class="icon-btn green" id="share-item-btn-<?php echo $item['id']; ?>"
                    onclick="copyValueToClipboard('share-item-<?php echo $item['id']; ?>', 'share-item-btn-<?php echo $item['id']; ?>', false)">
                <i class="fas fa-share-alt" title="Share <?php echo $item['title']; ?>"></i>
            </button>
            <?php if (isset($_SESSION['guest']) && $_SESSION['guest'] === FALSE): ?>
                <input type="hidden" value="<?php echo $item['id']; ?>" id="editItem" name="editItem">
                <button class="icon-btn edit" type="submit"><i class="fa fa-edit"
                                                               title="Edit <?php echo $item['title']; ?>."></i><span
                            class="sr-only"><?php echo 'Edit ' . $item['title']; ?></span></button>
                <button class="icon-btn delete" type="button" data-toggle="modal"
                        data-target="#deleteItem<?php echo $item['id']; ?>"><i class="fa fa-trash"
                                                                               title="Delete <?php echo $item['title']; ?>."></i><span
                            class="sr-only"><?php echo 'Delete ' . $item['title']; ?></span></button>
            <?php endif; ?>
        </div>
    </form>

    <!-- OVERLAY END -->

    <!-- Delete Modal START -->
    <div class="modal fade" id="deleteItem<?php echo $item['id']; ?>" tabindex="-1" role="dialog"
         aria-labelledby="confirm deletion" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLabel<?php echo $item['id']; ?>">Delete
                        <strong><?php echo $item['title']; ?></strong></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Keep in mind you'll still have access to the files related to this item:
                    <a href="adminpanel.php">via this link (the admin panel)</a>.
                </div>
                <div class="modal-footer">
                    <form action="delete.php" method="POST" style="padding:0px;margin:0px;">
                        <input type="hidden" id="<?php echo $item['title'] . $item['id']; ?>" name="item-to-delete"
                               value="<?php echo $item['id']; ?>">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger" name="delete-item">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal END -->
</div>
<!-- <?php echo $item['title']; ?> END -->

<br/>