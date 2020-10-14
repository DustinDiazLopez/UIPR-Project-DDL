<h2>Search for a Document:</h2>
<form>
    <div class="form-group">
        <label for="exampleInputEmail1">Search:</label>
        <input type="email" class="form-control" id="exampleInputEmail1" placeholder="e.g., Grito de Lares">
    </div>
    <?php
    $sql = 'SELECT `type` FROM `type`';
    $result = mysqli_query($conn, $sql);
    $types = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_free_result($result);
    foreach ($types as $type) {
        echo '<div class="form-check form-check-inline">';
        echo "<input class=\"form-check-input\" type=\"checkbox\" id=\"{$type['type']}\" value=\"{$type['type']}\">";
        echo "<label class=\"form-check-label\" for=\"{$type['type']}\" style=\"text-transform: capitalize;\">{$type['type']}</label>";
        echo '</div>';
    }
    ?>

    <div class="form-group">
        <br />
        <button class="btn btn-outline-success my-2 my-sm-0" name="advForm" type="submit" aria-label="Search">Search</button>
    </div>
</form>