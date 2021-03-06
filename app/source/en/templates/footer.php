<div style="width: 100%; height: 100%">
    <footer class="bg-light">
        <div style="text-align: center;">
            <p><a href="LICENSE">Copyright &copy; Dustin Díaz</a></p>
            <p>
                <a href="#">back to the top</a>
            </p>
        </div>
    </footer>
</div>

<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script charset="utf-8" type="text/javascript" src="./../js/jquery-3.2.1.slim.min.js"></script>
<script charset="utf-8" type="text/javascript" src="./../js/popper.min.js"></script>
<script charset="utf-8" type="text/javascript" src="./../js/bootstrap.min.js"></script>
<script charset="utf-8" type="text/javascript" src="./../js/generic.js"></script>
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

    function copyValueToClipboard(inputId, btnId, showText) {
        let copyText = document.getElementById(inputId);
        copyText.style.display = "block";
        copyText.select();
        copyText.setSelectionRange(0, 99999)
        document.execCommand("copy");
        copyText.style.display = "none";

        let btn = document.getElementById(btnId)
        btn.innerHTML = `<i class="fas fa-check"></i> ` + (showText ? 'Copied' : '');
        btn.style.opacity = "0.5";
        btn.copied = true;
    }
</script>

</body>

</html>
<?php if (isset($conn)) mysqli_close($conn); ?>