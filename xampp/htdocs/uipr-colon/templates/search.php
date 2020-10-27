<h2>Buscar un documento:</h2>
<form>
    <label for="search-query">Buscar: <?php hint('No sea demasiado específico. Evite palabras como: la, de, a, el, etc. Por ejemplo, en lugar de \'El Grito de Lares\', haz \'Grito Lares\'.'); ?></label>
    <div class="input-group mb-3">
        <input type="text" class="form-control" id="search-query" name="q" required value="<?php echo $searched_value; ?>">
    </div>

    <!-- RADIO START -->
    <div>
        <?php
        // persistance for radio buttons
        $all = $file = $description = $title = '';
        $checked_text = 'checked aria-checked';
        if (isset($_GET['only'])) {
            $selected = htmlspecialchars($_GET['only']);
            switch ($selected) {
                case 'Titulo':
                    $title = $checked_text;
                    break;
                case 'Descripcion':
                    $description = $checked_text;
                    break;
                case 'Archivo':
                    $file = $checked_text;
                    break;
                default:
                    $all = $checked_text;
                    break;
            }
        }
        ?>
        <h3>Realizar una búsqueda solo por:</h3>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="only" id="titleRestrict" value="Titulo" <?php echo $title; ?>>
            <label class="form-check-label" for="titleRestrict">Título</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="only" id="descriptionRestrict" value="Descripcion" <?php echo $description; ?>>
            <label class="form-check-label" for="descriptionRestrict">Descripción</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="only" id="fileRestrict" value="Archivo" <?php echo $file; ?>>
            <label class="form-check-label" for="fileRestrict">Archivo</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="only" id="all" value="Todos" <?php echo $all; ?>>
            <label class="form-check-label" for="all">Todos</label>
        </div>
    </div>
    <!-- RADIO END -->

    <br />

    <h3>Buscar por tipo de documento:</h3>
    <div>
        <?php
        $all = $file = $description = $title = $checked = NULL;
        $types = query(SQL_GET_DOC_TYPES);
        foreach ($types as $type) {
            $checked = isset($_GET[$type['type']]) ? 'checked' : '';
            echo '<div class="form-check form-check-inline">';
            echo "<input class=\"form-check-input\" type=\"checkbox\" id=\"{$type['type']}\" name=\"{$type['type']}\" value=\"{$type['id']}\" $checked>";
            echo "<label class=\"form-check-label cap\" for=\"{$type['type']}\">{$type['type']}</label>";
            echo '</div>';
        }
        ?>
    </div>



    <div class="form-group">
        <br />
        <button class="btn btn-outline-success my-2 my-sm-0" name="search" type="submit" aria-label="Búsqueda genérica" style="width:100%;height:100%;">Buscar</button>
    </div>
</form>

<hr>

<h2>Buscar un autor:</h2>
<form>
    <div class="input-group">
        <select class="custom-select" id="author" name="author">
            <!-- <option selected>Elige un autor(a)...</option> -->
            <?php
            $authors = query(SQL_GET_AUTHORS);
            foreach ($authors as $author) {
                echo "<option value=\"{$author['id']}\">{$author['author_name']}</option>";
            }
            ?>
        </select>
        <div class="input-group-append">
            <button class="btn btn-outline-success" name="author-search" type="submit" aria-label="Buscar un autor(a)">Button</button>
        </div>
    </div>
</form>