<br />
<!-- <?php echo $item['title']; ?> START -->
<div class="container-ddl" id="<?php echo $item['id']; ?>">
    <!-- TYPE START -->
    <h3 class="cap" title="Tipo del document">
        <?php echo icon($item['type']) . " <small>{$item['type']}</small>"; ?>
    </h3>
    <!-- TYPE END -->

    <hr>

    <!-- IMAGE START -->
    <div class="inline">
        <?php echo '<img alt="" class="img-thumbnail rounded" src="' . SQL_GET_IMAGE($item['image_id']) . '">'; ?>
    </div>
    <!-- IMAGE END -->

    <div class="inline">
        <!-- TITLE START -->
        <h4 title="Nombre del artículo"><a href="#"><?php echo $item['title']; ?></a></h4>
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

        echo '<h5 title="Autores">' . $icon . '</span> ' . AUTHORS_TO_CSV($authors, 'author_name') . '.</h5>';
        ?>
        <!-- AUTHORS END -->

        <!-- PUBLISHED DATE START -->
        <h5 title="Fecha de Publicación"><span class="fa fa-calendar-alt"></span>
            <?php echo FORMAT_DATE($item['published_date'], $item['year_only'] == '1'); ?>
        </h5>
        <!-- PUBLISHED DATE END -->

        <!-- SUBJECTS START -->
        <?php
        $subjects = SQL_GET_SUBJECTS($item['id']);
        echo '<h6 title="Sujetos">';
        foreach ($subjects as $subject) echo "<span class=\"badge badge-dark\">{$subject['subject']}</span> ";
        echo '</h6>';
        ?>
        <!-- SUBJECTS END -->
    </div>

    <br class="clearBoth" />

    <!-- DESCRIPTION START -->
    <p class="border-right-0"><?php echo $item['description']; ?></p>
    <!-- DESCRIPTION END -->


    <!-- FILES START -->
    <h5>Archivos:</h5>
    <div class="container-fluid">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Nombre del Archivo</th>
                    <th scope="col">En una Pestaña Nueva</th>
                    <th scope="col">En Ventana Emergente</th>
                    <th scope="col">Descargar</th>
                    <th scope="col">Tipo de Archivo</th>
                    <th scope="col">Tamaño del Archivo</th>
                </tr>
            </thead>
            <tbody>

                <?php
                $path = $name = '';
                $item_id = $item['id'];
                foreach (SQL_GET_FILES($item['id']) as $f) :
                    $path = FILE_FOLDER . '/' . $f['path'];
                    $name = basename($path);
                    $mod_name = '';

                    $arr_name = explode('.', $name);
                    $len = count($arr_name);
                    if ($len > 2) {
                        for ($i = 2; $i < $len; $i++) {
                            if ($i == $len - 1) $mod_name .= '.';
                            $mod_name .= $arr_name[$i];
                        }

                        $mod_name = '...' . $mod_name;
                    } else
                        $mod_name = $name;
                ?>

                    <tr>
                        <th scope="row"><?php echo $f['id']; ?></th>
                        <td scope="row"><?php echo $mod_name; ?></td>
                        <td scope="row">
                            <form action="file.php" method="POST" style="padding:0px;margin:0px;" target="_blank">
                                <input type="hidden" id="<?php echo $name; ?>View" name="file" value="<?php echo $f['path']; ?>">
                                <button type="submit" class="btn btn-light" name="view-file" style="width:100%;height:100%;">Ver</button>
                            </form>
                        </td>
                        <td scope="row">
                            <form action="file.php" method="POST" style="padding:0px;margin:0px;" onsubmit='window.open("", "open-pdf-view-<?php $mod_name; ?>", "width=800,height=600,resizable=yes")' target="open-pdf-view-<?php $mod_name; ?>">
                                <input type="hidden" id="<?php echo $name; ?>View" name="file" value="<?php echo $f['path']; ?>">
                                <button type="submit" class="btn btn-light" name="view-file" style="width:100%;height:100%;">Abrir</button>
                            </form>
                        </td>
                        <td scope="row">
                            <form action="file.php" method="POST" style="padding:0px;margin:0px;">
                                <input type="hidden" id="<?php echo $name; ?>Download" name="file" value="<?php echo $f['path']; ?>">
                                <button type="submit" class="btn btn-light" name="download-file" style="width:100%;height:100%;">Descargar</button>
                            </form>
                        </td>
                        <td scope="row" class="font-weight-light"><?php echo mime_content_type($path); ?></td>
                        <td scope="row" class="font-weight-light"><?php echo filesize($path) / 1e+6; ?> MB</td>
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
            <?php echo 'Últimamente editado el ' . FORMAT_DATE($item['create_at']); ?>
        </small>
    </p>
    <!-- MOD DATE END -->

    <div class="overlay">
        <button class="icon-btn edit"><i class="fa fa-edit" title="Editar <?php echo $item['title']; ?>." alt="Editar <?php echo $item['title']; ?>."></i><span class="sr-only"><?php echo 'Editar ' . $item['title']; ?></span></button>
        <button class="icon-btn delete" data-toggle="modal" data-target="#deleteItem<?php echo $item['id']; ?>"><i class="fa fa-trash" title="Borrar <?php echo $item['title']; ?>."></i><span class="sr-only"><?php echo 'Borrar ' . $item['title']; ?></span></button>
    </div>

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
                    Tenga en cuenta que esta acción es <strong title="no se puede deshacer"><u>irreversible</u></strong>.
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