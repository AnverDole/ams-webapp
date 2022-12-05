<?php

require_once __DIR__ . "/../app/flow.php";
checkFlowAccess();


if (!isset($head)) {
    $head = [];
}
if (!isset($foot)) {
    $foot = [];
}


function addToHead(callable $html)
{
    global $head;
    ob_start();
    $html();
    $head[] = ob_get_clean();
}
function addToFoot(callable $html)
{
    global $foot;
    ob_start();
    $html();
    $foot[] = ob_get_clean();
}

function renderHead()
{
    global $head;
    foreach ($head as $layout) {
        echo $layout;
    }
}
function renderFoot()
{
    global $foot;
    foreach ($foot as $layout) {
        echo $layout;
    }
}
