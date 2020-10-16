<h2>Search for a Document:</h2>
<form>
    <label for="search-query">Search:</label>
    <div class="input-group mb-3">
        <div class="input-group-prepend">
            <span class="input-group-text cap" id="selected-restriction">all</span>
        </div>
        <input type="text" class="form-control" id="search-query" aria-describedby="search for a document" required aira-required>
    </div>

    <!-- RADIO START -->
    <label for="restrict">Search only by:</label>
    <div id="restrict">
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="restrictSearchRadio" id="authorRestrict" value="author">
            <label class="form-check-label" for="authorRestrict">Author</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="restrictSearchRadio" id="subjectRestrict" value="subject">
            <label class="form-check-label" for="subjectRestrict">Subject</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="restrictSearchRadio" id="titleRestrict" value="title">
            <label class="form-check-label" for="titleRestrict">Title</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="restrictSearchRadio" id="all" value="all" checked aria-checked>
            <label class="form-check-label" for="all">All</label>
        </div>
    </div>
    <!-- RADIO END -->

    <br />

    <label for="types">Search by document type:</label>
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
        <button class="btn btn-outline-success my-2 my-sm-0" name="advForm" type="submit" aria-label="Search">Search</button>
    </div>
</form>