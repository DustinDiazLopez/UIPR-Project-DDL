<footer class="bg-light">
    <center>
        <p>Copyright &copy; Dustin A. Díaz 2020.</p>
        <p>
            <a href="#">Back to top</a>
        </p>
    </center>
</footer>

<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script charset="utf-8" src="js/jquery-3.2.1.slim.min.js"></script>
<script charset="utf-8" src="js/popper.min.js"></script>
<script charset="utf-8" src="js/bootstrap.min.js"></script>
<script charset="utf-8" src="js/fontawesome.js"></script>

<script>
    // open pdf in new window
    function openPDFDDL(base64URL) {
        let win = window.open();
        win.document.write('<style>body{margin:0;}iframe{display:block;background: white;border: none;height: 100vh;width: 100vw;}</style>');
        win.document.write('<iframe src="' + base64URL + '" frameborder="0" style="border:0; top:0px; left:0px; bottom:0px; right:0px; width:100%; height:100%;" allowfullscreen>Your browser does not support iframe. Please <a href="' + base64URL + '" download alt="link to download pdf">download the pdf</a>.</iframe>');
    }


    //Get the button
    let mybutton = document.getElementById("backToTopBtn");

    // When the user scrolls down 20px from the top of the document, show the button
    window.onscroll = function() {
        scrollFunction()
    };

    function scrollFunction() {
        if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
            mybutton.style.display = "block";
        } else {
            mybutton.style.display = "none";
        }
    }

    // When the user clicks on the button, scroll to the top of the document
    function topFunction() {
        document.body.scrollTop = 0;
        document.documentElement.scrollTop = 0;
    }

    $('.alert').alert('close')
</script>



</body>

</html>
<?php
mysqli_close($conn);
?>