<?php
include_once('connect.php');
include_once('utils/utils.php');
session_start();

$email = '';
$errors = array();

function redir($redir_loc='')
{
    if (isset($_SESSION['redir']) && !empty($_SESSION['redir'])) {
        $redir_loc = trim($_SESSION['redir']);
    }

    if (strpos($redir_loc, $_SERVER['HTTP_HOST']) === false) {
        if (empty($redir_loc)) {
            header("Location: index.php");
        } else {
            header("Location: index.php?error=not-to-host");
        }
    } else {
        header("Location: $redir_loc");
    }
}

if (isset($_GET['noauth']) && !empty($_GET['noauth'])) {
    $_SESSION['redir'] = $_GET['noauth'];
}

if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === TRUE) {
    redir();
}

if (isset($_POST['submit'])) {
    $email = $_POST['email'];
    if (empty($email)) {
        $errors[] = 'Provee un correo electrónico o nombre de usuario.';
    }

    if (empty($_POST['pwd'])) {
        $errors[] = 'Provee una contraseña.';
    }

    if (!array_filter($errors) && isset($conn)) {
        $id = SQL_GET_USER_ID_BY_UE(mysqli_real_escape_string($conn, $email));
        if (count($id) >= 1) {
            if (ddl_comp_pwd($_POST['pwd'], $id[0]['id'])) {
                $_SESSION['id'] = $id;
                $_SESSION['email'] = $id[0]['email'];
                $_SESSION['username'] = $id[0]['username'];
                $_SESSION['authenticated'] = TRUE;
                redir();
            } else {
                $_SESSION['authenticated'] = FALSE;
                $errors[] = 'Credenciales incorrectas.';
            }
        } else {
            $errors[] = 'Credenciales incorrectas.';
            $_SESSION['authenticated'] = FALSE;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/selection.color.css">
    <link href="css/fa/css/all.css" rel="stylesheet">

    <style>
        form {
            max-width: 460px;
            margin: 20px auto;
            padding: 20px;
        }
    </style>
</head>

<body>

    <main>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
            <div class="form-group">
                <h1>Iniciar Sesión</h1>
            </div>
            <?php if (isset($_GET['se'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo 'Se ha cerrado la sesión por inactividad.'; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <?php if (isset($_POST['submit']) && array_filter($errors)) : ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php foreach ($errors as $error) echo "$error"; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            <div class="form-group">
                <label for="email">Correo Electrónico <strong>o</strong> Nombre de Usuario</label>
                <input type="text" class="form-control" id="email" name="email" placeholder="su" value="<?php echo $email === '' ? 'colon' : htmlspecialchars($email); ?>" required>
            </div>
            <div class="form-group">
                <label for="pwd">Contraseña</label>
                <input type="password" class="form-control" name="pwd" id="pwd" placeholder="password" value="hello-password" required>
            </div>
            <button type="submit" name="submit" value="submit" class="btn btn-outline-success my-2 my-sm-0" style="width:100%;height:100%;">Iniciar Sesión</button>
        </form>
    </main>

    <script charset="utf-8" src="js/jquery-3.2.1.slim.min.js"></script>
    <script charset="utf-8" src="js/popper.min.js"></script>
    <script charset="utf-8" src="js/bootstrap.min.js"></script>
</body>

</html>


<?php
if (isset($conn)) mysqli_close($conn);
