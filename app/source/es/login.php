<?php
include_once('../connect.php');
include_once('utils/utils.php');
session_start();

$email = '';
$errors = array();

if (isset($_GET['noauth']) && !empty($_GET['noauth'])) {
    $_SESSION['redir'] = $_GET['noauth'];
}

if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === TRUE) {
    redir();
}

function not_valid(&$SESSION, &$errors, $msg = 'Credenciales no válidas.')
{
    $SESSION['authenticated'] = FALSE;
    $errors[] = $msg;
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
                $_SESSION['guest'] = FALSE;
                redir();
            } else {
                not_valid($_SESSION, $errors);
            }
        } else {
            not_valid($_SESSION, $errors);
        }
    }
}

if (isset($_POST['guest'])) {
    if (isset($conn)) {
        $_SESSION['id'] = -1;
        $_SESSION['email'] = 'Invitado(a)';
        $_SESSION['username'] = 'Invitado(a)';
        $_SESSION['authenticated'] = TRUE;
        $_SESSION['guest'] = TRUE;
        redir();
    }
}

?>

<!DOCTYPE html>
<html lang="<?php echo LANG; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo "Iniciar Sesión - " . APP_NAME; ?></title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="./../css/selection.color.css">
    <link rel="stylesheet" href="./../css/fa/css/all.css">

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
            <?php if (isset($_GET['se'])) : ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo 'Se ha cerrado la sesión por inactividad.'; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['noauth'])) : ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <?php echo 'Favor de iniciar la sesión o continuar como invitado(a) para acceder el enlace pedido'; ?>
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
            <div class="btn-group" role="group" aria-label="Basic example" style="width: 100%">
                <button type="submit" name="guest" value="guest" class="btn btn-outline-secondary" style="width: 100%">Continuar como Invitado(a) <i class="fas fa-hiking"></i></button>
                <button type="submit" name="submit" value="submit" class="btn btn-outline-primary" style="width: 100%">Iniciar Sesión <i class="fas fa-sign-in-alt"></i></button>
            </div>

        </form>
    </main>
</body>

</html>


<?php
if (isset($conn)) mysqli_close($conn);
