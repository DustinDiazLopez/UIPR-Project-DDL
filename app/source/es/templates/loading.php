
<?php
$color = isset($background_color) ? $background_color : 'rgba(0, 0, 0, 0.75)';
?>

<div id="ddl-lda">
    <style>
        #ddl-img-loading-div {
            display: block;
            background: <?php echo $color?>;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 99;
        }

        #ddl-loading-image {
            position: relative;
            margin-left: auto;
            margin-right: auto;
            top: 40%;
            left: 42%;
            z-index: 100;
        }
    </style>

    <div id="ddl-img-loading-div">
        <img id="ddl-loading-image" src="images/processing.svg" alt="Processando..." />
    </div>

    <script>
        window.onload = function __rm_ddl_lda_div__() {
            document.getElementById("ddl-img-loading-div").style.display = "none";
            document.getElementById("ddl-lda").parentNode.removeChild(document.getElementById("ddl-lda"));
        };
    </script>
</div>