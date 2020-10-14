<?php
include('connect.php');
include('utils/utils.php');
include('model/Item.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <!-- meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link href="css/fa/css/all.css" rel="stylesheet">
    <title>Library CMS - DDL</title>

    <script>
        // open pdf in new window
        function openPDFDDL(base64URL) {
            let win = window.open();
            win.document.write('<style>body{margin:0;}iframe{display:block;background: white;border: none;height: 100vh;width: 100vw;}</style>');
            win.document.write('<iframe src="' + base64URL + '" frameborder="0" style="border:0; top:0px; left:0px; bottom:0px; right:0px; width:100%; height:100%;" allowfullscreen>Your browser does not support iframe. Please <a href="' + base64URL + '" download alt="link to download pdf">download the pdf</a>.</iframe>');
        }

        function openPDFPHP(item_id, download, name) {
            window.open(`${window.location.href}file.php?id=${item_id}&download=${download}&name=${name}`);
        }
    </script>

    <style>
        a:link {
            text-decoration: none;
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

        .container-ddl {
            position: relative;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
            background-color: white;
            padding: 20px;
            width: auto;
            /* padding-top: 10px; */
            border: 1;
        }

        /* On small screens, set height to 'auto' for sidenav and grid */
        @media screen and (max-width: 767px) {
            .row.content {
                height: auto;
            }
        }

        .overlay {
            position: absolute;
            top: 0;
            right: 0;
            background: rgb(0, 0, 0);
            background: rgba(0, 0, 0, 0.01);
            /* Black see-through */
            color: #f1f1f1;
            width: 100%;
            height: 0%;
            transition: .5s ease;
            opacity: 0;
            color: white;
            font-size: 20px;
            /* padding: 20px; */
            text-align: right;
            resize: inherit;
        }

        .container-ddl:hover {
            background-color: rgba(0, 0, 0, 0.01);

        }

        .container-ddl:hover .overlay {
            opacity: 1;
        }

        .icon-btn {
            background-color: rgba(0, 0, 0, 0);
            border: none;
            color: white;
            padding: 12px 16px;
            font-size: 25px;
            cursor: pointer;
            text-shadow: 2px 2px 4px #000000;
            /* width: 100%; */
            /* height: auto; */
            color: white;
            display: inline-block;
        }

        .blue:hover {
            color: #34d5eb;
        }

        .green:hover {
            color: #04b800;
        }

        .edit:hover {
            color: #dbd123;
        }

        .delete:hover {
            color: red;
        }

        :not(div)>form {
            color: red;
        }

        div.inline {
            float: left;
            padding: 5px;
        }

        .clearBoth {
            clear: both;
        }

        .inline img {
            width: 100%;
            max-width: 100px;
        }

        #backToTopBtn {
            display: none;
            position: fixed;
            bottom: 20px;
            right: 30px;
            z-index: 99;
            font-size: 18px;
            border: none;
            outline: none;
            background-color: rgba(75, 75, 75, 0.50);
            color: white;
            cursor: pointer;
            padding: 15px;
            border-radius: 50%;
        }

        #backToTopBtn:hover {
            background-color: rgba(50, 50, 50, 0.75);
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

    <button onclick="topFunction()" id="backToTopBtn" title="Go to the top of the page">
        <span class="icon-btn green fas fa-long-arrow-alt-up"></span>
    </button>