
<?php if (isset($page) && isset($pages)): ?>
<nav aria-describedby="pagination">
    <ul class="pagination justify-content-center">
        <?php if ($page > 1) { ?>
            <li class="page-item">
                <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
            </li>
        <?php } else { ?>
            <li class="page-item disabled">
                <a class="page-link" href="#" tabindex="-1">Previous</a>
            </li>
        <?php }  ?>
        <li class="page-item"><a class="page-link" href="?page=1">1</a></li>
        <li class="page-item active">
            <a class="page-link" href="#"><?php echo $page; ?> <span class="sr-only">(current)</span></a>
        </li>
        <li class="page-item"><a class="page-link" href="?page=<?php echo $pages; ?>"><?php echo $pages; ?></a></li>
        <?php if ($page < $pages) { ?>
            <li class="page-item">
                <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
            </li>
        <?php } else { ?>
            <li class="page-item disabled">
                <a class="page-link" href="#" tabindex="-1">Next</a>
            </li>
        <?php }  ?>
    </ul>
</nav>
<?php endif; ?>