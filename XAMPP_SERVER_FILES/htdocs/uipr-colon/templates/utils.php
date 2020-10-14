<?php
include('timepassed.php');
function showWarn($title, $msg, $showCloseBtn = false) {
    $ret = "<div class=\"alert alert-warning alert-dismissible fade show\" role=\"alert\">
        <strong>$title</strong> $msg";
    if ($showCloseBtn) {
       return $ret . "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\"><span aria-hidden=\"true\">&times;</span></button></div>";
    } else {
        return $ret . "</div>";
    }
}

function showSuccess($head, $msg, $footer) {
    return "<div class=\"alert alert-success\" role=\"alert\"><h4 class=\"alert-heading\">$head</h4><p>$msg</p><hr /><p class=\"mb-0\">$footer</p></div>";
}

?>