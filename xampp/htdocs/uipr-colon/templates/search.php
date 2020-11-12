<h2>Realizar una Búsqueda:</h2>
<nav>
    <div class="nav nav-tabs" id="nav-tab" role="tablist">
        <a class="nav-item nav-link active" id="nav-keyword-tab" data-toggle="tab" href="#nav-keyword" role="tab" aria-controls="nav-keyword" aria-selected="true">Frase</a>
        <a class="nav-item nav-link" id="nav-type-tab" data-toggle="tab" href="#nav-type" role="tab" aria-controls="nav-type" aria-selected="false">Tipo</a>
        <a class="nav-item nav-link" id="nav-subject-tab" data-toggle="tab" href="#nav-subject" role="tab" aria-controls="nav-subject" aria-selected="false">Sujeto</a>
        <a class="nav-item nav-link" id="nav-author-tab" data-toggle="tab" href="#nav-author" role="tab" aria-controls="nav-author" aria-selected="false">Autor(a)</a>
    </div>
</nav>
<div class="tab-content" id="nav-tabContent">
    <div class="tab-pane fade show active" id="nav-keyword" role="tabpanel" aria-labelledby="nav-keyword-tab">
        <form>
            <label for="search-query">Buscar: <?php hint('No sea demasiado específico. Evite palabras como: la, de, a, el, etc. Por ejemplo, en lugar de \'El Grito de Lares\', haz \'Grito Lares\'.'); ?></label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" id="search-query" name="q" required
                       value="<?php if (!empty($searched_value)) echo $searched_value; ?>">
            </div>

            <!-- RADIO START -->
            <div>
                <?php
                // persistence for radio buttons
                $file = $description = $title = '';
                $checked_text = 'checked aria-checked';
                $all = $checked_text;
                if (isset($_GET['only'])) {
                    switch ($_GET['only']) {
                        case 'title':
                            $title = $checked_text;
                            break;
                        case 'description':
                            $description = $checked_text;
                            break;
                        case 'file':
                            $file = $checked_text;
                            break;
                        default:
                            $all = $checked_text;
                            break;
                    }
                }
                ?>
                <p>Refinar:</p>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="only" id="titleRestrict" value="title" <?php echo $title; ?>>
                    <label class="form-check-label" for="titleRestrict">Título</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="only" id="descriptionRestrict" value="description" <?php echo $description; ?>>
                    <label class="form-check-label" for="descriptionRestrict">Descripción</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="only" id="fileRestrict" value="file" <?php echo $file; ?>>
                    <label class="form-check-label" for="fileRestrict">Archivo</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="only" id="all" value="all" <?php echo $all; ?>>
                    <label class="form-check-label" for="all">Todos</label>
                </div>
            </div>
            <!-- RADIO END -->

            <br />

            <div>
                <?php
                $all = $file = $description = $title = $checked = NULL;
                $types = query(SQL_GET_TYPES_W_RELATION);
                if (count($types) > 0) {
                    echo '<p>Incluir en la búsqueda sólo:</p>';
                    foreach ($types as $type) {
                        $checked = isset($_GET[$type['type']]) ? 'checked' : '';
                        echo '<div class="form-check form-check-inline">';
                        echo "<input class=\"form-check-input\" type=\"checkbox\" id=\"{$type['type']}\" name=\"{$type['type']}\" value=\"{$type['id']}\" $checked>";
                        echo "<label class=\"form-check-label cap\" for=\"{$type['type']}\">{$type['type']}</label>";
                        echo '</div>';
                    }
                }
                ?>
            </div>

            <div class="form-group">
                <br />
                <button class="btn btn-outline-success my-2 my-sm-0" name="search" type="submit" aria-label="Búsqueda genérica" style="width:100%;height:100%;">Buscar</button>
            </div>
        </form>
    </div>

    <div class="tab-pane fade show active" id="nav-type" role="tabpanel" aria-labelledby="nav-type-tab">
        <form>
            <div class="input-group">
                <select class="custom-select" id="type" name="type">
                    <option selected>Elige un tipo...</option>
                    <?php
                    //$types = query(SQL_GET_DOC_TYPES);
                    foreach ($types as $type) {
                        echo "<option value=\"{$type['id']}\">{$type['type']}</option>";
                    }
                    ?>
                </select>
                <div class="input-group-append">
                    <button class="btn btn-outline-success" name="type-search" type="submit" aria-label="Buscar por tipo">Buscar Tipo</button>
                </div>
            </div>
        </form>
    </div>

    <div class="tab-pane fade" id="nav-subject" role="tabpanel" aria-labelledby="nav-subject-tab">
        <form>
            <div class="input-group">
                <select class="custom-select" id="subject" name="subject">
                    <option selected>Elige un sujeto...</option>
                    <?php
                    $subjects = query(SQL_GET_SUBJECTS);
                    foreach ($subjects as $subject) {
                        echo "<option value=\"{$subject['id']}\">{$subject['subject']}</option>";
                    }
                    ?>
                </select>
                <div class="input-group-append">
                    <button class="btn btn-outline-success" name="subject-search" type="submit" aria-label="Buscar por sujeto">Buscar Sujeto</button>
                </div>
            </div>
        </form>
    </div>

    <div class="tab-pane fade" id="nav-author" role="tabpanel" aria-labelledby="nav-author-tab">
        <form>
            <div class="input-group">
                <select class="custom-select" id="author" name="author">
                    <option selected>Elige un autor(a)...</option>
                    <?php
                    $authors = query(SQL_GET_AUTHORS);
                    foreach ($authors as $author) {
                        echo "<option value=\"{$author['id']}\">{$author['author_name']}</option>";
                    }
                    ?>
                </select>
                <div class="input-group-append">
                    <button class="btn btn-outline-success" name="author-search" type="submit" aria-label="Buscar por autor(a)">Buscar Autor(a)</button>
                </div>
            </div>
        </form>
    </div>
</div>
