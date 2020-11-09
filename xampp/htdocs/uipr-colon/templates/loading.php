
<?php
$color = isset($background_color) ? $background_color : 'rgba(0, 0, 0, 0.75)';
?>

<style>
    #loading {
        display: block;
        background: <?php echo $color?>;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 99;
    }

    #loading-image {
        position: relative;
        margin-left: auto;
        margin-right: auto;
        top: 40%;
        left: 42%;
        z-index: 100;
    }
</style>

<div id="loading">
    <img id="loading-image" src="images/processing.svg" alt="Loading..." />
</div>

<script>
    window.onload = function(){ document.getElementById("loading").style.display = "none" }
</script>