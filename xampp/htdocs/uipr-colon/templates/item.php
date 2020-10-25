<br />
<!-- <?php echo $item['title']; ?> START -->
<div class="container-ddl" id="<?php echo $item['id']; ?>">
    <!-- <?php echo $item['title']; ?> TYPE START -->
    <h3 class="cap" title="Tipo del document">
        <?php
        switch (strtolower($item['type'])) {
            case "libro":
            case "book":
                echo '<i class="fas fa-book"></i>';
                break;
            case "novel":
            case "novela":
                echo '<i class="fas fa-book-reader"></i>';
                break;
            case "arte":
            case "art":
                echo '<i class="fas fa-paint-brush"></i>';
                break;
            case "foto":
            case "photo":
            case "picture":
                echo '<i class="far fa-image"></i>';
                break;
            case "periódico":
            case "periodico":
            case "newspaper":
                echo '<i class="far fa-newspaper"></i>';
                break;
            case "revista":
            case "magazine":
                echo '<i class="fas fa-book-open"></i>';
                break;
            case "document":
            case "documento":
                echo '<i class="fas fa-file-invoice"></i>';
                break;
            case "word":
            case "word document":
            case "doc":
            case "docx":
                echo '<i class="far fa-file-word"></i>';
                break;
            case "ppt":
            case "pptx":
            case "powerpoint":
            case "powerpoint presentation":
                echo '<i class="far fa-file-powerpoint"></i>';
                break;
            case "excel":
            case "xlsx":
            case "xls":
            case "excel spreadsheet":
                echo '<i class="far fa-file-excel"></i>';
                break;
            case "csv":
            case "comma-separated values":
            case "comma separated values":
                echo '<i class="fas fa-file-csv"></i>';
                break;
            case "pdf":
                echo '<i class="fas fa-file-pdf"></i>';
                break;
            case "zip":
            case "archive":
                echo '<i class="fas fa-file-archive"></i>';
                break;
            case "code":
            case "programming":
                echo '<i class="fas fa-file-code"></i>';
                break;
            case "video":
            case "movie":
            case "animation":
                echo '<i class="far fa-file-video"></i>';
                break;
            case "audio":
            case "song":
            case "music":
                echo '<i class="far fa-file-audio"></i>';
                break;
            case "media":
                echo '<i class="fas fa-photo-video"></i>';
                break;
            case "atlast":
                echo '<i class="fas fa-atlas"></i>';
                break;
            case "bible":
                echo '<i class="fas fa-bible"></i>';
                break;
            case "quran":
                echo '<i class="fas fa-quran"></i>';
                break;
            case "torah":
                echo '<i class="fas fa-torah"></i>';
                break;
            default:
                echo '<i class="far fa-file-alt"></i>';
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
        <h4 title="Nombre del artículo"><a href="#"><?php echo $item['title']; ?></a></h4>
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

        echo '<h5 title="Autores">' . $icon . '</span> ' . AUTHORS_TO_CSV($authors, 'author_name') . '.</h5>';
        ?>
        <!-- <?php echo $item['title']; ?> AUTHORS END -->

        <!-- <?php echo $item['title']; ?> PUBLISHED DATE START -->
        <?php

        echo '<h5 title="Fecha de Publicación"><span class="fa fa-calendar-alt"></span> ' . FORMAT_DATE($item['published_date'], $item['year_only'] == '1') . '.</h5>'; 
        ?>
        <!-- <?php echo $item['title']; ?> PUBLISHED DATE END -->

        <!-- <?php echo $item['title']; ?> SUBJECTS START -->
        <?php
        $subjects = SQL_GET_SUBJECTS($item['id']);
        echo '<h6 title="Sujetos">';
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
    <h5>Archivos:</h5>
    <div class="container-fluid">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Nombre del Archivo</th>
                    <th scope="col">Ver</th>
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
                            <form action="file.php" method="POST" style="padding:0px;margin:0px;">
                                <input type="hidden" id="<?php echo $name; ?>View" name="file" value="<?php echo $f['path']; ?>">
                                <button type="submit" class="btn btn-light" name="view-file">Ver</button>
                            </form>
                        </td>
                        <td scope="row">
                            <form action="file.php" method="POST" style="padding:0px;margin:0px;">
                                <input type="hidden" id="<?php echo $name; ?>Download" name="file" value="<?php echo $f['path']; ?>">
                                <button type="submit" class="btn btn-light" name="download-file">Descargar</button>
                            </form>
                        </td>
                        <td scope="row" class="font-weight-light"><?php echo mime_content_type($path); ?></td>
                        <td scope="row" class="font-weight-light"><?php echo filesize($path) / 1e+6; ?> MB</td>
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
        <button class="icon-btn edit"><i class="fa fa-edit" title="Editar <?php echo $item['title']; ?>."></i></button>
        <button class="icon-btn delete" data-toggle="modal" data-target="#deleteItem<?php echo $item['id']; ?>"><i class="fa fa-trash" title="Borrar <?php echo $item['title']; ?>."></i></button>
    </div>

    <!-- Delete <?php echo $item['title']; ?> Modal START -->
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
    <!-- Delete <?php echo $item['title']; ?> Modal END -->
</div>
<!-- <?php echo $item['title']; ?> END -->
<br />