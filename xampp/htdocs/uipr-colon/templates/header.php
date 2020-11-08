<?php
include_once('connect.php');
include_once('utils/utils.php');
authenticate();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <!-- meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Icon -->
    <link rel="icon" href="favicon.ico">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/responsive.table.css">
    <link rel="stylesheet" href="css/selection.color.css">
    <link rel="stylesheet" href="css/item.css">
    <link rel="stylesheet" href="css/header.css">
    <link href="css/fa/css/all.css" rel="stylesheet">
    <title><?php echo isset($title_tag) ? $title_tag . " - " . APP_NAME : APP_NAME; ?></title>

</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="index.php"><i class="fab fa-hive"></i> <?php echo APP_NAME; ?></a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarText">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="nav-link" href="add.php"><i class="fas fa-plus-circle"></i> Añadir un Artículo</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="adminpanel.php"><i class="fas fa-cog"></i> Panel del Administrador</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false"><i class="fas fa-server"></i> Documentación</a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="https://github.com/DustinDiazLopez/UIPR-Project-DDL/wiki/User-Doc"><i class="fas fa-user-graduate"></i> Usuario</a>
                        <a class="dropdown-item" href="https://github.com/DustinDiazLopez/UIPR-Project-DDL/wiki/Dev-Doc"><i class="fas fa-ghost"></i> Desarrolladores</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" target="_blank" href="https://github.com/DustinDiazLopez/UIPR-Project-DDL"><i class="fab fa-github"></i> Código Fuente</a>
                        <a class="dropdown-item" target="_blank" href="https://github.com/DustinDiazLopez/UIPR-Project-DDL/blob/main/LICENSE"><i class="far fa-copyright"></i> Licencia (<span style="font-family:courier new,courier,monospace; font-size:16px;">MIT</span>)</a>
                    </div>
                </li>
            </ul>
            <span class="navbar-text">
                <?php
                $error_no = $error = '';
                if (!isset($conn) || !$conn) {
                    $error_no = mysqli_connect_errno();
                    $error = mysqli_connect_error();
                    echo "<button type=\"button\" class=\"btn btn-danger\" title=\"$error\">Not Connected! <span class=\"badge badge-light\">Error No. $error_no</span></button>";
                }
                ?>

                <a href="logout.php" class="btn btn-outline-warning" style="font-weight:bold;color:green;" title="cerrar sessión"><?php echo isset($_SESSION['username']) ? $_SESSION['username'] : ''; ?> <i class="fas fa-sign-out-alt"></i></a>
            </span>
        </div>
    </nav>

    <?php
    if (!isset($conn) || !$conn) {
        echo showWarn("Uh-Oh! MySQL DB Error No. $error_no:", $error);
        die("");
    }
    ?>