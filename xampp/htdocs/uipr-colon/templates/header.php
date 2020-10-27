<?php
include_once('connect.php');
include_once('utils/utils.php');

session_start();

if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] === FALSE) {
    header('Location: login.php');
}
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

        .highlight {
            background-color: green;
        }

        .limit-des {
            white-space: nowrap;
            width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>

    <?php
    $current = $active = 'index.php';

    function current_active($input)
    {
        $req_uri = $_SERVER['REQUEST_URI'];
        if (strpos($req_uri, 'index.php')) {
            if ('index.php' === $input) {
                echo 'active';
            }
        } elseif (strpos($req_uri, 'add.php')) {
            $current = $active = 'add.php';
        }
    }
    ?>

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
                    <a class="nav-link" href="#">Editar</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">History</a>
                </li>
            </ul>
            <span class="navbar-text">
                <?php
                if (!$conn) {
                    $error_no = mysqli_connect_errno();
                    $error = mysqli_connect_error();
                    echo "<button type=\"button\" class=\"btn btn-danger\" title=\"$error\">Not Connected! <span class=\"badge badge-light\">Error No. $error_no</span></button>";
                }
                ?>

                <a href="logout.php" class="btn btn-outline-warning" style="font-weight:bold;color:black;"> Cerrar sesión </a>
            </span>
        </div>
    </nav>

    <?php
    if (!$conn) {
        echo showWarn("Uh-Oh! MySQL DB Error No. $error_no:", $error);
        die("");
    }
    ?>