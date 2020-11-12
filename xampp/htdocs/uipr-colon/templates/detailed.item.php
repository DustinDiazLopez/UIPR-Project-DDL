<br />
<!-- <?php if (isset($item)) echo $item['title']; ?> START -->
<div class="container-ddl" id="<?php echo $item['id']; ?>">
    <!-- TYPE START -->
    <h3 class="cap" title="Tipo del document">
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
        <h4 title="Nombre del artículo" class="title"><a href="#<?php echo $item['id']; ?>"><?php echo $item['title']; ?></a></h4>
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

        echo '<h5 title="Autores">' . $icon . '</span> <span class="authors">' . authorsToCSV($authors, 'author_name') . '</span>.</h5>';
        ?>
        <!-- AUTHORS END -->

        <!-- PUBLISHED DATE START -->
        <h5 title="Fecha de Publicación"><span class="fa fa-calendar-alt"></span>
            <?php echo formatDate($item['published_date'], $item['year_only'] == '1'); ?>
        </h5>
        <!-- PUBLISHED DATE END -->

        <!-- SUBJECTS START -->
        <?php
        $subjects = SQL_GET_SUBJECTS($item['id']);
        echo '<h6 title="Sujetos">';
        foreach ($subjects as $subject) echo "<span class=\"badge badge-dark subject\">{$subject['subject']}</span> ";
        echo '</h6>';
        ?>
        <!-- SUBJECTS END -->
    </div>

    <br class="clearBoth" />

    <!-- DESCRIPTION START -->
    <p class="border-right-0 description"><?php echo $item['description']; ?></p>
    <!-- DESCRIPTION END -->


    <?php $files = SQL_GET_FILES($item['id']); ?>
    <!-- FILES START -->
    <h5>Archivo<?php echo count($files) == 1 ? '' : 's'; ?>:</h5>
    <div class="container-fluid">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Nombre del Archivo</th>
                    <th scope="col">Compartir</th>
                    <th scope="col">Pestaña Nueva</th>
                    <th scope="col">Ventana Emergente</th>
                    <th scope="col">Tipo de Archivo</th>
                    <th scope="col">Tamaño del Archivo</th>
                </tr>
            </thead>
            <tbody>

                <?php
                $item_id = $item['id'];
                foreach ($files as $f) :
                ?>

                    <tr>
                        <th scope="row"><?php echo $f['id']; ?></th>
                        <td scope="row" class="file"><?php echo $f['filename']; ?></td>
                        <td scope="row">
                            <input type="text" style="display: none" value="<?php
                            $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";


                            $basename = basename($_SERVER['REQUEST_URI'], '?' . $_SERVER['QUERY_STRING']);
                            $pos = strpos($url, $basename);
                            if ($pos) {
                                $url = substr($url, 0, $pos);
                            }


                            if (strEndsWith($url, '/'))  {
                                $url = substr($url, 0, strlen($url) - 1);
                            }

                            echo "$url/file.php?file={$f['id']}";

                            ?>" id="share-<?php echo $f['id']; ?>">
                            <button type="submit" class="btn btn-light copy-btn"
                                    style="width:100%;height:100%;" onclick="copyValueToClipboard('share-<?php echo $f['id']; ?>', this)" onmouseover="changeIcon(this)" onmouseout="revertIcon(this)">
                                <i class="fas fa-share-alt"></i> <span class="sr-only">Compartir el documento <?php echo $f['filename']; ?>.</span>
                            </button>
                        </td>
                        <td scope="row">
                            <form action="file.php" method="GET" style="padding:0px;margin:0px;" target="_blank">
                                <input type="hidden" id="<?php echo $f['filename'] . $f['id']; ?>View" name="file" value="<?php echo $f['id']; ?>">
                                <button type="submit" class="btn btn-light" name="view-file" style="width:100%;height:100%;"><i class="fas fa-external-link-alt"></i> <span class="sr-only">Abrir documento <?php echo $f['filename']; ?> en una pestaña nueva</span></button>
                            </form>
                        </td>
                        <td scope="row">
                            <form action="file.php" method="GET" style="padding:0px;margin:0px;" onsubmit='window.open("", "open-pdf-view-", "width=800,height=600,resizable=yes")' target="open-pdf-view-">
                                <input type="hidden" id="<?php echo $f['filename'] . $f['id']; ?>View" name="file" value="<?php echo $f['id']; ?>">
                                <button type="submit" class="btn btn-light" name="view-file" style="width:100%;height:100%;"><i class="far fa-window-restore"></i> <span class="sr-only">Abrir documento <?php echo $f['filename']; ?> en una ventana emergente</span></button>
                            </form>
                        </td>
                        <td scope="row" class="font-weight-light"><?php echo $f['type']; ?></td>
                        <td scope="row" class="font-weight-light"><?php echo $f['size'] / 1e+6; ?> MB</td>
                    </tr>

                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <!-- FILES END -->

    <hr />
    <!-- MOD DATE START -->
    <p class="card-text" style="position: relative;bottom:0;right:0;">
        <small class="text-muted">
            <?php echo 'Últimamente editado el ' . formatDate($item['create_at']); ?>
        </small>
    </p>
    <!-- MOD DATE END -->

    <!-- OVERLAY START -->
    <?php if (isset($_SESSION['guest']) && $_SESSION['guest'] === FALSE): ?>
    <form action="edit.php" method="POST" style="padding:0;margin:0;">
        <div class="overlay">
            <input type="hidden" value="<?php echo $item['id']; ?>" id="editItem" name="editItem">
            <button class="icon-btn edit" type="submit"><i class="fa fa-edit" title="Editar <?php echo $item['title']; ?>."></i><span class="sr-only"><?php echo 'Editar ' . $item['title']; ?></span></button>
            <button class="icon-btn delete" type="button" data-toggle="modal" data-target="#deleteItem<?php echo $item['id']; ?>"><i class="fa fa-trash" title="Borrar <?php echo $item['title']; ?>."></i><span class="sr-only"><?php echo 'Borrar ' . $item['title']; ?></span></button>
        </div>
    </form>
    <?php endif; ?>
    <!-- OVERLAY END -->

    <!-- Delete Modal START -->
    <div class="modal fade" id="deleteItem<?php echo $item['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="confirm deletion" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLabel<?php echo $item['id']; ?>">Borrar <strong><?php echo $item['title']; ?></strong></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Tenga en cuenta que esta acción es <strong title="no se puede deshacer"><u>irreversible</u></strong>, pero todavía tendrá acceso a los PDFs relacionado con este articulo <a href="adminpanel.php">a través de este enlace</a>.
                </div>
                <div class="modal-footer">
                    <form action="delete.php" method="POST" style="padding:0px;margin:0px;">
                        <input type="hidden" id="<?php echo $item['title'] . $item['id']; ?>" name="item-to-delete" value="<?php echo $item['id']; ?>">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger" name="delete-item">Borrar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal END -->
</div>
<!-- <?php echo $item['title']; ?> END -->

<br />