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
    <link href="css/fa/css/all.css" rel="stylesheet">
    <title>Library CMS - DDL</title>

    <style>
        .cap {
            text-transform: capitalize;
        }

        a:link {
            text-decoration: none;
            color: rgba(0, 150, 0, 255) !important;
        }

        a:visited {
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        a:active {
            text-decoration: underline;
        }

        .brand {
            background: #dbd123 !important;
        }

        .brand-text {
            color: #04b800 !important;
        }

        form {
            max-width: 460px;
            margin: 20px auto;
            padding: 20px;
        }

        .center-content {
            width: 100%;
            display: flex;
            justify-content: center;
        }

        :target {
            border-radius: 3px;
            animation: highlight 1000ms ease-out;
        }

        @keyframes highlight {
            0% {
                background-color: green;
            }

            100% {
                background-color: inherit;
            }
        }

        .highlight{
            color: green;
            background-color: yellow;
        }


        .limit-des {
            white-space: nowrap;
            width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .hide-overflow {
            overflow: hidden;
        }
    </style>

</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="index.php">UIPR CMS DDL</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarText">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="nav-link" href="add.php">Añadir Artículo</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="adminpanel.php">Panel de Administrador</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Documentación</a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item disabled" href="https://github.com/DustinDiazLopez/UIPR-Project-DDL/wiki/User-Doc">Usuario</a>
                        <a class="dropdown-item disabled" href="https://github.com/DustinDiazLopez/UIPR-Project-DDL/wiki/Dev-Doc">Desarrolladores</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" target="_blank" href="https://github.com/DustinDiazLopez/UIPR-Project-DDL">Código Fuente</a>
                        <a class="dropdown-item" target="_blank" href="https://github.com/DustinDiazLopez/UIPR-Project-DDL#readme">README</a>
                        <a class="dropdown-item" target="_blank" href="https://github.com/DustinDiazLopez/UIPR-Project-DDL/blob/main/LICENSE">LICENSE</a>
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

                <a href="logout.php" class="btn btn-outline-warning" style="font-weight:bold;color:black;"><?php echo $_SESSION['username']; ?>, cerrar sesión. </a>
            </span>
        </div>
    </nav>

    <?php
    if (!isset($conn) || !$conn) {
        echo showWarn("Uh-Oh! MySQL DB Error No. $error_no:", $error);
        die("");
    }
    ?>