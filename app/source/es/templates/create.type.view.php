<h2>Crear un Tipo</h2>

<?php
$type = '';
if (isset($_POST['type'])) {
    $type = $_POST['type'];
} elseif (isset($_GET['type'])) {
    $type = $_GET['type'];
}

?>
<form action="create.type.php" method="POST" style="padding:10px;margin:0px;">
    <div class="form-group">
        <label for="type">Nombre del Tipo</label>
        <input type="text" class="form-control" id="type" name="type" aria-describedby="type" placeholder="<?php echo $type; ?>" value="<?php echo $type; ?>" required>
    </div>
    <button type="submit" class="btn btn-primary" style="width:100%;height:100%;">Crear Tipo</button>
</form>