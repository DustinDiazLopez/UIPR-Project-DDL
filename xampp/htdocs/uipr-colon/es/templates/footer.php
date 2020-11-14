<div style="width: 100%; height: 100%">
    <footer class="bg-light">
        <div style="text-align: center;">
            <p><a href="LICENSE">Licencia</a></p>
            <p>
                <a href="#">Volver Arriba</a>
            </p>
        </div>
    </footer>
</div>

<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script charset="utf-8" src="js/jquery-3.2.1.slim.min.js"></script>
<script charset="utf-8" src="js/popper.min.js"></script>
<script charset="utf-8" src="js/bootstrap.min.js"></script>
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

    function copyValueToClipboard(inputId, btnId, showText) {
        let copyText = document.getElementById(inputId);
        copyText.style.display = "block";
        copyText.select();
        copyText.setSelectionRange(0, 99999)
        document.execCommand("copy");
        copyText.style.display = "none";

        let btn = document.getElementById(btnId)
        btn.innerHTML = `<i class="fas fa-check"></i> ` + (showText ? 'Copiado' : '');
        btn.style.opacity = "0.5";
        btn.copied = true;
    }

    const copyIcon = '<i class="far fa-copy"><span class="sr-only">Copiar enlace a documento</span></i>';
    const shareIcon = '<i class="fas fa-share-alt"><span class="sr-only">Compartir enlace a documento</span></i>';

    function changeIcon(btn) {
        if (!btn.copied) {
            btn.innerHTML = copyIcon;

        }
    }

    function revertIcon(btn) {
        if (!btn.copied) {
            btn.innerHTML = shareIcon;
        }
    }
</script>

</body>

</html>
<?php if (isset($conn)) mysqli_close($conn); ?>