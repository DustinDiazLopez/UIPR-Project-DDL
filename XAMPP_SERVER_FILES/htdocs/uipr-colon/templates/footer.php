<footer class="bg-light">
    <center>
        <p>Copyright &copy; UIPR 2020.</p>
        <p>
            <a href="#">Back to top</a>
        </p>
    </center>
</footer>

<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script charset="utf-8" src="js/jquery-3.2.1.slim.min.js"></script>
<script charset="utf-8" src="js/popper.min.js"></script>
<script charset="utf-8" src="js/bootstrap.min.js"></script>

<script>

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
</script>

</body>

</html>
<?php mysqli_close($conn); ?>