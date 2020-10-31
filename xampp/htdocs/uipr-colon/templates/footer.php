<footer class="bg-light">
    <center>
        <p>UIPR 2020.</p>
        <p>
            <a href="#">Back to top</a>
        </p>
    </center>
</footer>

<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script charset="utf-8" src="js/jquery-3.2.1.slim.min.js"></script>
<script charset="utf-8" src="js/popper.min.js"></script>
<script charset="utf-8" src="js/bootstrap.min.js"></script>
<script charset="utf-8" src="js/open.pdf.js"></script>
<script charset="utf-8" src="js/generic.js"></script>

<?php
// highlight search query
if (isset($_GET['q']) && isset($only) && !empty($only)) {
    $str = htmlspecialchars($_GET['q']);
    switch ($only) {
        case 'title':
            ?>
            <script> highlightTitles('<?php echo $str; ?>')</script>
            <?php
            break;
        case 'description':
            ?>
            <script> highlightDescriptions('<?php echo $str; ?>')</script>
            <?php
            break;
        case 'file':
            ?>
            <script> highlightFiles('<?php echo $str; ?>')</script>
            <?php
            break;
        default:
            ?>
            <script> highlightAll('<?php echo $str; ?>')</script>
            <?php
            break;
    }
}
?>

<script>
    // Changes the span in the search field
    const radioSelected = 'input[name=restrictSearchRadio]'
    const spanSelectedId = 'selected-restriction'
    $(radioSelected).click(function() {
        document.getElementById(spanSelectedId).innerHTML = $(radioSelected + ":checked").val();
    });

    $(function() {
        $('[data-toggle="tooltip"]').tooltip()
    })
</script>

</body>

</html>
<?php if (isset($conn)) mysqli_close($conn); ?>