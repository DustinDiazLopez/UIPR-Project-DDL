<?php
$background_color = 'rgba(255, 255, 255, 0.75)';
include_once('templates/loading.php');
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

function not_valid(&$SESSION, &$errors, $msg = 'Invalid credentials.')
{
    $SESSION['authenticated'] = FALSE;
    $errors[] = $msg;
}

if (isset($_POST['submit'])) {
    $email = $_POST['email'];
    if (empty($email)) {
        $errors[] = 'Provide an email or username.';
    }

    if (empty($_POST['pwd'])) {
        $errors[] = 'Provide a password.';
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
        $_SESSION['email'] = 'Guest';
        $_SESSION['username'] = 'Guest';
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
    <title><?php echo "Login - " . APP_NAME; ?></title>
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
                <h1>Login</h1>
            </div>
            <?php if (isset($_GET['se'])) : ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo 'You\'ve been logged out due to inactivity.'; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['noauth'])) : ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <?php echo 'Please login, or continue as a guest to access the requested link.'; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <?php if (isset($_POST['submit']) && array_filter($errors)) : ?>
                <?php foreach ($errors as $error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo "$error"; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
            <div class="form-group">
                <label for="email">Email <strong>or</strong> Username</label>
                <input type="text" class="form-control" id="email" name="email" placeholder="Username" value="<?php echo $email === '' ? '' : htmlspecialchars($email); ?>">
            </div>
            <div class="form-group">
                <label for="pwd">Password</label>
                <input type="password" class="form-control" name="pwd" id="pwd" placeholder="Password" value="">
            </div>
            <div class="btn-group" role="group" aria-label="Basic example" style="width: 100%">
                <button type="submit" name="guest" value="guest" class="btn btn-outline-secondary" style="width: 100%">
                    Continue as <b>Guest</b> <i class="fas fa-hiking"></i>
                </button>
                <button type="submit" name="submit" id="login" value="submit" class="btn btn-outline-primary" style="width: 100%">
                    Login <i class="fas fa-sign-in-alt"></i>
                </button>
            </div>
        </form>
    </main>

    <script charset="utf-8" type="text/javascript" src="./../js/jquery-3.2.1.slim.min.js"></script>
    <script>
        $(document).ready(function() {
            $(window).keydown(function(event){
                if(event.keyCode === 13) {
                    event.preventDefault();
                    document.getElementById('login').click();
                    return true;
                }
            });
        });
    </script>
</body>

</html>


<?php
if (isset($conn)) mysqli_close($conn);
