<h2>Create an Admin User</h2>

<?php
$username = $email = '';
if (isset($_POST['username'])) {
    $username = $_POST['username'];
} elseif (isset($_GET['username'])) {
    $username = $_GET['username'];
}

if (isset($_POST['email'])) {
    $email = $_POST['email'];
} elseif (isset($_GET['email'])) {
    $email = $_GET['email'];
}
?>
<form action="create.admin.php" method="POST" style="padding:10px;margin:0px;">
    <div class="form-group">
        <label for="username">Username</label>
        <input type="text" class="form-control" id="username" name="username" aria-describedby="username" placeholder="<?php echo $username; ?>" value="<?php echo $username; ?>" required>
    </div>
    <div class="form-group">
        <label for="email">E-mail</label>
        <input type="email" class="form-control" id="email" name="email" aria-describedby="email" placeholder="<?php echo $email; ?>" value="<?php echo $email; ?>" required>
    </div>
    <div class="form-group">
        <label for="pwd">Password</label>
        <input type="password" class="form-control" id="pwd" name="pwd" required>
    </div>
    <div class="form-group">
        <label for="repwd">Repeat Password</label>
        <input type="password" class="form-control" id="repwd" name="repwd" required>
    </div>
    <button type="submit" class="btn btn-primary" style="width:100%;height:100%;">Creat Admin</button>
</form>