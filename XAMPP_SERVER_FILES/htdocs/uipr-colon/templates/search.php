<h2>Search for a Document:</h2>
<form>
    <label for="search-query">Buscar:</label>
    <div class="input-group mb-3">
        <div class="input-group-prepend">
            <span class="input-group-text cap" id="selected-restriction">todos</span>
        </div>
        <input type="text" class="form-control" id="search-query" aria-describedby="search for a document" required aira-required>
    </div>

    <!-- RADIO START -->
    <label for="restrict">Realizar una búsqueda solo por:</label>
    <div id="restrict">
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="restrictSearchRadio" id="authorRestrict" value="Autor">
            <label class="form-check-label" for="authorRestrict">Autor</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="restrictSearchRadio" id="subjectRestrict" value="Sujeto">
            <label class="form-check-label" for="subjectRestrict">Sujeto</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="restrictSearchRadio" id="titleRestrict" value="Titulo">
            <label class="form-check-label" for="titleRestrict">Título</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="restrictSearchRadio" id="descriptionRestrict" value="Descripcion">
            <label class="form-check-label" for="descriptionRestrict">Descripción</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="restrictSearchRadio" id="fileRestrict" value="Archivo">
            <label class="form-check-label" for="fileRestrict">Archivo</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="restrictSearchRadio" id="all" value="Todos" checked aria-checked>
            <label class="form-check-label" for="all">Todos</label>
        </div>
    </div>
    <!-- RADIO END -->

    <br />

    <label for="types">Buscar por tipo de documento:</label>
    <div id="types">
        <?php
        $types = query(SQL_GET_DOC_TYPES);
        foreach ($types as $type) {
            echo '<div class="form-check form-check-inline">';
            echo "<input class=\"form-check-input\" type=\"checkbox\" id=\"{$type['type']}\" value=\"{$type['type']}\">";
            echo "<label class=\"form-check-label cap\" for=\"{$type['type']}\">{$type['type']}</label>";
            echo '</div>';
        }
        ?>
    </div>



    <div class="form-group">
        <br />
        <button class="btn btn-outline-success my-2 my-sm-0" name="advForm" type="submit" aria-label="Search">Buscar</button>
    </div>
</form>