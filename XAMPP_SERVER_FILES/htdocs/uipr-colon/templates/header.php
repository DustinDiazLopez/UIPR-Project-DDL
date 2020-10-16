<?php
include('connect.php');
include('utils/utils.php');
?>

<!DOCTYPE html>
<html lang="en">

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

        .center-div {
            display: inline;
            float: none;
        }
    </style>

</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">UIPR CMS DDL</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarText">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item active">
                    <a class="nav-link" href="#">Home <span class="sr-only">(current)</span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Account</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">History</a>
                </li>
            </ul>
            <span class="navbar-text">
                <a href="#add"><button class="btn btn-outline-success my-2 my-sm-0" alt="">Add Item</button></a>
                <?php
                if (!$conn) {
                    $error_no = mysqli_connect_errno();
                    $error = mysqli_connect_error();
                    echo "<button type=\"button\" class=\"btn btn-danger\" title=\"$error\">Not Connected! <span class=\"badge badge-light\">Error No. $error_no</span></button>";
                } else {
                    echo '<button type="button" class="btn btn-success" title="A connection to the database was successful">Connected</button>';
                }
                ?>
            </span>
        </div>
    </nav>

    <?php
    if (!$conn) {
        echo showWarn("Uh-Oh! MySQL DB Error No. $error_no:", $error);
        die("Stopped generating page due to a database error!");
    }
    ?>