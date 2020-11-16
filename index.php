<style>
:root {
    --green: #008066;
    --yellow: #FED557;
}

.cap {
    text-transform: capitalize;
}

a:link {
    text-decoration: none;
    color: var(--green) !important;
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



:target {
    border-radius: 3px;
    animation: highlight 1000ms ease-out;
}

@keyframes highlight {
    0% {
        background-color: var(--green);
    }

    100% {
        background-color: inherit;
    }
}

.highlight{
    color: var(--green);
    background-color: var(--yellow);
}

::selection {
    background: var(--yellow);
    color: var(--green);
}

::-moz-selection {
    background: var(--yellow);
    color: var(--green);
}
</style>


<?php
include_once('consts.php');
echo '<h1>Demo Site: <a target="_blank" href="'.DEV_SITE.'">Go</a></h1>';
//echo '<h1>Demo Site: <span style="color:red">Down for Maintenance</span></h1>';
echoPaths('<h2>Testing In Progress (II)</h2>');

